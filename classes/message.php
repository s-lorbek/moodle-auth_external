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
 *
 * @package   auth_external_users
 * @copyright 2023 Stephan Lorbek
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Load Moodle's configuration settings
require_once(dirname(__FILE__) . '/../../../config.php');

// Load Moodle's message API
require_once($CFG->dirroot . '/message/lib.php');

/**
 * @throws dml_exception
 */
function send_message($recipientid, $senderid, $comment) {
    /*global $DB;

    $recipient = $DB->get_record('user', array("id" => $recipientid));
    $messageid = message_post_message($senderid, $recipient, $comment, 0);
    return $messageid;*/

    global $DB, $CFG;
    $recipient = $DB->get_record('user', ["id" => $recipientid]);
    $noreply = new stdClass();
    $noreply->firstname = $CFG->supportname;
    $noreply->lastname = '';
    $noreply->username = 'usiadmin';
    $noreply->email = $CFG->noreplyaddress;
    $noreply->maildisplay = 2;
    $noreply->alternatename = "";
    $noreply->firstnamephonetic = "";
    $noreply->lastnamephonetic = "";
    $noreply->middlename = "";

    $subject = \get_config("auth_external", "email_subject_confirm");
    $content = \get_config("auth_external", "email_body_confirm");

    $content = str_replace("{{username}}", $recipient->username, $content);
    $content = str_replace("{{firstname}}", $recipient->firstname, $content);
    $content = str_replace("{{lastname}}", $recipient->lastname, $content);


    $messageid = email_to_user(
        $recipient,
        core_user::get_noreply_user(),
        $subject,
        html_to_text($content),
        $content,
        '',
        '',
        false
    );
    return $messageid;
}
