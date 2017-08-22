<?php

echo html_tag( 'h1', '', 'Gemeinsam Lernen - Lernhilfeangebote' );

echo html_tag( 'h2', '', "Tutorium am Institut f{$uUML}r Physik" );

open_div( 'smallskipb' );
  open_div( 'floatright large qqpadl bigpadb', photo_view( '/pp/fotos/tutorium3.jpg', 'Ines Mayan', 'class=teaser,format=url' ) );
  echo "
    Tutorien werden unter der Woche von Studierenden höherer Semester angeboten.
    Dort können unter Anleitung Übungsaufgaben gerechnet oder Fragen zu Vorlesungsinhalten
    und auch darüber hinaus gestellt werden.
    Die Teilnahme ist freiwillig, doch alle Studienanfänger_innen sind herzlich willkommen,
    dieses Angebot wahrzunehmen!
  ";
close_div();

echo html_tag( 'h3', 'clear', "Termine im Sommersemester 2017" );

open_table( 'bigskipb th;td:smallskipb;qquads;oneline th:black;bold;solidtop,id=tutorium,colgroup=40% 30% 30%' );
 open_tr();
   open_th('', we('times','Termine') );
   open_th('', we('rooms',"R{$aUML}ume") );
   open_th('', we('tutors',"Tutor_innen") );


  open_tr();
    open_th( 'colspan=3,center', 'MonoBachelor Physik (BSc), 2. Semester' );

   open_tr();
     open_td( '', 'Mittwoch, 14-16 Uhr' );
     open_td( '', '2.28.2.080' );
     open_td( '', 'Timon' );

  open_tr();
    open_td( '', 'Donnerstag 08-10 Uhr' );
    open_td( '', '2.28.2.080' );
    open_td( '', 'Florian' );

//  open_tr();
//    open_td( 'colspan=3,center', "(Termine werden noch festgelegt)" );

// 
  open_tr();
    open_th( 'colspan=3,center', 'Lehramt Physik (BEd), 2.Semester' );
 
 
//    open_td('colspan=3,center', '(Termine werden noch festgelegt)' );
  open_tr();
    open_td( '', 'Donnerstag, 10-12 Uhr' );
    open_td( '', '2.28.2.080' );
    open_td( '', 'Steffen' );

  open_tr();
    open_td( '', 'Freitag, 08-10 Uhr' );
    open_td( '', '2.28.2.080' );
    open_td( '', 'Steffen' );
 
 
close_table(); 

// open_div( 'medskips', "(weitere Tutoriumstermine werden noch festgelegt!)" );



echo html_tag( 'h2', 'clear', 'MINT-Raum' );

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



echo html_tag( 'h2', 'clear', 'Forum Physikum' );

open_div( 'smallskipb' );
  open_div( 'floatright large qqpadl bigpadb', photo_view( '/pp/fotos/forum1.jpg', 'Ines Mayan', 'class=teaser,format=url' ) );
  echo "
    Das Forum Physikum ist ein Raum im Erdgeschoss des Physikgebäudes (Haus 28, Raum 0.85),
    der Studierenden aller Semester jederzeit offen steht. Hier kann in Ruhe gearbeitet
    oder bei einer Tasse Kaffee über Physik und Co diskutiert werden.
    Im Forum stehen eine Tafel und ein PC-Arbeitsplatz zur Verfügung, sowie meist auch eine
    helfende Hand.
  ";
close_div();


?>
