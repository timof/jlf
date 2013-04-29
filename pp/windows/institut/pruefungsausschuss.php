<?php

foreach( array( 'examBoardMono' , 'examBoardEdu', 'studiesBoard' ) as $boardname ) {
  $board = $boards[ $boardname ];

  open_div( 'medskips' );
    open_table( "th;td:smallskipb;qquads;formcolor;shaded th:smallskipt;black;bold,id=$boardname" );
      open_caption( 'center bold medskips', $board['_BOARD'] );
      foreach( $board as $fname => $function ) {
        if( $fname[ 0 ] === '_' )
          continue;
        open_tr();
          open_th( 'left,colspan=3', $function['function'] );
          $members = sql_offices( "board=$boardname,function=$fname", 'orderby=rank' );
          foreach( $members as $m ) {
            open_tr();
              open_td( '', html_alink_person( $m['people_id'], 'office' ) );
              open_td( '', $m['roomnumber'] );
              open_td( '', $m['telephonenumber'] );
          }
      }
    close_table();
  close_div();
}

?>
