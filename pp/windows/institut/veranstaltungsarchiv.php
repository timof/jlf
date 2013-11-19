<?php

sql_transaction_boundary('*');

echo html_tag('h1', '', we('Events','Veranstaltungen') );


$events = sql_events( '', 'orderby=date' );

open_table('td:smallskipt;smallskipb;quads');
  foreach( $events as $r ) {
    $id = $r['events_id'];
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
        $pieces = 0;
        foreach( array( 'people_id', 'pdf', 'url', 'note', 'note' /* counts twice! */ ) as $key ) {
          if( $r[ $key ] ) {
            $pieces++;
          }
        }
        $t = $r['cn'];
//        if( $pieces > 1 ) {
        if( $r['flag_detailview'] ) {
          open_div( 'smallskipb', inlink( 'veranstaltung', array( 'events_id' => $id, 'text' => $r['cn'] ) ) );
        } else if( $r['url'] ) {
          open_div( 'smallskipb', html_alink_view( $r['url'], array( 'class' => 'href outlink', 'text' => $r['cn'] ) ) );
        } else if( $r['pdf'] ) {
          open_div( 'smallskipb', inlink( 'veranstaltung', array( 'class' => 'href file', 'text' => $r['cn'], 'i' => 'pdf', 'f' => 'pdf' ) ) );
        } else {
          open_div( 'smallskipb', $t );
          if( $r['people_id'] ) {
            open_div( '', we('Contact: ','Ansprechpartner: ') . alink_person_view( $r['people_id'] ) );
          }
        }
        if( $r['location'] ) {
          open_div( '', $r['location'] );
        }
  }
close_table();


?>
