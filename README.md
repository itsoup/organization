# ITsoup's Organization domain service

![Run tests](https://github.com/itsoup/organization/workflows/Run%20tests/badge.svg)
[![Coverage Status](https://coveralls.io/repos/github/itsoup/organization/badge.svg?branch=master)](https://coveralls.io/github/itsoup/organization?branch=master)

For detailed information, check the [documentation](https://github.com/itsoup/organization/wiki).   

## Installation

Refer to the [documentation](https://github.com/itsoup/organization/wiki/Installation) to see how to install and activate this service.

## Testing

This project is fully tested. We have an [automatic pipeline](https://github.com/itsoup/organization/actions) and an [automatic code quality analysis](https://coveralls.io/github/itsoup/organization) tool set up to continuously test and assert the quality of all code published in this repository, but you can execute the test suite yourself by running the following command:

``` bash
vendor/bin/phpunit
```

_Note: This assumes you've run `composer install` (without the `--no-dev` option)._

**We aim to keep the master branch always deployable.** Exceptions may happen, but they should be extremely rare.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

Please see [SECURITY](SECURITY.md) for details.

## Credits

- [José Postiga](https://github.com/josepostiga)
- [All Contributors](../../contributors)

## License

The MIT License (MIT).
