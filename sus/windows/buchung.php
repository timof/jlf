<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');


function form_row_posten( $art, $n ) { // most info is taken from global variables!
  global $problem_summe, $geschaeftsjahr, $valuta, $flag_editable, $buchungen_id;

  $p = $GLOBALS["p$art"][ $n ];

  open_td('top');
    open_div( 'oneline quads' );
      if( $flag_editable ) {
        echo selector_kontenkreis( $p['kontenkreis'] );
        echo selector_seite( $p['seite'] );
      } else {
        echo "{$p['kontenkreis']['value']} {$p['seite']['value']}";
      }
    close_div();
    if( ( "{$p['kontenkreis']['value']}" == 'E' ) && $GLOBALS['unterstuetzung_geschaeftsbereiche'] ) {
      open_div( 'oneline smallskip quads' );
        if( $flag_editable ) {
          echo selector_geschaeftsbereich( $p['geschaeftsbereich'] );
        } else {
          echo $p['geschaeftsbereich']['value'];
        }
      close_div();
    }
  open_td('top');
    open_div( 'oneline quads' );
      if( $flag_editable ) {
        echo selector_hauptkonto( $p['hauptkonten_id'], array( 'filters' => $p['_filters'] ) );
      } else {
        $hk = sql_one_hauptkonto( $p['hauptkonten_id']['value'] );
        echo "{$hk['rubrik']} - {$hk['titel']}";
      }
    close_div();
    if( $p['hauptkonten_id']['value'] ) {
      open_div( 'oneline', inlink( 'hauptkonto', array(
        'class' => 'edit href', 'hauptkonten_id' => $p['hauptkonten_id']['value'], 'text' => 'bearbeiten...'
      ) ) );
    }
  open_td('top');
    if( $p['hauptkonten_id'] ) {
      open_div( 'oneline quads' );
        if( $flag_editable ) {
          echo selector_unterkonto( $p['unterkonten_id'], array( 'filters' => $p['_filters'] ) );
        } else {
          $uk = sql_one_unterkonto( $p['unterkonten_id']['value'] );
          echo "{$uk['cn']}";
        }
      close_div();
      if( ( $uk_id = $p['unterkonten_id']['value'] ) ) {
        open_div( 'oneline' );
          echo inlink( 'unterkonto', array( 'class' => 'edit href qquadr', 'unterkonten_id' => $p['unterkonten_id']['value'], 'text' => 'bearbeiten...' ) );
          if( $p['kontenkreis']['value'] === 'B' ) {
            $p['saldo']['value'] = sql_unterkonten_saldo( "unterkonten_id=$uk_id,geschaeftsjahr=$geschaeftsjahr,valuta<=$valuta,flag_ausgefuehrt,buchungen_id!=$buchungen_id" );
            if( $p['betrag']['value'] !== NULL ) {
              $p['saldo']['value'] += ( $p['betrag']['value'] * ( $p['seite']['value'] == 'P' ? 1 : -1 ) * ( $art === 'H' ? 1 : -1 ) );
            }
            $p['saldo']['auto'] = "nr=$n,action=setSaldo$art";
            echo "Saldo: " . ( $flag_editable ? price_element( $p['saldo'] ) : price_view( $p['saldo']['value'] ) );
          }
        close_div();
      }
    }
  if( $flag_editable ) {
    open_td('bottom oneline', string_element( $p['beleg'] ) );
    open_td("bottom oneline $problem_summe", price_element( $p['betrag'] ) );
  } else {
    open_td('bottom oneline', string_view( $p['beleg']['value'] ) );
    open_td("bottom oneline $problem_summe", price_view( $p['betrag']['value'] ) );
  }
}


init_var( 'flag_problems', 'type=b,sources=persistent,default=0,global,set_scopes=self' );

init_var( 'flag_editable', 'type=b,sources=http persistent,default=0,global,set_scopes=self' );

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
    $postenS = sql_posten( "buchungen_id=$buchungen_id,art=S" );
    $postenH = sql_posten( "buchungen_id=$buchungen_id,art=H" );
    $nS_init = max( 1, count( $postenS ) );
    $nH_init = max( 1, count( $postenH ) );
    $flag_modified = 1;
    $field_geschaeftsjahr = init_var( 'geschaeftsjahr', "global,sources=initval,set_scopes=self,initval={$buchung['geschaeftsjahr']}" );
  } else {
    $flag_editable = 1;
    $nS_init = 1;
    $nH_init = 1;
    $flag_modified = 0;
    $field_geschaeftsjahr = init_var( 'geschaeftsjahr', array(
      'global' => 1
    , 'sources' => 'http self initval'
    , 'set_scopes' => 'self'
    , 'min' => $geschaeftsjahr_min
    , 'initval' => $geschaeftsjahr_thread
    , 'default' => $geschaeftsjahr_min
    ) );
  }

  init_var( 'nS', "global,type=U,sources=$sources,set_scopes=self,initval=$nS_init" );
  init_var( 'nH', "global,type=U,sources=$sources,set_scopes=self,initval=$nH_init" );

  $flag_vortrag = 0;

  $abgeschlossen = ( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen );
  if( $abgeschlossen ) {
    $flag_editable = 0;
  }
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
      , 'type' => 'U4', 'min' => 100, 'max' => 1299
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
  , 'saldo' => 'type=f,format=%.2lf,size=8'
  );
  for( $n = 0; $n < $nS ; $n++ ) {
    $opts = $common_opts;
    $opts['tables'] = 'posten';
    $opts[ 'cgi_prefix' ] = "pS{$n}_";
    if( isset( $postenS[ $n ] ) ) {
      $opts[ 'rows' ] = array( 'posten' => $postenS[ $n ] );
    } else {
      unset( $opts['rows'] );
    }
    $pS[ $n ] = filters_kontodaten_prepare( $pfields, $opts );
  }
  for( $n = 0; $n < $nH ; $n++ ) {
    $opts = $common_opts;
    $opts['tables'] = 'posten';
    $opts[ 'cgi_prefix' ] = "pH{$n}_";
    if( isset( $postenH[ $n ] ) ) {
      $opts[ 'rows' ] = array( 'posten' => $postenH[ $n ] );
    } else {
      unset( $opts['rows'] );
    }
    $pH[ $n ] = filters_kontodaten_prepare( $pfields, $opts );
  }

  $reinit = false;

  $values_buchung = array(
    'valuta' => $valuta
  , 'geschaeftsjahr' => $geschaeftsjahr
  , 'vorfall' => $vorfall
  , 'flag_ausgefuehrt' => $flag_ausgefuehrt
  );

  if( $flag_editable ) {
    handle_actions( array( 'init', 'reset', 'save', 'addS', 'addH', 'setSaldoS', 'setSaldoH', 'deleteS', 'deleteH', 'upS', 'upH', 'fillH', 'fillS', 'template', 'deleteBuchung' ) );
  } else {
    handle_actions( array( 'template' ) );
  }
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
      $problem_summe = '';
      if( ! $error_messages ) {
        if( abs( $summeH - $summeS ) > 0.001 ) {
          $error_messages += new_problem("Bilanz nicht ausgeglichen");
          $problem_summe = 'problem';
        }
      }
      if( ! $error_messages ) {
        $error_messages = sql_buche( $buchungen_id , $values_buchung , $values_posten, 'allow_negative=1,action=dryrun' );
      }
      if( ! $error_messages ) {
        $buchungen_id = sql_buche( $buchungen_id , $values_buchung , $values_posten, 'allow_negative=1,action=hard' );
        js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
        $info_messages[] = 'Posten wurden verbucht';
        $flag_editable = 0;
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
      reinit('self');
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
      $pH[ $nr ]['betrag']['value'] = $pH[ $nr ]['betrag']['raw'] = $saldoS - $saldoH;
      $flag_problems = 0;
      reinit('self');
      break;
  
    case 'setSaldoS':
      need( ( $nr >= 0 ) && ( $nr < $nS ) );
      need( ( $uk_id = $pS[ $nr ]['unterkonten_id']['value'] ) );
      need( $pS[ $nr ]['kontenkreis']['value'] === 'B' );
      if( ( $saldo_soll = $pS[ $nr ]['saldo']['value'] ) === NULL ) {
        continue;
      }
      $saldo_ist = sql_unterkonten_saldo( "unterkonten_id=$uk_id,geschaeftsjahr=$geschaeftsjahr,valuta<=$valuta,flag_ausgefuehrt,buchungen_id!=$buchungen_id" );
      
      $pS[ $nr ]['betrag']['value'] = ( ( $pS[ $nr ]['seite']['value'] === 'A' ) ? ( $saldo_soll - $saldo_ist ) : ( $saldo_ist - $saldo_soll ) );
      $flag_problems = 0;
      reinit('self');
      break;
      
    case 'setSaldoH':
      need( ( $nr >= 0 ) && ( $nr < $nH ) );
      need( ( $uk_id = $pH[ $nr ]['unterkonten_id']['value'] ) );
      need( $pH[ $nr ]['kontenkreis']['value'] === 'B' );
      if( ( $saldo_soll = $pH[ $nr ]['saldo']['value'] ) === NULL ) {
        continue;
      }
      $saldo_ist = sql_unterkonten_saldo( "unterkonten_id=$uk_id,geschaeftsjahr=$geschaeftsjahr,valuta<=$valuta,flag_ausgefuehrt,buchungen_id!=$buchungen_id" );
      $pH[ $nr ]['betrag']['value'] = ( ( $pH[ $nr ]['seite']['value'] === 'P' ) ? ( $saldo_soll - $saldo_ist ) : ( $saldo_ist - $saldo_soll ) );
      $flag_problems = 0;
      reinit('self');
      break;

    case 'template':
      $buchungen_id = 0;
      for( $i = 0; $i < $nS ; $i++ ) {
        $pS[ $i ]['posten_id']['value'] = 0;
        $pS[ $i ]['posten_id']['beleg'] = '';
      }
      for( $i = 0; $i < $nH ; $i++ ) {
        $pH[ $i ]['posten_id']['value'] = 0;
        $pH[ $i ]['posten_id']['beleg'] = '';
      }
      $flag_editable = 1;
      $geschaeftsjahr = $geschaeftsjahr_thread;
      reinit('self');
      break;
  }

} while( $reinit );

if( $buchungen_id ) {
  open_fieldset( 'hfill old', "Buchung [$buchungen_id]" );
} else {
  open_fieldset( 'hfill new', 'neue Buchung' );
}
  open_div( 'oneline smallskips' );
    echo "Gesch{$aUML}ftsjahr: ";
    if( $buchungen_id ) {
      open_span( 'quadl bold', "$geschaeftsjahr" );
    } else {
      echo selector_geschaeftsjahr( $field_geschaeftsjahr );
    }
  close_div();

  if( $abgeschlossen && ( ! $buchungen_id ) ) {
    open_div( 'warn qqpads bigpads', "Jahr ist abgeschlossen - keine Buchung m{$oUML}glich" );
  } else {

    open_table('css td:quads;smallpads');
      open_tr();
        open_th( '', 'Valuta:' );
        if( $flag_editable ) {
          open_td( '', selector_valuta( $fields['valuta'], "geschaeftsjahr=$geschaeftsjahr" ) );
        } else {
          open_td( 'bold', $fields['valuta']['value'] );
        }

      open_tr();
        open_th( '', 'Status:' );
        if( $buchungen_id ) {
          if( $buchung['flag_ausgefuehrt'] ) {
            open_td( 'bold', "ausgef{$uUML}hrt" );
          } else {
            open_td( 'bold' );
            echo "geplante Buchung";
            if( $flag_editable ) {
              echo " -  ausf{$uUML}hren? " . checkbox_element( $fields['flag_ausgefuehrt'] );
            }
          }
        } else {
          open_td( 'bold', radiolist_element( $fields['flag_ausgefuehrt'], "choices=:geplant:ausgef{$uUML}hrt" ) );
        }
      open_tr();
         open_th( '', 'Vorfall:' );
         if( $flag_editable ) {
           open_td( '', textarea_element( $fields['vorfall'] ) );
         } else {
           open_td( '', $fields['vorfall']['value'] );
         }
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
          if( $flag_editable ) {
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
      }

      open_tr( 'medskip' );
        open_th( 'bold left smallpads solidtop solidbottom qquadl,colspan=6', 'an' );

      for( $i = 0; $i < $nH ; $i++ ) {
        open_tr( 'dottedbottom td:smallpads' );
          form_row_posten( 'H', $i );
          if( $flag_editable ) {
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
      }

    close_table();
  }

  open_div( 'right oneline smallskips' );
    if( $buchungen_id && have_priv( 'buchungen', 'create' ) ) {
      open_span( 'quads', template_button_view() );
    }
    if( $flag_editable ) {
      open_span( 'quads', reset_button_view() );
      if( $buchungen_id && have_priv( 'buchungen', 'delete', $buchungen_id ) ) {
        echo inlink( '!', array(
          'class' => 'drop button qquads'
        , 'action' => 'deleteBuchung'
        , 'text' => "Buchung l{$oUML}schen"
        , 'confirm' => "wirklich l{$oUML}schen?"
  //      , 'inactive' => sql_buche( $buchungen_id, $values_buchung, array(), 'action=dryrun' )
        ) );
      }
      if( have_priv( 'buchungen', $buchungen_id ? 'edit' : 'create', $buchungen_id ) ) {
        open_span( 'qquadl', save_button_view() );
      }
    } else if( ! $abgeschlossen ) {
      if( have_priv( 'buchungen', 'edit' ) ) {
        echo inlink( '!', array(
          'class' => 'edit button qquads'
        , 'text' => "Buchung bearbeiten"
        , 'flag_editable' => 1
        ) );
      }
    }
  close_div();

close_fieldset();

if( $action === 'deleteBuchung' ) {
  need( $buchungen_id );
  sql_buche( $buchungen_id, $values_buchung, array(), 'action=hard' );
  js_on_exit( "flash_close_message({$H_SQ}Buchung gelöscht{$H_SQ});" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}
  
?>
