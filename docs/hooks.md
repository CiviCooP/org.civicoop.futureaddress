# Available hooks in this extension

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

