<?php

sql_transaction_boundary('*');

define( 'OPTION_SHOW_MODULES', 1 );
init_var('options','type=u,global=1,sources=http persistent,set_scopes=script' );

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('lehre');
    echo html_tag( 'h1', '', we('Studies / MSc Physics','Lehre / MSc Physik' ) );
  close_div();
close_div();

open_ccbox( '', we('Master of Science (MSc) in physics','Studiengang: Master of Science (MSc) in Physik' ) );

// echo html_tag( 'h2', '', we('Studying in Potsdam','Wahl des Studienortes Potsdam') );


echo tb( inlink( 'einschreibung', 'anchor=bscmsc,text='.we('For prospective Students: Information on admission and enrollment', "Für Studieninteressierte: Informationen zu Zulassung und Einschreibung" ) ) );

echo tb( html_alink( 'http://www.uni-potsdam.de/studium/studienangebot/masterstudium/master-a-z/physik-master.html'
  , 'class=href outlink,text='.we('General information on the program', "{$UUML}berblicksseite der Universität zum Studiengang" ) ) );

$s = alink_document_view( array( 'tag' => 'flyer_msc', 'flag_current', 'programme_flags &=' => PROGRAMME_MSC ), array( 'format' => 'list', 'default' => NULL ) );
if( $s ) {
  echo tb( $s );
}

echo tb( we('Course guidance for students in BSc/MSc/magister/diploma program',"Studienfachberatung Physik f{$uUML}r Studierende im BSc/MSc/Magister/Diplom-Studiengang")
       , alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list' )
);


// echo html_tag( 'h2', 'medskipt', we('Planning your studies','Planung des Studiums') );

echo tb( we('course directories','Vorlesungsverzeichnisse'), array(
  alink_document_view( array( 'type' => 'VVZ', 'flag_current', 'programme_flags &=' => PROGRAMME_MSC ), 'format=list,default=' )
, inlink( 'vorlesungsverzeichnisse', array( 'text' => we('Archive: course directories of past years...',"Archiv: Vorlesungsverzeichnisse vergangener Jahre...") ) )
), 'class=smallskipb' );

$list = array();
foreach( array( 'LF', 'SVP', 'MOV', 'MHB', 'SO', 'VUeS' ) as $type ) {
  $s = alink_document_view( array( 'type' => $type, 'flag_current', 'programme_flags &=' => PROGRAMME_MSC ), array( 'format' => 'list', 'default' => NULL ) );
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
//   , 'class' => 'icon close qpadr'
//   , 'title' => we('close','ausblenden' )
//   , 'text' => ''
//   ) );
//   open_fieldset( 'toggle', html_span( 'oneline', $button . we('Modules and contact persons','Module und Modulverantwortliche' ) ) );
//     moduleslist_view( 'programme_flags &= '.PROGRAMME_MSC, 'columns=programme_flags=t=off' );
//   close_fieldset();
// } else {
//   echo tb( inlink( '', array(
//     'options' => ( $options | OPTION_SHOW_MODULES )
//   , 'text' => we('show modules and contact persons...', 'Module und Modulverantwortliche anzeigen...' )
//   ) ) );
// }
//  


echo tb( html_alink( 'http://puls.uni-potsdam.de', array(
  'class' => 'href outlink'
, 'text' => we('Registration for courses and examinations: online portal PULS',"Anmeldung zu Veranstaltungen und Pr{$uUML}fungen: Online-Portal PULS" )
) ) );

// echo tb( inlink( 'themen', array( 'programme_flags' => PROGRAMME_MSC, 'text' => we('Topics for Master Theses',"Themenvorschl{$aUML}ge f{$uUML}r Masterarbeiten") ) ) );

close_ccbox();

?>
