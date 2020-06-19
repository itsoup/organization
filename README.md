# ITsoup's Organization domain service

![Run tests](https://github.com/itsoup/organization/workflows/Run%20tests/badge.svg)
[![Coverage Status](https://coveralls.io/repos/github/itsoup/organization/badge.svg?branch=master)](https://coveralls.io/github/itsoup/organization?branch=master)

## Quick review

* Aggregators for Customers’ information.
* Authorization and authentication of Users, via Roles and Scopes.

For more detailed information, check the corresponding [documentation Wiki section](https://github.com/itsoup/documentation/wiki/Organization).

## Installation

1. Clone this repository.
2. Run `composer install` to install all dependencies (add `--no-dev` if you're using this in production).
3. Run `cp .env.example .env` to create an `.env` file based on the distributed `.env.example` file.
4. Run `php artisan key:generate` to generate a new application key.
5. Update the `.env` file with the connection details for the database.
6. Run `php artisan migrate` to create the database schema.
7. Run `php artisan passport:install` to activate the oAuth2 server implementation.

## Testing

This project is fully tested. We have an [automatic pipeline](https://github.com/itsoup/organization/actions) and an [automatic code quality analysis](https://coveralls.io/github/itsoup/organization) tool set up to continuously test and assert the quality of all code published in this repository, but you can execute the test suite yourself by running the following command:

``` bash
vendor/bin/phpunit
```

_Note: This assumes you've run `composer install` (without the `--no-dev` option)._

**We aim to keep the master branch always deployable.** Exceptions may happen, but they should be extremely rare.

## Deploy

There're docker-compose related files that tries to provide an easy way to deploy this project to your infrastructure. You should create a docker-compose.override.yml (probably based on the example file provided), and change to your specific needs.

If you have any problem and need assistance, feel free to use the issues tracker to ask for support. However, be patient! Your request might take time to be answered.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

Please see [SECURITY](SECURITY.md) for details.

## Credits

- [José Postiga](https://github.com/josepostiga)
- [All Contributors](../../contributors)

## License

The MIT License (MIT).
