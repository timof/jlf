<?php

sql_transaction_boundary('*');


open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    open_tag( 'img', array( 'src' => '/pp/fotos/innenhof.jpg', 'alt' => 'Innenhof des Instituts bei Nacht' ), NULL );
    open_div( 'rights', we('Image:','Bild:') . ' David Gruner' );
    echo html_tag( 'h1', '', we('Institute','Institut') );
  close_div();
close_div();

echo html_tag( 'h1', '', we('Institute','Institut') );

  echo tb( we('Head of the Institute:','Geschäftsführender Leiter:')
         , alink_person_view( 'board=executive,function=chief', 'office' ) );

  echo tb( we('Deputy Head:','Stellvertretender Geschäftsführender Leiter:')
         , alink_person_view( 'board=executive,function=deputy', 'office' ) );

  echo tb( we('Scientific Coordinator:','Wissenschaftlicher Koordinator:')
         , alink_person_view( 'board=special,function=coordinator', 'office' ) );

  echo tb( inlink( 'institutsrat', 'text='.we('Institute board','Institutsrat') ) );

  echo tb( inlink( 'pruefungsausschuss', 'text='.we('Examination board and board of study affairs','Prüfungsausschuss und Studienkommission') ) );


  echo tb( html_tag( 'a', 'href=http://www.uni-potsdam.de/mnfakul/die-fakultaet/gremien/promotionsausschuss.html,class=href outlink', 'Promotionsausschuss der Fakultät' ) );

  echo tb( 'BAFöG'.we(' guidance:','-Beratung:')
         , alink_person_view( 'board=guidance,function=bafoeg', 'office' ) );

  echo tb( 'SOKRATES/ERASMUS'.we(' guidance:','-Beratung:')
         , alink_person_view( 'board=guidance,function=erasmus', 'office' ) );

  echo tb( inlink( 'studierendenvertretung', 'text='.we('Student representation','Studierendenvertretung') ) );

  echo tb( inlink( 'mitarbeiter', 'text='.we('Staff','Personalverzeichnis') ) );

  echo tb( inlink( 'professuren', 'text='.we('Professsors','Professuren') ) );

  echo tb( inlink( 'labore', 'text='.we('Labs and contact persons','Labore und Laborverantwortliche') ) );


?>
