<?php

sql_transaction_boundary('*');

define( 'OPTION_SHOW_TEASER', 1 );
init_var('options','type=u,global=1,sources=http persistent initval,set_scopes=script,initval='.OPTION_SHOW_TEASER );

echo html_tag( 'h1', '', we('Studying at the Institute','Studium und Lehre am Institut') );

if( $options & OPTION_SHOW_TEASER ) {
  echo teaser_view('studium');
}

echo html_tag( 'h2', 'medskips', we('Key research areas','Forschungsschwerpunkte am Institut') );

$captionlink = false;
require( 'pp/schwerpunkte.php' );

open_tag( 'a', array( 'class' => 'block keyareathumbnails', 'href' => inlink( 'forschung', 'context=url' ) ) );
  foreach( $schwerpunkte_keys as $k ) {
    $s = $schwerpunkte[ $k ];
    open_div( 'inline_block center smallpads qpads', html_div( '', $s['title'] ) . $s['photoview'] );
  }
close_tag( 'a' );


// echo html_div( 'floatleft level1photo', photo_view( '/pp/fotos/lehre.h27.1.jpg', 'Thomas Roese (AVZ)', 'format=url' ) );

echo html_tag( 'h2', '', we('Degree programs offered at the Institute of Physics', "Studieng{$aUML}nge am Institut f{$uUML}r Physik" ) );

open_ul('plain');
  open_li( '', inlink( 'bsc', 'text='.we('Bachelor of Science (BSc) in physics','Bachelor of Science (BSc) in Physik') ) );
  open_li( '', inlink( 'bed', 'text='.we('Bachelor of Education (BEd) with physics as one subject','Bachelor of Education (BEd) mit Fach Physik') ) );
  open_li( '', inlink( 'msc', 'text='.we('Master of Science (MSc) in physics',' Master of Science (MSc) in Physik') ) );
  open_li( '', inlink( 'med', 'text='.we('Master of Education (MEd) with physics as one subject','Master of Education (MEd) mit Fach Physik') ) );
  open_li( '', inlink( 'phd', 'text='.we('PhD program at the institute of physics','Promotionsstudium am Institut für Physik') ) );
  open_li( '', inlink( 'diplom', 'text='.we('Diploma/Magister Program in physics (phased out)','Diplom-/Magisterstudium in Physik (auslaufend)') ) );
close_ul();
  

echo html_tag( 'h2', 'medskipt smallskipb', we('General Information',"Allgemeine Informationen") );

  echo tb( inlink( 'einschreibung', 'text='.we('Information for prospective students', "Informationen f{$uUML}r Studieninteressierte" ) ), '' );

  echo tb( inlink( 'terminelehre', 'text='.we('Important dates for students',"Wichtige Termine f{$uUML}r Studierende") ) );

  echo tb( inlink( 'pruefungsausschuss', 'text='.we('Examination board and board of study affairs',"Pr{$uUML}fungsausschuss und Studienkommission Physik" ) )
//  , "Der Pr{$uUML}fungsausschuss entscheidet unter anderem {$uUML}ber Belegungsverpflichtungen"
  );

  echo tb( we('Lab courses at the institute','Praktika am Institut')
  , alink_group_view( array( 'status' => GROUPS_STATUS_LABCOURSE ), 'fullname=1' )
  );

  echo tb( inlink( 'studierendenvertretung', 'text='.we('Student representation','Studierendenvertretung') )
//  , "Vertretung der Studierenden am Institut und in der Universit{$aUML}t"
  );
  
  echo tb( we('Exchange program: ', 'Austausch-Programm: ' . html_alink( 'http://www.exph.physik.uni-potsdam.de/erasmus.html', 'class=outlink,text=SOCRATES/ERASMUS') )
  , we('contact: ','Kontakt: ').alink_person_view('board=guidance,function=erasmus', 'office=1,format=list,class=quadl' )
  );
  
  echo tb( we("BAf{$oUML}G (Federal Education Assistance Act) guidance", "BAf{$oUML}G Beratung")
  , we('contact: ','Kontakt: ') . alink_person_view('board=guidance,function=bafoeg', 'office=1,format=list,class=quadl' )
  );
  
  echo tb( inlink( 'download', array( 'text' => we('Download area: course directories, regulations, ...','Download-Bereich: Vorlesungsverzeichnisse, Ordnungen, ...' ) ) ) );


?>
