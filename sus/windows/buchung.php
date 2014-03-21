<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

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

  init_var( 'buchungen_id', "global,type=u,sources=$sources,set_scopes=self" );
  if( $buchungen_id ) {
    $buchung = sql_one_buchung( $buchungen_id );
    $flag_modified = 1;
    init_var( 'geschaeftsjahr', "global,sources=initval,set_scopes=self,initval={$buchung['geschaeftsjahr']}" );
  } else {
    $flag_modified = 0;
    init_var( 'geschaeftsjahr', "global,sources=http self initval,set_scopes=self,initval={$geschaeftsjahr_thread}" );
  }

  init_var( 'nS', "global,type=U,sources=$sources,set_scopes=self,init_val=1" );
  init_var( 'nH', "global,type=U,sources=$sources,set_scopes=self,init_val=1" );

  $is_vortrag = 0;
  $is_gewinnverwendung = 0;

  $abgeschlossen = ( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen );
  $problem_summe = '';

  if( $action === 'save' ) {
    $flag_problems = 1;
  }

  $common_opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => & $flag_modified
  , 'tables' => 'posten'
  , 'failsafe' => false
  , 'auto_select_unique' => true
  , 'sources' => $sources
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
    )
  , $opts
  );

  $pfields = array(
    'kontenkreis' => '/^[BE]$/'
  , 'seite' => '/^[AP]$/'
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
    $opts['set_scopes'] = false;  // not yet!
    $opts[ 'prefix' ] = 'pS'.$n.'_';
    switch( $reinit ) {
      case 'init':
      case 'reset':
        if( $buchungen_id ) {
          $opts[ 'rows' ] = array( 'posten' => $postenS[ $n ] );
        }
        break;
      case '':
        // check whether this posten was saved before - only used to flag modifications!
        $id_field = init_var( "pS{$n}_posten_id", 'type=u,default=0,sources=persistent' );
        if( $id_field['value'] ) {
          $opts[ 'rows' ] = array( 'posten' => sql_one_posten( $id_field['value'], array() ) );
        }
        break;
    }
    $pS[ $n ] = filters_kontodaten_prepare( $pfields, $opts );
  }
  for( $n = 0; $n < $nH ; $n++ ) {
    $opts = $common_opts;
    $opts['tables'] = 'posten';
    $opts['set_scopes'] = false;  // not yet!
    $opts[ 'prefix' ] = 'pH'.$n.'_';
    switch( $reinit ) {
      case 'init':
      case 'reset':
        if( $buchungen_id ) {
          $opts[ 'rows' ] = array( 'posten' => $postenH[ $n ] );
        }
        break;
      case '':
        // check whether this posten was saved before - only used to flag modifications!
        $id_field = init_var( "pH{$n}_posten_id", 'type=u,default=0,sources=persistent' );
        if( $id_field['value'] ) {
          $opts[ 'rows' ] = array( 'posten' => sql_one_posten( $id_field['value'], array() ) );
        }
        break;
    }
    $pH[ $n ] = filters_kontodaten_prepare( $pfields, $opts );
  }

  $reinit = false;


  handle_actions( array( 'init', 'reset', 'save', 'addS', 'addH', 'deleteS', 'deleteH', 'upS', 'upH', 'fillH', 'fillS', 'template' ) );
  if( $action ) switch( $action ) {
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
        // debug( $values_posten, 'buche: values_posten' );
        $buchungen_id = sql_buche( $buchungen_id, $valuta, $vorfall, $values_posten );
        $action = '';
        reinit( 'reset' );
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
        $pS[ $i ]['posten_id']['value'] = 0;
      }
      for( $i = 0; $i < $nH ; $i++ ) {
        $pH[ $i ]['posten_id']['value'] = 0;
      }
      break;
  }


} while( $reinit );

// bigskip();


foreach( $pfields as $name => $r ) {
  for( $i = 0; $i < $nS ; $i++ ) {
    $fieldname = 'pS'.$i.'_'.$name;
    $pS[ $i ][ $name ]['name'] = $fieldname;  // may be changed after 'upS'!
    $jlf_persistent_vars[ 'self' ][ $fieldname ] = & $pS[ $i ][ $name ]['value'];
  }
  for( $i = 0; $i < $nH ; $i++ ) {
    $fieldname = 'pH'.$i.'_'.$name; 
    $pH[ $i ][ $name ]['name'] = $fieldname;
    $jlf_persistent_vars[ 'self' ][ $fieldname ] = & $pH[ $i ][ $name ]['value'];
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
            echo inlink( '', "action=fillS_$i,class=equal href" );
            if( $nS > 1 )
              echo inlink( '', "action=deleteS_$i,class=drop href,confirm=Posten wirklich loeschen?" );
            if( $i > 0 )
              echo inlink( '', "action=upS_$i,class=uparrow href" );
      }
      open_tr( 'smallskips' );
        open_td( 'right,colspan=6', inlink( '', 'action=addS,class=plus href' ) );

      open_tr( 'medskip' );
        open_th( 'bold,colspan=6', 'an' );

      for( $i = 0; $i < $nH ; $i++ ) {
        open_tr( 'smallskips solidbottom' );
          form_row_posten( 'H', $i );
          open_td( 'bottom' );
            echo inlink( '', "action=fillH_$i,class=equal href" );
            if( $nH > 1 )
              echo inlink( '', "action=deleteH_$i,class=drop href,confirm=Posten wirklich loeschen?" );
            if( $i > 0 )
              echo inlink( '', "action=upH_$i,class=uparrow href" );
      }
      open_tr( 'smallskips' );
        open_td( 'right,colspan=6', inlink( '', 'action=addH,class=plus href' ) );

    if( $problems ) {
      open_tr( 'smallskips' );
        open_td( 'medskip,colspan=6' );
          open_ul();
            flush_problems( 'tag=li' );
          close_ul();
    }

      open_tr( 'smallskips' );
        open_td( 'right medskip,colspan=6' );
          if( $buchungen_id )
            open_span( 'quads', template_button_view() );
          open_span( 'quads', save_button_view() );
          open_span( 'quads', reset_button_view() );

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
