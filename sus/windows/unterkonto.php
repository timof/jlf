<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

define( 'OPTION_SHOW_POSTEN', 1 );
define( 'OPTION_SHOW_STAMM', 2 );
init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window,default='.OPTION_SHOW_POSTEN );

$field_geschaeftsjahr = init_var( 'geschaeftsjahr', "global,type=u,sources=http persistent,default=$geschaeftsjahr_thread,min=$geschaeftsjahr_min,allow_null=0,set_scopes=self" );
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
  , 'tags_unterkonto' => 'a128,size=40'
  , 'url' => 'a256,size=40'
  , 'kommentar' => 'h,rows=2,cols=60'
  , 'ust_satz' => 'W1,default=0,pattern=/^[012]$/'
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
  );
  $fields = $unterkonten_fields;
  $rows = array( 'unterkonten' => $uk );

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => & $flag_modified
  , 'rows' => $rows
  , 'tables' => array( 'unterkonten' )
//  , 'global' => true    // for convenience: ref-bind all values in global scope
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

  if( ( $people_id = $f['people_id']['value'] ) ) {
    $person = sql_person( $people_id, 'default=0' );
  } else {
    $person = array();
  }

  switch( $hk['vortragskonto'] ) {
    case '':
      $vortragskonto_name = '';
      break;
    case '1':
      $vortragskonto_name = 'Vortragskonto';
      break;
    default:
      $vortragskonto_name = 'Vortragskonto '.$hk['vortragskonto'];
      break;;
  }

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
  open_fieldset( 'old', "Unterkonto [$unterkonten_id]" );
  $t = inlink( '!', array( 'class' => 'icon close quadr', 'text' => '', 'options' => $options & ~OPTION_SHOW_STAMM ) );
} else {
  $options |= OPTION_SHOW_STAMM;
  open_fieldset( 'new', 'neues Unterkonto' );
  $t = '';
}

if( $options & OPTION_SHOW_STAMM ) {
  open_fieldset( '', $t . 'Stammdaten' );

    open_div( 'oneline smallskips bold', 'Kontoklasse: ' . "{$hk['kontoklassen_cn']} {$hk['geschaeftsbereich']}" );

    open_div( 'oneline smallskips bold', 'Hauptkonto: ' . inlink( 'hauptkonto', array(
        'hauptkonten_id' => $hauptkonten_id
      , 'text' => html_tag( 'span', 'bold', "{$hk['kontenkreis']} {$hk['seite']}" ) ." {$hk['rubrik']} / {$hk['titel']}"
    ) ) );

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

    open_fieldset( 'line'
    , label_element( $f['tags_unterkonto'], '', 'Tags:' )
    , string_element( $f['tags_unterkonto'] )
    );

    open_fieldset( 'line', label_element( $f['unterkonten_hgb_klasse'], '', 'HGB-Klasse:' ) );
      if( $hk['hauptkonten_hgb_klasse'] ) {
        echo open_span( 'kbd', $hk['hauptkonten_hgb_klasse'] );
      } else {
        echo selector_hgb_klasse( $f['unterkonten_hgb_klasse'] );
      }
    close_fieldset();

    switch( $hk['kontenkreis'] . $hk['seite'] ) {
      case 'EP':
        $t = "zu zahlende Umsatzsteuer bei Umsatz";
        break;
      case 'EA':
        $t = "r{$uUML}ckforderbare Vorsteuer bei Umsatz";
        break;
      case 'BP':
        $t = "Umsatzsteuerschuld";
        break;
      case 'BA':
        $t = "Vorsteuerforderungen";
        break;
    }
    open_fieldset( 'line'
    , label_element( $f['ust_satz'], '', 'USt-Satz:' )
    , selector_ust_satz( $f['ust_satz'] ) . html_div( 'comment', "falls nicht 0: $t" )
    );

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
        if( have_priv( 'unterkonten', 'write', $unterkonten_id ) ) {
          echo inlink( 'self', array(
            'class' => 'button qquads'
          , 'action' => 'unterkontoSchliessen'
          , 'text' => 'Unterkonto schliessen'
          , 'confirm' => 'wirklich schliessen?'
          , 'inactive' => sql_unterkonto_schliessen( $unterkonten_id, 'action=dryrun' )
          ) );
        }
      } else {
        open_span( 'quads', 'Konto ist geschlossen' );
        if( have_priv( 'unterkonten', 'write', $unterkonten_id ) ) {
          echo inlink( 'self', array(
            'class' => 'button qquads'
          , 'action' => 'unterkontoOeffnen'
          , 'text' => "Unterkonto {$oUML}ffnen"
          , 'confirm' => "wirklich {$oUML}ffnen?"
          , 'inactive' => sql_unterkonto_oeffnen( $unterkonten_id, 'action=dryrun' )
          ) );
        }
        if( have_priv( 'unterkonten', 'delete', $unterkonten_id ) ) {
          echo inlink( 'self', array(
            'class' => 'drop button qquads'
          , 'action' => 'deleteUnterkonto'
          , 'text' => "Unterkonto l{$oUML}schen"
          , 'confirm' => "wirklich l{$oUML}schen?"
          , 'inactive' => sql_delete_unterkonten( $unterkonten_id, 'action=dryrun' )
          ) );
        }
      }
    close_div();

  }

    open_div( 'right smallpadt' );
      if( have_priv( 'unterkonten', 'create' ) ) {
        if( $unterkonten_id && ! $f['_changes'] ) {
          echo template_button_view();
        }
      }
      echo reset_button_view();
      if( have_priv( 'unterkonten', $unterkonten_id ? 'write' : 'create', $unterkonten_id ) ) {
        echo save_button_view();
      }
    close_div();

  close_fieldset();

} else {
  open_table('css td:bottom;quads;tinypads');
    open_tr();
      open_td( '', 'Unterkonto:' );
      open_td( 'bold', $uk['cn'] . inlink( '!', array( 'class' => 'qquadl edit noprint', 'text' => 'Details...', 'options' => $options | OPTION_SHOW_STAMM ) ) );
    open_tr();
      open_td( '', 'Hauptkonto:' );
      open_td( 'bold', inlink( 'hauptkonto', array(
        'hauptkonten_id' => $hauptkonten_id
      , 'text' => html_tag( 'span', 'bold', "{$hk['kontenkreis']} {$hk['seite']}" ) ." {$hk['rubrik']} / {$hk['titel']}"
      ) ) );
    open_tr();
      open_td( '', 'Kontoklasse:' );
      open_td( 'bold', "{$hk['kontoklassen_cn']} {$hk['geschaeftsbereich']}" );
    open_tr();
      open_td( '', 'Status:' );
      open_td( 'bold', $uk['flag_unterkonto_offen'] ? 'offen' : 'geschlossen' );
  if( $unterkonten_id ) {
    open_tr('td:smallpads' );
      open_td( '', "Gesch{$aUML}ftsjahr: "  );
      open_td( '', filter_geschaeftsjahr( $field_geschaeftsjahr ) );
  }
  close_table();

  if( $unterkonten_id ) {
  
    open_fieldset( 'line smallskips oneline', "Gesch{$aUML}ftsjahr: ", filter_geschaeftsjahr( $field_geschaeftsjahr ) );
  
    if( $geschaeftsjahr ) {
  
      if( have_priv( 'buchungen', 'create' ) ) {
        if( $uk['flag_unterkonto_offen'] ) {
          open_table( 'medskipt hfill css' );
            open_tr();
            open_td( 'left', action_link(
              array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Soll' )
            , array( 'action' => 'init', 'geschaeftsjahr' => $geschaeftsjahr, 'buchungen_id' => 0, 'nS' => 1, 'pS0_unterkonten_id' => $unterkonten_id, 'nH' => 1, 'geschaeftsjahr' => $geschaeftsjahr )
            ) );
            open_td( 'right', action_link(
              array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Haben' )
            , array( 'action' => 'init', 'geschaeftsjahr' => $geschaeftsjahr, 'buchungen_id' => 0, 'nS' => 1, 'pH0_unterkonten_id' => $unterkonten_id, 'nH' => 1, 'geschaeftsjahr' => $geschaeftsjahr )
            ) );
          close_table();
        }
      }
  
      if( ( $options & OPTION_SHOW_POSTEN ) ) {
        open_fieldset( 'clear medskipt'
          , inlink( 'self', array( 'options' => $options & ~OPTION_SHOW_POSTEN , 'class' => 'icon close quadr' ) )
            . ' Posten: '
            . inlink( 'posten', "class=qquadl icon browse,text=,geschaeftsjahr=$geschaeftsjahr,unterkonten_id=$unterkonten_id" )
        );
          postenlist_view( "unterkonten_id=$unterkonten_id,geschaeftsjahr=$geschaeftsjahr" );
        close_fieldset();
      } else {
        $n = sql_posten( "unterkonten_id=$unterkonten_id,geschaeftsjahr=$geschaeftsjahr", 'single_field=COUNT' );
        open_div( 'medskipt'
        , inlink( 'self', array( 'options' => $options | OPTION_SHOW_POSTEN, 'class' => 'button', 'text' => "$n Posten - anzeigen"
          . inlink( 'posten', "class=qquadl icon browse,text=geschaeftsjahr=$geschaeftsjahr,unterkonten_id=$unterkonten_id" )
        ) ) );
      }
    
  
    } else {
  
      saldenlist_view( "unterkonten_id=$unterkonten_id", 'select_jahr=P3_geschaeftsjahr' );
  
    }
  
    if( $hk['flag_personenkonto'] ) {
      open_fieldset( 'medskipt clear', 'Darlehen zu diesem Konto' );
        darlehenlist_view( array( 'darlehen_unterkonten_id' => $unterkonten_id ) );
      close_fieldset();
    }
  
  } // $unterkonten_id != 0

} // ~SHOW_STAMM

close_fieldset();

if( $action === 'deleteUnterkonto' ) {
  need( $unterkonten_id );
  sql_delete_unterkonten( $unterkonten_id, 'action=hard' );
  js_on_exit( "flash_close_message({$H_SQ}Konto gel{$oUML}scht{$H_SQ});" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
