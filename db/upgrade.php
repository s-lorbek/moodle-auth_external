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
 * No authentication plugin upgrade code
 *
 * @package    auth_external
 * @copyright  2017 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade auth_email.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_auth_external_upgrade($oldversion) {
    global $CFG, $DB;

    // Automatically generated Moodle v3.6.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.7.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.9.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.0.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2026021600) {
        $sql = "SELECT u.id, u.timecreated
                  FROM {user} u
             LEFT JOIN {user_preferences} up ON (u.id = up.userid AND up.name = :prefname)
                 WHERE u.auth = :auth
                   AND u.deleted = 0
                   AND up.id IS NULL";

        $params = [
            'prefname' => 'auth_external_passwordupdatetime',
            'auth'     => 'external',
        ];
        $users = $DB->get_recordset_sql($sql, $params);
        foreach ($users as $user) {
            set_user_preference('auth_external_passwordupdatetime', $user->timecreated, $user->id);
        }
        $users->close();
        upgrade_plugin_savepoint(true, 2026021600, 'auth', 'external');
    }

    return true;
}
