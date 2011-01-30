<?php

// main menu
//

$mainmenu = array();


// $mainmenu[] = array( 'script' => "bank",
//     "title" => "bank",
//     "text" => "bank" );

$mainmenu[] = array( 'script' => "bestandskonten",
     "title" => "Bilanz",
     "text" => "Bestandskonten" );

$mainmenu[] = array( 'script' => "erfolgskonten",
     "title" => "GV-Rechnung",
     "text" => "Erfolgskonten" );

$mainmenu[] = array( 'script' => "unterkonten",
     "title" => "Unterkonten",
     "text" => "Unterkonten" );

$mainmenu[] = array( 'script' => "journal",
     "title" => "Journal",
     "text" => "Journal" );

$mainmenu[] = array( 'script' => "geschaeftsjahre",
     "title" => "Gesch&auml;ftsjahre",
     "text" => "Gesch&auml;ftsjahre" );

$mainmenu[] = array( 'script' => "personen",
     "title" => "Personen",
     "text" => "Personen" );

$mainmenu[] = array( 'script' => "things",
     "title" => "Gegenst&auml;nde",
     "text" => "Gegenst&auml;nde" );


function mainmenu_fullscreen() {
  global $mainmenu;
  foreach( $mainmenu as $h ) { 
    open_tr();
      open_td( '', "colspan='2'", inlink( $h['script'], array(
        'text' => $h['text'], 'title' => $h['title'] , 'class' => 'bigbutton'
      ) ) );
  }
}

function mainmenu_header() {
  global $mainmenu;
  foreach( $mainmenu as $h ) { 
    open_li( '', '', inlink( $h['script'], array(
      'text' => $h['text'], 'title' => $h['title'] , 'class' => 'href'
    ) ) );
  }
}


function window_title() {
  global $geschaeftsjahr, $geschaeftsjahr_thread, $geschaeftsjahr_current;
  get_http_var( 'geschaeftsjahr_thread', 'u', $geschaeftsjahr_current, 'thread' );
  if( $geschaeftsjahr_thread ) {
    get_http_var( 'geschaeftsjahr', 'u' );
    if( isset( $geschaeftsjahr ) && ( $geschaeftsjahr != $geschaeftsjahr_thread ) )
      return "Gesch&auml;ftsjahr: <span class='alert quads'>$geschaeftsjahr</span> ($geschaeftsjahr_thread)";
    else
      return "Gesch&auml;ftsjahr: $geschaeftsjahr_thread";
  } else {
    return "(kein Gesch&auml;ftsjahr gewaehlt)";
  }
}


// people:
//

$jlf_url_vars[ 'people_N_ordernew' ] = 'l';
$jlf_url_vars[ 'people_N_limit_from' ] = 'u';
$jlf_url_vars[ 'people_N_limit_count' ] = 'u';
function people_view( $filters = array(), $p_ = true, $select = '' ) {
  global $script, $login_people_id;
  static $num = 0;

  if( $p_ === true ) {
    $num++;
    $p_ = "people_N{$num}_";
  }
  $orderby_sql = handle_orderby( array(
    'cn' => 'cn', 'gn' => 'gn', 'sn' => 'sn', 'phone' => 'telephonenumber', 'mail' => 'mail', 'jperson' => 'jperson', 'uid' => 'uid'
  ), $p_ );

  get_http_var( $p_.'limit_from', 'u', 0, 'self' );
  get_http_var( $p_.'limit_count', 'u', 20, 'window' );
  $limit_from = $GLOBALS[ $p_.'limit_from' ];
  $limit_count = $GLOBALS[ $p_.'limit_count' ];

  $people = sql_people( $filters, $orderby_sql );
  $count = count( $people );
  if( ! $people ) {
    open_div( '', '', 'Keine Personen vorhanden' );
    return;
  }

  if( $count <= $limit_from )
    $limit_from = $count - 1;

  if( $select ) {
    $selected_people_id = ( isset( $GLOBALS[$select] ) ? $GLOBALS[$select] : 0 );
  } else {
    $selected_people_id = 0;
  }
  open_table('list hfill oddeven');
    if( $count > 10 ) {
      open_caption();
        form_limits( $p_, $count, $limit_from, $limit_count );
      close_caption();
    } else {
      $limit_count = 10;
      $limit_from = 0;
    }
    open_th( '','', 'juristisch', 'jperson', $p_ );
    open_th( '','', 'cn', 'cn', $p_ );
    open_th( '','', 'Vorname', 'gn', $p_ );
    open_th( '','', 'Nachname', 'sn', $p_ );
    open_th( '','', 'Telefon', 'phone', $p_ );
    open_th( '','', 'Email', 'mail', $p_ );
    open_th( '','', 'user-ID', 'uid', $p_ );
    open_th( '','', 'Konten' );
    open_th( '','', 'Aktionen' );

    foreach( $people as $person ) {
      if( $person['nr'] <= $limit_from )
        continue;
      if( $person['nr'] > $limit_from + $limit_count )
        break;
      $people_id = $person['people_id'];
      if( $select ) {
        open_tr(
          $people_id == $selected_people_id ? 'selected' : 'unselected'
        , "onclick=\"".inlink( '', array( 'context' => 'js', $select => $people_id ) ) ."\";"
        );
      } else {
        open_tr();
      }
        open_td( 'left', '', $person['jperson'] );
        open_td( 'left', '', $person['cn'] );
        open_td( 'left', '', $person['gn'] );
        open_td( 'left', '', $person['sn'] );
        open_td( 'left', '', $person['telephonenumber'] );
        open_td( 'left', '', $person['mail'] );
        open_td( 'left', '', $person['uid'] );
        open_td( 'left' );
          $unterkonten = sql_unterkonten( array( 'people_id' => $people_id ), 'geschaeftsjahr DESC,zinskonto' );
          if( ! $unterkonten ) {
            echo '-';
          } else {
            $j = $unterkonten[0]['geschaeftsjahr'];
            foreach( $unterkonten as $uk ) {
              if( $uk['geschaeftsjahr'] != $j ) {
                open_div( '', '', inlink( 'unterkonten', array(
                  'people_id' => $people_id, 'class' => 'browse', 'text' => 'alle Konten...'
                ) ) );
                break;
              }
              open_div( '', '', inlink( 'unterkonto', array( 'unterkonten_id' => $uk['unterkonten_id'] , 'text' => $uk['cn'] ) ) );
            }
          }
        open_td();
          echo inlink( 'person', "class=edit,text=,people_id=$people_id" );
          if( ( $script == 'personen' ) && ( $people_id != $login_people_id ) ) {
            echo postaction( 'update,class=drop,confirm=Person loeschen?', "action=delete,message=$people_id" );
          }
    }
  close_table();
}

function person_view( $people_id ) {
  $person = sql_people( $people_id );
  open_table( 'list oddeven' );
    if( $person['jperson'] ) {
      open_tr();
        open_td( '', '', 'Firma:' );
        open_td( '', '', $person['cn'] );
      open_tr();
        open_td( '', '', 'Ansprechpartner:' );
        open_td( '', '', "{$person['title']} {$person['vorname']} {$person['nachname']}" );
    } else {
      open_tr();
        open_td( '', '', 'Anrede:' );
        open_td( '', '', $person['title'] );
      open_tr();
        open_td( '', '', 'Vorname:' );
        open_td( '', '', $person['vorname'] );
      open_tr();
        open_td( '', '', 'Nachname:' );
        open_td( '', '', $person['nachname'] );
    }
    open_tr();
      open_td( '', '', 'Email:' );
      open_td( '', '', $person['email'] );
    open_tr();
      open_td( '', '', 'Telefon:' );
      open_td( '', '', $person['telephonenumber'] );
  close_table();
}

// things:
//

function thingslist_view( $filters, $orderby_prefix = false ) {
  if( $orderby_prefix === false ) {
    $orderby_sql = 'cn';
    $p_ = false;
  } else {
    $p_ = ( $orderby_prefix ? $orderby_prefix.'_' : '' );
    $orderby_sql = handle_orderby( array( 'cn' => 'cn', 'aj' => 'anschaffungsjahr', 'wert' ), $orderby_prefix );
  }

  $things = sql_things( $filters, $orderby_sql );
  open_table( 'list oddeven' );
    open_tr();
      open_th( '', '', 'Name', 'cn', $p_ );
      open_th( '', '', 'Anschaffungsjahr', 'aj', $p_ );
      open_th( '', '', 'Restwert', 'wert', $p_ );
      open_th( '', '', 'Aktionen' );
    foreach( $things as $th ) {
      open_tr();
        open_td( 'left', '', $th['cn'] );
        open_td( 'center', '', $th['anschaffungsjahr'] );
        open_td( 'number', '', price_view( $th['wert'] ) );
        open_td();
          echo inlink( 'thing', "things_id={$th['things_id']},class=record" );
    }
  close_table();
}

function thing_view( $things_id, $stichtag = false ) {
  $filters = array( 'things_id' => $things_id );
  if( $stichtag )
    $filters['posten.valuta'] = " <= $stichtag";
  $thing = sql_things( $filters );
  open_table( 'list' );
    open_tr();
      open_td( '', '', 'Name:' );
      open_td( '', '', $thing['cn'] );
    open_tr();
      open_td( '', '', 'Anschaffungsjahr:' );
      open_td( '', '', $thing['anschaffungsjahr'] );
    open_tr();
      open_td( '', '', 'Abschreibungszeit:' );
      open_td( '', '', $thing['abschreibungszeit'] );
    open_tr();
      open_td( '', '', 'Restwert:' );
      open_td( '', '', price_view( $thing['wert'] ) );
    open_tr();
      open_td( '', '', inlink( 'thing', "class=edit,text=edieren,things_id=$things_id" ) );
      open_td( '', '', inlink( 'unterkonto', "class=browse,text=Wertentwicklung,things_id=$things_id" ) );
  close_table();
}

// bankkonten
//

// function bankkontenlist_view( $filters, $orderby = 'cn' ) {
//   $bankkonten = sql_bankkonten( $filters, $orderby );
//   open_table( 'list' );
//     open_tr();
//       open_th( '', '', inlink( '', 'order_nwe=cn,text=Name' ) );
//       open_th( '', '', inlink( '', 'order_new=kontonr,text=kontonr' ) );
//       open_th( '', '', inlink( '', 'order_new=blz,text=blz' ) );
//       open_th( '', '', 'Saldo' );
//       open_th( '', '', 'Aktionen' );
//     foreach( $bankkonten as $bk ) {
//       open_tr();
//         open_td( 'left', '', $bk['cn'] );
//         open_td( 'left', '', $bk['kontonr'] );
//         open_td( 'left', '', $bk['blz'] );
//         open_td( 'number', '', $bk['saldo'] );
//         open_td();
//           echo inlink( 'bankkonto', "bankkonten_id={$bk['bankkonten_id']},class=record" );
//     }
//   close_table();
// }
// 
// function bankkonto_view( $bankkonto_id ) {
//   $filters = array( 'bankkonten_id' => $bankkonten_id );
//   $bk = sql_one_bankkonto( $filters );
//   open_table( 'list' );
//     open_tr();
//       open_td( '', '', 'Name:' );
//       open_td( '', '', $bk['cn'] );
//     open_tr();
//       open_td( '', '', 'Konto-Nr:' );
//       open_td( '', '', $bk['kontonr'] );
//     open_tr();
//       open_td( '', '', 'BLZ:' );
//       open_td( '', '', $bk['blz'] );
//     open_tr();
//       open_td( '', '', 'Saldo:' );
//       open_td( '', '', $bk['saldo'] );
//     open_tr();
//       open_td( '', '', inlink( 'bankkonto', "class=edit,text=edieren,bankkonten_id=$bankkonten_id" ) );
//       open_td( '', '', inlink( 'posten', "class=browse,text=Auszug,bankkonten_id=$bankkonten_id" ) );
//   close_table();
// }

// unterkonten
//
$jlf_url_vars[ 'unterkontenlist_N_ordernew' ] = 'l';
$jlf_url_vars[ 'unterkontenlist_N_limit_from' ] = 'u';
$jlf_url_vars[ 'unterkontenlist_N_limit_count' ] = 'u';
function unterkontenlist_view( $filters, $p_ = true, $select = '' ) {
  static $num = 0;

  if( $p_ === true ) {
    $num++;
    $p_ = "unterkontenlist_N{$num}_";
  }
  $orderby_sql = handle_orderby( array(
      'geschaeftsjahr' => 'geschaeftsjahr'
    , 'kontoart' => 'kontoart', 'seite' => 'seite', 'rubrik' => 'rubrik', 'titel' => 'titel' , 'cn' => 'cn'
    , 'gb' => 'geschaeftsbereich', 'klasse' => 'kontoklassen.kontoklassen_id'
    , 'id' => 'unterkonten_id', 'saldo'
    , 'attribute' => 'CONCAT( personenkonto, sachkonto, bankkonto, zinskonto )'
    )
  , $p_
  );
  get_http_var( $p_.'limit_from', 'u', 0, 'self' );
  get_http_var( $p_.'limit_count', 'u', 20, 'window' );
  $limit_from = $GLOBALS[ $p_.'limit_from' ];
  $limit_count = $GLOBALS[ $p_.'limit_count' ];

  $unterkonten = sql_unterkonten( $filters, $orderby_sql );
  $count = count( $unterkonten );
  if( ! $unterkonten ) {
    open_div( '', '', 'Keine Konten gefunden' );
    return;
  }
  if( $select ) {
    $selected_unterkonten_id = ( isset( $GLOBALS[$select] ) ? $GLOBALS[$select] : 0 );
  } else {
    $selected_unterkonten_id = 0;
  }

  if( $count <= $limit_from )
    $limit_from = $count - 1;

  open_table( 'list hfill oddeven' );
    if( $count > 10 ) {
      open_caption();
        form_limits( $p_, $count, $limit_from, $limit_count );
      close_caption();
    } else {
      $limit_count = 10;
      $limit_from = 0;
    }
    open_tr();
      open_th( '', '', 'Nr', 'id', $p_ );
      open_th( '', '', 'Jahr', 'geschaeftsjahr', $p_ );
      open_th( '', '', 'Art', 'kontoart', $p_ );
      open_th( '', '', 'Seite', 'seite', $p_ );
      open_th( '', '', 'Klasse', 'klasse', $p_ );
      open_th( '', '', 'Gesch&auml;ftsbereich', 'gb', $p_ );
      open_th( '', '', 'Rubrik', 'rubrik', $p_ );
      open_th( '', '', 'Hauptkonto', 'titel', $p_ );
      open_th( '', '', 'Unterkonto', 'cn', $p_ );
      open_th( '', '', 'Attribute', 'attribute', $p_ );
      open_th( '', '', 'Saldo', 'saldo', $p_ );
      open_th( '', '', 'Aktionen' );
    $attr = '';
    foreach( $unterkonten as $uk ) {
      $unterkonten_id = $uk['unterkonten_id'];
      if( $uk['nr'] <= $limit_from )
        continue;
      if( $uk['nr'] > $limit_from + $limit_count )
        break;
      if( $select ) {
        open_tr(
          $unterkonten_id == $selected_unterkonten_id ? 'selected' : 'unselected'
        , "onclick=\"".inlink( '', array( 'context' => 'js', $select => $unterkonten_id ) ) ."\";"
        );
      } else {
        open_tr();
      }
        open_td( 'right', '', $unterkonten_id );
        open_td( 'right', '', $uk['geschaeftsjahr'] );
        open_td( 'center' );
          switch( $uk['kontoart'] ) {
            case 'E':
              echo inlink( 'erfolgskonten', 'text=E,class=href' );
              break;
            case 'B':
              echo inlink( 'bestandskonten', 'text=B,class=href' );
              break;
          }
        close_td();
        open_td( 'center', '', $uk['seite'] );
        open_td( 'left', '', inlink( 'unterkonten', array(
               'class' => 'href', 'text' => $uk['kontoklassen_cn']
             , 'kontoklassen_id' => $uk['kontoklassen_id'] ) )
        );
        open_td( 'left' );
          if( $uk['kontoart'] == 'E' ) {
            echo inlink( 'unterkonten', array(
              'class' => 'href', 'text' => $uk['geschaeftsbereich'] , 'kontoart' => 'E'
            , 'geschaeftsbereiche_id' => sql_unique_id( 'kontoklassen', 'geschaeftsbereich', $uk['geschaeftsbereich'] )
            ) );
          } else {
            echo 'n/a';
          }
        close_td(); 
        open_td( 'left', '', $uk['rubrik'] );
        open_td( 'left', '', inlink( 'hauptkonto', array(
               'class' => 'href', 'text' => $uk['titel'] , 'hauptkonten_id' => $uk['hauptkonten_id'] ) )
        );
        open_td( 'left' );
          echo inlink( 'unterkonto', array( 'unterkonten_id' => $unterkonten_id, 'class' => 'href'
                                          , 'text' => $uk['cn'] ) );
        open_td( 'left' );
          if( $uk['personenkonto'] )
            echo inlink( 'person', array( 'people_id' => $uk['people_id'] ) );
          if( $uk['sachkonto'] )
            echo inlink( 'thing', array( 'things_id' => $uk['things_id'] ) );
          if( $uk['bankkonto'] )
            echo 'Bank';
          if( $uk['zinskonto'] )
            echo ' Zins';
        open_td( 'number', '', saldo_view( $uk['seite'], $uk['saldo'] ) );
        open_td();
          if( $uk['unterkonto_geschlossen'] ) {
            echo "geschlossen";
          } else {
            if( ! sql_unterkonto_delete( $unterkonten_id, 'check' ) ) {
              echo postaction(
                array( 'class' => 'drop', 'confirm' => 'wirklich loeschen?', 'update' => 1 )
              , array( 'action' => 'unterkontoDelete', 'message' => $unterkonten_id )
              );
            }
          }
    }
  close_table();
}

// posten
//
$jlf_url_vars[ 'postenlist_N_ordernew' ] = 'l';
$jlf_url_vars[ 'postenlist_N_limit_from' ] = 'u';
$jlf_url_vars[ 'postenlist_N_limit_count' ] = 'u';
function postenlist_view( $filters, $p_ = true ) {
  global $script;
  static $num = 0;

  if( $p_ === true ) {
    $num++;
    $p_ = "postenlist_N{$num}_";
  }
  $ordertags = array(
      'valuta' => 'CONCAT( geschaeftsjahr, valuta )'
    , 'buchung' => 'buchungsdatum', 'hauptkonto' => 'titel', 'unterkonto' => 'cn'
    , 'kommentar', 'beleg', 'soll' => 'art DESC', 'haben' => 'art'
  );
  $orderby_sql = handle_orderby( $ordertags, $p_ );

  get_http_var( $p_.'limit_from', 'u', 0, 'self' );
  get_http_var( $p_.'limit_count', 'u', 20, 'window' );
  $limit_from = $GLOBALS[ $p_.'limit_from' ];
  $limit_count = $GLOBALS[ $p_.'limit_count' ];

  $posten = sql_posten( $filters, $orderby_sql );
  $count = count( $posten );
  if( ! $posten ) {
    open_div( '', '', 'Keine Posten vorhanden' );
    return;
  }

  $pattern = '/^ *CONCAT\( geschaeftsjahr, valuta \)[-,]/';
  $saldieren = preg_match( $pattern, $orderby_sql );

  if( $count <= $limit_from )
    $limit_from = $count - 1;

  $saldoS = 0.0;
  $saldoH = 0.0;
  open_table( 'list hfill oddeven' );
    if( $count > 10 ) {
      open_caption();
        form_limits( $p_, $count, $limit_from, $limit_count );
      close_caption();
    } else {
      $limit_count = 10;
      $limit_from = 0;
    }
    open_tr();
      open_th( '', '', 'Geschaeftsjahr' );
      open_th( '', '', 'Valuta', 'valuta', $p_ );
      open_th( '', '', 'Buchung', 'buchung', $p_ );
      switch( $script ) {
        case 'unterkonto':
        case 'hauptkonto':
          open_th( '', '', 'Text', 'kommentar', $p_ );
          open_th( '', '', 'Beleg', 'beleg', $p_ );
          break;
        default:
          open_th( '', '', 'Hauptkonto', 'hauptkonto', $p_ );
          open_th( '', '', 'Unterkonto', 'unterkonto', $p_ );
      }
      open_th( '', '', 'Soll', 'soll', $p_ );
      open_th( '', '', 'Haben', 'haben', $p_ );
      open_th( '', '', 'Aktionen' );
    foreach( $posten as $p ) {
      if( $p['nr'] > $limit_from + $limit_count )
        break;
      $is_vortrag = ( $p['valuta'] == 100 );
      if( $is_vortrag ) {
        $saldoS = 0.0;
        $saldoH = 0.0;
      }
      switch( $p['art'] ) {
        case 'S':
          $saldoS += $p['betrag'];
          break;
        case 'H':
          $saldoH += $p['betrag'];
          break;
      }
      if( $saldieren && ( $p['nr'] == $limit_from ) ) {
        open_tr( 'summe' );
          open_td( '', "colspan='5'", 'Anfangssaldo:' );
          if( $saldoS > $saldoH ) {
            open_td( 'number', '', price_view( $saldoS - $saldoH ) );
            open_td( '', '', ' ' );
          } else {
            open_td( '', '', ' ' );
            open_td( 'number', '', price_view( $saldoH - $saldoS ) );
          }
          open_td( '', '', ' ' );
      }
      if( $p['nr'] > $limit_from && $p['nr'] <= $limit_from + $limit_count ) {
        open_tr( $is_vortrag ? 'solidtop ' : '' );
          open_td( 'right', '', $p['geschaeftsjahr'] );
          open_td( 'right', '', ( $is_vortrag ? 'Vortrag' : monthday_view( $p['valuta'] ) ) );
          open_td( 'right', '', $p['buchungsdatum'] );
          switch( $script ) {
            case 'unterkonto':
            case 'hauptkonto':
              open_td( 'left', '', $p['kommentar'] );
              open_td( 'left', '', $p['beleg'] );
              break;
            default:
              open_td( 'left', '', inlink( 'hauptkonto', array(
                'class' => 'href', 'hauptkonten_id' => $p['hauptkonten_id']
              , 'text' => "{$p['kontoart']} {$p['seite']} {$p['titel']}"
              ) ) );
              open_td( 'left', '', inlink( 'unterkonto', array( 'class' => 'href', 'unterkonten_id' => $p['unterkonten_id'] ) ) );
          }
          if( $saldoH > $saldoS )
            $title = sprintf( "title='Zwischensaldo: %.02lf H'", $saldoH - $saldoS );
          else
            $title = sprintf( "title='Zwischensaldo: %.02lf S'", $saldoS - $saldoH );
          switch( $p['art'] ) {
            case 'S':
              open_td( 'number', $title, price_view( $p['betrag'] ) );
              open_td( '', '', ' ' );
              break;
            case 'H':
              open_td( '', '', ' ' );
              open_td( 'number', $title, price_view( $p['betrag'] ) );
              break;
          }
          open_td( 'left' );
            echo inlink( 'buchung', array( 'class' => 'record', 'buchungen_id' => $p['buchungen_id'] ) );
      }
    }
    if( $saldieren ) {
      open_tr( 'summe' );
        open_td( '', "colspan='5'", 'Saldo:' );
        if( $saldoS > $saldoH ) {
          open_td( 'number', '', price_view( $saldoS - $saldoH ) );
          open_td( '', '', ' ' );
        } else {
          open_td( '', '', ' ' );
          open_td( 'number', '', price_view( $saldoH - $saldoS ) );
        }
        open_td( '', '', ' ' );
    }
  close_table();
}

// buchungen
//
// function buchung_view( $buchungen_id ) {
//   open_div();
//     open_div( 'bold', '', "Buchung $buchungen_id:" );
//     postenlist_view( "buchungen_id=$buchungen_id,art=S", 'seite,kontoklassen_id' );
//     open_div( 'bold', '', "an" );
//     postenlist_view( "buchungen_id=$buchungen_id,art=H", 'seite,kontoklassen_id' );
//   close_div();
// }

$jlf_url_vars[ 'buchungenlist_N_ordernew' ] = 'l';
$jlf_url_vars[ 'buchungenlist_N_limit_from' ] = 'u';
$jlf_url_vars[ 'buchungenlist_N_limit_count' ] = 'u';
function buchungenlist_view( $filters = array(), $p_ = true ) {
  static $num = 0;

  if( $p_ === true ) {
    $num++;
    $p_ = "buchungenlist_N{$num}_";
  }
  $orderby_sql = handle_orderby( array( 'valuta' => 'CONCAT( geschaeftsjahr, valuta )', 'bd' => 'buchungsdatum' ), $p_ );

  get_http_var( $p_.'limit_from', 'u', 0, 'self' );
  get_http_var( $p_.'limit_count', 'u', 20, 'window' );
  $limit_from = $GLOBALS[ $p_.'limit_from' ];
  $limit_count = $GLOBALS[ $p_.'limit_count' ];

  $buchungen = sql_buchungen( $filters, $orderby_sql );
  $count = count( $buchungen );
  if( ! $buchungen ) {
    open_div( '', '', 'Keine Buchungen vorhanden' );
    return;
  }

  if( $count <= $limit_from )
    $limit_from = $count - 1;

  open_table( 'list hfill oddeven' );
    if( $count > 10 ) {
      open_caption();
        form_limits( $p_, $count, $limit_from, $limit_count );
      close_caption();
    } else {
      $limit_count = 10;
      $limit_from = 0;
    }
    open_tr( 'solidbottom solidtop' );
      open_th( 'center solidright solidleft', '', 'Buchung', 'bd', $p_ );
      open_th( 'center solidright solidleft', '', 'Geschaeftsjahr' );
      open_th( 'center solidright solidleft', '', 'Valuta', 'valuta', $p_ );
      open_th( 'center solidright', "colspan='3'", 'Soll' );
      open_th( 'center solidright', "colspan='3'", 'Haben' );
      open_th( 'center solidright', '', 'Aktionen' );
    foreach( $buchungen as $b ) {
      if( $b['nr'] <= $limit_from )
        continue;
      if( $b['nr'] > $limit_from + $limit_count )
        break;
      $id = $b['buchungen_id'];
      $pS = sql_posten( array( 'buchungen_id' => $id, 'art' => 'S' ) );
      $pH = sql_posten( array( 'buchungen_id' => $id, 'art' => 'H' ) );
      $nS = count( $pS );
      $nH = count( $pH );
      $nMax = ( $nS > $nH ? $nS : $nH );
      $geschaeftsjahr = $pS[0]['geschaeftsjahr'];
      for( $i = 0; $i < $nMax; $i++ ) {
        open_tr( $i == $nMax-1 ? 'solidbottom' : '' );
        if( $i == 0 ) {
          open_td( 'center top solidright solidbottom', "rowspan='$nMax'", inlink( 'buchungen', array(
            'class' => 'href', 'text' => $b['buchungsdatum'], 'buchungsdatum' => $b['buchungsdatum']
          ) ) );
          open_td( 'center top solidright solidbottom', "rowspan='$nMax'", inlink( 'buchungen', array(
            'class' => 'href', 'text' => $geschaeftsjahr, 'geschaeftsjahr' => $geschaeftsjahr
          ) ) );
          open_td( 'center top solidright solidbottom', "rowspan='$nMax'", inlink( 'buchungen', array(
            'class' => 'href', 'valuta' => $b['valuta'], 'text' => ( $b['valuta'] == 100 ? 'Vortrag' : monthday_view( $b['valuta'] ) )
          ) ) );
        }
        if( $i < $nS ) {
          $p = & $pS[$i];
          open_td( 'left' );
            echo inlink( 'hauptkonto', array(
              'hauptkonten_id' => $p['hauptkonten_id'], 'text' => "<b>{$p['kontoart']} {$p['seite']}</b> {$p['titel']}"
            ) );
          open_td( 'left', '', inlink( 'unterkonto', array( 'text' => $p['cn'], 'unterkonten_id' => $p['unterkonten_id'] ) ) );
          open_td( 'number solidright', '', price_view( $p['betrag'] )) ;
        } else if( $i == $nS ) {
          open_td( '', "rowspan='".($nMax - $nS)."' colspan='3'", ' ' );
        }
        if( $i < $nH ) {
          $p = & $pH[$i];
          open_td( 'left' );
            echo inlink( 'hauptkonto', array(
              'hauptkonten_id' => $p['hauptkonten_id'], 'text' => "<b>{$p['kontoart']} {$p['seite']}</b> {$p['titel']}"
            ) );
          open_td( 'left', '', inlink( 'unterkonto', array( 'text' => $p['cn'], 'unterkonten_id' => $p['unterkonten_id'] ) ) );
          open_td( 'number solidright', '', price_view( $p['betrag'] ) );
        } else if( $i == $nH ) {
          open_td( '', "rowspan='".($nMax - $nH)."' colspan='3'", ' ' );
        }
        if( $i == 0 ) {
          open_td( 'top solidright solidbottom', "rowspan='$nMax'" );
            echo inlink( 'buchung', array( 'class' => 'record', 'buchungen_id' => $id ) );
            echo postaction(
              array( 'class' => 'drop', 'confirm' => 'wirklich loeschen?', 'update' => 1 )
            , array( 'action' => 'buchungDelete', 'message' => $id )
            );
          close_td();
        }
      }
    }
  close_table();
}

$jlf_url_vars[ 'geschaeftsjahrelist_N_ordernew' ] = 'l';
$jlf_url_vars[ 'geschaeftsjahrelist_N_limit_from' ] = 'u';
$jlf_url_vars[ 'geschaeftsjahrelist_N_limit_count' ] = 'u';
function geschaeftsjahrelist_view( $filters = array(), $p_ = true ) {
  global $geschaeftsjahr_abgeschlossen;
  static $num = 0;

  if( $p_ === true ) {
    $num++;
    $p_ = "geschaeftsjahrelist_N{$num}_";
  }
  // $orderby_sql = handle_orderby( array( 'geschaeftsjahr' => 'geschaeftsjahr', 'je' => 'jahresergebnis' ) );
  $orderby_sql = 'geschaeftsjahr';

  get_http_var( $p_.'limit_from', 'u', 0, 'self' );
  get_http_var( $p_.'limit_count', 'u', 20, 'window' );
  $limit_from = $GLOBALS[ $p_.'limit_from' ];
  $limit_count = $GLOBALS[ $p_.'limit_count' ];

  $geschaeftsjahre = sql_geschaeftsjahre( $filters, $orderby_sql );
  $count = count( $geschaeftsjahre );
  if( ! $geschaeftsjahre ) {
    open_div( '', '', 'Keine Geschaeftsjahre vorhanden' );
    return;
  }

  if( $count <= $limit_from )
    $limit_from = $count - 1;

  open_table( 'list hfill' );
    if( $count > 10 ) {
      open_caption();
        form_limits( $p_, $count, $limit_from, $limit_count );
      close_caption();
    } else {
      $limit_count = 10;
      $limit_from = 0;
    }
    open_tr( 'solidbottom solidtop' );
      open_th( 'center solidright solidleft', '', 'Jahr' );
      open_th( 'center solidright', '', 'Hauptkonten' );
      open_th( 'center solidright', '', 'Unterkonten' );
      open_th( 'center solidright', '', 'Buchungen' );
      open_th( 'center solidright', '', 'Posten' );
      open_th( 'center solidright', '', 'Jahresergebnis' );
      open_th( 'center solidright', '', 'Bilanzsumme' );
      open_th( 'center solidright', '', 'Status' );
      // open_th( 'center solidright', '', 'Aktionen' );
    foreach( $geschaeftsjahre as $g ) {
      if( $g['nr'] <= $limit_from )
        continue;
      if( $g['nr'] > $limit_from + $limit_count )
        break;
      $j = $g['geschaeftsjahr'];
      open_tr();
        open_td( 'top', '', $j );
        open_td( 'top', '', $g['hauptkonten_count'] );
        open_td( 'top', '', inlink( 'unterkonten', array(
          'geschaeftsjahr' => $j, 'text' => $g['unterkonten_count']
        ) ) );
        $buchungen_count = count( sql_buchungen( array( 'geschaeftsjahr' => $j ) ) );
        open_td( 'top', '', inlink( 'journal', array(
          'geschaeftsjahr' => $j, 'text' => $buchungen_count
        ) ) );
        $posten_count = count( sql_posten( array( 'geschaeftsjahr' => $j ) ) );
        open_td( 'top', '', inlink( 'journal', array(
          'geschaeftsjahr' => $j, 'text' => $posten_count
        ) ) );
        $saldoE = sql_unterkonten_saldo( array( 'seite' => 'P', 'kontoart' => 'E', 'geschaeftsjahr' => $j ) )
                - sql_unterkonten_saldo( array( 'seite' => 'A', 'kontoart' => 'E', 'geschaeftsjahr' => $j ) );
        open_td( 'top number', '', inlink( 'erfolgskonten', array(
          'geschaeftsjahr' => $j, 'text' => saldo_view( 'P', $saldoE )
        ) ) );
        $saldoP = sql_unterkonten_saldo( array( 'seite' => 'P', 'kontoart' => 'B', 'geschaeftsjahr' => $j ) )
                - sql_unterkonten_saldo( array( 'seite' => 'A', 'kontoart' => 'B', 'geschaeftsjahr' => $j ) );
                + $saldoE;
        open_td( 'top number', '', inlink( 'bestandskonten', array(
          'geschaeftsjahr' => $j, 'text' => saldo_view( 'P', $saldoP )
        ) ) );
        open_td( '', '', $j > $geschaeftsjahr_abgeschlossen ? 'offen' : 'abgeschlossen' );
        // open_td( '', '', '' );  // aktionen
    }
  close_table();
}


// darlehen
//

// function darlehenlist_view( $filters ) {
//   $darlehen = sql_darlehen( $filters );
//   if( ! $darlehen ) {
//     open_div( '', '', 'Keine Darlehen gefunden' );
//     return;
//   }
//   open_table( 'list' );
//     open_tr();
//       open_th( '', '', inlink( '', 'order_nwe=id,text=Id' ) );
//       open_th( '', '', inlink( '', 'order_new=cn,text=Darlehensgeber' ) );
//       open_th( '', '', 'zugesagt' );
//       open_th( '', '', 'abgerufen' );
//       open_th( '', '', 'eingezogen' );
//       open_th( '', '', inlink( '', 'orderby=soll,text=Restschuld' ) );
//       open_th( '', '', 'Aktionen' );
//     foreach( $darlehen as $d ) {
//       $person = sql_person( $d['people_id'] );
//       open_tr();
//         open_td( '', '', $d['darlehen_id'] );
//         open_td( '', '', $person['cn'] );
//         open_td( '', '', price_view( $d['betrag_zugesagt'] ) );
//         open_td( '', '', price_view( $d['betrag_abgerufen'] ) );
//         open_td( '', '', price_view( $d['betrag_eingezogen'] ) );
//         open_td( '', '', price_view( $d['saldo'] ) );
//         open_td( '', '', inlink( 'darlehen', array( 'darlehen_id' => $d['darlehen_id'] ) ) );
//     }
//   close_table();
// }
// 
// function darlehen_view( $darlehen_id ) {
//   $d = sql_darlehen( $darlehen_id );
//   $people_id = $d['people_id'];
//   $p = sql_person( $people_id );
//   open_table( 'list' );
//     open_tr();
//       open_td( '', '', 'Kreditor:' );
//       open_th( '', '', inlink( 'person', array( 'people_id' => $people_id, 'class' => 'href', 'text' => $p['cn'] ) ) );
//     open_tr();
//       open_td( '', '', 'zugesagt:' );
//       open_th( '', '', price_view( $d['betrag_zugesagt'] ) );
//     open_tr();
//       open_td( '', '', 'Zinssatz:' );
//       open_th( '', '', price_view( $d['zins_prozent'] ) );
//     open_tr();
//       open_td( '', '', 'abgerufen:' );
//       open_th( '', '', price_view( $d['betrag_abgerufen'] ) );
//   close_table();
// 
//   buchungenlist_view( array( 'unterkonten_id' => $d['unterkonten_id'] ) );
//   open_fieldset( '', '', 'Zahlungsplan', 'off' );
//     zahlungsplan_view( );
//   close_fieldset();
// }
// 
?>
