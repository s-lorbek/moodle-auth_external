import jQuery from 'jquery';

export const clearSelection = (alertmsg) => {
    jQuery(document).ready(function() {
        let birthdateField = jQuery('.custom-select[name*="profile_field_gebdat"]');
        birthdateField.append(new Option(" ", "-1"));
        birthdateField.val("-1");

        jQuery('form').on('submit', function(event) {
            jQuery('select[name*="profile_field_gebdat"]').each(function() {
                if (jQuery(this).find('option:selected[value="-1"]').length > 0) {
                    event.preventDefault();
                    alert(alertmsg);
                    return false;
                }
            });
        });
    });
};

export const addPasswordCheck = (content, alertmsg) => {
    jQuery(document).ready(function() {
        let passwordDiv = jQuery('div[id="fitem_id_password"]');
        passwordDiv.append(content);

        jQuery('form').on('submit', function(event) {
            const pass1 = jQuery('#id_password').val();
            const pass2 = jQuery('#id_passwordConfirm').val();
                if (pass1 !== pass2) {
                    event.preventDefault();
                    alert(alertmsg);
                    return false;
                }
        });
    });
};

export const checkUniversityAffiliation = (alertmsg, subdomains) => {
    jQuery('form').on('submit', function(event) {
        const email = jQuery('#id_email').val();
        const parts = subdomains.split(",");

        // eslint-disable-next-line consistent-return
        parts.forEach(function(subdomain) {
            var regexPattern = new RegExp(subdomain, "i");
            if (regexPattern.test(email)) {
                // eslint-disable-next-line no-alert
                if (!confirm(alertmsg)) {
                    event.preventDefault();
                    return false;
                }
            }
        });
    });
};
