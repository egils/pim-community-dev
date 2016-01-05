@javascript
Feature: Edit common attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "apparel" catalog configuration
    And the following products:
      | sku          | family  | name-en_US     | name-fr_FR        | customer_rating-ecommerce | customer_rating-print|
      | black_jacket | jackets | A black jacket | Une veste noire   | 1                         | 2                    |
      | white_jacket | jackets | A white jacket | Une veste blanche | 3                         | 4                    |
    And I am logged in as "Julia"
    And I am on the products page

  @info https://akeneo.atlassian.net/browse/PIM-5351
  Scenario: Successfully mass edit scoped product values
    Given I filter by "channel" with value "Print"
    And I mass-edit products black_jacket and white_jacket
    And I choose the "Edit common attributes" operation
#    Then I should see "The selected product's attributes will be edited with the following data for the locale English and the channel Print, chosen in the product grid."
    When I display the Customer rating attribute
    And I change the "Customer rating" to "5"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the unlocalized ecommerce customer_rating of "black_jacket" should be "[1]"
    And the unlocalized ecommerce customer_rating of "white_jacket" should be "[3]"
    And the unlocalized print customer_rating of "black_jacket" should be "[5]"
    And the unlocalized print customer_rating of "white_jacket" should be "[5]"

  @info https://akeneo.atlassian.net/browse/PIM-5351
  Scenario: Successfully mass edit localized product values
    Given I switch the locale to "French (France)"
    And I filter by "channel" with value "Ecommerce"
    And I mass-edit products black_jacket and white_jacket
    And I choose the "Edit common attributes" operation
#    Then I should see "The selected product's attributes will be edited with the following data for the locale French and the channel Ecommerce, chosen in the product grid."
    When I display the Nom attribute
    And I change the "Nom" to "Une veste"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the french name of "black_jacket" should be "Une veste"
    And the french name of "white_jacket" should be "Une veste"
    And the english name of "black_jacket" should be "A black jacket"
    And the english name of "white_jacket" should be "A white jacket"
