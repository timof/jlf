<?php

sql_transaction_boundary('*');

define( 'OPTION_SHOW_TEASER', 0 );
init_var('options','type=u,global=1,sources=http persistent initval,set_scopes=script,initval='.OPTION_SHOW_TEASER );

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    open_tag( 'img', array( 'src' => '/pp/fotos/lehre.jpg', 'alt' => 'Vorlesung im großsen Hörsaal' ), NULL );
    open_div( 'rights', we('Image:','Bild:') . ' Karla Fritze' );
    echo html_tag( 'h1', '', we('Studies','Lehre am Institut') );
  close_div();
close_div();


if( $options & OPTION_SHOW_TEASER ) {
  echo teaser_view('studium');
}

// echo html_tag( 'h2', 'medskips', we('Key research areas','Forschungsschwerpunkte am Institut') );
// 
// $captionlink = false;
// require( 'pp/schwerpunkte.php' );
// 
// open_tag( 'a', array( 'class' => 'block keyareathumbnails', 'href' => inlink( 'forschung', 'context=url' ) ) );
//   foreach( $schwerpunkte_keys as $k ) {
//     $s = $schwerpunkte[ $k ];
//     open_div( 'inline_block center smallpads qpads', html_div( '', $s['title'] ) . $s['photoview'] );
//   }
// close_tag( 'a' );


// echo html_div( 'floatleft level1photo', photo_view( '/pp/fotos/lehre.h27.1.jpg', 'Thomas Roese (AVZ)', 'format=url' ) );

open_ccbox( '', we('Degree programs at the Institute of Physics', "Studieng{$aUML}nge am Institut f{$uUML}r Physik" ) );

  open_tag( 'h3', '', inlink( 'studiengaenge', array( 'text' => we('Overview and information for prospective students',"Übersicht und Informationen zur Einschreibung für Studieninteressierte" ) ) ) );
  
  open_tag( 'h3', '', we('Information for students',"Informationen für Studierende" ) );
  open_ul('plain');
    open_li( '', inlink( 'bsc', array( 'text' => $programme_text[ PROGRAMME_BSC ] ) ) );
    open_li( '', inlink( 'bed', array( 'text' => $programme_text[ PROGRAMME_BED ] ) ) );
    open_li( '', inlink( 'msc', array( 'text' => $programme_text[ PROGRAMME_MSC ] ) ) );
    open_li( '', inlink( 'med', array( 'text' => $programme_text[ PROGRAMME_MED ] ) ) );
    open_li( '', inlink( 'mastro', array( 'text' => $programme_text[ PROGRAMME_M_ASTRO ] ) ) );
    open_li( '', inlink( 'phd', 'text='.we('PhD program at the institute of physics','Promotionsstudium am Institut für Physik') ) );
  //  open_li( '', inlink( 'diplom', 'text='.we('Diploma/Magister Program in physics (phased out)','Diplom-/Magisterstudium in Physik (auslaufend)') ) );
  close_ul();
close_ccbox();
  

open_ccbox( '', we('General Information for students',"Allgemeine Informationen zum Studium") );

//  echo tb( inlink( 'einschreibung', 'text='.we('Information for prospective students', "Informationen f{$uUML}r Studieninteressierte" ) ), '' );

  echo tb( inlink( 'terminelehre', 'text='.we('Important dates for students and prospective students',"Wichtige Termine f{$uUML}r Studierende und Studieninteressierte") ) );

  echo tb( inlink( 'pruefungsausschuss', 'text='.we('Examination board and board of study affairs',"Pr{$uUML}fungsausschuss und Studienkommission Physik" ) )
//  , "Der Pr{$uUML}fungsausschuss entscheidet unter anderem {$uUML}ber Belegungsverpflichtungen"
  );

  echo tb( inlink( 'praktika', 'text='.we('Lab courses at the institute','Praktika am Institut') ) );

  echo tb( inlink( 'studierendenvertretung', 'text='.we('Student representation','Studierendenvertretung') )
//  , "Vertretung der Studierenden am Institut und in der Universit{$aUML}t"
  );
  
  echo tb( /* 'Tutorium' , */ inlink( 'tutorium', array( 'text' => we(
      "Tutorials and other opportunities for learning together"
    , "Gemeinsam Lernen: Angebote f{$uUML}r Studierende"
    ) ) )
  );

  echo tb( we('Exchange program: ', 'Austausch-Programm: ' . html_alink( 'http://www.exph.physik.uni-potsdam.de/erasmus.html', 'class=outlink,text=SOCRATES/ERASMUS') )
  , we('contact: ','Kontakt: ').alink_person_view('board=guidance,function=erasmus', 'office=1,format=list,class=quadl' )
  );
  
  echo tb( we("BAf{$oUML}G (Federal Education Assistance Act) guidance", "BAf{$oUML}G Beratung")
  , we('contact: ','Kontakt: ') . alink_person_view('board=guidance,function=bafoeg', 'office=1,format=list,class=quadl' )
  );
  
  echo tb( inlink( 'download', array( 'text' => we('Download area: course directories, regulations, ...','Download-Bereich: Vorlesungsverzeichnisse, Ordnungen, ...' ) ) ) );


close_ccbox();

?>
