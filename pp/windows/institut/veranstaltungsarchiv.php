<?php

sql_transaction_boundary('*');

echo html_tag('h1', '', we('Events','Veranstaltungen') );

$f = init_fields(
  array(
    'year' => "global=1,type=U4,min=2013,max=$current_year,default=$current_year"
  , 'REGEX' => 'size=40,auto=1'
  )
, ''
);

open_div('menubox');
  open_table( 'css');
    open_caption( '', filter_reset_button( $f ) . 'Filter' );
    open_tr();
      open_th( '', we('Year:','Jahr:') );
      open_td( '', selector_year( $f['year'] ) );
//    open_tr();
//      open_th( '', we('Search:','Suche:') );
//      open_td( '', ' / '.string_element( $f['REGEX'] ).' / ' );
  close_table();
close_div();

$filters = array( 'flag_publish', "date >= {$year}0000" );
if( $year < $current_year ) {
  $filters[] = "date <= {$year}1231";
}

$events = sql_events( $filters, 'orderby=date' );

if( $events ) {
  open_table('events');
    foreach( $events as $r ) {
      echo event_view( $r, 'format=table' );
    }
  close_table();
} else {
  open_div('smallskips', we('no events found','keine Veranstaltungen gefunden') );
}

?>
