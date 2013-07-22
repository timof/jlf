<?php

init_var( 'id', 'global,type=U,sources=http persistent,set_scopes=self' );
init_var( 'table', 'global,type=W,sources=http persistent,set_scopes=self' );


need_priv( $table, 'read', $id );

$cols = $tables[ $table ]['cols'];
$row = sql_query( $table, $id, 'single_row,default=0' );

open_fieldset( '', "entry: $table / $id" );

  if( ! $row ) {
    open_div( 'warn', 'no such entry' );
  } else {
    open_list();
      open_list_row('header');
        open_list_cell('fieldname');
        open_list_cell('payload');
      foreach( $row as $fieldname => $payload ) {
        open_list_row();
          open_list_cell('fieldname', $fieldname );
          if( ! check_utf8( $payload ) ) {
            $payload = html_span( 'bold italic', '(binary data)' );
          }
          open_list_cell('payload', substr( $payload, 0, 64 ) );
      }
    close_list();
  }
  
close_fieldset();


?>
