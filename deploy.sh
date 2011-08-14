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
chmod 700 ./foodsoft
chmod 644 ./version.txt
chmod 755 ./pi
chmod 644 ./pi/leitvariable.php
chmod 644 ./pi/inlinks.php
chmod 644 ./pi/structure.php
chmod 644 ./pi/mysql.php
chmod 644 ./pi/views.php
chmod 755 ./pi/windows
chmod 644 ./pi/windows/personen.php
chmod 644 ./pi/windows/menu.php
chmod 644 ./pi/windows/person.php
chmod 755 ./cluster
chmod 644 ./cluster/leitvariable.php
chmod 644 ./cluster/inlinks.php
chmod 644 ./cluster/structure.php
chmod 644 ./cluster/ldap.php
chmod 644 ./cluster/mysql.php
chmod 644 ./cluster/views.php
chmod 644 ./cluster/common.php
chmod 755 ./cluster/windows
chmod 644 ./cluster/windows/backupprofileslist.php
chmod 644 ./cluster/windows/sync.php
chmod 644 ./cluster/windows/backupchunkslist.php
chmod 644 ./cluster/windows/accountdomainslist.php
chmod 644 ./cluster/windows/tapeslist.php
chmod 644 ./cluster/windows/accountslist.php
chmod 644 ./cluster/windows/tapechunkslist.php
chmod 644 ./cluster/windows/host.php
chmod 644 ./cluster/windows/backupslist.php
chmod 644 ./cluster/windows/logbook.php
chmod 644 ./cluster/windows/logentry.php
chmod 644 ./cluster/windows/hostslist.php
chmod 644 ./cluster/windows/tape.php
chmod 644 ./cluster/windows/serviceslist.php
chmod 644 ./cluster/windows/diskslist.php
chmod 644 ./cluster/windows/disk.php
chmod 644 ./cluster/windows/menu.php
chmod 644 ./cluster/gadgets.php
chmod 644 ./cluster/physik.schema
chmod 600 ./.gitignore
chmod 700 ./pre-commit
chmod 777 ./index.php
chmod 755 ./alien
chmod 644 ./alien/prototype.js
chmod 755 ./img
chmod 644 ./img/warp9.trans.gif
chmod 644 ./img/b_edit.png
chmod 644 ./img/warp8.trans.gif
chmod 644 ./img/close_black_hover.gif
chmod 644 ./img/warp0.trans.gif
chmod 644 ./img/fork_black.gif
chmod 644 ./img/warp2.trans.gif
chmod 644 ./img/minus.png
chmod 644 ./img/green.png
chmod 644 ./img/open.small.blue.gif
chmod 644 ./img/euro.png
chmod 644 ./img/warp6.trans.gif
chmod 644 ./img/sort.down.3.trans.gif
chmod 644 ./img/equal.small.blue.trans.gif
chmod 644 ./img/b_drop.png
chmod 644 ./img/plus.png
chmod 644 ./img/card.png
chmod 644 ./img/arrow.up.blue.png
chmod 644 ./img/print_black.gif
chmod 644 ./img/people.png
chmod 644 ./img/sort.up.2.trans.gif
chmod 644 ./img/sort.down.trans.gif
chmod 644 ./img/sort.up.3.trans.gif
chmod 644 ./img/close_black.gif
chmod 644 ./img/warp7.trans.gif
chmod 644 ./img/warp4.trans.gif
chmod 644 ./img/warp3.trans.gif
chmod 644 ./img/plus.small.blue.active.gif
chmod 644 ./img/sort.up.trans.gif
chmod 644 ./img/arrow.down.blue.png
chmod 644 ./img/lock.trans.gif
chmod 644 ./img/reload_black.gif
chmod 644 ./img/open_black_trans.gif
chmod 644 ./img/sort.down.2.trans.gif
chmod 644 ./img/plus.blue.png
chmod 644 ./img/warp5.trans.gif
chmod 644 ./img/euro.small.blue.trans.gif
chmod 644 ./img/plus.small.blue.trans.gif
chmod 644 ./img/people.blue.trans.gif
chmod 644 ./img/warp1.trans.gif
chmod 644 ./img/home_black.gif
chmod 644 ./img/open.small.blue.trans.gif
chmod 644 ./img/open.small.active.gif
chmod 644 ./img/b_browse.png
chmod 644 ./img/close.small.blue.trans.gif
chmod 644 ./img/equal.small.blue.gif
chmod 644 ./img/close_black_trans.gif
chmod 644 ./img/equal.small.blue.active.gif
chmod 644 ./img/close.small.active.gif
chmod 644 ./img/plus.small.blue.gif
chmod 644 ./img/close.small.blue.gif
chmod 777 ./setup.php
chmod 755 ./sus
chmod 644 ./sus/leitvariable.php
chmod 644 ./sus/inlinks.php
chmod 644 ./sus/structure.php
chmod 644 ./sus/mysql.php
chmod 644 ./sus/kontenrahmen.php
chmod 644 ./sus/forms.php
chmod 644 ./sus/views.php
chmod 644 ./sus/common.php
chmod 755 ./sus/windows
chmod 644 ./sus/windows/posten.php
chmod 644 ./sus/windows/things.php
chmod 644 ./sus/windows/hauptkontenliste.php
chmod 744 ./sus/windows/journal.php
chmod 644 ./sus/windows/darlehenliste.php
chmod 644 ./sus/windows/personen.php
chmod 644 ./sus/windows/darlehen.php
chmod 644 ./sus/windows/logbook.php
chmod 644 ./sus/windows/geschaeftsjahre.php
chmod 644 ./sus/windows/buchung.php
chmod 644 ./sus/windows/hauptkonten.php
chmod 644 ./sus/windows/logentry.php
chmod 644 ./sus/windows/zahlungsplan.php
chmod 644 ./sus/windows/unterkontenliste.php
chmod 644 ./sus/windows/hauptkonto.php
chmod 644 ./sus/windows/unterkonto.php
chmod 644 ./sus/windows/thing.php
chmod 644 ./sus/windows/menu.php
chmod 644 ./sus/windows/person.php
chmod 644 ./sus/gadgets.php
chmod 644 ./sus/css.css
chmod 644 ./sus/basic.php
chmod 644 ./sus/hgb_klassen.php
chmod 755 ./code
chmod 644 ./code/leitvariable.php
chmod 644 ./code/html.php
chmod 644 ./code/head.php
chmod 644 ./code/inlinks.php
chmod 644 ./code/js.js
chmod 644 ./code/structure.php
chmod 644 ./code/ldap.php
chmod 644 ./code/mysql.php
chmod 644 ./code/forms.php
chmod 644 ./code/views.php
chmod 644 ./code/common.php
chmod 644 ./code/err_functions.php
chmod 644 ./code/gadgets.php
chmod 644 ./code/index.php
chmod 644 ./code/config.php
chmod 644 ./code/setup.php
chmod 644 ./code/css.css
chmod 644 ./code/basic.php
chmod 644 ./code/login.php
chmod 600 ./code/tests.php
chmod 700 ./deploy.sh
chmod 700 .git
