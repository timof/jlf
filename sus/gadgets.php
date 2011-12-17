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
  $choices[''] = $choices ? ' - Person w'.H_AMP.'auml;hlen - ' : '(keine Personen vorhanden)';
  return $choices;
}

function selector_people( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'people_id' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_people( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_person( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_people( $field, $opts );
}


function choices_jperson() {
  return array( '' => ' - Personenart w'.H_AMP.'auml;hlen - ', 'N' => 'nat'.H_AMP.'uuml;rlich', 'J' => 'juristisch' );
}

function selector_jperson( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'jperson' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_jperson( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_jperson( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_jperson( $field, $opts );
}

function choices_dusie() {
  return array( '' => ' - Anredeart w'.H_AMP.'auml;hlen - ', 'S' => 'Siezen', 'D' => 'Duzen' );
}

function selector_dusie( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'dusie' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_dusie( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_dusie( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_dusie( $field, $opts );
}

function choices_genus() {
  return array( '' => ' - Genus w'.H_AMP.'auml;hlen - ', 'N' => 'ne-utrum', 'M' => 'maskulin', 'F' => 'feminin' );
}

function selector_genus( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'genus' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_genus( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_genus( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_genus( $field, $opts );
}


function choices_kontenkreis() {
  return array( '' => ' - Kontenkreis w'.H_AMP.'auml;hlen - ', 'B' => 'Bestand', 'E' => 'Erfolg' );
}

function selector_kontenkreis( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'kontenkreis' );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_kontenkreis( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_kontenkreis( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_kontenkreis( $field, $opts );
}


function choices_seite() {
  return array( '' => ' - Seite w'.H_AMP.'auml;hlen - ', 'A' => 'Aktiv', 'P' => 'Passiv' );
}

function selector_seite( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'seite' );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_seite( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_seite( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_seite( $field, $opts );
}


function choices_geschaeftsbereiche() {
  $choices = array();
  $choices += sql_unique_values( 'kontoklassen', 'geschaeftsbereich' );
  foreach( $choices as $k => $v ) {
    if( ! $v )
      unset( $choices[$k] );
  }
  $choices[''] = $choices ? ' - Gesch'.H_AMP.'auml;ftsbereich w'.H_AMP.'auml;hlen - ' : '(keine Gesch'.H_AMP.'auml;ftsbereiche vorhanden)';
  return $choices;
}

function selector_geschaeftsbereich( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'geschaeftsbereiche_id' );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_geschaeftsbereiche( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_geschaeftsbereich( $field, $opts =array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_geschaeftsbereich( $field, $opts );
}


function choices_kontoklassen( $filters = array() ) {
  $choices = array();
  foreach( sql_kontoklassen( $filters ) as $k ) {
    $id = $k['kontoklassen_id'];
    $choices[ $id ] = "$id {$k['kontenkreis']} {$k['seite']} {$k['cn']}";
    if( $k['geschaeftsbereich'] )
      $choices[ $id ] .= " / " . $k['geschaeftsbereich'];
  }
  $choices[''] = $choices ? ' - Kontoklasse w'.H_AMP.'auml;hlen - ' : '(keine Kontoklassen vorhanden)';
  return $choices;
}

function selector_kontoklasse( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'kontoklassen_id' );
  $opts = parameters_explode( $opts );
  $filters = parameters_explode( $opts['filters'], array( 'keep' => 'seite,kontenkreis,geschaeftsbereiche_id' ) );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_kontoklassen( $filters );
  dropdown_select( $field, $choices );
}

function filter_kontoklasse( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_kontoklasse( $field, $opts );
}

function choices_hgb_klassen( $kontenkreis = '', $seite = '' ) {
  $choices = array();
  foreach( $GLOBALS['hgb_klassen'] as $i => $k ) {
    if( $kontenkreis && ( substr( $i, 0, 1 ) !== $kontenkreis ) )
      continue;
    if( $seite && ( substr( $i, 2, 1 ) !== $seite ) )
      continue;
    if( adefault( $k, 'zwischensumme', false ) )
      continue;
    $choices[ $i ] = "[$i] ${k['rubrik']}";
    if( adefault( $k, 'titel', '' ) )
      $choices[ $i ] .= "/ ${k['titel']}";
    if( adefault( $k, 'subtitel', '' ) )
      $choices[ $i ] .= "/ ${k['subtitel']}";
  }
  $choices[''] = $choices ? ' -'.H_AMP.'nbsp;HGB-Klasse w'.H_AMP.'auml;hlen'.H_AMP.'nbsp;- ' : '(keine HGB-Klassen vorhanden)';
  return $choices;
}

function selector_hgb_klasse( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'hgb_klasse' );
  $opts = parameters_explode( $opts, array( 'keep' => 'filters=,more_choices' ) );
  $filters = parameters_explode( $opts['filters'], array( 'keep' => 'seite=,kontenkreis=' ) );
  $choices = adefault( $opts, 'more_choices', array() )
             + choices_hgb_klassen( $filters['kontenkreis'], $filters['seite'] );
  dropdown_select( $field, $choices );
}

function filter_hgb_klasse( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_hgb_klasse( $r, NULL, $kontenkreis, $seite, $option_0 );
}


function choices_hauptkonten( $filters = array() ) {
  $choices = array();
  foreach( sql_hauptkonten( $filters ) as $k ) {
    $id = $k['hauptkonten_id'];
    $choices[ $id ] = "{$k['kontenkreis']} {$k['seite']} {$k['rubrik']} : {$k['titel']}";
    if( $GLOBALS['unterstuetzung_geschaeftsbereiche'] && $k['geschaeftsbereich'] ) {
      if( ! adefault( $filters, 'geschaeftsbereiche_id', 0 ) )
        $choices[ $id ] .= " / ".$k['geschaeftsbereich'];
    }
  }
  $choices[''] = $choices ? ' -'.H_AMP.'nbsp;Hauptkonto w'.H_AMP.'auml;hlen'.H_AMP.'nbsp;- ' : '(keine Hauptkonten vorhanden)';
  return $choices;
}

function selector_hauptkonto( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'hauptkonten_id' );
  $opts = parameters_explode( $opts, array( 'keep' => 'filters=,more_choices' ) );
  $filters = parameters_explode( $opts['filters'], array( 'keep' =>
    'hauptkonto_geschlossen,personenkonto,sachkonto,bankkonto,vortragskonto,seite,kontenkreis,geschaeftsbereiche_id,kontoklassen_id,geschaeftsjahr'
  ) );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_hauptkonten( $filters );
  dropdown_select( $field, $choices );
}

function filter_hauptkonto( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_hauptkonto( $field, $opts );
}


function choices_unterkonten( $filters = array() ) {
  $choices = array();
  foreach( sql_unterkonten( $filters, 'cn' ) as $k ) {
    $choices[ $k['unterkonten_id'] ] = $k['cn'];
  }
  $choices[''] = $choices ? ' -'.H_AMP.'nbsp;Unterkonto w'.H_AMP.'auml;hlen'.H_AMP.'nbsp;- ' : '(keine Unterkonten vorhanden)';
  return $choices;
}

function selector_unterkonto( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'unterkonten_id' );
  $opts = parameters_explode( $opts, array( 'keep' => 'filters=,more_choices' ) );
  $filters = parameters_explode( $opts['filters'], array( 'keep' =>
    'unterkonto_geschlossen,people_id,zinskonto,personenkonto,sachkonto,bankkonto,vortragskonto'
    . ',hauptkonten_id,seite,kontenkreis,geschaeftsbereiche_id,kontoklassen_id,geschaeftsjahr'
  ) );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_unterkonten( $filters );
  dropdown_select( $field, $choices );
}

function filter_unterkonto( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_unterkonto( $field, $opts );
}


function choices_rubriken( $filters = array() ) {
  $choices = array();
  foreach( sql_rubriken( $filters ) as $r ) {
    $choices[ $r['rubriken_id'] ] = $r['rubrik'];
  }
  $choices[''] = $choices ? ' - Rubrik w'.H_AMP.'auml;hlen - ' : '(keine Rubriken vorhanden)';
  return $choices;
}

function selector_rubrik( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'rubriken_id' );
  $opts = parameters_explode( $opts, array( 'keep' => 'filters=,more_choices' ) );
  $filters = parameters_explode( $opts['filters'], array( 'keep' => 'seite,kontenkreis,geschaeftsbereiche_id,kontoklassen_id,geschaeftsjahr' ) );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_rubriken( $filters );
  dropdown_select( $field, $choices );
}

function filter_rubrik( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_rubrik( $field, $opts );
}


function choices_titel( $filters = array() ) {
  $choices = array();
  foreach( sql_titel( $filters ) as $t ) {
    $choices[ $t['titel_id'] ] = $t['titel'];
  }
  $choices[''] = $choices ? ' - Titel w'.H_AMP.'auml;hlen - ' : '(keine Titel vorhanden)';
  return $choices;
}

function selector_titel( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'titel_id' );
  $opts = parameters_explode( $opts, array( 'keep' => 'filters=,more_choices' ) );
  $filters = parameters_explode( $opts['filters'], array( 'keep' => 'seite,kontenkreis,geschaeftsbereiche_id,kontoklassen_id,geschaeftsjahr,rubriken_id' ) );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_titel( $filters );
  dropdown_select( $field, $choices );
}

function filter_titel( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_titel( $field, $opts );
}


function choices_things( $filters = array(), $option_0 = false ) {
  $choices = array();
  foreach( sql_things( $filters ) as $thing ) {
    $choices[ $thing['things_id'] ] = $thing['cn'];
    if( $thing['anschaffungsjahr'] )
      $choices[ $thing['things_id'] ] .= " ({$thing['anschaffungsjahr']}) ";
  }
  $choices[''] = $choices ? ' - Gegenstand w'.H_AMP.'auml;hlen - ' : '(keine Gegenst'.H_AMP.'auml;nde vorhanden)';
  return $choices;
}

function selector_thing( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'things_id' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_things( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_thing( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  selector_thing( $field, $opts );
}


function choices_anschaffungsjahr() {
  $choices = array();
  foreach( sql_unique_values( 'things', 'anschaffungsjahr' ) as $r ) {
    $j = $r['anschaffungsjahr'];
    $choices[ $j ] = $j;
  }
  $choices[''] = $choices ? ' - Anschaffungsjahr w'.H_AMP.'auml;hlen - ' : '(keine Jahre vorhanden)';
  return $choices;
}

function selector_anschaffungsjahr( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'anschaffungsjahr' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_anschaffungsjahr();
  dropdown_select( $field, $choices );
}

function filter_anschaffungsjahr( $prefix = '', $option_0 = '(alle)' ) {
  $r = init_var( $prefix.'anschaffungsjahr', 'global,pattern=u,sources=http persistent,default=0,set_scopes=self' );
  selector_anschaffungsjahr( $r, NULL, $option_0 );
}


// function options_geschaeftsjahre( $selected = 0, $option_0 = false ) {
//   $options = array();
//   if( $option_0 )
//     $options[ 0 ] = $option_0;
//   for( $j = $GLOBALS['geschaeftsjahr_min']; $j <= $GLOBALS['geschaeftsjahr_max']; $j++ )
//     $options[ $j ] = $j;
//   $options[''] = $options ? ' - Gesch'.H_AMP.'auml;ftsjahr w'.H_AMP.'auml;hlen - ' : '(keine Jahre vorhanden)';
//   return $options;
// }

function selector_geschaeftsjahr( $field = NULL, $opts = array() ) {
  global $geschaeftsjahr_current, $geschaeftsjahr_min, $geschaeftsjahr_max;

  if( ! $field )
    $field = array( 'name' => 'geschaeftsjahr' );

  $opts = parameters_explode( $opts, array( 'keep' => 'min,max,choice_0' ) );

  $g = adefault( $field, 'value', $geschaeftsjahr_current );

  if( $g ) {
    $g = max( min( $g, $geschaeftsjahr_max ), $geschaeftsjahr_min );
  }
  $field['min'] = adefault( $field, 'min', $geschaeftsjahr_min );
  $field['max'] = adefault( $field, 'max', $geschaeftsjahr_max );

  $choice_0 = adefault( $opts, 'choice_0', '' );
  // debug( $choice_0, 'choice_0' );
  if( $g || ! $choice_0 ) {
    selector_int( $field );
    if( $choice_0 ) {
      open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", $field['name'] => 0 ) ) );
    }
  } else {
    open_span( 'quads', $choice_0 );
    open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => $geschaeftsjahr_current ) ) );
  }
}

function filter_geschaeftsjahr( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'min=0,max=,choice_0= (alle) ' ) );
  selector_geschaeftsjahr( $field, $opts );
}


function selector_stichtag( $field, $opts = array() ) {
  $p = array(
    'class' => 'button'
  , 'text' => 'Vortrag < '
  , 'inactive' => ( $field['value'] <= 100 )
  , $field['name'] => 100
  );
  echo inlink( '', $p );
  $field['size'] = 4;
  echo int_element( $field );
  $p['text'] = ' > Ultimo';
  $p['inactive'] = ( $field['value'] >= 1231 );
  $p[ $field['name'] ] = 1231;
  echo inlink( '', $p );
}

// filter_stichtag() ... makes no sense!


// filters_kontodaten_prepare:
// $fields: list of $fields to initialize. will call prepare_filters, then apply special logic to get
// some well-known fields consistent and derive values of less specific fields from more specific ones.
//
function filters_kontodaten_prepare( $fields = true, $opts = array() ) {

  $opts = parameters_explode( $opts );
  $auto_select_unique = adefault( $opts, 'auto_select_unique', false );
  $flag_modified = adefault( $opts, 'flag_modified', false );
  $flag_problems = adefault( $opts, 'flag_problems', false );

  // kontodaten_fields: order matters here, for specifity and for filtering
  // (later fields must allow earlier ones as filters)
  $kontodaten_fields = array( 'seite', 'kontenkreis', 'geschaeftsbereiche_id', 'kontoklassen_id', 'geschaeftsjahr', 'hauptkonten_id', 'unterkonten_id' );
  if( $fields === true )
    $fields = $kontodaten_fields;

  if( isset( $opts['rows']['posten']['geschaeftsbereich'] ) ) {
    $opts['rows']['posten']['geschaeftsbereiche_id'] = sql_unique_id( 'kontoklassen', 'geschaeftsbereich', $opts['rows']['posten']['geschaeftsbereich'] );
  }
  $state = init_fields( $fields, $opts );
  // debug( $state, 'state A' );

  // make complete working copy of state, also containing dummy entries for fields from
  // $kontodaten_fields missing in $state (saving lots of conditionals in the loops below):
  //
  $work = array();
  foreach( $kontodaten_fields as $fieldname ) {
    if( isset( $state[ $fieldname ] ) ) {
      $work[ $fieldname ] = & $state[ $fieldname ];
    } else {
      $work[ $fieldname ] = array( 'value' => NULL );
    }
  }

  // loop one: insert info from http:
  // - if field is reset, reset more specific fields too
  // - remove inconsistencies: reset more specific fields as needed
  // - auto_select_unique: if only one possible choice for a field, select it
  //
  $filters = array();
  foreach( $kontodaten_fields as $fieldname ) {
    if( ! isset( $state[ $fieldname ] ) )
      continue;
    $r = & $state[ $fieldname ];

    if( $r['source'] === 'http' ) {
      if( $r['value'] ) {
        $filters[ $fieldname ] = & $r['value'];
      } else {
        // filter was reset - reset more specific fields too:
        switch( $fieldname ) {
          case 'geschaeftsbereiche_id':
            if( isset( $state['kontenkreis'] ) ) {
              if( $state['kontenkreis']['value'] !== 'E' ) {
                break;
              }
            }
          case 'seite':
          case 'kontenkreis':
            $work['kontoklassen_id']['value'] = 0;
          case 'kontoklassen_id':
            $work['hauptkonten_id']['value'] = 0;
          case 'hauptkonten_id':
            $work['unterkonten_id']['value'] = 0;
        }
      }
    } else { /* not passed via http */

      if( $r['value'] ) {

        $filters[ $fieldname ] = & $r['value'];
        // value not from http - check and drop setting if inconsistent:
        switch( $fieldname ) {
          case 'unterkonten_id':
            $check = sql_unterkonten( $filters );
            break;
          case 'hauptkonten_id':
            $check = sql_hauptkonten( $filters );
            break;
          case 'geschaeftsbereiche_id':
            if( isset( $state['kontenkreis'] ) ) {
              $check = ( $state['kontenkreis']['value'] !== 'B' );
            } else {
              $check = true;
            }
            break;
          case 'kontoklassen_id':
            $check = sql_kontoklassen( $filters );
            break;
          default:
            $check = true;;
        }
        if( ! $check ) {
          $r['value'] = 0;
          unset( $filters[ $fieldname ] );
        }
      }

      if( ! $r['value'] && $auto_select_unique ) {

        switch( $fieldname ) {
          case 'unterkonten_id':
            $uk = sql_unterkonten( $filters );
            if( count( $uk ) == 1 ) {
              $r['value'] = $uk[ 0 ]['unterkonten_id'];
              $filters['unterkonten_id'] = & $r['value'];
            }
            break;
          case 'hauptkonten_id':
            $hk = sql_hauptkonten( $filters );
            if( count( $hk ) == 1 ) {
              $r['value'] = $hk[ 0 ]['hauptkonten_id'];
              $filters['hauptkonten_id'] = & $r['value'];
            }
            break;
        }

      }

    }
  }

  // loop: fill less specific fields from more specific ones:
  //
  foreach( $kontodaten_fields as $fieldname ) {
    $r = & $work[ $fieldname ];
    if( $r['value'] ) {
      // debug( $r, "propagate up: propagating: $fieldname" );
      switch( $fieldname ) {
        case 'unterkonten_id':
          $uk = sql_one_unterkonto( $work['unterkonten_id']['value'] );
          $work['hauptkonten_id']['value'] = $uk['hauptkonten_id'];
          // fall-through
        case 'hauptkonten_id':
          $hk = sql_one_hauptkonto( $work['hauptkonten_id']['value'] );
          $work['geschaeftsjahr']['value'] = $hk['geschaeftsjahr'];
          $work['kontoklassen_id']['value'] = $hk['kontoklassen_id'];
          // fall-through
        case 'kontoklassen_id':
          $kontoklasse = sql_one_kontoklasse( $work['kontoklassen_id']['value'] );
          $work['seite']['value'] = $kontoklasse['seite'];
          $work['kontenkreis']['value'] = $kontoklasse['kontenkreis'];
          if( $work['kontenkreis']['value'] === 'E' && $GLOBALS['unterstuetzung_geschaeftsbereiche'] ) {
            $work['geschaeftsbereiche_id']['value'] = sql_unique_id( 'kontoklassen', 'geschaeftsbereich', $kontoklasse['geschaeftsbereich'] );
          } else {
            $work['geschaeftsbereiche_id']['value'] = 0;
          }
      }
    }
  }

  // loop 3:
  // - recheck for problems and modifications
  // - fill and return $filters array to be used in sql queries:
  //
  foreach( $kontodaten_fields as $fieldname ) {
    if( ! isset( $state[ $fieldname ] ) )
      continue;
    $r = & $state[ $fieldname ];

    $r['class'] = '';
    if( normalize( $r['value'], 'u' ) !== normalize( adefault( $r, 'old', $r['value'] ), 'u' ) ) {
      $r['modified'] = 'modified';
      $state['_changes'][ $fieldname ] = $r['value'];
      if( $flag_modified ) {
        $r['class'] = 'modified';
        // debug( $r['value'], ' modified value: '.$fieldname );
        // debug( $r['old'], ' old: '.$fieldname );
      }
    } else {
      $r['modified'] = '';
      unset( $state['_changes'][ $fieldname ] );
    }

    if( checkvalue( $r['value'], array( 'pattern' => $r['pattern'] ) ) === NULL )  {
      $r['problem'] = 'type mismatch';
      $state['_problems'][ $fieldname ] = $r['value'];
      if( $flag_problems )
        $r['class'] = 'problem';
    } else {
      $r['problem'] = '';
      unset( $state['_problems'][ $fieldname ] );
    }

    if( $r['value'] ) {
      $state['_filters'][ $fieldname ] = & $r['value'];
    } else {
      unset( $state['_filters'][ $fieldname ] );
    }
  }
  if( ! $GLOBALS['unterstuetzung_geschaeftsbereiche'] || ( ! isset( $state['kontenkreis']['value'] ) ) || ( $state['kontenkreis']['value'] !== 'E' ) ) {
    unset( $state['_problems']['geschaeftsbereiche_id'] );
  }
  // debug( $state, 'state B' );
  return $state;
}


// form_row_posten():
// display one posten in buchung.php
//
function form_row_posten( $art, $n ) {
  global $problem_summe, $geschaeftsjahr, $geschlossen;

  $p = $GLOBALS["p$art"][ $n ];

  if( $p['_problems'] ) {
  // debug( $p['_problems'], 'p problems' );
  }

  open_td( "smallskip top" );
    open_div( 'oneline' );
      if( $geschlossen ) {
        echo "{$p['kontenkreis']['value']} {$p['seite']['value']}";
      } else {
        selector_kontenkreis( $p['kontenkreis'] );
        selector_seite( $p['seite'] );
      }
    close_div();
    if( ( "{$p['kontenkreis']['value']}" == 'E' ) && $GLOBALS['unterstuetzung_geschaeftsbereiche'] ) {
      open_div( 'oneline smallskip' );
        if( $geschlossen ) {
          echo sql_unique_value( 'kontoklassen', 'geschaeftsbereich', $p['geschaeftsbereiche_id']['value'] );
        } else {
          selector_geschaeftsbereich( $p['geschaeftsbereiche_id'] );
        }
      close_div();
    }
  open_td( "smallskip top" );
    open_div( 'oneline' );
      selector_hauptkonto( $p['hauptkonten_id'], array( 'filters' => $p['_filters'] ) );
    close_div();
    if( $p['hauptkonten_id']['value'] ) {
      open_div( 'oneline', inlink( 'hauptkonto', array(
        'class' => 'href', 'hauptkonten_id' => $p['hauptkonten_id']['value'], 'text' => 'zum Hauptkonto...'
      ) ) );
    }
  open_td( "smallskip top" );
    if( $p['hauptkonten_id'] ) {
      open_div( 'oneline' );
        selector_unterkonto( $p['unterkonten_id'], array( 'filters' => $p['_filters'] ) );
      close_div();
      if( $p['unterkonten_id']['value'] ) {
        open_div( 'oneline', inlink( 'unterkonto', array(
          'class' => 'href', 'unterkonten_id' => $p['unterkonten_id']['value'], 'text' => 'zum Unterkonto...'
        ) ) );
      }
    }
  open_td( 'smallskip bottom oneline', string_element( $p['beleg'] ) );
  open_td( "smallskip bottom oneline $problem_summe", price_element( $p['betrag'] ) );
}


?>
