<?php

define( 'OPTION_SHOW_POSTEN', 1 );
init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window,default='.OPTION_SHOW_POSTEN );

init_var( 'unterkonten_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
init_var( 'flag_problems', 'type=u,sources=persistent,default=0,global,set_scopes=self' );

do {
  $reinit = false;

  if( $unterkonten_id ) {
    $uk = sql_one_unterkonto( $unterkonten_id );
    $hauptkonten_id = $uk['hauptkonten_id'];
    init_var( 'hauptkonten_id', "global,type=U,sources=initval,set_scopes=self,initval=$hauptkonten_id" );
  } else {
    $uk = array();
    init_var( 'hauptkonten_id', 'global,type=U,sources=http persistent,set_scopes=self' );
  }
  $hk = sql_one_hauptkonto( $hauptkonten_id );

  $unterkonten_fields = array(
    'cn' => 'H,size=40,default='
  , 'kommentar' => 'h,rows=2,cols=60'
  , 'zinskonto' => 'b'
  , 'unterkonten_hgb_klasse' => array( 'type' => 'a32' )
  , 'unterkonto_geschlossen' => 'b'
  , 'people_id' => 'u'
  , 'things_id' => 'type=u,sources=initval default'
  , 'bankkonten_id' => 'type=u,sources=initval default'
  );
  if( $hk['hauptkonten_hgb_klasse'] ) {
    $unterkonten_fields['unterkonten_hgb_klasse']['initval'] = $hk['hauptkonten_hgb_klasse'];
  }
  $bankkonten_fields = array(
    'bankkonten_bank' => 'h,size=40'
  , 'bankkonten_kontonr' => '/^\d[0-9 ]+\d$/,size=40'
  , 'bankkonten_blz' => '/^\d[0-9 ]+\d$/,size=40'
  , 'bankkonten_url' => 'h,size=40'
  );
  $things_fields = array(
    'things_cn' => 'H,size=40,default='
  , 'things_anschaffungsjahr' => 'U,size=4'
  , 'things_abschreibungszeit' => 'u,size=4'
  );
  $fields = $unterkonten_fields;
  $rows = array( 'unterkonten' => $uk );
  if( $hk['bankkonto'] ) {
    $fields += $bankkonten_fields;
    if( $uk )
      $rows += array( 'bankkonten' => sql_one_bankkonto( $uk['bankkonten_id'], array() ) );
  }
  if( $hk['sachkonto'] ) {
    $fields += $things_fields;
    if( $uk )
      $rows += array( 'things' => sql_one_thing( $uk['things_id'], array() ) );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => & $flag_modified
  , 'rows' => $rows
  , 'tables' => array( 'unterkonten', 'bankkonten', 'things' )
  , 'global' => true    // for convenience: ref-bind all values in global scope
  , 'failsafe' => false // retrieve and display offending values
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $action === 'reset' ) {
    $opts['reset'] = 1;
    $flag_problems = 0;
  }

  $f = init_fields( $fields, $opts );
  if( $hk['personenkonto'] ) {
    if( $people_id ) {
      $person = sql_person( $people_id, array() );
    } else {
      $person = array();
    }
  }

  $vortragskonto_name = ( $hk['vortragskonto'] ? 'Vortragskonto '.$hk['vortragskonto'] : '' );
  $geschaeftsjahr = $hk['geschaeftsjahr'];
  $hauptkonto_geschlossen = $hk['hauptkonto_geschlossen'];

  if( $flag_problems ) {
    if( $hk['personenkonto'] && ! $person ) {
      $problems[] = 'Person nicht gefunden';
      $f['_problems']['people_id'] = $people_id;
      $f['people_id']['class'] = 'problem';
    }
  }

  $kann_schliessen = false;
  $kann_oeffnen = false;
  $oeffnen_schliessen_problem = array();
  if( $unterkonten_id ) {
    if( $unterkonto_geschlossen ) {
      if( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen ) {
        $oeffnen_schliessen_problem[] = 'oeffnen nicht moeglich: geschaeftsjahr ist abgeschlossen';
      }
      if( $hauptkonto_geschlossen ) {
        $oeffnen_schliessen_problem[] = 'oeffnen nicht moeglich: hauptkonto ist geschlossen';
      }
      if( ! $oeffnen_schliessen_problem ) {
        $kann_oeffnen = true;
      }
    } else {
      $oeffnen_schliessen_problem = sql_unterkonto_schliessen( $unterkonten_id, 'check' );
      if( ! $oeffnen_schliessen_problem ) {
        $kann_schliessen = true;
      }
    }
  }

  $actions = array( 'update', 'reset', 'template' ); // 'deleteUnterkonto', 'delete' ???
  if( ! $unterkonto_geschlossen )
    $actions[] = 'save';
  if( $kann_oeffnen )
    $actions[] = 'oeffnen';
  if( $kann_schliessen )
    $actions[] = 'schliessen';
  handle_action( $actions );
  switch( $action ) {

    case 'template':
      $unterkonten_id = 0;
      reinit();
      break;
  
    case 'save':
  
      if( ! $f['_problems'] ) {

        $values = array();
        foreach( $unterkonten_fields as $fieldname => $type ) {
          $values[ $fieldname ] = $f[ $fieldname ]['value'];
        }
        if( ! $unterkonten_id ) {
          $values['hauptkonten_id'] = $hauptkonten_id;
        }
  
        if( $hk['sachkonto'] ) {
          $values_things = array();
          foreach( $things_fields as $fieldname => $type ) {
            $values_things[ substr( $fieldname, 7 ) ] = $f[ $fieldname ]['value'];
          }
          if( ( $things_id = $f['things_id']['value'] ) ) {
            sql_update( 'things', $things_id, $values_things );
          } else {
            $things_id = sql_insert( 'things', $values_things );
            $values['things_id'] = $things_id;
          }
        }
        if( $hk['bankkonto'] ) {
          $values_bankkonten = array();
          foreach( $bankkonten_fields as $fieldname => $type ) {
            $values_bankkonten[ substr( $fieldname, 11 ) ] = $f[ $fieldname ]['value'];
          }
          if( ( $bankkonten_id = $f['bankkonten_id']['value'] ) ) {
            sql_update( 'bankkonten', $bankkonten_id, $values_bankkonten );
          } else {
            $bankkonten_id = sql_insert( 'bankkonten', $values_bankkonten );
            $values['bankkonten_id'] = $bankkonten_id;
          }
        }
        if( $unterkonten_id ) {
          sql_update( 'unterkonten', $unterkonten_id, $values );
        } else {
          $unterkonten_id = sql_insert( 'unterkonten', $values );
        }
        for( $id = $unterkonten_id, $j = $geschaeftsjahr; $j < $geschaeftsjahr_max; $j++ ) {
          $id = sql_unterkonto_folgekonto_anlegen( $id );
        }
        reinit();

      }
      break;
  
    case 'schliessen':
      need( $kann_schliessen, $oeffnen_schliessen_problem );
      sql_unterkonto_schliessen( $unterkonten_id );
      reinit();
      break;
  
    case 'oeffnen':
      need( $kann_oeffnen, $oeffnen_schliessen_problem );
      sql_update( 'unterkonten', $unterkonten_id, array( 'unterkonto_geschlossen' => 0 ) );
      for( $id = unterkonten_id, $j = $geschaeftsjahr; $j < $geschaeftsjahr_max; $j++ ) {
        $id = sql_unterkonto_folgekonto_anlegen( $id );
      }
      reinit();
      break;
  }

} while( $reinit );

if( $unterkonten_id ) {
  open_fieldset( 'small_form old', "Stammdaten Unterkonto [$unterkonten_id]" );
} else {
  open_fieldset( 'small_form new', 'neues Unterkonto' );
}
  open_table( 'hfill,colgroup=20% 80%');
    open_tr( 'smallskip' );
      open_td( '', 'Geschaeftsjahr: ' );
      open_td( 'qquad' );
        if( $unterkonten_id ) {
          $pred = sql_one_unterkonto( array( 'folge_unterkonten_id' => $unterkonten_id ), true );
          $pred_id = adefault( $pred, 'unterkonten_id', 0 );

          open_span( '', inlink( '', array( 'class' => 'button', 'text' => ' < '
                                                     , 'unterkonten_id' => $pred_id , 'inactive' => ( $pred_id == 0 )
          ) ) );

          open_span( 'quads bold', $uk['geschaeftsjahr'] );
          $succ_id = $uk['folge_unterkonten_id'];
          open_span( '', inlink( '', array( 'class' => 'button', 'text' => ' > '
                                                     , 'unterkonten_id' => $succ_id, 'inactive' => ( $succ_id == 0 )
          ) ) );
        } else {
          echo $hk['geschaeftsjahr'];
        }
    open_tr( 'smallskip' );
      open_td( '', 'Kontoklasse: ' );
      open_td( 'qquad', "{$hk['kontoklassen_cn']} {$hk['geschaeftsbereich']}" );
    open_tr( 'smallskip' );
      open_td( '', 'Hauptkonto: ' );
      open_td( 'qquad' );
        echo inlink( 'hauptkonto', array(
          'hauptkonten_id' => $hauptkonten_id
        , 'text' => html_tag( 'span', 'bold', "{$hk['kontenkreis']} {$hk['seite']}" ) ." {$hk['rubrik']} / {$hk['titel']}"
        ) );

    open_tr( 'smallskip' );
      open_td( '', 'Attribute: ' );
      open_td( 'qquad' );
        if( $vortragskonto_name ) {
          open_span( 'bold', $vortragskonto_name );
          qquad();
        }
        open_span( 'online,quads' );
          open_label( $f['zinskonto'], "Sonderkonto Zins:" );
          echo checkbox_element( $f['zinskonto'] );
        close_span();

    open_tr();
      open_td( array( 'label' => $f['cn'] ), 'Kontobezeichnung:' );
      open_td( '', string_element( $f['cn'] ) );

    open_tr();
      open_td( array( 'label' => $f['unterkonten_hgb_klasse'] ), 'HGB-Klasse:' );
      open_td( '' );
        if( $hk['hauptkonten_hgb_klasse'] ) {
          echo open_span( 'kbd', $hk['hauptkonten_hgb_klasse'] );
        } else {
          echo selector_hgb_klasse( $f['unterkonten_hgb_klasse'] );
        }

    if( $hk['bankkonto'] ) {
      open_tr('medskip');
        open_td( array( 'label' => $f['bankkonten_bank'] ), 'Bank:' );
        open_td( '', string_element( $f['bankkonten_bank'] ) );
      open_tr();
        open_td( array( 'label' => $f['bankkonten_kontonr'] ), 'Konto-Nr:' );
        open_td( '', string_element( $f['bankkonten_kontonr'] ) );
      open_tr();
        open_td( array( 'label' => $f['bankkonten_blz'] ), 'BLZ:' );
        open_td( '', string_element( $f['bankkonten_blz'] ) );
      open_tr();
        open_td( array( 'label' => $f['bankkonten_blz'] ), 'url:' );
        open_td( '', string_element( $f['bankkonten_url'] ) );
    }

    if( $hk['personenkonto'] ) {
      open_tr( 'medskip' );
        open_td( array( 'label' => $f['people_id'] ), 'Person:' );
        open_td( 'oneline' );
          echo selector_people( $f['people_id'] );
          if( $f['people_id']['value'] )
            open_span( 'qquad', inlink( 'person', array( 'class' => 'people', 'text' => '', 'people_id' => $f['people_id']['value'] ) ) );
    }

    if( $hk['sachkonto'] ) {
      open_tr( 'medskip' );
        open_td( array( 'label' => $f['things_cn'] ), 'Gegenstand:' );
        open_td( '', string_element( $f['things_cn'] ) );
      open_tr();
        open_td( array( 'label' => $f['things_anschaffungsjahr'] ), 'Anschaffungsjahr:' );
        open_td( '', int_element( $f['things_anschaffungsjahr'] ) );
      open_tr();
        open_td( array( 'label' => $f['things_abschreibungszeit'] ), 'Abschreibungszeit:' );
       open_td( '', int_element( $f['things_abschreibungszeit'] ) );
    }

    open_tr( 'medskip' );
      open_td( array( 'label' => $f['kommentar'] ), 'Kommentar:' );
      open_td( '', textarea_element( $f['kommentar'] ) );

    open_tr( 'medskip' );
      open_td( 'right,colspan=2' );
        if( $unterkonten_id && ! $f['_changes'] )
          template_button();
        reset_button( $f['_changes'] ? '' : 'display=none' );
        submission_button( $f['_changes'] ? '' : 'display=none' );


  if( $unterkonten_id ) {
    open_tr( 'medskip' );
      open_td();
      echo 'Status:';
      open_td();
      if( $unterkonto_geschlossen ) {
        open_span( 'quads', 'Konto ist geschlossen' );
        if( $kann_oeffnen ) {
          open_span( 'quads', action_button_view( 'text=wieder oeffnen', 'action=oeffnen' ) );
        } else {
          open_ul();
            flush_messages( $oeffnen_schliessen_problem, 'class=info,tag=li'  );
          close_ul();
        }
      } else {
        open_span( 'quads', 'offen' );
        if( $kann_schliessen ) {
          open_span( 'quads', action_button_view( 'text=konto schliessen', 'action=schliessen' ) );
        } else {
          open_ul();
            flush_messages( $oeffnen_schliessen_problem, 'class=info,tag=li'  );
          close_ul();
        }
      }
  }

  close_table();

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

  if( $unterkonten_id && ! $unterkonto_geschlossen ) {
    open_div( 'smallskips' );
      open_span( "qquad,style=float:left;", action_button_view(
        array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Soll' )
      , array( 'action' => 'init', 'buchungen_id' => 0, 'nS' => 1, 'pS0_unterkonten_id' => $unterkonten_id, 'nH' => 1, 'geschaeftsjahr' => $geschaeftsjahr )
      ) );
      open_span( "qquad,style=float:right;", action_button_view(
        array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Haben' )
      , array( 'action' => 'init', 'buchungen_id' => 0, 'nS' => 1, 'pH0_unterkonten_id' => $unterkonten_id, 'nH' => 1, 'geschaeftsjahr' => $geschaeftsjahr )
      ) );
    close_div();
  }

  if( $unterkonten_id && ( $options & OPTION_SHOW_POSTEN ) ) {
    bigskip();
    open_fieldset( 'small_form'
      , inlink( 'self', array( 'options' => $options & ~OPTION_SHOW_POSTEN, 'class' => 'close_small' ) )
        . ' Posten: '
    );
      postenlist_view( array( 'unterkonten_id' => $unterkonten_id ) );
    close_fieldset();
  }

  if( $unterkonten_id && $hk['personenkonto'] ) {
    $zahlungsplan = sql_zahlungsplan( array( 'unterkonten_id' => $unterkonten_id ) );
    if( $zahlungsplan ) {
      medskip();
      open_fieldset( 'small_form', 'Darlehen mit Zahlungsplan zu diesem Konto' );
        $darlehen = array();
        foreach( $zahlungsplan as $z )
          $darlehen[ $z['darlehen_id'] ] = $z['darlehen_id'];
        darlehenlist_view( array( 'darlehen_id' => $darlehen ) );
      close_fieldset();
    }
  }

close_fieldset();

?>
