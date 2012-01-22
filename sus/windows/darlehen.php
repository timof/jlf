<?php

init_var( 'options', 'global,pattern=u,sources=http persistent,set_scopes=window,default=0' );

if( $parent_script !== 'self' ) {
  $reinit = 'init';  // generate empty entry, plus initialization from http
} else if( $action === 'reset' ) {
  $reinit = 'reset'; // re-initialize from db, or generate empty entry
} else {
  $reinit = 'http';
}

do {

  switch( $reinit ) {
    case 'init':
      init_var( 'darlehen_id', 'global,pattern=u,sources=http,default=0,set_scopes=self' );
      if( ! $darlehen_id ) {
        init_var( 'flag_problems', 'global,pattern=b,sources=,default=0,set_scopes=self' );
        $sources = 'http default';
        break;
      } else {
        // fall-through...
      }
    case 'reset':
      init_var( 'darlehen_id', 'global,pattern=u,sources=self,set_scopes=self' );
      init_var( 'flag_problems', 'global,pattern=b,sources=,default=0,set_scopes=self' );
      $sources = 'keep default';
      break;
    case 'http':
      init_var( 'darlehen_id', 'global,pattern=u,sources=self,set_scopes=self' );
      init_var( 'flag_problems', 'global,pattern=b,sources=self,default=0,set_scopes=self' );
      $sources = 'http self';
      break;
    case 'persistent':
      init_var( 'darlehen_id', 'global,pattern=u,sources=self,set_scopes=self' );
      init_var( 'flag_problems', 'global,pattern=b,sources=self,default=0,set_scopes=self' );
      $sources = 'self';
      break;
    default:
      error( 'cannot initialize - invalid $reinit' );
  }
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
    // debug( $darlehen, 'darlehen' );
    $darlehen_uk = sql_one_unterkonto( $darlehen['darlehen_unterkonten_id'] );
    $darlehen_hk = sql_one_hauptkonto( $darlehen_uk['hauptkonten_id'] );
    $person = sql_person( $darlehen_uk['people_id'] );
    $opts['rows'] = array( 'darlehen' => $darlehen );
    init_var( 'geschaeftsjahr', 'global,pattern=U,sources=,set_scopes=self,default='.$darlehen['geschaeftsjahr_darlehen'] );
    // fuer berechnung zahlungsplan:
    init_var( 'gj_zahlungsplan', "global,pattern=u,sources=http persistent,set_scopes=self,default={$darlehen['geschaeftsjahr']}" );
  } else {
    $flag_modified = 0;
    $darlehen_uk = $darlehen_hk = $person = array();
    init_var( 'geschaeftsjahr', "global,pattern=U,sources=http self,set_scopes=self,default=$geschaeftsjahr_thread" );
    init_var( 'gj_zahlungsplan', 'global,pattern=u,sources=,default=0' );
  }

  $jahr_max = $geschaeftsjahr + 99;
  $fields = array(
    'cn' => 'h,cols=60'
  , 'kommentar' => 'h,cols=60,rows=2'
  , 'geschaeftsjahr_darlehen' => array( 
       'pattern' => 'U', 'default' => $geschaeftsjahr
     ,'sources' => ( $darlehen_id ? 'keep' : 'http persistent' )
     )
  , 'geschaeftsjahr_tilgung_start' => array(
       'pattern' => 'U', 'default' => $geschaeftsjahr + 1
     , 'min' => $f['geschaeftsjahr_darlehen']['value'] , 'max' => $jahr_max
     )
  , 'geschaeftsjahr_zinslauf_start' => array(
       'pattern' => 'U', 'default' => $geschaeftsjahr + 1
     , 'min' => $f['geschaeftsjahr_darlehen']['value'], 'max' => $jahr_max
     )
  , 'geschaeftsjahr_zinsauszahlung_start' => array(
       'pattern' => 'U', 'default' => $geschaeftsjahr + 1
     , 'min' => $f['geschaeftsjahr_darlehen']['value'], 'max' => $jahr_max
     )
  , 'geschaeftsjahr_tilgung_ende' =>  array(
       'pattern' => 'U', 'default' => $geschaeftsjahr + 1
     , 'min' => $f['geschaeftsjahr_darlehen']['value'], 'max' => $jahr_max
     )
  , 'zins_prozent' => 'f,format=%.2f'
  , 'betrag_zugesagt' => 'f,format=%.2f'
  , 'betrag_abgerufen' => 'f,format=%.2f'
  , 'valuta_betrag_abgerufen' => array(
      'default' => sprintf( '%04u', ( $valuta_letzte_buchung ? $valuta_letzte_buchung : 100 * $now[1] + $now[2] ) )
    , 'pattern' => 'U', 'min' => 100, 'max' => 1231, 'format' => '%04u'
    )
  , 'darlehen_unterkonten_id' => 'U'
  , 'zins_unterkonten_id' => 'u'
  );
  if( ! $darlehen_id ) {
    $fields['hauptkonten_id'] = 'u';
    $fields['people_id'] = 'U';
  }
  $f = init_fields( $fields, $opts );

  if( ! $darlehen_id ) {
    if( $f['darlehen_unterkonten_id']['value'] ) {
      $darlehen_uk = sql_one_unterkonto( $f['darlehen_unterkonten_id']['value'] );
      $f['people_id']['value'] = $darlehen_uk['people_id'];
      $f['hauptkonten_id']['value'] = $darlehen_uk['hauptkonten_id'];
    }
    if( $f['people_id']['value'] ) {
      $person = sql_person( $f['people_id']['value'] );
    }
    if( $f['hauptkonten_id']['value'] ) {
      $darlehen_hk = sql_one_hauptkonto( $f['hauptkonten_id']['value'] );
      $f['geschaeftsjahr_darlehen']['value'] = $darlehen_hk['geschaeftsjahr'];
    }
  }

//   $darlehen_uk = $zins_uk = array();
//   if( $f['darlehen_unterkonten_id']['value'] ) {
//     $darlehen_uk = sql_one_unterkonto( $f['darlehen_unterkonten_id']['value'], array() );
//     $f['people_id']['value'] = $darlehen_uk['people_id'];
//   }
//   if( $f['zins_unterkonten_id']['value'] ) {
//     $zins_uk = sql_one_unterkonto( $f['zins_unterkonten_id']['value'];
//     if( $darlehen_uk && ( $zins_uk['people_id'] != $darlehen_uk['people_id'] ) ) {
//       $zins_uk = array();
//       $f['zins_unterkonten_id']['value'] = 0;
//     }
//   }

  if( $flag_problems ) {
    if( $f['geschaeftsjahr_tilgung_start']['value'] < $f['geschaeftsjahr_darlehen']['value'] ) {
      $f['_problems']['geschaeftsjahr_tilgung_start'] = $f['geschaeftsjahr_tilgung_start']['problem'] = $f['geschaeftsjahr_tilgung_start']['value'];
      $f['geschaeftsjahr_tilgung_start']['class'] = 'problem';
    }
    if( $f['geschaeftsjahr_tilgung_ende']['value'] < $f['geschaeftsjahr_tilgung_start']['value'] ) {
      $f['_problems']['geschaeftsjahr_tilgung_ende'] = $f['geschaeftsjahr_tilgung_ende']['problem'] = $f['geschaeftsjahr_tilgung_ende']['value'];
      $f['geschaeftsjahr_tilgung_ende']['class'] = 'problem';
    }
    if( $f['geschaeftsjahr_zinslauf_start']['value'] < $f['geschaeftsjahr_darlehen']['value'] ) {
      $f['_problems']['geschaeftsjahr_zinslauf_start'] = $f['geschaeftsjahr_zinslauf_start']['problem'] = $f['geschaeftsjahr_zinslauf_start']['value'];
      $f['geschaeftsjahr_zinslauf_ende']['class'] = 'problem';
    }
    if( $f['darlehen_unterkonten_id']['value'] ) {
      $uk = sql_one_unterkonto( $f['darlehen_unterkonten_id']['value'], array() );
      if( ! $uk ) {
        $f['darlehen_unterkonten_id'] = 0;
      } else {
        if( ! $uk['personenkonto'] ) {
          $f['_problems']['darlehen_unterkonten_id'] = $f['darlehen_unterkonten_id']['problem'] = $f['darlehen_unterkonten_id']['value'];
          $f['darlehen_unterkonten_id']['class'] = 'problem';
          $problems[] = 'Darlehenkonto: kein Personenkonto!';
        }
        if( $uk['geschaeftsjahr'] !== $f['geschaeftsjahr_darlehen']['value'] ) {
          $f['_problems']['darlehen_unterkonten_id'] = $f['darlehen_unterkonten_id']['problem'] = $f['darlehen_unterkonten_id']['value'];
          $f['darlehen_unterkonten_id']['class'] = 'problem';
          $problems[] = 'Darlehenkonto: falsches Geschaeftsjahr!';
        }
      }
    }
    if( $f['zins_unterkonten_id']['value'] ) {
      $zk = sql_one_unterkonto( $f['zins_unterkonten_id']['value'], array() );
      if( ! $zk ) {
        $f['zins_unterkonten_id'] = 0;
      } else {
        if( ! $zk['zinskonto'] ) {
          $f['_problems']['zins_unterkonten_id'] = $f['zins_unterkonten_id']['problem'] = $f['zins_unterkonten_id']['value'];
          $f['zins_unterkonten_id']['class'] = 'problem';
          $problems[] = 'Zinskonto: kein Sonderkonto Zins!';
        }
        if( $zk['geschaeftsjahr'] !== $f['geschaeftsjahr_darlehen']['value'] ) {
          $f['_problems']['zins_unterkonten_id'] = $f['zins_unterkonten_id']['problem'] = $f['zins_unterkonten_id']['value'];
          $f['zins_unterkonten_id']['class'] = 'problem';
          $problems[] = 'Zinskonto: falsches Geschaeftsjahr!';
        }
      }
    }
  }

  $gj_buchungen_field = init_var( 'gj_buchungen'
  , array(
      'name' => 'gj_buchungen'
    , 'global' => true
    , 'pattern' => 'U'
    , 'default' => max( $geschaeftsjahr, $geschaeftsjahr_current )
    , 'min' => $geschaeftsjahr
    , 'max' => $f['geschaeftsjahr_tilgung_ende']['value']
    , 'sources' => 'self http'
    )
  );

  $reinit = false;

  $actions = array( 'save', 'update', 'init', 'reset' );

  if( $darlehen_id ) {
    $actions[] = 'zahlungsplanBerechnen';
  }
  if( adefault( $f, 'people_id', 0 ) && $f['hauptkonten_id']['value'] ) {
    $actions[] = 'darlehenkontoAnlegen';
  }
  if( $person  && ! $f['zins_unterkonten_id']['value'] ) {
    $actions[] = 'zinskontoAnlegen';
  }
  handle_action( $actions );

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
      open_td( 'bold,colspan=2' );
        selector_geschaeftsjahr( $f['geschaeftsjahr_darlehen'] );


      open_tr();
        open_td( array( 'label' => $f['hauptkonten_id'] ), 'Hauptkonto:' );
        open_td( 'colspan=2' );
          selector_hauptkonto( $f['hauptkonten_id'], array( 'filters' => $filters_hk ) );
          if( $f['hauptkonten_id']['value'] ) {
            open_div( '', inlink( 'hauptkonto', "text=zum hauptkonto...,class=href,hauptkonten_id={$f['hauptkonten_id']['value']}" ) );
          }

      open_tr();
        open_td( array( 'label' => $f['people_id'] ), 'Kreditor:' );
        open_td( 'colspan=2' );
          selector_people( $f['people_id'] );

  if( $f['people_id']['value'] ) {
         open_div( '', inlink( 'person', "class=people,text=zur Person...,class=href,people_id={$f['people_id']['value']}" ) );

      open_tr();
        open_td( array( 'label' => $f['darlehen_unterkonten_id'] ), 'Darlehenkonto:' );
        open_td( 'colspan=2' );
          $filters_uk['zinskonto'] = 0;
          selector_unterkonto( $f['darlehen_unterkonten_id'], array( 'filters' => $filters_uk ) );
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
          open_label( $f['betrag_abgerufen'], 'abgerufen:' );
          echo price_element( $f['betrag_abgerufen'] );
          quad();
          open_label( $f['valuta_betrag_abgerufen'], 'valuta:' );
          echo monthday_element( $f['valuta_betrag_abgerufen'] );

      open_tr();
        open_td( array( 'label' => $f['zins_prozent'] ), 'Zinssatz:' );
        open_td( 'colspan=2', price_element( $f['zins_prozent'] ).' %' );

      open_tr();
        open_td( array( 'label' => $f['zins_unterkonten_id'] ), 'Sonderkonto Zins:' );
        open_td( 'colspan=2' );
            $filters_uk['zinskonto'] = 1;
            selector_unterkonto( $f['zins_unterkonten_id'], array(
              'filters' => $filters_uk
            , 'more_choices' => array( '!empty' => '(kein Zinskonto angelegt)', '0' => ' --- kein Sonderkonto fuer Zins ---' )
            ) );
          if( $f['zins_unterkonten_id']['value'] ) {
            open_div( '', inlink( 'unterkonto', "text=zum Zinskonto...,class=href,unterkonten_id={$f['zins_unterkonten_id']['value']}" ) );
          }

      open_tr( 'smallskips' );
        open_td( array( 'label' => $f['geschaeftsjahr_zinslauf_start'] ), 'Zinslauf ab Anfang des Jahres:' );
        open_td( 'colspan=1' );
          selector_geschaeftsjahr( $f['geschaeftsjahr_zinslauf_start'] );
        open_td( 'qquad oneline' );
          open_label( $f['geschaeftsjahr_zinsauszahlung_start'], 'Ausschuettung ab:' );
          selector_geschaeftsjahr( $f['geschaeftsjahr_zinsauszahlung_start'] );

      open_tr();
        open_td( array( 'label' => $f['geschaeftsjahr_tilgung_start'] ), 'Tilgung erstmals Ende des Jahres:' );
        open_td( 'colspan=1' );
          selector_geschaeftsjahr( $f['geschaeftsjahr_tilgung_start'] );
        open_td( 'qquad oneline' );
          open_label( $f['geschaeftsjahr_tilgung_ende'], 'letztmalig Ende des Jahres:' );
          selector_geschaeftsjahr( $f['geschaeftsjahr_tilgung_ende'] );

      open_tr( 'smallskip' );
        open_td( 'left' );
          if( ! sql_zahlungsplan( "darlehen_id=$darlehen_id" ) ) {
            echo inlink( '!submit', 'action=zahlungsplanBerechnen,text=Zahlungsplan berechnen' );
          }
        open_td( 'colspan=2,right' );
          reset_button( $f['_changes'] ? '' : 'display=none' );
          submission_button( $f['_changes'] ? '' : 'display=none' );

} // if $darlehen_unterkonten_id

    close_table();

    if( $problems ) {
      open_ul();
        flush_problems();
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
          open_span( 'qquad', action_button_view(
            array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Gutschrift Zins' )
          , $posten_gutschrift
          ) );
        }

        if( $posten_auszahlung['nS'] ) {
          open_span( "qquad", action_button_view(
            array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Auszahlung' )
          , $posten_auszahlung
          ) );
        }
        $j = ( $gj_buchungen['value'] ? $gj_buchungen : $f['geschaeftsjahr_darlehen']['value'] );
        open_span( 'qquad', inlink( '!submit', array(
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
//           echo action_button_view( "action=zahlungsplanBerechnen,text=Zahlungsplan neu berechnen ab $j,confirm=Zahlungsplan neu berechnen?" );
//         close_div();
//         zahlungsplanlist_view( "darlehen_id=$darlehen_id" );
//       close_fieldset();
//     } else {
//       open_div( 'center' );
//         echo "(kein Zahlungsplan)";
//         qquad();
//         echo action_button_view( "action=zahlungsplanBerechnen,text=Zahlungsplan berechnen ab $j" );
//       close_div();
//     }

  } // if $darlehen_id


?>
