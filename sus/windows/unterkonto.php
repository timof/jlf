<?php

get_http_var( 'unterkonten_id', 'u', 0, true );
$uk = ( $unterkonten_id ? sql_one_unterkonto( $unterkonten_id ) : false );
row2global( 'unterkonten', $uk );

if( ! $uk ) {
  need_http_var( 'hauptkonten_id', 'U', true );
}
$hk = sql_one_hauptkonto( $hauptkonten_id );
row2global( 'hauptkonten', $hk, array( 'kommentar' => 'hauptkonten_kommentar' ) );

$is_personenkonto = $hk['personenkonto'];
$is_bankkonto = $hk['bankkonto'];
$is_sachkonto = $hk['sachkonto'];

$thing = ( $things_id ? sql_one_thing( $things_id ) : false );
row2global( 'things', $thing, 'things' );

$bankkonto = ( $bankkonten_id ? sql_one_bankkonto( $bankkonten_id ) : false );
row2global( 'bankkonten', $bankkonto, 'bankkonten' );

$problems = array();

get_http_var( 'cn', 'h', $cn );
$cn = trim( $cn );

get_http_var( 'kommentar', 'h', $kommentar );

if( $is_personenkonto ) {
  get_http_var( 'people_id', 'u', $people_id );
  if( $people_id ) {
    if( ! sql_person( $people_id, true ) )
      $people_id = 0;
  }
}

if( $is_sachkonto ) {
  get_http_var( 'things_cn', 'h', $things_cn );
  $things_cn = trim( $things_cn );
  get_http_var( 'things_anschaffungsjahr', 'u', $things_anschaffungsjahr );
  get_http_var( 'things_abschreibungszeit', 'u', $things_abschreibungszeit );
}

if( $is_bankkonto ) {
  get_http_var( 'bankkonten_bank', 'h', $bankkonten_bank );
  get_http_var( 'bankkonten_kontonr', 'h', $bankkonten_kontonr );
  get_http_var( 'bankkonten_blz', 'h', $bankkonten_blz );
  get_http_var( 'bankkonten_url', 'h', $bankkonten_url );
}


define( 'OPTION_SHOW_POSTEN', 1 );
get_http_var( 'options', 'u', 0, true );
get_http_var( 'orderby', 'l', '', true ); // make it available for all forms


handle_action( array( 'save', 'update', 'init' ) );
switch( $action ) {
  case 'save':
    if( ! $cn )
      $problems[] = 'cn';

    $values = array(
      'cn' => $cn
    , 'kommentar' => $kommentar
    , 'hauptkonten_id' => $hauptkonten_id
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
        if( $bankonten_id ) {
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
        $self_fields['unterkonten_id'] = $unterkonten_id;
      }
    }
  break;
}

open_fieldset( 'small_form', '', ( $unterkonten_id ? 'Stammdaten Unterkonto' : 'neues Unterkonto' ) . " [$unterkonten_id]" );
  open_form( 'name=update_form', 'action=update' );
    hidden_input( 'hauptkonten_id', $hauptkonten_id );
    open_table('small_form hfill');
      open_tr();
        open_td( '', '', 'Kontoklasse: ' );
        open_td( '', '', "{$hk['kontoklassen_cn']} {$hk['geschaeftsbereich']}" );
      open_tr();
        open_td( '', '', 'Hauptkonto: ' );
        open_td();
          echo inlink( 'hauptkonto', array( 'hauptkonten_id' => $hauptkonten_id, 'text' => "<b>{$hk['kontoart']} {$hk['seite']}</b> {$hk['rubrik']} / {$hk['titel']}" ) );

      form_row_text( 'Kontobezeichnung:', 'cn', 40, $cn );

      if( $is_bankkonto ) {
        form_row_text( 'Bank:', 'bankkonten_bank', 40, $bankkonten_bank );
        form_row_text( 'Konto-Nr:', 'bankkonten_kontonr', 40, $bankkonten_kontonr ); 
        form_row_text( 'BLZ:', 'bankkonten_blz', 40, $bankkonten_blz ); 
        form_row_text( 'url:', 'bankkonten_url', 40, $bankkonten_url ); 
      }
      if( $is_personenkonto ) {
        open_tr();
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
      open_tr();
        open_td( '', '', 'Kommentar:' );
        open_td();
          echo "<textarea name='kommentar' rows='4' cols='60'>$kommentar</textarea>";
      open_tr( 'smallskips' );
        open_td( 'right bottom', "colspan='2'", html_submission_button( 'save', 'Speichern' ) );

      if( $unterkonten_id && ! ( $options & OPTION_SHOW_POSTEN ) ) {
        $n = sql_count( 'posten', "unterkonten_id=$unterkonten_id" );
        if( $n > 0 ) {
          $saldo = sql_unterkonten_saldo( $unterkonten_id );
          open_tr( 'solidtop smallskips' );
            open_td( 'right' );
              echo inlink( 'self', array(
                'text' => "Saldo ($n Posten):", 'class' => 'href', 'options' => $options | OPTION_SHOW_POSTEN
              ) );
            open_td( 'left qquad' );
              echo inlink( 'self', array(
                'text' => price_view( $saldo ), 'class' => 'href', 'options' => $options | OPTION_SHOW_POSTEN
              ) );
        } else {
          open_tr( 'smallskips' );
            open_td( 'center', "colspan='2'" );
              echo '(keine Posten vorhanden)';
        }
      }
    close_table();
  close_form();

  if( $unterkonten_id && ( $options & OPTION_SHOW_POSTEN ) ) {
    open_fieldset( 'small_form', ''
      , inlink( 'self', array( 'options' => $options & ~OPTION_SHOW_POSTEN, 'class' => 'close' ) )
        . ' Posten: '
    );
    open_span( 'qquad', 'style:float:right;', postaction(
      array( 'window' => 'buchung', 'class' => 'button', 'text' => 'Buchung Soll' )
    , array( 'action' => 'init', 'buchungen_id' => 0, 'nS' => 1, 'pS0_unterkonten_id' => $unterkonten_id, 'nH' => 1 )
    ) );
    open_span( 'qquad', 'style:float:right;', postaction(
      array( 'window' => 'buchung', 'class' => 'button', 'text' => 'Buchung Haben' )
    , array( 'action' => 'init', 'buchungen_id' => 0, 'nS' => 1, 'pH0_unterkonten_id' => $unterkonten_id, 'nH' => 1 )
    ) );
    medskip();
    postenlist_view( array( 'unterkonten_id' => $unterkonten_id ), '' );
  }

close_fieldset();

?>
