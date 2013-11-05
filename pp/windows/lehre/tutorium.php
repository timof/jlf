<?php

echo html_tag( 'h1', '', 'Tutorium' );


open_table( 'th;td:smallskipb;qquads;oneline th:black;bold;solidtop,id=tutorium,colgroup=40% 30% 30%' );
  open_tr();
    open_th('', 'Termine' );
    open_th('', "R{$aUML}ume" );
    open_th('', "Tutoren" );

  open_tr();
    open_th( 'colspan=3,center', 'MonoBachelor Physik (BSc), 1.Semester' );

  open_tr();
    open_td( '', 'Dienstag, 16-18 Uhr' );
    open_td( '', 'tba' );
    open_td( '', 'tba' );

  open_tr();
    open_td( '', 'Mittwoch, 16-18 Uhr' );
    open_td( '', 'tba' );
    open_td( '', 'tba' );

  open_tr();
    open_td( '', 'Donnerstag, 16-18 Uhr' );
    open_td( '', 'tba' );
    open_td( '', 'tba' );

  open_tr();
    open_th( 'colspan=3,center', 'Lehramt Physik (BEd), 1.Semester' );

  open_tr();
    open_td( '', 'Montag, 18-20 Uhr' );
    open_td( '', '2.28.2.080' );
    open_td( '', 'Lukas' );

  open_tr();
    open_td( '', 'Dienstag, 10-12 Uhr' );
    open_td( '', '2.28.2.080' );
    open_td( '', 'Michi' );

  open_tr();
    open_td( '', 'Donnerstag, 14-16 Uhr' );
    open_td( '', '2.28.2.080' );
    open_td( '', 'Steffen' );

close_table(); 

open_span( 'kommentar', "
   Die Teilnahme an den Tutorien ist freiwillig. Sie werden von Studenten in h{$oUML}heren Semestern f{uUML}r
   Studienanf{$aUML}nger abgehalten und sollen bei Problemen (vor allem physikalischen) helfen.
" );

?>
