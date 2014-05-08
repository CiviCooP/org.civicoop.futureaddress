<?php

/* 
 * This class checks for addresses of type (future) to be changed to type (current)
 */

class CRM_Futureaddress_AddressChanger implements CRM_Futureaddress_Interface_Changer {
  
  protected $future_type_id;
  
  protected $active_type_id;
  
  protected $changeCount = 0;
  
  protected $failureCount = 0;
  
  protected $activity_type_id;
  
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
  public function __construct($future_location_type_id, $active_location_type_id) {
    $this->future_type_id = $future_location_type_id;
    $this->active_type_id = $active_location_type_id;
    
    $config = CRM_Futureaddress_Config::singleton();
    $activity_type = civicrm_api3('OptionValue', 'getsingle', array('name' => 'address_change', 'option_group_id' => $config->getActivityTypeOptionGroupId()));
    $this->activity_type_id = $activity_type['value'];
  }
  
  /**
   * Retrieve all addresses which should be changed
   * 
   */
  public function checkAndChange() {
    $this->changeCount = 0;
    $this->failureCount = 0;
    
    $config = CRM_Futureaddress_Config::singleton();
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
    while ($dao->fetch()) {
      try {
        $this->changeAddress($dao, $this->active_type_id);
        
        //update process field
        $update = "UPDATE `".$cgroup['table_name']."` SET `".$processField['column_name']."` = CURDATE() WHERE `id` = '".$dao->rid."'";
        CRM_Core_DAO::executeQuery($update, array(), false); //do not abort on query error
        
        $this->changeCount ++;
      } catch (Exception $e) {
        $this->failureCount ++;    
      }
    }
  }
  
  public function getChangeCount() {
    return $this->changeCount;
  }
  
  public function getFailureCount() {
    return $this->failureCount;
  }
  
  /**
   * Archive an address
   * 
   * Default this will create an activity with the address data
   * 
   * @param type $objAddress
   */
  protected function archiveAddress(CRM_Core_BAO_Address $objAddress) {
       
    $activityParams = array();
    $activityParams['activity_type_id'] = $this->getActivityTypeId();    
    $activityParams['target_contact_id'] = $objAddress->contact_id;
    $activityParams['subject'] = ts('Address change');
    $activityParams['status_id'] = 2; //completed
    
    
    $this->changeActivityParameters($objAddress, $activityParams);
    // create the activity
    
    civicrm_api3('Activity', 'create', $activityParams);
    
    $hooks = CRM_Utils_Hook::singleton();
    $hooks->invoke(1,
      $objAddress, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject,
      'civicrm_future_address_archive_address'
      );
  }
  
  /**
   * Function to be overriden by child classes to set specific activity parameters
   * 
   * @param object $objAddress
   * @param array $activityParams
   */
  protected function changeActivityParameters(CRM_Core_BAO_Address $objAddress, &$activityParams) {
    $history = CRM_Futureaddress_AddressHistory::singleton();
    $history->generateActivityParams($objAddress, $activityParams);
    
    $hooks = CRM_Utils_Hook::singleton();
    $hooks->invoke(2,
      $objAddress, $activityParams, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject,
      'civicrm_future_address_activity_parameters'
      );
  }
  
  /**
   * Returns the activity type id for saving old addresses
   * 
   */
  protected function getActivityTypeId() {
    return $this->activity_type_id;
  }
  
  private function changeAddress(CRM_Core_BAO_Address $objAddress, $change_to_type_id) {
    //retrieve current active address
    $current = new CRM_Core_BAO_Address();
    $current->contact_id = $objAddress->contact_id;
    $current->location_type_id = $change_to_type_id;
    if ($current->find(TRUE)) {
      //set future address to primary if current active address is primary
      if ($current->is_primary) {
        $objAddress->is_primary = true;
      }
      
      //acrhive the old address
      $this->archiveAddress($current);
      
      //remove the old address
      CRM_Core_BAO_Address::del($current->id);
    }
   
    $objAddress->location_type_id = $change_to_type_id;
    $objAddress->save();    
  } 
  
}

