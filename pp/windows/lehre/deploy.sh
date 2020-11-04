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
echo "http://github.com/timof/jlf/commit/$COMMIT_FULL" > version.txt
echo "$BRANCH-$COMMIT$DIRTY" >> version.txt

chmod 700 .git
chmod 755 .
chmod 600 ./1einschreibung.php
chmod 644 ./1studiengaenge.php
chmod 600 ./b2
chmod 644 ./bed.php
chmod 644 ./brueckenkurs.php
chmod 644 ./bsc.php
chmod 700 ./deploy.sh
chmod 644 ./diplom.php
chmod 644 ./einschreibung.php
chmod 600 ./exam.template.summer
chmod 644 ./intro.php
chmod 644 ./lehre.php
chmod 644 ./mastro.php
chmod 644 ./med.php
chmod 644 ./modul.php
chmod 644 ./msc.php
chmod 644 ./phd.php
chmod 644 ./praktika.php
chmod 644 ./studiengaenge.php
chmod 644 ./studierendenvertretung.php
chmod 644 ./terminelehre.php
chmod 644 ./tutorium.php
