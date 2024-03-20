<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Authentication Plugin: Email Authentication
 *
 * @author Martin Dougiamas
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package auth_email
 */

use core\event\user_created;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');
require_once('classes/message.php');


/**
 * Email authentication plugin.
 */
class auth_plugin_external extends auth_plugin_base {
    /**
     * Constructor.
     * @throws dml_exception
     */
    public function __construct() {
        $this->authtype = 'external';
        $this->config = get_config('auth_external');
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @throws dml_exception
     * @deprecated since Moodle 3.1
     */
    public function auth_plugin_external(): void {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     * @throws dml_exception
     */
    public function user_login($username, $password): bool {
        global $CFG, $DB;
        if ($user = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {
            return validate_internal_user_password($user, $password);
        }
        return false;
    }

    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param object $user User table object  (with system magic quotes)
     * @param string $newpassword Plaintext password (with system magic quotes)
     * @return boolean result
     *
     * @throws dml_exception
     */
    public function user_update_password($user, $newpassword): bool {
        $user = get_complete_user_data('id', $user->id);
        // This will also update the stored hash to the latest algorithm
        // if the existing hash is using an out-of-date algorithm (or the
        // legacy md5 algorithm).
        return update_internal_user_password($user, $newpassword);
    }

    public function can_signup(): bool {
        return true;
    }

    /**
     * Sign up a new user ready for confirmation.
     * Password is passed in plaintext.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     * @throws moodle_exception
     */
    public function user_signup($user, $notify = true): bool {
        // Standard signup, without custom confirmatinurl.
        return $this->user_signup_with_confirmation($user, $notify);
    }

    /**
     * Sign up a new user ready for confirmation.
     *
     * Password is passed in plaintext.
     * A custom confirmationurl could be used.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     * @param string|null $confirmationurl user confirmation URL
     * @return boolean true if everything well ok and $notify is set to true
     * @throws moodle_exception
     * @since Moodle 3.2
     */
    public function user_signup_with_confirmation(object $user, bool $notify = true, string $confirmationurl = null): bool {
        global $CFG, $DB, $SESSION;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        require_once($CFG->dirroot . '/user/lib.php');

        // BEGIN USI Generated Username.
        if (\get_config("auth_external", "generated_username")) {
            $cleanedfirstname = iconv(
                "utf-8",
                "ascii//TRANSLIT",
                str_replace(' ', '', $user->firstname)
            );
            $cleanedlastname = iconv(
                "utf-8",
                "ascii//TRANSLIT",
                str_replace(' ', '', $user->lastname)
            );

            $user->username = \get_config("auth_external", "generated_prefix") .
                strtolower($cleanedlastname . substr($cleanedfirstname, 0, 3));
            global $DB;
            $postfix = 2;
            while ($DB->record_exists("user", ["username" => $user->username])) {
                $user->username = $user->username . $postfix;
                $postfix++;
            }
        }
        // END.

        $plainpassword = $user->password;
        $user->password = hash_internal_user_password($user->password);
        if (empty($user->calendartype)) {
            $user->calendartype = $CFG->calendartype;
        }

        $user->id = user_create_user($user, false, false);

        user_add_password_history($user->id, $plainpassword);
        profile_save_data($user);

        profile_load_custom_fields($user);

        // Setting external profile fields.
        $user->profile_field_external_user = true;
        $user->profile_field_external_user_verified = 0;
        $user->profile_field_external_user_pending = false;

        // Save any custom profile field information.
        profile_save_data($user);

        // Save wantsurl against user's profile, so we can return them there upon confirmation.
        if (!empty($SESSION->wantsurl)) {
            set_user_preference('auth_external_wantsurl', $SESSION->wantsurl, $user);
        }

        // Trigger event.
        user_created::create_from_userid($user->id)->trigger();

        if ($this->is_email_confirmation_enabled()) {
            if (!send_confirmation_email($user, $confirmationurl)) {
                throw new moodle_exception('auth_emailnoemail', 'auth_external');
            }

            if ($notify) {
                global $CFG, $PAGE, $OUTPUT;
                $emailconfirm = get_string('emailconfirm');
                $PAGE->navbar->add($emailconfirm);
                $PAGE->set_title($emailconfirm);
                $PAGE->set_heading($PAGE->course->fullname);
                echo $OUTPUT->header();
                notice(get_string('emailconfirmsent', '', $user->email), "$CFG->wwwroot/index.php");
            } else {
                return true;
            }
        }
        $DB->set_field("user", "confirmed", 1, ["id" => $user->id]);

        send_message($user->id, null, " " .  $user->username);

        $url = new \moodle_url("/login/index.php", []);
        redirect($url, '', 5);
        return true;
    }

    /**
     * Returns true if plugin allows confirming of new users.
     *
     * @return bool
     */
    public function can_confirm(): bool {
        return true;
    }

    /**
     * Confirm the new user as registered.
     *
     * @param string $username
     * @param string $confirmsecret
     * @throws dml_exception|coding_exception
     */
    public function user_confirm($username, $confirmsecret): int {
        global $DB, $SESSION;
        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->auth != $this->authtype) {
                return AUTH_CONFIRM_ERROR;
            } else if ($user->secret === $confirmsecret && $user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else if ($user->secret === $confirmsecret) {   // They have provided the secret key to get in.
                $DB->set_field("user", "confirmed", 1, ["id" => $user->id]);

                if ($wantsurl = get_user_preferences('auth_email_wantsurl', false, $user)) {
                    // Ensure user gets returned to page they were trying to access before signing up.
                    $SESSION->wantsurl = $wantsurl;
                    unset_user_preference('auth_email_wantsurl', $user);
                }

                return AUTH_CONFIRM_OK;
            }
        } else {
            return AUTH_CONFIRM_ERROR;
        }
        return 0;
    }

    public function prevent_local_passwords(): bool {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    public function is_internal(): bool {
        return true;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    public function can_change_password(): bool {
        return true;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    public function change_password_url(): ?moodle_url {
        return null; // Use default internal method.
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    public function can_reset_password(): bool {
        return true;
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    public function can_be_manually_set(): bool {
        return true;
    }

    /**
     * Returns whether or not the captcha element is enabled.
     * @return bool
     * @throws dml_exception
     */
    public function is_captcha_enabled(): bool {
        return get_config("auth_{$this->authtype}", 'recaptcha');
    }

    /**
     * @throws dml_exception
     */
    public function is_email_confirmation_enabled() {
        return get_config("auth_{$this->authtype}", 'email_confirm');
    }

    public function signup_form() {
        global $CFG;

        require_once($CFG->dirroot . '/auth/external/signup_form.php');
        return new login_signup_form(
            null,
            null,
            'post',
            '',
            ['autocomplete' => 'on']
        );
    }
}
