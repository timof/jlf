<?php

foreach( array( 'examBoardMono' , 'examBoardEdu', 'studiesBoard' ) as $boardname ) {
  $board = $boards[ $boardname ];

  open_div( 'medskips' );
    open_table( "th;td:smallskipb;qquads th:smallskipb;black;bold;solidtop,id=$boardname" );
      open_caption( 'center bold medskips large', $board['_BOARD'] );
      foreach( $board as $fname => $function ) {
        if( $fname[ 0 ] === '_' )
          continue;
        open_tr();
          open_th( 'left,colspan=3', $function['function'] );
          $members = sql_offices( "board=$boardname,function=$fname", 'orderby=rank' );
          foreach( $members as $m ) {
            open_tr();
              open_td( '', alink_person_view( $m['people_id'], 'office' ) );
              open_td( '', $m['roomnumber'] );
              open_td( '', $m['telephonenumber'] );
          }
      }
    close_table();
  close_div();
}

?>
