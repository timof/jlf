<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

define( 'OPTION_SHOW_UNTERKONTEN', 1 );
define( 'OPTION_SHOW_POSTEN', 2 );
define( 'OPTION_SHOW_STAMM', 4 );
init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window,default='.OPTION_SHOW_UNTERKONTEN );

$field_geschaeftsjahr = init_var( 'geschaeftsjahr', "global,type=u,sources=http persistent,default=$geschaeftsjahr_thread,min=$geschaeftsjahr_min,allow_null=0,set_scopes=self" );
$field_valuta_von = init_var( 'valuta_von', 'global,type=u,sources=http persistent,default=100,min=100,max=1299,set_scopes=self' );
$field_valuta_bis = init_var( 'valuta_bis', 'global,type=u,sources=http persistent,default=1299,initval=1231,min=100,max=1299,set_scopes=self' );
if( $valuta_von > $valuta_bis ) {
  if( $field_valuta_von['source'] == 'http' ) {
    $valuta_bis = $valuta_von;
  } else {
    $valuta_von = $valuta_bis;
  }
}

init_var( 'hauptkonten_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
init_var( 'flag_problems', 'type=u,sources=persistent,default=0,global,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

do {
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

  $hauptkonten_fields = array(
    'seite' => array( 'default' => '', 'global' => '1' )
  , 'kontenkreis' => array( 'default' => '', 'global' => '1' )
  , 'kontoklassen_id' => array( 'global' => 1 )
  , 'hauptkonten_hgb_klasse'
  , 'titel' => 'size=30'
  , 'rubrik' => 'size=30'
  , 'kommentar' => 'lines=3,cols=60'
  , 'flag_hauptkonto_offen' => 'b,default=1'
  );

  if( $hauptkonten_id ) {
    $hk = sql_one_hauptkonto( $hauptkonten_id );
    $flag_modified = 1;
    // cannot be changed for existing accounts:
    $hauptkonten_fields['seite']['readonly'] = 1;
    $hauptkonten_fields['kontenkreis']['readonly'] = 1;
    $hauptkonten_fields['kontoklassen_id']['readonly'] = 1;
  } else {
    $hk = array();
    $flag_modified = 0;
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => & $flag_modified
  , 'rows' => array( 'hauptkonten' => $hk ) // provide current values, pattern, defaults
  , 'tables' => 'hauptkonten'               // provide pattern, default if not set in 'rows'
//  , 'global' => true                        // for convenience: ref-bind variables in global scope
  , 'failsafe' => false   // allow 'value' => NULL: don't map to default but return offending 'raw'
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  , 'auto_select_unique' => 1
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }

  // $f = init_fields( $hauptkonten_fields, $opts );
  $f = filters_kontodaten_prepare( $hauptkonten_fields, $opts );
  $filters = parameters_explode( $f['_filters'], array( 'keep' => 'seite, kontenkreis' ) );

  if( $kontoklassen_id ) {
    $klasse = sql_one_kontoklasse( $kontoklassen_id, 'default=0' );
  } else {
    $klasse = 0;
  }
  if( $klasse ) {
    switch( $klasse['vortragskonto'] ) {
      case '':
        $vortragskonto_name = '';
        break;
      case '1':
        $vortragskonto_name = 'Vortragskonto';
        break;
      default:
        $vortragskonto_name = 'Vortragskonto ' . $klasse['vortragskonto'];
        break;;
    }
  } else {
    $vortragskonto_name = '';
  }

  if( $flag_problems ) {
    if( ! $kontoklassen_id ) {
      $error_messages += new_problem( "Keine g{$uUML}tige Kontoklasse" );
      $f['_problems']['kontoklassen_id'] = $f['kontoklassen_id']['raw'];
      $f['kontoklassen_id']['class'] = 'problem';
    }
    if( ! $f['_problems'] && ! $hauptkonten_id ) {
      if( $f['titel']['value'] && $f['rubrik']['value'] && $klasse ) {
        if( sql_hauptkonten( array(
          'titel' => $f['titel']['value'], 'rubrik' => $f['rubrik']['value'], 'geschaeftsbereich' => $klasse['geschaeftsbereich'] )
        ) ) {
          $error_messages += new_problem( 'Hauptkonto mit diesem Titel und Rubrik existiert bereits' );
          $f['_problems']['exists'] = true;
        }
      }
    }
  }

  $actions = array( 'reset', 'save', 'template', 'deleteHauptkonto', 'hauptkontoSchliessen', 'hauptkontoOeffnen' );
  handle_actions( $actions );
  switch( $action ) {

    case 'template':
      $hauptkonten_id = 0;
      reinit('self');
      break;

    case 'save':

      if( ! $error_messages ) {

        $values = array(
          'rubrik' => $f['rubrik']['value']
        , 'titel' => $f['titel']['value']
        , 'kontoklassen_id' => $f['kontoklassen_id']['value']
        , 'hauptkonten_hgb_klasse' => $f['hauptkonten_hgb_klasse']['value']
        , 'flag_hauptkonto_offen' => $f['flag_hauptkonto_offen']['value']
        , 'kommentar' => $f['kommentar']['value']
        );

        $error_messages = sql_save_hauptkonto( $hauptkonten_id, $values, 'action=dryrun' );
        if( ! $error_messages ) {
          $hauptkonten_id = sql_save_hauptkonto( $hauptkonten_id, $values, 'action=hard' );
          js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
          $info_messages[] = 'Eintrag wurde gespeichert';
          reinit('reset');
        }
      }
      break;

    case 'hauptkontoSchliessen':
      sql_hauptkonto_schliessen( $hauptkonten_id, 'action=hard' );
      $f['flag_hauptkonto_offen']['value'] = 0;
      reinit('self');
      break;

    case 'hauptkontoOeffnen':
      sql_hauptkonto_oeffnen( $hauptkonten_id, 'action=hard' );
      $f['flag_hauptkonto_offen']['value'] = 1;
      reinit('self');
      break;

  }

} while( $reinit );


if( $hauptkonten_id ) {
  open_fieldset( 'old', "Hauptkonto [$hauptkonten_id]" );
  $t = inlink( '!', array( 'class' => 'icon close quadr', 'text' => '', 'options' => $options & ~OPTION_SHOW_STAMM ) );
} else {
  $options |= OPTION_SHOW_STAMM;
  open_fieldset( 'new', 'neues Hauptkonto' );
  $t = '';
}

if( $options & OPTION_SHOW_STAMM ) {

  open_fieldset( '', $t . 'Stammdaten' );
  
    if( $hauptkonten_id ) {
      open_div( 'oneline bold smallskips', 'Kreis / Seite: ' . kontenkreis_name( $kontenkreis ) .' '. seite_name( $seite ) );
  
      open_div( 'oneline bold smallskips' );
        echo 'Kontoklasse: '. $klasse['cn'];
        if( $klasse['geschaeftsbereich'] ) {
          open_span( 'quads bold', $klasse['geschaeftsbereich'] );
        }
        if( $vortragskonto_name ) {
          open_span( 'bold qquads', $vortragskonto_name );
        }
      close_div();
  
    } else {
  
      open_fieldset( 'line oneline', label_element( $f['kontenkreis'], '', 'Kontenkreis:' ), selector_kontenkreis( $f['kontenkreis'] ) );
      open_fieldset( 'line oneline', label_element( $f['seite'], '', 'Seite:' ), selector_seite( $f['seite'] ) );
  
      open_fieldset( 'line oneline'
      , label_element( $f['kontoklassen_id'], '', 'Kontoklasse:' )
      , selector_kontoklasse( $f['kontoklassen_id'], array( 'filters' => $filters ) )
      );
    }
  
    if( $kontoklassen_id ) {
  
      open_fieldset( 'line oneline'
      , label_element( $f['hauptkonten_hgb_klasse'], '', 'HGB-Klasse:' )
      , selector_hgb_klasse( $f['hauptkonten_hgb_klasse'], array( 'filters' => $filters ) )
      );
  
      if( ! $hauptkonten_id ) {
        $f['rubrik']['uid_choices'] = uid_choices_rubriken( $filters );
        $f['rubrik']['default_display'] = '(vorhandene Rubriken...)';
      }
      open_fieldset( 'line oneline'
      , label_element( $f['rubrik'], '', 'Rubrik:' )
      , string_element( $f['rubrik'] )
      );
      open_fieldset( 'line oneline'
      , label_element( $f['titel'], '', 'Titel:' )
      , string_element( $f['titel'] )
      );
  
      open_fieldset( 'line oneline'
      , label_element( $f['kommentar'], '', 'Kommentar:' )
      , textarea_element( $f['kommentar'] )
      );
  
    }
  
    if( $hauptkonten_id ) {
      open_div( 'oneline smallpadt' );
        echo 'Status: ';
        if( $hk['flag_hauptkonto_offen'] ) {
          open_span( 'quads bold', 'offen' );
          if( have_priv( 'hauptkonten', 'write', $hauptkonten_id ) ) {
            echo inlink( 'self', array(
              'class' => 'button qquads'
            , 'action' => 'hauptkontoSchliessen'
            , 'text' => 'Hauptkonto schliessen'
            , 'confirm' => 'wirklich schliessen?'
            , 'inactive' => sql_hauptkonto_schliessen( $hauptkonten_id, 'action=dryrun' )
            ) );
          }
        } else {
          open_span( 'quads bold', 'geschlossen' );
          if( have_priv( 'hauptkonten', 'write', $hauptkonten_id ) ) {
            echo inlink( 'self', array(
              'class' => 'button qquads'
            , 'action' => 'hauptkontoOeffnen'
            , 'text' => 'Hauptkonto oeffnen'
            , 'confirm' => 'wirklich oeffnen?'
            , 'inactive' => sql_hauptkonto_oeffnen( $hauptkonten_id, 'action=dryrun' )
            ) );
          }
          if( have_priv( 'hauptkonten', 'delete', $hauptkonten_id ) ) {
            echo inlink( 'self', array(
              'class' => 'drop button qquads'
            , 'action' => 'deleteHauptkonto'
            , 'text' => 'Hauptkonto loeschen'
            , 'confirm' => 'wirklich loeschen?'
            , 'inactive' => sql_delete_hauptkonten( $hauptkonten_id, 'action=dryrun' )
            ) );
          }
        }
      close_div();
  
      open_div( 'right smallpadt' );
        if( have_priv( 'hauptkonten', 'create' ) ) {
          if( ! $f['_changes'] ) {
            echo template_button_view();
          }
        }
        echo reset_button_view();
        if( have_priv( 'hauptkonten', $hauptkonten_id ? 'write' : 'create', $hauptkonten_id ) ) {
          echo save_button_view();
        }
      close_div();
  
  
    } else if( $kontoklassen_id ) {
      if( have_priv( 'hauptkonten', $hauptkonten_id ? 'write' : 'create', $hauptkonten_id ) ) {
        open_div( 'right smallpadt', save_button_view() );
      }
    }

  close_fieldset(); // stammdaten

} else {

  open_table('css td:bottom;quads;tinypads');
    open_tr();
      open_td( '', 'Hauptkonto:' );
      open_td( 'bold', "{$hk['kontenkreis']} {$hk['seite']} {$hk['rubrik']} / {$hk['titel']}"
                        . inlink( '!', array( 'class' => 'qquadl edit noprint', 'text' => 'Details...', 'options' => $options | OPTION_SHOW_STAMM ) )
      );
    open_tr();
      open_td( '', 'Kontoklasse:' );
      open_td( 'bold', "{$hk['kontoklassen_cn']} {$hk['geschaeftsbereich']}" );
    open_tr();
      open_td( '', 'Attribute:' );
      open_td( 'bold', kontoattribute_view( $hk ) );
    open_tr();
      open_td( '', 'Status:' );
      open_td( 'bold', $hk['flag_hauptkonto_offen'] ? 'offen' : 'geschlossen' );
  if( $hauptkonten_id ) {
    open_tr('td:smallpads' );
      open_td( '', "Gesch{$aUML}ftsjahr: "  );
      open_td( '', filter_geschaeftsjahr( $field_geschaeftsjahr ) );
      if( $geschaeftsjahr ) {
        open_tr();
          open_th( '', 'von:' );
          open_td( '', selector_valuta( $field_valuta_von ) );
        open_tr();
          open_th( '', 'bis:' );
          open_td( '', selector_valuta( $field_valuta_bis ) );
      }
  }
  close_table();

  if( $hauptkonten_id ) {
    
    if( $geschaeftsjahr ) {
    
      init_var( 'unterkonten_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
      $uk = sql_unterkonten( array( 'hauptkonten_id' => $hauptkonten_id ) );
      if( $options & OPTION_SHOW_UNTERKONTEN ) {
    
        open_fieldset( ''
          , inlink( 'self', array( 'options' => $options & ~OPTION_SHOW_UNTERKONTEN , 'class' => 'icon close quadr' ) )
            . ' Unterkonten: '
        );
          unterkontenlist_view(
            array(
              'hauptkonten_id' => $hauptkonten_id
            , 'geschaeftsjahr' => $geschaeftsjahr
            , 'valuta >=' => $valuta_von
            , 'valuta <=' => $valuta_bis
            )
          , array( 'select' => 'unterkonten_id' )
          );
        close_fieldset();
      } else {
        if( $uk ) {
          $n = count( $uk );
          open_div( 'smallskip', inlink( 'self', array(
            'options' => $options | OPTION_SHOW_UNTERKONTEN, 'class' => 'button', 'text' => "$n Unterkonten - anzeigen"
          ) ) );
        } else {
          open_div( 'center smallskip', '(keine Unterkonten vorhanden)' );
        }
      }
      if( have_priv( 'unterkonten', 'create' ) ) {
        open_div( 'right smallskipb', inlink( 'unterkonto', "class=big button noprint,text=Neues Unterkonto,hauptkonten_id=$hauptkonten_id" ) );
      }
    
      if( $options & OPTION_SHOW_UNTERKONTEN ) {
        if( $unterkonten_id ) {
          if( $options & OPTION_SHOW_POSTEN ) {
            open_fieldset( ''
              , inlink( 'self', array( 'options' => $options & ~OPTION_SHOW_POSTEN , 'class' => 'icon close quadr' ) )
                . ' Posten: '
                . inlink( 'posten', "class=qquadl icon browse,text=,geschaeftsjahr=$geschaeftsjahr,hauptkonten_id=$hauptkonten_id" )
            );
              postenlist_view( array( 'unterkonten_id' => $unterkonten_id, 'geschaeftsjahr' => $geschaeftsjahr ), 'geschaeftsjahr_zeigen=0' );
            close_fieldset();
          } else {
            $n = sql_posten( "hauptkonten_id=$hauptkonten_id,geschaeftsjahr=$geschaeftsjahr", 'single_field=COUNT' );
            open_div( 'smallskip'
            , inlink( 'self', array( 'options' => $options | OPTION_SHOW_POSTEN, 'class' => 'button', 'text' => "$n Posten - anzeigen" )
              . inlink( 'posten', "class=qquadl icon browse,text=,geschaeftsjahr=$geschaeftsjahr,hauptkonten_id=$hauptkonten_id" )
            ) );
          }
        }
      }
    
    } else { // $geschaeftsjahr == 0
    
      saldenlist_view( "hauptkonten_id=$hauptkonten_id" );
    
    }
  
  }

} // ~SHOW_STAMM

close_fieldset(); // global

if( $action === 'deleteHauptkonto' ) {
  need( $hauptkonten_id );
  sql_delete_hauptkonten( $hauptkonten_id, 'action=hard' );
  js_on_exit( "flash_close_message({$H_SQ}Konto gelöscht{$H_SQ});" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
