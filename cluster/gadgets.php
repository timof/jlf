<?php


function options_hosts( $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  $options[ 4711 ] = 'giebs.nich'; // for testing...
  foreach( sql_hosts( $filters ) as $host ) {
    $options[ $host['hosts_id'] ] = $host['fqhostname'];
  }
  $options[''] = $options ? ' - select host - ' : '(no hosts)';
  return $options;
}

function selector_host( $field = NULL, $selected = NULL, $filters = array(), $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'hosts_id' );
  $options = options_hosts( $filters, $option_0 );
  return dropdown_select( $field, $options, $selected );
}

function filter_host( $prefix = '', $filters = array(), $option_0 = '(all)' ) {
  $r = init_var( $prefix.'hosts_id', 'global,pattern=u,from=keep http persistent,default=0,set_scopes=self' );
  return selector_host( $r, NULL, $filters, $option_0 );
}


function options_disks( $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( sql_disks( $filters ) as $disk ) {
    $options[ $disk['disks_id'] ] = $disk['cn'];
  }
  $options[''] = $options ? ' - select disk - ' : '(no disks)';
  return $options;
}

function selector_disk( $field = NULL, $selected = NULL, $filters = array(), $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'hosts_id' );
  $options = options_disks( $filters, $option_0 );
  return dropdown_select( $field, $options, $selected );
}

function filter_disk( $prefix = '', $filters = array(), $option_0 = '(all)' ) {
  $r = init_var( $prefix.'disks_id', 'global,pattern=u,failsafe=1,from=keep http persistent,default=0,set_scopes=self' );
  return selector_disk( $r, NULL, $filters, $option_0 );
}


function options_tapes( $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( sql_tapes( $filters ) as $tape ) {
    $options[ $tape['tapes_id'] ] = $tape['cn'];
  }
  $options[''] = $options ? ' - select tape - ' : '(no tapes)';
  return $options;
}

function selector_tape( $field = NULL, $selected = NULL, $filters = array(), $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'tapes_id' );
  $options = options_tapes( $filters, $option_0 );
  return dropdown_select( $field, $options, $selected );
}

function filter_tape( $prefix = '', $filters = array(), $option_0 = '(all)' ) {
  $r = init_var( $prefix.'tapes_id', 'global,pattern=u,failsafe=1,from=keep http persistent,default=0,set_scopes=self' );
  return selector_tape( $r, NULL, $filters, $option_0 );
}


function options_locations( $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  $options += sql_unique_values( 'hosts', 'location' );
  $options += sql_unique_values( 'disks', 'location' );
  $options += sql_unique_values( 'tapes', 'location' );
  $options[''] = $options ? ' - select location - ' : '(no locations)';
  return $options;
}

function selector_location( $field = NULL, $selected = NULL, $filters = array(), $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'locations_id' );
  $options = options_locations( $filters, $option_0 );
  return dropdown_select( $field, $options, $selected );
}

function filter_location( $prefix = '', $filters = array(), $option_0 = '(all)' ) {
  $r = init_var( $prefix.'locations_id', 'global,pattern=l,failsafe=1,from=keep http persistent,default=,set_scopes=self' );
  return selector_location( $r, NULL, $filters, $option_0 );
}


function options_type_disk( $option_0 = false ) {
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( $GLOBALS['disk_types'] as $t )
    $options[ $t ] = $t;
  $options[''] = $options ? ' - select type - ' : '(no types)';
  return $options;
}

function selector_type_disk( $field = NULL, $selected = NULL, $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'type_disk' );
  $options = options_type_disk( $option_0 );
  return dropdown_select( $field, $options, $selected );
}

function filter_type_disk( $prefix = '', $option_0 = '(all)' ) {
  $r = init_var( $prefix.'type_disk', 'global,pattern=Ttype_disk,from=keep http persistent,default=,set_scopes=self' );
  return selector_type_disk( $r, NULL, $option_0 );
}

function options_interface_disk( $option_0 = false ) {
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( $GLOBALS['disk_interfaces'] as $t )
    $options[ $t ] = $t;
  $options[''] = $options ? ' - select interface - ' : '(no interfaces)';
  return $options;
}

function selector_interface_disk( $field = NULL, $selected = NULL, $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'interface_disk' );
  $options = options_interface_disk( $option_0 );
  return dropdown_select( $field, $options, $selected );
}

function filter_interface_disk( $prefix = '', $option_0 = '(all)' ) {
  $r = init_var( $prefix.'interface_disk', 'global,pattern=Tinterface_disk,from=keep http persistent,default=,set_scopes=self' );
  return selector_interface_disk( $r, NULL, $option_0 );
}


function options_type_tape( $option_0 = false ) {
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( $GLOBALS['tape_types'] as $t )
    $options[$t] = $t;
  $options[''] = $options ? ' - select type - ' : '(no types)';
  return $options;
}

function selector_type_tape( $field = NULL, $selected = NULL, $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'type_tape' );
  $options = options_type_tape( $option_0 );
  return dropdown_select( $field, $options, $selected );
}

function filter_type_tape( $prefix = '', $option_0 = '(all)' ) {
  $r = init_var( $prefix.'type_tape', 'global,pattern=Ttype_tape,from=keep http persistent,default=,set_scopes=self' );
  return selector_type_tape( $r, NULL, $option_0 );
}


function options_accountdomains( $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( sql_accountdomains( $filters ) as $l )
    $options[ $l['accountdomains_id'] ] = $l['accountdomain'];
  $options[''] = $options ? ' - select accountdomain - ' : '(no accountdomains)';
  return $options;
}

function selector_accountdomain( $field = NULL, $selected = NULL, $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'accountdomains_id' );
  $options = options_accountdomains( array(), $option_0 );
  return dropdown_select( $field, $options, $selected );
}

function filter_accountdomain( $prefix = '', $option_0 = '(all)' ) {
  $r = init_var( $prefix.'accountdomains_id', 'global,pattern=u,from=keep http persistent,default=0,set_scopes=self' );
  return selector_accountdomain( $r, NULL, $option_0 );
}

?>
