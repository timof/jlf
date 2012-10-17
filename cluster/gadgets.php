<?php


function choices_hosts( $filters = array() ) {
  $choices = array();
  foreach( sql_hosts( $filters ) as $host ) {
    $choices[ $host['hosts_id'] ] = "{$host['fqhostname']} / {$host['sequential_number']}";
  }
  $choices[''] = $choices ? ' - select host - ' : '(no hosts)';
  return $choices;
}

function selector_host( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'hosts_id' );
  $opts = parameters_explode( $opts );
  // array-operator + : union of arrays: do not renumber numeric keys; lhs wins in case of index collision:
  $more_choices = adefault( $opts, 'more_choices', array() );
  $field['choices'] = $more_choices + choices_hosts( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
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
  $field['choices'] = adefault( $opts, 'more_choices', array() ) + choices_disks( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
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
  $field['choices'] = choices_tapes( $filters );
  echo dropdown_element( $field );
}
 
function filter_tape( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_tape( $field, $opts );
}


// choices_locations(): for the time being, $filters may only specify
// pseudo-key 'tables' to match tables from which locations are to be collected
//
function choices_locations( $tables = 'hosts, disks, tapes' ) {
  $tables = parameters_explode( $tables, array( 'default_value' => array() ) );

  $choices = array();
  foreach( $tables as $tname => $filter ) {
    $choices += sql_query( $tname, array( 'filters' => $filter, 'distinct' => 'location' ) );
  }
  $a = array_unique( $choices );
  asort( /* & */ $a );
  return $a;
}

function selector_location( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'location' );
  $opts = parameters_explode( $opts );
  $field['uid_choices'] = adefault( $opts, 'more_choices', array() ) + choices_locations( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
}

function filter_location( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_location( $field, $opts );
}


function choices_backupprofiles() {
  $query = "SELECT DISTINCT profile FROM ( backupjobs ) ORDER BY profile";
  $result = mysql2array( sql_do( $query ) );
  $choices = array();
  foreach( $result as $row ) {
    $p = $row['profile'];
    $choices[ value2uid( $p ) ] = $p;
  }
  return $choices;
}

function selector_backupprofile( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'profile' );
  $opts = parameters_explode( $opts );
  $field['uid_choices'] = adefault( $opts, 'more_choices', array() ) + choices_backupprofiles( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
}

function filter_backupprofile( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_backupprofile( $field, $opts );
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
  $field['choices'] = adefault( $opts, 'more_choices', array() ) + choices_type_disk( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
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
  $field['choices'] = adefault( $opts, 'more_choices', array() ) + choices_interface_disk( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
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
  $field['choices'] = adefault( $opts, 'more_choices', array() ) + choices_type_tape( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
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
  $field['choices'] = adefault( $opts, 'more_choices', array() ) + choices_accountdomains( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
}

function filter_accountdomain( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  return selector_accountdomain( $field, $opts );
}

?>
