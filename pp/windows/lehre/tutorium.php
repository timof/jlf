<?php

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('lehre');
    echo html_tag( 'h1', '', we('Studies / Tutorials','Lehre am Institut / Lernhilfeangebote') );
  close_div();
close_div();

// open_span( 'qquadl bigpadb banner', photo_view( '/pp/fotos/mint.jpg', 'Karla Fritze', 'format=url' ) );

// echo html_tag( 'h1', '', 'Gemeinsam Lernen - Lernhilfeangebote' );


open_ccbox('', "Tutorium am Institut f{$uUML}r Physik" );
  open_div( 'illu', image( 'tutorium' ) );
  echo "
    Tutorien werden unter der Woche von Studierenden höherer Semester angeboten.
    Dort können unter Anleitung Übungsaufgaben gerechnet oder Fragen zu Vorlesungsinhalten
    und auch darüber hinaus gestellt werden.
    Die Teilnahme ist freiwillig, doch alle Studienanfänger_innen sind herzlich willkommen,
    dieses Angebot wahrzunehmen!
  ";

  open_table( array(
    'class' => 'bigskips th;td:smallskipb;qquads;oneline th:black;bold;solidtop'
  , 'id' => 'tutorium'
  , 'caption' => we('Tutorials in Winter term 2017/18','Termine im Wintersemester 2017/18') 
  , 'colgroup' => '40% 30% 30%'
  ) );
    open_tr();
      open_th('', we('times','Termine') );
      open_th('', we('rooms',"R{$aUML}ume") );
      open_th('', we('tutors',"Tutor_innen") );

     open_tr();
       open_th( 'colspan=3,center', 'MonoBachelor Physik (BSc), 1. Semester' );

     open_tr();
      open_td( '', 'Freitag, 12-14 Uhr' );
      open_td( '', '2.09.013' );
      open_td( '', 'Markus' );

     open_tr();
      open_td( '', 'Freitag, 12-14 Uhr' );
      open_td( '', '2.28.0.102' );
      open_td( '', 'Timon' );

//    open_tr();
//      open_td( 'colspan=3,center', "(Termine werden noch festgelegt)" );

  // 
    open_tr();
      open_th( 'colspan=3,center', 'Lehramt Physik (BEd), 1.Semester' );

    open_tr();
     open_td('colspan=3,center', '(Termine werden noch festgelegt)' );
//     open_tr();
//       open_td( '', 'Donnerstag, 10-12 Uhr' );
//       open_td( '', '2.28.2.080' );
//       open_td( '', 'Steffen' );
//   
//     open_tr();
//       open_td( '', 'Freitag, 08-10 Uhr' );
//       open_td( '', '2.28.2.080' );
//       open_td( '', 'Steffen' );
//    
   
  close_table(); 
  
  // open_div( 'medskips', "(weitere Tutoriumstermine werden noch festgelegt!)" );

  open_div('clear','');
close_ccbox();


open_ccbox('', 'MINT-Raum' );
  open_div( 'illu', image( 'mint' ) );

  open_div( 'smallskipb', "
      Wer sich gerne mit anderen Studierenden zum Lernen und Rechnen zusammensetzt,
      aber trotzdem bei Bedarf erfahrene Studierende zu Rate ziehen möchte, ist
      mit dem MINT-Raum gut beraten. 
      Er bietet allen Studierenden der naturwissenschaftlichen Fächer einen Ort
      zum Selbststudium, wobei ältere Studierende für Fragen zur Verfügung stehen.
  " );
  $url = 'http://www.uni-potsdam.de/mnfakul/studium-und-lehre/mint-raum.html';
  open_div( 'bigskipb'
  , "Link: " . html_alink( 'http://www.uni-potsdam.de/mnfakul/studium-und-lehre/mint-raum.html', array( 'class' => 'href outlink', 'text' => $url ) )
  );

  open_div('clear','');
close_ccbox();


open_ccbox('', 'Forum Physikum' );

  open_div( 'illu', image('forum') );
  echo "
    Das Forum Physikum im Erdgeschoss des Physikgebäudes (Haus 28, Raum 0.85),
    ist ein Raum,
    der Studierenden aller Semester jederzeit offen steht. Hier kann in Ruhe gearbeitet
    oder bei einer Tasse Kaffee über Physik und Co diskutiert werden.
    Im Forum stehen eine Tafel und ein PC-Arbeitsplatz zur Verfügung, sowie meist auch eine
    helfende Hand.
  ";

  open_div('clear','');
close_ccbox();


?>
