<?php


function options_hosts( $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( sql_hosts( $filters ) as $host ) {
    $options[ $host['hosts_id'] ] = $host['fqhostname'];
  }
  $options[''] = $options ? ' - select host - ' : '(no hosts)';
  return $options;
}

function selector_host( $fieldname = 'hosts_id', $selected = NULL, $filters = array(), $option_0 = false ) {
  $options = options_hosts( $filters, $option_0 );
  return dropdown_select( $fieldname, $options, $selected );
}

function filter_host( $prefix = '', $filters = array(), $option_0 = '(all)' ) {
  $id = init_global_var( $prefix.'hosts_id', 'u', 'keep,http,persistent', 0, 'self' );
  return selector_host( $prefix.'hosts_id', $id, $filters, $option_0 );
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

function selector_disk( $fieldname = 'disks_id', $selected = NULL, $filters = array(), $option_0 = false ) {
  $options = options_disks( $filters, $option_0 );
  return dropdown_select( $fieldname, $options, $selected );
}

function filter_disk( $prefix = '', $filters = array(), $option_0 = '(all)' ) {
  $id = init_global_var( $prefix.'disks_id', 'u', 'keep,http,persistent', 0, 'self' );
  return selector_host( $prefix.'disks_id', $id, $filters, $option_0 );
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

function selector_tape( $fieldname = 'tapes_id', $selected = NULL, $filters = array(), $option_0 = false ) {
  $options = options_tapes( $filters, $option_0 );
  return dropdown_select( $fieldname, $options, $selected );
}

function filter_tape( $prefix = '', $filters = array(), $option_0 = '(all)' ) {
  $id = init_global_var( $prefix.'tapes_id', 'u', 'keep,http,persistent', 0, 'self' );
  return selector_host( $prefix.'tapes_id', $id, $filters, $option_0 );
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

function selector_location( $fieldname, $selected = NULL, $filters = array(), $option_0 = false ) {
  $options = options_locations( $filters, $option_0 );
  prettydump( $options, 'options' );
  return dropdown_select( $fieldname, $options, $selected );
}

function filter_location( $prefix = '', $filters = array(), $option_0 = '(all)' ) {
  global $form_id;
  $id = init_global_var( $prefix.'locations_id', 'w', 'keep,http,persistent', 0, 'self' );
  return selector_location( $prefix.'locations_id' , $id, $filters, $option_0 );
}


function options_type_disk( $option_0 = false ) {
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( $GLOBALS['disk_types'] as $t )
    $options[$t] = $t;
  $options[''] = $options ? ' - select type - ' : '(no types)';
  return $options;
}

function selector_type_disk( $fieldname = 'type_disk', $selected = NULL, $option_0 = false ) {
  $options = options_type_disk( $option_0 );
  return dropdown_select( $fieldname, $options, $selected );
}

function filter_type_disk( $prefix = '', $option_0 = '(all)' ) {
  $type = init_global_var( $prefix.'type_disk', '', 'keep,http,persistent', 0, 'self' );
  return selector_type_disk( $prefix.'type_disk', $type, $option_0 );
}

function options_interface_disk( $option_0 = false ) {
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( $GLOBALS['disk_interfaces'] as $t )
    $options[$t] = $t;
  $options[''] = $options ? ' - select interface - ' : '(no interfaces)';
  return $options;
}

function selector_interface_disk( $fieldname = 'interface_disk', $selected = NULL, $option_0 = false ) {
  $options = options_interface_disk( $option_0 );
  return dropdown_select( $fieldname, $options, $selected );
}

function filter_interface_disk( $prefix = '', $option_0 = '(all)' ) {
  $interface = init_global_var( $prefix.'interface_disk', '', 'keep,http,persistent', 0, 'self' );
  return selector_interface_disk( $prefix.'interface_disk', $interface, $option_0 );
}



function options_type_tape( $option_0 = false ) {
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( $GLOBALS['tape_types'] as $t )
    $options[$t] = $t;
  $options[''] = $options ? ' - select type - ' : '(no types)';
  return $options;
}

function selector_type_tape( $fieldname = 'type_tape', $selected = NULL, $option_0 = false ) {
  $options = options_type_tape( $option_0 );
  return dropdown_select( $fieldname, $options, $selected );
}

function filter_type_tape( $prefix = '', $option_0 = '(all)' ) {
  $type = init_global_var( $prefix.'type_tape', '', 'keep,http,persistent', 0, 'self' );
  return selector_type_disk( $prefix.'type_tape', $type, $option_0 );
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

function selector_accountdomain( $fieldname, $selected = NULL, $option_0 = false ) {
  $options = options_accountdomains( array(), $option_0 );
  return dropdown_select( $fieldname, $options, $selected );
}

function filter_accountdomain( $prefix = '', $option_0 = '(all)' ) {
  $accountdomains_id = init_global_var( $prefix.'accountdomains_id', '', 'keep,http,persistent', 0, 'self' );
  return selector_accountdomain( $prefix.'accountdomains_id', $accountdomains_id, $option_0 );
}

?>
