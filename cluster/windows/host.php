<?php

assert( $logged_in ) or exit();

$editable = true;

get_http_var( 'hosts_id', 'u', 0, true );

if( $hosts_id ) {
  $host = sql_host( $hosts_id );
  $hostname = $host['hostname'];
  $domain = $host['domain'];
  $sequential_number = $host['sequential_number'];
  $ip4 = $host['ip4'];
  $ip6 = $host['ip6'];
  $oid = $host['oid'];
  $processor = $host['processor'];
  $os = $host['os'];
  $invlabel = $host['invlabel'];
  $location = $host['location'];
} else {
  $hostname='';
  $domain="quantum.physik.uni-potsdam.de";
  $sequential_number = 1;
  $ip4 = "$ip4_prefix";
  $ip6 = '';
  $oid = "$oid_prefix.5.";
  $processor = '';
  $os = '';
  $invlabel = '';
  $location = '2.28.2.';
}

get_http_var( 'action', 'w', '' );
$editable or $action = '';
switch( $action ) {
  case 'save':
    need_http_var( 'hostname', '/^[a-z0-9-]+$/' );
    need_http_var( 'domain', '/^[a-z0-9.-]+$/' );
    need_http_var( 'sequential_number', 'U' );
    need_http_var( 'ip4', '/^[0-9.]*$/' );
    need_http_var( 'ip6', '/^[0-9:]*$/' );
    need_http_var( 'oid', '/^[0-9.]+$/' );
    need_http_var( 'processor', 'h' );
    need_http_var( 'os', 'h' );
    need_http_var( 'invlabel', 'h' );
    need_http_var( 'location', 'h' );

    $parts = explode( '.', $ip4 );
    $ip4 = sprintf( "%03u.%03u.%03u.%03u", $parts[0], $parts[1], $parts[2], $parts[3] );

    $values = array(
      'fqhostname' => "$hostname.$domain"
    , 'sequential_number' => $sequential_number
    , 'ip4' => $ip4
    , 'ip6' => $ip6
    , 'oid' => $oid
    , 'processor' => $processor
    , 'os' => $os
    , 'invlabel' => $invlabel
    , 'location' => $location
    );
    if( $hosts_id ) {
      sql_update( 'hosts', $hosts_id, $values );
    } else {
      $hosts_id = sql_insert( 'hosts', $values );
      $self_fields['hosts_id'] = $hosts_id;
    }
    break;
}

open_form( '', 'action=save' );
  open_fieldset( 'small_form', '', ( $hosts_id ? 'edit host' : 'new host' ) );
    open_table('small_form hfill');
      form_row_text( 'fqhostname: ', 'hostname', 12, $hostname );
        open_span( '', '', '.'. string_view( $domain, 24, 'domain' ) );
        open_span( 'quad', '', '#: '. int_view( $sequential_number, 'sequential_number', 2 ) );
      form_row_text( 'ip4: ', 'ip4', 20, $ip4 );
      form_row_text( 'ip6: ', 'ip6', 30, $ip6 );
      form_row_text( 'oid: ', 'oid', 30, $oid );
      form_row_text( 'processor: ', 'processor', 20, $processor );
        open_span( 'qquad', '', "os: ". string_view( $os, 20, 'os' ) );
      form_row_text( 'invlabel: ', 'invlabel', 10, $invlabel );
        open_span( 'qquad', '', "location: ". string_view( $location, 20, 'location' ) );
      open_tr();
      open_td( 'right', "colspan='2'" );
        submission_button();
    close_table();
  close_fieldset();
close_form();

if( $hosts_id ) {
  open_fieldset( 'small_form', '', 'disks', 'on' );
    disks_view( array( 'hosts_id' => $hosts_id ), false );
  close_fieldset();

  open_fieldset( 'small_form', '', 'accounts', 'on' );
    accounts_view( array( 'hosts_id' => $hosts_id ), false );
  close_fieldset();

  open_fieldset( 'small_form', '', 'services', 'on' );
    services_view( array( 'hosts_id' => $hosts_id ), false );
  close_fieldset();
}

?>
