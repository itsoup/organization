# Changelog

All notable changes to this repository will be documented in this file.

## [Unreleased]

### Added

- Users generated tokens are invalidated when syncing roles (#35)
- A newly created User must verify their email before being able to login (#36)
- An authenticated User can access his own account information (#37)
- An authenticated User can edit his own account information (#39)
- An authenticated User can list all of his issued tokens (#38)

### Changed

- N/A

### Deprecated

- N/A

### Removed

- N/A

### Fixed

- N/A

### Security

- N/A

## 1.0.0-beta - 2020-06-24

### Added

- `.http-client` folder for practical API calls collections' storing.

### Changed

- Roles' show endpoint now works for soft-deleted resources.
- Roles can now be attached to deleted Users.
- Users' show endpoint now works for soft-deleted resources.

## 1.0.0-alpha - 2020-06-19

### Added

- Modules to manage Customers, Users and Roles.
