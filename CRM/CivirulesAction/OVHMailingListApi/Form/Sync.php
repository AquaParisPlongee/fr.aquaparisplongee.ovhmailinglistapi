<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_CivirulesAction_OVHMailingListApi_Form_Sync extends CRM_Core_Form {

  protected $ruleActionId = false;

  protected $ruleAction;

  protected $rule;

  protected $action;

  protected $triggerClass;

  protected $hasCase = false;

  /**
   * Method to get groups
   *
   * @return array
   * @access protected
   */
  protected function getGroups() {
    return CRM_Contact_BAO_GroupContact::getGroupList();
  }

  /**
   * Overridden parent method to do pre-form building processing
   *
   * @throws Exception when action or rule action not found
   * @access public
   */
  public function preProcess() {
    $this->ruleActionId = CRM_Utils_Request::retrieve('rule_action_id', 'Integer');
    $this->ruleAction = new CRM_Civirules_BAO_RuleAction();
    $this->action = new CRM_Civirules_BAO_Action();
    $this->rule = new CRM_Civirules_BAO_Rule();
    $this->ruleAction->id = $this->ruleActionId;
    if ($this->ruleAction->find(true)) {
      $this->action->id = $this->ruleAction->action_id;
      if (!$this->action->find(true)) {
        throw new Exception('CiviRules Could not find action with id '.$this->ruleAction->action_id);
      }
    } else {
      throw new Exception('CiviRules Could not find rule action with id '.$this->ruleActionId);
    }

    $this->rule->id = $this->ruleAction->rule_id;
    if (!$this->rule->find(true)) {
      throw new Exception('Civirules could not find rule');
    }

    $this->triggerClass = CRM_Civirules_BAO_Trigger::getTriggerObjectByTriggerId($this->rule->trigger_id, true);
    $this->triggerClass->setTriggerId($this->rule->trigger_id);
    $providedEntities = $this->triggerClass->getProvidedEntities();
    if (isset($providedEntities['Case'])) {
      $this->hasCase = true;
    }

    parent::preProcess();
  }


  function buildQuickForm() {

    // $this->setFormTitle();
    // $this->registerRule('emailList', 'callback', 'emailList', 'CRM_Utils_Rule');

    $this->add('hidden', 'rule_action_id');
    $this->add('text', 'list_name', ts('List name'), TRUE, TRUE);
    $this->add('text', 'list_domain', ts('List domain'), TRUE, TRUE);

    $this->add('select', 'group_id', ts('Group'), array('' => ts('-- please select --')) + $this->getGroups());

    if ($this->hasCase) {
      $this->add('checkbox','file_on_case', ts('File subscription on case'));
    }
    $this->assign('has_case', $this->hasCase);

    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  public function addRules() {
    $this->addFormRule(array('CRM_CivirulesActions_GroupContact_Form_GroupId', 'validateGroupFields'));
  }

  /**
   * Function to validate value of rule action form
   *
   * @param array $fields
   * @return array|bool
   * @access public
   * @static
   */
  static function validateGroupFields($fields) {
    $errors = array();
    $errors['group_id'] = ts('You have to select at least one group');
    if (count($errors)) {
      return $errors;
    }
    return true;
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $data = array();
    $defaultValues = array();
    $defaultValues['rule_action_id'] = $this->ruleActionId;
    if (!empty($this->ruleAction->action_params)) {
      $data = unserialize($this->ruleAction->action_params);
    }
    if (!empty($data['group_id'])) {
      $defaultValues['group_id'] = $data['group_id'];
    }
    if (!empty($data['list_name'])) {
      $defaultValues['list_name'] = $data['list_name'];
    }
    if (!empty($data['list_domain'])) {
      $defaultValues['list_domain'] = $data['list_domain'];
    }
    $defaultValues['file_on_case'] = false;
    if (!empty($data['file_on_case'])) {
      $defaultValues['file_on_case'] = true;
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data['group_id'] = $this->_submitValues['group_id'];
    $data['list_name'] = $this->_submitValues['list_name'];
    $data['list_domain'] = $this->_submitValues['list_domain'];
    $data['file_on_case'] = false;
    if (!empty($this->_submitValues['file_on_case'])) {
      $data['file_on_case'] = true;
    }

    $ruleAction = new CRM_Civirules_BAO_RuleAction();
    $ruleAction->id = $this->ruleActionId;
    $ruleAction->action_params = serialize($data);
    $ruleAction->save();

    $session = CRM_Core_Session::singleton();
    $session->setStatus('Action '.$this->action->label.' parameters updated to CiviRule '.CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->ruleAction->rule_id),
      'Action parameters updated', 'success');

    $redirectUrl = CRM_Utils_System::url('civicrm/civirule/form/rule', 'action=update&id='.$this->ruleAction->rule_id, TRUE);
    CRM_Utils_System::redirect($redirectUrl);
  }

  /**
   * Method to set the form title
   *
   * @access protected
   */
  protected function setFormTitle() {
    $title = 'CiviRules Edit Action parameters';
    $this->assign('ruleActionHeader', 'Edit action '.$this->action->label.' of CiviRule '.CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->ruleAction->rule_id));
    CRM_Utils_System::setTitle($title);
  }
}
