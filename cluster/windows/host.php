<?php

init_global_var( 'hosts_id', 'u', 'http,persistent', 0, 'self' );

$host = ( $hosts_id ? sql_one_host( $hosts_id ) : false );
row2global( 'hosts', $host );

$oid || ( $oid = $oid_prefix );
$oid = oid_canonical2traditional( $oid );

$problems = array();

$fields = array(
   'hostname' => '/^[a-z0-9-]+$/'
,  'domain' => '/^[a-z0-9.-]+$/'
,  'sequential_number' => 'U'
,  'ip4' => '/^[0-9.]*$/'
,  'ip6' => '/^[0-9:]*$/'
,  'oid' => '/^[0-9.]+$/'
,  'processor' => 'H'
,  'os' => 'H'
,  'invlabel' => 'W'
,  'location' => 'H'
);
foreach( $fields as $fieldname => $type )
  init_global_var( $fieldname, $type, 'http,persistent,keep', '', 'self' );

handle_action( array( 'update', 'save', 'init', 'template' ) );
switch( $action ) {
  case 'template':
    $hosts_id = 0;
    break;

  case 'init':
    $hosts_id = 0;
    $oid = $oid_prefix;
    $ip4 = $ip4_prefix;
    $domain = $default_domain;
    break;

  case 'save':
    $values = array();
    foreach( $fields as $fieldname => $type ) {
      if( checkvalue( $fieldname, $type ) !== NULL )
        $values[ $fieldname ] = $$fieldname;
      else
        $problems[] = $fieldname;
    }
    if( in_array( 'domain', $problems ) )
      $problems[] = 'hostname';

    if( ! $problems ) {
      $values['ip4'] = ip4_traditional2canonical( $values['ip4'] );
      $values['oid'] = oid_traditional2canonical( $values['oid'] );
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

open_form( '', 'action=save' );
  open_fieldset( 'small_form', '', ( $hosts_id ? 'edit host' : 'new host' ) );
    open_table('small_form hfill');
      form_row_text( 'fqhostname: ', 'hostname', 12, $hostname );
        open_span( '', '', '.'. string_view( $domain, 24, 'domain' ) );
        open_span( 'quad '.problem_class('sequential_number'), '', '#: '. int_view( $sequential_number, 'sequential_number', 2 ) );
      form_row_text( 'ip4: ', 'ip4', 20, $ip4 );
      form_row_text( 'ip6: ', 'ip6', 30, $ip6 );
      form_row_text( 'oid: ', 'oid', 30, $oid );
      form_row_text( 'processor: ', 'processor', 20, $processor );
        open_span( 'qquad '.problem_class('os'), '', "os: ". string_view( $os, 20, 'os' ) );
      form_row_text( 'invlabel: ', 'invlabel', 10, $invlabel );
        open_span( 'qquad '.problem_class('location'), '', "location: ". string_view( $location, 20, 'location' ) );
      open_tr();
      open_td( 'right', "colspan='2'" );
        submission_button();
    close_table();
  close_fieldset();
close_form();

if( $hosts_id ) {
  open_fieldset( 'small_form', '', 'disks', 'on' );
    diskslist_view( array( 'hosts_id' => $hosts_id ), false );
  close_fieldset();

  open_fieldset( 'small_form', '', 'accounts', 'on' );
    accountslist_view( array( 'hosts_id' => $hosts_id ), false );
  close_fieldset();

  open_fieldset( 'small_form', '', 'services', 'on' );
    serviceslist_view( array( 'hosts_id' => $hosts_id ), false );
  close_fieldset();
}

?>
