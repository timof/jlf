<?php

sql_transaction_boundary('*');

define( 'OPTION_SHOW_MODULES', 1 );
init_var('options','type=u,global=1,sources=http persistent,set_scopes=script' );

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('lehre');
    echo html_tag( 'h1', '', we('Studies / BEd','Lehre / BEd' ) );
  close_div();
close_div();

open_ccbox( '', we('Bachelor of Education (BEd) with physics as one subject','Lehramtsstudium: Bachelor of Education (BEd) mit Fach Physik' ) );

echo html_tag( 'h2', '', we('Studying in Potsdam','Wahl des Studienortes Potsdam') );

open_tag( 'p', 'smallskips', "
  Das Lehramtsstudium der Physik an der Uni Potsdam besteht aus einem 3-j{$aUML}hrigen Bachelor- und
  einem 2-j{$aUML}hrigen Masterstudiengang.
  Im Bachelorstudium werden fachliche Kenntnisse im Fach Physik einschlie{$SZLIG}lich der spezifischen
  Erkenntnis- und Arbeitsmethoden sowie Kompetenzen der Fachdidaktik erworben, die dazu bef{$aUML}higen,
  einen Sch{$uUML}lerorientierten und wissenschaftlich fundierten Physikunterricht zu gestalten.
  Die Ausbildung in experimenteller Physik erfolgt vorwiegend gemeinsam mit Studierenden ohne
  Lehramtsbezug.
" );

open_tag( 'p', 'smallskips', we(
"Admission to the Bachelor of Education (BEd, teaching-oriented) in physics is not restricted (no Numerus Clausus); depending on the other subject(s), application for admission
 may or may not be required.
", "
 Der Studiengang Bachelor of Education (BEd, lehramts-bezogen) ist im Fach Physik nicht zulassungsbeschr{$aUML}nkt (kein NC). F{$uUML}r andere F{$aUML}cher
 kann eine Zulassungsbeschr{$aUML}nkung bestehen; ob vor der Einschreibung eine
 Bewerbung um Zulassung erforderlich ist h{$aUML}ngt daher von der F{$aUML}cherkombination ab.
") );

$s = alink_document_view( array( 'tag' => 'flyer_bed', 'flag_current', 'programme_flags &=' => PROGRAMME_BED ), array( 'format' => 'list', 'default' => NULL ) );
if( $s ) {
  echo tb( $s );
}

echo tb( html_alink(
  'http://www.uni-potsdam.de/studium/studienangebot/lehramt/bachelor/physik.html'
, 'class=href outlink,text='.we('General information on the program', "{$UUML}berblicksseite zum Studiengang" )
) );

echo tb( inlink( 'einschreibung', 'text='.we('Information for prospective students', "Informationen f{$uUML}r Studieninteressierte" ) ) );

echo tb( inlink( 'tutorium', array( 'text' => we(
    'Tutorium for beginners: help and guidance from students for students'
  , "Tutorium f{$uUML}r Studienanf{$aUML}nger: Hilfe und Beratung von Studierenden f{$uUML}r Studierende"
  ) ) )
);

echo tb( html_alink( 'http://www.uni-potsdam.de/mnfakul/studium-und-lehre/mint-raum.html', 'class=href outlink,text=Offener MINT Raum: Lernen mit Hilfe von Kommilitonen' ) );


// echo tb( we('Introductory courses',"Einf{$uUML}hrungsveranstaltungen und Vorkurse")
// , inlink( 'intro', array( 'text' => we('Introductory courses for beginners',"Einf{$uUML}hrungsveranstaltungen und Vorkurse vor Beginn des Vorlesungszeitraums") ) )
// );

echo tb( we('Course guidance for students in BEd and MEd program',"Studienfachberatung Physik f{$uUML}r Studierende im Lehramtsstudium (BEd umd MEd)")
       , alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list' )
);


echo html_tag( 'h2', 'medskipt', we('Planning your studies','Planung des Studiums') );


echo tb( we('course directories','Vorlesungsverzeichnisse'), array(
  alink_document_view( array( 'type' => 'VVZ', 'flag_current', 'programme_flags &=' => PROGRAMME_BED ), 'format=list,default=,class=smallskipb' )
, inlink( 'vorlesungsverzeichnisse', array( 'text' => we('Archive: course directories of past years...',"Archiv: Vorlesungsverzeichnisse vergangener Jahre...") ) )
), 'class=smallskipb' );

$list = array();
foreach( array( 'LF', 'SVP', 'MOV', 'MHB', 'SO', 'VUeS' ) as $type ) {
  $s = alink_document_view( array( 'type' => $type, 'flag_current', 'programme_flags &=' => PROGRAMME_BED ), array( 'format' => 'list', 'default' => NULL ) );
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
//     moduleslist_view( 'programme_flags &= '.PROGRAMME_BED, 'columns=programme_flags=t=off' );
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


echo tb( inlink( 'themen', array( 'programme_flags' => PROGRAMME_BED, 'text' => we('Topics for Bachelor Theses',"Themenvorschl{$aUML}ge f{$uUML}r Bachelorarbeiten") ) ) );

close_ccbox();

?>
