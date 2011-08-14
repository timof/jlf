<?php

init_global_var( 'hosts_id', 'u', 'http,persistent', 0, 'self' );
if( $hosts_id ) {
  $host = sql_one_host( $hosts_id );
  $oid_t = $host['oid_t'] = oid_canonical2traditional( $host['oid'] );
  $ip4_t = $host['ip4_t'] = oid_canonical2traditional( $host['ip4'] );
  $hostname = $host['hostname'] = $host['fqhostname'];
  $domain = $host['domain'] = '';
  if( ( $n = strpos( $hostname, '.' ) ) !== false ) {
    $domain = $host['domain'] = substr( $hostname, $n + 1 );
    $hostname = $host['hostname'] = substr( $hostname, 0, $n );
  }
} else { 
  $host = false;
}
row2global( 'hosts', $host );

$fields = array(
  'hostname' => '/^[a-z0-9-]+$/'
, 'domain' => '/^[a-z0-9.-]+$/'
, 'sequential_number' => 'U'
, 'ip4_t' => '/^[0-9.]*$/'
, 'ip6' => '/^[0-9:]*$/'
, 'oid_t' => '/^[0-9.]+$/'
, 'processor' => 'H'
, 'os' => 'H'
, 'invlabel' => 'W'
, 'active' => '^[01]$'
, 'location' => 'H'
);
$changes = array();
$problems = array();
foreach( $fields as $fieldname => $type ) {
  // prettydump( $$fieldname, "$fieldname 1" );
  init_global_var( $fieldname, $type, 'http,persistent,keep', '', 'self' );
  // prettydump( $$fieldname, "$fieldname 2" );
  if( $hosts_id ) {
    if( $GLOBALS[ $fieldname ] !== $host[ $fieldname ] ) {
      $changes[ $fieldname ] = 'modified';
    }
  }
}

handle_action( array( 'update', 'save', 'init', 'template' ) );
switch( $action ) {
  case 'template':
    $hosts_id = 0;
    break;

  case 'init':
    $hosts_id = 0;
    $oid_t = $oid_prefix;
    $ip4_t = $ip4_prefix;
    $domain = $default_domain;
    break;

  case 'save':
    $values = array();
    foreach( $fields as $fieldname => $type ) {
      if( checkvalue( $fieldname, $type ) !== NULL ) {
        $values[ $fieldname ] = $$fieldname;
      } else {
        $problems[ $fieldname ] = 'type mismatch';
      }
    }
    if( in_array( 'domain', $problems ) ) {
      $problems['hostname'] = 'type mismatch';
    }
    if( ! $problems ) {
      $values['ip4'] = ip4_traditional2canonical( $values['ip4_t'] );
      unset( $values['ip4_t'] );
      $values['oid'] = oid_traditional2canonical( $values['oid_t'] );
      unset( $values['oid_t'] );
      $values['fqhostname'] = "$hostname.$domain";
      unset( $values['hostname'] );
      unset( $values['domain'] );

      if( $hosts_id ) {
        sql_update( 'hosts', $hosts_id, $values );
      } else {
        $hosts_id = sql_insert( 'hosts', $values );
      }
    }
    break;
}

if( $hosts_id ) {
  open_fieldset( 'small_form', 'edit host' );
} else {
  open_fieldset( 'small_form new', 'new host' );
}
  open_table( 'hfill' );
    open_tr();
      open_td();
        open_label( 'hostname', field_class('domain'), '', 'fqhostname:' );
      open_td( 'oneline', '', false, 2 );
        $c = field_class('hostname');
        open_input( 'hostname', 'qquad', '', string_view( $hostname, 15, 'hostname' ) );
        open_span( 'quads', '.' );
        open_input( 'domain', '', '', string_view( $domain, 25, 'domain' ) );
    open_tr();
      open_td();
        open_label( 'ip4_t', '', '', 'ip4: ' );
      open_td( 'colspan=2' );
        open_input( 'ip4_t', '', '', string_view( $ip4_t, 'ip4_t', 20 ) );
    open_tr();
      open_td();
        open_label( 'ip6', '', '', 'ip6: ' );
      open_td( '', '', false, 2 );
        open_input( 'ip6', '', '', string_view( $ip4, 'ip4', 30 ) );
    open_tr();
      open_td();
        open_label( 'oid_t', '', '', 'oid: ' );
      open_td( '', '', false, 2 );
        open_input( 'oid_t', '', '', string_view( $oid_t, 'oid_t', 30 ) );

    open_tr();
      open_td();
        open_label( 'sequential_number', '', '', '#: ' );
      open_td();
        open_input( 'sequential_number', '', '', int_view( $sequential_number, 'sequential_number', 2 ) );
      open_td( 'qquad' );
        open_label( 'active', '', '', 'active: ' );
        open_input( 'active', 'quad', '', checkbox_view( $active, 'active' ) );

    open_tr();
      open_td();
        open_label( 'processor', '', '', 'processor: ' );
      open_td();
        open_input( 'processor', '', '', string_view( $processor, 'processor', 20 ) );
      open_td( 'qquad' );
        open_label( 'os', '', '', 'os: ' );
        open_input( 'quad', '', '', string_view( $os, 'os', 20 ) );

    open_tr();
      open_td();
        open_label( 'location', '', '', 'location: ' );
      open_td();
        open_input( 'location', '', '', string_view( $location, 'location', 20 ) );
      open_td( 'qquad' );
        open_label( 'invlabel', '', '', 'invlabel: ' );
        open_input( 'quad', '', '', string_view( $invlabel, 'invlabel', 10 ) );

    open_tr( 'medskip' );
    open_td( 'right', '', false, 3 );
      if( ! $changes )
        template_button();
      submission_button();
  close_table();
close_fieldset();


if( $hosts_id ) {
  open_fieldset( 'small_form', 'disks', 'on' );
    diskslist_view( array( 'hosts_id' => $hosts_id ) );
  close_fieldset();

  open_fieldset( 'small_form', 'accounts', 'on' );
    accountslist_view( array( 'hosts_id' => $hosts_id ) );
  close_fieldset();

  open_fieldset( 'small_form', 'services', 'on' );
    serviceslist_view( array( 'hosts_id' => $hosts_id ) );
  close_fieldset();
}

?>
