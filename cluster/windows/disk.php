<?php

get_http_var( 'disks_id', 'u', 0, true );
$disk = ( $disks_id ? sql_disk( $disks_id ) : false );
row2global( 'disks', $disk );

$problems = array();

get_http_var( 'cn', 'w', $cn );
get_http_var( 'type_disk', 'w', $type_disk );
get_http_var( 'description', 'h', $description );
get_http_var( 'oid', '/^[0-9.]+$/', $oid );
get_http_var( 'sizeGB', 'u', $sizeGB );
get_http_var( 'location', 'h', $location );
get_http_var( 'hosts_id', 'u', $hosts_id );

handle_actions( array( 'update', 'save' ) );
switch( $action ) {
  case 'save':
    if( ! $cn )
      $problems[] = 'cn';
    if( ! $type_disk )
      $problems[] = 'type_disk';
    if( ! $sizeGB )
      $problems[] = 'sizeGB';
    if( ! $problems ) {
      $values = array(
        'cn' => "$cn"
      , 'type_disk' => $type_disk
      , 'description' => $description
      , 'oid' => $oid
      , 'sizeGB' => $sizeGB
      , 'location' => $location
      , 'hosts_id' => $hosts_id
      );
      if( $disks_id ) {
        sql_update( 'disks', $disks_id, $values );
      } else {
        $disks_id = sql_insert( 'disks', $values );
        $self_fields['disks_id'] = $disks_id;
      }
    }
    break;
}

open_form( 'name=update_form', "action=save" );
  open_fieldset( 'small_form', '', ( $disks_id ? 'edit disk' : 'new disk' ) );
    open_table('small_form hfill');
      form_row_text( 'cn: ', 'cn', 10, $cn );
        open_span( 'quad '.problem_class('type_disk') );
          echo 'type: ';
          open_select( 'type_disk', '', html_options_type_disk( $type_disk ) );
        close_span();
        open_span( 'quad '.problem_class('sizeGB'), '', 'size: '. int_view( $sizeGB, 'sizeGB', 5 ).'GB' );
      form_row_text( 'description: ', 'description', 30, $description );
      form_row_text( 'oid: ', 'oid', 30, $oid );
      form_row_text( 'location: ', 'location', 10, $location );
        open_span( 'qquad' );
          echo 'host: ';
          open_select( 'hosts_id', '', html_options_hosts( $hosts_id, false, ' (none) ' ) );
        close_span();
      open_tr();
      open_td( 'right', "colspan='2'" );
        submission_button();
    close_table();
  close_fieldset();
close_form();

?>
