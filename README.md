# Cronical (Omeka S module)

This module is an action scheduler for Omeka.

It allows to executed various actions based on the date and time. Actions can
be executed daily, weekly, monthly, yearly, or anything in between (for
instance, "every 1st and 15th of each month"). Each action has its own
schedule.

Actions themselves are mostly provided by other modules.

The complete documentation of Cronical can be found at
<https://biblibre.github.io/omeka-s-module-Cronical/>

## Requirements

* Omeka S >= 4.1.0
* Access to the server crontab (or equivalent) to set up a script to be run
  periodically

## Quick start

1. [Add the module to Omeka S](https://omeka.org/s/docs/user-manual/modules/#adding-modules-to-omeka-s)
2. Add the following line to the server crontab:

    ```
    * * * * * [<user>] <path-to-omeka>/modules/Cronical/bin/cronical
    ```
3. Login to the admin interface, go to the "Scheduled actions" section and start adding scheduled actions

## Features

* Execute actions automatically based on date (day of month, month, day of
  week) and time (hour, minute)
* Any valid cron expression is allowed for day of month, month, and day of week.
* Hour and minute are limited so that each action cannot be run more than once
  per day, but system administrators can bypass that limitation to schedule
  actions to be executed up to once per minute.
* Omeka S modules can provide their own actions
* An action can start a background job if the task is expected to be long, but
  it's optional.

## Comparison with similar modules

### Cron

[Cron](https://omeka.org/s/modules/Cron/) is a module for Omeka S that provides
a scheduled action system allowing modules to register and execute recurring
actions.

* With Cron, every scheduled action is executed with the same schedule. With
  Cronical, each action has its own schedule.
* Cronical has more options regarding schedule. You can configure the time, the
  day of the month, the month and the day of the week.

## How to contribute

You can contribute to this module in many ways. Discover how by reading
[Contributing](CONTRIBUTING.md).

## Contributors / Sponsors

Cronical was sponsored by:

* Université de Lille

## License

Cronical is distributed under the GNU General Public License, version 3 (GPLv3).
The full text of this license is given in the `LICENSE` file.
