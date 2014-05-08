<?php

/*
 * This class retrieves location type to be matched and changed
 * And starts the changing process
 */

class CRM_Futureaddress_Changer {

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
  
  protected function getChanger(CRM_Core_BAO_LocationType $future_location_type, CRM_Core_BAO_LocationType $active_location_type) {
    return new CRM_Futureaddress_AddressChanger($future_location_type->id, $active_location_type->id);
  }
  
  /**
   * Returns an array with the location type change classes
   * 
   */
  private function retrieveLocationTypes() {
    $sql = "SELECT * FROM `civicrm_location_type` WHERE `name` LIKE 'new_%'";
    $return = array();
    $future_loc_type = CRM_Core_DAO::executeQuery($sql, array(), TRUE, 'CRM_Core_BAO_LocationType');
    while ($future_loc_type->fetch()) {
      $active_loc_type = new CRM_Core_BAO_LocationType();
      $active_loc_type->name = str_replace('new_', '', $future_loc_type->name);
      if ($active_loc_type->find(TRUE)) {
        $changer = $this->getChanger($future_loc_type, $active_loc_type);
        if ($changer instanceof CRM_Futureaddress_AddressChanger) {
          $return[] = $changer;
        }
      }
    }
    return $return;
  }

}
