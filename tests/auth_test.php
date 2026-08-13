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

/**
 * Tests for the auth_external authentication plugin.
 *
 * @package    auth_external
 * @copyright  2026 Stephan Lorbek <stephan.lorbek@uni-graz.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \auth_plugin_external
 */
final class auth_test extends \advanced_testcase {
    /** @var int external_user profile field id. */
    private $externaluserfieldid;

    /** @var int external_user_verified profile field id. */
    private $externaluserverifiedfieldid;

    /** @var int external_user_pending profile field id. */
    private $externaluserpendingfieldid;

    protected function setUp(): void {
        global $DB;
        parent::setUp();
        $this->resetAfterTest(true);

        // Custom profile fields used by the onboarding flow (see classes/observer.php and auth.php).
        $this->externaluserfieldid = $DB->get_field('user_info_field', 'id', ['shortname' => 'external_user']);
        if (!$this->externaluserfieldid) {
            $this->externaluserfieldid = $this->getDataGenerator()->create_custom_profile_field([
                'shortname' => 'external_user', 'name' => 'External user',
                'datatype' => 'checkbox', 'signup' => 0, 'visible' => 1, 'required' => 0,
            ])->id;
        }

        $this->externaluserverifiedfieldid = $DB->get_field('user_info_field', 'id', ['shortname' => 'external_user_verified']);
        if (!$this->externaluserverifiedfieldid) {
            $this->externaluserverifiedfieldid = $this->getDataGenerator()->create_custom_profile_field([
                'shortname' => 'external_user_verified', 'name' => 'External user verified',
                'datatype' => 'checkbox', 'signup' => 0, 'visible' => 1, 'required' => 0,
            ])->id;
        }

        $this->externaluserpendingfieldid = $DB->get_field('user_info_field', 'id', ['shortname' => 'external_user_pending']);
        if (!$this->externaluserpendingfieldid) {
            $this->externaluserpendingfieldid = $this->getDataGenerator()->create_custom_profile_field([
                'shortname' => 'external_user_pending', 'name' => 'External user pending',
                'datatype' => 'checkbox', 'signup' => 0, 'visible' => 1, 'required' => 0,
            ])->id;
        }
    }

    /**
     * Return the plugin instance.
     *
     * @return \auth_plugin_external
     */
    private function get_plugin(): \auth_plugin_external {
        $auth = get_auth_plugin('external');
        $this->assertInstanceOf(\auth_plugin_external::class, $auth);
        return $auth;
    }

    /**
     * H1: the generated username must use the configured prefix.
     */
    public function test_generate_username_uses_configured_prefix(): void {
        set_config('generated_prefix', 'ext_', 'auth_external');
        $this->assertSame('ext_doejoh', $this->get_plugin()->generate_username('John', 'Doe'));
    }

    /**
     * The generated username is lowercased and built as lastname + first 3 chars of firstname.
     */
    public function test_generate_username_lowercases_and_concatenates_names(): void {
        set_config('generated_prefix', '', 'auth_external');
        $this->assertSame('doejoh', $this->get_plugin()->generate_username('John', 'Doe'));
    }

    /**
     * Names are transliterated to ASCII and accents are stripped.
     */
    public function test_generate_username_transliterates_accented_characters(): void {
        set_config('generated_prefix', '', 'auth_external');
        $this->assertSame('perezjos', $this->get_plugin()->generate_username('José', 'Pérez'));
    }

    /**
     * Whitespace is removed from names before building the username.
     */
    public function test_generate_username_removes_whitespace(): void {
        set_config('generated_prefix', '', 'auth_external');
        $this->assertSame('doejan', $this->get_plugin()->generate_username('Jane Ann', 'Doe'));
    }

    /**
     * When the generated username is already taken, a numeric postfix is appended
     * incrementally (based on the base name, not the previous attempt).
     */
    public function test_generate_username_increments_on_collision(): void {
        global $DB;
        set_config('generated_prefix', 'ext_', 'auth_external');

        $this->getDataGenerator()->create_user(['username' => 'ext_doejoh']);
        $this->assertSame('ext_doejoh2', $this->get_plugin()->generate_username('John', 'Doe'));

        $this->getDataGenerator()->create_user(['username' => 'ext_doejoh2']);
        $this->assertSame('ext_doejoh3', $this->get_plugin()->generate_username('John', 'Doe'));
        $this->assertFalse($DB->record_exists('user', ['username' => 'ext_doejoh22']));
    }

    public function test_password_expire_disabled(): void {
        set_config('expiration', 0, 'auth_external');
        $user = $this->getDataGenerator()->create_user(['auth' => 'external']);
        $this->assertEquals(0, $this->get_plugin()->password_expire($user->username));
    }

    public function test_password_expire_enabled(): void {
        set_config('expiration', 1, 'auth_external');
        set_config('expirationtime', 30, 'auth_external');
        $user = $this->getDataGenerator()->create_user(['auth' => 'external']);

        // Password last updated 10 days ago -> 20 days remaining.
        set_user_preference('auth_external_passwordupdatetime', time() - (10 * DAYSECS), $user->id);
        $this->assertEquals(20, $this->get_plugin()->password_expire($user->username));

        // Password last updated 40 days ago -> already expired 10 days ago.
        set_user_preference('auth_external_passwordupdatetime', time() - (40 * DAYSECS), $user->id);
        $this->assertEquals(-10, $this->get_plugin()->password_expire($user->username));
    }

    public function test_user_login_validates_password_and_sets_default_preference(): void {
        $plugin = $this->get_plugin();
        $user = $this->getDataGenerator()->create_user([
            'auth' => 'external',
            'password' => 'Moodle@12345!',
        ]);
        unset_user_preference('auth_external_passwordupdatetime', $user->id);

        $this->assertTrue($plugin->user_login($user->username, 'Moodle@12345!'));
        // The plugin seeds the password update preference on first login if it is not set yet.
        $this->assertNotNull(get_user_preferences('auth_external_passwordupdatetime', null, $user->id));

        $this->assertFalse($plugin->user_login($user->username, 'Wrong!Password'));
    }

    public function test_user_update_password_refreshes_preference(): void {
        $plugin = $this->get_plugin();
        $user = $this->getDataGenerator()->create_user([
            'auth' => 'external',
            'password' => 'Moodle@12345!',
        ]);
        set_user_preference('auth_external_passwordupdatetime', 1, $user->id);

        $this->assertTrue($plugin->user_update_password($user, 'Moodle@54321?'));
        $this->assertGreaterThan(1, (int) get_user_preferences('auth_external_passwordupdatetime', 0, $user->id));
    }

    public function test_user_confirm_validates_secret_and_restores_wantsurl(): void {
        global $DB, $SESSION;
        $plugin = $this->get_plugin();
        $user = $this->getDataGenerator()->create_user([
            'auth' => 'external',
            'confirmed' => 0,
            'secret' => 'confirmsecret',
        ]);

        // A wrong secret must not confirm the user.
        $plugin->user_confirm($user->username, 'wrongsecret');
        $this->assertEquals(0, $DB->get_field('user', 'confirmed', ['id' => $user->id]));

        // A user of a different auth type cannot be confirmed here.
        $other = $this->getDataGenerator()->create_user(['auth' => 'manual', 'confirmed' => 1, 'secret' => 'x']);
        $this->assertEquals(AUTH_CONFIRM_ERROR, $plugin->user_confirm($other->username, 'x'));

        // Correct secret: confirm and restore wantsurl.
        set_user_preference('auth_external_wantsurl', '/course/view.php?id=1', $user->id);
        $this->assertEquals(AUTH_CONFIRM_OK, $plugin->user_confirm($user->username, 'confirmsecret'));
        $this->assertEquals(1, $DB->get_field('user', 'confirmed', ['id' => $user->id]));
        $this->assertEquals('/course/view.php?id=1', $SESSION->wantsurl);
        $this->assertEmpty(get_user_preferences('auth_external_wantsurl', null, $user->id));

        // Already confirmed.
        $this->assertEquals(AUTH_CONFIRM_ALREADY, $plugin->user_confirm($user->username, 'confirmsecret'));
    }

    /**
     * H1 + H2 through the real signup path: a generated, unique username is stored and the
     * onboarding profile fields are set.
     */
    public function test_user_signup_with_generated_username_and_external_fields(): void {
        global $DB;
        set_config('generated_username', 1, 'auth_external');
        set_config('generated_prefix', 'ext_', 'auth_external');
        // Email confirmation enabled so the method returns cleanly instead of redirecting.
        set_config('email_confirm', 1, 'auth_external');

        $user = (object) [
            'username' => 'ignored',
            'password' => 'Moodle@12345!',
            'auth' => 'external',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'firstnamephonetic' => '',
            'lastnamephonetic' => '',
            'middlename' => '',
            'alternatename' => '',
            'email' => 'john@example.com',
            'city' => 'Graz',
            'country' => 'AT',
            'lang' => 'en',
            'secret' => 'confirmsecret',
        ];

        $this->assertTrue($this->get_plugin()->user_signup_with_confirmation($user, false));

        $created = $DB->get_record('user', ['email' => 'john@example.com']);
        $this->assertEquals('ext_doejoh', $created->username);
        $this->assertEquals('external', $created->auth);
        $this->assertEquals(0, $created->confirmed);
        // Password expiry preference is recorded at signup time.
        $this->assertNotNull(get_user_preferences('auth_external_passwordupdatetime', null, $created->id));
        // Onboarding flags are persisted as custom profile data.
        $this->assertEquals(
            1,
            $DB->get_field(
                'user_info_data',
                'data',
                ['userid' => $created->id, 'fieldid' => $this->externaluserfieldid]
            )
        );
        $this->assertEmpty(
            $DB->get_field(
                'user_info_data',
                'data',
                ['userid' => $created->id, 'fieldid' => $this->externaluserpendingfieldid]
            )
        );
    }
}
