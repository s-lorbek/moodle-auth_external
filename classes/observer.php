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
 * Collection of observer callback functions and handler
 *
 * @package    auth_external
 * @author     Stephan Lorbek
 * @copyright  2024 Stephan Lorbek
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_external;

use core\event\base;
use dml_exception;

class observer {
    /**
     * @throws dml_exception
     */
    public static function user_update_external_fields(base $event): void {
        global $DB;
        $userid = $event->relateduserid;
        $user = $DB->get_record("user", ['id' => $userid]);

        if ($user->auth == "external" && $event->userid != 0) {
            profile_load_data($user);
            $user->profile_field_external_user = true;
            $user->profile_field_external_user_verified = true;
            $user->profile_field_external_user_pending = false;
            profile_save_data($user);
        }
    }
}
