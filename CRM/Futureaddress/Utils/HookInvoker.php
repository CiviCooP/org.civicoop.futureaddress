<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Futureaddress_Utils_HookInvoker {

  private static $singleton;

  private function __construct() {

  }

  /**
   * This hook is invoked upon creating the archived activity.
   *
   * You can use this hook to set custom parameters for the activity
   * e.g. change the status.
   *
   * @param $addressData
   * @param $activityParams
   * @return mixed
   */
  public function hook_civicrm_address_change_activity_parameters($addressData, &$activityParams) {
    return $this->invoke('civicrm_address_change_activity_parameters', 2, $addressData, $activityParams);
  }

  /**
   * This hook is invoked to retrieve the right type of address changer for a location type.
   *
   * In this hook you can return your own changer which should implement
   * the interface CRM_AddressChanger_Interface_Changer or which should
   * be subclass of CRM_AddressChanger_Model_Changer.
   *
   * @param CRM_Core_BAO_LocationType $location_type
   * @return array of CRM_AddressChanger_Interface_Changer
   *   This hook should return an array with the key the name of the
   *   location type and the value the Changer class.
   */
  public function hook_civicrm_future_address_get_changer(CRM_Core_BAO_LocationType $location_type) {
    return $this->invoke('civicrm_future_address_get_changer', 1, $location_type);
  }

  /**
   * This hook is invoked upon acrhiving of an address.
   *
   * Default this extension creates an activity containing the data
   * of the old address. In this hook you can create your own
   * archiving mechanism (e.g. storing the address in a separate database).
   *
   * @param CRM_Core_BAO_Address $objectAddress
   * @return void
   */
  public function hook_civicrm_archive_address(CRM_Core_BAO_Address $objectAddress) {
    return $this->invoke('civicrm_archive_address', 1, $objectAddress);
  }

  /**
   * @return \CRM_Futureaddress_Utils_HookInvoker
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Futureaddress_Utils_HookInvoker();
    }
    return self::$singleton;
  }

  private function invoke($fnSuffix, $numParams, &$arg1 = null, &$arg2 = null, &$arg3 = null, &$arg4 = null, &$arg5 = null) {
    $hook =  CRM_Utils_Hook::singleton();
    $civiVersion = CRM_Core_BAO_Domain::version();

    if (version_compare($civiVersion, '4.5', '<')) {
      //in CiviCRM 4.4 the invoke function has 5 arguments maximum
      return $hook->invoke($numParams, $arg1, $arg2, $arg3, $arg4, $arg5, $fnSuffix);
    } else {
      //in CiviCRM 4.5 and later the invoke function has 6 arguments
      return $hook->invoke($numParams, $arg1, $arg2, $arg3, $arg4, $arg5, CRM_Utils_Hook::$_nullObject, $fnSuffix);
    }
  }

}