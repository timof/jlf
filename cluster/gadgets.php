<?php


function choices_hosts( $filters = array() ) {
  $choices = array();
  $choices[ 4711 ] = 'giebs.nich'; // for testing...
  foreach( sql_hosts( $filters ) as $host ) {
    $choices[ $host['hosts_id'] ] = $host['fqhostname'];
  }
  $choices[''] = $choices ? ' - select host - ' : '(no hosts)';
  return $choices;
}

function selector_host( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'hosts_id' );
  $opts = parameters_explode( $opts );
  // array-operator + : union of arrays: do not renumber numeric keys; lhs wins in case of index collision:
  $more_choices = parameters_explode( adefault( $opts, 'more_choices', array() ), 'default_key=0' );
  $choices = $more_choices + choices_hosts( adefault( $opts, 'filters', array() ) );
  return dropdown_select( $field, $choices );
}

function filter_host( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_host( $field, $opts );
}


function choices_disks( $filters = array() ) {
  $choices = array();
  foreach( sql_disks( $filters ) as $disk ) {
    $choices[ $disk['disks_id'] ] = "{$disk['cn']} ({$disk['interface_disk']} {$disk['sizeGB']}GB)";
  }
  $choices[''] = $choices ? ' - select disk - ' : '(no disks)';
  return $choices;
}

function selector_disk( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'hosts_id' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_disks( adefault( $opts, 'filters', array() ) );
  return dropdown_select( $field, $choices );
}

function filter_disk( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_disk( $field, $opts );
}


function choices_tapes( $filters = array() ) {
  $choices = array();
  foreach( sql_tapes( $filters ) as $tape ) {
    $choices[ $tape['tapes_id'] ] = $tape['cn'];
  }
  $choices[''] = $choices ? ' - select tape - ' : '(no tapes)';
  return $choices;
}

function selector_tape( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'tapes_id' );
  $opts = parameters_explode( $opts );
  $choices = choices_tapes( $filters );
  return dropdown_select( $field, $choices );
}

function filter_tape( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_tape( $field, $opts );
}


function choices_locations( $filters = array() ) {
  // FIXME: use $filters here!
  $choices  = sql_unique_values( 'hosts', 'location' );
  $choices += sql_unique_values( 'disks', 'location' );
  $choices += sql_unique_values( 'tapes', 'location' );
  $choices[''] = $choices ? ' - select location - ' : '(no locations)';
  return $choices;
}

function selector_location( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'locations_id' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_locations( adefault( $opts, 'filters', array() ) );
  return dropdown_select( $field, $choices );
}

function filter_location( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_location( $field, $opts );
}


function choices_type_disk() {
  $choices = array();
  foreach( $GLOBALS['disk_types'] as $t )
    $choices[ $t ] = $t;
  $choices[''] = $choices ? ' - select type - ' : '(no types)';
  return $choices;
}

function selector_type_disk( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'type_disk' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_type_disk( adefault( $opts, 'filters', array() ) );
  return dropdown_select( $field, $choices );
}

function filter_type_disk( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_type_disk( $field, $opts );
}

function choices_interface_disk() {
  $choices = array();
  foreach( $GLOBALS['disk_interfaces'] as $t )
    $choices[ $t ] = $t;
  $choices[''] = $choices ? ' - select interface - ' : '(no interfaces)';
  return $choices;
}

function selector_interface_disk( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'interface_disk' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_interface_disk( adefault( $opts, 'filters', array() ) );
  return dropdown_select( $field, $choices );
}

function filter_interface_disk( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_interface_disk( $field, $opts );
}
 

function choices_type_tape() {
  $choices = array();
  foreach( $GLOBALS['tape_types'] as $t )
    $choices[$t] = $t;
  $choices[''] = $choices ? ' - select type - ' : '(no types)';
  return $choices;
}

function selector_type_tape( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'type_tape' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_type_tape( adefault( $opts, 'filters', array() ) );
  return dropdown_select( $field, $choices );
}

function filter_type_tape( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_type_tape( $field, $opts );
}


function choices_accountdomains( $filters = array() ) {
  $choices = array();
  foreach( sql_accountdomains( $filters ) as $l )
    $choices[ $l['accountdomains_id'] ] = $l['accountdomain'];
  $choices[''] = $choices ? ' - select accountdomain - ' : '(no accountdomains)';
  return $choices;
}

function selector_accountdomain( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'accountdomains_id' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_accountdomains( adefault( $opts, 'filters', array() ) );
  return dropdown_select( $field, $choices );
}

function filter_accountdomain( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_accountdomain( $field, $opts );
}

?>
