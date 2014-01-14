<?php

sql_transaction_boundary('*');


echo html_tag( 'h1', '', we('Studying at the Institute','Studium und Lehre am Institut') );

echo tb( inlink( 'einschreibung', 'text='.we('Application and Enrollment for Courses in Physics', "Bewerbung und Einschreibung zum Physikstudium" ) ) );

// echo html_div( 'floatleft level1photo', photo_view( '/pp/fotos/lehre.h27.1.jpg', 'Thomas Roese (AVZ)', 'format=url' ) );

echo html_tag( 'h2', '', we('Programmes offered at the Institute of Physics', "Studieng{$aUML}nge am Institut f{$uUML}r Physik" ) );

  echo tb( inlink( 'bsc', 'text='.we('Bachelor of Science','Bachelor of Science').' (BSc)' ) );
  echo tb( inlink( 'bed', 'text='.we('Bachelor of Education','Bachelor of Education').' (BEd)' ) );
  
  echo tb( inlink( 'msc', 'text='.we('Master of Science',' Master of Science').' (MSc)' ) );
  echo tb( inlink( 'med', 'text='.we('Master of Education','Master of Education').' (MEd)' ) );
  
  echo tb( inlink( 'phd', 'text='.we('PhD Programme','Promotionsstudium') ) );
  
  echo tb( inlink( 'diplom', 'text='.we('Diploma/Magister Programme','Diplom-/Magisterstudium') ) );
  

echo html_tag( 'h2', '', we('General Information for students',"Allgemeine Informationen f{$uUML}r Studierende") );

  echo tb( inlink( 'terminelehre', 'text='.we('Important dates for students',"Wichtige Termine f{$uUML}r Studierende") )
//  , "Der Pr{$uUML}fungsausschuss entscheidet unter anderem {$uUML}ber Belegungsverpflichtungen"
  );

  echo tb( inlink( 'pruefungsausschuss', "text=Pr{$uUML}fungsausschuss und Studienkommission Physik" )
//  , "Der Pr{$uUML}fungsausschuss entscheidet unter anderem {$uUML}ber Belegungsverpflichtungen"
  );

  echo tb( we('Lab courses at the institute','Praktika am Institut')
  , alink_group_view( array( 'status' => GROUPS_STATUS_LABCOURSE ), 'fullname=1' )
  );

  echo tb( inlink( 'studierendenvertretung', "text=Studierendenvertretung" )
//  , "Vertretung der Studierenden am Institut und in der Universit{$aUML}t"
  );
  
  echo tb( html_alink( 'http://www.exph.physik.uni-potsdam.de', 'class=outlink,text='.we('Exchange programme: SOCRATES/ERASMUS', 'Austausch-Programm: SOCRATES/ERASMUS') )
  , we('contact: ','Kontakt: ').alink_person_view('board=guidance,function=erasmus', 'office=1,format=list,class=quadl' )
  );
  
  echo tb( we("BAf{$oUML}G (Federal Education Assistance Act) guidance", "BAf{$oUML}G Beratung")
  , we('contact: ','Kontakt: ') . alink_person_view('board=guidance,function=bafoeg', 'office=1,format=list,class=quadl' )
  );
  
  echo tb( inlink( 'download', array( 'text' => we('Download area: course directories, regulations, ...','Download-Bereich: Vorlesungsverzeichnisse, Ordnungen, ...' ) ) ) );


?>
