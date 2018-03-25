<?php
/**
 * Class for CiviRule Condition OVHMailingListApi
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_OVHMailingListApi_CivirulesAction extends CRM_CivirulesActions_Generic_Api {

  /**
   * Method to get the api entity to process in this CiviRule action
   *
   * @access protected
   * @abstract
   */
  protected function getApiEntity() {
    return 'OVHMailingList';
  }

  /**
   * Method to get the api action to process in this CiviRule action
   *
   * @access protected
   * @abstract
   */
  protected function getApiAction() {
    return 'modify';
  }

  /**
   * Returns an array with parameters used for processing an action
   *
   * @param array $parameters
   * @param CRM_Civirules_TriggerData_TriggerData $rtiggerData
   * @return array
   * @access protected
   */
  protected function alterApiParameters($parameters, CRM_Civirules_TriggerData_TriggerData $triggerData) {
    //this method could be overridden in subclasses to alter parameters to meet certain criteria
    $parameters['contact_id'] = $triggerData->getContactId();

    $actionParameters = $this->getActionParameters();
    if (!empty($actionParameters['file_on_case'])) {
      $case = $triggerData->getEntityData('Case');
      $parameters['case_id'] = $case['id'];
    }

    return $parameters;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * $access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirules/actions/ovhmailinglistapi', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $params = $this->getActionParameters();
    $who = ts('the contact');
    if ($params['modify'] == 1){
        $label = ts('Add');
    } elseif ($params['modify'] == 2){
        $label = ts('Delete');
    } else {
        $label = ts('Unkown');
    }
    return ts('Modify the registration of %1 to the the mailing list %2@%3 (%4)', array(
        1=>$who,
        2=>$params['list_name'],
        3=>$params['list_domain'],
        4=>$label,
    ));
  }
}
