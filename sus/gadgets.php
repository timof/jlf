<?php


// functions for drop-down selectors; we usually provide a triple of functions:
// - options_X( $filters = array(), $option_0 = false )
//     returns an array of <id> => <option> pairs
//     $option_0: additional option to be offered, with id === 0 (typical use: option "(all)")
//     the returned array should contain an entry with index '' (empty string), to be displayed
//     if no option is currently selected (or no options are available at all)
// - selector_X( $field, $selected = 0, $filters = array(), $option_0 = false )
//     create drop-down selection gadget
// - filter_X( $field = '', $filters = array(), $option_0 = 'all' )
//     create drop-down selection gadget to select filter variable $prefixX
//     other global filters of the form $prefix may be appended to $filters
//     $prefixX will be initialized from keep,http,persistent,default
// the $filters argument will be missing in cases where filtering is not useful, in particular
// when only a binary choice is possible anyway.

function options_people( $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( sql_people( $filters ) as $p ) {
    $id = $p['people_id'];
    $options[$id] = $p['cn'];
  }
  $options[''] = $options ? ' - Person w'.H_AMP.'auml;hlen - ' : '(keine Personen vorhanden)';
  return $options;
}

function selector_people( $field = NULL, $selected = NULL, $filters = array(), $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'people_id' );
  $options = options_people( $filters, $option_0 );
  dropdown_select( $field, $options, $selected );
}

function filter_person( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  $r = init_var( $prefix.'people_id', 'global,pattern=u,sources=keep http persistent,default=0,set_scopes=self' );
  selector_people( $prefix.'people_id', NULL, $filters, $option_0 );
}


function options_jperson( $option_0 = false ) {
  $options[''] = ' - Personenart w'.H_AMP.'auml;hlen - ';
  if( $option_0 )
    $options['0'] = $option_0;
  $options['0'] = 'nat'.H_AMP.'uuml;rlich';
  $options['1'] = 'juristisch';
  return $options;
}

function selector_jperson( $field = NULL, $selected = NULL, $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'jperson' );
  $options = options_jperson( $option_0 );
  dropdown_select( $field, $options, $selected );
}

function filter_jperson( $prefix = '', $option_0 = '(beide)' ) {
  $r = init_var( $prefix.'jperson', 'global,pattern=b,sources=keep http persistent,default=0,set_scopes=self' );
  selector_jperson( $r, NULL, $option_0 );
}


function options_kontenkreis( $option_0 = false ) {
  $options[''] = ' - Kontenkreis w'.H_AMP.'auml;hlen - ';
  if( $option_0 )
    $options['0'] = $option_0;
  $options['B'] = 'Bestand';
  $options['E'] = 'Erfolg';
  return $options;
}

function selector_kontenkreis( $field = NULL, $selected = NULL, $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'kontenkreis' );
  $options = options_kontenkreis( $option_0 );
  dropdown_select( $field, $options, $selected );
}

function filter_kontenkreis( $prefix = '', $option_0 = '(beide)' ) {
  $r = init_var( $prefix.'kontenkreis', 'global,pattern=/^[0BE]$/,sources=keep http persistent,default=0,set_scopes=self' );
  selector_kontenkreis( $r, NULL, $option_0 );
}


function options_seite( $option_0 = false ) {
  $options[''] = ' - Seite w'.H_AMP.'auml;hlen - ';
  if( $option_0 )
    $options['0'] = $option_0;
  $options['A'] = 'Aktiv';
  $options['P'] = 'Passiv';
  return $options;
}

function selector_seite( $field = NULL, $selected = NULL, $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'seite' );
  $options = options_seite( $option_0 );
  dropdown_select( $field, $options, $selected );
}

function filter_seite( $prefix = '', $option_0 = '(beide)' ) {
  $r = init_var( $prefix.'seite', 'global,pattern=/^[0AP]$/,sources=keep http persistent,default=0,set_scopes=self' );
  selector_seite( $r, NULL, $option_0 );
}


function options_geschaeftsbereiche( $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  $options += sql_unique_values( 'kontoklassen', 'geschaeftsbereich' );
  foreach( $options as $k => $v ) {
    if( ! $v )
      unset( $options[$k] );
  }
  $options[''] = $options ? ' - Gesch'.H_AMP.'auml;ftsbereich w'.H_AMP.'auml;hlen - ' : '(keine Gesch'.H_AMP.'auml;ftsbereiche vorhanden)';
  return $options;
}

function selector_geschaeftsbereich( $field = NULL, $selected = NULL, $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'geschaeftsbereiche_id' );
  $options = options_geschaeftsbereiche( $option_0 );
  dropdown_select( $field, $options, $selected );
}

function filter_geschaeftsbereich( $prefix = '', $option_0 = '(alle)' ) {
  $r = init_var( $prefix.'geschaeftsbereiche_id', 'global,pattern=x,sources=keep http persistent,default=0,set_scopes=self' );
  selector_geschaeftsbereich( $r, NULL, $option_0 );
}


function options_kontoklassen( $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  foreach( sql_kontoklassen( $filters ) as $k ) {
    $id = $k['kontoklassen_id'];
    $options[$id] = "$id {$k['kontenkreis']} {$k['seite']} {$k['cn']}";
    if( $k['geschaeftsbereich'] )
      $options[$id] .= " / " . $k['geschaeftsbereich'];
  }
  $options[''] = $options ? ' - Kontoklasse w'.H_AMP.'auml;hlen - ' : '(keine Kontoklassen vorhanden)';
  return $options;
}

function selector_kontoklasse( $field = NULL, $selected = NULL, $filters = array(), $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'kontoklassen_id' );
  $options = options_kontoklassen( $filters, $option_0 );
  dropdown_select( $field, $options, $selected );
}

function filter_kontoklasse( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  $filters = parameters_explode( $filters );
  foreach( adefault( $GLOBALS, $prefix.'filters', array() ) as $k => $v ) {
    if( in_array( $k, array( 'seite', 'kontenkreis', 'geschaeftsbereiche_id' ) ) )
      $filters[ $k ] = $v;
  }
  $r = init_global_var( $prefix.'kontoklassen_id', 'global,pattern=u,sources=keep http persistent,default=0,set_scopes=self' );
  selector_kontoklasse( $r, NULL, $filters, $option_0 );
}

function options_hgb_klassen( $kontenkreis = '', $seite = '', $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options[ 0 ] = $option_0;
  foreach( $GLOBALS['hgb_klassen'] as $i => $k ) {
    if( $kontenkreis && ( substr( $i, 0, 1 ) !== $kontenkreis ) )
      continue;
    if( $seite && ( substr( $i, 2, 1 ) !== $seite ) )
      continue;
    if( adefault( $k, 'zwischensumme', false ) )
      continue;
    $options[ $i ] = "[$i] ${k['rubrik']}";
    if( adefault( $k, 'titel', '' ) )
      $options[ $i ] .= "/ ${k['titel']}";
    if( adefault( $k, 'subtitel', '' ) )
      $options[ $i ] .= "/ ${k['subtitel']}";
  }
  $options[''] = $options ? ' -'.H_AMP.'nbsp;HGB-Klasse w'.H_AMP.'auml;hlen'.H_AMP.'nbsp;- ' : '(keine HGB-Klassen vorhanden)';
  return $options;
}

function selector_hgb_klasse( $field = NULL, $selected = NULL, $kontenkreis, $seite, $option_0 = '(keine)' ) {
  if( ! $field )
    $field = array( 'name' => 'hgb_klasse' );
  $options = options_hgb_klassen( $kontenkreis, $seite, $option_0 );
  dropdown_select( $field, $options, $selected );
}

function filter_hgb_klasse( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  $filters = parameters_explode( $filters );
  foreach( adefault( $GLOBALS, $prefix.'filters', array() ) as $k => $v ) {
    if( in_array( $k, array( 'seite', 'kontenkreis' ) ) )
      $filters[ $k ] = $v;
  }
  $seite = adefault( $filters, 'seite', '' );
  $kontenkreis = adefault( $filters, 'kontenkreis', '' );
  $r = init_var( $prefix.'hgb_klasse', 'global,sources=keep http persistent,default=0,set_scopes=self' );
  selector_hgb_klasse( $r, NULL, $kontenkreis, $seite, $option_0 );
}


function options_hauptkonten( $filters = array() ) {
  $options = array();
  // fixme: add global filters?
  foreach( sql_hauptkonten( $filters ) as $k ) {
    $id = $k['hauptkonten_id'];
    $options[ $id ] = "{$k['kontenkreis']} {$k['seite']} {$k['rubrik']} : {$k['titel']}";
    if( $GLOBALS['unterstuetzung_geschaeftsbereiche'] && $k['geschaeftsbereich'] ) {
      if( ! adefault( $filters, 'geschaeftsbereiche_id', 0 ) )
        $options[ $id ] .= " / ".$k['geschaeftsbereich'];
    }
  }
  $options[''] = $options ? ' -'.H_AMP.'nbsp;Hauptkonto w'.H_AMP.'auml;hlen'.H_AMP.'nbsp;- ' : '(keine Hauptkonten vorhanden)';
  return $options;
}

function selector_hauptkonto( $field = NULL, $selected = NULL, $filters = array(), $more_options = array() ) {
  if( ! $field )
    $field = array( 'name' => 'hauptkonten_id' );
  $options = $more_options + options_hauptkonten( $filters );
  dropdown_select( $field, $options, $selected );
}

function filter_hauptkonto( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  $filters = parameters_explode( $filters );
  foreach( adefault( $GLOBALS, $prefix.'filters', array() ) as $k => $v ) {
    if( in_array( $k, array( 'seite', 'kontenkreis', 'geschaeftsbereiche_id', 'kontoklassen_id', 'geschaeftsjahr' ) ) )
      $filters[ $k ] = $v;
  }
  $r = init_global_var( $prefix.'hauptkonten_id', 'global,pattern=u,sources=keep http persistent,default=0,set_scopes=self' );
  $option_0 = ( $option_0 ? array( 0 => $option_0 ) : array() );
  selector_hauptkonto( $r, NULL, $filters, $option_0 );
}


function options_unterkonten( $filters = array() ) {
  $options = array();
  foreach( sql_unterkonten( $filters, 'cn' ) as $k ) {
    $options[ $k['unterkonten_id'] ] = $k['cn'];
  }
  $options[''] = $options ? ' -'.H_AMP.'nbsp;Unterkonto w'.H_AMP.'auml;hlen'.H_AMP.'nbsp;- ' : '(keine Unterkonten vorhanden)';
  return $options;
}

function selector_unterkonto( $field = NULL, $selected = NULL, $filters = array(), $more_options = array() ) {
  if( ! $field )
    $field = array( 'name' => 'unterkonten_id' );
  $options = $more_options + options_unterkonten( $filters );
  dropdown_select( $field, $options, $selected );
}

function filter_unterkonto( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  $filters = parameters_explode( $filters );
  foreach( adefault( $GLOBALS, $prefix.'filters', array() ) as $k => $v ) {
    if( in_array( $k, array( 'seite', 'kontenkreis', 'geschaeftsbereiche_id', 'kontoklassen_id', 'geschaeftsjahr', 'hauptkonten_id' ) ) )
      $filters[ $k ] = $v;
  }
  $r = init_global_var( $prefix.'unterkonten_id', 'global,pattern=u,sources=keep http persistent,default=0,set_scopes=self' );
  $option_0 = ( $option_0 ? array( 0 => $option_0 ) : array() );
  selector_unterkonto( $r, NULL, $filters, $option_0 );
}


function options_rubriken( $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options[ 0 ] = $option_0;
  foreach( sql_rubriken( $filters ) as $r ) {
    $options[ $r['rubriken_id'] ] = $r['rubrik'];
  }
  $options[''] = $options ? ' - Rubrik w'.H_AMP.'auml;hlen - ' : '(keine Rubriken vorhanden)';
  return $options;
}

function selector_rubrik( $field = NULL, $selected = NULL, $filters = array(), $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'rubriken_id' );
  $options = options_rubriken( $filters, $option_0 );
  dropdown_select( $field, $options, $selected );
}

function filter_rubrik( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  $filters = parameters_explode( $filters );
  foreach( adefault( $GLOBALS, $prefix.'filters', array() ) as $k => $v ) {
    if( in_array( $k, array( 'seite', 'kontenkreis', 'geschaeftsbereiche_id', 'kontoklassen_id', 'geschaeftsjahr' ) ) )
      $filters[ $k ] = $v;
  }
  $r = init_global_var( $prefix.'rubriken_id', 'global,pattern=u,sources=keep http persistent,default=0,set_scopes=self' );
  selector_rubrik( $r, NULL, $filters, $option_0 );
}


function options_titel( $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options[0] = $option_0;
  foreach( sql_titel( $filters ) as $t ) {
    $options[ $t['titel_id'] ] = $t['titel'];
  }
  $options[''] = $options ? ' - Titel w'.H_AMP.'auml;hlen - ' : '(keine Titel vorhanden)';
  return $options;
}

function selector_titel( $field = NULL, $selected = NULL, $filters = array(), $option_0 = false ) {
  $options = options_titel( $filters, $option_0 );
  dropdown_select( $field, $options, $selected );
}

function filter_titel( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  $filters = parameters_explode( $filters );
  foreach( adefault( $GLOBALS, $prefix.'filters', array() ) as $k => $v ) {
    if( in_array( $k, array( 'seite', 'kontenkreis', 'geschaeftsbereiche_id', 'kontoklassen_id', 'geschaeftsjahr', 'rubriken_id' ) ) )
      $filters[ $k ] = $v;
  }
  $r = init_var( $prefix.'titel_id', 'global,pattern=u,sources=keep http persistent,default=0,set_scopes=self' );
  selector_rubrik( $r, NULL, $filters, $option_0 );
}


function options_things( $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options[0] = $option_0;
  foreach( sql_things( $filters ) as $thing ) {
    $options[ $thing['things_id'] ] = $thing['cn'];
    if( $thing['anschaffungsjahr'] )
      $options[ $thing['things_id'] ] .= " ({$thing['anschaffungsjahr']}) ";
  }
  $options[''] = $options ? ' - Gegenstand w'.H_AMP.'auml;hlen - ' : '(keine Gegenst'.H_AMP.'auml;nde vorhanden)';
  return $options;
}

function selector_thing( $field = NULL, $selected = NULL, $filters = array(), $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'things_id' );
  $options = options_things( $filters, $option_0 );
  dropdown_select( $field, $options, $selected );
}

function filter_thing( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  $r = init_var( $prefix.'things_id', 'global,pattern=u,sources=keep http persistent,default=0,set_scopes=self' );
  selector_thing( $r, NULL, $filters, $option_0 );
}


function options_anschaffungsjahr( $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options[0] = $option_0;
  foreach( sql_unique_values( 'things', 'anschaffungsjahr' ) as $r ) {
    $j = $r['anschaffungsjahr'];
    $option[ $j ] = $j;
  }
  $options[''] = $options ? ' - Anschaffungsjahr w'.H_AMP.'auml;hlen - ' : '(keine Jahre vorhanden)';
  return $options;
}

function selector_anschaffungsjahr( $field = NULL, $selected = NULL, $option_0 = false ) {
  if( ! $field )
    $field = array( 'name' => 'anschaffungsjahr' );
  $options = options_anschaffungsjahr( $option_0 );
  dropdown_select( $field, $options, $selected );
}

function filter_anschaffungsjahr( $prefix = '', $option_0 = '(alle)' ) {
  $r = init_var( $prefix.'anschaffungsjahr', 'global,pattern=u,sources=keep http persistent,default=0,set_scopes=self' );
  selector_anschaffungsjahr( $r, NULL, $option_0 );
}


function options_geschaeftsjahre( $selected = 0, $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options[ 0 ] = $option_0;
  for( $j = $GLOBALS['geschaeftsjahr_min']; $j <= $GLOBALS['geschaeftsjahr_max']; $j++ )
    $options[ $j ] = $j;
  $options[''] = $options ? ' - Gesch'.H_AMP.'auml;ftsjahr w'.H_AMP.'auml;hlen - ' : '(keine Jahre vorhanden)';
  return $options;
}

function selector_geschaeftsjahr( $field = NULL, $selected = NULL, $option_0 = false ) {
  global $current_form, $geschaeftsjahr_current, $geschaeftsjahr_min, $geschaeftsjahr_max;

  if( ! $field )
    $field = array( 'name' => 'geschaeftsjahr' );
  if( $selected === NULL )
    $selected = adefault( $field, 'value', 0 );

  $form_id = ( $current_form ? $current_form['id'] : NULL );
  if( $selected !== NULL ) {
    $g = $selected;
  } else {
    $g = adefault( $field, 'value', 0 );
  }

  if( ! $g && ! $option_0 ) {
    $g = $geschaeftsjahr_current;
  }
  if( $g ) {
    $g = max( min( $g, $geschaeftsjahr_max ), $geschaeftsjahr_min );
  }

  if( $g ) {
    selector_int( $field, $g, $geschaeftsjahr_min, $geschaeftsjahr_max );
    if( $option_0 ) {
      open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$option_0", $fieldname => 0 ) ) );
    }
  } else {
    open_span( 'quads', ' (alle) ' );
    open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $fieldname => $geschaeftsjahr_current ) ) );
  }
}

function filter_geschaeftsjahr( $prefix = '', $option_0 = '(alle)' ) {
  $r = init_var( $prefix.'geschaeftsjahr', 'pattern=u,sources=keep http persistent,default=0,set_scopes=self' );
  selector_geschaeftsjahr( $r, NULL, $option_0 );
}


function selector_stichtag( $field = NULL, $selected = NULL ) {
  global $current_form;

  if( ! $field )
    $field = array( 'name' => 'stichtag' );
  if( $selected === NULL )
    $selected = adefault( $field, 'value', 0 );

  $stichtag = $selected;

  if( ! $stichtag ) {
    $stichtag = 1231;
  }
  $stichtag = max( min( $stichtag, 1231 ), 100 );

  $p = array(
    'class' => 'button'
  , 'text' => 'Vortrag < '
  , 'inactive' => ( $stichtag <= 100 )
  , 'form_id' => $current_form['id']
  , $fieldname => 100
  );
  echo inlink( '', $p );
  $field['size'] = 4;
  $field['value'] = $stichtag;
  echo int_element( $field );
  $p['text'] = ' > Ultimo';
  $p['inactive'] = ( $stichtag >= 1231 );
  $p[ $fieldname ] = 1231;
  echo inlink( '', $p );
}

function filter_stichtag( $prefix = '' ) {
  $r = init_var( $prefix.'stichtag', 'global,pattern=u,sources=keep http persistent,default=0,set_scopes=self' );
  selector_stichtag( $r );
}


// filters_kontodaten_prepare:
// $fields: the list of filters to actually use. order matters here: must be sorted from least to most specific!
//
function filters_kontodaten_prepare( $prefix = '', $fields = true, $auto_select_unique = false ) {
  global $cgi_vars;

  $all_fields = array( 'seite', 'kontenkreis', 'geschaeftsbereiche_id', 'kontoklassen_id', 'geschaeftsjahr', 'hauptkonten_id', 'unterkonten_id' );
  if( $fields === true )
    $fields = $all_fields;

  // init globals and bind local references (for convenience):
  //
  foreach( $fields as $field ) {
    init_var( $prefix.$field, 'global,sources=http persistent keep,default=0,set_scopes=self' );
    $$field = & $GLOBALS[ $prefix.$field ];
    // debug( $$field, "$prefix: got: $field" );
  }

  $filters = array();
  foreach( $fields as $field ) {
    // debug( $field, 'handling field:' );
    // debug( $filters, 'current filters:' );
    $pattern = jlf_get_pattern( $field );

    if( get_http_var( $prefix.$field, $pattern ) !== NULL ) {
      // prettydump( $$field, "$prefix: from http: $field:" );
      if( $$field ) {
        $filters[ $field ] = & $$field;
      } else {
        // $$field was reset - reset more specific fields too:
        switch( $field ) {
          case 'geschaeftsbereiche_id':
            if( $kontenkreis !== 'E' ) {
              break;
            }
          case 'seite':
          case 'kontenkreis':
            $kontoklassen_id = 0;
          case 'kontoklassen_id':
            $hauptkonten_id = 0;
          case 'hauptkonten_id':
            $unterkonten_id = 0;
        }
      }
    } else { /* not passed via http */

      if( $$field ) {

        $filters[ $field ] = & $$field;
        // value not from http - check and drop setting if inconsistent:
        switch( $field ) {
          case 'unterkonten_id':
            if( ! sql_unterkonten( $filters ) ) {
              $unterkonten_id = 0;
              unset( $filters['unterkonten_id'] );
            }
            break;
          case 'hauptkonten_id':
            if( ! sql_hauptkonten( $filters ) ) {
              $hauptkonten_id = 0;
              unset( $filters['hauptkonten_id'] );
            }
            break;
          case 'geschaeftsbereiche_id':
            if( "$kontenkreis" === 'B' ) {
              $geschaeftsbereiche_id = 0;
              unset( $filters['geschaeftsbereiche_id'] );
            }
            break;
          case 'kontoklassen_id':
            if( ! sql_kontoklassen( $filters ) ) {
              $kontoklassen_id = 0;
              unset( $filters['kontoklassen_id'] );
            }
        }
      } if( $auto_select_unique ) {

        switch( $field ) {
          case 'unterkonten_id':
            $uk = sql_unterkonten( $filters );
            if( count( $uk ) == 1 ) {
              $unterkonten_id = $uk[ 0 ]['unterkonten_id'];
              $filters['unterkonten_id'] = & $unterkonten_id;
            }
            break;
          case 'hauptkonten_id':
            $hk = sql_hauptkonten( $filters );
            if( count( $hk ) == 1 ) {
              $hauptkonten_id = $hk[ 0 ]['hauptkonten_id'];
              $filters['hauptkonten_id'] = & $hauptkonten_id;
            }
            break;
        }

      }

    }
  }

  foreach( $fields as $field ) {
    if( in_array( $field, $fields ) && $$field ) {
      // propagate up: set less specific fields too:
      switch( $field ) {
        case 'unterkonten_id':
          $uk = sql_one_unterkonto( $unterkonten_id );
          $hauptkonten_id = $uk['hauptkonten_id'];
          // fall-through
        case 'hauptkonten_id':
          $hk = sql_one_hauptkonto( $hauptkonten_id );
          $kontoklassen_id = $hk['kontoklassen_id'];
          $geschaeftsjahr = $hk['geschaeftsjahr'];
          // fall-through
        case 'kontoklassen_id':
          $kontoklasse = sql_one_kontoklasse( $kontoklassen_id );
          $seite = $kontoklasse['seite'];
          $kontenkreis = $kontoklasse['kontenkreis'];
          if( $kontenkreis === 'E' ) {
            $geschaeftsbereiche_id = sql_unique_id( 'kontoklassen', 'geschaeftsbereich', $kontoklasse['geschaeftsbereich'] );
          } else {
            $geschaeftsbereiche_id = 0;
          }
      }
    }
  }

  // fill and return $filters array to be used in sql queries:
  $filters = array();
  foreach( $fields as $field ) {
    if( in_array( $field, $fields ) && $$field ) {
      $filters[ $field ] = & $$field;
    } else {
      unset( $filters[ $field ] );
    }
  }
  return $filters;
}

?>
