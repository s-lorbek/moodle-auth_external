import jQuery from 'jquery';

export const clearSelection = () => {
    jQuery(document).ready(function() {
        jQuery('#fitem_id_profile_field_gebdat .custom-select').each(function() {
            jQuery(this).val('');
        });
    });
};