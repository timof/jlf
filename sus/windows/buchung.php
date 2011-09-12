<?php


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
    if( "{$p['kontenkreis']}" == 'E' ) {
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



$pfields = array(
  'kontenkreis' => '/^[BE]?$/'
, 'seite' => '/^[AP]?$/'
, 'geschaeftsbereiche_id' => 'x'
, 'hauptkonten_id' => 'U'
, 'unterkonten_id' => 'U'
, 'betrag' => 'f'
, 'beleg' => 'h,size=30'
, 'posten_id' => 'u'  // to compare with previously saved posten
);

init_var( 'flag_problems', 'pattern=u,sources=self,default=0,global,set_scopes=self' );


do { // re-init loop


  if( ( $parent_script !== 'self' ) || ( $action === 'reset' ) ) {

    // initialize working copy only once:

    init_var( 'buchungen_id', 'global,pattern=u,sources=self http,default=0,set_scopes=self' );

    if( $buchungen_id ) {
      $postenS = sql_posten( "buchungen_id=$buchungen_id,art=S" );
      $postenH = sql_posten( "buchungen_id=$buchungen_id,art=H" );
      init_var( 'nS', 'global,pattern=U,sources=,set_scopes=self,default='.count( $postenS ) );
      init_var( 'nH', 'global,pattern=U,sources=,set_scopes=self,default='.count( $postenH ) );
      init_var( 'geschaeftsjahr', 'global,pattern=U,sources=,set_scopes=self,default='.$postenS[ 0 ]['geschaeftsjahr'] );
    } else {
      $postenS = array();
      $postenH = array();
      init_var( 'nS', 'global,pattern=U,sources=,set_scopes=self,default=1' );
      init_var( 'nH', 'global,pattern=U,sources=,set_scopes=self,default=1' );
      init_var( 'geschaeftsjahr', 'global,pattern=U,sources=,set_scopes=self,default='.$geschaeftsjahr_thread );
      need( $geschaeftsjahr, 'kein geschaeftsjahr gewaehlt' );
      if( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen ) {
        div_msg( 'warn', 'Geschaeftsjahr abgeschlossen - keine Buchung moeglich' );
        return;
      }
    }

  } else {

    init_var( 'buchungen_id', 'global,pattern=u,sources=self,default=0,set_scopes=self' );
    init_var( 'nS', 'global,pattern=U,sources=self,set_scopes=self' );
    init_var( 'nH', 'global,pattern=U,sources=self,set_scopes=self' );
    init_var( 'geschaeftsjahr', 'global,pattern=U,sources=persistent,set_scopes=self' );
  }

  $buchung = sql_one_buchung( $buchungen_id, array() );
  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'rows' => array( 'buchungen' => $buchung )
  , 'tables' => 'buchungen'
  , 'global' => true
  , 'failsafe' => false
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $action === 'reset' ) {
    $opts['reset'] = 1;
    $flag_problems = 0;
  }
  $fields = init_fields( array(
      'valuta' => array( 'default' => ( $valuta_letzte_buchung ? $valuta_letzte_buchung : sprintf( '%02u%02u', $now[1], $now[2] ) ) )
    , 'vorfall' => 'h,rows=2,cols=80'
    , 'beleg' => 'h'
    )
  , $opts
  );

  $is_vortrag = 0;

  $geschlossen = ( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen );

  $common_opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'posten'
  , 'failsafe' => false
  , 'auto_select_unique' => true
  , 'set_scopes' => false // not yet!!!
  );
  if( $action === 'reset' ) {
    $common_opts['reset'] = 1;
  }
  for( $n = 0; $n < $nS ; $n++ ) {
    $opts = $common_opts;
    $opts[ 'prefix' ] = 'pS'.$n.'_';
    if( ( $parent_script !== 'self' ) || ( $action === 'reset' ) ) {
      $opts[ 'rows' ] = ( isset( $postenS[ $n ] ) ? array( 'posten' => $postenS[ $n ] ) : array() );
    } else {
      $id_field = init_var( "pS{$n}_posten_id", 'pattern=u,default=0,sources=persistent' );
      if( $id_field['value'] ) {
        $opts[ 'rows' ] = array( 'posten' => sql_one_posten( $id_field['value'] ) );
      }
    }
    $pS[ $n ] = filters_kontodaten_prepare( $pfields, $opts );
  }
  for( $n = 0; $n < $nH ; $n++ ) {
    $opts = $common_opts;
    $opts[ 'prefix' ] = 'pH'.$n.'_';
    if( ( $parent_script !== 'self' ) || ( $action === 'reset' ) ) {
      $opts[ 'rows' ] = ( isset( $postenH[ $n ] ) ? array( 'posten' => $postenH[ $n ] ) : array() );
    } else {
      $id_field = init_var( "pH{$n}_posten_id", 'pattern=u,default=0,sources=persistent' );
      if( $id_field['value'] ) {
        $opts[ 'rows' ] = array( 'posten' => sql_one_posten( $id_field['value'] ) );
      }
    }
    $pH[ $n ] = filters_kontodaten_prepare( $pfields, $opts );
  }

  $problem_summe = '';

  $reinit = false;

  handle_action( array( 'init', 'update', 'reset', 'save', 'addS', 'addH', 'deleteS', 'deleteH', 'upS', 'upH', 'fillH', 'fillS', 'template' ) );
  switch( $action ) {
    case 'save':
      $summeS = 0.0;
      $summeH = 0.0;
      $values_posten = array();
      for( $n = 0; $n < $nS; $n++ ) {
        if( $pS[ $n ]['_problems'] ) {
          $problems[] = "Posten S $n: Angaben fehlerhaft";
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
          $is_vortrag = 1;
        }
        if( $uk['unterkonto_geschlossen'] ) {
          $problems[] = "Posten S $n: Unterkonto geschlossen";
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
          $problems[] = "Posten H $n: Angaben fehlerhaft";
          continue;
        }
        $unterkonten_id = $pH[ $n ]['unterkonten_id']['value'];
        $betrag = sprintf( '%.2lf', $pH[ $n ]['betrag']['value'] );
        $summeH += $betrag;
        $uk = sql_one_unterkonto( $unterkonten_id );
        if( $uk['vortragskonto'] ) {
          $is_vortrag = 1;
        }
        if( $uk['unterkonto_geschlossen'] ) {
          $problems[] = "Posten H $n: Unterkonto geschlossen";
          $pH[ $n ]['unterkonten_id']['class'] = 'problem';
        }
        $values_posten[] = array(
          'art' => 'H'
        , 'betrag' => $betrag
        , 'unterkonten_id' => $unterkonten_id
        , 'beleg' => $pH[ $n ]['beleg']['value']
        );
      }
      if( ! $is_vortrag ) {
        if( ( $valuta < 100 ) || ( $valuta > 1231 ) ) {
          $problems[] = 'Valuta ung'.H_AMP.'uuml;ltig';
          $fields['valuta']['class'] = 'problem';
        }
      }
      $problem_summe = '';
      if( ! $problems ) {
        if( abs( $summeH - $summeS ) > 0.001 ) {
          $problems[] = "Bilanz nicht ausgeglichen";
          $problem_summe = 'problem';
        }
      }
      if( ! $problems ) {
        debug( $values_posten, 'buche: values_posten' );
        // $buchungen_id = sql_buche( $buchungen_id, $valuta, $vorfall, $values_posten );
        reinit();
      }
      break;

    case 'addS':
      $pS[ $nS ] = filters_kontodaten_prepare( $pfields, 'failsafe=0,tables=posten,sources=default,set_scopes=,prefix=pS'.$nS.'_' );
      $nS++;
      $flag_problems = 0;
      break;

    case 'addH':
      $pH[ $nH ] = filters_kontodaten_prepare( $pfields, 'failsafe=0,tables=posten,sources=default,set_scopes=,prefix=pH'.$nH.'_' );
      $nH++;
      $flag_problems = 0;
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
      $flag_problems = 0;
      break;

    case 'deleteH':
      need( ( $nH > 1 ) && is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nH ) );
      while( $message < $nH - 1 ) {
        $pH[ $message ] = $pH[ $message + 1 ];
        $message++;
      }
      $nH--;
      $flag_problems = 0;
      break;

    case 'fillS':
      need( is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nS ) );
      for( $i = 0, $saldoS = 0.0; $i < $nS; $i++ ) {
        if( $i == $message )
          continue;
        $saldoS += $pS[ $i ]['betrag']['value'];
      }
      for( $i = 0, $saldoH = 0.0; $i < $nH; $i++ ) {
        $saldoH += $pH[ $i ]['betrag']['value'];
      }
      $pS[ $message ]['betrag']['value'] = $pS[ $message ]['betrag']['raw'] = $saldoH - $saldoS;
      break;
  
    case 'fillH':
      need( is_numeric( $message ) && ( $message >= 0 ) && ( $message < $nH ) );
      for( $i = 0, $saldoS = 0.0; $i < $nS; $i++ ) {
        $saldoS += $pS[ $i ]['betrag']['value'];
      }
      for( $i = 0, $saldoH = 0.0; $i < $nH; $i++ ) {
        if( $i == $message )
          continue;
        $saldoH += $pH[ $i ]['betrag']['value'];
      }
      $pH[ $message ]['betrag']['value'] = $pH[ $message ]['betrag']['raw'] = $saldoS - $saldoH;
      break;
  
    case 'template':
      $buchungen_id = 0;
      for( $i = 0; $i < $nS ; $i++ ) {
        $pS[ $n ]['posten_id']['value'] = 0;
      }
      for( $i = 0; $i < $nH ; $i++ ) {
        $pH[ $n ]['posten_id']['value'] = 0;
      }
      break;
  }


} while( $reinit );


// debug( $pS[ 0 ]['unterkonten_id'], 'pS[ 0 ][unterkonten_id]' );

foreach( $pfields as $name => $r ) {
  for( $i = 0; $i < $nS ; $i++ ) {
    $jlf_persistent_vars[ 'self' ][ 'pS'.$i.'_'.$name ] = & $pS[ $i ][ $name ]['value'];
  }
  for( $i = 0; $i < $nH ; $i++ ) {
    $jlf_persistent_vars[ 'self' ][ 'pH'.$i.'_'.$name ] = & $pH[ $i ][ $name ]['value'];
  }
}


if( $buchungen_id ) {
  open_fieldset( 'small_form old', "Buchung [$buchungen_id]" );
} else {
  open_fieldset( 'small_form new', 'neue Buchung' );
}
    open_table();

      open_tr( ( $is_vortrag ? '' : 'nodisplay' ) . ',id=valuta_vortrag' );
        open_td( 'center,colspan=2', 'Vortrag' );

      open_tr( ( $is_vortrag ? 'nodisplay' : '' ) . ',id=valuta_normal' );
        open_td( array( 'label' => $fields['valuta'] ), 'Valuta:' );
        open_td( "qquad", monthday_element( $fields['valuta'] ) );
        open_td( 'qquads', "Geschaeftsjahr: $geschaeftsjahr" );

      open_tr();
        open_td( array( 'label' => $fields['vorfall'] ), 'Vorfall:' );
        open_td( 'qquad,colspan=2', textarea_element( $fields['vorfall'] ) );
      close_tr();
    close_table();
    bigskip();
    open_table( 'form' );
      open_tr( 'smallskips' );
        open_th( 'top' );
          open_div( 'tight', 'Kontenkreis / Seite' );
          open_div( 'tight', 'Geschaeftsbereich' );
        open_th( 'top' );
          open_div( 'tight', 'Hauptkonto' );
        open_th( 'top' );
          open_div( 'tight', 'Unterkonto' );
        open_th( 'top', 'Beleg' );
        open_th( "top $problem_summe", 'Betrag' );
        open_th( 'top', 'Aktionen' );
      for( $i = 0; $i < $nS ; $i++ ) {
        open_tr( 'solidbottom smallskips ' );
          form_row_posten( 'S', $i );
          open_td( 'bottom' );
            echo action_button_view( "action=fillS_$i,class=equal href" );
            if( $nS > 1 )
              echo action_button_view( "action=deleteS_$i,class=drop href,confirm=Posten wirklich loeschen?" );
            if( $i > 0 )
              echo action_button_view( "action=upS_$i,class=uparrow href" );
      }
      open_tr( 'smallskips' );
        open_td( 'right,colspan=6', action_button_view( 'action=addS,class=plus href' ) );

      open_tr( 'medskip' );
        open_th( 'bold,colspan=6', 'an' );

      for( $i = 0; $i < $nH ; $i++ ) {
        open_tr( 'smallskips solidbottom' );
          form_row_posten( 'H', $i );
          open_td( 'bottom' );
            echo action_button_view( "action=fillH_$i,class=equal href" );
            if( $nH > 1 )
              echo action_button_view( "action=deleteH_$i,class=drop href,confirm=Posten wirklich loeschen?" );
            if( $i > 0 )
              echo action_button_view( "action=upH_$i,class=uparrow href" );
      }
      open_tr( 'smallskips' );
        open_td( 'right,colspan=6', action_button_view( 'action=addH,class=plus href' ) );

    if( $problems ) {
      open_tr( 'smallskips' );
        open_td( 'medskip,colspan=6' );
          open_ul();
            flush_problems();
          close_ul();
    }

      open_tr( 'smallskips' );
        open_td( 'right medskip,colspan=6' );
          if( $buchungen_id )
            open_span( 'quads', action_button_view( 'action=template,text=als Vorlage benutzen' ) );
          open_span( 'quads', action_button_view( 'action=save,text=Speichern' ) );
          reset_button( 'text=Reset' );

    close_table();
close_fieldset();

// debug( $nH, 'nH' );

if( $is_vortrag ) {
  js_on_exit( "$({$H_SQ}valuta_normal{$H_SQ}).style.display = {$H_SQ}none{$H_SQ};" );
  js_on_exit( "$({$H_SQ}valuta_vortrag{$H_SQ}).style.display = {$H_SQ}{$H_SQ};" );
} else {
  js_on_exit( "$({$H_SQ}valuta_normal{$H_SQ}).style.display = {$H_SQ}{$H_SQ};" );
  js_on_exit( "$({$H_SQ}valuta_vortrag{$H_SQ}).style.display = {$H_SQ}none{$H_SQ};" );
}

?>
