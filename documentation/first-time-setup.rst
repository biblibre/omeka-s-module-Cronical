First time setup
================

Once the module is installed, you need to configure the system to execute the
``cronical`` script periodically. This script will check if actions need to
be executed and execute them.

There are different ways to do that. We will explain the most common ones.

Note that in the following examples we configure the system to run the
``cronical`` script every minute. It is not required to do so.  For instance
you can run it every hour and it will execute all actions that should have been
executed in the past hour. So adjust according to your needs.

cron
----

Create ``/etc/cron.d/cronical`` with the following contents::

    * * * * * <user> <path-to-omeka-s>/modules/Cronical/bin/cronical

Replace ``<user>`` with the name of the user that runs Omeka S (``www-data``
for instance), and replace ``<path-to-omeka-s>`` with the absolute path to
Omeka S.

If you do not have root access, but you are able to log in as the same user
that is running Omeka S, you can instead run ``crontab -e`` and add the
following line::

    * * * * * <path-to-omeka-s>/modules/Cronical/bin/cronical

systemd
-------

Create ``/etc/systemd/system/cronical.timer`` with the following contents::

    [Unit]
    Description=Cronical

    [Timer]
    OnCalendar=*-*-* *:*:00

    [Install]
    WantedBy=timers.target

and create ``/etc/systemd/system/cronical.service`` with the following contents::

    [Unit]
    Description=Cronical

    [Service]
    Type=oneshot
    ExecStart=<path-to-omeka>/modules/Cronical/bin/cronical
    User=<user>

Replace ``<user>`` with the name of the user that runs Omeka S (``www-data``
for instance), and replace ``<path-to-omeka-s>`` with the absolute path to
Omeka S.

Then execute the following command::

    systemctl enable --now cronical.timer
