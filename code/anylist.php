<?php

need_priv( '*', 'read' );

$f = init_fields( array('table' => 'type=w,sources=http persistent,set_scopes=self'
) );
$table =& $f['table']['value'];

open_div('menubox');
  open_table('css filters');
    open_caption( '', 'options' );
    open_tr('td:smallpads;qquads');
      open_th( '', 'table:' );
      open_td( 'oneline', selector_table( $f['table'] ) . filter_reset_button( $f['table'], '/floatright//' ) );
  close_table();
close_div();

if( "$table" === '' ) {

  $dangling_links = sql_dangling_links();
  open_list();
    open_list_row('header');
      open_list_cell('table');
      open_list_cell('cardinality');
      open_list_cell('dangling links');
    foreach( $tables as $tname => $props ) {
      open_list_row();
        open_list_cell('table', inlink( '', "table=$tname,text=$tname" ) );
        open_list_cell('cardinality', sql_query( $tname, 'single_field=COUNT' ), 'number' );

        $t = '';
        foreach( $dangling_links[ $tname ] as $col => $ids ) {
          if( ( $count = count( $ids ) ) ) {
            $t .= span_view( 'block number', "$col:$count" );
          }
        }
        open_list_cell('dangling links', $t ? $t : ' - ', 'number' );
    }
  close_list();

} else {
  
  $cols = $tables[ $table ]['cols'];

  $tcols = array( $table.'_id' => "s,t,h=$col" ); // make sure primary key comes first
  foreach( $cols as $col => $props ) {
    $tcols[ $col ] = "s,t,h=$col";
  }

  $list_options = handle_list_options( true, $table, $tcols );
  
  $rows = sql_query( $table, array( 'orderby' => $list_options['orderby_sql'] ) );
  $count = count( $rows );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;
  
  open_list( $list_options );
    open_list_row('header');
      foreach( $tcols as $c => $props ) {
        open_list_cell( $c );
      }
    foreach( $rows as $r ) {
      if( $r['nr'] < $limits['limit_from'] )
        continue;
      if( $r['nr'] > $limits['limit_to'] )
        break;
      open_list_row();
        foreach( $tcols as $c => $props ) {
          $payload = $r[ $c ];
          if( ! check_utf8( $payload ) ) {
            $payload = html_span( 'bold italic', '(binary data)' );
          } else if( preg_match( '/^([a-zA-Z0-9_]*_)?([a-zA-Z0-9]+)_id$/', $c, /* & */ $v ) ) {
            $payload = ( $payload ? inlink( 'any_view', array( 'table' => $v[ 2 ], 'any_id' => $payload, 'text' => "{$v[2]} / $payload" ) ) : 'NULL' );
          } else {
            $payload = substr( $payload, 0, 32 );
          }
          open_list_cell( $c, $payload );
        }
    }
  
  close_list();
}


?>
