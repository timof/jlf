<?php

if( isset( $geschaeftsjahr_thread ) && ! isset( $sessionvars['geschaeftsjahr'] ) )
  $sessionvars['geschaeftsjahr'] = $geschaeftsjahr_thread;

$pfields = array(
  'kontoart' => '/^[0BE]?$/'
, 'seite' => '/^[0AP]?$/'
, 'geschaeftsbereiche_id' => 'w'
, 'hauptkonten_id' => 'u'
, 'unterkonten_id' => 'u'
, 'betrag' => 'f'
, 'beleg' => 'h'
);

get_http_var( 'buchungen_id', 'u', 0, 'self' );
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
  form_ensure_geschaeftsjahr();
  $buchung = false;
  $nS = 1;
  $nH = 1;
  $pS = array();
  $pH = array();
}

row2global( 'buchungen', $buchung );

if( ! $buchung ) {
  if( $valuta_letzte_buchung )
    $valuta = $valuta_letzte_buchung;
  else
    $valuta = sprintf( '%02u%02u', $now[1], $now[2] );
}
get_http_var( 'valuta', 'U', $valuta );

get_http_var( 'kommentar', 'h', $kommentar );

get_http_var( 'nS', 'U', $nS );
get_http_var( 'nH', 'U', $nH );

// prettydump( $nS, 'nS' );
// prettydump( $nH, 'nH' );

foreach( $pfields as $field => $pattern ) {
  $default = adefault( $jlf_defaults, $pattern, 0 );
  for( $n = 0; $n < $nS ; $n++ ) {
    if( ! isset( $pS[$n] ) )
      $pS[$n] = array();
    get_http_var( 'pS'.$n.'_'.$field, $pattern, adefault( $pS[$n], $field, $default ) );
  }
  for( $n = 0; $n < $nH ; $n++ ) {
    if( ! isset( $pH[$n] ) )
      $pH[$n] = array();
    get_http_var( 'pH'.$n.'_'.$field, $pattern, adefault( $pH[$n], $field, $default ) );
  }
}

$is_vortrag = 0;

$problems = false;
$problem_summe = '';
$problem_valuta = '';

function form_row_posten( $s ) {
  global $problem_summe, $geschaeftsjahr;

  $betrag = $GLOBALS[$s.'_betrag'];
  $beleg = $GLOBALS[$s.'_beleg'];
  $kontoart = & $GLOBALS[$s.'_kontoart'];
  $seite = & $GLOBALS[$s.'_seite'];
  $geschaeftsbereiche_id = & $GLOBALS[$s.'_geschaeftsbereiche_id'];
  $hauptkonten_id = & $GLOBALS[$s.'_hauptkonten_id'];
  $unterkonten_id = & $GLOBALS[$s.'_unterkonten_id'];
  $GLOBALS[$s.'filters'] = array();
  $pf = & $GLOBALS[$s.'filters'];
  $problem = gdefault( $s.'_problem', '' );

  // open_div( 'warn', '', "form_row_posten 1:  $s, $kontoart, $seite, $hauptkonten_id, $unterkonten_id" );

  if( $unterkonten_id ) {
    $uk = sql_one_unterkonto( $unterkonten_id );
    if( ! $hauptkonten_id ) {
      $hauptkonten_id = $uk['hauptkonten_id'];
    } else {
      if( $uk['hauptkonten_id'] != $hauptkonten_id )
        $unterkonten_id = 0;
        $uk = false;
    }
  }

  if( $hauptkonten_id ) {
    $hk = sql_one_hauptkonto( $hauptkonten_id );
    if( $hk['geschaeftsjahr'] != $geschaeftsjahr )
      $hauptkonten_id = 0;
  }
  if( $hauptkonten_id ) {
    if( ! $kontoart ) {
      $kontoart = $hk['kontoart'];
    } else {
      if( $hk['kontoart'] != $kontoart ) {
        $hauptkonten_id = 0;
        $hk = false;
      }
    }
    if( ! $seite ) {
      $seite = $hk['seite'];
    } else {
      if( $hk['seite'] != $seite ) {
        $hauptkonten_id = 0;
        $hk = false;
      }
    }
    if( "$kontoart" == 'E' ) {
      $id = sql_unique_id( 'kontoklassen', 'geschaeftsbereich', $hk['geschaeftsbereich'] );
      if( ! $geschaeftsbereiche_id ) {
        $geschaeftsbereiche_id = $id;
      } else {
        if( $geschaeftsbereiche_id != $id ) {
          $hauptkonten_id = 0;
          $hk = false;
        }
      }
    } else {
      $geschaeftsbereiche_id = 0;
    }
    if( $hauptkonten_id ) {
      if( ! $unterkonten_id ) {
        $uk = sql_unterkonten( array( 'hauptkonten_id' => $hauptkonten_id ) );
        if( count( $uk ) == 1 )
          $unterkonten_id = $uk[0]['unterkonten_id'];
      }
      if( $hk['vortragskonto'] )
        $is_vortrag = 1;
    }
  }

  if( $geschaeftsbereiche_id ) {
    if( "$kontoart" != 'E' ) {
      $geschaeftsbereiche_id = 0;
    }
  }

  open_td( "smallskip top $problem" );
    open_div();
      filter_kontoart( $s, '' );
      filter_seite( $s, '' );
    close_div();
    if( "$kontoart" == 'E' ) {
      open_div( 'smallskip' );
        filter_geschaeftsbereich( $s, '' );
      close_div();
    } else {
      hidden_input( $s.'geschaeftsbereiche_id', '0' );
    }
  open_td( "smallskip top $problem" );
    open_div();
      $pf = array();
      if( $kontoart )
        $pf['kontoart'] = $kontoart;
      if( $seite )
        $pf['seite'] = $seite;
      if( $geschaeftsbereiche_id )
        $pf['geschaeftsbereiche_id'] = $geschaeftsbereiche_id;
      $pf['geschaeftsjahr'] = $geschaeftsjahr;
      filter_hauptkonto( $s, $pf, '' );
    close_div();
    if( $hauptkonten_id ) {
      open_div( '', '', inlink( 'hauptkonto', array(
        'class' => 'href', 'hauptkonten_id' => $hauptkonten_id, 'text' => 'zum Hauptkonto...'
      ) ) );
    }
  open_td( "smallskip top $problem" );
    if( $hauptkonten_id ) {
      open_div();
        filter_unterkonto( $s, array( 'hauptkonten_id' => $hauptkonten_id ), '' );
      close_div();
      if( $unterkonten_id ) {
        open_div( '', '', inlink( 'unterkonto', array(
          'class' => 'href', 'unterkonten_id' => $unterkonten_id, 'text' => 'zum Unterkonto...'
        ) ) );
      }
    } else {
      hidden_input( $s.'unterkonten_id', '0' );
    }
  open_td( 'smallskip bottom', '', string_view( $beleg, $s.'_beleg' ) );
  open_td( "smallskip bottom $problem_summe", '', price_view( $betrag, $s.'_betrag' ) );
}


handle_action( array( 'init', 'update', 'save', 'addS', 'addH', 'deleteS', 'deleteH', 'upS', 'upH', 'fillH', 'fillS', 'template' ) );
switch( $action ) {
  case 'save':
    $summeS = 0.0;
    $summeH = 0.0;
    $values_posten = array();
    for( $n = 0; $n < $nS; $n++ ) {
      $unterkonten_id = $GLOBALS['pS'.$n.'_unterkonten_id'];
      $betrag = sprintf( '%.2lf', $GLOBALS['pS'.$n.'_betrag'] );
      if( ! $unterkonten_id ) {
        $GLOBALS['pS'.$n.'_problem'] = 'problem';
        $problems = true;
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
        $GLOBALS['pS'.$n.'_problem'] = 'problem';
      }
      $values_posten[] = array(
        'art' => 'S'
      , 'betrag' => $betrag
      , 'unterkonten_id' => $unterkonten_id
      , 'beleg' => $GLOBALS['pS'.$n.'_beleg']
      );
    }
    for( $n = 0; $n < $nH; $n++ ) {
      $unterkonten_id = $GLOBALS['pH'.$n.'_unterkonten_id'];
      $betrag = sprintf( '%.2lf', $GLOBALS['pH'.$n.'_betrag'] );
      if( ! $unterkonten_id ) {
        $problems = true;
        $GLOBALS['pH'.$n.'_problem'] = 'problem';
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
        $GLOBALS['pH'.$n.'_problem'] = 'problem';
      }
      $values_posten[] = array(
        'art' => 'H'
      , 'betrag' => $betrag
      , 'unterkonten_id' => $unterkonten_id
      , 'beleg' => $GLOBALS['pH'.$n.'_beleg']
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
        // prettydump( $summeH, 'summeH' );
        // prettydump( $summeS, 'summeS' );
      }
    }
    if( ! $problems )
      $buchungen_id = sql_buche( $buchungen_id, $valuta, $kommentar, $values_posten );
    break;
  case 'addS':
    foreach( $pfields as $field => $pattern ) {
      ${'pS'.$nS.'_'.$field} = adefault( $jlf_defaults, $pattern, 0 );
    }
    $nS++;
    break;
  case 'addH':
    foreach( $pfields as $field => $pattern ) {
      ${'pH'.$nH.'_'.$field} = adefault( $jlf_defaults, $pattern, 0 );
    }
    $nH++;
    break;
  case 'upS':
    need( is_numeric( $message ) && ( $message >= 1 ) && ( $message < $nS ) );
    foreach( $pfields as $f => $def ) {
      $h = $GLOBALS['pS'.($message-1).'_'.$f];
      $GLOBALS['pS'.($message-1).'_'.$f] = $GLOBALS['pS'.$message.'_'.$f];
      $GLOBALS['pS'.$message.'_'.$f] = $h;
    }
    break;
  case 'upH':
    need( is_numeric( $message ) && ( $message >= 1 ) && ( $message < $nH ) );
    foreach( $pfields as $f => $def ) {
      $h = $GLOBALS['pH'.($message-1).'_'.$f];
      $GLOBALS['pH'.($message-1).'_'.$f] = $GLOBALS['pH'.$message.'_'.$f];
      $GLOBALS['pH'.$message.'_'.$f] = $h;
    }
    break;
  case 'deleteS':
    need( ( $nS > 1 ) && is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nS ) );
    while( $message < $nS-1 ) {
      foreach( $pfields as $f => $def ) {
        $GLOBALS['pS'.$message.'_'.$f] = $GLOBALS['pS'.($message+1).'_'.$f];
      }
      $message++;
    }
    $nS--;
    break;
  case 'deleteH':
    need( ( $nH > 1 ) && is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nH ) );
    while( $message < $nH-1 ) {
      foreach( $pfields as $f => $def ) {
        $GLOBALS['pH'.$message.'_'.$f] = $GLOBALS['pH'.($message+1).'_'.$f];
      }
      $message++;
    }
    $nH--;
    break;
  case 'fillS':
    need( is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nS ) );
    for( $i = 0, $saldoS = 0.0; $i < $nS; $i++ ) {
      if( $i == $message )
        continue;
      $saldoS += $GLOBALS['pS'.$i.'_betrag'];
    }
    for( $i = 0, $saldoH = 0.0; $i < $nH; $i++ ) {
      $saldoH += $GLOBALS['pH'.$i.'_betrag'];
    }
    $GLOBALS['pS'.$message.'_betrag'] = $saldoH - $saldoS;
    break;
  case 'fillH':
    need( is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nH ) );
    for( $i = 0, $saldoS = 0.0; $i < $nS; $i++ ) {
      $saldoS += $GLOBALS['pS'.$i.'_betrag'];
    }
    for( $i = 0, $saldoH = 0.0; $i < $nH; $i++ ) {
      if( $i == $message )
        continue;
      $saldoH += $GLOBALS['pH'.$i.'_betrag'];
    }
    $GLOBALS['pH'.$message.'_betrag'] = $saldoS - $saldoH;
    break;
  case 'template':
    $buchungen_id = 0;
    break;
}


open_fieldset( 'small_form', '', 'Buchung ' . ( $buchungen_id ? "$buchungen_id" : '(neu)' ) );
  open_form( 'name=update_form', 'action=update' );
    open_table( 'form' );
      open_tr();
    hidden_input( 'nS' );
    hidden_input( 'nH' );

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
          echo "<textarea name='kommentar' rows='3' cols='60'>$kommentar</textarea>";
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
          form_row_posten( 'pS'.$i );
          open_td( 'bottom' );
            submission_button( 'fillS_'.$i, '=', 'href' );
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
          form_row_posten( 'pH'.$i );
          open_td( 'bottom' );
            submission_button( 'fillH_'.$i, '=', 'href' );
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
