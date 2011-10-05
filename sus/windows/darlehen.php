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
      $sources = 'keep default';
      $flag_problems = 0;
      break;
    case 'http':
      init_var( 'darlehen_id', 'global,pattern=u,sources=self,set_scopes=self' );
      $sources = 'http self';
      break;
    case 'persistent':
      init_var( 'darlehen_id', 'global,pattern=u,sources=self,set_scopes=self' );
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
    $darlehen_uk = sql_one_unterkonto( $darlehen['darlehen_unterkonten_id'] );
    $darlehen_hk = sql_one_hauptkonto( $darlehen_uk['hauptkonten_id'] );
    $person = sql_person( $darlehen_uk['people_id'] );
    $opts['rows'] = array( 'darlehen' => $darlehen );
    init_var( 'geschaeftsjahr', 'global,pattern=U,sources=,set_scopes=self,default='.$darlehen['geschaeftsjahr_darlehen'] );
  } else {
    $flag_modified = 0;
    $darlehen_uk = $darlehen_hk = $person = array();
    init_var( 'geschaeftsjahr', "global,pattern=U,sources=http self,set_scopes=self,default=$geschaeftsjahr_thread" );
  }

  $jahr_max = $geschaeftsjahr + 99;
  $fields = array(
    'kommentar' => 'h,cols=60,rows=2'
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
  , 'geschaeftsjahr_tilgung_ende' =>  array(
       'pattern' => 'U', 'default' => $geschaeftsjahr + 1
     , 'min' => $f['geschaeftsjahr_darlehen']['value'], 'max' => $jahr_max
     )
  , 'zins_prozent' => 'f,format=%.2f'
  , 'betrag_zugesagt' => 'f,format=%.2f'
  , 'betrag_abgerufen' => 'f,format=%.2f'
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
      sql_zahlungsplan_berechnen( $darlehen_id, 'delete' );
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

  open_table( 'hfill,colgroup=40% 30% 30%' );
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
        open_td( array( 'label' => $f['kommentar'] ), 'Kommentar:' );
        open_td( 'colspan=2', textarea_element( $f['kommentar'] ) );

      open_tr( 'smallskips' );
        open_td( array( 'label' => $f['betrag_zugesagt'] ), 'Betrag zugesagt:' );
        open_td( 'colspan=2', price_element( $f['betrag_zugesagt'] ) );

      open_tr( 'smallskips' );
        open_td( array( 'label' => $f['betrag_abgerufen'] ), 'Betrag abgerufen:' );
        open_td( 'colspan=2', price_element( $f['betrag_abgerufen'] ) );

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
        open_td( 'colspan=2' );
          selector_geschaeftsjahr( $f['geschaeftsjahr_zinslauf_start'] );

      open_tr();
        open_td( array( 'label' => $f['geschaeftsjahr_tilgung_start'] ), 'Tilgung erstmals Ende des Jahres:' );
        open_td( 'colspan=2' );
          selector_geschaeftsjahr( $f['geschaeftsjahr_tilgung_start'] );

      open_tr();
        open_td( array( 'label' => $f['geschaeftsjahr_tilgung_ende'] ), 'Tilgung letzmalig Ende des Jahres:' );
        open_td( 'colspan=2' );
          selector_geschaeftsjahr( $f['geschaeftsjahr_tilgung_ende'] );

      open_tr( 'smallskip' );
        open_td( 'colspan=3,right' );
        reset_button( $f['_changes'] ? '' : 'display=none' );
        submission_button( $f['_changes'] ? '' : 'display=none' );
} // ! $darlehen_id )

    close_table();

    if( $problems ) {
      open_ul();
        flush_problems();
      close_ul();
    }

  medskip();
  if( $darlehen_id ) {
    if( sql_zahlungsplan( "darlehen_id=$darlehen_id" ) ) {
      echo action_button_view( 'action=zahlungsplanBerechnen,text=Zahlungsplan neu berechnen,confirm=Zahlungsplan neu berechnen?' );
      open_fieldset( 'small_form', 'Zahlungsplan:' );
        zahlungsplanlist_view( "darlehen_id=$darlehen_id" );
      close_fieldset();
    } else {
      open_div( 'center' );
        echo "(kein Zahlungsplan)";
        qquad();
        echo action_button_view( 'action=zahlungsplanBerechnen,text=Zahlungsplan erstellen' );
      close_div();
    }

    if( $f['darlehen_unterkonten_id']['value'] ) {
      open_fieldset( 'small_form', 'Darlehenkonto:' );
        postenlist_view( array( 'unterkonten_id' => $f['darlehen_unterkonten_id']['value'] ) );
      close_fieldset();
    }

    if( $f['zins_unterkonten_id']['value'] ) {
      open_fieldset( 'small_form', 'Sonderkonto Zins:' );
        postenlist_view( array( 'unterkonten_id' => $f['zins_unterkonten_id']['value'] ) );
      close_fieldset();
    }
  }

?>
