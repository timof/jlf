<?php


function choices_hosts( $filters = array() ) {
  $choices = array();
  foreach( sql_hosts( $filters ) as $host ) {
    $choices[ $host['hosts_id'] ] = "{$host['fqhostname']} / {$host['sequential_number']}";
  }
  return $choices;
}

function selector_host( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'hosts_id' );
  $opts = parameters_explode( $opts );
  // array-operator + : union of arrays: do not renumber numeric keys; lhs wins in case of index collision:
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_hosts( adefault( $opts, 'filters', '' ) )
  , 'empty_display' => '(no hosts)'
  , 'default_display' => ' - select host - '
  );
  return dropdown_element( $field );
}

function filter_host( $field, $opts = array() ) {
  return selector_host( $field, add_filter_default( $opts ) );
}


function choices_disks( $filters = array() ) {
  $choices = array();
  foreach( sql_disks( $filters ) as $disk ) {
    $choices[ $disk['disks_id'] ] = "{$disk['cn']} ({$disk['interface_disk']} {$disk['sizeGB']}GB)";
  }
  return $choices;
}

function selector_disk( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'hosts_id' );
  $opts = parameters_explode( $opts );
  $field += array( 
    'choices' => adefault( $opts, 'choices', array() ) + choices_disks( adefault( $opts, 'filters', '' ) )
  , 'empty_display' => '(no disks)'
  , 'default_display' => ' - select disk - '
  );
  return dropdown_element( $field );
}

function filter_disk( $field, $opts = array() ) {
  return selector_disk( $field, add_filter_default( $opts ) );
}


function choices_tapes( $filters = array() ) {
  $choices = array();
  foreach( sql_tapes( $filters ) as $tape ) {
    $choices[ $tape['tapes_id'] ] = $tape['cn'];
  }
  return $choices;
}

function selector_tape( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'tapes_id' );
  $opts = parameters_explode( $opts );
  $field += array( 
    'choices' => adefault( $opts, 'choices', array() ) + choices_tapes( adefault( $opts, 'filters', '' ) )
  , 'empty_display' => '(no tapes)'
  , 'default_display' => ' - select tape - '
  );
  return dropdown_element( $field );
}
 
function filter_tape( $field, $opts = array() ) {
  return selector_tape( $field, add_filter_default( $opts ) );
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
  $field += array(
    'uid_choices' => adefault( $opts, 'uid_choices', array() ) + choices_locations( adefault( $opts, 'filters', array() ) )
  , 'empty_display' => '(no location)'
  , 'default_display' => ' - select location - '
  );
  return dropdown_element( $field );
}

function filter_location( $field, $opts = array() ) {
  return selector_location( $field, add_filter_default( $opts ) );
}


function choices_backupprofiles( $filters = array() ) {
  $choices = sql_backupjobs( $filters, 'distinct=profile' );
  return $choices;
}

function selector_backupprofile( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'profile' );
  $opts = parameters_explode( $opts );
  $field += array(
    'uid_choices' => adefault( $opts, 'uid_choices', array() ) + choices_backupprofiles( adefault( $opts, 'filters', array() ) )
  , 'empty_display' => '(no profile)'
  , 'default_display' => ' - select backupprofile - '
  );
  return dropdown_element( $field );
}

function filter_backupprofile( $field, $opts = array() ) {
  return selector_backupprofile( $field, add_filter_default( $opts ) );
}


function choices_type_disk() {
  $choices = array();
  foreach( $GLOBALS['disk_types'] as $t )
    $choices[ $t ] = $t;
  return $choices;
}

function selector_type_disk( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'type_disk' );
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_type_disk( adefault( $opts, 'filters', array() ) )
  , 'empty_display' => '(no types)'
  , ' - select type - '
  );
  return dropdown_element( $field );
}

function filter_type_disk( $field, $opts = array() ) {
  return selector_type_disk( $field, add_filter_default( $opts ) );
}

function choices_interface_disk() {
  $choices = array();
  foreach( $GLOBALS['disk_interfaces'] as $t )
    $choices[ $t ] = $t;
  return $choices;
}

function selector_interface_disk( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'interface_disk' );
  $opts = parameters_explode( $opts );
  $field['choices'] = adefault( $opts, 'choices', array() ) + choices_interface_disk( adefault( $opts, 'filters', array() ) );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_interface_disk( adefault( $opts, 'filters', array() ) )
  , 'empty_display' => '(no interfaces)'
  , ' - select interface - '
  );
  return dropdown_element( $field );
}

function filter_interface_disk( $field, $opts = array() ) {
  return selector_interface_disk( $field, add_filter_default( $opts ) );
}
 

function choices_type_tape() {
  $choices = array();
  foreach( $GLOBALS['tape_types'] as $t )
    $choices[$t] = $t;
  return $choices;
}

function selector_type_tape( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'type_tape' );
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_type_tape( adefault( $opts, 'filters', array() ) )
  , 'empty_display' => '(no types)'
  , 'default_display' => ' - select type - '
  );
  return dropdown_element( $field );
}

function filter_type_tape( $field, $opts = array() ) {
  return selector_type_tape( $field, add_filter_default( $opts ) );
}


function choices_accountdomains( $filters = array() ) {
  $choices = array();
  foreach( sql_accountdomains( $filters ) as $l )
    $choices[ $l['accountdomains_id'] ] = $l['accountdomain'];
  return $choices;
}

function selector_accountdomain( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'accountdomains_id' );
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_accountdomains( adefault( $opts, 'filters', array() ) )
  , 'empty_display' => '(no accountdomains)'
  , 'default_display' => ' - select accountdomain - '
  );
  return dropdown_element( $field );
}

function filter_accountdomain( $field, $opts = array() ) {
  return selector_accountdomain( $field, add_filter_default( $opts ) );
}



function filters_host_prepare( $fields, $opts = array() ) {

  $opts = parameters_explode( $opts );
  $auto_select_unique = adefault( $opts, 'auto_select_unique', false );
  $flag_modified = adefault( $opts, 'flag_modified', false );
  $flag_problems = adefault( $opts, 'flag_problems', false );

  // possible filter fields, semi-ordered by increasing specificity:
  //
  $host_fields = array(
    'location'
  , 'processor' , 'os'
  , 'accountdomain'
  , 'host_current'
  , 'REGEX'
  , 'fqhostname'
  , 'sequential_number'
  , 'ip4', 'ip6', 'oid', 'invlabel'
  , 'hosts_id'
  );
  if( $fields === true )
    $fields = $host_fields;
  $fields = parameters_explode( $fields );

  if( ! isset( $opts['tables'] ) ) {
    $opts['tables'] = 'hosts';
  }
  // initialize fields from specified sources (http, persistent, init, ...)
  //
  $state = init_fields( $fields, $opts );

  // define $bstate: like $state, but using basenames for all fields:
  //
  $bstate = array();
  foreach( $state as $fieldname => $field ) {
    if( ! isset( $fields[ $fieldname ] ) )
      continue; // skip pseudo-fields with _-prefix
    $basename = adefault( $field, 'basename', $fieldname );
    $sql_name = adefault( $field, 'sql_name', $basename );
    // debug( $field, $fieldname );
    need( in_array( $basename, array_keys( $host_fields ) ) );
    $bstate[ $basename ] = & $state[ $fieldname ];
  }

  // define $work: like $bstate, but contains stubs for non-existing fields:
  //
  $work = array();
  foreach( $host_fields as $fieldname => $field ) {
    if( isset( $bstate[ $fieldname ] ) ) {
      $work[ $fieldname ] = & $bstate[ $fieldname ];
    } else {
      $work[ $fieldname ] = array( 'value' => NULL );
    }
  }

  $filters = adefault( $opts, 'filters', array() );
  if( $filters ) {
    $filters = array( '&&', $filters );
  }
  // loop 1:
  // - insert info from http:
  // - if field is reset, reset more specific fields too
  // - remove inconsistencies: reset more specific fields as needed
  // - auto_select_unique: if only one possible choice for a field, select it
  foreach( $host_fields as $fieldname => $field ) {

    if( ! isset( $bstate[ $fieldname ] ) )
      continue;

    $r = & $bstate[ $fieldname ];
    $sql_name = $r['sql_name'];
    $filter_name = $sql_name;
    if( ( $relation = adefault( $r, 'relation' ) ) ) {
      $filter_name .= " $relation";
    }

    if( $r['source'] === 'http' ) {
      // submitted from http - force new value:
      if( $r['value'] ) {
        $filters[ $filter_name ] = & $r['value'];
      } else {
      // filter was reset - reset more specific fields too:
        switch( $fieldname ) {
          case 'hosts_id':
            //nop
            break;
          default:
            $work['hosts_id']['value'] = 0;
        }
      }

    } else { /* not passed via http */

      if( $r['value'] ) {
        $filters[ $filter_name ] = & $r['value'];
        // value not from http - check and drop setting if inconsistent:
        $check = sql_hosts( $filters );
        if( ! $check ) {
          $r['value'] = $r['default'];
          unset( $filters[ $filter_name ] );
        }
      }

      if( adefault( $r, 'auto_select_unique', $auto_select_unique ) ) {
        if( ! $r['value'] ) {
          $h = sql_hosts( $filters );
          if( count( $h ) == 1 ) {
            $h = $h[ 0 ];
            switch( $fieldname ) {
              // makes sense only for certain fields; in particular, when our goal
              // is to pick one host:
              //
              case 'hosts_id':
                $r['value'] = $p['hosts_id'];
                $filters['hosts_id'] = & $r['value'];
                break;
            }
          }
        }
        // the above may not always work if we don't have all filters yet, so...
        $r['auto_select_unique'] = 1; // ... the dropdown selector may do it
        //
      }
    }
  }

  // loop 2: fill less specific fields from more specific ones:
  //
  foreach( $host_fields as $fieldname => $field ) {
    $r = & $work[ $fieldname ];
    if( ! $r['value'] )
      continue;
    // debug( $r, "propagate up: propagating: $fieldname" );
    switch( $fieldname ) {
      case 'hosts_id':
        $h = sql_hosts( $filters );
        if( count( $h ) == 1 ) {
          // consistent - set less specific fields:
          $h = $h[ 0 ];
          // set less specific fields
        } else if( $count( $h ) < 1 ) {
          // inconsistent (possible if hosts_id was forced by http):
          $h = sql_host( $work['hosts_id']['value'] );
          if( $count( $h ) == 1 ) {
            //// ? $work['groups_id']['value'] = $p['primary_groups_id'];
          }
        }
        // fall-through (in case there ever happen to be more fields)
    }
  }

  // debug( $work, 'work before loop 3' );

  // loop 3: check for modifications, errors, and set filters:
  //
  foreach( $host_fields as $fieldname => $field ) {
    $r = & $work[ $fieldname ];

    $r['class'] = '';
    if( ( (string) $r['value'] ) !== ( (string) adefault( $r, 'initval', $r['value'] ) ) ) {
      $r['modified'] = 'modified';
      $state['_changes'][ $fieldname ] = $r['value'];
      if( $flag_modified ) {
        $r['class'] = 'modified';
      }
    } else {
      $r['modified'] = '';
      unset( $state['_changes'][ $fieldname ] );
    }

    // $r['value'] should normally alread be checked - but we check again, in case value was forced at some point in the code above:
    //
    if( checkvalue( $r['value'], $r ) === NULL )  {
      $r['problem'] = 'type mismatch';
      $state['_problems'][ $fieldname ] = $r['value'];
      $r['value'] = NULL;
      if( $flag_problems )
        $r['class'] = 'problem';
      // debug( $r, 'problem detected in loop 3:' );
    } else {
      $r['problem'] = '';
      unset( $state['_problems'][ $fieldname ] );
    }

    if( ( $r['value'] !== NULL ) && ( $r['value'] !== $r['default'] ) ) {
      $filter_name = $r['sql_name'];
      if( ( $relation = adefault( $r, 'relation' ) ) ) {
        $filter_name .= " $relation";
      }
      $state['_filters'][ $filter_name ] = & $r['value'];
    } else {
      unset( $state['_filters'][ $r['sql_name'] ] );
    }
  }
  // debug( $state, 'state final' );
  return $state;
}

?>
