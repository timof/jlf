<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Master of Education (MEd) Programme','Lehramtsstudium: Master of Education (MEd)' ) );

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

echo tb( inlink( 'einschreibung', 'class=href outlink,text='.we('Information on Enrollment', 'Informationen zur Einschreibung') ) );

echo tb( we('Course guidance for students in BEd and MEd programme',"Studienfachberatung Physik f{$uUML}r Studierende im Lehramtsstudium (BEd umd MEd)")
       , alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list' )
);


echo html_tag( 'h2', 'medskipt', we('Planning your studies','Planung des Studiums') );

echo tb( we('course directories','Vorlesungsverzeichnisse'), array(
  alink_document_view( array( 'type' => 'VVZ', 'flag_current', 'programme_flags &=' => PROGRAMME_MED ), 'format=list,default=' )
, inlink( 'vorlesungsverzeichnisse', array( 'text' => we('Archive: lecture directories of past years...',"Archiv: Vorlesungsverzeichnisse vergangener Jahre...") ) )
), 'class=smallskipb' );

$list = array();
foreach( array( 'SVP', 'MOV', 'MHB', 'SO', 'VUeS', 'INFO' ) as $type ) {
  $s = alink_document_view( array( 'type' => $type, 'flag_current', 'programme_flags &=' => PROGRAMME_MED ), array( 'format' => 'list', 'default' => NULL ) );
  if( $s ) {
    $list[] = $s;
    // open_li( '', $s );
  }
}
$list[] = inlink( 'ordnungen', array( 'text' => we('older versions...',"{$aUML}ltere Fassungen...") ) );
echo tb( we('Current regulations','Aktuelle Ordnungen'), $list, 'class=smallskipb' );
 

// echo tb( we('Programme schedules',"Studienverlaufspl{$aUML}ne" )
//        , alink_document_view( array( 'type' => 'SVP', 'flag_current', 'programme_flags &=' => PROGRAMME_MED ), 'format=list' )
// );
// 
// echo tb( we('Module manual MEd',"Modulhandbuch MEd")
//        , alink_document_view( array( 'type' => 'MHB', 'programme_flags &=' => PROGRAMME_MED ), 'format=latest' )
// );
// 
// echo tb( we('Course regulations Med',"Studienordnung MEd")
//        , alink_document_view( array( 'type' => 'SO', 'programme_flags &=' => PROGRAMME_MED ), 'format=latest' )
// );
// 
// echo tb( we('Course overview',"Veranstaltungs{$uUML}bersicht")
//        , alink_document_view( array( 'type' => 'VUeS', 'programme_flags &=' => PROGRAMME_MED ), 'format=latest' )
// );
// 
// echo tb( we('Course directory',"Vorlesungsverzeichnis")
//        , alink_document_view( array( 'type' => 'VVZ' ), 'format=latest' )
// );

echo tb( html_alink( 'http://puls.uni-potsdam.de', array(
  'class' => 'href outlink'
, 'text' => we('Registration for courses and examinations: online portal PULS',"Anmeldung zu Veranstaltungen und Pr{$uUML}fungen: Online-Portal PULS" )
) ) );


echo tb( inlink( 'themen', array( 'programme_flags' => PROGRAMME_MED, 'text' => we('Topics for Master Theses',"Themenvorschl{$aUML}ge f{$uUML}r Masterarbeiten") ) ) );

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
