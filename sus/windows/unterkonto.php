<?php

define( 'OPTION_SHOW_POSTEN', 1 );
init_global_var( 'options', 'u', 'http,persistent', 0, 'window' );

init_global_var( 'unterkonten_id', 'u', 'http,persistent', 0, 'self' );
$uk = ( $unterkonten_id ? sql_one_unterkonto( $unterkonten_id ) : false );
row2global( 'unterkonten', $uk );

if( ! $uk ) {
  init_global_var( 'hauptkonten_id', 'U', 'http,persistent', NULL, 'self' );
}
$hk = sql_one_hauptkonto( $hauptkonten_id );
row2global( 'hauptkonten', $hk, array( 'kommentar' => 'hauptkonten_kommentar' ) );

$is_personenkonto = $hk['personenkonto'];
$is_bankkonto = $hk['bankkonto'];
$is_sachkonto = $hk['sachkonto'];
$is_vortragskonto = $hk['vortragskonto'];

$thing = ( $things_id ? sql_one_thing( $things_id ) : false );
row2global( 'things', $thing, 'things' );

$bankkonto = ( $bankkonten_id ? sql_one_bankkonto( $bankkonten_id ) : false );
row2global( 'bankkonten', $bankkonto, 'bankkonten' );

$problems = array();

init_global_var( 'cn', 'h', 'http,keep' );
$cn = trim( $cn );

init_global_var( 'kommentar', 'h', 'http,keep' );
init_global_var( 'zinskonto', 'u', 'http,keep' );

if( $is_personenkonto ) {
  init_global_var( 'people_id', 'u', 'http,keep' );
  if( $people_id ) {
    if( ! sql_person( $people_id, true ) )
      $people_id = 0;
  }
}

if( $is_sachkonto ) {
  init_global_var( 'things_cn', 'h', 'http,keep' );
  $things_cn = trim( $things_cn );
  init_global_var( 'things_anschaffungsjahr', 'u', 'http,keep' );
  init_global_var( 'things_abschreibungszeit', 'u', 'http,keep' );
}

if( $is_bankkonto ) {
  init_global_var( 'bankkonten_bank', 'h', 'http,keep' );
  init_global_var( 'bankkonten_kontonr', 'h', 'http,keep' );
  init_global_var( 'bankkonten_blz', 'h', 'http,keep' );
  init_global_var( 'bankkonten_url', 'h', 'http,keep' );
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
    $oeffnen_schliessen_problem = sql_unterkonto_close( $unterkonten_id, 'check' );
    if( ! $oeffnen_schliessen_problem ) {
      $kann_schliessen = true;
    }
  }
}


$actions = array( 'update', 'deleteUnterkonto', 'delete', 'template' );
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

    $values = array(
      'cn' => $cn
    , 'kommentar' => $kommentar
    , 'hauptkonten_id' => $hauptkonten_id
    , 'zinskonto' => $zinskonto
    );
    $values_things = array();
    $values_bankkonten = array();

    if( $is_personenkonto ) {
      if( ! $people_id )
        $problems[] = 'people_id';
      $values['people_id'] = $people_id;
    } else {
      $values['people_id'] = 0;
    }
    if( $is_sachkonto ) {
      if( ! $things_cn )
        $problems[] = 'things_cn';
      if( ! $things_anschaffungsjahr )
        $problems[] = 'things_anschaffungsjahr';
      $values_things['cn'] = $things_cn;
      $values_things['anschaffungsjahr'] = $things_anschaffungsjahr;
      $values_things['abschreibungszeit'] = $things_abschreibungszeit;
    } else {
      $values['things_id'] = 0;
    }

    if( $is_bankkonto ) {
      if( ! $bankkonten_bank )
        $problems[] = 'bankkonten_bank';
      if( ! preg_match( '/[0-9 ]+/', $bankkonten_kontonr ) )
        $problems[] = 'bankkonten_kontonr';
      if( ! preg_match( '/[0-9 ]+/', $bankkonten_blz ) )
        $problems[] = 'bankkonten_blz';
      $values_bankkonten['bank'] = $bankkonten_bank;
      $values_bankkonten['kontonr'] = $bankkonten_kontonr;
      $values_bankkonten['blz'] = $bankkonten_blz;
      $values_bankkonten['url'] = $bankkonten_url;
    } else {
      $values['bankkonten_id'] = 0;
    }

    if( ! $problems ) {
      if( $values_things ) {
        if( $things_id ) {
          sql_update( 'things', $things_id, $values_things );
        } else {
          $things_id = sql_insert( 'things', $values_things );
          $values['things_id'] = $things_id;
        }
      } else if( $things_id ) {
        sql_delete( 'things', $things_id );
      }
      if( $values_bankkonten ) {
        if( $bankkonten_id ) {
          sql_update( 'bankkonten', $bankkonten_id, $values_bankkonten );
        } else {
          $bankkonten_id = sql_insert( 'bankkonten', $values_bankkonten );
          $values['bankkonten_id'] = $bankkonten_id;
        }
      } else if( $bankkonten_id ) {
        sql_delete( 'bankkonten', $bankkonten_id );
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
    sql_unterkonto_close( $unterkonten_id );
    break;
  case 'oeffnen':
    need( $kann_oeffnen, $oeffnen_schliessen_problem );
    sql_update( 'unterkonten', $unterkonten_id, array( 'unterkonto_geschlossen' => 0 ) );
    for( $id = unterkonten_id, $j = $geschaeftsjahr; $j < $geschaeftsjahr_max; $j++ ) {
      $id = sql_unterkonto_folgekonto_anlegen( $id );
    }
    break;

}

open_fieldset( 'small_form', '', ( $unterkonten_id ? 'Stammdaten Unterkonto' : 'neues Unterkonto' ) . " [$unterkonten_id]" );
  open_form( 'name=update_form', 'action=update' );
    open_table('small_form');
      open_tr( 'smallskip' );
        open_td( '', '', 'Geschaeftsjahr: ' );
        open_td();
          if( $unterkonten_id ) {
            $pred = sql_one_unterkonto( array( 'folge_unterkonten_id' => $unterkonten_id ), true );
            $pred_id = adefault( $pred, 'unterkonten_id', 0 );
          } else {
            $pred_id = 0;
          }
          open_span( '', '', inlink( '', array( 'class' => 'button', 'text' => ' &lt; '
                                                         , 'unterkonten_id' => $pred_id , 'inactive' => ( $pred_id == 0 )
          ) ) );
          open_span( 'quads bold', '', $geschaeftsjahr );
          $succ_id = $uk['folge_unterkonten_id'];
          open_span( '', '', inlink( '', array( 'class' => 'button', 'text' => ' &gt; '
                                                         , 'unterkonten_id' => $succ_id, 'inactive' => ( $succ_id == 0 )
          ) ) );
      open_tr( 'smallskip' );
        open_td( '', '', 'Kontoklasse: ' );
        open_td( '', '', "{$hk['kontoklassen_cn']} {$hk['geschaeftsbereich']}" );
      open_tr( 'smallskip' );
        open_td( '', '', 'Hauptkonto: ' );
        open_td();
          echo inlink( 'hauptkonto', array( 'hauptkonten_id' => $hauptkonten_id, 'text' => "<b>{$hk['kontoart']} {$hk['seite']}</b> {$hk['rubrik']} / {$hk['titel']}" ) );

      open_tr( 'smallskip' );
        open_td( '', '', 'Attribute: ' );
        open_td();
          if( $is_vortragskonto ) {
            open_span( 'bold', '', 'Vortragskonto' );
            qquad();
          }
          open_span( 'quads', '', sprintf(
            "Sonderkonto Zins: <input type='radio' name='zinskonto' value='1' %s> ja"
          , ( $zinskonto ? 'checked' : '' )
          ) );
          open_span( 'quads', '', sprintf(
            "<input type='radio' name='zinskonto' value='0' %s> nein"
          , ( $zinskonto ? '' : 'checked' )
          ) );
          qquad();

      form_row_text( 'Kontobezeichnung:', 'cn', 40, $cn );

      if( $is_bankkonto ) {
        form_row_text( 'Bank:', 'bankkonten_bank', 40, $bankkonten_bank );
        form_row_text( 'Konto-Nr:', 'bankkonten_kontonr', 40, $bankkonten_kontonr ); 
        form_row_text( 'BLZ:', 'bankkonten_blz', 40, $bankkonten_blz ); 
        form_row_text( 'url:', 'bankkonten_url', 40, $bankkonten_url ); 
      }
      if( $is_personenkonto ) {
        open_tr( 'smallskip' );
          open_td( problem_class('people_id'), '', 'Person:' );
          open_td();
          open_select( 'people_id', '', html_options_people( $people_id ) );
          if( $people_id )
            open_span( 'qquad', '', inlink( 'person', array( 'class' => 'people', 'text' => '', 'people_id' => $people_id ) ) );
      }
      if( $is_sachkonto ) {
        form_row_text( 'Gegenstand:', 'things_cn', 40, $things_cn );
        form_row_int( 'Anschaffungsjahr:', 'things_anschaffungsjahr', 4, $things_anschaffungsjahr );
        form_row_int( 'Abschreibungszeit:', 'things_abschreibungszeit', 4, $things_anschaffungsjahr );
      }
      open_tr( 'smallskip' );
        open_td( '', '', 'Kommentar:' );
        open_td();
          echo "<textarea name='kommentar' rows='4' cols='60'>$kommentar</textarea>";
      open_tr( 'smallskip' );
        open_td( 'right', "colspan='2'", html_submission_button( 'save', 'Speichern' ) );
    close_table();

    if( $unterkonten_id ) {
      open_div( 'smallskip left' );
        echo 'Status:';
        if( $unterkonto_geschlossen ) {
          open_span( 'quads', '', 'Konto ist geschlossen' );
          if( $kann_oeffnen ) {
            open_span( 'quads', '', html_submission_button( 'oeffnen', 'wieder oeffnen' ) );
          } else {
            open_span( 'quads small', '', $oeffnen_schliessen_problem );
          }
        } else {
          open_span( 'quads', '', 'offen' );
          if( $kann_schliessen ) {
            open_span( 'quads', '', html_submission_button( 'schliessen', 'konto schliessen' ) );
          } else {
            open_span( 'quads small', '', $oeffnen_schliessen_problem );
          }
        }
      close_div();
    }

    if( $unterkonten_id && ! ( $options & OPTION_SHOW_POSTEN ) ) {
      $n = sql_count( 'posten', "unterkonten_id=$unterkonten_id" );
      if( $n > 0 ) {
        $saldo = sql_unterkonten_saldo( $unterkonten_id );
        open_div( 'solidtop smallskips center', '', inlink( 'self', array(
          'text' => "Saldo ($n Posten): ".price_view( $saldo ), 'class' => 'button'
        , 'options' => $options | OPTION_SHOW_POSTEN
        ) ) );
      } else {
        open_div( 'center', '', '(keine Posten vorhanden)' );
      }
    }
  close_form();

  if( $unterkonten_id && ! $unterkonto_geschlossen ) {
    open_div( 'smallskip' );
      open_span( 'qquad', "style='float:left;'", postaction(
        array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Soll' )
      , array( 'action' => 'init', 'buchungen_id' => 0, 'nS' => 1, 'pS0_unterkonten_id' => $unterkonten_id, 'nH' => 1
        , 'geschaeftsjahr' => $geschaeftsjahr
        )
      ) );
      open_span( 'qquad', "style='float:right;'", postaction(
        array( 'script' => 'buchung', 'class' => 'button', 'text' => 'Buchung Haben' )
      , array( 'action' => 'init', 'buchungen_id' => 0, 'nS' => 1, 'pH0_unterkonten_id' => $unterkonten_id, 'nH' => 1
        , 'geschaeftsjahr' => $geschaeftsjahr
        )
      ) );
    close_div();
  }

  if( $unterkonten_id && ( $options & OPTION_SHOW_POSTEN ) ) {
    smallskip();
    open_fieldset( 'small_form', ''
      , inlink( 'self', array( 'options' => $options & ~OPTION_SHOW_POSTEN, 'class' => 'close' ) )
        . ' Posten: '
    );
      // smallskip();
      postenlist_view( array( 'unterkonten_id' => $unterkonten_id ) );
    close_fieldset();
  }

close_fieldset();

if( $unterkonten_id && $is_personenkonto ) {
  while( ( $uk = sql_one_unterkonto( array( 'folge_unterkonten_id' => $unterkonten_id ), true ) ) ) {
    $unterkonten_id = $uk['unterkonten_id'];
  }
  open_fieldset( 'small_form', '', 'Darlehen' );
    darlehenlist_view( array( ( $zinskonto ? 'zins_unterkonten_id' : 'darlehen_unterkonten_id' ) => $unterkonten_id ), '' );
  close_fieldset();
}

?>
