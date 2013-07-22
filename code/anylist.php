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
  

$rows = sql_query( $table );

echo count( $rows );



?>
