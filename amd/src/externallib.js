import jQuery from 'jquery';

export const clearSelection = () => {
    jQuery(document).ready(function() {
        jQuery(".custom-select[name*=\"profile_field_gebdat\"]").append(new Option(" ", "-1"));
        jQuery(".custom-select[name*=\"profile_field_gebdat\"]").val("-1");

        jQuery('form').on('submit', function(event) {
            jQuery('select[name*="profile_field_gebdat"]').each(function() {
                if (jQuery(this).find('option:selected[value="-1"]').length > 0) {
                    event.preventDefault();
                    alert("Bitte geben Sie Ihr Geburtsdatum an");
                    return false;
                }
            });
        });
    });
};

export const addPasswordCheck = () => {
    jQuery(document).ready(function() {
        const label = '<div class="col-md-3 col-form-label d-flex pb-0 pr-md-0">' +
            '<label id="id_password_label" class="d-inline word-break " for="id_password">Kennwort wiederholen</label>'+
            '<div class="form-label-addon d-flex align-items-center align-self-start">' +
            '<div class="text-danger" title="Erforderlich">' +
            '<i class="icon fa fa-exclamation-circle text-danger fa-fw " title="Erforderlich" role="img"></i>' +
            '</div></div></div>';
        const additionalPasswordField = '<input type="password" class="form-control" name="password" ' +
            'id="id_passwordConfirm" value="" size="12" maxlength="32" data-initial-value="">';
        jQuery('div[id=\"fitem_id_password\"]').append(label);
        jQuery('div[id=\"fitem_id_password\"]').append(additionalPasswordField);

        jQuery('form').on('submit', function(event) {
            const pass1 = jQuery('#id_password').val();
            const pass2 = jQuery('#id_passwordConfirm').val();
                if (pass1 !== pass2) {
                    event.preventDefault();
                    alert("Die eingegebenen Passwörter stimmen nicht überein!");
                    return false;
                }
        });
    });
};
