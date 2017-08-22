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
    // echo photo_view( '/pp/fotos/nopa_mareike.jpg', '', array( 'caption' => html_span( 'black', 'Quelle: Andre Bojahr' ), 'format' => 'url' ) );
    echo photo_view( '/pp/fotos/bargheer3.jpg', '', array( 'caption' => html_span( 'black', 'Quelle: Matias Bargheer' ), 'format' => 'url' ) );
  close_tag('a');
close_div();



// news ticker
//
$items = array();
$events = sql_events(
  array( 'flag_ticker', 'flag_publish', array( '||', 'date=0', "date>=$today_canonical" ) )
, 'orderby=date'
);
foreach( $events as $r ) {
  $items[] = event_view( $r, 'format=ticker' );
}
// $items[] = html_span( 'tickerline', inlink( 'tutorium', 'text='.we('Tutorial in Summer term 2017','Tutorium im Sommersemester 2017') ) );
$items[] = html_span( 'tickerline', inlink( 'einschreibung', array( 'text' => we('Information for prospective students', "Informationen f{$uUML}r Studieninteressierte" ) ) ) );
// $items[] = html_span( 'tickerline', alink_document_view( 'type=VVZ,flag_current', 'format=latest' ) );
open_div( 'id=tickerbox,medskips' );
  echo html_tag( 'h2','', we('News','Aktuelles') );

  foreach( $items as $r ) {
    echo html_div( 'tickeritem', "+++$NBSP$NBSP$r$NBSP$NBSP+++" );
  }
  echo html_div( 'smallskipt', inlink( 'veranstaltungsarchiv', 'text='.we('more events...','Veranstaltungsarchiv...') ) );
close_div();


// highlights
//
$items = array();
$highlights = sql_highlights(
  array( 'flag_publish' )
, array( 'limit_from' => 1, 'limit_count' => 3, 'orderby' => 'ctime DESC' )
);
foreach( $highlights as $r ) {
  $items[] = highlight_view( $r, 'format=highlight' );
}
if( $items ) {
  open_div( 'ccbox medskips' );
    echo html_tag( 'h2','', we('Highlights','Aus dem Institut') );
    foreach( $items as $r ) {
      echo $r;
    }
  close_div();
}



// publications --- currently unused
//
// $publications = sql_publications(
//   array(
//     'year >= '=> ( $current_year - 1 )
//   , 'groups.flag_publish'
//   )
// , array( 'limit_from' => 1 , 'limit_count' => 20 , 'orderby' => 'year DESC, ctime DESC' )
// );
$publications = array();
if( count( $publications ) >= 3 ) {
  shuffle( $publications );
  open_div( 'highlight nopads smallskipt' );
  echo html_tag( 'h2', 'tinyskipb tinypadb', we('Recent Publications','Aktuelle Veröffentlichungen') );
  $n = 0;
  foreach( $publications as $pub ) {
    if( ++$n > 3 ) {
      break;
    }
    open_div( 'highlight tinypads', publication_reference_view( $pub ) );
//     $s = html_span( 'block' );
//     $s .= html_span( 'block cn tinyskipb noskipt nopadt larger bold', $pub['cn'] );
//     $t = $pub['summary'];
//     if( strlen( $t ) > 200 ) {
//       $t = trim( substr( $t, 0, 195 ) ) . '...';
//     }
//     $s .= html_span( 'summary', $t );
//     $s .= html_span( false );
//     echo inlink( 'publikation', array( 'class' => 'href', 'text' => $s, 'publications_id' => $pub['publications_id'] ) );
//     echo html_div( 'tinyskips', we('Research group: ','Arbeitsgruppe: ') . alink_group_view( $pub['groups_id'], 'fullname=1' ) );
//     close_div();
  }
  // echo publication_block_view( $spub );
  echo html_div( 'smallskipt', inlink( 'publikationen', 'text='.we('more publications...','weitere Veröffentlichungen...') ) );
  close_div();
}

close_div();


?>
