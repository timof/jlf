<?php // /pp/windows/menu/menu.php

sql_transaction_boundary('*');


open_div('inline_block');

open_div('hugemenu');
  open_tag( 'a', 'class=inline_block medskips nounderline,href='.inlink('lehre', 'context=url' ) );
    open_span( 'block huge bold smallskips underlineifhover', we('Studies','Studium') );
    echo photo_view( '/pp/fotos/lehre2.jpg', '', array( 'caption' => html_span( 'black', 'Quelle: Karla Fritze (AVZ)' ), 'format' => 'url' ) );
  close_tag('a');
  open_tag( 'a', 'class=inline_block medskips nounderline,href='.inlink('forschung', 'context=url' ) );
    open_span( 'block huge bold smallskips underlineifhover', we('Research','Forschung') );
    // echo photo_view( '/pp/fotos/forschung2.jpg', '', array( 'caption' => html_span( 'black', 'Quelle: Prof. Ralf Menzel (Photonik)' ), 'format' => 'url' ) );
    echo photo_view( '/pp/fotos/nopa_mareike.jpg', '', array( 'caption' => html_span( 'black', 'Quelle: Prof. Matias Bargheer' ), 'format' => 'url' ) );
  close_tag('a');
close_div();


$tickeritems = array();

$events = sql_events(
  array( 'flag_ticker', 'flag_publish', array( '||', 'date=0', "date>=$today_canonical" ) )
, 'orderby=date'
);
foreach( $events as $r ) {
  $tickeritems[] = event_view( $r, 'format=ticker' );
}

$tickeritems[] = html_span( 'tickerline', alink_document_view( 'type=VVZ', 'format=latest' ) );
$tickeritems[] = html_span( 'tickerline', inlink( 'tutorium', 'text='.we('Tutorial in Winter term 2013/14','Tutorium im Wintersemester 2013/14') ) );
$tickeritems[] = html_span( 'tickerline', html_alink( 'https://141.89.115.248/Ab2013', 'class=href outlink,text='.we('Degree ceremony 2013 - Photos','Fotos der Absolventenfeier 2013') ) );

open_div( 'id=tickerbox,medskips' );
  echo html_tag( 'h2','', we('News','Aktuelles') );

  foreach( $tickeritems as $r ) {
    echo html_div( 'tickeritem', "+++$NBSP$NBSP$r$NBSP$NBSP+++" );
  }
  echo html_div( 'smallskipt', inlink( 'veranstaltungsarchiv', 'text='.we('more events...','Veranstaltungsarchiv...') ) );
close_div();

close_div();

$publications = sql_publications(
  array(
    'year >= '=> ( $current_year - 1 )
  , 'groups.flag_publish'
  )
, array( 'limit_from' => 1 , 'limit_count' => 3 , 'orderby' => 'year DESC, ctime DESC' )
);
if( count( $publications ) >= 2 ) {
  echo html_tag( 'h2','bigskipt', we('Recent Publications','Aktuelle Veröffentlichungen') );
  echo publication_block_view( $publications );
  echo html_div( '', inlink( 'publikationen', 'text='.we('more publications...','weitere Veröffentlichungen...') ) );
}



?>
