# Architecture Standards: Automated Tests

This document defines the information and structures to be used when building automated tests. Follow these rules strictly when suggesting or creating code.

## Unit Tests

We should use unit tests mainly in cases of isolated functions within the application, such as utility functions or functions that can be called from anywhere in the application.

There are currently no unit tests in the application.

## Feature Tests

We should use feature tests in all scenarios where we want to test an endpoint end-to-end.

The idea of feature tests is to validate the endpoint as a whole, sending input data in the format the endpoint requires and verifying whether the output data matches the expected results.

It is important to validate the feature as a whole because it ensures that whenever the tests are executed, all business rules will work correctly. This protects against changes in business rules made in the code causing any feature to stop working.

The main objective of the tests is to guarantee the proper functioning of business rules regardless of any changes made to the code.

- Add tests under `tests/Feature` and in the correct context according to the service module
- Use the traits `use RefreshDatabase, WithFaker;`.
- When the endpoint requires authentication, use the trait `use AuthenticationTrait;`.
- When the endpoint needs to work with timezone, use `use TimezoneTestTrait;`.
- Use the `setUp` method to prepare all data that will be used across all tests, and run seeders if necessary.
- To perform authentication, use the trait method following the example below:

```php
$this->token = $this->authenticate([
    'email' => 'member@example.com',
    'password' => 'Password123!'
]);
Add the received token to the headers:

$this->headers = [
    'Authorization' => "Bearer {$this->token}",
];
```

- Always keep the headers variable available so all tests can use it.
- Always use TestDox by providing a test description related to the business rule, for example: #[TestDox('[AUTH] Should user auth successfully')].
- If a service has business rules from multiple contexts, use brackets to identify which context is being tested, for example: [AUTH].
- Always follow the Arrange, Act, Assert pattern.
- Test both success and error scenarios to cover as many cases as possible.
- Use factories to create test data.
- Mock only external services (third-party APIs) or Jobs that send emails. Do not mock the Repository layer in Feature tests to ensure real persistence in the test database.
- In the Assert step, validate the output JSON using $response->assertJsonStructure([...]) to ensure the output structure is always correct.

### Useful Commands

Generate test coverage report: ./vendor/bin/phpunit --coverage-html tests/reports, access the report at tests/reports/index.html

Run tests: composer test

Run tests and see the description of each test: ./vendor/bin/phpunit tests/Feature/User/AuthControllerTest.php --testdox
