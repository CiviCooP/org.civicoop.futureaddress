<?php

/* 
 * This class checks for addresses of type (future) to be changed to type (current)
 */

class CRM_Futureaddress_AddressChanger {
  
  protected $future_type_id;
  
  protected $active_type_id;
  
  protected $changeCount = 0;
  
  protected $failureCount = 0;
  
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
        $this->changeCount ++;
      } catch (Exception $e) {
        throw $e;
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
  private function archiveAddress($objAddress) {
    return ; //@todo remove this return statement
    throw new Exception('to be implemented');
    
    $activity_type_id = $this->getActivityTypeId();    
    $activityParams = array();
    
    $this->changeActivityParameters($objAddress, $activityParams);
    //create hook so that external module could change the activity created
    hook_call();
    // create the activity
    civicrm_api3('activity', 'create', $activityParams);
  }
  
  /**
   * Function to be overriden by child classes to set specific activity parameters
   * 
   * @param object $objAddress
   * @param array $activityParams
   */
  protected function changeActivityParameters($objAddress, &$activityParams) {
    //do nothing in this class
  }
  
  /**
   * Returns the activity type id for saving old addresses
   * 
   */
  protected function getActivityTypeId() {
    throw new Exception('to be implemented');
  }
  
  private function changeAddress(CRM_Core_BAO_Address $objAddress, $change_to_type_id) {
    //retrieve current active address
    $current = new CRM_Core_BAO_Address();
    $current->contact_id = $objAddress->contact_id;
    $current->location_type_id = $change_to_type_id;
    if ($current->find(TRUE)) {
      $this->archiveAddress($current);
      //remove the old address
      CRM_Core_BAO_Address::del($current->id);
    }
   
    /*$addressParams = array();
    //CRM_Core_DAO::storeValues($objAddress, $addressParams);
    
    $addressParams['id'] = $objAddress->id;
    $addressParams['location_type_id'] = $change_to_type_id;
    
    CRM_Core_BAO_Address::add($addressParams, false);*/
    $objAddress->location_type_id = $change_to_type_id;
    $objAddress->save();
  } 
  
}

