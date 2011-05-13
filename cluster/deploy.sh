#!/bin/sh
#
# this file is generated - do not modify!
#

export LANG=C
BRANCH=`git branch | sed -e '/^[^*]/d' -e 's/^\* \(.*\)/\1/'`
COMMIT=`git rev-parse --short HEAD`
COMMIT_FULL=`git rev-parse HEAD`
DIRTY=""
git status | grep -qF 'working directory clean' || DIRTY='-dirty'
echo "<a href='http://github.com/timof/jlf/commits/$COMMIT_FULL'>$BRANCH-$COMMIT$DIRTY</a>" >version.txt

chmod 755 .
chmod 644 ./leitvariable.php
chmod 644 ./inlinks.php
chmod 644 ./structure.php
chmod 644 ./ldap.php
chmod 644 ./mysql.php
chmod 644 ./views.php
chmod 755 ./windows
chmod 644 ./windows/backupprofileslist.php
chmod 644 ./windows/sync.php
chmod 644 ./windows/accountdomainslist.php
chmod 644 ./windows/tapeslist.php
chmod 644 ./windows/accountslist.php
chmod 644 ./windows/tapechunkslist.php
chmod 644 ./windows/host.php
chmod 644 ./windows/backupslist.php
chmod 644 ./windows/logbook.php
chmod 644 ./windows/logentry.php
chmod 644 ./windows/hostslist.php
chmod 644 ./windows/tape.php
chmod 644 ./windows/serviceslist.php
chmod 644 ./windows/diskslist.php
chmod 644 ./windows/disk.php
chmod 644 ./windows/menu.php
chmod 644 ./gadgets.php
chmod 644 ./physik.schema
chmod 600 ./deploy.sh
chmod 700 .git
