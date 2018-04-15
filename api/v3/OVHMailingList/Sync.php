<?php
require_once __DIR__ . '/vendor/autoload.php';
use \Ovh\Api;

/**
 * OVHMailingList.Modify API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_o_v_h_mailing_list_sync_spec(&$spec) {
    $spec['group_id'] = array(
        'title' => 'Group ID',
        'type' => CRM_Utils_Type::T_INT,
        'api.required' => 1,
    );
    $spec['list_name'] = array(
        'title' => 'List name',
        'type' => CRM_Utils_Type::T_STRING,
        'api.required' => 1,
    );
    $spec['list_domain'] = array(
        'title' => 'List name',
        'type' => CRM_Utils_Type::T_STRING,
        'api.required' => 1,
    );
    $spec['case_id'] = array(
        'title' => 'Case ID',
        'type' => CRM_Utils_Type::T_INT,
    );
}

/**
 * OVHMailingList.Modify API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_o_v_h_mailing_list_sync($params) {	
  require __DIR__ . '/credential.php';
  $version = CRM_Core_BAO_Domain::version();
  if (!preg_match('/[0-9]+/i', $params['group_id'])) {
    throw new API_Exception('Parameter group_id must be a unique id');
  }
  $group_id = $params['group_id'];
  $case_id = false;
  if (isset($params['case_id'])) {
    $case_id = $params['case_id'];
  }

  if (!isset($params['list_name']) || !isset($params['list_domain'])) {
    throw new API_Exception('You have to provide list_name and list_domain');
  }
  $list_name = $params['list_name'];
  $list_domain = $params['list_domain'];

  $domain     = CRM_Core_BAO_Domain::getDomain();
  $result     = NULL;
  $hookTokens = array();



  $ovh = new Api($applicationKey,
                 $applicationSecret,
                 $endpoint,
                 $consumer_key);

  $ovh_email_list = $ovh->get('/email/domain/' . $list_domain . '/mailingList/' . $list_name . '/subscriber');
  $group_contacts = civicrm_api3('GroupContact', 'get', array(
                                 'sequential' => 1,
                                 'group_id' => $group_id,
                                 'status' => "Added",
                                 'options' => array( 'limit' => 200,),
                                ));
  $group_email_list = array();
  if ($group_contacts['count'] > 0) {
      foreach ($group_contacts['values'] as $contact_in_group){
          $contact_params = array(array('contact_id', '=', $contact_in_group['contact_id'], 0, 0));
          list($contact, $_) = CRM_Contact_BAO_Query::apiQuery($contact_params);
          $contact = reset($contact);
          array_push($group_email_list, $contact['email']);
      }
  }
  $add_counter=0;
  foreach ($group_email_list as $email){
      if (!in_array($email, $ovh_email_list)){
          $result = $ovh->post('/email/domain/' . $list_domain . '/mailingList/' . $list_name . '/subscriber',
                               array('email' => $email));
          $details = "Add to $list_name@$list_domain list.";
          $add_counter = $add_counter + 1;
          register_OVHMailingList_activity($email, $details, $case_id);
      }
  }
  $rm_counter=0;
  foreach ($ovh_email_list as $email){
      if (!in_array($email, $group_email_list)){
          $result = $ovh->delete('/email/domain/' . $list_domain . '/mailingList/' . $list_name . '/subscriber/' . $email);
          $details = "Remove from $list_name@$list_domain list.";
          $rm_counter = $rm_counter + 1;
          register_OVHMailingList_activity($email, $details, $case_id);
      }
  }

  $returnValues[$group_id] = array(
    'group_id' => $group_id,
    'sync' => 1,
    'status_msg' => "Succesfully sync subscription to $list_name@$list_domain (add $add_counter and remove $rm_counter)",
  );


  return civicrm_api3_create_success($returnValues, $params, 'OVHMailingList', 'Sync');
}

function register_OVHMailingList_activity($contact_email, $details, $case_id){
    $version = CRM_Core_BAO_Domain::version();
    $activityTypeID = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Email');
    try{
        $result = civicrm_api3('Contact', 'get', array(
              'sequential' => 1,
              'return' => array("id"),
              'email' => $contact_email,
          ));
    }
    catch (CiviCRM_API3_Exception $e) {
        return;
    }
    if ($result['count'] > 0) {
        foreach ($result['values'] as $contact){
            $contactId = $contact['contact_id'];
            $activityParams = array(
                'source_contact_id' => $contactId,
                'target_contact_id' => $contactId,
                'activity_type_id' => $activityTypeID,
                'activity_date_time' => date('YmdHis'),
                'subject' => $details,
                'details' => $details,
                // FIXME: check for name Completed and get ID from that lookup
                'status_id' => 2,
            );

            $activity = CRM_Activity_BAO_Activity::create($activityParams);

            // Compatibility with CiviCRM >= 4.4
            if ($version >= 4.4) {
              $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
              $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);

              $activityTargetParams = array(
                'activity_id' => $activity->id,
                'contact_id' => $contactId,
                'record_type_id' => $targetID
              );
              CRM_Activity_BAO_ActivityContact::create($activityTargetParams);
            }
            else {
              $activityTargetParams = array(
                'activity_id' => $activity->id,
                'target_contact_id' => $contactId,
              );
              CRM_Activity_BAO_Activity::createActivityTarget($activityTargetParams);
            }

            if (!empty($case_id)) {
              $caseActivity = array(
                'activity_id' => $activity->id,
                'case_id' => $case_id,
              );
              CRM_Case_BAO_Case::processCaseActivity($caseActivity);
            }
        }
    }
}
