@auth @auth_external

Feature: Signup and username generation test for auth_external plugin
  Scenario: User signup and username generation
    Given the following config values are set as admin:
      | registerauth       | external          |               |
      | passwordpolicy     | 0                 |               |
      | generated_username | 1                 | auth_external |
      | generated_prefix   | external_         | auth_external |
    And I am on the signup form
    When I set the field "id_email" to "test@example.com"
    And I set the field "id_email2" to "test@example.com"
    And I set the field "id_firstname" to "John"
    And I set the field "id_lastname" to "Doe"
    And I set the field "id_city" to "New York"
    And I set the field "id_password" to "password"
    And I press the button with id "id_submitbutton"
    Then the username should be generated correctly "external_doejoh"

  Scenario: Signup username generation handles collisions
    Given the following config values are set as admin:
      | registerauth       | external          |               |
      | passwordpolicy     | 0                 |               |
      | generated_username | 1                 | auth_external |
      | generated_prefix   | external_         | auth_external |
    And the following "users" exist:
      | username        | firstname | lastname | email             | auth     |
      | external_doejoh | John      | Doe      | taken@example.com | external |
    And I am on the signup form
    When I set the field "id_email" to "test@example.com"
    And I set the field "id_email2" to "test@example.com"
    And I set the field "id_firstname" to "John"
    And I set the field "id_lastname" to "Doe"
    And I set the field "id_city" to "New York"
    And I set the field "id_password" to "password"
    And I press the button with id "id_submitbutton"
    Then the username should be generated correctly "external_doejoh2"

