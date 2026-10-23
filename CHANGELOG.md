# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

- Add script `bin/schedule-edit` to edit system and non-system scheduled actions
- Script `bin/schedule-add` now exits when the value of option `--settings` is
  not valid JSON
- Remove `-v/--verbose` option of `bin/schedule-add` because using it made no
  difference

## [0.1.2] - 2026-10-23

- Prevent infinite loop when searching for the next run date
- Prevent inactive users to run scheduled actions
- Prevent creation of a scheduled action with something else than a single
  value for hour or minute fields
- Prevent using "0" as step in cron expressions
- Prevent SQL injection in `onApiSearchQuery`
- Forbid every user (even global admins) to create or update scheduled action
  runs using the web interface or the API. This responsibility belongs to
  `bin/cronical` only.

## [0.1.1] - 2026-06-29

- Prevent system scheduled actions to be selected for batch operations
- Fix compatibility with Omeka S 4.1

## [0.1.0] - 2026-06-26

Initial release

[0.1.2]: https://github.com/biblibre/omeka-s-module-Cronical/releases/tag/v0.1.2
[0.1.1]: https://github.com/biblibre/omeka-s-module-Cronical/releases/tag/v0.1.1
[0.1.0]: https://github.com/biblibre/omeka-s-module-Cronical/releases/tag/v0.1.0
