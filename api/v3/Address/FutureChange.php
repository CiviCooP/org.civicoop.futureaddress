<?php

/**
 * Address.FutureChange API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_address_futurechange_spec(&$spec) {
  
}

/**
 * Address.FutureChange API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_address_futurechange($params) {
  $config = CRM_Adresdatum_Config::singleton();
  $cgroup = $config->getCustomGroup();
  $changeField = $config->getChangeDateField();
  $processField = $config->getProcessDateField();
  //retrieve all addresses which should be set to primary by now
  $sql = "SELECT `a`.*, `c`.`id` AS `rid` FROM `civicrm_address` `a` INNER JOIN `".$cgroup['table_name']."` `c` ON `a`.`id` = `c`.`entity_id` 
    WHERE 
    `a`.`is_primary` = '0' AND
    `c`.`".$changeField['column_name']."` <= NOW() AND
    `c`.`".$processField['column_name']."` IS NULL
    ORDER BY `c`.`".$changeField['column_name']."` ASC
  ";

  $count = 0;
  $failureCount = 0;
  $dao = CRM_Core_DAO::executeQuery($sql);
  while ($dao->fetch()) {
    //get current primary address and set the end date for today
    try {
      $update_params['contact_id'] = $dao->contact_id;
      $update_params['location_type_id'] = $dao->location_type_id;
      $update_params['address_id'] = $dao->id;
      $update_params['is_primary'] = '1';
      civicrm_api3('Address', 'create', $update_params); //Updated the adress so that it becomes the primary address
      
      $update = "UPDATE `".$cgroup['table_name']."` SET `".$processField['column_name']."` = CURDATE() WHERE `id` = '".$dao->rid."'";
      CRM_Core_DAO::executeQuery($update);
      
      $count ++;
    } catch (Exception $e) {
      //do nothing
      throw $e;
      $failureCount ++;
    }
  }
  
  return civicrm_api3_create_success(array('message' => 'Updated '.$count.' addresses. Failed to update '.$failureCount.' addresses'));
}

