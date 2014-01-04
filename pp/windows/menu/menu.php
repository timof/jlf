<?php // /pp/windows/menu/menu.php

sql_transaction_boundary('*');


open_div('inline_block');

open_div('hugemenu');
  open_tag( 'a', 'class=inline_block medskips nounderline,href='.inlink('lehre', 'context=url' ) );
    open_span( 'block huge bold smallskips underlineifhover', we('Studies','Studium') );
    echo photo_view( '/pp/fotos/lehre2.jpg', '', array( 'caption' => html_span( 'black', 'Quelle: Dr. Horst Gebert' ), 'format' => 'url' ) );
  close_tag('a');
  open_tag( 'a', 'class=inline_block medskips nounderline,href='.inlink('forschung', 'context=url' ) );
    open_span( 'block huge bold smallskips underlineifhover', we('Research','Forschung') );
    echo photo_view( '/pp/fotos/forschung2.jpg', '', array( 'caption' => html_span( 'black', 'Quelle: Dr. Horst Gebert' ), 'format' => 'url' ) );
  close_tag('a');
close_div();


$tickeritems = array(
  html_span( 'tickerline', alink_document_view( 'type=VVZ', 'format=latest' ) )
, html_span( 'tickerline', inlink( 'tutorium', 'text='.we('Tutorial in Winter term 2013/14','Tutorium im Wintersemester 2013/14') ) )
);

$events = sql_events(
  array( 'flag_ticker', 'flag_publish', array( '||', 'date=0', "date>=$today_canonical" ) )
, 'orderby=date'
);
foreach( $events as $r ) {
  $tickeritems[] = event_view( $r, 'format=ticker' );
}

open_div( 'id=tickerbox,medskips' );
  echo html_tag( 'h2','', we('News','Aktuelles') );

  foreach( $tickeritems as $r ) {
    echo html_div( 'tickeritem', "+++$NBSP$NBSP$r$NBSP$NBSP+++" );
  }
  echo html_div( 'smallskipt', inlink( 'veranstaltungsarchiv', 'text='.we('more events...','Veranstaltungsarchiv...') ) );
close_div();

close_div();

$publications = sql_publications(
  'year >= '.( $current_year - 1 )
, array( 'limit_from' => 1 , 'limit_count' => 3 , 'orderby' => 'year DESC, ctime DESC' )
);
if( count( $publications ) >= 2 ) {
  echo html_tag( 'h2','bigskipt', we('Recent Publications','Aktuelle Veröffentlichungen') );
  echo publication_block_view( $publications );
  echo html_div( '', inlink( 'publikationen', 'text='.we('more publications...','weitere Veröffentlichungen...') ) );
}



?>
