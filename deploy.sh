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

chmod 755 .
chmod 700 ./foodsoft
chmod 644 ./foodsoft/inlinks.php
chmod 600 ./foodsoft/structure.php
chmod 600 ./foodsoft/mysql.php
chmod 600 ./foodsoft/common.php
chmod 755 ./foodsoft/foodsoft-git
chmod 644 ./foodsoft/foodsoft-git/leitvariable.php
chmod 755 ./foodsoft/foodsoft-git/antixls.modif
chmod 644 ./foodsoft/foodsoft-git/head.php
chmod 644 ./foodsoft/foodsoft-git/version.txt
chmod 600 ./foodsoft/foodsoft-git/fcck.php
chmod 644 ./foodsoft/foodsoft-git/INSTALL
chmod 755 ./foodsoft/foodsoft-git/js
chmod 755 ./foodsoft/foodsoft-git/js/lib
chmod 644 ./foodsoft/foodsoft-git/js/lib/prototype.js
chmod 644 ./foodsoft/foodsoft-git/js/foodsoft.js
chmod 644 ./foodsoft/foodsoft-git/js/tooltip.js
chmod 644 ./foodsoft/foodsoft-git/README.md
chmod 644 ./foodsoft/foodsoft-git/structure.php
chmod 644 ./foodsoft/foodsoft-git/dump.php
chmod 644 ./foodsoft/foodsoft-git/files_und_skripte
chmod 755 ./foodsoft/foodsoft-git/css
chmod 644 ./foodsoft/foodsoft-git/css/print.css
chmod 644 ./foodsoft/foodsoft-git/css/readonly.gif
chmod 644 ./foodsoft/foodsoft-git/css/modified.gif
chmod 644 ./foodsoft/foodsoft-git/css/foodsoft.css
chmod 700 ./foodsoft/foodsoft-git/pre-commit
chmod 755 ./foodsoft/foodsoft-git/windows
chmod 644 ./foodsoft/foodsoft-git/windows/dienstkontrollblatt.php
chmod 644 ./foodsoft/foodsoft-git/windows/gruppenpfand.php
chmod 644 ./foodsoft/foodsoft-git/windows/editLieferant.php
chmod 644 ./foodsoft/foodsoft-git/windows/gruppenmitglieder.php
chmod 644 ./foodsoft/foodsoft-git/windows/head.php
chmod 644 ./foodsoft/foodsoft-git/windows/lieferanten.php
chmod 644 ./foodsoft/foodsoft-git/windows/editProduktgruppe.php
chmod 644 ./foodsoft/foodsoft-git/windows/bestellungen.php
chmod 644 ./foodsoft/foodsoft-git/windows/abrechnung.php
chmod 644 ./foodsoft/foodsoft-git/windows/gruppen.php
chmod 644 ./foodsoft/foodsoft-git/windows/bestellen.php
chmod 644 ./foodsoft/foodsoft-git/windows/dienstplan.php
chmod 644 ./foodsoft/foodsoft-git/windows/editVerpackung.php
chmod 644 ./foodsoft/foodsoft-git/windows/editBestellung.php
chmod 644 ./foodsoft/foodsoft-git/windows/bestellschein.php
chmod 644 ./foodsoft/foodsoft-git/windows/editProdukt.php
chmod 644 ./foodsoft/foodsoft-git/windows/editKonto.php
chmod 644 ./foodsoft/foodsoft-git/windows/lieferantenkonto.php
chmod 644 ./foodsoft/foodsoft-git/windows/updownload.php
chmod 644 ./foodsoft/foodsoft-git/windows/gruppenkonto.php
chmod 644 ./foodsoft/foodsoft-git/windows/produktpreise.php
chmod 644 ./foodsoft/foodsoft-git/windows/bestellen.php.neu
chmod 644 ./foodsoft/foodsoft-git/windows/pfandverpackungen.php
chmod 644 ./foodsoft/foodsoft-git/windows/bilanz.php
chmod 644 ./foodsoft/foodsoft-git/windows/abschluss.php
chmod 644 ./foodsoft/foodsoft-git/windows/artikelsuche.php
chmod 644 ./foodsoft/foodsoft-git/windows/produkte.php
chmod 644 ./foodsoft/foodsoft-git/windows/editBuchung.php
chmod 644 ./foodsoft/foodsoft-git/windows/basar.php
chmod 644 ./foodsoft/foodsoft-git/windows/produktverteilung.php
chmod 644 ./foodsoft/foodsoft-git/windows/verluste.php
chmod 644 ./foodsoft/foodsoft-git/windows/konto.php
chmod 644 ./foodsoft/foodsoft-git/windows/gesamtlieferschein.php
chmod 644 ./foodsoft/foodsoft-git/windows/menu.php
chmod 644 ./foodsoft/foodsoft-git/windows/katalog_upload.php
chmod 644 ./foodsoft/foodsoft-git/index.php
chmod 600 ./foodsoft/foodsoft-git/ToDo.txt
chmod 755 ./foodsoft/foodsoft-git/img
chmod 644 ./foodsoft/foodsoft-git/img/b_edit.png
chmod 644 ./foodsoft/foodsoft-git/img/close_black_hover.gif
chmod 644 ./foodsoft/foodsoft-git/img/magic_wand.png
chmod 644 ./foodsoft/foodsoft-git/img/minus.png
chmod 644 ./foodsoft/foodsoft-git/img/green.png
chmod 644 ./foodsoft/foodsoft-git/img/euro.png
chmod 644 ./foodsoft/foodsoft-git/img/b_drop.png
chmod 644 ./foodsoft/foodsoft-git/img/plus.png
chmod 644 ./foodsoft/foodsoft-git/img/birne_rot.png
chmod 644 ./foodsoft/foodsoft-git/img/card.png
chmod 644 ./foodsoft/foodsoft-git/img/arrow.up.blue.png
chmod 644 ./foodsoft/foodsoft-git/img/print_black.gif
chmod 644 ./foodsoft/foodsoft-git/img/people.png
chmod 644 ./foodsoft/foodsoft-git/img/close_black.gif
chmod 644 ./foodsoft/foodsoft-git/img/fant.gif
chmod 644 ./foodsoft/foodsoft-git/img/arrow.down.blue.png
chmod 644 ./foodsoft/foodsoft-git/img/reload_black.gif
chmod 644 ./foodsoft/foodsoft-git/img/open_black_trans.gif
chmod 644 ./foodsoft/foodsoft-git/img/chalk_trans.gif
chmod 644 ./foodsoft/foodsoft-git/img/question_small.png
chmod 644 ./foodsoft/foodsoft-git/img/gluehbirne_15x16.png
chmod 644 ./foodsoft/foodsoft-git/img/b_browse.png
chmod 644 ./foodsoft/foodsoft-git/img/question.png
chmod 644 ./foodsoft/foodsoft-git/img/close_black_trans.gif
chmod 644 ./foodsoft/foodsoft-git/img/chart.png
chmod 755 ./foodsoft/foodsoft-git/setup.php
chmod 644 ./foodsoft/foodsoft-git/apache.sample.conf
chmod 644 ./foodsoft/foodsoft-git/links_und_parameter
chmod 755 ./foodsoft/foodsoft-git/code
chmod 644 ./foodsoft/foodsoft-git/code/html.php
chmod 644 ./foodsoft/foodsoft-git/code/inlinks.php
chmod 644 ./foodsoft/foodsoft-git/code/katalogsuche.php
chmod 644 ./foodsoft/foodsoft-git/code/forms.php
chmod 644 ./foodsoft/foodsoft-git/code/views.php
chmod 644 ./foodsoft/foodsoft-git/code/common.php
chmod 644 ./foodsoft/foodsoft-git/code/err_functions.php
chmod 644 ./foodsoft/foodsoft-git/code/config.php
chmod 644 ./foodsoft/foodsoft-git/code/login.php
chmod 644 ./foodsoft/foodsoft-git/foodsoft.class.php
chmod 700 ./foodsoft/foodsoft-git/deploy.sh
chmod 644 ./foodsoft/foodsoft.class.php
chmod 644 ./version.txt
chmod 777 ./get.rphp
chmod 755 ./pi
chmod 644 ./pi/leitvariable.php
chmod 755 ./pi/public
chmod 777 ./pi/public/download.php
chmod 777 ./pi/public/positionslist.php
chmod 777 ./pi/public/surveyslist.php
chmod 777 ./pi/public/person_view.php
chmod 777 ./pi/public/examslist.php
chmod 777 ./pi/public/groupslist.php
chmod 777 ./pi/public/survey_view.php
chmod 777 ./pi/public/logbook.php
chmod 777 ./pi/public/logentry.php
chmod 777 ./pi/public/peoplelist.php
chmod 777 ./pi/public/position_view.php
chmod 777 ./pi/public/group_view.php
chmod 777 ./pi/public/menu.php
chmod 644 ./pi/html.php
chmod 644 ./pi/inlinks.php
chmod 644 ./pi/structure.php
chmod 644 ./pi/mysql.php
chmod 644 ./pi/forms.php
chmod 644 ./pi/views.php
chmod 644 ./pi/common.php
chmod 755 ./pi/windows
chmod 644 ./pi/windows/download.php
chmod 644 ./pi/windows/teachinglist.php
chmod 644 ./pi/windows/positionslist.php
chmod 644 ./pi/windows/surveyslist.php
chmod 777 ./pi/windows/person_view.php
chmod 744 ./pi/windows/position_edit.php
chmod 744 ./pi/windows/examslist.php
chmod 644 ./pi/windows/groupslist.php
chmod 644 ./pi/windows/survey_view.php
chmod 644 ./pi/windows/group_edit.php
chmod 644 ./pi/windows/exam_edit.php
chmod 644 ./pi/windows/survey_edit.php
chmod 777 ./pi/windows/logbook.php
chmod 644 ./pi/windows/exam_view.php
chmod 777 ./pi/windows/logentry.php
chmod 644 ./pi/windows/peoplelist.php
chmod 777 ./pi/windows/position_view.php
chmod 777 ./pi/windows/group_view.php
chmod 644 ./pi/windows/person_edit.php
chmod 644 ./pi/windows/admin.php
chmod 644 ./pi/windows/menu.php
chmod 644 ./pi/gadgets.php
chmod 644 ./pi/basic.php
chmod 700 ./htmlDefuse
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
chmod 777 ./cluster/windows/logbook.php
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
chmod 644 ./img/file2.over.trans.gif
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
chmod 644 ./img/file2.trans.gif
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
chmod 644 ./img/dilbert.5652.gif
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
chmod 777 ./setup.rphp
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
chmod 644 ./sus/windows/ka.php
chmod 744 ./sus/windows/journal.php
chmod 644 ./sus/windows/darlehenliste.php
chmod 644 ./sus/windows/personen.php
chmod 644 ./sus/windows/darlehen.php
chmod 777 ./sus/windows/logbook.php
chmod 644 ./sus/windows/geschaeftsjahre.php
chmod 644 ./sus/windows/buchung.php
chmod 644 ./sus/windows/hauptkonten.php
chmod 777 ./sus/windows/logentry.php
chmod 644 ./sus/windows/zahlungsplan.php
chmod 755 ./sus/windows/config.php
chmod 644 ./sus/windows/unterkontenliste.php
chmod 644 ./sus/windows/hauptkonto.php
chmod 644 ./sus/windows/unterkonto.php
chmod 644 ./sus/windows/thing.php
chmod 644 ./sus/windows/zahlungsplanliste.php
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
chmod 644 ./code/footer.php
chmod 644 ./code/forms.php
chmod 644 ./code/views.php
chmod 644 ./code/logbook.php
chmod 644 ./code/common.php
chmod 644 ./code/err_functions.php
chmod 644 ./code/gadgets.php
chmod 644 ./code/logentry.php
chmod 777 ./code/index.php
chmod 644 ./code/config.php
chmod 644 ./code/setup.php
chmod 644 ./code/css.css
chmod 644 ./code/basic.php
chmod 644 ./code/login.php
chmod 600 ./code/tests.php
chmod 700 ./code/attic
chmod 600 ./code/attic/old.html.php
chmod 600 ./code/attic/old.forms.php
chmod 600 ./code/attic/mdefault.php
chmod 600 ./code/attic/l2a.php
chmod 600 ./code/attic/old.fields.php
chmod 777 ./get.php
chmod 700 ./deploy.sh
chmod 700 .git
