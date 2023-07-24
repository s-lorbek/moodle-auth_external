import jQuery from 'jquery';

export const clearSelection = () => {
    jQuery(document).ready(function() {
        jQuery(".custom-select[name*=\"profile_field_gebdat\"]").append(new Option(" ", "-1"));
        jQuery(".custom-select[name*=\"profile_field_gebdat\"]").val("-1");

        jQuery('form').on('submit', function(event) {
            $('select[name*="profile_field_gebdat"]').each(function() {
                if ($(this).find('option:selected[value="-1"]').length > 0) {
                    event.preventDefault();
                    alert("Bitte geben Sie Ihr Geburtsdatum an");
                    return false;
                }
            });
        });
    });
};

