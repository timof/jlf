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
    'class' => 'qquads bigskips th;td:smallskipb;qquads;oneline th:black;bold;solidtop'
  , 'id' => 'brueckenkurs'
  , 'caption' => 'Termine im Wintersemester 2019/20' 
  , 'colgroup' => '40% 30% 30%'
  ) );
    open_tr();
      open_th('', 'Termine' );
      open_th('', 'Uhrzeit' );
      open_th('', 'Raum' );

     open_tr();
      open_td( '', 'Montag, 30.09.2019' );
      open_td( '', '12:15 - 16:45' );
      open_td( '', '2.28.0.108' );

     open_tr();
      open_td( '', 'Dienstag, 01.10.2019' );
      open_td( '', '14:15 - 16:45' );
      open_td( '', '2.25.B2.01' );

     open_tr();
      open_td( '', 'Mittwoch, 02.10.2019' );
      open_td( '', '12:15 - 16:45' );
      open_td( '', '2.25.B2.01' );

     open_tr();
      open_td( '', 'Freitag, 04.10.2019' );
      open_td( '', '12:15 - 16:45' );
      open_td( '', '2.25.B2.01' );
   
  close_table(); 
 
  open_div('clear','');
close_ccbox();


?>
