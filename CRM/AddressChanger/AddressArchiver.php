<?php

/* 
 * This class will save an address into an activity for change address
 */

class CRM_AddressChanger_AddressArchiver {
  
  protected $activity_type_id;
  
  protected static $_instance;
  
  protected function __construct() {
    $config = CRM_AddressChanger_Config::singleton();
    $activity_type = civicrm_api3('OptionValue', 'getsingle', array('name' => 'address_change', 'option_group_id' => $config->getActivityTypeOptionGroupId()));
    $this->activity_type_id = $activity_type['value'];
  }
  
  public static function singleton() {
    if (!self::$_instance) {
      self::$_instance = new CRM_AddressChanger_AddressArchiver();
    }
    return self::$_instance;
  }
  
  /**
   * Archive an address into an activity
   * 
   * @param array $addressData
   */
  public function archiveIntoAnActivityAddress($addressData, $subject = false) {
       
    $activityParams = array();
    $activityParams['activity_type_id'] = $this->activity_type_id;    
    $activityParams['target_contact_id'] = $addressData['contact_id'];
    if (!empty($subject)) {
      $activityParams['subject'] = $subject;
    } else {
      $activityParams['subject'] = ts('Address change');
    }
    
    $activityParams['status_id'] = 2; //completed
    
    
    $this->changeActivityParameters($addressData, $activityParams);
    // create the activity
    
    civicrm_api3('Activity', 'create', $activityParams);
  }
  
    /**
   * Function to be overriden by child classes to set specific activity parameters
   * 
   * @param array $addressData
   * @param array $activityParams
   */
  protected function changeActivityParameters($addressData, &$activityParams) {
    $history = CRM_AddressChanger_AddressHistory::singleton();
    $history->generateActivityParams($addressData, $activityParams);
    
    $hooks = CRM_Utils_Hook::singleton();
    $hooks->invoke(2,
      $addressData, $activityParams, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject,
      'civicrm_address_change_activity_parameters'
      );
  }
  
}
