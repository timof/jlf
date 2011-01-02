<?php

$pfields = array(
  'kontoart' => '/^[0BE]$/'
, 'seite' => '/^[0AP]$/'
, 'geschaeftsbereiche_id' => 'w'
, 'hauptkonten_id' => 'u'
, 'unterkonten_id' => 'u'
, 'betrag' => 'f'
, 'beleg' => 'h'
);

function form_row_posten( $s ) {
  global $problem_summe;

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
    }
  }

  if( $hauptkonten_id ) {
    $hk = sql_one_hauptkonto( $hauptkonten_id );
    if( ! $kontoart ) {
      $kontoart = $hk['kontoart'];
    } else {
      if( $hk['kontoart'] != $kontoart )
        $hauptkonten_id = 0;
    }
    if( ! $seite ) {
      $seite = $hk['seite'];
    } else {
      if( $hk['seite'] != $seite )
        $hauptkonten_id = 0;
    }
    if( "$kontoart" == 'E' ) {
      $id = sql_unique_id( 'kontoklassen', 'geschaeftsbereich', $hk['geschaeftsbereich'] );
      if( ! $geschaeftsbereiche_id ) {
        $geschaeftsbereiche_id = $id;
      } else {
        if( $geschaeftsbereiche_id != $id )
          $hauptkonten_id = 0;
      }
    } else {
      $geschaeftsbereiche_id = 0;
    }
  }

  if( $geschaeftsbereiche_id ) {
    if( "$kontoart" != 'E' ) {
      $geschaeftsbereiche_id = 0;
    }
  }

  open_td( $problem );
    filter_kontoart( $s, '' );
  open_td( $problem );
    filter_seite( $s, '' );
  open_td( $problem );
    if( "$kontoart" == 'E' ) {
      filter_geschaeftsbereich( $s, '' );
    } else {
      hidden_input( $s.'geschaeftsbereiche_id', '0' );
      echo "-";
    }
  open_td( $problem );
    $pf = array();
    if( $kontoart )
      $pf['kontoart'] = $kontoart;
    if( $seite )
      $pf['seite'] = $seite;
    if( $geschaeftsbereiche_id )
      $pf['geschaeftsbereiche_id'] = $geschaeftsbereiche_id;
    filter_hauptkonto( $s, $pf, '' );
  open_td( $problem );
    if( $hauptkonten_id ) {
      filter_unterkonto( $s, array( 'hauptkonten_id' => $hauptkonten_id ), '' );
    } else {
      hidden_input( $s.'unterkonten_id', '0' );
    }
  open_td( '', '', string_view( $beleg, $s.'_beleg' ) );
  open_td( $problem_summe, '', price_view( $betrag, $s.'_betrag' ) );
}

$pS = array();
$pH = array();
$problems = false;
$problem_summe = '';

get_http_var( 'action', 'w', false );
// open_div( 'warn', '', "action: $action" );

if( ! $action || ( $action == 'nop' ) ) {
  // exterer aufruf: neue oder existierende Buchung edieren
  //
  get_http_var( 'buchungen_id', 'u', 0, true );
  if( $buchungen_id ) {
    $buchung = sql_one_buchung( $buchungen_id );
    $pS = sql_posten( array( 'buchungen_id' => $buchungen_id, 'art' => 'S' ) );
    $nS = count( $pS );
    $pH = sql_posten( array( 'buchungen_id' => $buchungen_id, 'art' => 'H' ) );
    $nH = count( $pH );
  } else {
    $buchung = false;
    $nS = 1;
    $pS[0] = false;
    $nH = 1;
    $pH[0] = false;
  }
  row2global( 'buchungen', $buchung );
  if( ! $buchung ) {
    $valuta = $mysqlheute;
  }
  sscanf( $valuta, "%u-%u-%u", & $valuta_year, & $valuta_month, & $valuta_day );
  foreach( $pS as $n => $p ) {
    row2global( 'posten', $p, "pS$n" );
    $GLOBALS['pS'.$n.'_hauptkonten_id'] = ( isset( $p['hauptkonten_id'] ) ? $p['hauptkonten_id'] : 0 );
  }
  foreach( $pH as $n => $p ) {
    row2global( 'posten', $p, "pH$n" );
    $GLOBALS['pH'.$n.'_hauptkonten_id'] = ( isset( $p['hauptkonten_id'] ) ? $p['hauptkonten_id'] : 0 );
  }

} else {
  // werte aus uebergebenem formular einlesen:

  need_http_var( 'buchungen_id', 'u', true );
  need_http_var( 'nS', 'U' );
  need_http_var( 'nH', 'U' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  $valuta = sprintf( "%04u-%02u-%02u", $valuta_year, $valuta_month, $valuta_day );
  need_http_var( 'kommentar', 'h' );

  foreach( $pfields as $field => $pattern ) {
    $default = adefault( $jlf_defaults, $pattern, 0 );
    for( $n = 0; $n < $nS; $n++ )
      get_http_var( 'pS'.$n.'_'.$field, $pattern, $default );
    for( $n = 0; $n < $nH; $n++ )
      get_http_var( 'pH'.$n.'_'.$field, $pattern, $default );
  }

  // $action verarbeiten:
  //
  $a = explode( '_', $action );
  $action = $a[0];
  $message = ( count($a) > 1 ? sprintf( '%u', $a[1] ) : '0' );
  print_on_exit( "\n<!-- action:[$action], message:[$message] -->" );
  switch( $action ) {
    case 'update':
      // nop: just redisplay the form, with possibly updated entries
      break;
    case 'save':
      $values_buchungen = array(
        'valuta' => $valuta
      , 'kommentar' => $kommentar
      , 'buchungsdatum' => $mysqlheute
      );
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
          // $uk = sql_one_unterkonto( $unterkonten_id );
          // $summeS += $APSH_vorzeichen[ $uk['seite'].'S' ] * $betrag;
          $summeS += $betrag;
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
          // $uk = sql_one_unterkonto( $unterkonten_id );
          // $summeH += $APSH_vorzeichen[ $uk['seite'].'H' ] * $betrag;
          $summeH += $betrag;
        }
        $values_posten[] = array(
          'art' => 'H'
        , 'betrag' => $betrag
        , 'unterkonten_id' => $unterkonten_id
        , 'beleg' => $GLOBALS['pH'.$n.'_beleg']
        );
      }
      if( ! $problems ) {
        if( $summeH != $summeS ) {
          open_div( 'warn', '', "mismatch: H:$summeH, S:$summeS" );
          $problems = true;
          $problem_summe = 'problem';
        }
      }
      if( ! $problems ) {
        if( $buchungen_id ) {
          sql_update( 'buchungen', $buchungen_id, $values_buchungen );
          sql_delete( 'posten', array( 'buchungen_id' => $buchungen_id ) );
        } else {
          $buchungen_id = sql_insert( 'buchungen', $values_buchungen );
        }
        foreach( $values_posten as $v ) {
          $v['buchungen_id'] = $buchungen_id;
          sql_insert( 'posten', $v );
        }
      }
      break;
    case 'addS':
      $n = $nS++;
      row2global( 'posten', false, 'pS'.$n );
      $GLOBALS['pS'.$n.'_hauptkonten_id'] = 0;
      break;
    case 'addH':
      $n = $nH++;
      row2global( 'posten', false, 'pH'.$n );
      $GLOBALS['pH'.$n.'_hauptkonten_id'] = 0;
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
        $GLOBALS['pS'.$message.'_'.$f] = $GLOBALS['pS'.($message+1).'_'.$f];
        $message++;
      }
      $nS--;
      break;
    case 'deleteH':
      need( ( $nH > 1 ) && is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nH ) );
      while( $message < $nH-1 ) {
        $GLOBALS['pH'.$message.'_'.$f] = $GLOBALS['pH'.($message+1).'_'.$f];
        $message++;
      }
      $nH--;
      break;
  }
} 


open_form( '', 'action=update' );
  hidden_input( 'nS' );
  hidden_input( 'nH' );
  open_fieldset( 'small_form', '', 'Buchung ' . ( $buchungen_id ? "$buchungen_id" : '(neu)' ) );

    open_table( 'form' );
      open_tr();
        open_td( 'smallskip', '', 'Valuta:' );
        open_td( '', '', date_view( $valuta, 'valuta' ) );
      open_tr();
      open_tr();
        open_td( 'smallskip', '', 'Notiz:' );
        open_td();
          echo "<textarea name='kommentar' rows='3' cols='60'>$kommentar</textarea>";
      close_tr();
    close_table();
    bigskip();
    open_table( 'form' );
      open_tr( 'smallskips' );
        open_th( '', '', 'Kontoart' );
        open_th( '', '', 'Seite' );
        open_th( '', '', 'Geschaeftsbereich' );
        open_th( '', '', 'Hauptkonto' );
        open_th( '', '', 'Unterkonto' );
        open_th( '', '', 'Beleg' );
        open_th( $problem_summe, '', 'Betrag' );
        open_th( '', '', 'Aktionen' );
      for( $i = 0; $i < $nS ; $i++ ) {
        open_tr( 'smallskip' );
          form_row_posten( 'pS'.$i );
          open_td();
            if( $nS > 1 )
              submission_button( ' ', 'drop href', 'Posten wirklich loeschen?', 'deleteS_'.$i );
            if( $i > 0 )
              submission_button( ' ', 'uparrow href', '', 'upS_'.$i );
      }
      open_tr( 'smallskips' );
        open_td( 'right', "colspan='8'", html_submission_button( ' ', 'plus href', false, 'addS' ) );
      open_tr( 'medskip' );
        open_th( 'bold', "colspan='6'", 'an' );
      for( $i = 0; $i < $nH ; $i++ ) {
        open_tr( 'smallskips' );
        form_row_posten( 'pH'.$i );
          open_td();
            if( $nH > 1 )
              submission_button( ' ', 'drop href', 'Posten wirklich loeschen?', 'deleteH_'.$i );
            if( $i > 0 )
              submission_button( ' ', 'uparrow href', '', 'upH_'.$i );
      }
      open_tr( 'smallskips' );
        open_td( 'right', "colspan='8'", html_submission_button( ' ', 'plus href', false, 'addH' ) );
      open_tr( 'smallskips' );
        open_td( 'right medskip', "colspan='8'", html_submission_button( 'Speichern', 'button', '', 'save' ) );
    close_table();
  close_fieldset();
close_form();

?>
