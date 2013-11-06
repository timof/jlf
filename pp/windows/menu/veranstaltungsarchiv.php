<?php

sql_transaction_boundary('*');

echo html_tag('h1', '', we('Events','Veranstaltungen') );


$events = sql_events( '', 'orderby=date' );

open_table('td:smallskipt;smallskipb;quads');
  foreach( $events as $r ) {
    if( ! ( $date = $r['date'] ) ) {
      continue;
    }
    open_tr();
      $t = substr( $date, 6, 2 ) .'.'. substr( $date, 4, 2 ) .'.';
      if( ( $time = $r['time'] ) ) {
        $t .= ', '.substr( $time, 0, 2 ) .':'. substr( $time, 2, 2 ) . we('',' Uhr');
      }
      open_td('oneline', $t );
      open_td();
        open_div( 'smallskipb', $r['cn'] );
        if( $r['location'] ) {
          open_div( '', $r['location'] );
        }
  }
close_table();


?>
