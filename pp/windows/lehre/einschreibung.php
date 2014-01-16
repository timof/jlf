<?php // /pp/windows/lehre/intor.php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Enrollment for Courses in Physics',"Einschreibung zum Physikstudium") );

echo tb( we('Contact and guidance for prospective students:',"Kontakt und Beratung zu allen Fragen zur Einschreibung:")
       , alink_person_view( 'people_id!=0,board=guidance,function=enrollment', 'office=1,format=list' )
);
echo tb( we(" You can apply for admission (in physics, only required for the Master of Science (MSc) degree course) and enroll on the web site of the University: "
           ,"Bewerbung um Zulassung (im Fach Physik nur erforderlich f{$uUML}r den Studiengang Master of Science (MSc)) und Einschreibung erfolgen {$uUML}ber die Webseite der Universit{$aUML}t:" )
, array(
    html_alink(
      we('http://www.uni-potsdam.de/en/studium/zugang0.html', 'http://www.uni-potsdam.de/studium/zugang.html')
    , 'class=href outlink,text='.we('Application and Enrollment at the University of Potsdam','Bewerbung und Einschreibung an der Universität Potsdam')
    ) 
  , we(' (note that enrollment will only be possible in the periods specified below!)', " (Einschreibung ist nur in den unten angegebenen Zeitr{$aUML}umen m{$oUML}glich!)" )
  )
);

echo html_tag( 'h2', 'bigskipt', we('Bachelor degree courses','Bachelorstudium (BSc oder BEd)') );

open_tag( 'p', 'smallskips', we(
"For the BSc (Bachelor of Science) and BEd (Bachelor of Education) degree courses, admission is not restricted (no Numerus Clausus); no application is
 required before enrollment.
", "
 Die Bachelor-Studieng{$aUML}nge BSc (Bachelor of Science, Ein-Fach-Bachelor) und BEd (Bachelor of Education, Lehramt mit Fach Physik)
 sind nicht zulassungsbeschr{$aUML}nkt (kein NC); die Einschreibung erfolgt ohne vorherige Bewerbung.
") );

open_tag( 'p', 'smallskips', we(
"Enrollment for a Bachelor degree course takes place from August 15 until September 15 for the following Winter term.
 Additionally, enrollment is possible from February 15 until March 15 for the following Summer term, but only for
 higher semesters of study (not for beginners).
 Enrollment is only possible in the specified periods.
", "
 Die Einschreibung zum Bachelorstudium in Physik erfolgt jeweils im Zeitraum 15.08. bis 15.09. für das folgende Wintersemester.
 Für höhere Fachsemester (alle außer dem ersten Fachsemester) ist die Einschreibung auch vom 15.02. bis 15.03 für das folgende Sommersemester möglich.
 Die Einschreibung erfolgt nur in den angegebenen Zeitr{$aUML}umen.
") );


echo tb( we('Guidance on the BSc (not teaching-oriented) degree course:',"Beratung zum BSc-Studiengang (Ein-Fach-Bachelor, nicht lehramtsbezogen):")
       , alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list' )
);
echo tb( we('Guidance on the BEd (teaching-oriented) degree course:',"Beratung zum BEd-Studiengang (Lehramt mit Fach Physik):")
       , alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list' )
);



echo html_tag( 'h2', 'bigskipt', we('Master degree courses (MSc or MEd)','Masterstudium (MSc oder MEd)') );

echo tb( we("For the Master of Science (MSc; not teaching-oriented) degree course, application for admission is required before enrollment:"
          , "Der Studiengang Master of Science (MSc; nicht lehramts-bezogen) ist zulassungsbeschr{$aUML}nkt (mit NC); vor der Einschreibung ist eine Bewerbung um Zulassung erforderlich:")
, array(
    html_alink(
      we('http://www.uni-potsdam.de/en/studium/zugang0/application-master.html', 'http://www.uni-potsdam.de/studium/zugang/bewerbung-master.html')
    , 'class=href outlink,text='.we('Application for the Master programme at the University of Potsdam', "Bewerbung zum Masterstudium an der Universit{$aUML}t Potsdam" )
    ) 
  , we('deadlines for application: March 15 (for summer term) and September 15 (for winter term)'
      ,"Bewerbungsfristen: 15. M{$aUML}rz (zum Sommersemester) und 15. September (zum Wintersemester)")
  )
);

echo tb( we("Enrollment for a Master degree course:"
          , "Einschreibung zum Masterstudium:")
, array(
    html_alink(
      we('http://www.uni-potsdam.de/en/studium/zugang0/enrollment-master.html', 'http://www.uni-potsdam.de/studium/zugang/immatrikulation-master.html')
    , 'class=href outlink,text='.we('Enrollment for the Master programme at the University of Potsdam', "Einschreibung zum Masterstudium an der Universit{$aUML}t Potsdam" )
    ) 
  , we('For the MSc degree course (not teaching-oriented), application and admission is required before enrollment.'
      ,"F{$uUML}r den MSc Studiengang (nicht lehramts-bezogen) ist die Einschreibung erst nach Zulassung m{$oUML}glich.")
  , we('For the MEd degree course (teaching-oriented), no application for admission is required (no NC).'
      ,"F{$uUML}r den MEd Studiengang (lehramts-bezogen) ist keine vorherige Bewerbung um Zulassung erforderlich (kein NC).")
  , we('deadlines for enrollment: February 15 until May 10 (for summer term) and August 15 until November 10 (for winter term)'
      ,"Einschreibezeitraum: 15.02. bis 10.05. (zum Sommersemester) und 15.08. bis 10.11. (zum Wintersemester)")
  )
);

echo tb( we('Guidance on the MSc programme:',"Beratung zum MSc-Studiengang (Ein-Fach-Master):")
       , alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list' )
);
echo tb( we('Guidance on the MEd programme:',"Beratung zum MEd-Studiengang (Lehramt mit Fach Physik):")
       , alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list' )
);





?>
