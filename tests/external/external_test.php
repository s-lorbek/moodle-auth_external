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

namespace auth_external\external;

use auth_external_external;
use externallib_advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * External auth_external API tests.
 *
 * @package    auth_external
 * @category   external
 * @copyright  2026 Stephan Lorbek <stephan.lorbek@uni-graz.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \auth_external_external
 */
final class external_test extends externallib_advanced_testcase {
    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->resetAfterTest(true);

        $CFG->registerauth = 'external';
        // Email confirmation is enabled so the signup path returns cleanly instead of redirecting.
        set_config('email_confirm', 1, 'auth_external');
    }

    public function test_get_signup_settings(): void {
        $result = auth_external_external::get_signup_settings();
        $result = \core_external\external_api::clean_returnvalue(
            auth_external_external::get_signup_settings_returns(),
            $result
        );

        $this->assertArrayHasKey('namefields', $result);
        $this->assertArrayHasKey('warnings', $result);
    }

    public function test_get_signup_settings_requires_external_registration(): void {
        global $CFG;
        $CFG->registerauth = 'email';
        $this->expectException(\moodle_exception::class);
        auth_external_external::get_signup_settings();
    }

    /**
     * H1 + H2 through the web-service entry point: when username generation is enabled the
     * submitted username is ignored and replaced with a generated, unique one.
     */
    public function test_signup_user_with_generated_username(): void {
        global $DB;
        set_config('generated_username', 1, 'auth_external');
        set_config('generated_prefix', 'ext_', 'auth_external');

        $result = auth_external_external::signup_user(
            'ignored',
            'Moodle@12345!',
            'John',
            'Doe',
            'john@example.com',
            'Graz',
            'AT'
        );
        $result = \core_external\external_api::clean_returnvalue(
            auth_external_external::signup_user_returns(),
            $result
        );

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['warnings']);

        $user = $DB->get_record('user', ['email' => 'john@example.com']);
        $this->assertEquals('ext_doejoh', $user->username);
        $this->assertEquals('external', $user->auth);
        $this->assertEquals(0, $user->confirmed);
    }

    /**
     * H2: when the generated username is already taken the web-service signup still succeeds,
     * storing a postfixed (unique) username.
     */
    public function test_signup_user_with_generated_username_collision(): void {
        global $DB;
        set_config('generated_username', 1, 'auth_external');
        set_config('generated_prefix', 'ext_', 'auth_external');
        $this->getDataGenerator()->create_user(['username' => 'ext_doejoh']);

        $result = auth_external_external::signup_user(
            'ignored',
            'Moodle@12345!',
            'John',
            'Doe',
            'john@example.com',
            'Graz',
            'AT'
        );
        $result = \core_external\external_api::clean_returnvalue(
            auth_external_external::signup_user_returns(),
            $result
        );

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['warnings']);

        $user = $DB->get_record('user', ['email' => 'john@example.com']);
        $this->assertEquals('ext_doejoh2', $user->username);
    }

    public function test_signup_user_requires_external_registration(): void {
        global $CFG;
        $CFG->registerauth = 'email';
        $this->expectException(\moodle_exception::class);
        auth_external_external::signup_user(
            'username',
            'Moodle@12345!',
            'John',
            'Doe',
            'john@example.com',
            'Graz',
            'AT'
        );
    }
}
