<?php

require_once('code/gadgets.php');

// functions for drop-down selectors; we usually provide a triple of functions:
// - choices_X( $filters = array() )
//     returns an array of <id> => <option> pairs matching $filters
// - selector_X( $field, $opts )
//     create drop-down selection gadget
//     $opts may contain
//      'filters': filter (array or string) to narrow selection
//      'choices': array of 'value' => 'text' pairs to also offer for selection
// - filter_X( $field, $opts = array() )
//     create drop-down selection gadget for filtering; $opts may contain
//       'filters': to narrow selection
//       'choices': array of extra choices; default: '0' => ' (all) '; pass an empty array to offer no 'choice 0'

function choices_people( $filters = array() ) {
  $choices = array();
  foreach( sql_people( $filters ) as $p ) {
    $choices[ $p['people_id'] ] = $p['cn'];
  }
  return $choices;
}

function selector_people( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'people_id' );
  }
  $opts = parameters_explode( $opts );
  $choices = (
    ( adefault( $opts, 'office' ) ? array ( '0' => we(' - vacant - ',' - vakant - ') ) : array() )
  + adefault( $opts, 'choices', array() )
  + choices_people( adefault( $opts, 'filters', array() ) )
  );
  $field += array(
    'choices' => $choices
  , 'default_display' => we(' - select person - ', ' - Person w'.H_AMP.'auml;hlen - ')
  , 'empty_display' => we('(no people available)', '(keine Personen vorhanden)')
  );
  return select_element( $field );
}

function filter_person( $field, $opts = array() ) {
  return selector_people( $field, add_filter_default( $opts, $field ) );
}


function choices_groups( $filters = array() ) {
  $choices = array();
  foreach( sql_groups( $filters, 'acronym' ) as $g ) {
    $choices[ $g['groups_id'] ] = $g['acronym'];
  }
  return $choices;
}

function selector_groups( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'groups_id' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_groups( adefault( $opts, 'filters', array() ) )
  , 'default_display' => we(' - select group - ', ' - Gruppe w'.H_AMP.'auml;hlen - ')
  , 'empty_display' => we('(no groups available)', '(keine Gruppen vorhanden)')
  );
  return select_element( $field );
}

function filter_group( $field, $opts = array() ) {
  return selector_groups( $field, add_filter_default( $opts, $field ) );
}

// 
// function selector_degree( $field = NULL, $opts = array() ) {
//   if( ! $field )
//     $field = array( 'name' => 'degree_id' );
//   $opts = parameters_explode( $opts );
//   $field += array(
//     'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['degree_text']
//   , 'default_display' => we(' - select type / degree - ',' - Art / Abschluss w'.H_AMP.'auml;hlen - ')
//   );
//   return dropdown_element( $field );
// }
// 
// function filter_degree( $field, $opts = array() ) {
//   return selector_degree( $field, add_filter_default( $opts ) );
// }
// 

function selector_programme( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'programme_flags' );
  }
  $opts = parameters_explode( $opts );
  $field += array( 
    'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['programme_text']
  , 'default_display' => we(' - select program - ',' - Studiengang w'.H_AMP.'auml;hlen - ')
  );
  return select_element( $field );
}

function filter_programme( $field, $opts = array() ) {
  return selector_programme( $field, add_filter_default( $opts, $field ) );
}

function selector_documenttype( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'documenttype' );
  }
  $opts = parameters_explode( $opts );
  $field += array( 
    'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['choices_documenttype']
  , 'default_display' => we(' - select type - ',' - Typ wählen - ')
  );
  return select_element( $field );
}

function filter_documenttype( $field, $opts = array() ) {
  return selector_documenttype( $field, add_filter_default( $opts, $field ) );
}

function selector_person_status( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'status' );
  }
  $opts = parameters_explode( $opts );
  $field += array( 
    'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['choices_person_status']
  , 'default_display' => we(' - select status - ',' - Status w'.H_AMP.'auml;hlen - ')
  );
  return select_element( $field );
}

function filter_person_status( $field, $opts = array() ) {
  return selector_person_status( $field, add_filter_default( $opts, $field ) );
}


function selector_group_status( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'status' );
  }
  $opts = parameters_explode( $opts );
  $field += array( 
    'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['choices_group_status']
  , 'default_display' => we(' - select status - ',' - Status w'.H_AMP.'auml;hlen - ')
  );
  return select_element( $field );
}

function filter_group_status( $field, $opts = array() ) {
  return selector_group_status( $field, add_filter_default( $opts, $field ) );
}

function selector_semester( $field = NULL, $opts = array() ) {

  if( ! $field ) {
    $field = array( 'name' => 'semester' );
  }

  $opts = parameters_explode( $opts, array( 'keep' => 'min,max,choice_0' ) );

  $s = adefault( $field, 'value', 0 );

  $field['min'] = adefault( $field, 'min', 1 );
  $field['max'] = adefault( $field, 'max', 12 );
  if( $s ) {
    $s = max( min( $s, $field['max'] ), $field['min'] );
  }

  $choice_0 = adefault( $opts, 'choice_0', '' );
  // debug( $choice_0, 'choice_0' );
  if( $s || ! $choice_0 ) {
    $t = selector_int( $field );
    if( $choice_0 ) {
      $t .= html_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", $field['name'] => 0 ) ) );
    }
  } else {
    $t = html_span( 'quads', $choice_0 ) . html_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => 1 ) ) );
  }
  return $t;
}

function filter_semester( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'min=1,max=12,choice_0= '.we(' (all) ',' (alle) ' ) ) );
  return selector_semester( $field, $opts );
}


function selector_term( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'semester' );
  }

  $opts = parameters_explode( $opts );

  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + array( 'S' => we('Summer','Sommer'), 'W' => 'Winter' )
  , 'default_display' => we(' - select term - ',' - Semester w'.H_AMP.'auml;hlen - ')
  );
  return select_element( $field );
}
function filter_term( $field, $opts = array() ) {
  return selector_term( $field, add_filter_default( $opts, $field ) );
}


function selector_year( $field = NULL, $opts = array() ) {
  global $current_year;
  // kludge alert:
  $year_min = 2012;
  $year_max = 2020;

  $bpriority = 1 + adefault( $field, 'priority', 1 );
  if( ! $field ) {
    $field = array( 'name' => 'year' );
  }

  $opts = parameters_explode( $opts, array( 'keep' => 'min,max,choice_0' ) );

  $g = adefault( $field, 'value', $current_year );

  if( $g ) {
    $g = max( min( $g, $year_max ), $year_min );
  }
  $field['min'] = adefault( $field, 'min', $year_min );
  $field['max'] = adefault( $field, 'max', $year_max );

  $choice_0 = adefault( $opts, 'choice_0', '' );
  // debug( $choice_0, 'choice_0' );
  if( $g || ! $choice_0 ) {
    $s = selector_int( $field );
    if( $choice_0 ) {
      $s .= html_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", "P{$bpriority}_{$field['name']}" => 0 ) ) );
    }
  } else {
    $s = html_span( 'quads', $choice_0 ) . html_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => $current_year ) ) );
  }
  return $s;
}

function filter_year( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'min=0,max=,choice_0= '.we(' (all) ',' (alle) ') ) );
  return selector_year( $field, $opts );
}

/*

function selector_yearterm( $fyear, $fterm, $opts = array() ) {
  global $current_year;
  // kludge alert:
  $year_min = 2012;
  $year_max = 2020;

  $opts = parameters_explode( $opts, array( 'keep' => 'min,max,choice_0' ) );

  $y = adefault( $fyear, 'value', $current_year );
  $t = adefault( $fterm, 'value', $current_term );

  if( $y ) {
    $y = max( min( $y, $year_max ), $year_min );
  }
  $min = $fyear['min'] = adefault( $fyear, 'min', $year_min );
  $max = $fyear['max'] = adefault( $fyear, 'max', $year_max );

  $choice_0 = adefault( $opts, 'choice_0', '' );
  // debug( $choice_0, 'choice_0' );
  if( $y || ! $choice_0 ) {
    $fieldname_year = $fyear['cgi_name'];
    $fieldname_term = $fterm['cgi_name'];
    $priority = 1 + adefault( $fyear, 'priority', 1 );
    $s = 
    
   inlink( '', array( 'class' => 'button tight', 'text' => ' < ', "P{$priority}_{$fieldname}" => min( $max, max( $min, $value - 1 ) ) ) )
    . int_element( $field + array( 'auto' => 1 ) )
    . inlink( '', array( 'class' => 'button tight', 'text' => ' > ', "P{$priority}_{$fieldname}" => max( $min, min( $max, $value + 1 ) ) ) )
  );
    $s = selector_int( $field );
    if( $choice_0 ) {
      $s .= html_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", $field['name'] => 0 ) ) );
    }
  } else {
    $s = html_span( 'quads', $choice_0 ) . html_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => $current_year ) ) );
  }
  return $s;
}


*/


function selector_typeofposition( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'typeofposition' );
  }

  $opts = parameters_explode( $opts );

  $field += array( 'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['choices_typeofposition'] );
  if( ! adefault( $opts, 'positionBudget' ) ) {
    if( $field['value'] === 'H' ) {
      return html_span( 'input', we('budget','Haushalt') );
    } else {
      unset( $field['choices']['H'] );
    }
  }
  return select_element( $field );
}
function filter_typeofposition( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'positionBudget=1' ) );
  return selector_typeofposition( $field, add_filter_default( $opts, $field ) );
}

function selector_lesson_type( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'lesson_type' );
  }

  $opts = parameters_explode( $opts );

  $field += array( 'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['choices_lesson_type'] );
  return select_element( $field );
}
function filter_lesson_type( $field, $opts = array() ) {
  return selector_lesson_type( $field, add_filter_default( $opts, $field ) );
}

function selector_credit_factor( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'credit_factor' );
  }

  $opts = parameters_explode( $opts );

  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['choices_credit_factor']
  , 'default_display' => ' - ? - '
  );
  return select_element( $field );
}

function selector_SWS( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'hours_per_week' );
  }

  $opts = parameters_explode( $opts );

  switch( adefault( $opts, 'lesson_type', 'other' ) ) {
    case 'FP':
      $choices = $GLOBALS['choices_SWS_FP'];
      break;
    case 'GP':
    default:
    case 'other':
      $choices = $GLOBALS['choices_SWS_other'];
      break;
  }
  if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    $choices['0.0'] = ' - 0.0 - ';
  }
  $field += array(
    'choices' => $choices
  , 'default_display' => ' - ? - '
  );
  return select_element( $field );
}

function uid_choices_keyarea() {
  return sql_query( 'groups', 'distinct=keyarea,filters=keyarea!=' );
}

function filters_person_prepare( $fields, $opts = array() ) {

  $opts = parameters_explode( $opts );
  $auto_select_unique = adefault( $opts, 'auto_select_unique', false );
  $flag_modified = adefault( $opts, 'flag_modified', false );
  $flag_problems = adefault( $opts, 'flag_problems', false );

  $person_fields = array( 'groups_id' => 'u', 'people_id' => 'u' );
  $person_fields = parameters_explode( $person_fields, 'default_value=' );
  if( $fields === true ) {
    $fields = $person_fields;
  }
  $fields = parameters_explode( $fields, 'default_value=' );

  $state = init_fields( $fields, $opts );
  $bstate = array(); // state with basenames, referencing $state (only contains relevant entries)
  foreach( $state as $fieldname => $field ) {
    if( $fieldname[ 0 ] == '_' ) {
      continue; // skip pseudo-fields
    }
    $basename = $field['basename'];
    if( ! isset( $person_fields[ $basename ] ) ) {
      continue;
    }
    $bstate[ $basename ] = & $state[ $fieldname ];
  }

  // make complete working copy of relevant fields, also containing dummy entries for fields from
  // $person_fields missing in $state (saving lots of conditionals in the loops below):
  //
  $work = array();
  foreach( $person_fields as $basename => $dummy ) {
    if( isset( $bstate[ $basename ] ) ) {
      $work[ $basename ] = & $bstate[ $basename ];
    } else {
      $work[ $basename ] = array( 'value' => NULL );
    }
  }

  $filters_global = array( '&&' );
  if( ( $f = adefault( $opts, 'filters' ) ) ) {
    $filters_global[] = $f;
  }

  // loop 1:
  // - insert info from http:
  // - if field is reset, reset more specific fields too
  // - remove inconsistencies: reset more specific fields as needed
  // - auto_select_unique: if only one possible choice for a field, select it
  //
  $filters = $filters_global;
  foreach( $bstate as $basename => & $r ) {

    if( $f = adefault( $r, 'filters' ) ) {
      $filters[] = $f;
    }

    if( $r['source'] === 'http' ) {
      // submitted from http - force new value:
      if( ( $r['value'] !== NULL ) && ( "{$r['value']}" !== "{$r['default']}" ) ) {
        $filters[ $basename ] = & $r['value'];
      } else {
        // filter was reset - reset more specific fields too:
        switch( $basename ) {
          case 'groups_id':
            $work['people_id']['value'] = 0;
        }
      }

    } else { /* not passed via http */

      if( ( $r['value'] !== NULL ) && ( "{$r['value']}" !== "{$r['default']}" ) ) {

        $filters[ $basename ] = & $r['value'];
        // value not from http - check and drop setting if inconsistent:
        switch( $basename ) {
          case 'people_id':
            $check = sql_person( $filters, 'default=0' );
            break;
          case 'groups_id':
            $check = sql_one_group( $filters, null );
            break;
          default:
            error( "unexpected fieldname [$basename]", LOG_FLAG_CODE, 'init' );
        }
        if( ! $check ) {
          $r['value'] = $r['default'];
          unset( $filters[ $basename ] );
        }
      }

      if( $auto_select_unique ) {
        if( ( $r['value'] === NULL ) || ( "{$r['value']}" === "{$r['default']}" ) ) {

          switch( $basename ) {
            case 'people_id':
              $p = sql_people( $filters );
              if( count( $p ) == 1 ) {
                $r['value'] = $p[ 0 ]['people_id'];
                $filters['people_id'] = & $r['value'];
              }
              break;
            case 'groups_id':
              $g = sql_groups( $filters );
              if( count( $g ) == 1 ) {
                $r['value'] = $g[ 0 ]['groups_id'];
                $filters['groups_id'] = & $r['value'];
              }
              break;
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
  $filters = $filters_global;
  foreach( $bstate as $basename => & $r ) {
    if( $f = adefault( $r, 'filters' ) ) {
      $filters[] = $f;
    }
    if( ( $r['value'] === NULL ) || ( "{$r['value']}" === "{$r['default']}" ) ) {
      continue;
    }
    $filters[ $basename ] = & $r['value'];
    // debug( $r, "propagate up: propagating: $basename" );
    switch( $basename ) {
      case 'people_id':
        $p = sql_people( $filters );
        if( count( $p ) == 1 ) {
          // consistent - set group to primary only if none is set:
          $p = $p[ 0 ];
          if( ! $work['groups_id']['value'] ) {
            $work['groups_id']['value'] = $p['primary_groups_id'];
          }
        } else if( $count( $p ) < 1 ) {
          // inconsistent (possible iff people_id was forced by http) - force group to primary:
          $p = sql_people( $work['people_id']['value'] );
          if( $count( $p ) == 1 ) {
            $work['groups_id']['value'] = $p['primary_groups_id'];
          } else {
            // non-existant person - refuse value:
            $work['people_id']['value'] = 0;
          }
        }

        // fall-through (in case there ever happen to be more fields)
    }
  }

  // loop 3:
  // - recheck for problems and modifications
  // - fill and return $filters array to be used in sql queries:
  //
  foreach( $bstate as $basename => & $r ) {
    $fieldname = $r['name'];
    $sql_name = adefault( $r, 'sql_name', $fieldname );

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

    if( checkvalue( $r['value'], $r ) === NULL ) {
      $r['problem'] = 'type mismatch';
      $state['_problems'][ $fieldname ] = $r['value'];
      $r['value'] = NULL;
      if( $flag_problems ) {
        $r['class'] = 'problem';
      }
    } else {
      $r['problem'] = '';
      $r['normalized'] = & $r['value'];
      unset( $state['_problems'][ $fieldname ] );
    }

    if( $r['value'] ) {
      $state['_filters'][ $sql_name ] = & $r['value'];
    } else {
      unset( $state['_filters'][ $sql_name ] );
    }
  }
  return $state;
}

?>
