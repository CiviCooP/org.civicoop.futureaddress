# Available hooks in this extension

## hook_civicrm_future_address_get_changer

**Description**

This hook is invoked to retrieve the right type of address changer for a location type.
In this hook you can return your own changer which should implement the interface `CRM_Futureaddress_Interface_Changer` or which should be subclass of `CRM_Futureaddress_AddressChanger`

**Spec**

    hook_civicrm_future_address_get_changer(CRM_Core_BAO_LocationType $future_location_type, CRM_Core_BAO_LocationType $active_location_type, CRM_Futureaddress_Interface_Changer &$changer);

**Example**

In the example below we want to disable the creation of an activity on the archiving. We make a subclass of `CRM_Futureaddress_AddressChanger` which has an empty method for archiving.

*In CRM/MyModule/AddressChanger.php:*

    CRM_MyModule_AddressChanger extends CRM_Futureaddress_AddressChanger {

        protected function archiveAddress(CRM_Core_BAO_Address $objAddress) {
            //do nothing so that the address doesn't get archived (it will just dissappear)
        }
    }

*In mymodule.php:*

    mymodule_civicrm_future_address_get_changer(CRM_Core_BAO_LocationType $future_location_type, CRM_Core_BAO_LocationType $active_location_type, CRM_Futureaddress_Interface_Changer &$changer) {
        $changer = new  CRM_MyModule_AddressChanger($future_location_type->id, $active_location_type->id);
    }

## hook_civicrm_future_address_archive_address

**Description**

This hook is invoked upon acrhiving of an address. Default this extension creates an activity containing the data of the old address.
In this hook you can create your own archiving mechanism (e.g. storing the address in a separate database)

**Spec**

    hook_civicrm_future_address_archive_address(CRM_Core_BAO_Address $objAddress);

## hook_civicrm_future_address_activity_parameters

**Description**

This hook is invoked upon creating the archived activity. You can use this hook to set custom parameters for the activity e.g. change the status

**Spec**

    hook_civicrm_future_address_activity_parameters(CRM_Core_BAO_Address $objAddress, &$activityParameters);

