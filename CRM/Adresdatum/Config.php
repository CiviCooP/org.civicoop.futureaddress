<?php

/* 
 * Class which holds information on configuration for this extension
 */

class CRM_Adresdatum_Config {
  
  protected static $_instance;
  
  protected $custom_group;
  protected $change_date_field;
  protected $process_date_field;
  
  protected function __construct() {
    $this->custom_group = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'address_date'));
    $this->change_date_field = civicrm_api3('CustomField', 'getsingle', array('name' => 'Change_date', 'custom_group_id' => $this->custom_group['id']));
    $this->process_date_field = civicrm_api3('CustomField', 'getsingle', array('name' => 'Process_date', 'custom_group_id' => $this->custom_group['id']));
  }
  
  public static function singleton() {
    if (!self::$_instance) {
      self::$_instance = new CRM_Adresdatum_Config();
    }
    return self::$_instance;
  }
  
  public function getCustomGroup() {
    return $this->custom_group;
  }
  
  public function getChangeDateField() {
    return $this->change_date_field;
  }
  
  public function getProcessDateField() {
    return $this->process_date_field;
  }

}

