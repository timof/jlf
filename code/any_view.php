<?php

init_var( 'any_id', 'global,type=U,sources=http persistent,set_scopes=self' );
init_var( 'table', 'global,type=W,sources=http persistent,set_scopes=self' );


need_priv( $table, 'read', $any_id );

$cols = $tables[ $table ]['cols'];
$row = sql_query( $table, "$any_id,single_row=1,default=0" );

open_fieldset( '', "entry: $table / $any_id" );

  if( ! $row ) {
    open_div( 'warn medskips', 'no such entry' );
  } else {
    if( ( $v = adefault( $tables[ $table ], 'viewer' ) ) ) {
      open_div( 'medskips oneline', inlink( $v, array( $table.'_id' => $any_id, 'class' => 'href inlink', 'text' => 'viewer: '.$v ) ) );
    }
    open_list();
      open_list_row('header');
        open_list_cell('fieldname');
        open_list_cell('payload');
      foreach( $row as $fieldname => $payload ) {
        open_list_row();
          open_list_cell('fieldname', $fieldname );
          if( ! check_utf8( $payload ) ) {
            $payload = html_span( 'bold italic', '(binary data)' );
          } else if( preg_match( '/^([a-zA-Z0-9_]*_)?([a-zA-Z0-9]+)_id$/', $fieldname, /* & */ $v ) ) {
            $payload = ( $payload ? inlink( 'any_view', array( 'table' => $v[ 2 ], 'any_id' => $payload, 'text' => "{$v[2]} / $payload" ) ) : 'NULL' );
          } else {
            $payload = substr( $payload, 0, 64 );
          }
          open_list_cell('payload', $payload );
      }
    close_list();
  }
  
close_fieldset();


?>
