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
 * Tests for auth_external\\observer.
 *
 * @package    auth_external
 * @copyright  2026 Stephan Lorbek <stephan.lorbek@uni-graz.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \auth_external\observer
 */
final class observer_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Helper to create onboarding custom profile fields.
     */
    private function create_onboarding_profile_fields(): void {
        global $DB;
        $generator = $this->getDataGenerator();
        $fields = [
            'external_user' => 'External user',
            'external_user_verified' => 'External user verified',
            'external_user_pending' => 'External user pending',
        ];
        foreach ($fields as $shortname => $name) {
            if (!$DB->record_exists('user_info_field', ['shortname' => $shortname])) {
                $generator->create_custom_profile_field([
                    'shortname' => $shortname, 'name' => $name,
                    'datatype' => 'checkbox', 'signup' => 0, 'visible' => 1, 'required' => 0,
                ]);
            }
        }
    }

    /**
     * An external user created by a logged-in user (e.g. an admin) is automatically approved:
     * marked as external, verified and not pending.
     */
    public function test_user_created_by_actor_is_auto_approved(): void {
        global $DB;
        $this->create_onboarding_profile_fields();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user(['auth' => 'external']);

        $fieldids = [];
        foreach (['external_user', 'external_user_verified', 'external_user_pending'] as $shortname) {
            $fieldids[$shortname] = $DB->get_field('user_info_field', 'id', ['shortname' => $shortname]);
        }

        $data = function (string $shortname) use ($DB, $user, $fieldids) {
            return $DB->get_field(
                'user_info_data',
                'data',
                ['userid' => $user->id, 'fieldid' => $fieldids[$shortname]]
            );
        };

        $this->assertEquals(1, $data('external_user'));
        $this->assertEquals(1, $data('external_user_verified'));
        $this->assertEmpty($data('external_user_pending'));
    }

    /**
     * The observer must leave non-external users untouched.
     */
    public function test_user_created_with_other_auth_is_not_modified(): void {
        global $DB;
        $this->create_onboarding_profile_fields();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user(['auth' => 'manual']);

        $this->assertFalse($DB->record_exists('user_info_data', ['userid' => $user->id]));
    }
}
