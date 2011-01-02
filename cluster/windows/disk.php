<?php

assert( $logged_in ) or exit();

$editable = true;

get_http_var( 'disks_id', 'u', 0, true );

if( $disks_id ) {
  $disk = sql_disk( $disks_id );
  $cn = $disk['cn'];
  $type_disk = $disk['type_disk'];
  $description = $disk['description'];
  $oid = $disk['oid'];
  $sizeGB = $disk['sizeGB'];
  $location = $disk['location'];
  $hosts_id = $disk['hosts_id'];
} else {
  $cn = '';
  $type_disk = '';
  $description = '';
  $oid = "$oid_prefix.4.";
  $sizeGB = '';
  $location = '';
  $hosts_id = 0;
}

get_http_var( 'action', 'w', '' );
$editable or $action = '';
switch( $action ) {
  case 'save':
    need_http_var( 'cn', 'W' );
    need_http_var( 'type_disk', '' );
    need_http_var( 'description', 'h' );
    need_http_var( 'oid', '/^[0-9.]+$/' );
    need_http_var( 'sizeGB', 'U' );
    need_http_var( 'location', 'h' );
    need_http_var( 'hosts_id', 'u' );

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
    break;
}

open_form( '', "action=save" );
  open_fieldset( 'small_form', '', ( $disks_id ? 'edit disk' : 'new disk' ) );
    open_table('small_form hfill');
      form_row_text( 'cn: ', 'cn', 10, $cn );
        open_span( 'quad' );
          echo 'type: ';
          open_select( 'type_disk', '', html_options_type_disk( $type_disk ) );
        close_span();
        open_span( 'quad', '', 'size: '. int_view( $sizeGB, 'sizeGB', 5 ).'GB' );
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
