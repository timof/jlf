<?php // /pp/windows/lehre/intor.php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Enrollment for Courses in Physiks',"Einschreibung zum Physikstudium") );

echo tb( we('Contact and guidance for prospective students:',"Ansprechpartner und Beratung zu allen Fragen zur Einschreibung:")
       , alink_person_view( 'people_id!=0,board=guidance,function=enrollment', 'office=1,format=list' )
);

echo tb(
  html_alink(
    'http://www.uni-potsdam.de/studium/zugang.html'
  , 'class=href outlink,text='.we('Enrollment at the University of Potsdam','Einschreibung an der Universität Potsdam')
  )
);


?>
