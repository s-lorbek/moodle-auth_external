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
 * Step definition for auth_email
 *
 * @package    auth_external
 * @category   test
 * @copyright  2024 Stephan Lorbek
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Step definition for auth_email.
 *
 * @package    auth_external
 * @category   test
 * @copyright  2024 Stephan Lorbek
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_auth_external extends behat_base implements \Behat\Behat\Context\Context {
    private $firstname;
    private $lastname;
    private $generatedusername;

    /**
     * @When I fill in the following:
     */
    public function ifillinthefollowing(TableNode $table) {
        $page = $this->getSession()->getPage();

        foreach ($table->getHash() as $row) {
            foreach ($row as $field => $value) {
                $page->fillField($field, $value);
            }
        }
    }

    /**
     * @Given /^I am on the signup form$/
     */
    public function iamonthesignupform() {
        $this->visitPath('/login/signup.php');
    }

    /**
     * @Given /^I press the button with id "([^"]*)"$/
     */
    public function ipressthebuttonwithid($arg1) {
        $button = $this->getSession()->getPage()->find('css', "#$arg1");
        if ($button) {
            $button->press();
        } else {
            throw new \Exception("Button with ID '$arg1' not found");
        }
    }


    /**
     * @Then /^the username should be generated correctly "([^"]*)"$/
     */
    public function theusernameshouldbegeneratedcorrectly($username) {
        global $DB;
        $user = $DB->get_record("user", ["firstname" => "John", "lastname" => "Doe"]);
        if (strcmp($user->username, $username) == 0) {
            throw new \Exception("Username is generated wrong");
        }
        throw new PendingException();
    }

    /**
     * Sets a user preference for a specific user.
     *
     * @Given /^the user "(?P<username_string>(?:[^"]|\\")*)" has the following preferences:$/
     * @param string $username
     * @param TableNode $table
     */
    public function the_user_has_the_following_preferences($username, TableNode $table) {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], 'id', MUST_EXIST);
        $rows = $table->getRows();

        foreach ($rows as $row) {
            $name = $row[0];
            $value = $row[1];

            // If the value is a negative number, treat it as a relative timestamp from now.
            if (is_numeric($value) && $value < 0) {
                $value = time() + (int)$value;
            }

            set_user_preference($name, $value, $user->id);
        }
    }
}
