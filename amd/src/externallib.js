import jQuery from 'jquery';

export const clearSelection = (notification) => {
    jQuery(document).ready(function () {
        let birthdateField = jQuery('.custom-select[name*="profile_field_gebdat"]');
        birthdateField.append(new Option(" ", "-1"));
        birthdateField.val("-1");

        jQuery('form').on('submit', function (event) {
            if (event.originalEvent.submitter && event.originalEvent.submitter.name === "submitbutton") {
                jQuery('select[name*="profile_field_gebdat"]').each(function () {
                    if (jQuery(this).find('option:selected[value="-1"]').length > 0) {
                        var error = document.createElement("div");
                        error.classList.add("form-control-feedback");
                        error.classList.add("invalid-feedback");
                        error.id = "birthday-check-failed";
                        error.style.display = "block";
                        error.innerHTML = notification;

                        const cond = document.getElementById('birthday-check-failed') || false;
                        if (!cond) {
                            document.getElementById("id_profile_field_gebdat").appendChild(error);
                        }
                        event.preventDefault();
                        return false;
                    }
                });
            }
        });
    });
};

export const addPasswordCheck = (content, notification) => {
    jQuery(document).ready(function () {
        let passwordDiv = jQuery('div[id="fitem_id_password"]');
        passwordDiv.append(content);

        jQuery('form').on('submit', function (event) {
            if (event.originalEvent.submitter && event.originalEvent.submitter.name === "submitbutton") {
                const pass1 = jQuery('#id_password').val();
                const pass2 = jQuery('#id_passwordConfirm').val();
                if (pass1 !== pass2) {
                    var error = document.createElement("div");
                    error.classList.add("form-control-feedback");
                    error.classList.add("invalid-feedback");
                    error.id = "pass-check-failed";
                    error.style.display = "block";
                    error.innerHTML = notification;

                    const cond = document.getElementById('pass-check-failed') || false;
                    if (!cond) {
                        document.getElementById("fitem_id_password").appendChild(error);
                    }
                    event.preventDefault();
                    return false;
                }
            }
        });
    });
};

export const checkUniversityAffiliation = (notification, subdomains) => {
    jQuery('form').on('submit', function (event) {
        if (event.originalEvent.submitter && event.originalEvent.submitter.name === "submitbutton") {
            const email = jQuery('#id_email').val();
            const parts = subdomains.split(",");

            parts.forEach(function (subdomain) {
                var regexPattern = new RegExp(subdomain, "i");
                if (regexPattern.test(email)) {
                    var error = document.createElement("div");
                    error.classList.add("form-control-feedback");
                    error.classList.add("invalid-feedback");
                    error.id = "affiliation-check-failed";
                    error.style.display = "block";
                    error.innerHTML = notification;

                    const cond = document.getElementById('affiliation-check-failed') || false;
                    if (!cond) {
                        document.getElementById("fitem_id_email").appendChild(error);
                    }
                    event.preventDefault();
                    return false;
                }
            });
        }
    });
};
