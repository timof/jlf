<?php

echo html_tag( 'h1', '', 'Tutorium' );

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
    open_th( 'colspan=3,center', 'MonoBachelor Physik (BSc), 2.Semester' );

  open_tr();
    open_td( '', 'Mittwoch, 12-14 Uhr' );
    open_td( '', '2.28.2.080' );
    open_td( '', 'Timon' );


  open_tr();
    open_th( 'colspan=3,center', 'Lehramt Physik (BEd), 2.Semester' );


  open_tr();
    open_td( '', 'Donnerstag, 10-12 Uhr' );
    open_td( '', '2.28.2.080' );
    open_td( '', 'Steffen' );

close_table(); 

open_div( 'medskips', "(weitere Tutoriumstermine werden noch festgelegt!)" );


?>
