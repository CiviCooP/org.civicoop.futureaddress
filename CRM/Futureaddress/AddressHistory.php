<?php

/* 
 * This class archives an addres under an activity
 */

class CRM_Futureaddrdess_AddressHistory {
  
  protected static $_instance;
  
  protected $fields = array();
  
  protected $custom_location_type_label_field;
  
  protected function __construct() {
    $group = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Address_change'));
    $gid = $group['id'];
    
    $loc_custom_id = $this->getCustomFieldId($gid, 'Location_type_label');
    $this->custom_location_type_label_field = 'custom_'.$loc_custom_id;
    
    $this->addField('Street_address', 'street_address', $gid);
    $this->addField('Street_number', 'street_number', $gid);
    $this->addField('Street_number_suffix', 'street_number_suffix', $gid);
    $this->addField('Street_number_predirectional', 'street_number_predirectional', $gid);
    $this->addField('Street_name', 'street_name', $gid);
    $this->addField('Street_type', 'street_type', $gid);
    $this->addField('Street_number_postdirectional', 'street_number_postdirectional', $gid);
    $this->addField('Street_unit', 'street_unit', $gid);
    $this->addField('Supplemental_address_1', 'supplemental_address_1', $gid);
    $this->addField('Supplemental_address_2', 'supplemental_address_2', $gid);
    $this->addField('Supplemental_address_3', 'supplemental_address_3', $gid);
    $this->addField('City', 'city', $gid);
    $this->addField('Country_ID', 'country_id', $gid);
    $this->addField('State_province_ID', 'state_province_id', $gid);
    $this->addField('Postal_code_suffix', 'postal_code_suffix', $gid);
    $this->addField('Postal_code', 'postal_code', $gid);
    $this->addField('County_ID', 'county_id', $gid);
    $this->addField('Billing', 'is_billing', $gid);
    $this->addField('Is_primary', 'is_primary', $gid);
    $this->addField('Location_type_ID', 'location_type_id', $gid);
  } 
  
  public static function singleton() {
    if (!self::$_instance) {
      self::$_instance = new CRM_Futureaddrdess_AddressHistory();
    }
    return self::$_instance;
  }
  
  public function generateActivityParams(CRM_Core_BAO_Address $address, &$params) {
    foreach($this->fields as $custom => $address_field) {
      $val = $address->$address_field;
      if ($val) {
        $params[$custom] = $val;
      }
    }
    
    //get location type label
    $location_type = new CRM_Core_BAO_LocationType();
    $location_type->id = $address->location_type_id;
    if ($location_type->find(true)) {
      $params[$this->custom_location_type_label_field] = $location_type->display_name;
    }
  }
  
  private function addField($custom_name, $address_field, $gid) {
    $fid = $this->getCustomFieldId($gid, $custom_name);
    $this->fields['custom_'.$fid] = $address_field;
  }
  
  
  private function getCustomFieldId($gid, $field_name) {
    $field = civicrm_api3('CustomField', 'getsingle', array('name' => $field_name, 'custom_group_id' => $gid));
    return $field['id'];
  }
  
}

