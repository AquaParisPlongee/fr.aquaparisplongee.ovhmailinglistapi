<?php

if (_ovhmailinglistapi_is_civirules_installed()) {
  return array (
    0 =>
      array (
        'name' => 'Civirules:Action.OVHMailingListApi',
        'entity' => 'CiviRuleAction',
        'params' =>
          array (
            'version' => 3,
            'name' => 'ovhmailinglistapi_send',
            'label' => 'Modify OVH mailing list subscription',
            'class_name' => 'CRM_OVHMailingListApi_CivirulesAction',
            'is_active' => 1
          ),
      ),
  );
}
else { return array(); }
