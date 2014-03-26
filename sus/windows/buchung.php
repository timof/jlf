<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');


function form_row_posten( $art, $n ) { // most info is taken from global variables!
  global $problem_summe, $geschaeftsjahr, $abgeschlossen;

  $p = $GLOBALS["p$art"][ $n ];

  open_td('top');
    open_div( 'oneline' );
      if( $abgeschlossen ) {
        echo "{$p['kontenkreis']['value']} {$p['seite']['value']}";
      } else {
        echo selector_kontenkreis( $p['kontenkreis'] );
        echo selector_seite( $p['seite'] );
      }
    close_div();
    if( ( "{$p['kontenkreis']['value']}" == 'E' ) && $GLOBALS['unterstuetzung_geschaeftsbereiche'] ) {
      open_div( 'oneline smallskip' );
        if( $abgeschlossen ) {
          echo $p['geschaeftsbereich']['value'];
        } else {
          echo selector_geschaeftsbereich( $p['geschaeftsbereich'] );
        }
      close_div();
    }
  open_td('top');
    open_div( 'oneline' );
      if( $abgeschlossen ) {
        echo "{$p['rubrik']} - {$p['titel']}";
      } else {
        echo selector_hauptkonto( $p['hauptkonten_id'], array( 'filters' => $p['_filters'] ) );
      }
    close_div();
    if( $p['hauptkonten_id']['value'] ) {
      open_div( 'oneline', inlink( 'hauptkonto', array(
        'class' => 'href', 'hauptkonten_id' => $p['hauptkonten_id']['value'], 'text' => 'zum Hauptkonto...'
      ) ) );
    }
  open_td('top');
    if( $p['hauptkonten_id'] ) {
      open_div( 'oneline' );
        if( $abgeschlossen ) {
          echo "{$p['unterkonten_cn']}";
        } else {
          echo selector_unterkonto( $p['unterkonten_id'], array( 'filters' => $p['_filters'] ) );
        }
      close_div();
      if( $p['unterkonten_id']['value'] ) {
        open_div( 'oneline', inlink( 'unterkonto', array(
          'class' => 'href', 'unterkonten_id' => $p['unterkonten_id']['value'], 'text' => 'zum Unterkonto...'
        ) ) );
      }
    }
  open_td('bottom oneline', string_element( $p['beleg'] ) );
  open_td("bottom oneline $problem_summe", price_element( $p['betrag'] ) );
}




init_var( 'flag_problems', 'type=u,sources=persistent,default=0,global,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

do { // re-init loop

  switch( $reinit ) {
    case 'init':
      $sources = 'http self initval default';
      break;
    case 'self':
      $sources = 'self initval default';  // need 'initval' here for big blobs!
      break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'initval default';
      break;
    default:
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'hauptkonten,init' );
  }
  $reinit = false;

  init_var( 'buchungen_id', "global,type=u,sources=self http,set_scopes=self" );
  if( $buchungen_id ) {
    $buchung = sql_one_buchung( $buchungen_id );
    $flag_modified = 1;
    $field_geschaeftsjahr = init_var( 'geschaeftsjahr', "global,sources=initval,set_scopes=self,initval={$buchung['geschaeftsjahr']}" );
  } else {
    $flag_modified = 0;
    $field_geschaeftsjahr = init_var( 'geschaeftsjahr', "global,sources=http self initval,set_scopes=self,initval={$geschaeftsjahr_thread}" );
  }

  init_var( 'nS', "global,type=U,sources=$sources,set_scopes=self,initval=1" );
  init_var( 'nH', "global,type=U,sources=$sources,set_scopes=self,initval=1" );

  $flag_vortrag = 0;

  $abgeschlossen = ( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen );
  $problem_summe = '';

  if( $action === 'save' ) {
    $flag_problems = 1;
  }

  $common_opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => & $flag_modified
  , 'tables' => 'buchung,posten'
  , 'failsafe' => false
  , 'auto_select_unique' => true
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );

  $opts = $common_opts;
  $opts['tables'] = 'buchungen';
  $opts['global'] = true;
  if( $buchungen_id ) {
    $opts['rows'] = array( 'buchungen' => $buchung );
  }
  $opts['set_scopes'] = 'self';
  $fields = init_fields( array(
      'valuta' => array(
        'default' => sprintf( '%04u', ( $valuta_letzte_buchung ? $valuta_letzte_buchung : 100 * $now[1] + $now[2] ) )
      , 'type' => 'U', 'min' => 100, 'max' => 1299, 'format' => '%04u'
      )
    , 'vorfall' => 'h,lines=2,cols=80'
    , 'flag_ausgefuehrt' => 'b,default=1'
    )
  , $opts
  );

  $pfields = array(
    'kontenkreis' => 'pattern=/^[BE]$/'
  , 'seite' => 'pattern=/^[AP]$/'
  , 'geschaeftsbereich' => 'h'
  , 'hauptkonten_id' => 'U'
  , 'unterkonten_id' => 'U'
  , 'betrag' => 'type=f,format=%.2lf'
  , 'beleg' => 'h,size=30'
  , 'posten_id' => 'u'  // to compare with previously saved posten
  );
  for( $n = 0; $n < $nS ; $n++ ) {
    $opts = $common_opts;
    $opts['tables'] = 'posten';
    $opts[ 'cgi_prefix' ] = "pS{$n}_";
    switch( $reinit ) {
      case 'init':
      case 'reset':
        if( $buchungen_id ) {
          $opts[ 'rows' ] = array( 'posten' => $postenS[ $n ] );
        }
        break;
      case 'self':
        // check whether this posten was saved before - only used to flag modifications!
        $id_field = init_var( "pS{$n}_posten_id", 'type=u,default=0,sources=persistent' );
        if( $id_field['value'] ) {
          $opts[ 'rows' ] = array( 'posten' => sql_one_posten( $id_field['value'], array( 'default' => array() ) ) );
        }
        break;
    }
    $pS[ $n ] = filters_kontodaten_prepare( $pfields, $opts );
  }
  for( $n = 0; $n < $nH ; $n++ ) {
    $opts = $common_opts;
    $opts['tables'] = 'posten';
    $opts[ 'cgi_prefix' ] = "pH{$n}_";
    switch( $reinit ) {
      case 'init':
      case 'reset':
        if( $buchungen_id ) {
          $opts[ 'rows' ] = array( 'posten' => $postenH[ $n ] );
        }
        break;
      case 'self':
        // check whether this posten was saved before - only used to flag modifications!
        $id_field = init_var( "pH{$n}_posten_id", 'type=u,default=0,sources=persistent' );
        if( $id_field['value'] ) {
          $opts[ 'rows' ] = array( 'posten' => sql_one_posten( $id_field['value'], array( 'default' => array() ) ) );
        }
        break;
    }
    $pH[ $n ] = filters_kontodaten_prepare( $pfields, $opts );
  }

  $reinit = false;

  handle_actions( array( 'init', 'reset', 'save', 'addS', 'addH', 'deleteS', 'deleteH', 'upS', 'upH', 'fillH', 'fillS', 'template' ) );
  init_var( 'nr', 'global,type=u,sources=http' );
  if( $action ) switch( $action ) {
    case 'save':
      $summeS = 0.0;
      $summeH = 0.0;
      $values_posten = array();
      for( $n = 0; $n < $nS; $n++ ) {
        if( $pS[ $n ]['_problems'] ) {
          $error_messages += new_problem("Posten S $n: Angaben fehlerhaft");
          continue;
        }
        $unterkonten_id = $pS[ $n ]['unterkonten_id']['value'];
        $betrag = sprintf( '%.2lf', $pS[ $n ]['betrag']['value'] );
        $summeS += $betrag;
        // if( ! ( $betrag > 0.001 ) ) {
          // open_div( 'warn', '', "betrag (S)" );
        //  $problems = true;
        //  $problem_summe = 'problem';
        // }
        $uk = sql_one_unterkonto( $unterkonten_id );
        if( $uk['vortragskonto'] ) {
          $flag_vortrag = 1;
        }
        if( ! $uk['flag_unterkonto_offen'] ) {
          $error_messages += new_problem("Posten S $n: Unterkonto geschlossen");
          $pS[ $n ]['unterkonten_id']['class'] = 'problem';
        }
        $values_posten[] = array(
          'art' => 'S'
        , 'betrag' => $betrag
        , 'unterkonten_id' => $unterkonten_id
        , 'beleg' => $pS[ $n ]['beleg']['value']
        );
      }
      for( $n = 0; $n < $nH; $n++ ) {
        if( $pH[ $n ]['_problems'] ) {
          $error_messages += new_problem("Posten H $n: Angaben fehlerhaft");
          continue;
        }
        $unterkonten_id = $pH[ $n ]['unterkonten_id']['value'];
        $betrag = sprintf( '%.2lf', $pH[ $n ]['betrag']['value'] );
        $summeH += $betrag;
        $uk = sql_one_unterkonto( $unterkonten_id );
        if( $uk['vortragskonto'] ) {
          $flag_vortrag = 1;
        }
        if( ! $uk['flag_unterkonto_offen'] ) {
          $error_messages += new_problem("Posten H $n: Unterkonto geschlossen");
          $pH[ $n ]['unterkonten_id']['class'] = 'problem';
        }
        $values_posten[] = array(
          'art' => 'H'
        , 'betrag' => $betrag
        , 'unterkonten_id' => $unterkonten_id
        , 'beleg' => $pH[ $n ]['beleg']['value']
        );
      }
      if( ! $flag_vortrag ) {
        if( ( $valuta < 100 ) || ( $valuta > 1231 ) ) {
          $error_messages += new_problem("Valuta ung{$uUML}ltig");
          $fields['valuta']['class'] = 'problem';
        }
      }
      $problem_summe = '';
      if( ! $error_messages ) {
        if( abs( $summeH - $summeS ) > 0.001 ) {
          $error_messages += new_problem("Bilanz nicht ausgeglichen");
          $problem_summe = 'problem';
        }
      }
      $values_buchung = array(
        'valuta' => $valuta
      , 'geschaeftsjahr' => $geschaeftsjahr
      , 'vorfall' => $vorfall
      , 'flag_ausgefuehrt' => $flag_ausgefuehrt
      );
      if( ! $error_messages ) {
        $error_messages = sql_buche( $buchungen_id , $values_buchung , $values_posten, 'action=dryrun' );
      }
      if( ! $error_messages ) {
        $buchungen_id = sql_buche( $buchungen_id , $values_buchung , $values_posten, 'action=dryrun' );
        reinit( 'reset' );
      }
      break;

    case 'addS':
      $pS[ $nS++ ] = filters_kontodaten_prepare( $pfields, "failsafe=0,tables=posten,sources=default,set_scopes=self,cgi_prefix=pS{$nS}_" );
      $flag_problems = 0;
      break;

    case 'addH':
      $pH[ $nH++ ] = filters_kontodaten_prepare( $pfields, "failsafe=0,tables=posten,sources=default,set_scopes=,cgi_prefix=pH{$nH}_" );
      $flag_problems = 0;
      break;

    case 'upS':
      need( ( $nr >= 1 ) && ( $nr < $nS ) );
      $n2 = $nr - 1;
      mv_persistent_vars( 'self', "/^pS{$n2}_/", "pTMP_" );
      mv_persistent_vars( 'self', "/^pS{$nr}_/", "pS{$n2}_" );
      mv_persistent_vars( 'self', "/^pTMP_/", "pS{$nr}_" );
      reinit('self');
      break;

    case 'upH':
      need( ( $nr >= 1 ) && ( $nr < $nH ) );
      $n2 = $nr - 1;
      mv_persistent_vars( 'self', "/^pH{$n2}_/", "pTMP_" );
      mv_persistent_vars( 'self', "/^pH{$nr}_/", "pH{$n2}_" );
      mv_persistent_vars( 'self', "/^pTMP_/", "pH{$nr}_" );
      reinit('self');
      break;

    case 'deleteS':
      need( ( $nS > 1 ) && ( $nr >= 0 ) && ( $nr < $nS ) );
      while( $nr < $nS - 1 ) {
        $n2 = $nr + 1;
        mv_persistent_vars( 'self', "/^pS{$n2}_/", "pS{$nr}_" );
        $nr++;
      }
      $nS--;
      $flag_problems = 0;
      reinit('self');
      break;

    case 'deleteH':
      need( ( $nH > 1 ) && ( $nr >= 0 ) && ( $nr < $nH ) );
      while( $nr < $nH - 1 ) {
        $n2 = $nr + 1;
        mv_persistent_vars( 'self', "/^pH{$n2}_/", "pH{$nr}_" );
        $nr++;
      }
      $nH--;
      $flag_problems = 0;
      reinit('self');
      break;

    case 'fillS':
      need( ( $nr >= 0 ) && ( $nr < $nS ) );
      for( $i = 0, $saldoS = 0.0; $i < $nS; $i++ ) {
        if( $i == $nr ) {
          continue;
        }
        $saldoS += $pS[ $i ]['betrag']['value'];
      }
      for( $i = 0, $saldoH = 0.0; $i < $nH; $i++ ) {
        $saldoH += $pH[ $i ]['betrag']['value'];
      }
      $pS[ $nr ]['betrag']['value'] = $pS[ $nr ]['betrag']['raw'] = $saldoH - $saldoS;
      $flag_problems = 0;
      break;
  
    case 'fillH':
      need( ( $nr >= 0 ) && ( $nr < $nH ) );
      for( $i = 0, $saldoS = 0.0; $i < $nS; $i++ ) {
        $saldoS += $pS[ $i ]['betrag']['value'];
      }
      for( $i = 0, $saldoH = 0.0; $i < $nH; $i++ ) {
        if( $i == $nr ) {
          continue;
        }
        $saldoH += $pH[ $i ]['betrag']['value'];
      }
      $pH[ $nr ]['betrag']['value'] = $pH[ $message ]['betrag']['raw'] = $saldoS - $saldoH;
      $flag_problems = 0;
      break;
  
    case 'template':
      $buchungen_id = 0;
      for( $i = 0; $i < $nS ; $i++ ) {
        $pS[ $i ]['posten_id']['value'] = 0;
      }
      for( $i = 0; $i < $nH ; $i++ ) {
        $pH[ $i ]['posten_id']['value'] = 0;
      }
      break;
  }


} while( $reinit );

if( $buchungen_id ) {
  open_fieldset( 'hfill old', "Buchung [$buchungen_id]" );
} else {
  open_fieldset( 'hfill new', 'neue Buchung' );
}
    open_table('css td:quads;smallpads');
      open_tr();
        open_th( '', "Gesch{$aUML}ftsjahr:" );
        if( $buchungen_id ) {
          open_td( 'bold', "$geschaeftsjahr" );
        } else {
          open_td( '', selector_geschaeftsjahr( $field_geschaeftsjahr ) );
        }

      open_tr();
        open_th( '', 'Valuta:' );
        open_td( '', selector_valuta( $fields['valuta'], "geschaeftsjahr=$geschaeftsjahr" ) );

      open_tr();
        open_th( '', 'Status:' );
        if( $buchungen_id ) {
          if( $buchung['flag_ausgefuehrt'] ) {
            open_td( 'bold', "ausgef{$uUML}hrt" );
          } else {
            open_td( 'bold', "geplante Buchung -  ausf{$uUML}hren? " . checkbox_element( $fields['flag_ausgefuehrt'] ) );
          }
        } else {
          open_td( 'bold', radiolist_element( $fields['flag_ausgefuehrt'], "choices=:geplant:ausgef{$uUML}hrt" ) );
        }
      open_tr();
         open_th( '', 'Vorfall:' );
         open_td( '', textarea_element( $fields['vorfall'] ) );
    close_table();

    open_table( 'form medskipt td:smallpads;quads' );
      open_tr( 'smallskips th:smallpadb;solidbottom' );
        open_th( 'top th:solidbottom' );
          open_div( 'tight', 'Kontenkreis / Seite' );
          open_div( 'tight', "Gesch{$aUML}ftsbereich" );
        open_th( 'top' );
          open_div( 'tight', 'Hauptkonto' );
        open_th( 'top' );
          open_div( 'tight', 'Unterkonto' );
        open_th( 'top', 'Beleg' );
        open_th( "top $problem_summe", 'Betrag' );
        open_th( 'top', 'Aktionen' );
      for( $i = 0; $i < $nS ; $i++ ) {
        open_tr( 'dottedbottom td:smallpads' );
          form_row_posten( 'S', $i );
          open_td( 'bottom' );
            echo inlink( '!', "action=fillS,nr=$i,class=icon equal quads" );
            if( $nS > 1 ) {
              echo inlink( '!', "action=deleteS,nr=$i,class=icon drop quads,confirm=Posten wirklich l{$oUML}schen?" );
            }
            if( $i == 0 ) {
              echo inlink( '!', 'action=addS,class=icon plus quads' );
            } else {
              echo inlink( '!', "action=upS,nr=$i,class=icon uparrow quads" );
            }
      }

      open_tr( 'medskip' );
        open_th( 'bold left smallpads solidtop solidbottom qquadl,colspan=6', 'an' );

      for( $i = 0; $i < $nH ; $i++ ) {
        open_tr( 'dottedbottom td:smallpads' );
          form_row_posten( 'H', $i );
          open_td( 'bottom' );
            echo inlink( '!', "action=fillH,nr=$i,class=icon equal quads" );
            if( $nH > 1 ) {
              echo inlink( '!', "action=deleteH,nr=$i,class=icon drop quads,confirm=Posten wirklich l{$oUML}schen?" );
            }
            if( $i == 0 ) {
              echo inlink( '!', 'action=addH,class=icon plus quads' );
            } else {
              echo inlink( '!', "action=upH,nr=$i,class=icon uparrow quads" );
            }
      }

  close_table();
  if( $info_messages || $error_messages ) {
    open_ul('inline_block');
      flush_all_messages( 'tag=li' );
    close_ul();
  }

  open_div( 'right oneline smallskips' );
    if( $buchungen_id ) {
      open_span( 'quads', template_button_view() );
    }
    open_span( 'quads', save_button_view() );
    open_span( 'quads', reset_button_view() );
  close_div();

close_fieldset();

?>
