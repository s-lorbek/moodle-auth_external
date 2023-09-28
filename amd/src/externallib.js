import jQuery from 'jquery';

export const clearSelection = () => {
    jQuery(document).ready(function() {
        let birthdateField = jQuery('.custom-select[name*="profile_field_gebdat"]');
        birthdateField.append(new Option(" ", "-1"));
        birthdateField.val("-1");

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
            '<label id="id_password_label2" class="d-inline word-break " for="id_passwordConfirm">' +
            'Kennwort wiederholen</label>' +
            '<div class="form-label-addon d-flex align-items-center align-self-start">' +
            '<div class="text-danger" title="Erforderlich">' +
            '<i class="icon fa fa-exclamation-circle text-danger fa-fw " title="Erforderlich" role="img"></i>' +
            '</div></div></div>';
        const additionalPasswordField = '<input type="password" class="form-control" name="passwordConfirm" ' +
            'id="id_passwordConfirm" value="" size="12" maxlength="32" data-initial-value="">';
        let passwordDiv = jQuery('div[id="fitem_id_password"]');
        passwordDiv.append(label);
        passwordDiv.append(additionalPasswordField);

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

export const checkUniversityAffiliation = () => {
    jQuery('form').on('submit', function(event) {
        const email = jQuery('#id_email').val();
        var subdomain = "univie";
        var regexPattern = new RegExp(subdomain + "\\.", "i");
        if (regexPattern.test(email)) {
            event.preventDefault();
            alert("Bitte melden Sie sich mit Ihrer Universitäts-Mailadresse über Shibboleth an!");
            return false;
        }
    });
};
