<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Bachelor of Physics (BSc) Programme','Bachelorstudiengang (BSc)' ) );

echo html_tag( 'h2', '', we('Studying in Potsdam','Wahl des Studienortes Potsdam') );


echo we("
  In Potsdam wird das Studienfach Physik als 3-j{$aUML}hriges Bachelorstudium angeboten;
  die Immatrikulation zum 1.{$NBSP}Fachsemester ist im Fach Physik nur zum Beginn eines
  Wintersemesters m{$oUML}glich.
  Das Physikstudium zeichnen sehr gute Betreuungsverh{$aUML}ltnisse und eine angenehme
  Arbeitsatmosph{$aUML}re aus.
","
  In Potsdam wird das Studienfach Physik als 3-j{$aUML}hriges Bachelorstudium angeboten;
  die Immatrikulation zum 1.{$NBSP}Fachsemester ist im Fach Physik nur zum Beginn eines
  Wintersemesters m{$oUML}glich.
  Das Physikstudium zeichnen sehr gute Betreuungsverh{$aUML}ltnisse und eine angenehme
  Arbeitsatmosph{$aUML}re aus.
" );

echo tb( html_alink( 'http://www.uni-potsdam.de/zugang/index.html'
, we('Immatrikulation for the Phycics BSc/MSc Programme in Potsdam','Einschreibung zum Physikstudium in Potsdam') ) );

echo tb( inlink( 'tutorium', array( 'text' => we('Tutorium for beginners',"Tutorium f{$uUML}r Studienanf{$aUML}nger") ) )
         , we( 'Optional tutorial sessions: help and guidance from students for students'
              ,"freiwillige Veranstaltung: Angebot von Hilfe und Beratung von Studierenden f{$uUML}r Studierende" )
);

echo tb( html_alink( 'http://www.uni-potsdam.de/mnfakul/studium/offenermint-raum.html', 'class=href outlink,text=offener MINT Raum' )
         , 'MINT-Raum: Lernen mit Hilfe von Kommilitonen'
);


echo tb( inlink( 'intro', array( 'text' => we('Introductory courses for beginners',"Einf{$uUML}hrungsveranstaltungen und Vorkurse") ) )
        , 'Vorbereitende Veranstaltungen vor Beginn des Vorlesungszeitraumes'
);

$rows = sql_offices( 'board=guidance,function=mono,people_id!=0' );
$p = array();
foreach( $rows as $r ) {
  $p[] = alink_person_view( $r['people_id'], 'office' );
}
echo tb( we('Course guidance for students in BSc/MSc/magister/diploma programme',"Studienfachberatung Physik f{$uUML}r Studierende im BSc/MSc/Magister/Diplom-Studiengang")
       , $p
);


echo html_tag( 'h2', 'medskipt', we('Planning your studies','Planung des Studiums') );


echo tb( we('programme schedule','Studienverlaufsplan' )
       , alink_document_view( array( 'type' => 'SVP', 'programme_id &=' => PROGRAMME_BSC ), 'format=latest' )
);

echo tb( we('summary of courses',"Veranstaltungs{$uUML}bersicht")
       , alink_document_view( array( 'type' => 'VUeS', 'programme_id &=' => PROGRAMME_BSC ), 'format=latest' )
);

echo tb( we('module manual BSc',"Modulhandbuch BSc")
       , alink_document_view( array( 'type' => 'MHB', 'programme_id &=' => PROGRAMME_BSC ), 'format=latest' )
);

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
// _m4_medskip
// _m4_tr
//   _m4_file(
//     /studium/BaMaOrdnung-Physik-20120523-lesefassung.pdf,
//     [[Studienordnung Bachelor/Master Physik (Fassung vom 23.05.2012)]]
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
// 
// 
// _m4_bigskip
// _m4_atable
// 
// _m4_include(bottom.m4)
// 

?>
