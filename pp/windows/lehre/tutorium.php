<?php

echo html_tag( 'h1', '', 'Tutorium' );

echo html_tag( 'h2', '', "Tutorium am Institut f{$uUML}r Physik - Termine im Wintersemester 2015" );

open_div( 'bigskipb', "
   Die Teilnahme an den Tutorien ist freiwillig. Sie werden von Studierenden in h{$oUML}heren Semestern
   abgehalten und sollen
   Studienanf{$aUML}nger_innen
   bei Problemen (vor allem physikalischen) helfen.
" );


open_table( 'th;td:smallskipb;qquads;oneline th:black;bold;solidtop,id=tutorium,colgroup=40% 30% 30%' );
 open_tr();
   open_th('', we('times','Termine') );
   open_th('', we('rooms',"R{$aUML}ume") );
   open_th('', we('tutors',"Tutor_innen") );


  open_tr();
    open_th( 'colspan=3,center', 'MonoBachelor Physik (BSc), 2. Semester' );

  open_tr();
    open_td( '', 'Mittwoch, 16-18 Uhr' );
    open_td( '', '2.28.2.080' );
    open_td( '', 'Timon' );

  open_tr();
    open_td( '', 'Freitag, 14-16 Uhr' );
    open_td( '', '2.28.2.080' );
    open_td( '', '' );

//  open_tr();
//    open_td( 'colspan=3,center', "(weitere Termine werden noch festgelegt)" );

// 
  open_tr();
    open_th( 'colspan=3,center', 'Lehramt Physik (BEd), 1.Semester' );
 
 
  open_tr();
//    open_td('colspan=3,center', '(Termine werden noch festgelegt)' );
    open_td( '', 'Dienstag, 10-12 Uhr' );
    open_td( '', '2.28.2.080' );
    open_td( '', 'Steffen' );
 
  open_tr();
    open_td( '', 'Donnerstag, 10-12 Uhr' );
    open_td( 'bold', '2.28.2.020' );
    open_td( '', 'Steffen' );
 
//   open_tr();
//     open_td( '', 'Donnerstag, 10-12 Uhr' );
//     open_td( '', '2.28.2.080' );
//     open_td( '', 'Steffen' );

close_table(); 

// open_div( 'medskips', "(weitere Tutoriumstermine werden noch festgelegt!)" );


echo html_tag( 'h2', '', 'MINT-Raum' );

open_div( '', 'Ein den Tutorien am Institut ähnliches Angebot: '. html_alink(
  'http://www.uni-potsdam.de/mnfakul/studium-und-lehre/mint-raum.html'
 , 'class=href outlink,text=Offener MINT Raum: Lernen mit Hilfe von Kommilitonen'
) );

?>
