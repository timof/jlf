<?php

sql_transaction_boundary('*');

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('lehre');
    echo html_tag( 'h1', '', 'Einführungsveranstaltungen' );
  close_div();
close_div();

// open_span( 'qquadl bigpadb banner', photo_view( '/pp/fotos/mint.jpg', 'Karla Fritze', 'format=url' ) );

// echo html_tag( 'h1', '', 'Gemeinsam Lernen - Lernhilfeangebote' );


open_ccbox('', 'Brückenkurs "Auffrischung Mathe für Studienanfänger*innen"' );

  open_div( 'qquads bold medskips', 'Dozenten: ' . alink_person_view( 'cn=martin wilkens,title=prof. dr.', 'class=list' )
                          . ', ' . alink_person_view( 'cn=achim feldmeier', 'class=list' ) );
  open_table( array(
    'class' => 'qquads bigskips th;td:smallskipb;qquads;oneline;left th:black;bold;solidtop'
  , 'id' => 'brueckenkurs'
  , 'caption' => 'Termine im Wintersemester 2019/20' 
  , 'colgroup' => '40% 30% 30%'
  ) );
    open_tr();
      open_th('', 'Termine' );
      open_th('', 'Uhrzeit' );
      open_th('', 'Raum' );

     open_tr();
      open_td( '', 'Montag, 26.10.2020' );
      open_td( '', '13:30 - 17:00' );
      open_td( '', '2.27.0.01 (großer Physikhörsaal)' );

     open_tr();
      open_td( '', 'Dienstag, 27.10.2020' );
      open_td( '', '10:00 - 17:00' );
      open_td( '', '2.27.0.01 (großer Physikhörsaal)' );

     open_tr();
      open_td( '', 'Mittwoch, 28.10.2020' );
      open_td( '', '10:00 - 17:00' );
      open_td( '', '2.27.0.01 (großer Physikhörsaal)' );

     open_tr();
      open_td( '', 'Donnerstag, 29.10.2020' );
      open_td( '', '10:00 - 17:00' );
      open_td( '', '2.27.0.01 (großer Physikhörsaal)' );

     open_tr();
      open_td( '', 'Freitag, 30.10.2020' );
      open_td( '', '10:00 - 13:00' );
      open_td( '', '2.27.0.01 (großer Physikhörsaal)' );
   
  close_table(); 
 
  open_div('clear','');
  open_div('medskipb', '
    Stoff:
      Übergang Schule - Uni, fachspezifische Fortsetzung des 
      allgemeinen
      Mathematik-Brückenkurses für alle Studienanfänger aus der Vorwoche.
  ');

close_ccbox();


?>
