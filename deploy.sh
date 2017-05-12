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
chmod 600 ./.gitignore
chmod 600 ./README
chmod 755 ./alien
chmod 644 ./alien/prototype.js
chmod 755 ./cluster
chmod 600 ./cluster/1structure.php
chmod 644 ./cluster/basic.php
chmod 755 ./cluster/code
chmod 644 ./cluster/code/common.php
chmod 644 ./cluster/code/config.php
chmod 644 ./cluster/code/err_functions.php
chmod 644 ./cluster/code/forms.php
chmod 644 ./cluster/code/html.php
chmod 644 ./cluster/code/inlinks.php
chmod 644 ./cluster/code/ldap.php
chmod 644 ./cluster/code/views.php
chmod 644 ./cluster/code/zuordnen.php
chmod 644 ./cluster/common.php
chmod 755 ./cluster/css
chmod 644 ./cluster/css/css.css
chmod 644 ./cluster/gadgets.php
chmod 644 ./cluster/html.php
chmod 755 ./cluster/img
chmod 644 ./cluster/img/b_browse.png
chmod 644 ./cluster/img/b_drop.png
chmod 644 ./cluster/img/b_edit.png
chmod 644 ./cluster/img/card.png
chmod 644 ./cluster/img/close_black.gif
chmod 644 ./cluster/img/close_black_hover.gif
chmod 644 ./cluster/img/close_black_trans.gif
chmod 644 ./cluster/img/euro.png
chmod 644 ./cluster/img/green.png
chmod 644 ./cluster/img/minus.png
chmod 644 ./cluster/img/open_black_trans.gif
chmod 644 ./cluster/img/people.png
chmod 644 ./cluster/img/plus.png
chmod 644 ./cluster/img/print_black.gif
chmod 644 ./cluster/img/reload_black.gif
chmod 644 ./cluster/index.php
chmod 644 ./cluster/inlinks.php
chmod 755 ./cluster/js
chmod 644 ./cluster/js/js.js
chmod 644 ./cluster/ldap.php
chmod 644 ./cluster/leitvariable.php
chmod 644 ./cluster/menu.php
chmod 644 ./cluster/mysql.php
chmod 644 ./cluster/physik.schema
chmod 700 ./cluster/scripts
chmod 700 ./cluster/scripts/b7
chmod 644 ./cluster/setup.php
chmod 644 ./cluster/structure.php
chmod 755 ./cluster/textemplates
chmod 644 ./cluster/textemplates/disk.tex
chmod 644 ./cluster/views.php
chmod 755 ./cluster/windows
chmod 644 ./cluster/windows/accountdomainlist.php
chmod 644 ./cluster/windows/accountdomainslist.php
chmod 600 ./cluster/windows/accountlist.php
chmod 644 ./cluster/windows/accountslist.php
chmod 644 ./cluster/windows/asset.php
chmod 644 ./cluster/windows/assetslist.php
chmod 644 ./cluster/windows/backupchunk.php
chmod 644 ./cluster/windows/backupchunkslist.php
chmod 644 ./cluster/windows/backupprofileslist.php
chmod 644 ./cluster/windows/backupslist.php
chmod 644 ./cluster/windows/disk.php
chmod 644 ./cluster/windows/disklist.php
chmod 644 ./cluster/windows/diskslist.php
chmod 644 ./cluster/windows/host.php
chmod 644 ./cluster/windows/hostlist.php
chmod 644 ./cluster/windows/hostslist.php
chmod 644 ./cluster/windows/menu.php
chmod 644 ./cluster/windows/person_view.php
chmod 644 ./cluster/windows/servicelist.php
chmod 644 ./cluster/windows/serviceslist.php
chmod 644 ./cluster/windows/sync.php
chmod 644 ./cluster/windows/tape.php
chmod 644 ./cluster/windows/tapechunkslist.php
chmod 644 ./cluster/windows/tapelist.php
chmod 644 ./cluster/windows/tapeslist.php
chmod 644 ./cluster/windows/userlist.php
chmod 755 ./code
chmod 600 ./code/1list.php
chmod 644 ./code/any_view.php
chmod 644 ./code/anylist.php
chmod 700 ./code/attic
chmod 600 ./code/attic/1css.css
chmod 600 ./code/attic/1gadgets.php
chmod 600 ./code/attic/1mysql.php
chmod 600 ./code/attic/l2a.php
chmod 600 ./code/attic/mdefault.php
chmod 600 ./code/attic/mysql.php
chmod 600 ./code/attic/no-table_person_edit.php
chmod 600 ./code/attic/old.fields.php
chmod 600 ./code/attic/old.forms.php
chmod 600 ./code/attic/old.html.php
chmod 600 ./code/attic/pp.js.rphp
chmod 600 ./code/attic/table_person.php
chmod 644 ./code/basic.php
chmod 600 ./code/changelog.php
chmod 755 ./code/cli
chmod 755 ./code/cli/cli
chmod 744 ./code/cli/cli.sh
chmod 644 ./code/cli/cli_commands.php
chmod 644 ./code/cli/cli_environment.php
chmod 755 ./code/cli/people.sh
chmod 600 ./code/cli/readme
chmod 644 ./code/config.php
chmod 755 ./code/css
chmod 644 ./code/css/css.rphp
chmod 644 ./code/css/floatingstuff.css
chmod 644 ./code/css/generic.css
chmod 644 ./code/css/layout.css
chmod 644 ./code/css/payload.css
chmod 644 ./code/debugentry.php
chmod 644 ./code/debuglist.php
chmod 644 ./code/dynamic_css.php
chmod 644 ./code/environment.php
chmod 644 ./code/err_functions.php
chmod 644 ./code/foot.php
chmod 644 ./code/gadgets.php
chmod 644 ./code/garbage.php
chmod 644 ./code/global.php
chmod 644 ./code/head.php
chmod 644 ./code/html.php
chmod 755 ./code/img
chmod 644 ./code/img/arrow.down.blue.png
chmod 644 ./code/img/arrow.up.blue.png
chmod 644 ./code/img/b_browse.png
chmod 644 ./code/img/b_drop.png
chmod 644 ./code/img/b_edit.png
chmod 644 ./code/img/block.white.gif
chmod 644 ./code/img/broken.small.trans.gif
chmod 644 ./code/img/broken.tiny.trans.gif
chmod 644 ./code/img/browse.202080.gif
chmod 644 ./code/img/browse.202080.over.gif
chmod 644 ./code/img/browse.202080.over.trans.gif
chmod 644 ./code/img/browse.202080.trans.gif
chmod 644 ./code/img/card.png
chmod 644 ./code/img/check.yes.small.grey.gif
chmod 644 ./code/img/close.small.104871.over.trans.gif
chmod 644 ./code/img/close.small.104871.trans.gif
chmod 644 ./code/img/close.small.202080.over.trans.gif
chmod 644 ./code/img/close.small.202080.trans.gif
chmod 644 ./code/img/close.small.active.gif
chmod 644 ./code/img/close.small.blue.gif
chmod 644 ./code/img/close.small.blue.trans.gif
chmod 644 ./code/img/close.small.e08cd0.over.trans.gif
chmod 644 ./code/img/close.small.e08cd0.trans.gif
chmod 644 ./code/img/close.small.ppblue.over.gif
chmod 644 ./code/img/close.small.ppblue.trans.gif
chmod 644 ./code/img/close_black.gif
chmod 644 ./code/img/close_black_hover.gif
chmod 644 ./code/img/close_black_trans.gif
chmod 644 ./code/img/equal.small.blue.active.gif
chmod 644 ./code/img/equal.small.blue.gif
chmod 644 ./code/img/equal.small.blue.trans.gif
chmod 644 ./code/img/euro.png
chmod 644 ./code/img/euro.small.blue.trans.gif
chmod 644 ./code/img/fant.gif
chmod 644 ./code/img/fant.over.trans.gif
chmod 644 ./code/img/fant.trans.gif
chmod 644 ./code/img/file.104871.over.trans.gif
chmod 644 ./code/img/file.104871.trans.gif
chmod 644 ./code/img/file.202080.over.trans.gif
chmod 644 ./code/img/file.202080.trans.gif
chmod 644 ./code/img/file.e08cd0.over.trans.gif
chmod 644 ./code/img/file.e08cd0.trans.gif
chmod 644 ./code/img/file.gif
chmod 644 ./code/img/file.over.gif
chmod 644 ./code/img/file.ppblue.over.trans.gif
chmod 644 ./code/img/file.ppblue.trans.gif
chmod 644 ./code/img/fork_black.gif
chmod 644 ./code/img/ggt.white.gif
chmod 644 ./code/img/green.png
chmod 644 ./code/img/gt.white.gif
chmod 644 ./code/img/home_black.gif
chmod 644 ./code/img/il.gif
chmod 644 ./code/img/il.over.gif
chmod 644 ./code/img/inlink.gif
chmod 644 ./code/img/inlink.over.gif
chmod 644 ./code/img/letter.gif
chmod 644 ./code/img/letter.over.gif
chmod 644 ./code/img/lock.trans.gif
chmod 644 ./code/img/minus.png
chmod 644 ./code/img/ol.gif
chmod 644 ./code/img/ol.over.gif
chmod 644 ./code/img/open.small.active.gif
chmod 644 ./code/img/open.small.blue.gif
chmod 644 ./code/img/open.small.blue.trans.gif
chmod 644 ./code/img/open_black_trans.gif
chmod 644 ./code/img/outlink.104871.over.trans.gif
chmod 644 ./code/img/outlink.104871.trans.gif
chmod 644 ./code/img/outlink.202080.over.trans.gif
chmod 644 ./code/img/outlink.202080.trans.gif
chmod 644 ./code/img/outlink.e08cd0.over.trans.gif
chmod 644 ./code/img/outlink.e08cd0.trans.gif
chmod 644 ./code/img/outlink.gif
chmod 644 ./code/img/outlink.over.gif
chmod 644 ./code/img/outlink.ppblue.over.trans.gif
chmod 644 ./code/img/outlink.ppblue.trans.gif
chmod 644 ./code/img/people.blue.trans.gif
chmod 644 ./code/img/people.png
chmod 644 ./code/img/plus.blue.png
chmod 644 ./code/img/plus.png
chmod 644 ./code/img/plus.small.blue.active.gif
chmod 644 ./code/img/plus.small.blue.gif
chmod 644 ./code/img/plus.small.blue.trans.gif
chmod 644 ./code/img/print_black.gif
chmod 644 ./code/img/reload_black.gif
chmod 644 ./code/img/sort.down.2.trans.gif
chmod 644 ./code/img/sort.down.3.trans.gif
chmod 644 ./code/img/sort.down.trans.gif
chmod 644 ./code/img/sort.up.2.trans.gif
chmod 644 ./code/img/sort.up.3.trans.gif
chmod 644 ./code/img/sort.up.trans.gif
chmod 644 ./code/img/warp0.trans.gif
chmod 644 ./code/img/warp1.trans.gif
chmod 644 ./code/img/warp2.trans.gif
chmod 644 ./code/img/warp3.trans.gif
chmod 644 ./code/img/warp4.trans.gif
chmod 644 ./code/img/warp5.trans.gif
chmod 644 ./code/img/warp6.trans.gif
chmod 644 ./code/img/warp7.trans.gif
chmod 644 ./code/img/warp8.trans.gif
chmod 644 ./code/img/warp9.trans.gif
chmod 644 ./code/inlinks.php
chmod 755 ./code/js
chmod 600 ./code/js/attic.rphp
chmod 644 ./code/js/js.rphp
chmod 644 ./code/ldap.php
chmod 644 ./code/leitvariable.php
chmod 644 ./code/lists.php
chmod 644 ./code/logbook.php
chmod 644 ./code/logentry.php
chmod 644 ./code/login.php
chmod 644 ./code/maintenance.php
chmod 644 ./code/mysql.php
chmod 644 ./code/persistentvars.php
chmod 644 ./code/profile.php
chmod 644 ./code/profileentry.php
chmod 644 ./code/references.php
chmod 644 ./code/robots.php
chmod 644 ./code/session.php
chmod 644 ./code/sessions.php
chmod 644 ./code/start.php
chmod 644 ./code/structure.php
chmod 644 ./code/tests.php
chmod 644 ./code/tex2pdf.php
chmod 755 ./code/textemplates
chmod 644 ./code/textemplates/prettytables.tex
chmod 644 ./code/textemplates/texhead.tex
chmod 644 ./code/textemplates/texlist.tex
chmod 644 ./code/views.php
chmod 700 ./deploy.sh
chmod 700 ./doc
chmod 600 ./doc/conventions.txt
chmod 700 ./foodsoft
chmod 600 ./foodsoft/common.php
chmod 755 ./foodsoft/foodsoft-git
chmod 644 ./foodsoft/foodsoft-git/INSTALL
chmod 644 ./foodsoft/foodsoft-git/README.md
chmod 600 ./foodsoft/foodsoft-git/ToDo.txt
chmod 755 ./foodsoft/foodsoft-git/antixls.modif
chmod 644 ./foodsoft/foodsoft-git/apache.sample.conf
chmod 755 ./foodsoft/foodsoft-git/code
chmod 644 ./foodsoft/foodsoft-git/code/common.php
chmod 644 ./foodsoft/foodsoft-git/code/config.php
chmod 644 ./foodsoft/foodsoft-git/code/err_functions.php
chmod 644 ./foodsoft/foodsoft-git/code/forms.php
chmod 644 ./foodsoft/foodsoft-git/code/html.php
chmod 644 ./foodsoft/foodsoft-git/code/inlinks.php
chmod 644 ./foodsoft/foodsoft-git/code/katalogsuche.php
chmod 644 ./foodsoft/foodsoft-git/code/login.php
chmod 644 ./foodsoft/foodsoft-git/code/views.php
chmod 755 ./foodsoft/foodsoft-git/css
chmod 644 ./foodsoft/foodsoft-git/css/foodsoft.css
chmod 644 ./foodsoft/foodsoft-git/css/modified.gif
chmod 644 ./foodsoft/foodsoft-git/css/print.css
chmod 644 ./foodsoft/foodsoft-git/css/readonly.gif
chmod 700 ./foodsoft/foodsoft-git/deploy.sh
chmod 644 ./foodsoft/foodsoft-git/dump.php
chmod 600 ./foodsoft/foodsoft-git/fcck.php
chmod 644 ./foodsoft/foodsoft-git/files_und_skripte
chmod 644 ./foodsoft/foodsoft-git/foodsoft.class.php
chmod 644 ./foodsoft/foodsoft-git/head.php
chmod 755 ./foodsoft/foodsoft-git/img
chmod 644 ./foodsoft/foodsoft-git/img/arrow.down.blue.png
chmod 644 ./foodsoft/foodsoft-git/img/arrow.up.blue.png
chmod 644 ./foodsoft/foodsoft-git/img/b_browse.png
chmod 644 ./foodsoft/foodsoft-git/img/b_drop.png
chmod 644 ./foodsoft/foodsoft-git/img/b_edit.png
chmod 644 ./foodsoft/foodsoft-git/img/birne_rot.png
chmod 644 ./foodsoft/foodsoft-git/img/card.png
chmod 644 ./foodsoft/foodsoft-git/img/chalk_trans.gif
chmod 644 ./foodsoft/foodsoft-git/img/chart.png
chmod 644 ./foodsoft/foodsoft-git/img/close_black.gif
chmod 644 ./foodsoft/foodsoft-git/img/close_black_hover.gif
chmod 644 ./foodsoft/foodsoft-git/img/close_black_trans.gif
chmod 644 ./foodsoft/foodsoft-git/img/euro.png
chmod 644 ./foodsoft/foodsoft-git/img/fant.gif
chmod 644 ./foodsoft/foodsoft-git/img/gluehbirne_15x16.png
chmod 644 ./foodsoft/foodsoft-git/img/green.png
chmod 644 ./foodsoft/foodsoft-git/img/magic_wand.png
chmod 644 ./foodsoft/foodsoft-git/img/minus.png
chmod 644 ./foodsoft/foodsoft-git/img/open_black_trans.gif
chmod 644 ./foodsoft/foodsoft-git/img/people.png
chmod 644 ./foodsoft/foodsoft-git/img/plus.png
chmod 644 ./foodsoft/foodsoft-git/img/print_black.gif
chmod 644 ./foodsoft/foodsoft-git/img/question.png
chmod 644 ./foodsoft/foodsoft-git/img/question_small.png
chmod 644 ./foodsoft/foodsoft-git/img/reload_black.gif
chmod 644 ./foodsoft/foodsoft-git/index.php
chmod 755 ./foodsoft/foodsoft-git/js
chmod 644 ./foodsoft/foodsoft-git/js/foodsoft.js
chmod 755 ./foodsoft/foodsoft-git/js/lib
chmod 644 ./foodsoft/foodsoft-git/js/lib/prototype.js
chmod 644 ./foodsoft/foodsoft-git/js/tooltip.js
chmod 644 ./foodsoft/foodsoft-git/leitvariable.php
chmod 644 ./foodsoft/foodsoft-git/links_und_parameter
chmod 700 ./foodsoft/foodsoft-git/pre-commit
chmod 755 ./foodsoft/foodsoft-git/setup.php
chmod 644 ./foodsoft/foodsoft-git/structure.php
chmod 644 ./foodsoft/foodsoft-git/version.txt
chmod 755 ./foodsoft/foodsoft-git/windows
chmod 644 ./foodsoft/foodsoft-git/windows/abrechnung.php
chmod 644 ./foodsoft/foodsoft-git/windows/abschluss.php
chmod 644 ./foodsoft/foodsoft-git/windows/artikelsuche.php
chmod 644 ./foodsoft/foodsoft-git/windows/basar.php
chmod 644 ./foodsoft/foodsoft-git/windows/bestellen.php
chmod 644 ./foodsoft/foodsoft-git/windows/bestellen.php.neu
chmod 644 ./foodsoft/foodsoft-git/windows/bestellschein.php
chmod 644 ./foodsoft/foodsoft-git/windows/bestellungen.php
chmod 644 ./foodsoft/foodsoft-git/windows/bilanz.php
chmod 644 ./foodsoft/foodsoft-git/windows/dienstkontrollblatt.php
chmod 644 ./foodsoft/foodsoft-git/windows/dienstplan.php
chmod 644 ./foodsoft/foodsoft-git/windows/editBestellung.php
chmod 644 ./foodsoft/foodsoft-git/windows/editBuchung.php
chmod 644 ./foodsoft/foodsoft-git/windows/editKonto.php
chmod 644 ./foodsoft/foodsoft-git/windows/editLieferant.php
chmod 644 ./foodsoft/foodsoft-git/windows/editProdukt.php
chmod 644 ./foodsoft/foodsoft-git/windows/editProduktgruppe.php
chmod 644 ./foodsoft/foodsoft-git/windows/editVerpackung.php
chmod 644 ./foodsoft/foodsoft-git/windows/gesamtlieferschein.php
chmod 644 ./foodsoft/foodsoft-git/windows/gruppen.php
chmod 644 ./foodsoft/foodsoft-git/windows/gruppenkonto.php
chmod 644 ./foodsoft/foodsoft-git/windows/gruppenmitglieder.php
chmod 644 ./foodsoft/foodsoft-git/windows/gruppenpfand.php
chmod 644 ./foodsoft/foodsoft-git/windows/head.php
chmod 644 ./foodsoft/foodsoft-git/windows/katalog_upload.php
chmod 644 ./foodsoft/foodsoft-git/windows/konto.php
chmod 644 ./foodsoft/foodsoft-git/windows/lieferanten.php
chmod 644 ./foodsoft/foodsoft-git/windows/lieferantenkonto.php
chmod 644 ./foodsoft/foodsoft-git/windows/menu.php
chmod 644 ./foodsoft/foodsoft-git/windows/pfandverpackungen.php
chmod 644 ./foodsoft/foodsoft-git/windows/produkte.php
chmod 644 ./foodsoft/foodsoft-git/windows/produktpreise.php
chmod 644 ./foodsoft/foodsoft-git/windows/produktverteilung.php
chmod 644 ./foodsoft/foodsoft-git/windows/updownload.php
chmod 644 ./foodsoft/foodsoft-git/windows/verluste.php
chmod 644 ./foodsoft/foodsoft.class.php
chmod 644 ./foodsoft/inlinks.php
chmod 600 ./foodsoft/mysql.php
chmod 600 ./foodsoft/structure.php
chmod 700 ./htmlDefuse
chmod 644 ./index.php
chmod 755 ./pi
chmod 644 ./pi/basic.php
chmod 644 ./pi/cli_commands.php
chmod 755 ./pi/css
chmod 644 ./pi/css/css.rphp
chmod 644 ./pi/css/payload_shared.css
chmod 644 ./pi/forms.php
chmod 644 ./pi/gadgets.php
chmod 644 ./pi/garbage.php
chmod 644 ./pi/html.php
chmod 644 ./pi/inlinks.php
chmod 755 ./pi/public
chmod 755 ./pi/shared
chmod 644 ./pi/shared/basic.php
chmod 644 ./pi/shared/common.php
chmod 644 ./pi/shared/leitvariable.php
chmod 644 ./pi/shared/mysql.php
chmod 644 ./pi/shared/structure.php
chmod 644 ./pi/shared/views.php
chmod 755 ./pi/textemplates
chmod 644 ./pi/textemplates/applicant.tex
chmod 644 ./pi/textemplates/position.tex
chmod 644 ./pi/textemplates/publication.tex
chmod 644 ./pi/textemplates/room.tex
chmod 644 ./pi/views.php
chmod 755 ./pi/windows
chmod 644 ./pi/windows/admin.php
chmod 777 ./pi/windows/applicant_view.php
chmod 644 ./pi/windows/applicantslist.php
chmod 644 ./pi/windows/configuration.php
chmod 744 ./pi/windows/document_edit.php
chmod 777 ./pi/windows/document_view.php
chmod 644 ./pi/windows/documentslist.php
chmod 644 ./pi/windows/download.php
chmod 644 ./pi/windows/event_edit.php
chmod 644 ./pi/windows/event_view.php
chmod 644 ./pi/windows/eventslist.php
chmod 644 ./pi/windows/exam_edit.php
chmod 644 ./pi/windows/exam_view.php
chmod 744 ./pi/windows/examslist.php
chmod 644 ./pi/windows/group_edit.php
chmod 777 ./pi/windows/group_view.php
chmod 644 ./pi/windows/groupslist.php
chmod 644 ./pi/windows/highlight_edit.php
chmod 644 ./pi/windows/highlight_view.php
chmod 644 ./pi/windows/highlightslist.php
chmod 644 ./pi/windows/menu.php
chmod 644 ./pi/windows/module_edit.php
chmod 644 ./pi/windows/module_view.php
chmod 644 ./pi/windows/moduleslist.php
chmod 644 ./pi/windows/peoplelist.php
chmod 644 ./pi/windows/person_edit.php
chmod 777 ./pi/windows/person_view.php
chmod 744 ./pi/windows/position_edit.php
chmod 777 ./pi/windows/position_view.php
chmod 644 ./pi/windows/positionslist.php
chmod 644 ./pi/windows/publication_edit.php
chmod 644 ./pi/windows/publication_view.php
chmod 644 ./pi/windows/publicationslist.php
chmod 644 ./pi/windows/room_edit.php
chmod 644 ./pi/windows/room_view.php
chmod 644 ./pi/windows/roomslist.php
chmod 644 ./pi/windows/survey_edit.php
chmod 644 ./pi/windows/survey_view.php
chmod 644 ./pi/windows/surveyslist.php
chmod 644 ./pi/windows/teachers.php
chmod 644 ./pi/windows/teaching_edit.php
chmod 644 ./pi/windows/teachinganon.php
chmod 644 ./pi/windows/teachinglist.php
chmod 644 ./pi/windows/teaser_edit.php
chmod 644 ./pi/windows/teaserlist.php
chmod 755 ./pp
chmod 600 ./pp/.gitignore
chmod 644 ./pp/basic.php
chmod 755 ./pp/code
chmod 600 ./pp/code/1css.css
chmod 644 ./pp/code/common.php
chmod 644 ./pp/code/forms.php
chmod 644 ./pp/code/gadgets.php
chmod 644 ./pp/code/index.php
chmod 644 ./pp/code/js.js
chmod 644 ./pp/code/js.rphp
chmod 644 ./pp/code/map.php
chmod 644 ./pp/code/views.php
chmod 755 ./pp/css
chmod 644 ./pp/css/css.rphp
chmod 644 ./pp/css/payload.css
chmod 700 ./pp/deploy.sh
chmod 755 ./pp/docs
chmod 644 ./pp/dynamic_css.php
chmod 644 ./pp/faknav.php
chmod 644 ./pp/foot.php
chmod 644 ./pp/forms.php
chmod 755 ./pp/fotos
chmod 644 ./pp/fotos/astro.jpg
chmod 644 ./pp/fotos/astrophysik.jpg
chmod 644 ./pp/fotos/crescent_small.jpg
chmod 644 ./pp/fotos/didaktik.jpg
chmod 644 ./pp/fotos/focus.gif
chmod 644 ./pp/fotos/forschung2.jpg
chmod 644 ./pp/fotos/forum1.jpg
chmod 644 ./pp/fotos/general_nld.png
chmod 644 ./pp/fotos/in_the_lab.jpg
chmod 644 ./pp/fotos/lehre2.jpg
chmod 644 ./pp/fotos/nopa_mareike.jpg
chmod 644 ./pp/fotos/osm.haus28.tiny.gif
chmod 644 ./pp/fotos/photonik1.gif
chmod 644 ./pp/fotos/prisoner.gif
chmod 644 ./pp/fotos/prisoner.jpg
chmod 644 ./pp/fotos/pwm.gif
chmod 644 ./pp/fotos/reflexion.gif
chmod 644 ./pp/fotos/tutorium3.jpg
chmod 644 ./pp/fotos/wigner.gif
chmod 644 ./pp/fotos/wigner2.gif
chmod 644 ./pp/gadgets.php
chmod 644 ./pp/head.php
chmod 644 ./pp/html.php
chmod 755 ./pp/img
chmod 644 ./pp/img/bannerjura.gif
chmod 644 ./pp/img/bannermatnat.gif
chmod 644 ./pp/img/bannernull.gif
chmod 644 ./pp/img/bannerphil1.gif
chmod 644 ./pp/img/bannerphil2.gif
chmod 644 ./pp/img/bannerwiso.gif
chmod 644 ./pp/img/bbio.gif
chmod 644 ./pp/img/bchem.gif
chmod 644 ./pp/img/bdots.gif
chmod 644 ./pp/img/bfood.gif
chmod 644 ./pp/img/bggraph.gif
chmod 644 ./pp/img/bgoek.gif
chmod 644 ./pp/img/bgwiss.gif
chmod 644 ./pp/img/binfo.gif
chmod 644 ./pp/img/bizp.gif
chmod 644 ./pp/img/bmath.gif
chmod 644 ./pp/img/bnull.gif
chmod 644 ./pp/img/bo_int.jpg
chmod 644 ./pp/img/bo_mail.jpg
chmod 644 ./pp/img/bo_suche.jpg
chmod 644 ./pp/img/bphys.gif
chmod 644 ./pp/img/bphysastro.gif
chmod 644 ./pp/img/fakjura.gif
chmod 644 ./pp/img/fakmatnat.gif
chmod 644 ./pp/img/faknull.gif
chmod 644 ./pp/img/fakphil1.gif
chmod 644 ./pp/img/fakphil2.gif
chmod 644 ./pp/img/fakwiso.gif
chmod 644 ./pp/img/haus28innen.gif
chmod 644 ./pp/img/teaser.1.jpg
chmod 644 ./pp/inlinks.php
chmod 755 ./pp/js
chmod 644 ./pp/js/js.rphp
chmod 644 ./pp/media.php
chmod 644 ./pp/outlinks.php
chmod 755 ./pp/pp
chmod 644 ./pp/pp/css.rphp
chmod 700 ./pp/pp/deploy.sh
chmod 644 ./pp/pp/faknav.php
chmod 644 ./pp/pp/footer.php
chmod 755 ./pp/pp/fotos
chmod 644 ./pp/pp/fotos/astro.jpg
chmod 644 ./pp/pp/fotos/focus.gif
chmod 644 ./pp/pp/fotos/in_the_lab.jpg
chmod 644 ./pp/pp/fotos/neher03.gif
chmod 644 ./pp/pp/fotos/prisoner.gif
chmod 644 ./pp/pp/fotos/prisoner.jpg
chmod 644 ./pp/pp/fotos/reflexion.gif
chmod 644 ./pp/pp/fotos/wigner.gif
chmod 644 ./pp/pp/fotos/wigner2.gif
chmod 644 ./pp/pp/gadgets.php
chmod 644 ./pp/pp/head.php
chmod 644 ./pp/pp/html.php
chmod 755 ./pp/pp/img
chmod 644 ./pp/pp/img/bannerjura.gif
chmod 644 ./pp/pp/img/bannermatnat.gif
chmod 644 ./pp/pp/img/bannernull.gif
chmod 644 ./pp/pp/img/bannerphil1.gif
chmod 644 ./pp/pp/img/bannerphil2.gif
chmod 644 ./pp/pp/img/bannerwiso.gif
chmod 644 ./pp/pp/img/bbio.gif
chmod 644 ./pp/pp/img/bchem.gif
chmod 644 ./pp/pp/img/bdots.gif
chmod 644 ./pp/pp/img/bfood.gif
chmod 644 ./pp/pp/img/bggraph.gif
chmod 644 ./pp/pp/img/bgoek.gif
chmod 644 ./pp/pp/img/bgwiss.gif
chmod 644 ./pp/pp/img/binfo.gif
chmod 644 ./pp/pp/img/bizp.gif
chmod 644 ./pp/pp/img/bmath.gif
chmod 644 ./pp/pp/img/bnull.gif
chmod 644 ./pp/pp/img/bo_int.jpg
chmod 644 ./pp/pp/img/bo_mail.jpg
chmod 644 ./pp/pp/img/bo_suche.jpg
chmod 644 ./pp/pp/img/bphys.gif
chmod 644 ./pp/pp/img/bphysastro.gif
chmod 644 ./pp/pp/img/fakjura.gif
chmod 644 ./pp/pp/img/fakmatnat.gif
chmod 644 ./pp/pp/img/faknull.gif
chmod 644 ./pp/pp/img/fakphil1.gif
chmod 644 ./pp/pp/img/fakphil2.gif
chmod 644 ./pp/pp/img/fakwiso.gif
chmod 644 ./pp/pp/inlinks.php
chmod 644 ./pp/pp/js.rphp
chmod 644 ./pp/pp/map.php
chmod 644 ./pp/pp/media.php
chmod 644 ./pp/pp/outlinks.php
chmod 644 ./pp/pp/sidenav.php
chmod 644 ./pp/pp/uninav.php
chmod 644 ./pp/pp/views.php
chmod 755 ./pp/pp/windows
chmod 755 ./pp/pp/windows/institut
chmod 644 ./pp/pp/windows/institut/gruppe.php
chmod 644 ./pp/pp/windows/institut/gruppen.php
chmod 644 ./pp/pp/windows/institut/impressum.php
chmod 644 ./pp/pp/windows/institut/institut.php
chmod 644 ./pp/pp/windows/institut/irat.php
chmod 644 ./pp/pp/windows/institut/pruefungsausschuss.php
chmod 755 ./pp/pp/windows/menu
chmod 644 ./pp/pp/windows/menu.php
chmod 644 ./pp/pp/windows/menu/menu.php
chmod 755 ./pp/pp/windows/mitarbeiter
chmod 644 ./pp/pp/windows/mitarbeiter/mitarbeiter.php
chmod 644 ./pp/pp/windows/mitarbeiter/visitenkarte.php
chmod 755 ./pp/pp/windows/professuren
chmod 644 ./pp/pp/windows/professuren/professuren.php
chmod 700 ./pp/pre-commit
chmod 644 ./pp/schwerpunkte.php
chmod 644 ./pp/sidenav.php
chmod 644 ./pp/start.php
chmod 644 ./pp/uninav.php
chmod 644 ./pp/version.txt
chmod 644 ./pp/views.php
chmod 755 ./pp/windows
chmod 755 ./pp/windows/download
chmod 644 ./pp/windows/download/download.php
chmod 644 ./pp/windows/download/ordnungen.php
chmod 644 ./pp/windows/download/vorlesungsverzeichnisse.php
chmod 755 ./pp/windows/forschung
chmod 600 ./pp/windows/forschung/1forschung.php
chmod 644 ./pp/windows/forschung/forschung.php
chmod 644 ./pp/windows/forschung/publikation.php
chmod 644 ./pp/windows/forschung/publikationen.php
chmod 644 ./pp/windows/forschung/schwerpunkte.php
chmod 644 ./pp/windows/forschung/thema.php
chmod 644 ./pp/windows/forschung/themen.php
chmod 755 ./pp/windows/institut
chmod 644 ./pp/windows/institut/impressum.php
chmod 644 ./pp/windows/institut/institut.php
chmod 644 ./pp/windows/institut/irat.php
chmod 644 ./pp/windows/institut/labore.php
chmod 644 ./pp/windows/institut/pruefungsausschuss.php
chmod 644 ./pp/windows/institut/veranstaltung.php
chmod 644 ./pp/windows/institut/veranstaltungsarchiv.php
chmod 755 ./pp/windows/lehre
chmod 600 ./pp/windows/lehre/1einschreibung.php
chmod 600 ./pp/windows/lehre/b2
chmod 644 ./pp/windows/lehre/bed.php
chmod 644 ./pp/windows/lehre/bsc.php
chmod 644 ./pp/windows/lehre/diplom.php
chmod 644 ./pp/windows/lehre/einschreibung.php
chmod 644 ./pp/windows/lehre/intro.php
chmod 644 ./pp/windows/lehre/lehre.php
chmod 644 ./pp/windows/lehre/mastro.php
chmod 644 ./pp/windows/lehre/med.php
chmod 644 ./pp/windows/lehre/modul.php
chmod 644 ./pp/windows/lehre/msc.php
chmod 644 ./pp/windows/lehre/phd.php
chmod 644 ./pp/windows/lehre/praktika.php
chmod 644 ./pp/windows/lehre/studiengaenge.php
chmod 644 ./pp/windows/lehre/studierendenvertretung.php
chmod 644 ./pp/windows/lehre/terminelehre.php
chmod 644 ./pp/windows/lehre/tutorium.php
chmod 755 ./pp/windows/menu
chmod 600 ./pp/windows/menu/attic.items
chmod 644 ./pp/windows/menu/menu.php
chmod 755 ./pp/windows/mitarbeiter
chmod 644 ./pp/windows/mitarbeiter/mitarbeiter.php
chmod 644 ./pp/windows/mitarbeiter/visitenkarte.php
chmod 755 ./pp/windows/professuren
chmod 644 ./pp/windows/professuren/gruppe.php
chmod 644 ./pp/windows/professuren/professuren.php
chmod 700 ./pre-commit
chmod 644 ./robots-deny.txt
chmod 644 ./setup.rphp
chmod 755 ./sus
chmod 644 ./sus/basic.php
chmod 644 ./sus/common.php
chmod 755 ./sus/css
chmod 644 ./sus/css/css.rphp
chmod 644 ./sus/forms.php
chmod 644 ./sus/gadgets.php
chmod 755 ./sus/hbci
chmod 644 ./sus/hbci/muster.gls.csv
chmod 644 ./sus/hbci/muster.mbs.csv
chmod 644 ./sus/hgb_klassen.php
chmod 755 ./sus/img
chmod 644 ./sus/img/dilbert.5652.gif
chmod 644 ./sus/inlinks.php
chmod 644 ./sus/leitvariable.php
chmod 644 ./sus/mysql.php
chmod 755 ./sus/public
chmod 644 ./sus/structure.php
chmod 644 ./sus/views.php
chmod 755 ./sus/windows
chmod 644 ./sus/windows/buchung.php
chmod 755 ./sus/windows/config.php
chmod 644 ./sus/windows/darlehen.php
chmod 644 ./sus/windows/darlehenliste.php
chmod 644 ./sus/windows/geschaeftsjahre.php
chmod 644 ./sus/windows/hauptkonten.php
chmod 644 ./sus/windows/hauptkontenliste.php
chmod 644 ./sus/windows/hauptkonto.php
chmod 744 ./sus/windows/journal.php
chmod 644 ./sus/windows/ka.php
chmod 644 ./sus/windows/kontenrahmen.php
chmod 644 ./sus/windows/meinkonto.php
chmod 644 ./sus/windows/menu.php
chmod 644 ./sus/windows/person.php
chmod 644 ./sus/windows/personen.php
chmod 644 ./sus/windows/posten.php
chmod 644 ./sus/windows/thing.php
chmod 644 ./sus/windows/things.php
chmod 644 ./sus/windows/unterkontenliste.php
chmod 644 ./sus/windows/unterkonto.php
chmod 644 ./sus/windows/zahlungsplan.php
chmod 644 ./sus/windows/zahlungsplanliste.php
chmod 644 ./version.txt
