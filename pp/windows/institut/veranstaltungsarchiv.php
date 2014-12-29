<?php

sql_transaction_boundary('*');

echo html_tag('h1', '', we('Events','Veranstaltungen') );

$f = init_fields(
  array(
    'year' => "global=1,type=U4,default=$current_year"
  , 'SEARCH' => 'size=40,auto=1,relation=%='
  )
, ''
);
$f['year']['choices'] = sql_query( 'events', 'key_col=year,val_col=year,orderby=year DESC' );

open_div('menubox');
  open_table( 'css');
    open_caption( '', filter_reset_button( $f ) . 'Filter' );
    open_tr();
      open_th( '', we('Year:','Jahr:') );
      open_td( '', selector_year( $f['year'] ) );
    open_tr();
      open_th( '', we('Search:','Suche:') );
      open_td( '', string_element( $f['SEARCH'] ) );
  close_table();
close_div();

$filters = array( 'flag_publish', "date >= {$year}0000" );
if( $year < $current_year ) {
  $filters[] = "date <= {$year}1231";
}
if( $f['SEARCH']['value'] ) {
  $filters['SEARCH %='] = "%{$f['SEARCH']['value']}%";
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
