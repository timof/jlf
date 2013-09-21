<?php // /pp/windows/menu/menu.php

sql_transaction_boundary('*');


$f = file_get_contents( './pp/fotos/lehre.jpg.base64' );
open_div('inline_block medskips qqquadr');
  open_div( 'huge bold smallskips a', inlink( 'lehre', 'text='.we('Studies','Studium') ) );
  echo photo_view( $f, 3, 'style=width:360px;height:240px;,url='.inlink( 'lehre', 'context=url' ) );
close_div();



$f = file_get_contents( './pp/fotos/in_the_lab.jpg.base64' );
open_div('inline_block medskips qqquadr');
  open_div( 'huge bold smallskips a', inlink( 'lehre', 'text='.we('Research','Forschung') ) );
  echo photo_view( $f, 3, 'style=width:360px;height:240px;,url='.inlink( 'forschung', 'context=url' ) );
close_div();


echo html_tag( 'h2','bigskipt', we('News','Aktuelles') );

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
echo html_div( '', inlink( 'aktuelles', 'text='.we('more news...','weitere Meldungen...') ) );

$publications = sql_publications(
  'year >= '.( $current_year - 1 )
, array( 'limit_from' => 1 , 'limit_to' => 3 , 'orderby' => 'year DESC, ctime DESC' )
);
if( count( $publications ) >= 2 ) {
  echo html_tag( 'h2','bigskipt', we('Current Publications','Aktuelle Veröffentlichungen') );
  echo publication_columns_view( $publications );
  echo html_div( '', inlink( 'publikationen', 'text='.we('more publications...','weitere Veröffentlichungen...') ) );
}


?>
