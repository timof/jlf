<?php

sql_transaction_boundary('*');

define( 'OPTION_SHOW_MODULES', 1 );
init_var('options','type=u,global=1,sources=http persistent,set_scopes=script' );

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('lehre');
    echo html_tag( 'h1', '', we('Studies / BSc Physics','Lehre / BSc Physik' ) );
  close_div();
close_div();

open_ccbox('', we('Bachelor of Science (BSc) in physics','Bachelorstudiengang (BSc) Physik' ) );


echo tb( inlink( 'studiengaenge', 'anchor=bscmsc,text='.we('For prospective Students: Information on admission and enrollment', "Für Studieninteressierte: Informationen zu Zulassung und Einschreibung" ) ) );

echo tb( html_alink( 'http://www.uni-potsdam.de/studium/studienangebot/bachelor/ein-fach-bachelor/physik.html',
  'class=href outlink,text='.we('General information on the program', "{$UUML}berblicksseite der Universität zum Studiengang" ) ) );

echo tb( /* 'Tutorium' , */ inlink( 'tutorium', array( 'text' => we(
    'Tutorials: help and guidance from students for students'
  , "Gemeinsam Lernen - Hilfe und Beratung von Studierenden f{$uUML}r Studierende"
  ) ) )
);


// echo tb( we('Introductory courses',"Einf{$uUML}hrungsveranstaltungen und Vorkurse")
// , inlink( 'intro', array( 'text' => we('Introductory courses for beginners',"Einf{$uUML}hrungsveranstaltungen und Vorkurse vor Beginn des Vorlesungszeitraums") ) )
// );

echo tb( we('Course guidance for students in BSc/MSc/magister/diploma program',"Studienfachberatung Physik f{$uUML}r Studierende im BSc/MSc/Magister/Diplom-Studiengang")
       , alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list' )
);


// echo html_tag( 'h2', 'medskipt', we('Planning your studies','Planung des Studiums') );


echo tb( we('course directories','Vorlesungsverzeichnisse'), array(
  alink_document_view( array( 'type' => 'VVZ', 'flag_current', 'programme_flags &=' => PROGRAMME_BSC ), 'format=list,default=,class=smallskipb' )
, inlink( 'vorlesungsverzeichnisse', array( 'text' => we('Archive: course directories of past years...',"Archiv: Vorlesungsverzeichnisse vergangener Jahre...") ) )
), 'class=smallskipb' );

$list = array();
foreach( array( 'LF', 'SVP', 'MOV', 'MHB', 'SO', 'VUeS' ) as $type ) {
  $s = alink_document_view( array( 'type' => $type, 'flag_current', 'programme_flags &=' => PROGRAMME_BSC ), array( 'format' => 'list', 'default' => NULL ) );
  if( $s ) {
    $list[] = $s;
    // open_li( '', $s );
  }
}
$list[] = inlink( 'ordnungen', array( 'text' => we('Archive: older versions...',"Archiv: {$aUML}ltere Fassungen...") ) );
echo tb( we('Current regulations','Aktuelle Ordnungen'), $list, 'class=smallskipb' );

// if( $options & OPTION_SHOW_MODULES ) {
//   $button = inlink( '', array(
//     'options' => ( $options & ~OPTION_SHOW_MODULES )
//   , 'class' => 'icon close qpadr'
//   , 'title' => we('close','ausblenden' )
//   , 'text' => ''
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
 

echo tb( html_alink( 'http://puls.uni-potsdam.de', array(
  'class' => 'href outlink'
, 'text' => we('Registration for courses and examinations: online portal PULS',"Anmeldung zu Veranstaltungen und Pr{$uUML}fungen: Online-Portal PULS" )
) ) );


// echo tb( inlink( 'themen', array( 'programme_flags' => PROGRAMME_BSC, 'text' => we('Topics for Bachelor Theses',"Themenvorschl{$aUML}ge f{$uUML}r Bachelorarbeiten") ) ) );

close_ccbox();

?>
