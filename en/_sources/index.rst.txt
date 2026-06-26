Introduction
============

Cronical is an action scheduler for Omeka.

It allows to executed various actions based on the date and time. Actions can
be executed daily, weekly, monthly, yearly, or anything in between (for
instance, "every 1st and 15th of each month"). Each action has its own
schedule.

Actions themselves are mostly provided by other modules.

Requirements
------------

* Omeka S >= 4.1.0
* Access to the server crontab (or equivalent) to set up a script to be run
  periodically

Features
--------

* Execute actions automatically based on date (day of month, month, day of
  week) and time (hour, minute)
* Any valid cron expression is allowed for day of month, month, and day of week.
* Hour and minute are limited so that each action cannot be run more than once
  per day, but system administrators can bypass that limitation to schedule
  actions to be executed up to once per minute.
* Omeka S modules can provide their own actions
* An action can start a background job if the task is expected to be long, but
  it's optional.

Comparison with similar modules
-------------------------------

Cron
^^^^

`Cron <https://omeka.org/s/modules/Cron/>`__ is a module for Omeka S that provides
a scheduled action system allowing modules to register and execute recurring
actions.

* With Cron, every scheduled action is executed with the same schedule. With
  Cronical, each action has its own schedule.
* Cronical has more options regarding schedule. You can configure the time, the
  day of the month, the month and the day of the week.

.. toctree::
   :maxdepth: 2
   :caption: User manual

   first-time-setup
   scheduled-actions
   builtin-actions

.. toctree::
   :maxdepth: 2
   :caption: Developer documentation

   developer/write-new-action
