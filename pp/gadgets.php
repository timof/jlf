<?php

// add_filter_default(): add default choice to turn selector into filter;
// the key for the 'no filter' choice will be
//  - '0' if keys are not uids: such keys are typically primary db keys, where 0 is an impossible value
//  - '0-0' if keys are uids: such keys are typically arbitrary strings, and '0-0' is the hard-wired uid for ''
// which should be suitable for most cases
//
function add_filter_default( $opts = array() ) {
  $opts = parameters_explode( $opts );
  // + for arrays: lhs wins in case of index conflict:
  $opts['choices'] = adefault( $opts, 'choices', array() ) + array( 0 => we( ' (all) ', ' (alle) ' ) );
  $opts['uid_choices'] = adefault( $opts, 'uid_choices', array() ) + array( '0-0' => we( ' (all) ', ' (alle) ' ) );
  return $opts;
}

function filter_reset_button( $filters, $opts = array() ) {
  $opts = parameters_explode( $opts, 'class' );
  $class = merge_classes( 'button tight floatright', adefault( $opts, 'class', '' ) );
  $parameters = array( 'text' => 'C', 'class' => $class, 'inactive' => true, 'title' => we('reset filter','filter zurücksetzen') );
  if( isset( $filters['cgi_name'] ) && ! isarray( $filters['cgi_name'] ) ) {
    $filters = array( 'f' => $filters );
  }
  foreach( $filters as $key => $f ) {
    if( $key[ 0 ] === '_' ) {
      continue;
    }
    if( $f['value'] !== NULL ) {
      if( $f['value'] !== $f['initval'] ) {
        unset( $parameters['inactive'] );
        $parameters[ $f['cgi_name'] ] = $f['initval'];
      }
    }
  }
  return inlink( '!', $parameters );
}

function select_element( $field, $more_opts = array() ) {
  global $H_SQ;

  $more_opts = parameters_explode( $more_opts );
  $field = parameters_merge( $field, $more_opts );

  $keyformat = adefault( $field, 'keyformat', 'choice' );
  $selected = adefault( $field, array( 'selected', 'normalized', 'value' ), false );

  // what to display if no valid choice is currently selected:
  //
  $default_display = adefault( $field, 'default_display', we('(please select)','(bitte wählen)') );

  // what to display if no choices are available at all:
  //
  $empty_display = adefault( $field, 'empty_display', we('(selection is empty)','(Auswahl ist leer)' ) );

  $form_id = adefault( $field, 'form_id', 'update_form' );
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ), '' );
  $fieldclass = adefault( $field, 'class', '' );
  $priority = adefault( $field, 'priority', 1 );

  $choices = adefault( $field, 'choices', array() );
  if( $keyformat === 'uid_choice' ) {
    $tmp = array();
    foreach( $choices as $key => $val ) {
      $tmp[ value2uid( $key ) ] = $val;
    }
    $choices = $tmp + adefault( $field, 'uid_choices', array() );
    $fieldname = "UID_$fieldname";
    $selected = value2uid( $selected );
  }
  $pfieldname = "P{$priority}_{$fieldname}";
  if( ! $choices ) {
    return html_span( '', $empty_display );
  }

  $id = 'select'.new_html_id();
  $attr = array(
    'name' => '' // don't submit unless changed
  , 'id' => $id
  , 'class' => $fieldclass
  );

  switch( $keyformat ) {
    case 'choice':
    case 'uid_choice':
      $tmp = array();
      foreach( $choices as $key => $val ) {
        if( "$val" !== '' ) {
          $tmp[ bin2hex( $key ) ] = $val;
        }
      }
      $choices = $tmp;
      if( ( $selected !== false ) && ( "$selected" !== '' ) ) {
        $selected = bin2hex( $selected );
      }
      $attr['onchange'] = "submit_form( {$H_SQ}{$form_id}{$H_SQ}, {$H_SQ}{$pfieldname}={$H_SQ} + $({$H_SQ}{$id}{$H_SQ}).value );";
      break;
    case 'form_id':
      $attr['onchange'] = "submit_form( $({$H_SQ}{$id}{$H_SQ}).value )";
      break;
    case 'line':
      error( "browser_select_element(): key format 'line' not supported" );
  }
  return html_tag( 'select', $attr, html_options( $choices, array( 'selected' => $selected, 'default_display' => $default_display ) ) );
}


//   $attr = array(
//     'name' => '' // don't submit unless changed ////was:  "P{$priority}_{$fieldname}"
//   , 'id' => $id
//   , 'class' => $fieldclass
//   , 'onchange' => ( $fieldname ?
//         "submit_form( {$H_SQ}{$form_id}{$H_SQ}, {$H_SQ}{$pfieldname}={$H_SQ} + $({$H_SQ}{$id}{$H_SQ}).value );"
//       : "submit_form( $({$H_SQ}{$id}{$H_SQ}).value )"
//     )
//   );
//   if( ! $selected ) {
//     $selected = '0';
//   }
//   if( ! isset( $choices[ $selected ] ) ) {
//     $choices = array( $selected => $default_display ) + $choices;
//   }
// 
//   if( $fieldname ) {
//     $hexchoices = array();
//     foreach( $choices as $key => $val ) {
//       if( ! $val )
//         continue;
//       $hexchoices[ bin2hex( $key ) ] = $val;
//     }
//     // if( isset( $attr['selected'] ) ) {
//     //  $attr['selected'] = bin2hex( $attr['selected'] );
//     // }
//     $selected = bin2hex( $selected );
// 
//     return html_tag( 'select', $attr, html_options( $hexchoices, array( 'selected' => $selected ) ) );
// 
//   } else {
//     return html_tag( 'select', $attr, html_options( $choices, array( 'selected' => $selected ) ) );
// 
//   }
// }


function download_button( $item, $formats, $opts = array() ) {
  global $script;
  $formats = parameters_explode( $formats );
  $opts = parameters_explode( $opts, 'item' );
  $action = adefault( $opts, 'action', 'download' );
  $choices = array();
  $s = html_tag( 'ul', 'inline' );
  foreach( $formats as $f => $flag ) {
    if( ! $flag )
      continue;
    switch( $f ) {
      case 'csv':
      case 'jpg':
        $window = 'NOWINDOW';
        break;
      case 'ldif':
      case 'pdf': // force different browser window (for people with embedded viewers!)
      default:
        $window = 'download';
        break;
    }
    // $choices[ open_form( "script=self,window=$window,f=$f,i=$item,text=$f", "action=$action", 'hidden' ) ] = $f;
    $s .= html_tag( 'li', '', inlink( '', "class=file,window=$window,f=$f,i=$item,text=$f,title=download $f" ) );
  }
  $s .= html_tag( 'ul', false );
  return $s;
  // return select_element( array( 'default_display' => 'download...', 'choices' => $choices ) );
}

// functions for drop-down selectors; we usually provide a triple of functions:
// - choices_X( $filters = array() )
//     returns an array of <id> => <option> pairs matching $filters
// - selector_X( $field, $opts )
//     create drop-down selection gadget
//     $opts may contain
//      'filters': filter (array or string) to narrow selection
//      'choices': array of 'value' => 'text' pairs to also offer for selection
//      ('uid_choices' in those cases where keys are uids)
// - filter_X( $field, $opts = array() )
//     create drop-down selection gadget for filtering; $opts may contain
//       'filters': to narrow selection
//       'choices': array of extra choices; default: '0' => ' (all) '; pass an empty array to offer no 'choice 0'

function choices_people( $filters = array() ) {
  $filters = restrict_view_filters( $filters, 'people' );
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
  $filters = parameters_explode( adefault( $opts, 'filters', ''), array( 'keep' => 'groups_id' ) );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_people( adefault( $opts, 'filters', array() ) )
  , 'default_display' => we(' - select person - ', ' - Person w'.H_AMP.'auml;hlen - ')
  , 'empty_display' => we('(no people available)', '(keine Personen vorhanden)')
  );
  return select_element( $field );
}

function filter_person( $field, $opts = array() ) {
  return selector_people( $field, add_filter_default( $opts ) );
}


function choices_groups( $filters = array() ) {
  $filters = restrict_view_filters( $filters, 'groups' );
  $choices = array();
  foreach( sql_groups( $filters, 'acronym' ) as $g ) {
    $choices[ $g['groups_id'] ] = $g['cn'];
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
  $opts = parameters_explode( $opts );
  if( isset( $field['choices'] ) ) {
    $field['choices'] = array( 0 => we( ' (all) ', ' (alle) ' ) ) + $field['choices'];
    return select_element( $field, $opts );
  } else {
    return selector_groups( $field, add_filter_default( $opts ) );
  }
}


function selector_degree( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'degree_id' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['degree_text']
  , 'default_display' => we(' - select type / degree - ',' - Art / Abschluss w'.H_AMP.'auml;hlen - ')
  );
  return select_element( $field );
}

function filter_degree( $field, $opts = array() ) {
  return selector_degree( $field, add_filter_default( $opts ) );
}


function selector_programme( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'programme_id' );
  }
  $opts = parameters_explode( $opts );
  $field += array( 
    'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['programme_text']
  , 'default_display' => we(' - select programme - ',' - Studiengang w'.H_AMP.'auml;hlen - ')
  );
  return select_element( $field );
}

function filter_programme( $field, $opts = array() ) {
  return selector_programme( $field, add_filter_default( $opts ) );
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
      $t .= html_span( 'quads', inlink( '!', array( 'class' => 'button', 'text' => "$choice_0", $field['name'] => 0 ) ) );
    }
  } else {
    $t = html_span( 'quads', $choice_0 ) . html_span( 'quads', inlink( '!', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => 1 ) ) );
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
  return selector_term( $field, add_filter_default( $opts ) );
}


function selector_year( $field = NULL, $opts = array() ) {
  global $current_year;
  // kludge alert:
  $year_min = 2012;
  $year_max = 2020;

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
      $s .= html_span( 'quads', inlink( '!', array( 'class' => 'button', 'text' => "$choice_0", $field['name'] => 0 ) ) );
    }
  } else {
    $s = html_span( 'quads', $choice_0 ) . html_span( 'quads', inlink( '!', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => $current_year ) ) );
  }
  return $s;
}

function filter_year( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'min=0,max=,choice_0= '.we(' (all) ',' (alle) ') ) );
  if( isset( $field['choices'] ) ) {
    $field['choices'] = array( 0 => $opts['choice_0'] ) + $field['choices'];
    return select_element( $field, $opts );
  } else {
    return selector_year( $field, $opts );
  }
}


function selector_typeofposition( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'typeofposition' );
  }

  $opts = parameters_explode( $opts );

  $field += array( 'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['choices_typeofposition'] );
  return select_element( $field );
}
function filter_typeofposition( $field, $opts = array() ) {
  return selector_typeofposition( $field, add_filter_default( $opts ) );
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
  return selector_lesson_type( $field, add_filter_default( $opts ) );
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

  $choices = adefault( $opts, 'choices', array() );
  switch( adefault( $opts, 'lesson_type', 'other' ) ) {
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
  $field += array(
    'choices' => $choices
  , 'default_display' => ' - ? - '
  );
  return select_element( $field );
}

function filters_person_prepare( $fields, $opts = array() ) {

  $opts = parameters_explode( $opts );
  $auto_select_unique = adefault( $opts, 'auto_select_unique', false );
  $flag_modified = adefault( $opts, 'flag_modified', false );
  $flag_problems = adefault( $opts, 'flag_problems', false );

  $person_fields = array( 'groups_id' => 'u', 'people_id' => 'u' );
  if( $fields === true ) {
    $fields = $person_fields;
  }
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

    if( ! isset( $bstate[ $fieldname ] ) ) {
      continue;
    }

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
    if( ! $r['value'] ) {
      continue;
    }
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
