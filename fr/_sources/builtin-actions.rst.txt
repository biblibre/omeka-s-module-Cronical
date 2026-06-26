Built-in actions
================

Cronical provides a few actions, mostly to serve as examples for developers.

Heartbeat
---------

This is the simplest one: it writes a message to logs. It illustrates how an
action can use the Omeka logger and how to declare action-specific parameters.

It can be useful to verify that Cronical is indeed working correctly.

Parameters
^^^^^^^^^^

Log level
    What log level will be used. One of: debug, notice, info, warn, error,
    alert, emergency.

Index full-text
---------------

This action starts the rebuilding of Omeka full-text search index in a
background job and exit immediately. The real work is done in the background
job, which can be seen in the administration interface.

This action has no parameters.
