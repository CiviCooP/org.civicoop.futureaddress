<?php

/* 
 * Factory class to retrieve the changer
 * 
 */

class CRM_AddressChanger_Factory {
  
  /**
   * Returns the changer when found or false when not found
   * 
   * @param CRM_Core_BAO_LocationType $location_type
   * @return false|CRM_AddressChanger_Interface_Changer 
   */
  public static function getChanger(CRM_Core_BAO_LocationType $location_type) {
    $changer = false;
    if (stripos($location_type->name, "new_") === 0) {
    //location type is new
      $changer = CRM_AddressChanger_Factory::getNewAddressChanger($location_type);
    } elseif (stripos($location_type->name, "temp_") === 0) {
      //location type is temp
      $changer = CRM_AddressChanger_Factory::getTempAddressChanger($location_type);
    }
    return $changer;
  }
  
  private static function getNewAddressChanger(CRM_Core_BAO_LocationType $loc_type) {
    $active_loc_type = new CRM_Core_BAO_LocationType();
    $active_loc_type->name = str_replace('new_', '', $loc_type->name);
    if ($active_loc_type->find(TRUE)) {
      return new CRM_Futureaddress_AddressChanger($loc_type->id, $active_loc_type->id);
    }
    return false;
  }
  
  private static function getTempAddressChanger(CRM_Core_BAO_LocationType $loc_type) {
    $active_loc_type = new CRM_Core_BAO_LocationType();
    $active_loc_type->name = str_replace('temp_', '', $loc_type->name);
    if (!$active_loc_type->find(TRUE)) {
      return false;
    }
    $future_loc_type = new CRM_Core_BAO_LocationType();
    $future_loc_type->name = 'new_'.$active_loc_type->name;
    if (!$future_loc_type->find(TRUE)) {
      return false;
    }
    
    return new CRM_Temporarilyaddress_AddressChanger($loc_type->id, $future_loc_type->id, $active_loc_type->id);
  }
  
}
