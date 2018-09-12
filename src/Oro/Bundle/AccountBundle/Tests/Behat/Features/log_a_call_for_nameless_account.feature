@ticket-CRM-9070
@fixture-OroAccountBundle:nameless_account.yml

Feature: Log a call for nameless account
  In order to have ability work with accounts
  As administrator
  I need to have ability log a call for nameless account

  Scenario: Log a call for nameless account
    Given I login as administrator
    And I go to Customers/Accounts
    And I click "View" on first row in grid
    And follow "More actions"
    And follow "Log call"
    And fill form with:
      | Subject             | Very important call           |
      | Additional comments | Propose something interesting |
      | Call date & time    | 2017-08-24 11:00              |
      | Duration            | 00:05:30                      |
    When I press "Log call"
    Then I should see "Call saved" flash message
    And should see "Very important call" call in activity list

  Scenario: View Call in activity list
    Given I go to Activities/ Calls
    And I click "View" on first row in grid
    Then I should see call with:
      | Subject             | Very important call           |
      | Additional comments | Propose something interesting |
      | Call date & time    | Aug 24, 2017                  |
      | Call date & time    | 11:00 AM                      |
      | Phone number        | (310) 475-0859                |
      | Direction           | Outgoing                      |
      | Duration            | 5:30                          |
