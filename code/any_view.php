<?php

init_var( 'any_id', 'global,type=U,sources=http persistent,set_scopes=self' );
init_var( 'table', 'global,type=W,sources=http persistent,set_scopes=self' );
init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

define( 'OPTION_SHOW_REFERENCES', 0x01 );

handle_action( array( 'update', 'deleteEntry' ) );

need_priv( $table, 'read', $any_id );

$cols = $tables[ $table ]['cols'];
$row = sql_query( $table, "$any_id,single_row=1,default=0" );

if( ( $v = adefault( $tables[ $table ], 'viewer', '' ) ) ) {
  $v = html_span( 'qquadl', inlink( $v, array( $table.'_id' => $any_id, 'text' => $v ) ) );
}
open_fieldset( '', "entry: $table / $any_id $v" );

  if( ! $row ) {
    open_div( 'warn medskips', 'no such entry' );
  } else {
    open_list();
      open_list_row('header');
        open_list_cell('fieldname');
        open_list_cell('payload');
      foreach( $row as $fieldname => $payload ) {
        open_list_row();
          open_list_cell('fieldname', $fieldname );
          if( $fieldname === "{$table}_id" ) {
            open_list_cell('payload', "self: $table / $payload", 'dgreen' );
          } else {
            open_list_cell('payload', any_field_view( $payload, "$fieldname,validate=1" ) );
          }
      }
    close_list();
  }
  open_div( 'right smallskipt', inlink( '', array( 'text' => 'delete', 'class' => 'button drop', 'action' => 'deleteEntry', 'confirm' => 'really delete?' ) ) );
  
close_fieldset();

if( sql_references( $table, $any_id ) ) {
  if( $options & OPTION_SHOW_REFERENCES ) {
    open_fieldset( '', inlink( '', array(
      'options' => ( $options & ~OPTION_SHOW_REFERENCES )
    , 'class' => 'close_small'
    , 'text' => ''
    ) ) . ' references' );
      references_view( $table, $any_id );
    close_fieldset();
  } else {
    open_div( 'left smallskipb', inlink( ''
    , array( 'options' => ( $options | OPTION_SHOW_REFERENCES ) , 'text' => 'references...', 'class' => 'button' )
    ) );
  }
} else {
  open_div( 'info', '(no references pointing to this entry)' );
}

if( $action === 'deleteEntry' ) {
  need( $any_id );
  sql_delete_entry( $table, $any_id );
  js_on_exit( "flash_close_message($H_SQ entry deleted $H_SQ);" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}


?>
