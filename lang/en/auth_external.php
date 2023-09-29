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
 * Strings for component 'auth_email', language 'en'.
 *
 * @package   auth_external
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_emaildescription'] = '<p>External self-registration enables a user to create their own account via a \'Create new account\' button on the login page. The user then receives an email containing a secure link to a page where they can confirm their account. Future logins just check the username and password against the stored values in the Moodle database.</p><p>Note: In addition to enabling the plugin, email-based self-registration must also be selected from the self registration drop-down menu on the \'Manage authentication\' page.</p>';
$string['auth_emailnoemail'] = 'Tried to send you an email but failed!';
$string['auth_emailrecaptcha'] = 'Adds a visual/audio confirmation form element to the sign-up page for email self-registering users. This protects your site against spammers and contributes to a worthwhile cause. See https://www.google.com/recaptcha for more details.';
$string['auth_emailrecaptcha_key'] = 'Enable reCAPTCHA element';
$string['auth_emailsettings'] = 'Settings';

$string['auth_email_confirm'] = 'Enable email confirmation';
$string['auth_email'] = 'Enables email confirmation. If disabled external users must not confirm their email after signup.';

$string['pluginname'] = 'External User';
$string['privacy:metadata'] = 'External User self-registration authentication plugin does not store any personal data.';

$string['auth_usernameconfirm'] = '<p>External self-registration enables a user to create their own account via a \'Create new account\' button on the login page. The user then receives an email containing a secure link to a page where they can confirm their account. Future logins just check the username and password against the stored values in the Moodle database.</p><p>Note: In addition to enabling the plugin, email-based self-registration must also be selected from the self registration drop-down menu on the \'Manage authentication\' page.</p>';

$string['auth_email_subject_confirm'] = "Signup E-Mail Subject";
$string['auth_email_body_confirm'] = "Signup E-Mail Body";
$string['auth_email_subject'] = "Subject of the notification mail a user receives for information about their generated username";
$string['auth_email_body'] = "Be sure to include {{username}} in your template, as this is the placeholder for the users username. Other options are {{firstname}} and {{lastname}}";
$string['auth_generated_user'] = "Generated Username";
$string['auth_generated_user_desc'] = "Usernames are generated instead of entered during signup";
$string['auth_username_prefix'] = "Generated Username Prefix";
$string['auth_username_prefix_desc'] = "String to be prefixed to a generated username";

$string['auth_label'] = 'Password (again)';
$string['auth_hint'] = 'Mandatory';
$string['auth_password_alert'] = 'The passwords entered do not match!';

$string['auth_university_alert'] = 'Please log in with your university mail address via your eduID!';

$string['auth_birthday_alert'] = "Please enter your birthdate!";


