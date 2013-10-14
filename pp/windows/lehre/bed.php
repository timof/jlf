<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Bachelor of Education (BEd) Programme','Lehramtsstudium: Bachelor of Education (BEd)' ) );

echo html_tag( 'h2', '', we('Studying in Potsdam','Wahl des Studienortes Potsdam') );

echo "
  Das Lehramtsstudium der Physik an der Uni Potsdam besteht aus einem 3-{$aUML}hrigen Bachelor- und
  einem 2-j{$aUML}hrigen Masterstudiengang.
  Im Bachelorstudium werden fachliche Kenntnisse im Fach Physik einschlie{$SZLIG}lich der spezifischen
  Erkenntnis- und Arbeitsmethoden sowie Kompetenzen der Fachdidaktik erworben, die dazu bef{$aUML}higen,
  einen Sch{$uUML}lerorientierten und wissenschaftlich fundierten Physikunterricht zu gestalten.
  Die Ausbildung in experimenteller Physik erfolgt vorwiegend gemeinsam mit Studierenden ohne
  Lehramtsbezug.
";

echo tb(
  html_alink(
    'http://www.uni-potsdam.de/studium/zugang.html'
  , 'class=href outlink,text='.we('Enrollment for the Phycics Programme in Potsdam','Einschreibung zum Physikstudium in Potsdam')
  )
);

echo tb( 'Tutorium'
, inlink( 'tutorium', array( 'text' => we(
    'Tutorium for beginners: help and guidance from students for students'
  , "Tutorium f{$uUML}r Studienanf{$aUML}nger: Hilfe und Beratung von Studierenden f{$uUML}r Studierende"
  ) ) )
);

echo tb( 'Offener MINT Raum'
, html_alink( 'http://www.uni-potsdam.de/mnfakul/studium/offenermint-raum.html', 'class=href outlink,text=MINT Raum: Lernen mit Hilfe von Kommilitonen' )
);


// echo tb( we('Introductory courses',"Einf{$uUML}hrungsveranstaltungen und Vorkurse")
// , inlink( 'intro', array( 'text' => we('Introductory courses for beginners',"Einf{$uUML}hrungsveranstaltungen und Vorkurse vor Beginn des Vorlesungszeitraums") ) )
// );

echo tb( we('Course guidance for students in BEd and MEd programme',"Studienfachberatung Physik f{$uUML}r Studierende im Lehramtsstudium (BEd umd MEd)")
       , alink_person_view( 'people_id!=0,board=guidance,function=lehramt', 'office=1,format=list' )
);


echo html_tag( 'h2', 'medskipt', we('Planning your studies','Planung des Studiums') );


echo tb( we('Programme schedules',"Studienverlaufspl{$aUML}ne" )
       , alink_document_view( array( 'type' => 'SVP', 'flag_current', 'programme_id &=' => PROGRAMME_BED ), 'format=list' )
);

echo tb( we('Module manual BSc',"Modulhandbuch BSc")
       , alink_document_view( array( 'type' => 'MHB', 'programme_id &=' => PROGRAMME_BED ), 'format=latest' )
);

echo tb( we('Course regulations BSc',"Studienordnung BSc")
       , alink_document_view( array( 'type' => 'SO', 'programme_id &=' => PROGRAMME_BED ), 'format=latest' )
);

echo tb( we('Course overview',"Veranstaltungs{$uUML}bersicht")
       , alink_document_view( array( 'type' => 'VUeS', 'programme_id &=' => PROGRAMME_BED ), 'format=latest' )
);

echo tb( we('Course catalog',"Vorlesungsverzeichnis")
       , alink_document_view( array( 'type' => 'VVZ' ), 'format=latest' )
);

echo tb( we('Registration for courses and examinations',"Anmeldung zu Veranstaltungen und Pr{$uUML}fungen" )
 , html_alink( 'http://puls.uni-potsdam.de', array( 'class' => 'href outlink', 'text' => we('Online portal: PULS','Online-Portal: PULS') ) )
);


echo tb( we('Bachelor Theses','Bachelor Arbeiten')
       , inlink( 'themen', array( 'programme_id' => PROGRAMME_BED, 'text' => we('Topics for Bachelor Theses',"Themenvorschl{$aUML}ge f{$uUML}r Bachelorarbeiten") ) )
);

// _m4_smallskip
// _m4_tr
//   _m4_td
//     _m4_p(class='smallskip')
//        Studienverlaufspl&auml;ne
//     _m4_p(class='smallskip' style='padding-left:2em';)
//       _m4_file(/studium/studienverlauf.BEd.Gym1.pdf,[[Bachelor Gymnasium 1.Fach]])
//     _m4_p(class='smallskip' style='padding-left:2em';)
//       _m4_file(/studium/studienverlauf.BEd.Gym2.pdf,[[Bachelor Gymnasium 2.Fach]])
//     _m4_p(class='smallskip' style='padding-left:2em';)
//       _m4_file(/studium/studienverlauf.BEd.SIP1.pdf,[[Bachelor SIP 1.Fach]])
//     _m4_p(class='smallskip' style='padding-left:2em';)
//       _m4_file(/studium/studienverlauf.BEd.SIP2.pdf,[[Bachelor SIP 2.Fach]])
//     _m4_p(class='smallskip' style='padding-left:2em';)
//       _m4_file(/studium/studienverlauf.MEd.Gym1.pdf,[[Master Gymnasium 1.Fach]])
//     _m4_p(class='smallskip' style='padding-left:2em';)
//       _m4_file(/studium/studienverlauf.MEd.Gym2.pdf,[[Master Gymnasium 2.Fach]])
//     _m4_p(class='smallskip' style='padding-left:2em';)
//       _m4_file(/studium/studienverlauf.MEd.SIP1.pdf,[[Master SIP 1.Fach]])
//     _m4_p(class='smallskip' style='padding-left:2em';)
//       _m4_file(/studium/studienverlauf.MEd.SIP2.pdf,[[Master SIP 2.Fach]])
// 
// _m4_smallskip
// 
// _m4_ifelse([[
// _m4_tr
//   _m4_td
//   _m4_file(lehramt.uebersicht.pdf,Veranstaltungsübersicht)
// ]])
// 
// _m4_tr
//   _m4_file(
//     /studium/Handbuch_LAPhysik_111006.pdf,
//     [[Modulhandbuch Lehramt Physik (Fassung vom 06.10.2011)]]
//   )
// _m4_tr
//   _m4_file(
//     http://theosolid.qipc.org/KomVV_WS2012.pdf,
//     [[_m4_de([[Kommentiertes Vorlesungsverzeichnis: Physik, Wintersemester 2012/13]])_m4_en([[Course Catalog: Physics, Winter term 2012/13]])]]
//   )
// _m4_tr
// _m4_td
//   _m4_inlink(/lehre/belegung.m4,[[Belegen von Lehrveranstaltungen und Anmeldung zu Pr&uuml;fungen]])
// _m4_tr
//   _m4_td
//   _m4_link(/lehre/termine.m4,Wichtige Termine f&uuml;r Studierende am Institut)
// 
// 
// _m4_medskip
// _m4_tr
//   _m4_file(
//     /studium/BAMALAPHYS_2011_amtlich.pdf,
//     [[Studienordnung Bachelor/Master Physik Lehramt (2011)]]
//   )
//   _m4_p(style='padding-left:2em;')
//   _m4_inlink(/lehre/studienordnungen.m4,[[&auml;ltere Fassungen...]])
//   _m4_ap
// 
// 
// _m4_medskip
// _m4_tr
//   _m4_td
//   _m4_inlink(/lehre/themen.bachelor.m4,[[Themenvorschl&auml;ge f&uuml;r Bachelorarbeiten]])
// 
// _m4_tr
//   _m4_td
//   _m4_inlink(/lehre/themen.master.m4,[[Themenvorschl&auml;ge f&uuml;r Masterarbeiten]])
// 
// 
// 
// 
// 
// _m4_bigskip
// _m4_atable
// 
// _m4_include(bottom.m4)
// 

?>
