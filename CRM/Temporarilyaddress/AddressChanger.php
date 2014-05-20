<?php

/* 
 * This class checks for addresses of type (temp) to be changed to type (current) and change current to future
 */

class CRM_Temporarilyaddress_AddressChanger extends CRM_AddressChanger_Model_Changer {
  
  protected $temp_location_type_id;
  protected $future_type_id;
  
  /**
   * Initialize the class for changing addresses
   * 
   * All adresses which should become active from today are given in $future_location_type_id
   * they are changed to $active_location_type_id
   * 
   * All current $active_location_type_id are saved into an activity
   * 
   * @param int $temp_location_type_id
   * @param int $future_location_type_id
   * @param int $current_location_type_id
   */
  public function __construct($temp_location_type_id, $future_location_type_id, $active_location_type_id) {
    parent::__construct($active_location_type_id);
    $this->temp_location_type_id = $temp_location_type_id;
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
           '1' => array($this->temp_location_type_id, 'Integer')
        ), 
        TRUE, 
        'CRM_Core_BAO_Address'
    );
    
    return $dao;
  }
  
  protected function changeAddress(CRM_Core_BAO_Address $objAddress, $change_to_type_id) {
    $config = CRM_AddressChanger_Config::singleton();
    $change_date_field = $config->getChangeDateField();
    
    //retrieve current active address
    $current = new CRM_Core_BAO_Address();
    $current->contact_id = $objAddress->contact_id;
    $current->location_type_id = $change_to_type_id;
    if ($current->find(TRUE)) {     
      //acrhive the old address
      $this->archiveAddress($current);
      
      //save current adress into a future address
      $params = array();
      $params['id'] = $current->id;
      $params['location_type_id'] = $this->future_type_id;
      //add date parameters to this address
      $endDate = $this->getEndDateForAddress($objAddress->id);
      if (!empty($endDate)) {
        $endDate = new DateTime($endDate);
        $params['custom_'.$change_date_field['id']] = $endDate->format('Ymd');
      }
      
      civicrm_api3('Address', 'create', $params);
    }
   
    $params = array();
    //CRM_Core_DAO::storeValues($objAddress, $params);
    $params['id'] = $objAddress->id;
    $params['location_type_id'] = $change_to_type_id;
    //set future address to primary if current active address is primary
    if ($current->is_primary) {
      $params['is_primary']= '1';
    }
    
    civicrm_api3('Address', 'create', $params);
  } 
  
  private function getEndDateForAddress($address_id) {
    $config = CRM_AddressChanger_Config::singleton();
    $end_date_field = $config->getEndDateField();
    $end_date_field_id = $end_date_field['id'];
    $custom_values['entityID'] = $address_id;
    $custom_values['custom_'.$end_date_field_id] = '1';
    $values = CRM_Core_BAO_CustomValueTable::getValues($custom_values);
    if (isset($values['custom_'.$end_date_field_id])) {
      return $values['custom_'.$end_date_field_id];
    }
    return '';
  }
  
}

