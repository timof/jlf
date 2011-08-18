<?php

define( 'OPTION_SHOW_POSTEN', 1 );
init_global_var( 'options', 'u', 'http,persistent', OPTION_SHOW_POSTEN, 'window' );

init_global_var( 'unterkonten_id', 'u', 'http,persistent', 0, 'self' );
if( $unterkonten_id ) {
  $uk = sql_one_unterkonto( $unterkonten_id );
} else {
  $uk = false;
}
row2global( 'unterkonten', $uk );

if( ! $uk ) {
  init_global_var( 'hauptkonten_id', 'U', 'http,persistent', NULL, 'self' );
}
$hk = sql_one_hauptkonto( $hauptkonten_id );
row2global( 'hauptkonten', $hk, array( 'kommentar' => 'hauptkonten_kommentar' ) );

$problems = array();
$changes = array();

$fields = array(
  'cn' => 'H'
, 'kommentar' => 'h'
, 'hauptkonten_id' => 'U'
, 'zinskonto' => 'b'
, 'unterkonten_hgb_klasse' => 'h'
);
$personenkonto_fields = array(
  'people_id' => 'U'
);
$bankkonto_fields = array(
  'bankkonten_bank' => 'h'
, 'bankkonten_kontonr' => '/[0-9 ]+/'
, 'bankkonten_blz' => '/[0-9 ]+/'
, 'bankkonten_url' => 'h'
);
$sachkonto_fields = array(
  'things_cn' => 'H'
, 'things_anschaffungsjahr' => 'U'
, 'things_abschreibungszeit' => 'u'
);

$all_fields = $fields;
if( ( $is_personenkonto = $hk['personenkonto'] ) ) {
  $all_fields = tree_merge( $all_fields, $personenkonto_fields );
}
if( ( $is_bankkonto = $hk['bankkonto'] ) ) {
  $all_fields = tree_merge( $all_fields, $bankkonto_fields );
  $bankkonto = ( $bankkonten_id ? sql_one_bankkonto( $bankkonten_id, 0 ) : false );
  row2global( 'bankkonten', $bankkonto, 'bankkonten_' );
}
if( ( $is_sachkonto = $hk['sachkonto'] ) ) {
  $all_fields = tree_merge( $all_fields, $sachkonto_fields );
  $thing = ( $uk ? sql_one_thing( $things_id, 0 ) : false );
  row2global( 'things', $thing, 'things_' );
}
$vortragskonto_name = ( $hk['vortragskonto'] ? 'Vortragskonto '.$hk['vortragskonto'] : '' );

init_global_var( 'action', 'w', 'http', 'nop' );
if( $action !== 'reset' ) {
  foreach( $all_fields as $fieldname => $type ) {
    init_global_var( $fieldname, $type, 'http,persistent,keep', '', 'self' );
    if( $uk ) {
      if( $GLOBALS[ $fieldname ] !== $uk[ $fieldname ] ) {
        $changes[ $fieldname ] = 'modified';
      }
    }
  }
}

if( $people_id ) {
  if( ! sql_person( $people_id, 0 ) ) {
    $people_id = 0;
    $problems['people_id'] = 'Person nicht gefunden';
  }
}
if( $things_id ) {
  if( ! sql_one_thing( $things_id, 0 ) ) {
    $things_id = 0;
  }
}
if( $bankkonten_id ) {
  if( ! sql_one_bankkonto( $bankkonten_id, 0 ) ) {
    $bankkonten_id = 0;
  }
}


$kann_schliessen = false;
$kann_oeffnen = false;
$oeffnen_schliessen_problem = '';
if( $unterkonten_id ) {
  if( $unterkonto_geschlossen ) {
    if( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen ) {
      $oeffnen_schliessen_problem = 'oeffnen nicht moeglich: geschaeftsjahr ist abgeschlossen';
    }
    if( $hauptkonto_geschlossen ) {
      $oeffnen_schliessen_problem = 'oeffnen nicht moeglich: hauptkonto ist geschlossen';
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


$actions = array( 'update', 'reset', 'deleteUnterkonto', 'delete', 'template' );
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
    break;

  case 'init':
    // nop
    break;

  case 'save':

    need( ! $unterkonto_geschlossen );
    if( ! $cn )
      $problems[] = 'cn';

    $values = array();
    foreach( $fields as $fieldname => $type ) {
      if( checkvalue( $$fieldname, $type ) !== NULL ) {
        $values[ $fieldname ] = $$fieldname;
      } else {
        $problems[ $fieldname ] = 'type mismatch';
      }
    }

    if( $is_personenkonto ) {
      if( ! $people_id )
        $problems[] = 'people_id';
      $values['people_id'] = $people_id;
    } else {
      $values['people_id'] = 0;
    }

    if( $is_sachkonto ) {
      $values_things = array();
      foreach( $sachkonto_fields as $fieldname => $type ) {
        if( checkvalue( $$fieldname, $type ) !== NULL ) {
          $values_things[ substr( $fieldname, 7 ) ] = $$fieldname;
        } else {
          $problems[ $fieldname ] = 'type mismatch';
        }
      }
      $values['things_id'] = $things_id;
    } else {
      $values['things_id'] = 0;
    }

    if( $is_bankkonto ) {
      $values_bankkonten = array();
      foreach( $bankkonto_fields as $fieldname => $type ) {
        if( checkvalue( $$fieldname, $type ) !== NULL ) {
          $values_bankkonten[ substr( $fieldname, 10 ) ] = $$fieldname;
        } else {
          $problems[ $fieldname ] = 'type mismatch';
        }
      }
      $values['bankkonten_id'] = $bankkonten_id;
    } else {
      $values['bankkonten_id'] = 0;
    }

    if( ! $problems ) {
      if( $is_sachkonto ) {
        if( $things_id ) {
          sql_update( 'things', $things_id, $values_things );
        } else {
          $things_id = sql_insert( 'things', $values_things );
          $values['things_id'] = $things_id;
        }
      }
      if( $is_bankkonto ) {
        if( $bankkonten_id ) {
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
    }
  break;

  case 'schliessen':
    need( $kann_schliessen, $oeffnen_schliessen_problem );
    sql_unterkonto_schliessen( $unterkonten_id );
    schedule_reload();
    return;

  case 'oeffnen':
    need( $kann_oeffnen, $oeffnen_schliessen_problem );
    sql_update( 'unterkonten', $unterkonten_id, array( 'unterkonto_geschlossen' => 0 ) );
    for( $id = unterkonten_id, $j = $geschaeftsjahr; $j < $geschaeftsjahr_max; $j++ ) {
      $id = sql_unterkonto_folgekonto_anlegen( $id );
    }
    schedule_reload();
    return;
}

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
        } else {
          $pred_id = 0;
        }
        open_span( '', inlink( '', array( 'class' => 'button', 'text' => ' &lt; '
                                                   , 'unterkonten_id' => $pred_id , 'inactive' => ( $pred_id == 0 )
        ) ) );
        open_span( 'quads bold', $geschaeftsjahr );
        $succ_id = $uk['folge_unterkonten_id'];
        open_span( '', inlink( '', array( 'class' => 'button', 'text' => ' &gt; '
                                                       , 'unterkonten_id' => $succ_id, 'inactive' => ( $succ_id == 0 )
        ) ) );
    open_tr( 'smallskip' );
      open_td( '', 'Kontoklasse: ' );
      open_td( 'qquad', "{$hk['kontoklassen_cn']} {$hk['geschaeftsbereich']}" );
    open_tr( 'smallskip' );
      open_td( '', 'Hauptkonto: ' );
      open_td( 'qquad' );
        echo inlink( 'hauptkonto', array( 'hauptkonten_id' => $hauptkonten_id, 'text' => "<b>{$hk['kontenkreis']} {$hk['seite']}</b> {$hk['rubrik']} / {$hk['titel']}" ) );

    open_tr( 'smallskip' );
      open_td( '', 'Attribute: ' );
      open_td( 'qquad' );
        if( $vortragskonto_name ) {
          open_span( 'bold', $vortragskonto_name );
          qquad();
        }
        open_span( 'quads', sprintf(
          "Sonderkonto Zins: <input type='radio' name='zinskonto' value='1' %s> ja"
        , ( $zinskonto ? 'checked' : '' )
        ) );
        open_span( 'quads', sprintf(
          "<input type='radio' name='zinskonto' value='0' %s> nein"
        , ( $zinskonto ? '' : 'checked' )
        ) );
        qquad();

    open_tr();
      open_td( 'label=cn', 'Kontobezeichnung:' );
      open_td( '', string_element( 'cn', 'size=40' ) );

    open_tr();
      open_td( 'label=hgb_klasse', 'HGB-Klasse:' );
      open_td( '' );
        if( $hauptkonten_hgb_klasse ) {
          echo open_span( 'kbd', $hauptkonten_hgb_klasse );
        } else {
          selector_hgb_klasse( 'hgb_klasse', $hgb_klasse, $hk['kontenkreis'], $hk['seite'] );
        }

    if( $is_bankkonto ) {
      open_tr('medskip');
        open_td( 'label=bankkonten_bank', 'Bank:' );
        open_td( '', string_element( 'bankkonten_bank', 'size=40' ) );
      open_tr();
        open_td( 'label=bankkonten_kontonr', 'Konto-Nr:' );
        open_td( '', string_element( 'bankkonten_kontonr', 'size=40' ) );
      open_tr();
        open_td( 'label=bankkonten_blz', 'BLZ:' );
        open_td( '', string_element( 'bankkonten_blz', 'size=40' ) );
      open_tr();
        open_td( 'label=bankkonten_blz', 'url:' );
        open_td( '', string_element( 'bankkonten_url', 'size=40' ) );
    }

    if( $is_personenkonto ) {
      open_tr( 'medskip' );
        open_td( 'label=people_id', 'Person:' );
        open_td( 'oneline' );
          selector_people( 'people_id', $people_id );
          if( $people_id )
            open_span( 'qquad', inlink( 'person', array( 'class' => 'people', 'text' => '', 'people_id' => $people_id ) ) );
    }

    if( $is_sachkonto ) {
      open_tr( 'medskip' );
        open_td( 'label=things_cn', 'Gegenstand:' );
        open_td( '', string_element( 'things_cn', 'size=40' ) );
      open_tr();
        open_td( 'label=things_anschaffungsjahr', 'Anschaffungsjahr:' );
        open_td( '', int_element( 'things_anschaffungsjahr', 'size=4' ) );
      open_tr();
        open_td( 'label=things_abschreibungszeit', 'Abschreibungszeit:' );
        open_td( '', int_element( 'Abschreibungszeit:', 'size=4' ) );
    }

    open_tr( 'medskip' );
      open_td( 'label=kommentar', 'Kommentar:' );
      open_td( '', textarea_element( 'kommentar', 'rows=4,cols=60' ) );

    open_tr( 'medskip' );
      open_td( 'right,colspan=2' );
        if( $unterkonten_id ) {
          reset_button( 'text=Reset,style=display:none;' );
          if( ! $changes )
            template_button( 'text=als Vorlage benutzen' );
        }
        submission_button( 'text=Speichern' );

  close_table();

  if( $unterkonten_id ) {
    open_div( 'smallskip left' );
      echo 'Status:';
      if( $unterkonto_geschlossen ) {
        open_span( 'quads', 'Konto ist geschlossen' );
        if( $kann_oeffnen ) {
          open_span( 'quads', action_button_view( 'action=oeffnen,text=wieder oeffnen' ) );
        } else {
          open_span( 'quads small', $oeffnen_schliessen_problem );
        }
      } else {
        open_span( 'quads', 'offen' );
        if( $kann_schliessen ) {
          open_span( 'quads', action_button_view( 'action=schliessen,text=konto schliessen' ) );
        } else {
          open_span( 'quads small', $oeffnen_schliessen_problem );
        }
      }
    close_div();
  }

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
        array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Soll', 'action' => 'init' )
      , array( 'buchungen_id' => 0, 'nS' => 1, 'pS0_unterkonten_id' => $unterkonten_id, 'nH' => 1, 'geschaeftsjahr' => $geschaeftsjahr )
      ) );
      open_span( "qquad,style=float:right;", action_button_view(
        array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Haben', 'action' => 'init' )
      , array( 'buchungen_id' => 0, 'nS' => 1, 'pH0_unterkonten_id' => $unterkonten_id, 'nH' => 1, 'geschaeftsjahr' => $geschaeftsjahr )
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

  if( $unterkonten_id && $is_personenkonto ) {
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
