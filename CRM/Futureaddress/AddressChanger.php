<?php

/* 
 * This class checks for addresses of type (future) to be changed to type (current)
 */

class CRM_Futureaddress_AddressChanger extends CRM_AddressChanger_Model_Changer {
  
  protected $future_type_id;
  
  /**
   * Initialize the class for changing addresses
   * 
   * All adresses which should become active from today are given in $future_location_type_id
   * they are changed to $active_location_type_id
   * 
   * All current $active_location_type_id are saved into an activity
   * 
   * @param int $future_location_type_id
   * @param int $current_location_type_id
   */
  public function __construct($future_location_type_id, $active_locationtype_id) {
    parent::__construct($active_locationtype_id);
    $this->future_type_id = $future_location_type_id;
  }
  
  protected function findAddressesToChange() {
    $config = CRM_AddressChanger_Config::singleton();
    $cgroup = $config->getCustomGroup();
    $changeField = $config->getChangeDateField();
    $processField = $config->getProcessDateField();
  
    //retrieve all addresses which should be changed
    $sql = "SELECT `a`.*, `c`.`id` AS `rid` FROM `civicrm_address` `a` INNER JOIN `".$cgroup['table_name']."` `c` ON `a`.`id` = `c`.`entity_id` 
    WHERE 
    `a`.`location_type_id` = %1 AND
    `c`.`".$changeField['column_name']."` <= NOW() AND
    `c`.`".$processField['column_name']."` IS NULL
    ORDER BY `c`.`".$changeField['column_name']."` ASC
    ";
    $dao = CRM_Core_DAO::executeQuery(
        $sql, 
        array(
           '1' => array($this->future_type_id, 'Integer')
        ), 
        TRUE, 
        'CRM_Core_BAO_Address'
    );
    return $dao;
  }
  
}

