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
function _civicrm_api3_o_v_h_mailing_list_modify_spec(&$spec) {
    $spec['contact_id'] = array(
        'title' => 'Contact ID',
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
    $spec['modify'] = array(
        'title' => 'List name',
        // 'type' => CRM_Utils_Type::T_ENUM,
        'type' => CRM_Utils_Type::T_INT,
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
function civicrm_api3_o_v_h_mailing_list_modify($params) {	
  require __DIR__ . '/credential.php';
  $version = CRM_Core_BAO_Domain::version();
  if (!preg_match('/[0-9]+(,[0-9]+)*/i', $params['contact_id'])) {
    throw new API_Exception('Parameter contact_id must be a unique id or a list of ids separated by comma');
  }
  $contactIds = explode(",", $params['contact_id']);
  $case_id = false;
  if (isset($params['case_id'])) {
    $case_id = $params['case_id'];
  }

  if (!isset($params['list_name']) || !isset($params['list_domain']) || !isset($params['modify'])) {
    throw new API_Exception('You have to provide list_name and list_domain and modify');
  }
  $list_name = $params['list_name'];
  $list_domain = $params['list_domain'];
  $modify = $params['modify'];

  $domain     = CRM_Core_BAO_Domain::getDomain();
  $result     = NULL;
  $hookTokens = array();

  $returnValues = array();
  foreach($contactIds as $contactId) {
    $contact_params = array(array('contact_id', '=', $contactId, 0, 0));
    list($contact, $_) = CRM_Contact_BAO_Query::apiQuery($contact_params);

    //CRM-4524
    $contact = reset($contact);

    if (!$contact || is_a($contact, 'CRM_Core_Error')) {
      throw new API_Exception('Could not find contact with ID: ' . $contact_params['contact_id']);
    }

    //CRM-5734

    // get replacement text for these tokens
    $returnProperties = array(
        'sort_name' => 1,
        'email' => 1,
        'do_not_email' => 1,
        'is_deceased' => 1,
        'on_hold' => 1,
        'display_name' => 1,
        'preferred_mail_format' => 1,
    );
    if ($case_id) {
      $contact['case.id'] = $case_id;
    }

    if ($alternativeEmailAddress) {
      /**
       * If an alternative reciepient address is given
       * then send e-mail to that address rather than to
       * the e-mail address of the contact
       *
       */
      $email = $alternativeEmailAddress;
    } elseif ($contact['do_not_email'] || empty($contact['email']) || CRM_Utils_Array::value('is_deceased', $contact) || $contact['on_hold']) {
      /**
       * Contact is decaused or has opted out from mailings so do not send the e-mail
       */
      throw new API_Exception('Suppressed sending e-mail to: ' . $contact['display_name']);
    } else {
      /**
       * Send e-mail to the contact
       */
      $email = $contact['email'];
    }


    $ovh = new Api($applicationKey,
                   $applicationSecret,
                   $endpoint,
                   $consumer_key);
    //GET
    try {
        $result = $ovh->get('/email/domain/' . $list_domain . '/mailingList/' . $list_name . '/subscriber/' . $email);
        $present = TRUE;
    } catch (GuzzleHttp\Exception\ClientException $e) {
        $response = $e->getResponse();
        if ($response->getStatusCode() == 404) {
            $present = FALSE;
        }
    }
    if ($modify == 1) {
        if ($present == FALSE) {
            //ADD
            $result = $ovh->post('/email/domain/' . $list_domain . '/mailingList/' . $list_name . '/subscriber',
                                 array('email' => $email));
            $details = "Add to $list_name@$list_domain list.";
        } else {
            $details = "Already in $list_name@$list_domain list: not added.";
        }
    } elseif ($modify == 0){
        if ($present) {
            //DELETE
            $result = $ovh->delete('/email/domain/' . $list_domain . '/mailingList/' . $list_name . '/subscriber/' . $email);
            $details = "Remove from $list_name@$list_domain list.";
        } else {
            $details = "Not in $list_name@$list_domain list: not removed.";
        }
    } else {
        $details = "unknown action modify: " . $modify;
    }

    // $myfile = file_put_contents('/var/www/html/logs.txt', $details.PHP_EOL , FILE_APPEND | LOCK_EX);
    // Save the result as activity
    // $subject = "Mailing list subscription modification $list_name@$list_domain";
    $activityTypeID = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Email');

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

    $returnValues[$contactId] = array(
      'contact_id' => $contactId,
      'modify' => 1,
      'status_msg' => "Succesfully modify subscription to $list_name@$list_domain",
    );
  }


  return civicrm_api3_create_success($returnValues, $params, 'OVHMailingList', 'Modify');
  //throw new API_Exception(/*errorMessage*/ 'Everyone knows that the magicword is "sesame"', /*errorCode*/ 1234);
}

