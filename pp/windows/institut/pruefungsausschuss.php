<?php

sql_transaction_boundary('*');

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('h28innenhof');
    echo html_tag( 'h1', '', we('Institute / Committees','Institut / Ausschüsse') );
  close_div();
close_div();


// open_div('inline_block'); // to force equal table widths

foreach( array( 'examBoardMono' , 'examBoardEdu', 'studiesBoard' ) as $boardname ) {
  $board = $boards[ $boardname ];

  open_ccbox( '', $board['_BOARD'] );
    open_table( "hfill th;td:smallskipb;qquads th:smallskipb;black;bold;solidtop,id=$boardname" );
      foreach( $board as $fname => $function ) {
        if( $fname[ 0 ] === '_' )
          continue;
        open_tr();
          open_th( 'left,colspan=4', $function['function'] );
          $members = sql_offices( "board=$boardname,function=$fname", 'orderby=rank' );
          foreach( $members as $m ) {
            open_tr();
              open_td( '', alink_person_view( $m['people_id'], 'office' ) );
              open_td( '', $m['roomnumber'] );
              open_td( '', $m['telephonenumber'] );
              open_td( 'style=max-width:200px;', $m['office_hours'] );
          }
      }
    close_table();
  close_ccbox();
}


?>
