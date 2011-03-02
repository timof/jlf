<?php

$pfields = array(
  'kontoart' => '/^[0BE]?$/'
, 'seite' => '/^[0AP]?$/'
, 'geschaeftsbereiche_id' => 'w'
, 'hauptkonten_id' => 'u'
, 'unterkonten_id' => 'u'
, 'betrag' => 'f'
, 'beleg' => 'h'
);

init_global_var( 'buchungen_id', 'u', 'http,persistent', 0, 'self' );
// prettydump( $buchungen_id, 'buchungen_id' );
if( $buchungen_id ) {
  $buchung = sql_one_buchung( $buchungen_id );
  $pS = sql_posten( array( 'buchungen_id' => $buchungen_id, 'art' => 'S' ) );
  $nS = count( $pS );
  $pH = sql_posten( array( 'buchungen_id' => $buchungen_id, 'art' => 'H' ) );
  $nH = count( $pH );
  if( $nH < 1 || $nS < 1 ) {
    $buchungen_id = 0;
  } else {
    $geschaeftsjahr = $pS[0]['geschaeftsjahr'];
  }
}
if( ! $buchungen_id ) {
  init_global_var( 'geschaeftsjahr', 'u', 'http,persistent,keep', $geschaeftsjahr_current, 'self' );
  need( $geschaeftsjahr, 'kein geschaeftsjahr gewaehlt' );
  $buchung = false;
  $nS = 1;
  $nH = 1;
  $pS = array();
  $pH = array();
  if( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen ) {
    div_msg( 'warn', 'Geschaeftsjahr abgeschlossen - keine Buchung moeglich' );
    return;
  }
}

row2global( 'buchungen', $buchung );

if( ! $buchung ) {
  if( $valuta_letzte_buchung )
    $valuta = $valuta_letzte_buchung;
  else
    $valuta = sprintf( '%02u%02u', $now[1], $now[2] );
}
init_global_var( 'valuta', 'U', 'http,persistent,keep', NULL, 'self' );

init_global_var( 'kommentar', 'h', 'http,persistent,keep', NULL, 'self' );

init_global_var( 'nS', 'U', 'http,persistent,keep', NULL, 'self' );
init_global_var( 'nH', 'U', 'http,persistent,keep', NULL, 'self' );

// prettydump( $nS, 'nS' );
// prettydump( $nH, 'nH' );

$is_vortrag = 0;

$problems = false;
$problem_summe = '';
$problem_valuta = '';

$geschlossen = ( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen );

foreach( $pfields as $field => $pattern ) {
  $default = adefault( $jlf_defaults, $pattern, 0 );
  for( $n = 0; $n < $nS ; $n++ ) {
    if( ! isset( $pS[$n] ) )
      $pS[$n] = array();
    init_global_var( 'pS'.$n.'_'.$field, $pattern, 'http,persistent', adefault( $pS[$n], $field, $default ), 'self' );
    $pS[ $n ][ $field ] = ${'pS'.$n.'_'.$field};
  }
  for( $n = 0; $n < $nH ; $n++ ) {
    if( ! isset( $pH[$n] ) )
      $pH[$n] = array();
    init_global_var( 'pH'.$n.'_'.$field, $pattern, 'http,persistent', adefault( $pH[$n], $field, $default ), 'self' );
    $pH[ $n ][ $field ] = ${'pH'.$n.'_'.$field};
  }
}

for( $n = 0; $n < $nS ; $n++ ) {
  $pS[$n] = normalize_posten( $pS[$n] );
}
for( $n = 0; $n < $nH ; $n++ ) {
  $pH[$n] = normalize_posten( $pH[$n] );
}

// function normalize_posten():
//   - initialize empty fields from more specific fields (if possible)
//   - remove inconsistencies (drop more specific fields as needed)
//   - check for closed accounts
function normalize_posten( $p ) {
  global $geschlossen, $geschaeftsjahr;

  if( $p['unterkonten_id'] ) {
    $uk = sql_one_unterkonto( $p['unterkonten_id'] );
    $geschlossen |= $uk['unterkonto_geschlossen'];
    if( ! $p['hauptkonten_id'] ) {
      $p['hauptkonten_id'] = $uk['hauptkonten_id'];
    } else {
      if( $uk['hauptkonten_id'] != $p['hauptkonten_id'] )
        $p['unterkonten_id'] = 0;
    }
  }

  if( $p['hauptkonten_id'] ) {
    $hk = sql_one_hauptkonto( $p['hauptkonten_id'] );
    if( $hk['geschaeftsjahr'] != $geschaeftsjahr )
      $hauptkonten_id = 0;
  }
  if( $p['hauptkonten_id'] ) {
    if( ! $p['kontoart'] ) {
      $p['kontoart'] = $hk['kontoart'];
    } else {
      if( $hk['kontoart'] != $p['kontoart'] )
        $p['hauptkonten_id'] = 0;
    }
    if( ! $p['seite'] ) {
      $p['seite'] = $hk['seite'];
    } else {
      if( $hk['seite'] != $p['seite'] )
        $p['hauptkonten_id'] = 0;
    }
    if( $p['kontoart'] == 'E' ) {
      $id = sql_unique_id( 'kontoklassen', 'geschaeftsbereich', $hk['geschaeftsbereich'] );
      if( ! $p['geschaeftsbereiche_id'] ) {
        $p['geschaeftsbereiche_id'] = $id;
      } else {
        if( $p['geschaeftsbereiche_id'] != $id )
          $p['hauptkonten_id'] = 0;
      }
    } else {
      $p['geschaeftsbereiche_id'] = 0;
    }
    if( $p['hauptkonten_id'] ) {
      if( ! $p['unterkonten_id'] ) {
        $uk = sql_unterkonten( array( 'hauptkonten_id' => $p['hauptkonten_id'] ) );
        if( count( $uk ) == 1 )
          $p['unterkonten_id'] = $uk[0]['unterkonten_id'];
      }
      if( $hk['vortragskonto'] )
        $is_vortrag = 1;
    }
  }

  if( $p['geschaeftsbereiche_id'] ) {
    if( ! $p['kontoart'] ) {
      $p['kontoart'] = 'E';
    } else {
      if( $p['kontoart'] != 'E' )
        $p['geschaeftsbereiche_id'] = 0;
    }
  }

  return $p;
}

function form_row_posten( $art, $n ) {
  global $problem_summe, $geschaeftsjahr, $geschlossen;

  $p = $GLOBALS["p$art"][ $n ]; // existing data
  $s = 'p'.$art.$n.'_';             // prefix for form field names

  // the filters need globals to provide initial values:
  $GLOBALS[ $s.'kontoart'] = $p['kontoart'];
  $GLOBALS[ $s.'seite'] = $p['seite'];
  $GLOBALS[ $s.'geschaeftsbereiche_id'] = $p['geschaeftsbereiche_id'];
  $GLOBALS[ $s.'hauptkonten_id'] = $p['hauptkonten_id'];
  $GLOBALS[ $s.'unterkonten_id'] = $p['unterkonten_id'];
  $problem = adefault( $p, 'problem', '' );

  open_td( "smallskip top $problem" );
    open_div( 'oneline' );
      if( $geschlossen ) {
        echo "{$p['kontoart']} {$p['seite']}";
      } else {
        filter_kontoart( $s, '' );
        filter_seite( $s, '' );
      }
    close_div();
    if( $p['kontoart'] == 'E' ) {
      open_div( 'smallskip' );
        if( $geschlossen ) {
          echo sql_unique_value( 'kontoklassen', 'geschaeftsbereich', $p['geschaeftsbereiche_id'] );
        } else {
          filter_geschaeftsbereich( $s, '' );
        }
      close_div();
    }
  open_td( "smallskip top $problem" );
    open_div();
      if( $p['kontoart'] )
        $pf['kontoart'] = $p['kontoart'];
      if( $p['seite'] )
        $pf['seite'] = $p['seite'];
      if( $p['geschaeftsbereiche_id'] )
        $pf['geschaeftsbereiche_id'] = $p['geschaeftsbereiche_id'];
      $pf['geschaeftsjahr'] = $geschaeftsjahr;
      filter_hauptkonto( $s, $pf, '' );
    close_div();
    if( $p['hauptkonten_id'] ) {
      open_div( '', '', inlink( 'hauptkonto', array(
        'class' => 'href', 'hauptkonten_id' => $p['hauptkonten_id'], 'text' => 'zum Hauptkonto...'
      ) ) );
    }
  open_td( "smallskip top $problem" );
    if( $p['hauptkonten_id'] ) {
      open_div();
        filter_unterkonto( $s, array( 'hauptkonten_id' => $p['hauptkonten_id'] ), '' );
      close_div();
      if( $p['unterkonten_id'] ) {
        open_div( '', '', inlink( 'unterkonto', array(
          'class' => 'href', 'unterkonten_id' => $p['unterkonten_id'], 'text' => 'zum Unterkonto...'
        ) ) );
      }
    }
  open_td( 'smallskip bottom', '', string_view( $p['beleg'], $s.'beleg' ) );
  open_td( "smallskip bottom $problem_summe", '', price_view( $p['betrag'], $s.'betrag' ) );
}


handle_action( array( 'init', 'update', 'save', 'addS', 'addH', 'deleteS', 'deleteH', 'upS', 'upH', 'fillH', 'fillS', 'template' ) );
switch( $action ) {
  case 'save':
    $summeS = 0.0;
    $summeH = 0.0;
    $values_posten = array();
    for( $n = 0; $n < $nS; $n++ ) {
      $unterkonten_id = $pS[ $n ]['unterkonten_id'];
      $betrag = sprintf( '%.2lf', $pS[ $n ]['betrag'] );
      if( ! $unterkonten_id ) {
        $pS[ $n ]['problem'] = 'problem';
        $problems = true;
        continue;
      } else {
        $summeS += $betrag;
      }
      // if( ! ( $betrag > 0.001 ) ) {
        // open_div( 'warn', '', "betrag (S)" );
      //  $problems = true;
      //  $problem_summe = 'problem';
      // }
      $uk = sql_one_unterkonto( $unterkonten_id );
      if( $uk['vortragskonto'] ) {
        $is_vortrag = 1;
      }
      if( $uk['unterkonto_geschlossen'] ) {
        $problems = true;
        $pS[ $n ]['problem'] = 'problem';
      }
      $values_posten[] = array(
        'art' => 'S'
      , 'betrag' => $betrag
      , 'unterkonten_id' => $unterkonten_id
      , 'beleg' => $pS[ $n ]['beleg']
      );
    }
    for( $n = 0; $n < $nH; $n++ ) {
      $unterkonten_id = $pH[ $n ]['unterkonten_id'];
      $betrag = sprintf( '%.2lf', $pH[ $n ]['betrag'] );
      if( ! $unterkonten_id ) {
        $pH[ $n ]['problem'] = 'problem';
        $problems = true;
        continue;
      } else {
        $summeH += $betrag;
      }
      // if( ! ( $betrag > 0.001 ) ) {
      //   $problems = true;
      //   $problem_summe = 'problem';
      // }
      $uk = sql_one_unterkonto( $unterkonten_id );
      if( $uk['vortragskonto'] ) {
        $is_vortrag = 1;
      }
      if( $uk['unterkonto_geschlossen'] ) {
        $problems = true;
        $pH[ $n ]['problem'] = 'problem';
      }
      $values_posten[] = array(
        'art' => 'H'
      , 'betrag' => $betrag
      , 'unterkonten_id' => $unterkonten_id
      , 'beleg' => $pH[ $n ]['beleg']
      );
    }
    if( ! $is_vortrag ) {
      if( ( $valuta < 100 ) || ( $valuta > 1231 ) ) {
        $problems = true;
        $problem_valuta = 'problem';
      }
    }
    if( ! $problems ) {
      if( abs( $summeH - $summeS ) > 0.001 ) {
        $problems = true;
        $problem_summe = 'problem';
      }
    }
    if( ! $problems )
      $buchungen_id = sql_buche( $buchungen_id, $valuta, $kommentar, $values_posten );
    break;
  case 'addS':
    foreach( $pfields as $field => $pattern ) {
      $pS[ $nS ][ $field ] = adefault( $jlf_defaults, $pattern, 0 );
    }
    $nS++;
    break;
  case 'addH':
    foreach( $pfields as $field => $pattern ) {
      $pH[ $nH ][ $field ] = adefault( $jlf_defaults, $pattern, 0 );
    }
    $nH++;
    break;
  case 'upS':
    need( is_numeric( $message ) && ( $message >= 1 ) && ( $message < $nS ) );
    $h = $pS[ $message - 1 ];
    $pS[ $message - 1 ] = $pS[ $message ];
    $pS[ $message ] = $h;
    break;
  case 'upH':
    need( is_numeric( $message ) && ( $message >= 1 ) && ( $message < $nH ) );
    $h = $pH[ $message - 1 ];
    $pH[ $message - 1 ] = $pH[ $message ];
    $pH[ $message ] = $h;
    break;
  case 'deleteS':
    need( ( $nS > 1 ) && is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nS ) );
    while( $message < $nS - 1 ) {
      $pS[ $message ] = $pS[ $message + 1 ];
      $message++;
    }
    $nS--;
    break;
  case 'deleteH':
    need( ( $nH > 1 ) && is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nH ) );
    while( $message < $nH - 1 ) {
      $pH[ $message ] = $pH[ $message + 1 ];
      $message++;
    }
    $nH--;
    break;
  case 'fillS':
    need( is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nS ) );
    for( $i = 0, $saldoS = 0.0; $i < $nS; $i++ ) {
      if( $i == $message )
        continue;
      $saldoS += $pS[ $i ]['betrag'];
    }
    for( $i = 0, $saldoH = 0.0; $i < $nH; $i++ ) {
      $saldoH += $pH[ $i ]['betrag'];
    }
    $pS[ $message ]['betrag'] = $saldoH - $saldoS;
    break;
  case 'fillH':
    need( is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nH ) );
    for( $i = 0, $saldoS = 0.0; $i < $nS; $i++ ) {
      $saldoS += $pS[ $i ]['betrag'];
    }
    for( $i = 0, $saldoH = 0.0; $i < $nH; $i++ ) {
      if( $i == $message )
        continue;
      $saldoH += $pH[ $i ]['betrag'];
    }
    $pH[ $message ]['betrag'] = $saldoS - $saldoH;
    break;
  case 'template':
    $buchungen_id = 0;
    break;
}


open_fieldset( 'small_form', '', 'Buchung ' . ( $buchungen_id ? "$buchungen_id" : '(neu)' ) );
  open_form( 'name=update_form', 'action=update' );
    open_table( 'form' );
      open_tr();

      open_tr( $is_vortrag ? '' : 'nodisplay', "id='valuta_vortrag'" );
        open_td( 'center', "colspan='2'", 'Vortrag' );

      open_tr( $is_vortrag ? 'nodisplay' : '', "id='valuta_normal'" );
        open_td( "smallskip $problem_valuta", '', 'Valuta:' );
        open_td( "$problem_valuta" );
          form_field_monthday( $valuta, 'valuta' );
        open_td( 'quads', '', "Geschaeftsjahr: $geschaeftsjahr" );

      open_tr();
        open_td( 'smallskip', '', 'Notiz:' );
        open_td( '', "colspan='2'" );
          echo "<textarea name='kommentar' rows='2' cols='80'>$kommentar</textarea>";
      close_tr();
    close_table();
    bigskip();
    open_table( 'form' );
      open_tr( 'smallskips' );
        open_th( 'top' );
          open_div( 'tight', '', 'Kontoart / Seite' );
          open_div( 'tight', '', 'Geschaeftsbereich' );
        open_th( 'top' );
          open_div( 'tight', '', 'Hauptkonto' );
        open_th( 'top' );
          open_div( 'tight', '', 'Unterkonto' );
        open_th( 'bottom', '', 'Beleg' );
        open_th( "bottom $problem_summe", '', 'Betrag' );
        open_th( 'bottom', '', 'Aktionen' );
      for( $i = 0; $i < $nS ; $i++ ) {
        open_tr( 'solidbottom smallskips ' );
          form_row_posten( 'S', $i );
          open_td( 'bottom' );
            submission_button( 'fillS_'.$i, " = ", 'button' );
            if( $nS > 1 )
              submission_button( 'deleteS_'.$i, '', 'drop href', 'Posten wirklich loeschen?' );
            if( $i > 0 )
              submission_button( 'upS_'.$i, '', 'uparrow href' );
      }
      open_tr( 'smallskips' );
        open_td( 'right', "colspan='6'", html_submission_button( 'addS', ' ', 'plus href' ) );
      open_tr( 'medskip' );
        open_th( 'bold', "colspan='6'", 'an' );
      for( $i = 0; $i < $nH ; $i++ ) {
        open_tr( 'smallskips solidbottom' );
          form_row_posten( 'H', $i );
          open_td( 'bottom' );
            submission_button( 'fillH_'.$i, " = ", 'qquads button' );
            if( $nH > 1 )
              submission_button( 'deleteH_'.$i, '', 'drop href', 'Posten wirklich loeschen?' );
            if( $i > 0 )
              submission_button( 'upH_'.$i, '', 'uparrow href' );
      }
      open_tr( 'smallskips' );
        open_td( 'right', "colspan='6'", html_submission_button( 'addH', ' ', 'plus href' ) );
      open_tr( 'smallskips' );
        open_td( 'right medskip', "colspan='6'" );
          if( $buchungen_id )
            open_span( 'quads', '', html_submission_button( 'template', 'als Vorlage benutzen', 'button' ) );
          open_span( 'quads', '', html_submission_button( 'save', 'Speichern', 'button' ) );
    close_table();
  close_form();
close_fieldset();

if( $is_vortrag ) {
  js_on_exit( "document.getElementById['valuta_normal'].style.display = 'none';" );
  js_on_exit( "document.getElementById['valuta_vortrag'].style.display = '';" );
} else {
  js_on_exit( "document.getElementById['valuta_vortrag'].style.display = 'none';" );
  js_on_exit( "document.getElementById['valuta_normal'].style.display = '';" );
}

?>
