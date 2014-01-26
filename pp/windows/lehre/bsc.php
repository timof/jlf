<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Bachelor of Physics (BSc) Programme','Bachelorstudiengang (BSc)' ) );

echo html_tag( 'h2', '', we('Studying in Potsdam','Wahl des Studienortes Potsdam') );

define( 'OPTION_SHOW_MODULES', 1 );
init_var('options','type=u,global=1,sources=http persistent' );

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

echo tb( inlink( 'einschreibung', 'class=href outlink,text='.we('Information on Enrollment', 'Informationen zur Einschreibung') ) );

echo tb( /* 'Tutorium' , */ inlink( 'tutorium', array( 'text' => we(
    'Tutorium for beginners: help and guidance from students for students'
  , "Tutorium f{$uUML}r Studienanf{$aUML}nger: Hilfe und Beratung von Studierenden f{$uUML}r Studierende"
  ) ) )
);

echo tb( html_alink(
  'http://www.uni-potsdam.de/mnfakul/studium/offenermint-raum.html'
  , 'class=href outlink,text=Offener MINT Raum: Lernen mit Hilfe von Kommilitonen'
) );


// echo tb( we('Introductory courses',"Einf{$uUML}hrungsveranstaltungen und Vorkurse")
// , inlink( 'intro', array( 'text' => we('Introductory courses for beginners',"Einf{$uUML}hrungsveranstaltungen und Vorkurse vor Beginn des Vorlesungszeitraums") ) )
// );

echo tb( we('Course guidance for students in BSc/MSc/magister/diploma programme',"Studienfachberatung Physik f{$uUML}r Studierende im BSc/MSc/Magister/Diplom-Studiengang")
       , alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list' )
);


echo html_tag( 'h2', 'medskipt', we('Planning your studies','Planung des Studiums') );


echo tb( we('course directories','Vorlesungsverzeichnisse'), array(
  alink_document_view( array( 'type' => 'VVZ', 'flag_current', 'programme_flags &=' => PROGRAMME_BSC ), 'format=list,default=' )
, inlink( 'vorlesungsverzeichnisse', array( 'text' => we('Archive: course directories of past years...',"Archiv: Vorlesungsverzeichnisse vergangener Jahre...") ) )
), 'class=smallskipb' );

$list = array();
foreach( array( 'SVP', 'MOV', 'MHB', 'SO', 'VUeS', 'INFO' ) as $type ) {
  $s = alink_document_view( array( 'type' => $type, 'flag_current', 'programme_flags &=' => PROGRAMME_BSC ), array( 'format' => 'list', 'default' => NULL ) );
  if( $s ) {
    $list[] = $s;
    // open_li( '', $s );
  }
}
$list[] = inlink( 'ordnungen', array( 'text' => we('older versions...',"{$aUML}ltere Fassungen...") ) );
echo tb( we('Current regulations','Aktuelle Ordnungen'), $list, 'class=smallskipb' );

// if( $options & OPTION_SHOW_MODULES ) {
//   $button = inlink( '', array(
//     'options' => ( $options & ~OPTION_SHOW_MODULES )
//   , 'class' => 'icon close'
//   , 'title' => we('close','ausblenden' )
//   ) );
//   open_fieldset( 'toggle', html_span( 'oneline', $button . we('Modules and contact persons','Module und Modulverantwortliche' ) ) );
//     moduleslist_view( 'programme_flags &= '.PROGRAMME_BSC, 'columns=programme_flags=t=off' );
//   close_fieldset();
// } else {
//   echo tb( inlink( '', array(
//     'options' => ( $options | OPTION_SHOW_MODULES )
//   , 'text' => we('show modules and contact persons...', 'Module und Modulverantwortliche anzeigen...' )
//   ) ) );
// }
 

// echo tb( we('Programme schedule','Studienverlaufsplan' )
//        , alink_document_view( array( 'type' => 'SVP', 'programme_flags &=' => PROGRAMME_BSC ), 'format=latest' )
// );
// 
// echo tb( we('Module manual BSc',"Modulhandbuch BSc")
//        , alink_document_view( array( 'type' => 'MHB', 'programme_flags &=' => PROGRAMME_BSC ), 'format=latest' )
// );
// 
// echo tb( we('Course regulations BSc',"Studienordnung BSc")
//        , alink_document_view( array( 'type' => 'SO', 'programme_flags &=' => PROGRAMME_BSC ), 'format=latest' )
// );
// 
// echo tb( we('Course overview',"Veranstaltungs{$uUML}bersicht")
//        , alink_document_view( array( 'type' => 'VUeS', 'programme_flags &=' => PROGRAMME_BSC ), 'format=latest' )
// );
// 
// echo tb( we('Course directory',"Vorlesungsverzeichnis")
//        , alink_document_view( array( 'type' => 'VVZ' ), 'format=latest' )
// );

echo tb( html_alink( 'http://puls.uni-potsdam.de', array(
  'class' => 'href outlink'
, 'text' => we('Registration for courses and examinations: online portal PULS',"Anmeldung zu Veranstaltungen und Pr{$uUML}fungen: Online-Portal PULS" )
) ) );


echo tb( inlink( 'themen', array( 'programme_flags' => PROGRAMME_BSC, 'text' => we('Topics for Bachelor Theses',"Themenvorschl{$aUML}ge f{$uUML}r Bachelorarbeiten") ) ) );

?>
