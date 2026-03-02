@auth @auth_external @javascript
Feature: External authentication password expiration
  In order to maintain security
  As a user of the external auth plugin
  I need to be forced to change my password when it expires

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            | auth     | suspended | password |
      | student1 | Student   | One      | student1@example.com | external | 0 | Moodle@12345! |
    And the following config values are set as admin:
      | passwordpolicy  | 1     |
      | registerauth    | external |
      | auth            | external |
    And the following config values are set as admin:
      | expiration | 1 | auth_external |
      | expirationtime | 30 | auth_external |

  Scenario: User logs in with a fresh password and is not prompted to change it
     hen I am on homepage
    And I click on "Log in" "link"
    And I set the field "Username" to "student1"
    And I set the field "Password" to "Moodle@12345!"
    And I press "Log in"

 Scenario: User is forced to change password when expired
    Given the user "student1" has the following preferences:
      | auth_external_passwordupdatetime | -2678400 |
    When I am on homepage
    And I click on "Log in" "link"
    And I set the field "Username" to "student1"
    And I set the field "Password" to "Moodle@12345!"
    And I press "Log in"
    Then I should see "Your password has expired"
    And I click on "Continue" "button"
    And I set the field "Current password" to "Moodle@12345!"
    And I set the field "New password" to "Moodle@12345?"
    And I set the field "New password (again)" to "Moodle@12345?"
    And I press "Save changes"
    Then I should see "Password has been changed"

 Scenario: User is logs in with an expired password but no passwordpolicy
    Given the following config values are set as admin:
      | expiration | 0 | auth_external |
      | expirationtime | 30 | auth_external |
    When the user "student1" has the following preferences:
      | auth_external_passwordupdatetime | -2678400 |
    And I am on homepage
    And I click on "Log in" "link"
    And I set the field "Username" to "student1"
    And I set the field "Password" to "Moodle@12345!"
    And I press "Log in"
    Then I should not see "Your password has expired"
