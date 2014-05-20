<?php

/*
 * This class retrieves location type to be matched and changed
 * And starts the changing process
 */

class CRM_AddressChanger_Changer {

  protected $changeCount = 0;
  protected $failureCount = 0;

  public function __construct() {
    
  }
  
  public function checkAndChange() {
    $this->changeCount = 0;
    $this->failureCount = 0;
    $changers = $this->retrieveLocationTypes();
    foreach($changers as $changer) {
      $changer->checkAndChange();
      $this->changeCount += $changer->getChangeCount();
      $this->failureCount += $changer->getFailureCount();
    }
  }

  public function getChangeCount() {
    return $this->changeCount;
  }

  public function getFailureCount() {
    return $this->failureCount;
  }
  
  protected function getChanger(CRM_Core_BAO_LocationType $location_type) {
    $hooks = CRM_Utils_Hook::singleton();
    $return = $hooks->invoke(1, $location_type, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, 'civicrm_future_address_get_changer');
    if (isset($return[$location_type->name]) && $return[$location_type->name] instanceof CRM_AddressChanger_Interface_Changer) {
      return $return[$location_type->name];
    }
    return false;
  }
  
  /**
   * Returns an array with the location type change classes
   * 
   */
  private function retrieveLocationTypes() {
    $sql = "SELECT * FROM `civicrm_location_type` WHERE `is_active` = '1'";
    $return = array();
    $loc_type = CRM_Core_DAO::executeQuery($sql, array(), TRUE, 'CRM_Core_BAO_LocationType');
    while ($loc_type->fetch()) {
      $changer = $this->getChanger($loc_type);
      if ($changer instanceof CRM_AddressChanger_Interface_Changer) {        
        $return[] = $changer;
      }
      
    }
    return $return;
  }

}
