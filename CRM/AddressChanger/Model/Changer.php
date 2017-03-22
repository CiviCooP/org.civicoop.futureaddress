<?php

/*
 * This function is a base class for address changer, such as temporarily address changes
 */

abstract class CRM_AddressChanger_Model_Changer implements CRM_AddressChanger_Interface_Changer {

  protected $changeCount = 0;
  protected $failureCount = 0;
  
  protected $active_locationtype_id;
  
  public function __construct($active_locationtype_id) {
    $this->active_locationtype_id = $active_locationtype_id;
  }
  
  /**
   * returns the address bao with all the address whil should be changed
   * @return CRM_Core_BAO_Address
   */
  abstract protected function findAddressesToChange();

  public function getChangeCount() {
    return $this->changeCount;
  }

  public function getFailureCount() {
    return $this->failureCount;
  }
  
  /**
   * Retrieve all addresses which should be changed
   * 
   */
  public function checkAndChange() {
    $this->changeCount = 0;
    $this->failureCount = 0;
    
    $config = CRM_AddressChanger_Config::singleton();
    $cgroup = $config->getCustomGroup();
    $processField = $config->getProcessDateField();
  
    //retrieve all addresses which should be changed
    $dao = $this->findAddressesToChange();
    while ($dao->fetch()) {
      try {
        $this->changeAddress($dao, $this->active_locationtype_id);
        
        //update process field
        $update = "UPDATE `".$cgroup['table_name']."` SET `".$processField['column_name']."` = CURDATE() WHERE `id` = '".$dao->rid."'";
        CRM_Core_DAO::executeQuery($update, array(), false); //do not abort on query error
        
        $this->changeCount ++;
      } catch (Exception $e) {
        $this->failureCount ++;    
        throw $e;
      }
    }
  }
  
  /**
   * Archive an address
   * 
   * Default this will create an activity with the address data
   * 
   * @param type $objAddress
   */
  protected function archiveAddress(CRM_Core_BAO_Address $objAddress) {   
    
    $data = array();
    CRM_Core_DAO::storeValues($objAddress, $data);
    $archiver = $this->getArchiver();
    $archiver->archiveIntoAnActivityAddress($data);
    
    CRM_Futureaddress_Utils_HookInvoker::singleton()->hook_civicrm_archive_address($objAddress);
  }
  
  protected function getArchiver() {
    $archiver = CRM_AddressChanger_AddressArchiver::singleton();
    return $archiver;
  }
  
  /**
   * removes the old address and the address to the new location type id
   * @param CRM_Core_BAO_Address $objAddress
   * @param type $change_to_type_id
   */
  protected function changeAddress(CRM_Core_BAO_Address $objAddress, $change_to_locationtype_id) {
    //retrieve current active address
    $current = new CRM_Core_BAO_Address();
    $current->contact_id = $objAddress->contact_id;
    $current->location_type_id = $change_to_locationtype_id;
    if ($current->find(TRUE)) {     
      //acrhive the old address
      $this->archiveAddress($current);
      
      //remove the old address
      CRM_Core_BAO_Address::del($current->id);
    }

    // load the new address
    $params = array();
    $params['id'] = $objAddress->id;
    $params = civicrm_api3('Address', 'getsingle', $params);

    // set the new location type and set the future address to primary if current active address is primary
    $params['location_type_id'] = $change_to_locationtype_id;
    if ($current->is_primary) {
      $params['is_primary']= '1';
    }
    
    civicrm_api3('Address', 'create', $params);
  } 

}
