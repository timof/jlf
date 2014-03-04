<?php

sql_transaction_boundary('*');

define( 'OPTION_SHOW_MODULES', 1 );
init_var('options','type=u,global=1,sources=http persistent,set_scopes=script' );

echo html_tag( 'h1', '', we('Master of Education (MEd) Program','Lehramtsstudium: Master of Education (MEd)' ) );

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

echo tb( we('Course guidance for students in BEd and MEd program',"Studienfachberatung Physik f{$uUML}r Studierende im Lehramtsstudium (BEd umd MEd)")
       , alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list' )
);


echo html_tag( 'h2', 'medskipt', we('Planning your studies','Planung des Studiums') );

echo tb( we('course directories','Vorlesungsverzeichnisse'), array(
  alink_document_view( array( 'type' => 'VVZ', 'flag_current', 'programme_flags &=' => PROGRAMME_MED ), 'format=list,default=' )
, inlink( 'vorlesungsverzeichnisse', array( 'text' => we('Archive: lecture directories of past years...',"Archiv: Vorlesungsverzeichnisse vergangener Jahre...") ) )
), 'class=smallskipb' );

$list = array();
foreach( array( 'LF', 'SVP', 'MOV', 'MHB', 'SO', 'VUeS', 'INFO' ) as $type ) {
  $s = alink_document_view( array( 'type' => $type, 'flag_current', 'programme_flags &=' => PROGRAMME_MED ), array( 'format' => 'list', 'default' => NULL ) );
  if( $s ) {
    $list[] = $s;
    // open_li( '', $s );
  }
}
$list[] = inlink( 'ordnungen', array( 'text' => we('older versions...',"{$aUML}ltere Fassungen...") ) );
echo tb( we('Current regulations','Aktuelle Ordnungen'), $list, 'class=smallskipb' );
 
if( $options & OPTION_SHOW_MODULES ) {
  $button = inlink( '', array(
    'options' => ( $options & ~OPTION_SHOW_MODULES )
  , 'class' => 'icon close qpadr'
  , 'title' => we('close','ausblenden' )
  , 'text' => ''
  ) );
  open_fieldset( 'toggle', html_span( 'oneline', $button . we('Modules and contact persons','Module und Modulverantwortliche' ) ) );
    moduleslist_view( 'programme_flags &= '.PROGRAMME_MED, 'columns=programme_flags=t=off' );
  close_fieldset();
} else {
  echo tb( inlink( '', array(
    'options' => ( $options | OPTION_SHOW_MODULES )
  , 'text' => we('show modules and contact persons...', 'Module und Modulverantwortliche anzeigen...' )
  ) ) );
}


echo tb( html_alink( 'http://puls.uni-potsdam.de', array(
  'class' => 'href outlink'
, 'text' => we('Registration for courses and examinations: online portal PULS',"Anmeldung zu Veranstaltungen und Pr{$uUML}fungen: Online-Portal PULS" )
) ) );


echo tb( inlink( 'themen', array( 'programme_flags' => PROGRAMME_MED, 'text' => we('Topics for Master Theses',"Themenvorschl{$aUML}ge f{$uUML}r Masterarbeiten") ) ) );


?>
