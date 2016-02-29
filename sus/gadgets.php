<?php

require_once('code/gadgets.php');

// functions for drop-down selectors; we usually provide a triple of functions:
// - choices_X( $filters = array() )
//     returns an array of <id> => <option> pairs matching $filters
// - selector_X( $field, $opts )
//     create drop-down selection gadget
//     $opts may contain
//      'filters': filter (array or string) to narrow selection
//      'choices': array of additional 'key' => 'text' pairs to also offer for selection
// - filter_X( $field, $opts = array() )
//     create drop-down selection gadget for filtering; $opts may contain
//       'filters': to narrow selection
//       'choice_0': extra choice with value '0' (default: '(all)'; set to NULL to offer no choice_0)



function selector_groessenklasse( $field = NULL, $opts = array() ) {
  global $choices_groessenklasse;
  if( ! $field ) {
    $field = array( 'name' => 'groessenklasse' );
  }
  $opts = parameters_explode( $opts );
  $field += array( 'choices' => $choices_groessenklasse , 'default_display' => ' - Groessenklasse waehlen - ' );
  return select_element( $field );
}

function filter_groessenklasse( $field, $opts = array() ) {
  return selector_groessenklasse( $field, add_filter_default( $opts, $field ) );
}


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
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_people( adefault( $opts, 'filters', array() ) )
  , 'empty_display' => '(keine Personen vorhanden)'
  , 'default_display' => ' - Person w'.H_AMP.'auml;hlen - '
  );
  return select_element( $field );
}

function filter_person( $field, $opts = array() ) {
  return selector_people( $field, add_filter_default( $opts, $field ) );
}


function selector_status_person( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'status_person' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['choices_status_person']
  , 'default_display' => ' - Status w'.H_AMP.'auml;hlen - '
  );
  return select_element( $field );
}

function filter_status_person( $field, $opts = array() ) {
  return selector_status_person( $field, add_filter_default( $opts, $field ) );
}


function selector_jperson( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'jperson' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + array( 'N' => 'nat'.H_AMP.'uuml;rlich', 'J' => 'juristisch' )
  , 'default_display' => ' - Personenart w'.H_AMP.'auml;hlen - '
  );
  return select_element( $field );
}

function filter_jperson( $field, $opts = array() ) {
  return selector_jperson( $field, add_filter_default( $opts, $field ) );
}

function selector_ust_satz( $field = NULL, $opts = array() ) {
  global $aUML, $SZLIG, $ust_satz_1_prozent, $ust_satz_2_prozent;
  if( ! $field ) {
    $field = array( 'name' => 'just_satz' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + array(
      '0' => "0: keine Umsatzsteuer"
    , '1' => "1: Umsatzsteuer regul{$aUML}rer Satz ($ust_satz_1_prozent%)"
    , '2' => "2: Umsatzsteuer erm{$aUML}{$SZLIG}igter Satz ($ust_satz_2_prozent%)"
    )
  , 'default_display' => ' - Personenart w'.H_AMP.'auml;hlen - '
  );
  return select_element( $field, 'max_cols=60' );
}

function filter_ust_satz( $field, $opts = array() ) {
  return selector_ust_satz( $field, add_filter_default( $opts, $field ) );
}

function selector_dusie( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'dusie' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + array( 'S' => 'Siezen', 'D' => 'Duzen' )
  , 'default_display' => ' - Anredeart w'.H_AMP.'auml;hlen - '
  );
  return select_element( $field );
}

function filter_dusie( $field, $opts = array() ) {
  return selector_dusie( $field, add_filter_default( $opts, $field ) );
}


function selector_genus( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'genus' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + array( 'N' => 'ne-utrum', 'M' => 'maskulin', 'F' => 'feminin' )
  , 'default_display' => ' - Genus w'.H_AMP.'auml;hlen - '
  );
  return select_element( $field );
}

function filter_genus( $field, $opts = array() ) {
  return selector_genus( $field, add_filter_default( $opts, $field ) );
}


function selector_kontenkreis( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'kontenkreis' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + array( 'B' => 'Bestand', 'E' => 'Erfolg' )
  , 'default_display' => ' - Kontenkreis w'.H_AMP.'auml;hlen - '
  );
  return select_element( $field );
}

function filter_kontenkreis( $field, $opts = array() ) {
  return selector_kontenkreis( $field, add_filter_default( $opts, $field ) );
}


function selector_seite( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'seite' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + array( 'A' => 'Aktiv', 'P' => 'Passiv' )
  , 'default_display' => ' - Seite w'.H_AMP.'auml;hlen - '
  );
  return select_element( $field );
}

function filter_seite( $field, $opts = array() ) {
  return selector_seite( $field, add_filter_default( $opts, $field ) );
}


function uid_choices_geschaeftsbereiche( $kreis = '' ) {
  static $choices = array();
  if( ! isset( $choices[ $kreis ] ) ) {
    $filters = ( $kreis ? "kontenkreis=$kreis" : true );
    $choices[ $kreis ] = sql_kontoklassen( $filters, 'distinct=geschaeftsbereich' );
  }
  return $choices[ $kreis ];
}

function selector_geschaeftsbereich( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'geschaeftsbereich', 'cgi_name' => 'UID_geschaeftsbereich' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'uid_choices' => adefault( $opts, 'uid_choices', array() ) + uid_choices_geschaeftsbereiche( adefault( $opts, 'kreis', '' ) )
  , 'choices' => adefault( $opts, 'choices', array() )
  , 'default_display' => ' - Gesch'.H_AMP.'auml;ftsbereich w'.H_AMP.'auml;hlen - '
  , 'empty_display' => '(keine Gesch'.H_AMP.'auml;ftsbereiche vorhanden)'
  , 'keyformat' => 'uid_choice'
  );
  return select_element( $field );
}

function filter_geschaeftsbereich( $field, $opts =array() ) {
  return selector_geschaeftsbereich( $field, add_filter_default( $opts, $field ) );
}


function choices_kontoklassen( $filters = array() ) {
  $choices = array();
  foreach( sql_kontoklassen( $filters ) as $k ) {
    $id = $k['kontoklassen_id'];
    $choices[ $id ] = "$id {$k['kontenkreis']} {$k['seite']} {$k['cn']}";
    if( $k['geschaeftsbereich'] ) {
      $choices[ $id ] .= " / " . $k['geschaeftsbereich'];
    }
  }
  return $choices;
}

function selector_kontoklasse( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'kontoklassen_id' );
  }
  $opts = parameters_explode( $opts );
  $filters = parameters_explode( adefault( $opts, 'filters', array() ), array( 'keep' => 'seite,kontenkreis,geschaeftsbereich' ) );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_kontoklassen( $filters )
  , 'default_display' => ' - Kontoklasse w'.H_AMP.'auml;hlen - '
  , 'empty_display' => '(keine Kontoklassen vorhanden)'
  );
  return select_element( $field, 'max_cols=60' );
}

function filter_kontoklasse( $field, $opts = array() ) {
  return selector_kontoklasse( $field, add_filter_default( $opts, $field ) );
}

function choices_hgb_klassen( $kontenkreis = '', $seite = '' ) {
  $choices = array();
  foreach( $GLOBALS['hgb_klassen'] as $i => $k ) {
    if( $kontenkreis && ( substr( $i, 0, 1 ) !== $kontenkreis ) ) {
      continue;
    }
    if( $seite && ( substr( $i, 2, 1 ) !== $seite ) ) {
      continue;
    }
    if( adefault( $k, 'zwischensumme', false ) ) {
      continue;
    }
    $choices[ $i ] = "[$i] ${k['rubrik']}";
    if( adefault( $k, 'titel', '' ) ) {
      $choices[ $i ] .= "/ ${k['titel']}";
    }
    if( adefault( $k, 'subtitel', '' ) ) {
      $choices[ $i ] .= "/ ${k['subtitel']}";
    }
  }
  return $choices;
}

function selector_hgb_klasse( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'hgb_klasse' );
  }
  $opts = parameters_explode( $opts, array( 'keep' => 'filters=,choices' ) );
  $filters = parameters_explode( $opts['filters'], array( 'keep' => 'seite=,kontenkreis=' ) );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_hgb_klassen( $filters['kontenkreis'], $filters['seite'] )
  , 'default_display' => ' -'.H_AMP.'nbsp;HGB-Klasse w'.H_AMP.'auml;hlen'.H_AMP.'nbsp;- '
  , 'empty_display' => '(keine HGB-Klassen vorhanden)'
  );
  return select_element( $field, 'max_cols=90' );
}

function filter_hgb_klasse( $field, $opts = array() ) {
  return selector_hgb_klasse( $field, add_filter_default( $opts, $field ) );
}


function choices_hauptkonten( $filters = array() ) {
  $choices = array();
  foreach( sql_hauptkonten( $filters ) as $k ) {
    $id = $k['hauptkonten_id'];
    $choices[ $id ] = "{$k['kontenkreis']} {$k['seite']} {$k['rubrik']} : {$k['titel']}";
    if( $GLOBALS['unterstuetzung_geschaeftsbereiche'] && $k['geschaeftsbereich'] ) {
      if( ! adefault( $filters, 'geschaeftsbereich', 0 ) ) {
        $choices[ $id ] .= " / ".$k['geschaeftsbereich'];
      }
    }
  }
  return $choices;
}

function selector_hauptkonto( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'hauptkonten_id' );
  }
  $opts = parameters_explode( $opts, array( 'keep' => 'filters=,choices' ) );
  $filters = parameters_explode( $opts['filters'], array( 'keep' =>
    'flag_hauptkonto_offen=1,flag_personenkonto,flag_sachkonto,flag_bankkonto,vortragskonto,seite,kontenkreis,geschaeftsbereich,kontoklassen_id'
  ) );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_hauptkonten( $filters )
  , 'default_display' =>  ' -'.H_AMP.'nbsp;Hauptkonto w'.H_AMP.'auml;hlen'.H_AMP.'nbsp;- '
  , 'empty_display' => '(keine Hauptkonten vorhanden)'
  );
  return select_element( $field, 'max_cols=60' );
}

function filter_hauptkonto( $field, $opts = array() ) {
  return selector_hauptkonto( $field, add_filter_default( $opts, $field ) );
}


function choices_unterkonten( $filters = array() ) {
  $choices = array();
  foreach( sql_unterkonten( $filters, 'orderby=cn' ) as $k ) {
    $choices[ $k['unterkonten_id'] ] = $k['cn'];
  }
  return $choices;
}

function selector_unterkonto( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'unterkonten_id' );
  }
  $opts = parameters_explode( $opts, array( 'keep' => 'filters=,choices' ) );
  $filters = parameters_explode( $opts['filters'], array( 'keep' =>
    'flag_unterkonto_offen=1,people_id,flag_zinskonto,flag_personenkonto,flag_sachkonto,flag_bankkonto,vortragskonto'
    . ',hauptkonten_id,seite,kontenkreis,geschaeftsbereich,kontoklassen_id,ust_satz'
  ) );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_unterkonten( $filters )
  , 'default_display' => ' -'.H_AMP.'nbsp;Unterkonto w'.H_AMP.'auml;hlen'.H_AMP.'nbsp;- '
  , 'empty_display' => '(keine Unterkonten vorhanden)'
  );
  return select_element( $field, 'max_cols=60' );
}

function filter_unterkonto( $field, $opts = array() ) {
  return selector_unterkonto( $field, add_filter_default( $opts, $field ) );
}


function uid_choices_rubriken( $filters = array() ) {
  return sql_hauptkonten( $filters, array( 'orderby' => 'rubrik', 'distinct' => 'rubrik' ) );
}

function selector_rubrik( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'rubriken_id' );
  }
  $opts = parameters_explode( $opts, array( 'keep' => 'filters=,uid_choices,choices' ) );
  $filters = parameters_explode( $opts['filters'], array( 'keep' => 'seite,kontenkreis,geschaeftsbereich,kontoklassen_id,geschaeftsjahr' ) );
  $field += array(
    'uid_choices' => adefault( $opts, 'uid_choices', array() ) + uid_choices_rubriken( $filters )
  , 'choices' => adefault( $opts, 'choices', array() )
  , 'default_display' => ' - Rubrik w'.H_AMP.'auml;hlen - '
  , 'empty_display' => '(keine Rubriken vorhanden)'
  );
  return select_element( $field );
}

function filter_rubrik( $field, $opts = array() ) {
  return selector_rubrik( $field, add_filter_default( $opts, $field ) );
}


function uid_choices_titel( $filters = array() ) {
  return sql_hauptkonten( $filters, array( 'orderby' => 'titel', 'distinct' => 'titel' ) );
}

function selector_titel( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'titel_id' );
  }
  $opts = parameters_explode( $opts, array( 'keep' => 'filters=,uid_choices,choices' ) );
  $filters = parameters_explode( $opts['filters'], array( 'keep' => 'seite,kontenkreis,geschaeftsbereich,kontoklassen_id,geschaeftsjahr,rubrik' ) );
  $field += array(
    'uid_choices' => adefault( $opts, 'uid_choices', array() ) + uid_choices_titel( $filters )
  , 'choices' => adefault( $opts, 'choices', array() )
  , 'default_display' => ' - Titel w'.H_AMP.'auml;hlen - '
  , 'empty_display' => '(keine Titel vorhanden)'
  );
  return select_element( $field, 'max_cols=60' );
}

function filter_titel( $field, $opts = array() ) {
  return selector_titel( $field, add_filter_default( $opts, $field ) );
}


function choices_anschaffungsjahr( $filters = array() ) {
  $choices = array();
  foreach( sql_unterkonten( array( '&&', 'flag_sachkonto', $filters ), 'distinct=anschaffungsjahr' ) as $r ) {
    $j = $r['anschaffungsjahr'];
    $choices[ $j ] = $j;
  }
  return $choices;
}

function selector_anschaffungsjahr( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'anschaffungsjahr' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + choices_anschaffungsjahr( adefault( $opts, 'filters', '' ) )
  , 'default_display' => ' - Anschaffungsjahr w'.H_AMP.'auml;hlen - '
  , 'empty_display' => '(keine Jahre vorhanden)'
  );
  return select_element( $field );
}

function filter_anschaffungsjahr( $field, $opts = array() ) {
  return selector_anschaffungsjahr( $field, add_filter_default( $opts, $field ) );
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

function selector_geschaeftsjahr( $field = NULL ) {
  global $geschaeftsjahr_current, $geschaeftsjahr_min, $geschaeftsjahr_max;

  if( ! $field ) {
    $field = array( 'name' => 'geschaeftsjahr' );
  }

  $g = adefault( $field, 'value', $geschaeftsjahr_current );

  if( $g ) {
    $g = max( min( $g, $geschaeftsjahr_max ), $geschaeftsjahr_min );
  }
  $field['min'] = adefault( $field, 'min', $geschaeftsjahr_min );
  $field['max'] = adefault( $field, 'max', $geschaeftsjahr_max + 99 );

  $choice_0 = adefault( $field, 'choice_0', '' );
  $priority = adefault( $field, 'priority', 1 );
  // debug( $choice_0, 'choice_0' );
  if( $g || ! $choice_0 ) {
    $s = selector_int( $field );
    $priority++;
    if( $choice_0 ) {
      $s .= html_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", "P{$priority}_{$field['name']}" => 0 ) ) );
    }
  } else {
    $s = html_span( 'quads', $choice_0 );
    $s .= html_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => $geschaeftsjahr_current ) ) );
  }
  return $s;
}

function filter_geschaeftsjahr( $field ) {
  $field['choice_0'] = adefault( $field, 'choice_0', ' (alle) ' );
  return selector_geschaeftsjahr( $field );
}


function selector_valuta( $field, $opts = array() ) {
  global $current_month, $current_day, $geschaeftsjahr_thread;

  $opts = parameters_explode( $opts, 'geschaeftsjahr' );
  $geschaeftsjahr = adefault( $opts, 'geschaeftsjahr', $geschaeftsjahr_thread );
  $min = adefault( $field, 'min', 100 );
  $max = adefault( $field, 'max', 1299 );
  $priority = adefault( $field, 'priority', 1 );
  $name = adefault( $field, array( 'cgi_name', 'name' ), 'valuta' );
  $pname = 'P'.( $priority + 1 )."_$name";

  $selected = adefault( $field, array( 'value', 'default' ), 0 );
  if( $selected < $min ) {
    $selected = $min;
  } else if( $selected > $max ) {
    $selected = $max;
  }
  if( ! is_valid_valuta( $selected, $geschaeftsjahr ) ) {
    $selected = 100 * $current_month + $current_day;
  }
  $selected_month = (int)( $selected / 100 );
  $selected_day = $selected % 100;
  $is_month_ultimo = ( $selected_day >= get_month_ultimo( $selected_month, $geschaeftsjahr ) );

  $s = '';

  // 100
  //
  if( $min <= 100 ) {
    $s .= inlink( '!', array(
      'class' => 'button qquadr ' . ( $selected == 100 ? 'on' : 'off' )
    , 'text' => '100'
    , 'inactive' => ( $selected == 100 )
    , $pname => 100
    ) );
  }

  // M <
  //
  if( $selected_day > 31 ) {
    $next = 100 * $selected_month + get_month_ultimo( $selected_month, $geschaeftsjahr );
  } else if( $selected_month > 1 ) {
    if( $is_month_ultimo ) {
      $next = 100 * ( $selected_month - 1 ) + get_month_ultimo( $selected_month - 1, $geschaeftsjahr );
    } else {
      $next = 100 * ( $selected_month - 1 ) + min( $selected_day, get_month_ultimo( $selected_month - 1, $geschaeftsjahr ) );
    }
  } else {
    $next = 100;
  }
  $s .= inlink( '!', array( 
    'class' => 'button quads'
  , 'text' => 'M <'
  , 'inactive' => ( $selected <= $min )
  , $pname => $next
  ) );

  // D <
  //
  if( $selected_day > get_month_ultimo( $selected_month, $geschaeftsjahr ) ) {
    $next = 100 * $selected_month + get_month_ultimo( $selected_month, $geschaeftsjahr );
  } else if( $selected_day > 1 ) {
    $next = $selected - 1;
  } else if( $selected_month > 1 ) {
    $next = 100 * ( $selected_month - 1 ) + get_month_ultimo( $selected_month - 1, $geschaeftsjahr );
  } else {
    $next = 100;
  }
  $s .= inlink( '!', array( 
    'class' => 'button quads'
  , 'text' => 'D <'
  , 'inactive' => ( $selected <= $min )
  , $pname => $next
  ) );

  // int
  //
  $s .= int_element( array(
    'name' => $name
  , 'priority' => $priority
  , 'auto' => 1
  , 'normalized' => $selected
  , 'size' => 4
  ) );

  // D >
  //
  if( checkdate( $selected_month, $selected_day + 1, $geschaeftsjahr ) ) {
    $next = $selected + 1;
  } else if( $selected_month < 12 ) {
    $next = 100 * ( $selected_month + 1 ) + 1;
  } else {
    $next = 1299;
  }
  $s .= inlink( '!', array( 
    'class' => 'button quads'
  , 'text' => 'D >'
  , 'inactive' => ( $selected >= $max )
  , $pname => $next
  ) );

  // M >
  //
  if( $selected_day < 1 ) {
    $next = 100 * $selected_month + 1;
  } else if( $selected_month < 12 ) {
    if( $is_month_ultimo ) {
      $next = 100 * ( $selected_month + 1 ) + get_month_ultimo( $selected_month + 1, $geschaeftsjahr );
    } else {
      $next = 100 * ( $selected_month + 1 ) + min( $selected_day, get_month_ultimo( $selected_month + 1, $geschaeftsjahr ) );
    }
  } else {
    $next = 1299;
  }
  $s .= inlink( '!', array( 
    'class' => 'button quads'
  , 'text' => 'M >'
  , 'inactive' => ( $selected >= $max )
  , $pname => $next
  ) );

  // 1231
  //
  if( $max >= 1231 ) {
    $s .= inlink( '!', array(
      'class' => 'button qquadl ' . ( $selected == 1231 ? 'on' : 'off' )
    , 'text' => '1231'
    , 'inactive' => ( $selected == 1231 )
    , $pname => 1231
    ) );
   }

  // 1299
  if( $max >= 1299 ) {
    $s .= inlink( '!', array(
      'class' => 'button qquadl ' . ( $selected == 1299 ? 'on' : 'off' )
    , 'text' => '1299'
    , 'inactive' => ( $selected == 1299 )
    , $pname => 1299
    ) );
  }

  return html_span( 'inline_block oneline', $s );
}

// filter_stichtag() ... makes no sense!


// FIXME: logic?
// filters_kontodaten_prepare:
// $fields: list of $fields to initialize:
// - will apply special logic to get well-known fields consistent and derive values of less specific fields from more specific ones.
// - fieldnames are arbitrary, but we assume basename === sql_name, and only the well-known basenames (see $kontodaten_fields below) are handled
// - http submissions should only occur with changes (use 'auto' with radiobuttons)
//
function filters_kontodaten_prepare( $fields = true, $opts = array() ) {

  $opts = parameters_explode( $opts );
  $auto_select_unique = adefault( $opts, 'auto_select_unique', false );
  $flag_modified = adefault( $opts, 'flag_modified', false );
  $flag_problems = adefault( $opts, 'flag_problems', false );
  $authorized = adefault( $opts, 'authorized', 0 );

  // kontodaten_fields: order matters here, for specifity and for filtering
  // (later fields must allow earlier ones as filters)
  $kontodaten_fields = array( 'seite', 'kontenkreis', 'geschaeftsbereich', 'kontoklassen_id', 'hauptkonten_id', 'unterkonten_id' );
  $kontodaten_fields = parameters_explode( $kontodaten_fields, 'default_value=' );
  if( $fields === true ) {
    $fields = $kontodaten_fields;
  }
  $fields = parameters_explode( $fields, 'default_value=' );

  $state = init_fields( $fields, $opts );
  $bstate = array(); // state with basenames, referencing $state (only contains relevant entries)
  foreach( $state as $fieldname => $field ) {
    if( $fieldname[ 0 ] == '_' ) {
      continue; // skip pseudo-fields
    }
    $basename = $field['basename'];
    if( ! isset( $kontodaten_fields[ $basename ] ) ) {
      continue;
    }
    need( $field['sql_name'] === $basename );
    $bstate[ $basename ] = & $state[ $fieldname ];
  }

  // make complete working copy of relevant fields, also containing dummy entries for fields from
  // $kontodaten_fields missing in $state (saving lots of conditionals in the loops below):
  //
  $work = array();
  foreach( $kontodaten_fields as $basename => $dummy ) {
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
          case 'geschaeftsbereich':
            if( isset( $bstate['kontenkreis'] ) ) {
              if( $bstate['kontenkreis']['value'] !== 'E' ) {
                break;
              }
            }
          case 'kontenkreis':
            $work['geschaeftsbereich']['value'] = '';
          case 'seite':
            $work['kontoklassen_id']['value'] = 0;
          case 'kontoklassen_id':
            $work['hauptkonten_id']['value'] = 0;
          case 'hauptkonten_id':
            $work['unterkonten_id']['value'] = 0;
        }
      }
    } else { /* not passed via http */

      if( ( $r['value'] !== NULL ) && ( "{$r['value']}" !== "{$r['default']}" ) ) {

        $filters[ $basename ] = & $r['value'];
        // value not from http - check and drop setting if inconsistent:
        switch( $basename ) {
          case 'unterkonten_id':
            $check = sql_unterkonten( $filters, array( 'authorized' => $authorized ) );
            break;
          case 'hauptkonten_id':
            $check = sql_hauptkonten( $filters, array( 'authorized' => $authorized ) );
            break;
          case 'geschaeftsbereich':
            if( isset( $bstate['kontenkreis'] ) ) {
              $check = ( $bstate['kontenkreis']['value'] !== 'B' );
            } else {
              $check = true;
            }
            break;
          case 'kontoklassen_id':
            $check = sql_kontoklassen( $filters, array( 'authorized' => $authorized ) );
            break;
          default:
            $check = true;
        }
        if( ! $check ) {
          $r['value'] = $r['default'];
          unset( $filters[ $basename ] );
        }
      }

      if( $auto_select_unique ) {
        if( ( $r['value'] === NULL ) || ( "{$r['value']}" === "{$r['default']}" ) ) {

          switch( $basename ) {
            case 'kontoklassen_id':
              $klassen = sql_kontoklassen( $filters, array( 'authorized' => $authorized ) );
              if( count( $klassen ) == 1 ) {
                $r['value'] = $klassen[ 0 ]['kontoklassen_id'];
                $filters['kontoklassen_id'] = & $r['value'];
              }
              break;
            case 'unterkonten_id':
              $uk = sql_unterkonten( $filters, array( 'authorized' => $authorized ) );
              if( count( $uk ) == 1 ) {
                $r['value'] = $uk[ 0 ]['unterkonten_id'];
                $filters['unterkonten_id'] = & $r['value'];
              }
              break;
            case 'hauptkonten_id':
              $hk = sql_hauptkonten( $filters, array( 'authorized' => $authorized ) );
              if( count( $hk ) == 1 ) {
                $r['value'] = $hk[ 0 ]['hauptkonten_id'];
                $filters['hauptkonten_id'] = & $r['value'];
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
      case 'unterkonten_id':
        $uk = sql_one_unterkonto( $r['value'], array( 'authorized' => $authorized ) );
        $work['hauptkonten_id']['value'] = $uk['hauptkonten_id'];
        // fall-through
      case 'hauptkonten_id':
        $hk = sql_one_hauptkonto( $work['hauptkonten_id']['value'], array( 'authorized' => $authorized ) );
        $work['kontoklassen_id']['value'] = $hk['kontoklassen_id'];
        // fall-through
      case 'kontoklassen_id':
        $kontoklasse = sql_one_kontoklasse( $work['kontoklassen_id']['value'], array( 'authorized' => $authorized ) );
        $work['seite']['value'] = $kontoklasse['seite'];
        $work['kontenkreis']['value'] = $kontoklasse['kontenkreis'];
        if( $work['kontenkreis']['value'] === 'E' && $GLOBALS['unterstuetzung_geschaeftsbereiche'] ) {
          // $work['geschaeftsbereich_uid']['value'] = value2uid( $kontoklasse['geschaeftsbereich'] );
          $work['geschaeftsbereich']['value'] = $kontoklasse['geschaeftsbereich'];
        } else {
          // $work['geschaeftsbereich_uid']['value'] = value2uid('');
          $work['geschaeftsbereich']['value'] = '';
        }
    }
  }

  // loop 3:
  // - recheck for problems and modifications
  // - fill and return $filters array to be used in sql queries:
  //
  foreach( $bstate as $basename => & $r ) {
    $fieldname = $r['name'];

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
      $state['_filters'][ $basename ] = & $r['value'];
    } else {
      unset( $state['_filters'][ $basename ] );
    }
  }
  if( ! $GLOBALS['unterstuetzung_geschaeftsbereiche'] || ( ! isset( $bstate['kontenkreis']['value'] ) ) || ( $bstate['kontenkreis']['value'] !== 'E' ) ) {
    unset( $state['_problems']['geschaeftsbereich'] );
  }
  return $state;
}



?>
