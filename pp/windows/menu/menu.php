<?php // /pp/windows/menu/menu.php

sql_transaction_boundary('*');


open_div('hugemenu');
  open_tag( 'a', 'class=inline_block medskips nounderline qqquadr,href='.inlink('forschung', 'context=url' ) );
    open_div( 'huge bold smallskips underlineifhover', we('Research','Forschung') );
    echo photo_view( '/pp/fotos/forschung2.jpg', '', array( 'caption' => html_span( 'black', 'Quelle: Dr. Horst Gebert' ), 'format' => 'url' ) );
  close_tag('a');

  open_tag( 'a', 'class=inline_block medskips nounderline qqquadr,href='.inlink('lehre', 'context=url' ) );
    open_div( 'huge bold smallskips underlineifhover', we('Studies','Lehre') );
    echo photo_view( '/pp/fotos/lehre2.jpg', '', array( 'caption' => html_span( 'black', 'Quelle: Dr. Horst Gebert' ), 'format' => 'url' ) );
  close_tag('a');
close_div();


open_div( 'id=tickerbox,medskips' );
  echo html_tag( 'h2','', we('News','Aktuelles') );
  
  $events = sql_events(
    array( 'flag_highlight', 'flag_publish', array( '||', 'date=0', "date>=$today_canonical" ) )
  , 'orderby=date'
  );
  foreach( $events as $r ) {
    echo event_view( $r, 'format=ticker' );
  }
  echo html_div( 'smallskipt', inlink( 'veranstaltungsarchiv', 'text='.we('more events...','Veranstaltungsarchiv...') ) );
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
