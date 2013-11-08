<?php // /pp/windows/menu/menu.php

sql_transaction_boundary('*');


open_div('inline_block medskips qqquadr');
  open_div( 'huge bold smallskips a', inlink( 'lehre', 'text='.we('Research','Forschung') ) );
  echo photo_view( '/pp/fotos/forschung2.jpg', array( 'gn' => 'horst', 'sn' => 'gebert' ), 'format=url,style=width:360px;height:240px;,url='.inlink( 'forschung', 'context=url' ) );
close_div();

open_div('inline_block medskips qqquadr');
  open_div( 'huge bold smallskips a', inlink( 'lehre', 'text='.we('Studies','Lehre') ) );
  echo photo_view( '/pp/fotos/lehre2.jpg', array( 'gn' => 'horst', 'sn' => 'gebert' ), 'format=url,style=width:360px;height:240px;,url='.inlink( 'lehre', 'context=url' ) );
close_div();


open_div( 'id=tickerbox,medskips' );
  echo html_tag( 'h2','', we('News','Aktuelles') );
  
  $events = sql_events( '', 'orderby=date' );
  foreach( $events as $r ) {
    $t = '';
    if( ( $date = $r['date'] ) ) {
      $t .= substr( $date, 6, 2 ) .'.'. substr( $date, 4, 2 ) .'.';
      if( ( $time = $r['time'] ) ) {
        $t .= ', '.substr( $time, 0, 2 ) .':'. substr( $time, 2, 2 ) . we('',' Uhr');
      }
      $t .= ': ';
    }
    $t .= inlink( 'event_view', array( 'text' => $r['cn'], 'events_id' => $r['events_id'] ) );
    open_div( 'ticker', '+++ '. $t . ' +++' );
  }
  echo html_div( '', inlink( 'veranstaltungsarchiv', 'text='.we('more news...','weitere Meldungen...') ) );
close_div();

$publications = sql_publications(
  'year >= '.( $current_year - 1 )
, array( 'limit_from' => 1 , 'limit_to' => 3 , 'orderby' => 'year DESC, ctime DESC' )
);
if( count( $publications ) >= 2 ) {
  echo html_tag( 'h2','bigskipt', we('Recent Publications','Aktuelle Veröffentlichungen') );
  echo publication_block_view( $publications );
  echo html_div( '', inlink( 'publikationen', 'text='.we('more publications...','weitere Veröffentlichungen...') ) );
}


?>
