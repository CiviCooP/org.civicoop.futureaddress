<?php

/**
 * FutureAddress.Change API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_future_address_Change_spec(&$spec) {
}

/**
 * FutureAddress.Change API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_future_address_Change($params) {
  $changer = new CRM_AddressChanger_Changer();
  $changer->checkAndChange();

  return civicrm_api3_create_success(array('message' => 'Updated '.$changer->getChangeCount().' addresses. Failed to update '.$changer->getFailureCount().' addresses'));
}

