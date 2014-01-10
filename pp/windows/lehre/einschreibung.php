<?php // /pp/windows/lehre/intor.php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Enrollment for Courses in Physics',"Einschreibung zum Physikstudium") );

echo tb( we('Contact and guidance for prospective students:',"Ansprechpartner und Beratung zu allen Fragen zur Einschreibung:")
       , alink_person_view( 'people_id!=0,board=guidance,function=enrollment', 'office=1,format=list' )
);
echo tb( we(" You can apply (only required for the Master programme) or enroll on the web site of the University: "
           ,"Bewerbung (nur erforderlich f{$uUML}r Master-Studieng{$aUML}nge) und Einschreibung erfolgen {$uUML}ber die Webseite der Universit{$aUML}t:" )
, array(
    html_alink(
      'http://www.uni-potsdam.de/studium/zugang.html'
    , 'class=href outlink,text='.we('Application and Enrollment at the University of Potsdam','Bewerbung und Einschreibung an der Universität Potsdam')
    ) 
  , we(' (note that enrollment will only be possible in the periods specified above!)', " (Einschreibung ist nur in den unten angegebenen Zeitr{$aUML}umen m{$oUML}glich!)" )
  )
);

echo html_tag( 'h2', 'medskipt', we('Enrollment in the BSc and BEd programme','Einschreibung zum Bachelorstudium (BSc oder BEd)') );

open_tag( 'p', 'smallskips', we(
"For the BSc (Bachelor of Science) and BEd (Bachelor of Education) programme, admission is not restricted (no Numerus Clausus); no application is
 required before enrollment.
", "
 Die Bachelor-Studiengaenge BSc (Bachelor of Science, Ein-Fach-Bachelor) und BEd (Bachelor of Education, Lehramt mit Fach Physik)
 sind nicht zulassungsbeschränkt; die Einschreibung erfolgt ohne vorherige Bewerbung
") );

open_tag( 'p', 'smallskips', we(
"Enrollment for the Bachelor programme takes place from August 15 until September 15 for the following Winter term.
 Additionally, enrollment is possible from February 15 until March 15 for the following Summer term, but only for
 higher semesters of study (not for beginners).
", "
 Die Einschreibung zum Bachelorstudium in Physik erfolgt jeweils im Zeitraum 15.08. bis 15.09. für das folgende Wintersemester.
 Für höhere Fachsemester (alle außer dem ersten Fachsemester) ist die Einschreibung auch vom 15.02. bis 15.03 für das folgende Sommersemester möglich.
") );

echo tb( we('Contact and guidance for the BSc programme:',"Ansprechpartner und Beratung zum BSc-Studiengang (Ein-Fach-Bachelor):")
       , alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list' )
);
echo tb( we('Contact and guidance for the BEd programme:',"Ansprechpartner und Beratung zum BEd-Studiengang (Lehramt mit Fach Physik):")
       , alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list' )
);


echo html_tag( 'h2', 'medskipt', we('Application for the MSc and MEd programme','Bewerbung zum Masterstudium (MSc oder MEd)') );

echo tb( we('Contact and guidance for the MSc programme:',"Ansprechpartner und Beratung zum MSc-Studiengang (Ein-Fach-Master):")
       , alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list' )
);
echo tb( we('Contact and guidance for the MEd programme:',"Ansprechpartner und Beratung zum MEd-Studiengang (Lehramt mit Fach Physik):")
       , alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list' )
);





?>
