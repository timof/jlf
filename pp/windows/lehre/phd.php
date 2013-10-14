<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('PhD Programme','Promotionsstudium' ) );


echo tb( html_alink( 'http://www.app.physik.uni-potsdam.de/phd.html', array(
           'class' => 'href outlink'
         , 'text' => we('Structured Doctoral Training in Astrophysics', 'Strukturierte Doktorandenausbildung in Astrophysik')
         ) )
       , we( "a common and lasting framwork for supervision and training of doctorate students
              in astronomy and astrophysics at Universit{$aUML}t Potsdam, Leibniz-Institute for
              Astrophysics Potsdam (AIP) and DESY/Zeuthen"
           , "ein gemeinsamer Rahmen zur Betreuung und Ausbildung von Promivierenden in Astronomie
              und Astrophysik an der Universit{$aUML}t Potsdam, am Leibniz-Institut f{$aUML}r Astrophysik Potsdam (AIP)
              und am DESY (Zeuthen)")
     );

echo tb( html_tag( 'a', 'href=http://www.uni-potsdam.de/mnfakul/promotion.html,class=href outlink', 'Promotionsausschuss der Fakultät' ) );


echo tb( we('PhD Theses','Doktorarbeiten')
       , inlink( 'themen', array( 'programme_id' => PROGRAMME_PHD, 'text' => we('Topics for PhD Theses',"Themenvorschl{$aUML}ge f{$uUML}r Doktorarbeiten") ) )
);

?>
