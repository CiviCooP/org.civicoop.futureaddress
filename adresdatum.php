<?php

require_once 'adresdatum.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function adresdatum_civicrm_config(&$config) {
  _adresdatum_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function adresdatum_civicrm_xmlMenu(&$files) {
  _adresdatum_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function adresdatum_civicrm_install() {
  return _adresdatum_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function adresdatum_civicrm_uninstall() {
  return _adresdatum_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function adresdatum_civicrm_enable() {
  return _adresdatum_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function adresdatum_civicrm_disable() {
  return _adresdatum_civix_civicrm_disable();
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
function adresdatum_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _adresdatum_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function adresdatum_civicrm_managed(&$entities) {
  return _adresdatum_civix_civicrm_managed($entities);
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
function adresdatum_civicrm_caseTypes(&$caseTypes) {
  _adresdatum_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function adresdatum_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _adresdatum_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_custom
 * 
 * Update the process date when the change date entered is in the future
 * This way we will make sure the adress gets parsed again in the future
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_custom
 */
function adresdatum_civicrm_custom($op, $groupID, $entityID, &$params) {
  if ($op != 'create' && $op != 'edit') {
    return;
  }
  
  $config = CRM_Adresdatum_Config::singleton();
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
