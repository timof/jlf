<?php

do {
  $reinit = false;
  
  init_var( 'hosts_id', 'global,pattern=u,sources=http persistent,default=0,set_scopes=self' );
  init_var( 'flag_problems', 'pattern=u,sources=persistent,default=0,global,set_scopes=self' );

  $hosts_fields = array(
    'hostname' => aray( '/^[a-z0-9-]+$/,default=' )
  , 'domain' => 'array( '/^[a-z0-9.-]+$/,default=' )
  , 'sequential_number' => 'U,default=0'
  , 'ip4_t' => '/^[0-9.]*$/,default='
  , 'ip6' => '/^[0-9:]*$/,default='
  , 'oid_t' => '/^[0-9.]+$/,default='.$oid_prefix
  , 'processor' => 'H,default='
  , 'os' => 'H,default='
  , 'invlabel' => 'W,default=C'
  , 'active' => 'b'
  , 'location' => 'H,default='
  );


  if( $hosts_id ) {
    $host = sql_one_host( $hosts_id );
    $host['oid_t'] = oid_canonical2traditional( $host['oid'] );
    $host['ip4_t'] = oid_canonical2traditional( $host['ip4'] );
    $host['hostname'] = $host['fqhostname'];
    $host['domain'] = '';
    if( ( $n = strpos( $host['hostname'], '.' ) ) !== false ) {
      $host['domain'] = substr( $host['hostname'], $n + 1 );
      $host['hostname'] = substr( $host['hostname'], 0, $n );
    }
    $flag_modified = 1;
  } else { 
    $host = array();
    $flag_modified = 0;
  }



row2global( 'hosts', $host );

$changes = array();
$problems = array();

handle_action( array( 'update', 'save', 'reset', 'init', 'template' ) );
if( $action !== 'reset' ) {
  foreach( $fields as $fieldname => $type ) {
    init_global_var( $fieldname, $type, 'http,persistent,keep', '', 'self' );
    if( $hosts_id ) {
      if( $GLOBALS[ $fieldname ] !== $host[ $fieldname ] ) {
        $changes[ $fieldname ] = 'modified';
      }
    }
  }
}

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
        open_label( 'hostname', $f['domain']['class'], 'fqhostname:' );
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
