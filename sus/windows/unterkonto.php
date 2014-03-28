<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

define( 'OPTION_SHOW_POSTEN', 1 );
init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window,default='.OPTION_SHOW_POSTEN );

$field_geschaeftsjahr = init_var( 'geschaeftsjahr', "global,type=U,sources=http persistent,default=$geschaeftsjahr_thread,min=$geschaeftsjahr_min,max=$geschaeftsjahr_max,set_scopes=self" );
init_var( 'unterkonten_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
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

  if( $unterkonten_id ) {
    $uk = sql_one_unterkonto( $unterkonten_id );
    $flag_modified = 1;
    $hauptkonten_id = $uk['hauptkonten_id'];
    init_var( 'hauptkonten_id', "global,type=U,sources=initval,set_scopes=self,initval=$hauptkonten_id" );
  } else {
    $uk = array();
    $flag_modified = 0;
    init_var( 'hauptkonten_id', 'global,type=U,sources=http persistent,set_scopes=self' );
  }
  $hk = sql_one_hauptkonto( $hauptkonten_id );
  if( ! $unterkonten_id ) {
    need( $hk['flag_hauptkonto_offen'], 'Hauptkonto ist geschlossen' );
  }

  $unterkonten_fields = array(
    'cn' => 'H,size=40,default='
  , 'flag_zinskonto' => 'b'
  , 'flag_unterkonto_offen' => 'b,default=1'
  , 'unterkonten_hgb_klasse' => array( 'type' => 'a32' )
  , 'attribute' => 'a128,size=40'
  , 'url' => 'a256,size=40'
  , 'kommentar' => 'h,rows=2,cols=60'
  );
  if( $hk['hauptkonten_hgb_klasse'] ) {
    $unterkonten_fields['unterkonten_hgb_klasse']['initval'] = $hk['hauptkonten_hgb_klasse'];
    $unterkonten_fields['unterkonten_hgb_klasse']['sources'] = 'initval';
  }
  $bankkonten_fields = array(
    'bank_cn' => 'h,size=40'
  , 'bank_kontonr' => 'size=40'
  , 'bank_blz' => 'size=40'
  , 'bank_bic' => 'size=40'
  , 'bank_iban' => 'size=40'
  , 'bank_url' => 'a256,size=40'
  );
  $sachkonten_fields = array(
    'thing_anschaffungsjahr' => 'U,size=4'
  , 'thing_abschreibungsmodus' => 'size=40'
  );
  $personenkonten_fields = array(
    'people_id' => 'U,default=0'
  , 'darlehen_id' => 'u'
  );
  $fields = $unterkonten_fields;
  $rows = array( 'unterkonten' => $uk );

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => & $flag_modified
  , 'rows' => $rows
  , 'tables' => array( 'unterkonten' )
  , 'global' => true    // for convenience: ref-bind all values in global scope
  , 'failsafe' => false // retrieve and display offending values
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }

  $f = init_fields( $unterkonten_fields, $opts );

  $opts['merge'] = $f;
  $opts['sources'] = ( $hk['flag_personenkonto'] ? $sources : 'default' );
  $f = init_fields( $personenkonten_fields, $opts );

  $opts['merge'] = $f;
  $opts['sources'] = ( $hk['flag_sachkonto'] ? $sources : 'default' );
  $f = init_fields( $sachkonten_fields, $opts );

  $opts['merge'] = $f;
  $opts['sources'] = ( $hk['flag_bankkonto'] ? $sources : 'default' );
  $f = init_fields( $bankkonten_fields, $opts );

  if( $people_id ) {
    $person = sql_person( $people_id, 'default=0' );
  } else {
    $person = array();
  }

  $vortragskonto_name = ( $hk['vortragskonto'] ? 'Vortragskonto '.$hk['vortragskonto'] : '' );

  if( $flag_problems ) {
    if( $hk['flag_personenkonto'] && ! $person ) {
      $error_messages += new_problem('Person nicht gefunden');
      $f['_problems']['people_id'] = $people_id;
      $f['people_id']['class'] = 'problem';
    }
  }

  $actions = array( 'reset', 'save', 'template', 'deleteUnterkonto', 'unterkontoSchliessen', 'unterkontoOeffnen' );
  handle_actions( $actions );
  if( $action ) switch( $action ) {

    case 'template':
      $unterkonten_id = 0;
      reinit('self');
      break;
  
    case 'save':
  
      if( ! $error_messages ) {

        $values = array( 'hauptkonten_id' => $hauptkonten_id );
        foreach( $f as $fieldname => $field ) {
          if( $fieldname[ 0 ] !== '_' ) {
            $values[ $fieldname ] = $field['value'];
          }
        }
  
        $error_messages = sql_save_unterkonto( $unterkonten_id, $values, 'action=dryrun' );
        if( ! $error_messages ) {
          $unterkonten_id = sql_save_unterkonto( $unterkonten_id, $values, 'action=hard' );
          js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
          $info_messages[] = 'Eintrag wurde gespeichert';
          reinit('reset');
        }
      }
      break;
  
    case 'unterkontoSchliessen':
      sql_unterkonto_schliessen( $unterkonten_id, 'action=hard' );
      reinit('self');
      break;
  
    case 'unterkontoOeffnen':
      sql_unterkonto_oeffnen( $unterkonten_id, 'action=hard' );
      reinit('self');
      break;
  }

} while( $reinit );

if( $unterkonten_id ) {
  open_fieldset( 'old', "Stammdaten Unterkonto [$unterkonten_id]" );
} else {
  open_fieldset( 'new', 'neues Unterkonto' );
}
  open_fieldset( '', 'Stammdaten' );

    open_fieldset( 'line bold', 'Kontoklasse', "{$hk['kontoklassen_cn']} {$hk['geschaeftsbereich']}" );

    open_fieldset( 'line' , 'Hauptkonto: ' );
      echo inlink( 'hauptkonto', array(
        'hauptkonten_id' => $hauptkonten_id
      , 'text' => html_tag( 'span', 'bold', "{$hk['kontenkreis']} {$hk['seite']}" ) ." {$hk['rubrik']} / {$hk['titel']}"
      ) );
    close_fieldset();

    open_fieldset( 'line', 'Attribute: ' );
      if( $hk['flag_vortragskonto'] ) {
        open_div( 'bold', $vortragskonto_name );
      }
      if( $hk['flag_personenkonto'] ) {
        open_div( 'oneline', label_element( $f['flag_zinskonto'], '', 'Sonderkonto Zins:' ) . checkbox_element( $f['flag_zinskonto'] ) );
      }
    close_fieldset();

    open_fieldset( 'line'
    , label_element( $f['cn'], '', 'Kontobezeichnung:' )
    , string_element( $f['cn'] )
    );

    open_fieldset( 'line', label_element( $f['unterkonten_hgb_klasse'], '', 'HGB-Klasse:' ) );
      if( $hk['hauptkonten_hgb_klasse'] ) {
        echo open_span( 'kbd', $hk['hauptkonten_hgb_klasse'] );
      } else {
        echo selector_hgb_klasse( $f['unterkonten_hgb_klasse'] );
      }
    close_fieldset();

    if( $hk['flag_bankkonto'] ) {
      open_fieldset( '', 'Bank:' );
        open_fieldset( 'line'
        , label_element( $f['bank_cn'], '', 'Bezeichnung:' )
        , string_element( $f['bank_cn'] )
        );
        open_fieldset( 'line'
        , label_element( $f['bank_iban'], '', 'IBAN:' )
        , string_element( $f['bank_iban'] )
        );
        open_fieldset( 'line'
        , label_element( $f['bank_kontonr'], '', 'Konto-Nr:' )
        , string_element( $f['bank_kontonr'] )
        );
        open_fieldset( 'line'
        , label_element( $f['bank_blz'], '', 'BLZ:' )
        , string_element( $f['bank_blz'] )
        );
        open_fieldset( 'line'
        , label_element( $f['bank_bic'], '', 'BIC:' )
        , string_element( $f['bank_bic'] )
        );
        open_fieldset( 'line'
        , label_element( $f['bank_url'], '', 'URL:' )
        , string_element( $f['bank_url'] )
        );
      close_fieldset();
    }

    if( $hk['flag_personenkonto'] ) {
      open_fieldset( 'line', label_element( $f['people_id'], '', 'Person:' ) );
        echo selector_people( $f['people_id'] );
        if( $f['people_id']['value'] ) {
          open_span( 'qquad', inlink( 'person', array( 'class' => 'people', 'text' => '', 'people_id' => $f['people_id']['value'] ) ) );
        }
      close_fieldset();
    }

    if( $hk['flag_sachkonto'] ) {
      open_fieldset( 'line', 'Gegenstand:' );
        open_fieldset( 'line'
        , label_element( $f['thing_anschaffungsjahr'], '', 'Anschaffungsjahr:' )
        , int_element( $f['thing_anschaffungsjahr'] )
        );
        open_fieldset( 'line'
        , label_element( $f['thing_abschreibungsmodus'], '', 'Abschreibungsmodus:' )
        , string_element( $f['thing_abschreibungsmodus'] )
        );
      close_fieldset();
    }

    open_fieldset( 'line', 'Kommentar:', textarea_element( $f['kommentar'] ) );

  if( $unterkonten_id ) {
    open_div( 'oneline smallpadt' );
      echo 'Status:';
      if( $uk['flag_unterkonto_offen'] ) {
        open_span( 'quads', 'Konto ist offen' );
        echo inlink( 'self', array(
          'class' => 'button qquads'
        , 'action' => 'unterkontoSchliessen'
        , 'text' => 'Unterkonto schliessen'
        , 'confirm' => 'wirklich schliessen?'
        , 'inactive' => sql_unterkonto_schliessen( $unterkonten_id, 'action=dryrun' )
        ) );
      } else {
        open_span( 'quads', 'Konto ist geschlossen' );
        echo inlink( 'self', array(
          'class' => 'button qquads'
        , 'action' => 'unterkontoOeffnen'
        , 'text' => "Unterkonto {$oUML}ffnen"
        , 'confirm' => "wirklich {$oUML}ffnen?"
        , 'inactive' => sql_unterkonto_oeffnen( $unterkonten_id, 'action=dryrun' )
        ) );
        echo inlink( 'self', array(
          'class' => 'drop button qquads'
        , 'action' => 'deleteUnterkonto'
        , 'text' => "Hauptkonto l{$oUML}schen"
        , 'confirm' => "wirklich l{$oUML}schen?"
        , 'inactive' => sql_delete_unterkonten( $unterkonten_id, 'action=dryrun' )
        ) );
      }
    close_div();

  }

    open_div( 'right smallpadt' );
      if( ! $f['_changes'] ) {
        echo template_button_view();
      }
      echo reset_button_view();
      echo save_button_view();
    close_div();

  close_fieldset();

  if( $unterkonten_id && ! ( $options & OPTION_SHOW_POSTEN ) ) {
    $n = sql_count( 'posten', "unterkonten_id=$unterkonten_id" );
    if( $n > 0 ) {
      $saldo = sql_unterkonten_saldo( $unterkonten_id );
      open_div( 'solidtop smallskips center', inlink( 'self', array(
        'text' => "Saldo ($n Posten): ".price_view( $saldo ), 'class' => 'button'
      , 'options' => $options | OPTION_SHOW_POSTEN
      ) ) );
    } else {
      open_div( 'center', '(keine Posten vorhanden)' );
    }
  }

  if( $unterkonten_id && $uk['flag_unterkonto_offen'] ) {
    open_div( 'smallskips' );
      open_span( "qquad,style=float:left;", action_link(
        array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Soll' )
      , array( 'action' => 'init', 'buchungen_id' => 0, 'nS' => 1, 'pS0_unterkonten_id' => $unterkonten_id, 'nH' => 1, 'geschaeftsjahr' => $geschaeftsjahr )
      ) );
      open_span( "qquad,style=float:right;", action_link(
        array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Haben' )
      , array( 'action' => 'init', 'buchungen_id' => 0, 'nS' => 1, 'pH0_unterkonten_id' => $unterkonten_id, 'nH' => 1, 'geschaeftsjahr' => $geschaeftsjahr )
      ) );
    close_div();
  }

  if( $unterkonten_id && ( $options & OPTION_SHOW_POSTEN ) ) {
    bigskip();
    open_fieldset( ''
      , inlink( 'self', array( 'options' => $options & ~OPTION_SHOW_POSTEN, 'class' => 'close_small' ) )
        . ' Posten: '
    );
      postenlist_view( array( 'unterkonten_id' => $unterkonten_id ) );
    close_fieldset();
  }

  if( $unterkonten_id && $hk['flag_personenkonto'] ) {
    $zahlungsplan = sql_zahlungsplan( array( 'unterkonten_id' => $unterkonten_id ) );
    if( $zahlungsplan ) {
      medskip();
      open_fieldset( '', 'Darlehen mit Zahlungsplan zu diesem Konto' );
        $darlehen = array();
        foreach( $zahlungsplan as $z )
          $darlehen[ $z['darlehen_id'] ] = $z['darlehen_id'];
        darlehenlist_view( array( 'darlehen_id' => $darlehen ) );
      close_fieldset();
    }
  }

close_fieldset();

if( $action === 'deleteUnterkonto' ) {
  need( $unterkonten_id );
  sql_delete_unterkonten( $unterkonten_id, 'action=hard' );
  js_on_exit( "flash_close_message({$H_SQ}Konto gel{$oUML}scht{$H_SQ});" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
