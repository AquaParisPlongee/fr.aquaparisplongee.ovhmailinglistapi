<?php

if (_ovhmailinglistapi_modify_is_civirules_installed()) {
  return array (
    0 =>
      array (
        'name' => 'Civirules:Action.OVHMailingListApi.Modify',
        'entity' => 'CiviRuleAction',
        'params' =>
          array (
            'version' => 3,
            'name' => 'ovhmailinglistapi_modify',
            'label' => 'Modify OVH mailing list subscription',
            'class_name' => 'CRM_CivirulesAction_OVHMailingListApi_Modify',
            'is_active' => 1
          ),
      ),
  );
}
else { return array(); }
