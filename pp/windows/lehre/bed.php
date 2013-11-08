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

echo tb( inlink( 'tutorium', array( 'text' => we(
    'Tutorium for beginners: help and guidance from students for students'
  , "Tutorium f{$uUML}r Studienanf{$aUML}nger: Hilfe und Beratung von Studierenden f{$uUML}r Studierende"
  ) ) )
);

echo tb( html_alink( 'http://www.uni-potsdam.de/mnfakul/studium/offenermint-raum.html', 'class=href outlink,text=Offener MINT Raum: Lernen mit Hilfe von Kommilitonen' ) );


// echo tb( we('Introductory courses',"Einf{$uUML}hrungsveranstaltungen und Vorkurse")
// , inlink( 'intro', array( 'text' => we('Introductory courses for beginners',"Einf{$uUML}hrungsveranstaltungen und Vorkurse vor Beginn des Vorlesungszeitraums") ) )
// );

echo tb( we('Course guidance for students in BEd and MEd programme',"Studienfachberatung Physik f{$uUML}r Studierende im Lehramtsstudium (BEd umd MEd)")
       , alink_person_view( 'people_id!=0,board=guidance,function=lehramt', 'office=1,format=list' )
);


echo html_tag( 'h2', 'medskipt', we('Planning your studies','Planung des Studiums') );


echo tb( we('course directories','Vorlesungsverzeichnisse'), array(
  alink_document_view( array( 'type' => 'VVZ', 'flag_current', 'programme_id &=' => PROGRAMME_BED ), 'format=list,default=' )
, inlink( 'vorlesungsverzeichnisse', array( 'text' => we('Archive: course directories of past years...',"Archiv: Vorlesungsverzeichnisse vergangener Jahre...") ) )
), 'class=smallskipb' );

$list = array();
foreach( array( 'SVP', 'MHB', 'SO', 'VUeS', 'INFO' ) as $type ) {
  $s = alink_document_view( array( 'type' => $type, 'flag_current', 'programme_id &=' => PROGRAMME_BED ), array( 'format' => 'list', 'default' => NULL ) );
  if( $s ) {
    $list[] = $s;
    // open_li( '', $s );
  }
}
$list[] = inlink( 'ordnungen', array( 'text' => we('older versions...',"{$aUML}ltere Fassungen...") ) );
echo tb( we('Current regulations','Aktuelle Ordnungen'), $list, 'class=smallskipb' );
 


// 
// 
// echo tb( we('Programme schedules',"Studienverlaufspl{$aUML}ne" )
//        , alink_document_view( array( 'type' => 'SVP', 'flag_current', 'programme_id &=' => PROGRAMME_BED ), 'format=list' )
// );
// 
// echo tb( we('Module manual BSc',"Modulhandbuch BSc")
//        , alink_document_view( array( 'type' => 'MHB', 'programme_id &=' => PROGRAMME_BED ), 'format=latest' )
// );
// 
// echo tb( we('Course regulations BSc',"Studienordnung BSc")
//        , alink_document_view( array( 'type' => 'SO', 'programme_id &=' => PROGRAMME_BED ), 'format=latest' )
// );
// 
// echo tb( we('Course overview',"Veranstaltungs{$uUML}bersicht")
//        , alink_document_view( array( 'type' => 'VUeS', 'programme_id &=' => PROGRAMME_BED ), 'format=latest' )
// );
// 
// echo tb( we('Course directory',"Vorlesungsverzeichnis")
//        , alink_document_view( array( 'type' => 'VVZ' ), 'format=latest' )
// );
// 
// echo tb( html_alink( 'http://puls.uni-potsdam.de', array(
//   'class' => 'href outlink'
// , 'text' => we('Registration for courses and examinations: online portal PULS',"Anmeldung zu Veranstaltungen und Pr{$uUML}fungen: Online-Portal PULS" )
// ) ) );


echo tb( inlink( 'themen', array( 'programme_id' => PROGRAMME_BED, 'text' => we('Topics for Bachelor Theses',"Themenvorschl{$aUML}ge f{$uUML}r Bachelorarbeiten") ) ) );

?>
