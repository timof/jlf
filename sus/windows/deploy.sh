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
chmod 644 ./posten.php
chmod 644 ./things.php
chmod 644 ./hauptkontenliste.php
chmod 744 ./journal.php
chmod 644 ./darlehenliste.php
chmod 644 ./personen.php
chmod 644 ./darlehen.php
chmod 644 ./logbook.php
chmod 644 ./geschaeftsjahre.php
chmod 644 ./buchung.php
chmod 644 ./hauptkonten.php
chmod 644 ./logentry.php
chmod 644 ./zahlungsplan.php
chmod 644 ./unterkontenliste.php
chmod 644 ./hauptkonto.php
chmod 644 ./unterkonto.php
chmod 644 ./thing.php
chmod 700 ./deploy.sh
chmod 644 ./menu.php
chmod 644 ./person.php
chmod 700 .git
