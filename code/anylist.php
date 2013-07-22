<?php

$f = init_fields( array(
  'table' => 'type=w,sources=http persistent,set_scopes=self'
) );
$table =& $f['table']['value'];

open_div('menu');
  open_table('css hfill');
    open_caption( 'center th', html_span( 'floatright', filter_reset_button( $f ) ) . 'Filter' );
    open_tr();
      open_th( '', 'table:' );
      open_td( '', selector_table( $f['table'] ) );
  close_table();
close_div();
bigskip();

if( "$table" === '' ) {
  return;
}

need_priv( $table, 'list' );

$cols = $tables[ $table ]['cols'];
  
$tcols = array();
foreach( $cols as $col => $props ) {
  $tcols[ $col ] = "s,t,h=$col";
}


$list_options = handle_list_options( true, $table, $tcols );

$rows = sql_query( $table, array( 'orderby' => $list_options['orderby_sql'] ) );
$count = count( $rows );
$limits = handle_list_limits( $list_options, $count );
$list_options['limits'] = & $limits;

$pri = $table.'_id';

open_list( $list_options );
  open_list_row('header');
    foreach( $tcols as $c => $props ) {
      open_list_cell( $c );
    }
  foreach( $rows as $r ) {
    open_list_row();
      foreach( $tcols as $c => $props ) {
        $payload = $r[ $c ];
        if( ! check_utf8( $payload ) ) {
          $payload = html_span( 'bold italic', '(binary data)' );
        } else if( preg_match( '/^([a-zA-Z0-9_]*_)?([a-zA-Z0-9]+)_id$/', $c, /* & */ $v ) ) {
          $payload = entry_link( $v[ 2 ], $payload );
        } else {
          $payload = substr( $payload, 0, 32 );
        }
        open_list_cell( $c, $payload );
      }
  }

close_list();


?>
