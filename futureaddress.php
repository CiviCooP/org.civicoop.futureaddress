<?php

require_once 'futureaddress.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function futureaddress_civicrm_config(&$config) {
  _futureaddress_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function futureaddress_civicrm_xmlMenu(&$files) {
  _futureaddress_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function futureaddress_civicrm_install() {
  return _futureaddress_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function futureaddress_civicrm_uninstall() {
  return _futureaddress_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function futureaddress_civicrm_enable() {
  return _futureaddress_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function futureaddress_civicrm_disable() {
  return _futureaddress_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function futureaddress_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _futureaddress_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function futureaddress_civicrm_managed(&$entities) {
  return _futureaddress_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function futureaddress_civicrm_caseTypes(&$caseTypes) {
  _futureaddress_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function futureaddress_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _futureaddress_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_custom
 * 
 * Update the process date when the change date entered is in the future
 * This way we will make sure the adress gets parsed again in the future
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_custom
 */
function futureaddress_civicrm_custom($op, $groupID, $entityID, &$params) {
  if ($op != 'create' && $op != 'edit') {
    return;
  }
  
  $config = CRM_AddressChanger_Config::singleton();
  $cgroup = $config->getCustomGroup();
  $changeField = $config->getChangeDateField();
  $processField = $config->getProcessDateField();
  
  if ($cgroup['id'] == $groupID) {
    foreach($params as $param) {
      if ($param['custom_field_id'] == $changeField['id'] && !empty($param['value']) && strtotime($param['value']) >= time()) {
        //change date is changed and is in the future
        //so we should reset the processDate so that this address gets processed again in the future
        $sql = "UPDATE `".$cgroup['table_name']."` SET `".$processField['column_name']."` = NULL WHERE `entity_id` = '".$entityID."'";
        CRM_Core_DAO::executeQuery($sql);
      }
    }
  }
}

/**
 * Implementation of hook_civicrm_pre
 * 
 * Archive deleted addresses
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pre
 * @param type $op
 * @param type $objectName
 * @param type $id
 * @param type $params
 */
function futureaddress_civicrm_pre( $op, $objectName, $id, &$params ) {
  $config = CRM_AddressChanger_Config::singleton();
  if ($objectName == 'Address' && $op == 'delete' && $config->isArchiveOnDeleteEnabled()) {
    //archive address
    $archiver = CRM_AddressChanger_AddressArchiver::singleton();
    $archiver->archiveIntoAnActivityAddress($params, ts('Address removed'));
  } 
}

function futureaddress_civicrm_future_address_get_changer(CRM_Core_BAO_LocationType $location_type) {
  $return = array();
  $changer = CRM_AddressChanger_Factory::getChanger($location_type);
  if ($changer instanceof CRM_AddressChanger_Interface_Changer) {
    $return[$location_type->name] = $changer;
  }
  return $return;
}

/**
 * Add javascript to show/hide the date fields for certain location types
 * 
 * Implementation of hook_civicrm_alterContent
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterContent
 * 
 * @param type $content
 * @param type $context
 * @param type $tplName
 * @param type $object
 */
function futureaddress_civicrm_alterContent(  &$content, $context, $tplName, &$object ) {
  $config = CRM_AddressChanger_Config::singleton();
  $cgroup = $config->getCustomGroup();
  if ($object instanceof CRM_Contact_Form_Inline_Address) {
    
    $location_type_ids = array();
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_location_type` WHERE `is_active` = '1'");
    while($dao->fetch()) {
      if (stripos($dao->name, "new_") === 0 || stripos($dao->name, "temp_") === 0) {
        $location_type_ids[] = $dao->id;
      }
    }
    
    $locBlockNo = CRM_Utils_Request::retrieve('locno', 'Positive', CRM_Core_DAO::$_nullObject, TRUE, NULL, $_REQUEST);
    $template = CRM_Core_Smarty::singleton();
    $template->assign('blockId', $locBlockNo);
    $template->assign('custom_group_id', $cgroup['id']);
    $template->assign('custom_group_name', $cgroup['name']);
    $template->assign('location_type_ids', json_encode($location_type_ids));
    $content .= $template->fetch('CRM/Contact/Form/Edit/Address/futureaddress_js.tpl');
  }
  if ($object instanceof CRM_Contact_Form_Contact) {
    $location_type_ids = array();
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_location_type` WHERE `is_active` = '1'");
    while($dao->fetch()) {
      if (stripos($dao->name, "new_") === 0 || stripos($dao->name, "temp_") === 0) {
        $location_type_ids[] = $dao->id;
      }
    }
    
    $template = CRM_Core_Smarty::singleton();
    $template->assign('custom_group_id', $cgroup['id']);
    $template->assign('custom_group_name', $cgroup['name']);
    $template->assign('location_type_ids', json_encode($location_type_ids));
    $content .= $template->fetch('CRM/Contact/Form/Edit/futureaddress_js.tpl');
  }
}

/**
 * Validate the custom date fields on the address form.
 * 
 * Implementation of hook_civicrm_validateForm
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_validateForm
 * 
 * @param type $formName
 * @param type $fields
 * @param type $files
 * @param type $form
 * @param type $errors
 */
function futureaddress_civicrm_validateForm( $formName, &$fields, &$files, &$form, &$errors ) {
  $config = CRM_AddressChanger_Config::singleton();
  $change_date_field = $config->getChangeDateField();
  $end_date_field = $config->getEndDateField();
  $change_date_field_mask = 'custom_'.$change_date_field['id'].'_';
  $end_date_field_mask = 'custom_' .$end_date_field['id']. '_';
  if ($formName == 'CRM_Contact_Form_Contact' || $formName == 'CRM_Contact_Form_Inline_Address') {
    foreach ($fields['address'] as $key => $address) {
      $changeDateKey = _futureaddress_get_custom_data_by_field_mask($change_date_field_mask, $address);
      $endDateKey = _futureaddress_get_custom_data_by_field_mask($end_date_field_mask, $address);
      $changeDate = false;
      $endDate = false;
      if ($changeDateKey) {
        $changeDate = $address[$changeDateKey];
      }
      if ($endDateKey) {
        $endDate = $address[$endDateKey];
      }
      
      $location_type = new CRM_Core_BAO_LocationType();
      $location_type->id = $address['location_type_id'];
      if ($location_type->find(true)) {
        if (stripos($location_type->name, "new_")===0) {
          if (empty($changeDate)) {
            $errors['address['.$key.']['.$changeDateKey.']'] = ts('Change date is required for a future address');
          }
        } elseif (stripos($location_type->name, "temp_")===0) {
          if (empty($changeDate)) {
            $errors['address['.$key.']['.$changeDateKey.']'] = ts('Change date is required for a future address');
          }
          if (empty($endDate)) {
            $errors['address['.$key.']['.$endDateKey.']'] = ts('End date is required for a temporarily address');
          }
          if (!empty($changeDate) && !empty($endDate)) {
            $changeDate = new DateTime($changeDate);
            $endDate = new DateTime($endDate);
            if ($endDate <= $changeDate) {
              $errors['address['.$key.']['.$endDateKey.']'] = ts('End date must be greater than change date');
            }
          }
        }
      }
    }
  }
}

/**
 * Helper function to retrieve a the field key on a form for custom values
 * 
 * The field key is usually something like custom_x_aa
 * You give this function the mask custom_x as a mask and it returns the key custom_x_aa
 * 
 * @param type $mask
 * @param type $fields
 * @return null
 */
function _futureaddress_get_custom_data_by_field_mask($mask, $fields) {
  foreach($fields as $key => $field) {
    if (strpos($key, $mask)===0) {
      return $key;
    }
  }
  return null;
}