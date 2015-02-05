<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window,default=0' );

init_var( 'darlehen_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
init_var( 'flag_problems', 'type=u,sources=persistent,default=0,global,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

do {

  switch( $reinit ) {
    case 'init':
      $sources = 'http self initval default';
      break;
    case 'self':
      $sources = 'self initval default';
      break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'initval default';
      break;
    default:
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'hauptkonten,init' );
  }
  $reinit = false;

  if( $action === 'save' ) {
    $flag_problems = 1;
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => & $flag_modified
  , 'tables' => 'darlehen'
  , 'failsafe' => false
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );

  if( $darlehen_id ) {
    $flag_modified = 1;
    $darlehen = sql_one_darlehen( $darlehen_id );
    $opts['rows'] = array( 'darlehen' => $darlehen );

    $darlehen_unterkonto = sql_one_unterkonto( $darlehen['darlehen_unterkonten_id'], '0,'.AUTH );
    $zins_unterkonto = sql_one_unterkonto( $darlehen['zins_unterkonten_id'], '0,'.AUTH );
    $zinsaufwand_unterkonto = sql_one_unterkonto( $darlehen['zinsaufwand_unterkonten_id'], '0,'.AUTH );
    $person = sql_person( $darlehen['people_id'], '0,'.AUTH );

    // init_var( 'geschaeftsjahr', 'global,type=U,sources=,set_scopes=self,default='.$darlehen['geschaeftsjahr_darlehen'] );
  } else {
    $flag_modified = 0;
    $darlehen = array();
    $darlehen_unterkonto = 0;
    $zins_unterkonto = 0;
    $zinsaufwand_unterkonto = 0;
    $person = 0;
    // init_var( 'geschaeftsjahr', "global,type=U,sources=http self,set_scopes=self,default=$geschaeftsjahr_thread" );
  }

  $jahr_max = $geschaeftsjahr + 99;
  $fields = array(
    'cn' => 'h,cols=60'
  , 'kommentar' => 'h,cols=60,lines=2'
  , 'geschaeftsjahr_darlehen' => array( 
       'type' => 'U', 'default' => $geschaeftsjahr_thread, 'min' => $geschaeftsjahr_min, 'max' => $geschaeftsjahr_max
     )
  , 'geschaeftsjahr_tilgung_start' => array(
       'type' => 'U', 'default' => $geschaeftsjahr_thread, 'min' => $geschaeftsjahr_min, 'max' => $jahr_max
     )
  , 'geschaeftsjahr_tilgung_ende' =>  array(
       'type' => 'U', 'default' => $geschaeftsjahr_thread, 'min' => $geschaeftsjahr_min, 'max' => $jahr_max
     )
  , 'geschaeftsjahr_zinslauf_start' => array(
       'type' => 'U', 'default' => $geschaeftsjahr_thread, 'min' => $geschaeftsjahr_min, 'max' => $jahr_max
    )
  , 'valuta_zinslauf_start' => array(
      'default' => 100 // bedeutet: ab einzahlung
    , 'type' => 'U', 'min' => 100, 'max' => 1231, 'format' => '%04u'
    )
  , 'geschaeftsjahr_zinsauszahlung_start' => array(
       'type' => 'U', 'default' => $geschaeftsjahr_thread, 'min' => $geschaeftsjahr_min, 'max' => $jahr_max
     )
  , 'darlehen_unterkonten_id' => 'U'
  , 'zins_unterkonten_id' => 'u'
  , 'zinsaufwand_unterkonten_id' => "u,default=$default_erfolgskonto_zinsaufwand_uk"
  , 'zins_prozent' => 'f,format=%.2f'
  , 'betrag_zugesagt' => 'f,format=%.2f'
  , 'betrag_abgerufen' => 'f,format=%.2f'
  , 'people_id' => 'U'
  );
  $f = init_fields( $fields, $opts );

  if( ! $darlehen_id ) {
    if( $f['darlehen_unterkonten_id']['value'] ) {
      $darlehen_unterkonto = sql_one_unterkonto( $f['darlehen_unterkonten_id'], '0,'.AUTH );
    }
    if( $f['zins_unterkonten_id']['value'] ) {
      $zins_unterkonto = sql_one_unterkonto( $f['zins_unterkonten_id'], '0,'.AUTH );
    }
    if( $f['zinsaufwand_unterkonten_id']['value'] ) {
      $zins_unterkonto = sql_one_unterkonto( $f['zinsaufwand_unterkonten_id'], '0,'.AUTH );
    }
  }

  if( adefault( $darlehen_unterkonto, 'people_id' ) && $f['people_id']['value'] ) {
    if( adefault( $darlehen_unterkonto, 'people_id' ) != $f['people_id']['value'] ) {
      $f = fields_problem( $f, 'darlehen_unterkonten_id' );
    }

  $darlehen_uk = $zins_uk = $zinsaufwand_uk = array();
  
  $filters_darlehen_uk = 'flag_personenkonto,kontenkreis=B,seite=P,flag_zinskonto=0,flag_unterkonto_offen';
  if( $f['darlehen_unterkonten_id']['value'] ) {
    $darlehen_uk = sql_one_unterkonto( array( $filters_darlehen_uk, 'id' => $f['darlehen_unterkonten_id']['value'] ), array() );
    if( $darlehen_uk ) {
      $f['people_id']['value'] = $darlehen_uk['people_id'];
    } else {
      $f = fields_problem( $f, 'darlehen_unterkonten_id', "ung{$uUML}ltiges Darlehenkonto" );
    }
  }
  
  $filters_zins_uk = 'flag_personenkonto,kontenkreis=B,seite=P,flag_zinskonto=1,flag_unterkonto_offen';
  if( $f['zins_unterkonten_id']['value'] ) {
    $zins_uk = sql_one_unterkonto( array( $filters_zins_uk, 'id' => $f['zins_unterkonten_id']['value'] ), array() );
    if( $darlehen_uk && $zins_uk && ( $zins_uk['people_id'] != $darlehen_uk['people_id'] ) ) {
      $zins_uk = array();
    }
    if( ! $zins_uk ) {
      $f['zins_unterkonten_id']['value'] = 0;
      $f = fields_problem( $f, 'zins_unterkonten_id', "ung{$uUML}ltiges Zinskonto" );
    }
  }
  
  $filters_zinsaufwand_uk = 'kontenkreis=E,seite=A,ust_satz=0,flag_unterkonto_offen';
  if( $f['zinsaufwand_unterkonten_id']['value'] ) {
    $zinsaufwand_uk = sql_one_unterkonto( array( $filters_zinsaufwand_uk, 'id' => $f['zins_unterkonten_id']['value'] ), array() );
    if( ! $zinsaufwand_uk ) {
      $f['zinsaufwand_unterkonten_id']['value'] = 0;
      $f = fields_problem( $f, 'zinsaufwand_unterkonten_id', "ung{$uUML}ltiges Zinsaufwandskonto" );
    }
  }

  $person = array();
  if( $f['people_id']['value'] ) {
    $person = sql_person( $f['people_id']['value'], array() );
    if( ! $person ) {
      $f['people_id']['value'] = 0;
      $f = fields_problem( $f, 'person', "ung{$uUML}ltiger Kreditor" );
    }
  }

  if( $flag_problems ) {
    if( $f['geschaeftsjahr_tilgung_start']['value'] < $f['geschaeftsjahr_darlehen']['value'] ) {
      $f = fields_problem( $f, 'geschaeftsjahr_tilgung_start' );
    }
    if( $f['geschaeftsjahr_tilgung_ende']['value'] < $f['geschaeftsjahr_tilgung_start']['value'] ) {
      $f = fields_problem( $f, 'geschaeftsjahr_tilgung_ende' );
    }
    if( $f['geschaeftsjahr_zinslauf_start']['value'] < $f['geschaeftsjahr_darlehen']['value'] ) {
      $f = fields_problem( $f, 'geschaeftsjahr_zinslauf_start' );
    }
    if( $f['geschaeftsjahr_zinsauszahlung_start']['value'] < $f['geschaeftsjahr_zinslauf_start']['value'] ) {
      $f = fields_problem( $f, 'geschaeftsjahr_zinsauszahlung_start' );
    }
  }

  $gj_buchungen_field = init_var( 'gj_buchungen'
  , array(
      'name' => 'gj_buchungen'
    , 'global' => true
    , 'type' => 'U'
    , 'default' => max( $geschaeftsjahr, $geschaeftsjahr_current )
    , 'min' => $geschaeftsjahr
    , 'max' => $f['geschaeftsjahr_tilgung_ende']['value']
    , 'sources' => 'self http'
    )
  );

  $reinit = false;

  $actions = array( 'save', 'init', 'reset' );

  if( $darlehen_id && ! $f['_problems'] ) {
    $actions[] = 'zahlungsplanBerechnen';
  }
  if( adefault( $f, 'people_id', 0 ) && $f['hauptkonten_id']['value'] ) {
    $actions[] = 'darlehenkontoAnlegen';
  }
  if( $person  && ! $f['zins_unterkonten_id']['value'] ) {
    $actions[] = 'zinskontoAnlegen';
  }
  handle_actions( $actions );
  switch( $action ) {

    case 'init':
    case 'reset':
      // nop
      break;

    case 'template':
      $darlehen_id = 0;
      $reinit = 'persistent';
      break;

    case 'save':
      if( ! $f['_problems'] ) {
        $values = array();
        foreach( $fields as $fieldname => $r ) {
          if( isset( $tables['darlehen']['cols'][ $fieldname ] ) ) {
            $values[ $fieldname ] = $f[ $fieldname ]['value'];
          }
        }
        if( ! $darlehen_id ) {
          $darlehen_id = sql_insert( 'darlehen', $values );
        } else {
          unset( $values['darlehen_unterkonten_id'] );
          unset( $values['geschaeftsjahr_darlehen'] );
          sql_update( 'darlehen', $darlehen_id, $values );
        }
        reinit('reset');
      }
      break;

    case 'darlehenkontoAnlegen':
      need( ! $darlehen_id, 'Darlehen existiert bereits - Konto nicht aenderbar' );
      need( $f['people_id']['value'], 'keine Person gewaehlt' );
      need( $f['hauptkonten_id']['value'], 'kein hauptkonto gewaehlt' );
      $hk = sql_one_hauptkonto( $hk_filters + array( 'hauptkonten_id' => $f['hauptkonten_id']['value'] ), array() );
      need( $hk && $hk['personenkonto'], 'ungeeignetes Hauptkonto' );
      $darlehen_unterkonten_id = sql_insert( 'unterkonten', array(
        'hauptkonten_id' => $hk['hauptkonten_id']
      , 'people_id' => $f['people_id']['value']
      , 'zinskonto' => 0
      , 'cn' => 'Darlehenkonto '
      ) );
      $f['darlehen_unterkonten_id']['value'] = $darlehen_unterkonten_id;
      for( $id = $darlehen_unterkonten_id, $j = $geschaeftsjahr_darlehen; $j < $geschaeftsjahr_max; $j++ ) {
        $id = sql_unterkonto_folgekonto_anlegen( $id );
      }
      openwindow( 'unterkonto', "unterkonten_id=$darlehen_unterkonten_id" );
      $f['darlehen_unterkonten_id']['value'] = $darlehen_unterkonten_id;
      reinit('reset');
      break;
  
    case 'zinskontoAnlegen':
      need( $darlehen_id, 'noch kein Darlehen gespeichert' );
      need( $person, 'keine Person zugeordnet' );
      $hk = sql_one_hauptkonto( $hk_filters + array( 'hauptkonten_id' => $darlehen_hauptkonten_id ), 'allow_null' );
      need( $hk, 'ungeeignetes Hauptkonto' );
      $zins_unterkonten_id = sql_insert( 'unterkonten', array(
        'hauptkonten_id' => $darlehen_hauptkonten_id
      , 'people_id' => $person['people_id']
      , 'zinskonto' => 1
      , 'cn' => 'Zinskonto ' . $person['cn']
      ) );
      $f['zins_unterkonten_id']['value'] = $zins_unterkonten_id;
      for( $id = $zins_unterkonten_id, $j = $geschaeftsjahr_darlehen; $j < $geschaeftsjahr_max; $j++ ) {
        $id = sql_unterkonto_folgekonto_anlegen( $id );
      }
      openwindow( 'unterkonto', "unterkonten_id=$zins_unterkonten_id" );
      reinit('reset');
      break;
  
    case 'zahlungsplanBerechnen':
      need( $darlehen_id, 'noch kein Darlehen gespeichert' );
      if( $gj_buchungen ) {
        sql_zahlungsplan_berechnen( $darlehen_id, 'delete,jahr_start='.$gj_buchungen );
      } else {
        sql_zahlungsplan_berechnen( $darlehen_id, 'delete' );
      }
      reinit('reset');
      break;

    default:
    case '':
    case 'nop':
      break;
  }
  
} while( $reinit );

$filters_hk = array( 'personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P', 'geschaeftsjahr' => $f['geschaeftsjahr_darlehen']['value'] );
$filters_uk = $filters_hk;

if( $darlehen_id ) {
  open_fieldset( 'small_form old', "Stammdaten Darlehen [$darlehen_id]" );

  $filters_uk['people_id'] = $darlehen_uk['people_id'];
  $filters_uk['hauptkonten_id'] = $darlehen_uk['hauptkonten_id'];

} else {
  open_fieldset( 'small_form new', 'neues Darlehen' );
  if( $f['hauptkonten_id']['value'] ) {
    $filters_uk['hauptkonten_id'] = $f['hauptkonten_id']['value'];
  }
  if( $f['people_id']['value'] ) {
    $filters_uk['people_id'] = $f['people_id']['value'];
  }
}

  open_table( 'hfill,colgroup=40% 20% 40%' );
    open_tr();
      open_td( 'oneline', 'Geschaeftsjahr Darlehen:' );

if( $darlehen_id ) {
      open_td( 'bold,colspan=2', $f['geschaeftsjahr_darlehen']['value'] );

      open_tr();
        open_td( '', 'Kreditor:' );
        open_td( 'colspan=2', inlink( 'person', array(
          'text' => $person['cn'], 'class' => 'people', 'people_id' => $darlehen_uk['people_id']
        ) ) );
      open_tr();
        open_td( '', 'Kontoklasse:' );
        open_td( 'colspan=2', "{$darlehen_uk['kontenkreis']} {$darlehen_uk['seite']} {$darlehen_uk['kontoklassen_cn']}" );
      open_tr();
        open_td( '', 'Hauptkonto:' );
        open_td( 'colspan=2', inlink( 'hauptkonto', array(
          'text' => "{$darlehen_uk['titel']} / {$darlehen_uk['rubrik']}"
        , 'class' => 'href', 'hauptkonten_id' => $darlehen_uk['hauptkonten_id']
        ) ) );
      open_tr();
        open_td( '', 'Darlehenkonto:' );
        open_td( 'colspan=2' );
          echo inlink( 'unterkonto', array(
            'text' => $darlehen_uk['cn'], 'class' => 'href', 'unterkonten_id' => $darlehen_uk['unterkonten_id']
          ) );
} else {
      open_td( 'bold,colspan=2', selector_geschaeftsjahr( $f['geschaeftsjahr_darlehen'] ) );


      open_tr();
        open_td( array( 'label' => $f['hauptkonten_id'] ), 'Hauptkonto:' );
        open_td( 'colspan=2' );
          echo selector_hauptkonto( $f['hauptkonten_id'], array( 'filters' => $filters_hk ) );
          if( $f['hauptkonten_id']['value'] ) {
            open_div( '', inlink( 'hauptkonto', "text=zum hauptkonto...,class=href,hauptkonten_id={$f['hauptkonten_id']['value']}" ) );
          }

      open_tr();
        open_td( array( 'label' => $f['people_id'] ), 'Kreditor:' );
        open_td( 'colspan=2' );
          echo selector_people( $f['people_id'] );

  if( $f['people_id']['value'] ) {
         open_div( '', inlink( 'person', "class=people,text=zur Person...,class=href,people_id={$f['people_id']['value']}" ) );

      open_tr();
        open_td( array( 'label' => $f['darlehen_unterkonten_id'] ), 'Darlehenkonto:' );
        open_td( 'colspan=2' );
          $filters_uk['zinskonto'] = 0;
          echo selector_unterkonto( $f['darlehen_unterkonten_id'], array( 'filters' => $filters_uk ) );
          if( $f['darlehen_unterkonten_id']['value'] ) {
            open_div( '', inlink( 'unterkonto', "text=zum Unterkonto...,class=href,unterkonten_id={$f['darlehen_unterkonten_id']['value']}" ) );
          }
  }

}

if( $f['darlehen_unterkonten_id']['value'] ) {
      open_tr( 'medskip' );
        open_td( array( 'label' => $f['cn'] ), 'cn:' );
        open_td( 'colspan=2', string_element( $f['cn'] ) );

      open_tr( 'medskip' );
        open_td( array( 'label' => $f['kommentar'] ), 'Kommentar:' );
        open_td( 'colspan=2', textarea_element( $f['kommentar'] ) );

      open_tr( 'smallskips' );
        open_td( array( 'label' => $f['betrag_zugesagt'] ), 'Betrag zugesagt:' );
        open_td( 'colspan=1', price_element( $f['betrag_zugesagt'] ) );

        open_td( 'qquad oneline' );
          open_label( $f['betrag_abgerufen'], '', 'abgerufen:' );
          echo price_element( $f['betrag_abgerufen'] );
          quad();
          open_label( $f['valuta_betrag_abgerufen'], '', 'valuta:' );
          echo monthday_element( $f['valuta_betrag_abgerufen'] );

      open_tr();
        open_td( array( 'label' => $f['zins_prozent'] ), 'Zinssatz:' );
        open_td( 'colspan=2', price_element( $f['zins_prozent'] ).' %' );

      open_tr();
        open_td( array( 'label' => $f['zins_unterkonten_id'] ), 'Sonderkonto Zins:' );
        open_td( 'colspan=2' );
            $filters_uk['zinskonto'] = 1;
            echo selector_unterkonto( $f['zins_unterkonten_id'], array(
              'filters' => $filters_uk
            , 'choices' => array( '!empty' => '(kein Zinskonto angelegt)', '0' => ' --- kein Sonderkonto fuer Zins ---' )
            ) );
          if( $f['zins_unterkonten_id']['value'] ) {
            open_div( '', inlink( 'unterkonto', "text=zum Zinskonto...,class=href,unterkonten_id={$f['zins_unterkonten_id']['value']}" ) );
          }

      open_tr( 'smallskips' );
        open_td( array( 'label' => $f['geschaeftsjahr_zinslauf_start'] ), 'Zinslauf ab Anfang des Jahres:' );
        open_td( 'colspan=1', selector_geschaeftsjahr( $f['geschaeftsjahr_zinslauf_start'] ) );
        open_td( 'qquad oneline' );
          open_label( $f['geschaeftsjahr_zinsauszahlung_start'], '', 'Ausschuettung ab:' );
          echo selector_geschaeftsjahr( $f['geschaeftsjahr_zinsauszahlung_start'] );

      open_tr();
        open_td( array( 'label' => $f['geschaeftsjahr_tilgung_start'] ), 'Tilgung erstmals Ende des Jahres:' );
        open_td( 'colspan=1', selector_geschaeftsjahr( $f['geschaeftsjahr_tilgung_start'] ) );
        open_td( 'qquad oneline' );
          open_label( $f['geschaeftsjahr_tilgung_ende'], '', 'letztmalig Ende des Jahres:' );
          echo selector_geschaeftsjahr( $f['geschaeftsjahr_tilgung_ende'] );

      open_tr( 'smallskip' );
        open_td( 'left' );
          if( ! sql_zahlungsplan( "darlehen_id=$darlehen_id" ) ) {
            echo inlink( '!', 'action=zahlungsplanBerechnen,text=Zahlungsplan berechnen' );
          }
        open_td( 'colspan=2,right' );
          echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
          echo save_button_view( $f['_changes'] ? '' : 'display=none' );

} // if $darlehen_unterkonten_id

    close_table();

    if( $problems ) {
      open_ul();
        flush_problems( 'tag=li' );
      close_ul();
    }

  medskip();
  if( $darlehen_id ) {

    open_fieldset( 'small_form', 'Buchungen:', 'on' );

      open_div( 'medskip', selector_geschaeftsjahr( $gj_buchungen_field ) );

      if( $darlehen_uk_id = $f['darlehen_unterkonten_id']['value'] ) {
        $buchungen_darlehen_uk_id = sql_get_folge_unterkonten_id( $darlehen_uk_id, $gj_buchungen );
      } else {
        $buchungen_darlehen_uk_id = 0;
      }
      if( $zins_uk_id = $f['zins_unterkonten_id']['value'] ) {
        $buchungen_zins_uk_id = sql_get_folge_unterkonten_id( $zins_uk_id, $gj_buchungen );
      } else {
        $buchungen_zins_uk_id = 0;
      }

      open_fieldset( 'small_form', $buchungen_darlehen_uk_id ? inlink( 'unterkonto', "unterkonten_id=$buchungen_darlehen_uk_id,text=Darlehenkonto" ) : 'Darlehenkonto' );
        if( $darlehen_uk_id ) {
          if( $buchungen_darlehen_uk_id ) {
            postenlist_view( array( 'unterkonten_id' => $buchungen_darlehen_uk_id ) );
          } else {
            open_div( 'center', '(kein Folgekonto angelegt)' );
          }
        } else {
          open_div( 'center', '(kein Darlehenkonto notiert)' );
        }
      close_fieldset();

      open_fieldset( 'small_form', $buchungen_zins_uk_id ? inlink( 'unterkonto', "unterkonten_id=$buchungen_zins_uk_id,text=Zinskonto" ) : 'Zinskonto' );
        if( $zins_uk_id ) {
          if( $buchungen_zins_uk_id ) {
            postenlist_view( array( 'unterkonten_id' => $buchungen_zins_uk_id ) );
          } else {
            open_div( 'center', '(kein Folgekonto angelegt)' );
          }
        } else {
          open_div( 'center', '(kein Zinskonto notiert)' );
        }
      close_fieldset();

      open_div( 'left' );

        $posten_auszahlung = array(
          'action' => 'init', 'buchungen_id' => 0
        , 'geschaeftsjahr' => $gj_buchungen, 'vorfall' => "Auszahlung $gj_buchungen Darlehen {$person['cn']}"
        , 'nH' => 1, 'pH0_unterkonten_id' => $default_girokonto_id, 'pH0_betrag' => '0'
        , 'nS' => 0
        );
        if( $buchungen_zins_uk_id ) {
          $n = $posten_auszahlung['nS']++;
          $posten_auszahlung[ "pS{$n}_unterkonten_id" ] = $buchungen_zins_uk_id;
          $posten_auszahlung[ "pS{$n}_beleg" ] = "Zinsausschuettung $gj_buchungen {$person['cn']}";
          $zp = sql_zahlungsplan( "darlehen_id=$darlehen_id,zins=1,geschaeftsjahr=$gj_buchungen,art=S" );
          if( count( $zp ) == 1 ) {
            $posten_auszahlung[ "pS{$n}_betrag" ] = $zp[ 0 ]['betrag'];
            $posten_auszahlung[ "pH0_betrag" ] += $zp[ 0 ]['betrag'];
          }
        }
        if( $buchungen_darlehen_uk_id ) {
          $n = $posten_auszahlung['nS']++;
          $posten_auszahlung[ "pS{$n}_unterkonten_id" ] = $buchungen_darlehen_uk_id;
          $posten_auszahlung[ "pS{$n}_beleg" ] = "Tilgung $gj_buchungen {$person['cn']}";
          $zp = sql_zahlungsplan( "darlehen_id=$darlehen_id,zins=0,geschaeftsjahr=$gj_buchungen,art=S" );
          if( count( $zp ) == 1 ) {
            $posten_auszahlung[ "pS{$n}_betrag" ] = $zp[ 0 ]['betrag'];
            $posten_auszahlung[ "pH0_betrag" ] += $zp[ 0 ]['betrag'];
          }
        }

        if( $buchungen_zins_uk_id ) {
          $posten_gutschrift = array(
            'action' => 'init', 'buchungen_id' => 0
          , 'geschaeftsjahr' => $gj_buchungen, 'vorfall' => "Zinsgutschrift $gj_buchungen Darlehen {$person['cn']}", 'valuta' => '1231'
          , 'nS' => 1, 'pS0_unterkonten_id' => $default_erfolgskonto_zinsaufwand_id, 'pS0_beleg' => "Zinsgutschrift $gj_buchungen {$person['cn']}"
          , 'nH' => 1, 'pH0_unterkonten_id' => $buchungen_zins_uk_id
          );
          $zp = sql_zahlungsplan( "darlehen_id=$darlehen_id,zins=1,geschaeftsjahr=$gj_buchungen,art=H" );
          if( count( $zp ) == 1 ) {
            $posten_gutschrift['pS0_betrag'] = $posten_gutschrift['pH0_betrag'] = $zp[ 0 ]['betrag'];
          }
          open_span( 'qquad', action_link(
            array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Gutschrift Zins' )
          , $posten_gutschrift
          ) );
        }

        if( $posten_auszahlung['nS'] ) {
          open_span( "qquad", action_link(
            array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Auszahlung' )
          , $posten_auszahlung
          ) );
        }
        $j = ( $gj_buchungen['value'] ? $gj_buchungen : $f['geschaeftsjahr_darlehen']['value'] );
        open_span( 'qquad', inlink( '!', array(
          'action' => 'zahlungsplanBerechnen', 'class' => 'button'
        , 'text' => "Zahlungsplan neu berechnen ab $j", 'confirm' => "Zahlungsplan ab $j neu berechnen?"
        ) ) );

      close_div();

      medskip();
      open_fieldset( '', inlink( 'zahlungsplanliste', "people_id={$darlehen_uk['people_id']},text=Zahlungsplan" ) );
        zahlungsplanlist_view( "darlehen_id=$darlehen_id,geschaeftsjahr=$gj_buchungen" );
      close_fieldset();

    close_fieldset();

//     if( sql_zahlungsplan( "darlehen_id=$darlehen_id" ) ) {
// 
//       open_fieldset( 'small_form', 'Zahlungsplan:', 'off' );
// 
//         open_div( 'medskip', filter_geschaeftsjahr( $gj_zahlungsplan_field
//         , array( 'min' => $f['geschaeftsjahr_darlehen']['value'], 'max' => $f['geschaeftsjahr_tilgung_ende'] )
//         ) );
// 
//         $j = ( $gj_zahlungsplan ? $gj_zahlungsplan : $f['geschaeftsjahr_darlehen']['value'] );
//         open_div( 'oneline' );
//           echo inlink( '', "action=zahlungsplanBerechnen,text=Zahlungsplan neu berechnen ab $j,confirm=Zahlungsplan neu berechnen?" );
//         close_div();
//         zahlungsplanlist_view( "darlehen_id=$darlehen_id" );
//       close_fieldset();
//     } else {
//       open_div( 'center' );
//         echo "(kein Zahlungsplan)";
//         qquad();
//         echo inlink( '', "action=zahlungsplanBerechnen,text=Zahlungsplan berechnen ab $j" );
//       close_div();
//     }

  } // if $darlehen_id


?>
