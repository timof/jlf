<?php


// functions for drop-down selectors; we usually provide a triple of functions:
// - choices_X( $filters = array() )
//     returns an array of <id> => <option> pairs matching $filters
// - selector_X( $field, $opts )
//     create drop-down selection gadget
//     $opts may contain
//      'filters': filter (array or string) to narrow selection
//      'more_choices': array of 'value' => 'text' pairs to also offer for selection
// - filter_X( $field, $opts = array() )
//     create drop-down selection gadget for filtering; $opts may contain
//       'filters': to narrow selection
//       'choice_0': extra choice with value '0' (default: '(all)'; set to NULL to offer no choice_0)

function choices_people( $filters = array() ) {
  $choices = array();
  foreach( sql_people( $filters ) as $p ) {
    $choices[ $p['people_id'] ] = $p['cn'];
  }
  $choices[''] = $choices ? we(' - select person - ', ' - Person w'.H_AMP.'auml;hlen - ') : we('(no people available)', '(keine Personen vorhanden)');
  return $choices;
}

function selector_people( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'people_id' );
  $opts = parameters_explode( $opts );
  $filters = parameters_explode( adefault( $opts, 'filters', ''), array( 'keep' => 'groups_id' ) );
  $field['choices'] = adefault( $opts, 'more_choices', array() ) + choices_people( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
}

function filter_person( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  $opts['more_choices'] = array( 0 => we(' (all) ',' (alle) ') );
  selector_people( $field, $opts );
}


function choices_groups( $filters = array() ) {
  $choices = array();
  foreach( sql_groups( $filters, 'acronym' ) as $g ) {
    $choices[ $g['groups_id'] ] = $g['acronym'];
  }
  $choices[''] = $choices ? we(' - select group - ', ' - Gruppe w'.H_AMP.'auml;hlen - ') : we('(no groups available)', '(keine Gruppen vorhanden)');
  return $choices;
}

function selector_groups( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'groups_id' );
  $opts = parameters_explode( $opts );
  $field['choices'] = adefault( $opts, 'more_choices', array() ) + choices_groups( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
}

function filter_group( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  $opts['more_choices'] = array( 0 => we(' (all) ', ' (alle) ') );
  selector_groups( $field, $opts );
}


function choices_degree() {
  $choices = $GLOBALS['degree_text'];
  $choices[''] = we(' - select type / final degree - ',' - Art / Abschluss w'.H_AMP.'auml;hlen - ');
  return $choices;
}

function selector_degree( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'degree_id' );
  $opts = parameters_explode( $opts );
  $field['choices'] = adefault( $opts, 'more_choices', array() ) + choices_degree( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
}

function filter_degree( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  $opts['more_choices'] = array( 0 => we(' (all) ', ' (alle) ') );
  selector_degree( $field, $opts );
}
  

function choices_programme() {
  $choices = $GLOBALS['programme_text'];
  $choices[''] = we(' - select programme - ',' - Studiengang w'.H_AMP.'auml;hlen - ');
  return $choices;
}

function selector_programme( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'programme_id' );
  $opts = parameters_explode( $opts );
  $field['choices'] = adefault( $opts, 'more_choices', array() ) + choices_programme( adefault( $opts, 'filters', array() ) );
  echo dropdown_element( $field );
}

function filter_programme( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  $opts['more_choices'] = array( 0 => we(' (all) ', ' (alle) ') );
  selector_programme( $field, $opts );
}


function selector_semester( $field = NULL, $opts = array() ) {

  if( ! $field )
    $field = array( 'name' => 'semester' );

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
    selector_int( $field );
    if( $choice_0 ) {
      open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", $field['name'] => 0 ) ) );
    }
  } else {
    open_span( 'quads', $choice_0 );
    open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => 1 ) ) );
  }
}

function filter_semester( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'min=1,max=12,choice_0= '.we(' (all) ',' (alle) ' ) ) );
  selector_semester( $field, $opts );
}


function selector_term( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'semester' );

  $opts = parameters_explode( $opts );

  $field['choices'] = adefault( $opts, 'more_choices', array() ) + array( 'S' => we('Summer','Sommer'), 'W' => 'Winter' );
  echo dropdown_element( $field );
}
function filter_term( $field, $opts = array() ) {
  selector_term( $field, array( 'more_choices' => array( '0' => we(' (all) ',' (alle) ') ) ) );
}


function selector_year( $field = NULL, $opts = array() ) {
  global $current_year;
  // kludge alert:
  $year_min = 2012;
  $year_max = 2020;

  if( ! $field )
    $field = array( 'name' => 'year' );

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
    selector_int( $field );
    if( $choice_0 ) {
      open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", $field['name'] => 0 ) ) );
    }
  } else {
    open_span( 'quads', $choice_0 );
    open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => $current_year ) ) );
  }
}

function filter_year( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'min=0,max=,choice_0= '.we(' (all) ',' (alle) ') ) );
  selector_year( $field, $opts );
}


function selector_typeofposition( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'typeofposition' );

  $opts = parameters_explode( $opts );

  $field['choices'] = adefault( $opts, 'more_choices', array() ) + $GLOBALS['choices_typeofposition'];
  echo dropdown_element( $field );
}
function filter_typeofposition( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'choice_0='.we(' (all) ',' (alle) ') ) );
  selector_typeofposition( $field, $opts );
}

function selector_course_type( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'course_type' );

  $opts = parameters_explode( $opts );

  $field['choices'] = adefault( $opts, 'more_choices', array() ) + $GLOBALS['choices_course_type'];
  echo dropdown_element( $field );
}
function filter_course_type( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'choice_0='.we(' (all) ',' (alle) ') ) );
  selector_course_type( $field, $opts );
}

function selector_credit_factor( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'credit_factor' );

  $opts = parameters_explode( $opts );

  $field['choices'] = adefault( $opts, 'more_choices', array() ) + $GLOBALS['choices_credit_factor'];
  $field['choices'][''] = ' - ? - ';
  echo dropdown_element( $field );
}

function selector_SWS( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'hours_per_week' );

  $opts = parameters_explode( $opts );

  $choices = adefault( $opts, 'more_choices', array() );
  switch( adefault( $opts, 'course_type', 'other' ) ) {
    case 'FP':
      $choices = $GLOBALS['choices_SWS_FP'];
      break;
    default:
    case 'other':
      $choices = $GLOBALS['choices_SWS_other'];
      break;
  }
  if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    $choices['0.0'] = ' - 0.0 - ';
  }
  $choices[''] = ' - ? - ';
  $field['choices'] = $choices;
  echo dropdown_element( $field );
}

function filters_person_prepare( $fields, $opts = array() ) {

  $opts = parameters_explode( $opts );
  $auto_select_unique = adefault( $opts, 'auto_select_unique', false );
  $flag_modified = adefault( $opts, 'flag_modified', false );
  $flag_problems = adefault( $opts, 'flag_problems', false );

  $person_fields = array( 'groups_id' => 'u', 'people_id' => 'u' );
  if( $fields === true )
    $fields = $person_fields;
  $fields = parameters_explode( $fields );

  $state = init_fields( $fields, $opts );
  $bstate = array();
  foreach( $state as $fieldname => $field ) {
    if( ! isset( $fields[ $fieldname ] ) )
      continue; // skip pseudo-fields with _-prefix
    $basename = adefault( $field, 'basename', $fieldname );
    $sql_name = adefault( $field, 'sql_name', $basename );
    // debug( $field, $fieldname );
    need( in_array( $basename, array_keys( $person_fields ) ) );
    $bstate[ $basename ] = & $state[ $fieldname ];
  }

  $work = array();
  foreach( $person_fields as $fieldname => $field ) {
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
  foreach( $person_fields as $fieldname => $field ) {

    if( ! isset( $bstate[ $fieldname ] ) )
      continue;

    $r = & $bstate[ $fieldname ];

    if( $r['source'] === 'http' ) {
      // submitted from http - force new value:
      if( $r['value'] ) {
        $filters[ $fieldname ] = & $r['value'];
      } else {
      // filter was reset - reset more specific fields too:
        switch( $fieldname ) {
          case 'groups_id':
            $work['people_id']['value'] = 0;
        }
      }

    } else { /* not passed via http */

      if( $r['value'] ) {
        $filters[ $fieldname ] = & $r['value'];
        // value not from http - check and drop setting if inconsistent:
        switch( $fieldname ) {
          case 'people_id':
          $check = sql_person( $filters, null );
          break;
        case 'groups_id':
          $check = sql_one_group( $filters, null );
          break;
        default:
          error( "unexpected fieldname [$fieldname]", LOG_FLAG_CODE, 'init' );
        }
        if( ! $check ) {
          $r['value'] = 0;
          unset( $filters[ $fieldname ] );
        }
      }

      if( $auto_select_unique ) {
        if( ! $r['value'] ) {
          switch( $fieldname ) {
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
  foreach( $person_fields as $fieldname => $field ) {
    $r = & $work[ $fieldname ];
    if( ! $r['value'] )
      continue;
    // debug( $r, "propagate up: propagating: $fieldname" );
    switch( $fieldname ) {
      case 'people_id':
        $p = sql_people( $filters );
        if( count( $p ) == 1 ) {
          // consistent - set group to primary only if none is set:
          $p = $p[ 0 ];
          if( ! $work['groups_id']['value'] ) {
            $work['groups_id']['value'] = $p['primary_groups_id'];
          }
        } else if( $count( $p ) < 1 ) {
          // inconsistent (possible if people_id was forced by http) - force group to primary:
          $p = sql_people( $work['people_id']['value'] );
          if( $count( $p ) == 1 ) {
            $work['groups_id']['value'] = $p['primary_groups_id'];
          }
        }
          
        // fall-through (in case there ever happen to be more fields)
    }
  }
  
  // debug( $work, 'work before loop 3' );

  // loop 3: check for modifications, errors, and set filters:
  //
  foreach( $person_fields as $fieldname => $field ) {
    $r = & $work[ $fieldname ];

    $r['class'] = '';
    if( ( (string) $r['value'] ) !== ( (string) adefault( $r, 'old', $r['value'] ) ) ) {
      $r['modified'] = 'modified';
      $state['_changes'][ $fieldname ] = $r['value'];
      if( $flag_modified ) {
        $r['class'] = 'modified';
      }
    } else {
      $r['modified'] = '';
      unset( $state['_changes'][ $fieldname ] );
    }

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

    if( $r['value'] ) {
      $state['_filters'][ $r['sql_name'] ] = & $r['value'];
    } else {
      unset( $state['_filters'][ $r['sql_name'] ] );
    }
  }
  // debug( $state, 'state final' );
  return $state;
}

?>
