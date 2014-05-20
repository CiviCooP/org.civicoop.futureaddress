{literal}
<script type="text/javascript">
cj(function() {
    var addressBlocks = cj('.crm-edit-address-block');
    addressBlocks.each(function(index, item) {
        var blockId = cj(item).attr('id').replace('Address_Block_', '');    

        var blockId = '{/literal}{$blockId}{literal}';
        var custom_group_name = '{/literal}{$custom_group_name}{literal}';
        var custom_group_id = '{/literal}{$custom_group_id}{literal}';
        var location_type_ids = {/literal}{$location_type_ids}{literal}

        cj('#address_'+blockId+'_location_type_id').change(function(e) {
            var value = cj('#address_'+blockId+'_location_type_id').val();
            var index = location_type_ids.indexOf(value);
            if (index >= 0) {
                //valid future address field
                //show date fields
                cj('#'+custom_group_name+'_'+custom_group_id+'_'+blockId).show();
            } else {
                cj('#'+custom_group_name+'_'+custom_group_id+'_'+blockId).hide();
            }
        });

        cj('#address_'+blockId+'_location_type_id').trigger('change');
    });

});

</script>
{/literal}