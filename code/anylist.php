<?php

need_priv( '*', 'read' );

define( 'OPTION_SHOW_DANGLING', 0x04 );

$f = init_fields( array(
  'table' => 'global=table,type=w,sources=http persistent,set_scopes=self'
, 'options' => 'global=options,type=u,sources=http persistent,set_scopes=window'
) );

if( $table ) {
  $dangling_links = sql_dangling_links( "tables=$table" );
  $dangling_links = $dangling_links[ $table ];
  $count = 0;
  $dangling_ids = array();
  foreach( $dangling_links as $col => $ids ) {
    $count += count( $ids );
    $dangling_ids += $ids;
  }
  if( ! $count ) {
    $options &= ~OPTION_SHOW_DANGLING;
  }
} else {
  $dangling_links = sql_dangling_links();
//  $options &= ~OPTION_SHOW_DANGLING;
}

open_div('menubox');
  open_table('css filters');
    open_caption( '', 'options' );
    open_tr('td:smallpads;qquads');
      open_th( '', 'table:' );
      open_td( 'oneline', selector_table( $f['table'] ) . filter_reset_button( $f['table'], '/floatright//' ) );
if( $table ) {
    open_tr('td:smallpads;qquads');
      open_th( '', 'dangling links:' );
      open_td( 'oneline' );
      if( $count ) {
        echo span_view( 'bold red qquads', $count );
        echo checkbox_element( array(
        'name' => 'options'
        , 'normalized' => $options
        , 'mask' => OPTION_SHOW_DANGLING
        , 'text' => 'show'
        , 'auto' => 1
        ) );
      } else {
        echo "(none)";
      }
}
  close_table();
close_div();

if( "$table" === '' ) {

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
  
  $filters = array();
  if( $options & OPTION_SHOW_DANGLING ) {
    $filters['id'] = array_unique( array_keys( $dangling_ids ) );
  }
  $rows = sql_query( $table, array( 'filters' => $filters, 'orderby' => $list_options['orderby_sql'] ) );
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
            if( $payload ) {
              $payload = any_link( $v[ 2 ], $payload, array( 'validate' => ( $options & OPTION_SHOW_DANGLING ) ) );
            } else {
              $payload = 'NULL';
            }
          } else {
            $payload = substr( $payload, 0, 32 );
          }
          open_list_cell( $c, $payload );
        }
    }
  
  close_list();
}


?>
