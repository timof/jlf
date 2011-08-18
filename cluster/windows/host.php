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
  $hostname = '';
  $domain = $default_domain;
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
  init_global_var( $fieldname, $type, 'http,persistent,keep', '', 'self' );
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
  open_fieldset( 'small_form old', 'edit host' );
} else {
  open_fieldset( 'small_form new', 'new host' );
}
  open_table( 'hfill,colgroup=20% 30% 50%' );
    open_tr();
      open_td();
        open_label( 'hostname', field_class('domain'), 'fqhostname:' );
      open_td( 'oneline,colspan=2', string_element( 'hostname', 'size=15' ) . ' . '. string_element( 'domain', 'size=25' ) );

    open_tr();
      open_td( 'label=ip4_t', 'ip4:' );
      open_td( 'colspan=2', string_element( 'ip4_t', 'size=20' ) );

    open_tr();
      open_td( 'label=ip6', 'ip6:' );
      open_td( 'colspan=2', string_element( 'ip6', 'size=30' ) );

    open_tr();
      open_td( 'label=oid_t',  'oid: ' );
      open_td( 'colspan=2', string_element( 'oid_t', 'size=30' ) );

    open_tr();
      open_td( 'label=sequential_number', '#: ' );
      open_td( '', int_element( 'sequential_number', 'size=2' ) );
      open_td( 'qquad' );
        open_label( 'active', '', 'active: ' );
        echo checkbox_element( 'active' );

    open_tr();
      open_td( 'label=processor', 'processor: ' );
      open_td( '', string_element( 'processor', 'size=20' ) );
      open_td( 'qquad' );
        open_label( 'os', '', 'os: ' );
        echo string_element( 'os', 'size=20' );

    open_tr();
      open_td( 'label=location', 'location: ' );
      open_td( '', string_element( 'location', 'size=20' ) );
      open_td( 'qquad' );
        open_label( 'invlabel', '', 'invlabel: ' );
        echo string_element( 'invlabel', 'size=10' );

    open_tr( 'medskip' );
    open_td( 'right,colspan=3' );
      if( $hosts_id && ! $changes )
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
