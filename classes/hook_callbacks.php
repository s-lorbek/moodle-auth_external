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

namespace auth_external;

use context_system;
use moodle_url;

/**
 * Class hook_callbacks
 *
 * @package    auth_external
 * @copyright  2025 Stephan Lorbek <stephan.lorbek@uni-graz.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    public static function onload(\core\hook\output\before_http_headers $hook): void {
        global $PAGE;
        if (strpos($PAGE->url, "/login/signup.php")) {
            global $OUTPUT;
            $passwordfieldstrings = ['label' => get_string('auth_label', 'auth_external'),
                'auth_hint' => get_string('auth_hint', 'auth_external')];
            $additionalpasswordfield = $OUTPUT->render_from_template("auth_external/passfield", $passwordfieldstrings);
            $PAGE->requires->js_call_amd(
                'auth_external/externallib',
                "addPasswordCheck",
                [$additionalpasswordfield,
                get_string('auth_password_alert', 'auth_external')]
            );

            $PAGE->requires->js_call_amd(
                'auth_external/externallib',
                "clearSelection",
                [get_string('auth_birthday_alert', 'auth_external')]
            );

            if (get_config("auth_external", "subdomains") != "") {
                $PAGE->requires->js_call_amd(
                    'auth_external/externallib',
                    "checkUniversityAffiliation",
                    [get_config("auth_external", "affiliation_error"),
                    get_config("auth_external", "subdomains")]
                );
            }
        }
    }
}
