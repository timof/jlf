<?php

sql_transaction_boundary('*');


echo html_tag( 'h1', '', we('Studying at the Institute','Studium und Lehre am Institut') );


echo html_tag( 'h2', '', we('Programme-specific Information','Studiengangspezifische Informationen') );

  echo tb( inlink( 'monobachelor', 'text='.we('Bachelor Programme','Bachelorstudiengang').' (BSc)' ) );
  
  echo tb( inlink( 'master', 'text='.we('Master Programme','Masterstudiengang').' (MSc)' ) );
  
  echo tb( inlink( 'lehramt', 'text='.we('Teacher Programme','Lehramtsstudium').' (BEd/Med)' ) );
  
  echo tb( inlink( 'diplom', 'text='.we('Diploma/Magister Programme','Diplom-/Magisterstudium') ) );
  
  echo tb( inlink( 'phd', 'text='.we('PhD Programme','Promotionsstudium') ) );
  

echo html_tag( 'h2', '', we('General Information for students','Allgemeine Informationen fuer Studierende') );

  echo tb( inlink( 'pruefungsausschuss', "text=Pr{$uUML}fungsausschuss und Studienkommission Physik" )
  , "Der Pr{$uUML}fungsausschuss entscheidet unter anderem {$uUML}ber Belegungsverpflichtungen"
  );

  echo tb( we('Lab courses at the institute','Praktika am Institut')
  , alink_group_view( array( 'status' => GROUPS_STATUS_LABCOURSE ), 'fullname=1' )
  );

  echo tb( inlink( 'studierendenvertretung', "text=Studierendenvertretung" )
  , "Vertretung der Studierenden am Institut und in der Universit{$aUML}t"
  );
  
  echo tb( html_alink( 'http://www.exph.physik.uni-potsdam.de', 'text='.we('Exchange programme: SOCRATES/ERASMUS', 'Austausch-Programm: SOCRATES/ERASMUS') )
  , we('contact: ','Kontakt: ').alink_person_view('board=guidance,function=erasmus', 'office=1,format=list,class=quadl' )
  );
  
  echo tb( we("BAf{$oUML}G (Federal Education Assistance Act) guidance", "BAf{$oUML}G Beratung")
  , we('contact: ','Kontakt: ') . alink_person_view('board=guidance,function=bafoeg', 'office=1,format=list,class=quadl' )
  );
  
  echo tb( inlink( 'download', 'text='.we('Download area','Download-Bereich') )
  , we('Documents: university calendars, regulations, ...', 'Dateien: Vorlesungsverzeichnisse, Ordnungen, ...' )
  );


  echo tb( inlink( 'termine', array( 'text' => we('Important dates for students','Wichtige Termine fuer Studierende am Institut') ) ) );

  echo tb( inlink( 'veranstaltungen', array( 'text' => we('Seminars, guest lectures, colloquia',"Seminare, Gastvortr{$aUML}ge, Kolloquia") ) ) );


?>
