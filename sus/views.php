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

$mainmenu[] = array( 'script' => "posten",
     "title" => "Posten",
     "text" => "Posten" );

$mainmenu[] = array( 'script' => "geschaeftsjahre",
     "title" => "Gesch&auml;ftsjahre",
     "text" => "Gesch&auml;ftsjahre" );

$mainmenu[] = array( 'script' => "personen",
     "title" => "Personen",
     "text" => "Personen" );

$mainmenu[] = array( 'script' => "darlehenlist",
     "title" => "Darlehen",
     "text" => "Darlehen" );

$mainmenu[] = array( 'script' => "things",
     "title" => "Gegenst&auml;nde",
     "text" => "Gegenst&auml;nde" );

$mainmenu[] = array( 'script' => "logbook",
     "title" => "Logbuch",
     "text" => "Logbuch" );



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
  if( $geschaeftsjahr_thread ) {
    init_global_var( 'geschaeftsjahr', 'u', 'http,persistent', $geschaeftsjahr_thread );
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

function people_view( $filters = array(), $opts = true ) {
  global $script, $login_people_id;

  $opts = handle_list_options( $opts, array(
      'cn' => 'cn', 'gn' => 'gn', 'sn' => 'sn', 'phone' => 'telephonenumber', 'mail' => 'mail', 'jperson' => 'jperson', 'uid' => 'uid'
  ) );

  if( ! ( $people = sql_people( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'Keine Personen vorhanden' );
    return;
  }
  $count = count( $people );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $selected_people_id = adefault( $GLOBALS, $opts['select'], 0 );
  open_table( 'list hfill oddeven', '', $opts );
    open_th( '','', 'juristisch', 'jperson', $opts['sort_prefix'] );
    open_th( '','', 'cn', 'cn', $opts['sort_prefix'] );
    open_th( '','', 'Vorname', 'gn', $opts['sort_prefix'] );
    open_th( '','', 'Nachname', 'sn', $opts['sort_prefix'] );
    open_th( '','', 'Telefon', 'phone', $opts['sort_prefix'] );
    open_th( '','', 'Email', 'mail', $opts['sort_prefix'] );
    open_th( '','', 'user-ID', 'uid', $opts['sort_prefix'] );
    open_th( '','', 'Konten' );
    open_th( '','', 'Aktionen' );

    foreach( $people as $person ) {
      if( $person['nr'] < $limits['limit_from'] )
        continue;
      if( $person['nr'] > $limits['limit_to'] )
        break;
      $people_id = $person['people_id'];
      if( $opts['select'] ) {
        open_tr( 'selectable' );
          open_td(
            'left ' .( $people_id == $selected_people_id ? 'selected' : 'unselected' )
          , "onclick=\"".inlink( '', array( 'context' => 'js', $opts['select'] => $people_id ) ) ."\";"
          , $person['jperson']
          );
      } else {
        open_tr();
          open_td( 'right', '', $person['jperson'] );
      }
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
            echo postaction( 'update,class=drop,confirm=Person loeschen?', "action=deletePerson,message=$people_id" );
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

function thingslist_view( $filters, $opts = true ) {

  $opts = handle_list_options( $opts, array( 'cn' => 'cn', 'aj' => 'anschaffungsjahr', 'wert' ) );
  if( ! ( $things = sql_things( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'Keine Gegenstaende vorhanden' );
    return;
  }
  $count = count( $things );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list oddeven', '', $opts );
    open_tr();
      open_th( '', '', 'Name', 'cn', $opts['sort_prefix'] );
      open_th( '', '', 'Anschaffungsjahr', 'aj', $opts['sort_prefix'] );
      open_th( '', '', 'Restwert', 'wert', $opts['sort_prefix'] );
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
function unterkontenlist_view( $filters, $opts = true ) {

  $opts = handle_list_options( $opts, array(
      'geschaeftsjahr' => 'geschaeftsjahr'
    , 'kontoart' => 'kontoart', 'seite' => 'seite', 'rubrik' => 'rubrik', 'titel' => 'titel' , 'cn' => 'cn'
    , 'gb' => 'geschaeftsbereich', 'klasse' => 'kontoklassen.kontoklassen_id'
    , 'id' => 'unterkonten_id', 'saldo'
    , 'attribute' => 'CONCAT( personenkonto, sachkonto, bankkonto, zinskonto )'
  ) );

  if( ! ( $unterkonten = sql_unterkonten( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'Keine Unterkonten vorhanden' );
    return;
  }
  $count = count( $unterkonten );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;
  // prettydump( $count, 'count' );
  // prettydump( $limits, 'limits' );

  $selected_unterkonten_id = adefault( $GLOBALS, $opts['select'], 0 );
  open_table( 'list hfill oddeven', '', $opts );
    open_tr();
      open_th( '', '', 'nr' );
      open_th( '', '', 'id', 'id', $opts['sort_prefix'] );
      open_th( '', '', 'Jahr', 'geschaeftsjahr', $opts['sort_prefix'] );
      open_th( '', '', 'Art', 'kontoart', $opts['sort_prefix'] );
      open_th( '', '', 'Seite', 'seite', $opts['sort_prefix'] );
      open_th( '', '', 'Klasse', 'klasse', $opts['sort_prefix'] );
      open_th( '', '', 'Gesch&auml;ftsbereich', 'gb', $opts['sort_prefix'] );
      open_th( '', '', 'Rubrik', 'rubrik', $opts['sort_prefix'] );
      open_th( '', '', 'Hauptkonto', 'titel', $opts['sort_prefix'] );
      open_th( '', '', 'Unterkonto', 'cn', $opts['sort_prefix'] );
      open_th( '', '', 'Attribute', 'attribute', $opts['sort_prefix'] );
      open_th( '', '', 'Saldo', 'saldo', $opts['sort_prefix'] );
      open_th( '', '', 'Aktionen' );
    $attr = '';
    foreach( $unterkonten as $uk ) {
      $unterkonten_id = $uk['unterkonten_id'];
      if( $uk['nr'] < $limits['limit_from'] )
        continue;
      if( $uk['nr'] > $limits['limit_to'] )
        break;
      if( $opts['select'] ) {
        open_tr( 'selectable' );
          open_td(
              'right ' . ( $unterkonten_id == $selected_unterkonten_id ? 'selected' : 'unselected' )
          , "onclick=\"".inlink( '', array( 'context' => 'js', $opts['select'] => $unterkonten_id ) ) ."\";"
          , $unterkonten_id
          );
      } else {
        open_tr();
          open_td( 'right', '', $uk['nr'] );
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
            if( ! sql_delete_unterkonten( $unterkonten_id, 'check' ) ) {
              echo postaction(
                array( 'class' => 'drop', 'confirm' => 'wirklich loeschen?', 'update' => 1 )
              , array( 'action' => 'deleteUnterkonto', 'message' => $unterkonten_id )
              );
            }
          }
    }
  close_table();
}

// posten
//
function postenlist_view( $filters, $opts = true ) {
  global $script;

  $saldieren = adefault( $opts, 'saldieren', true );
  $opts = handle_list_options( $opts, array(
      'valuta' => 'CONCAT( geschaeftsjahr, valuta )'
    , 'buchung' => 'buchungsdatum', 'hauptkonto' => 'titel', 'unterkonto' => 'cn'
    , 'kontoart', 'seite'
    , 'kommentar', 'beleg', 'soll' => 'art DESC', 'haben' => 'art'
    , 'id' => 'posten_id'
  ) );

  if( ! ( $posten = sql_posten( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'Keine Posten vorhanden' );
    return;
  }
  $count = count( $posten );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  // $pattern = '/^ *CONCAT\( geschaeftsjahr, valuta \)[-,]/';
  // $saldieren = preg_match( $pattern, $orderby_sql );

  $saldoS = 0.0;
  $saldoH = 0.0;
  $saldo_posten_count = 0;
  open_table( 'list hfill oddeven', '', $opts );
    open_tr();
      open_th( '', '', 'nr' );
      open_th( '', '', 'id' );
      open_list_head( 'valuta', 'valuta' );
      open_th( '', '', 'Buchung', 'buchung', $opts['sort_prefix'] );
      switch( $script ) {
        case 'unterkonto':
        case 'hauptkonto':
          open_th( '', '', 'Text', 'kommentar', $opts['sort_prefix'] );
          open_th( '', '', 'Beleg', 'beleg', $opts['sort_prefix'] );
          $cols = 6;
          break;
        default:
          open_th( '', '', 'Art', 'kontoart', $opts['sort_prefix'] );
          open_th( '', '', 'Seite', 'seite', $opts['sort_prefix'] );
          open_th( '', '', 'Hauptkonto', 'hauptkonto', $opts['sort_prefix'] );
          open_th( '', '', 'Unterkonto', 'unterkonto', $opts['sort_prefix'] );
          $cols = 8;
      }
      open_th( '', '', 'Soll', 'soll', $opts['sort_prefix'] );
      open_th( '', '', 'Haben', 'haben', $opts['sort_prefix'] );
      open_th( '', '', 'Aktionen' );
    foreach( $posten as $p ) {
      // if( $p['nr'] > $limits['limit_from'] + $limits['limit_count'] )
      //   break;
      $is_vortrag = ( $p['valuta'] == 100 );
      // if( $is_vortrag ) {
      //   $saldoS = 0.0;
      //   $saldoH = 0.0;
      // }
      if( $saldieren && ( $p['nr'] == $limits['limit_from'] ) ) {
        open_tr( 'summe' );
          open_td( '', "colspan='$cols'" );
          echo "Anfangssaldo" . ( $saldo_posten_count ? " ($saldo_posten_count nicht gezeigte Posten)" : '' ) .':';
          if( $saldoS > $saldoH ) {
            open_td( 'number', '', price_view( $saldoS - $saldoH ) );
            open_td( '', '', ' ' );
          } else {
            open_td( '', '', ' ' );
            open_td( 'number', '', price_view( $saldoH - $saldoS ) );
          }
          open_td( '', '', ' ' );
          $saldo_posten_count = 0;
      }
      switch( $p['art'] ) {
        case 'S':
          $saldoS += $p['betrag'];
          break;
        case 'H':
          $saldoH += $p['betrag'];
          break;
      }
      $saldo_posten_count++;
      if( ( $p['nr'] >= $limits['limit_from'] ) && ( $p['nr'] <= $limits['limit_to'] ) ) {
        open_tr( $is_vortrag ? 'solidtop ' : '' );
          open_td( 'right', '', $p['nr'] );
          open_td( 'right', '', $p['posten_id'] );
          open_td( 'right', '', $p['geschaeftsjahr'] . ' / ' . ( $is_vortrag ? 'Vortrag' : monthday_view( $p['valuta'] ) ) );
          open_td( 'right', '', $p['buchungsdatum'] );
          switch( $script ) {
            case 'unterkonto':
            case 'hauptkonto':
              open_td( 'left', '', $p['kommentar'] );
              open_td( 'left', '', $p['beleg'] );
              break;
            default:
              open_td( 'center', '', $p['kontoart'] );
              open_td( 'center', '', $p['seite'] );
              open_td( 'left', '', inlink( 'hauptkonto', array(
                'class' => 'href', 'hauptkonten_id' => $p['hauptkonten_id']
              , 'text' => $p['titel']
              ) ) );
              open_td( 'left', '', inlink( 'unterkonto', array(
                'class' => 'href', 'unterkonten_id' => $p['unterkonten_id']
              , 'text' => "{$p['cn']}"
              ) ) );
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
      if( $p['nr'] == $limits['limit_to'] ) {
        if( $saldieren && ( $limits['limit_to'] + 1 < $count ) ) {
          open_tr( 'summe' );
            open_td( '', "colspan='$cols'", 'Zwischensaldo:' );
            if( $saldoS > $saldoH ) {
              open_td( 'number', '', price_view( $saldoS - $saldoH ) );
              open_td( '', '', ' ' );
            } else {
              open_td( '', '', ' ' );
              open_td( 'number', '', price_view( $saldoH - $saldoS ) );
            }
            open_td( '', '', ' ' );
        }
        $saldo_posten_count = 0;
      }
    }
    if( $saldieren ) {
      open_tr( 'summe' );
        open_td( '', "colspan='$cols'" );
        echo "Saldo gesamt" . ( $saldo_posten_count ? " (mit $saldo_posten_count nicht gezeigen Posten)" : '' ) .':';
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

function buchungenlist_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, array( 'valuta' => 'CONCAT( geschaeftsjahr, valuta )', 'bd' => 'buchungsdatum', 'id' => 'buchungen_id' ) );

  if( ! ( $buchungen = sql_buchungen( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'Keine Buchungen vorhanden' );
    return;
  }
  $count = count( $buchungen );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list hfill oddeven', '', $opts );
    open_tr( 'solidbottom solidtop' );
      open_th( 'center solidright solidleft', '', 'nr' );
      open_th( 'center solidright solidleft', '', 'id' );
      open_th( 'center solidright solidleft', '', 'Buchung', 'bd', $opts['sort_prefix'] );
      open_th( 'center solidright solidleft', '', 'Geschaeftsjahr / Valuta', 'valuta', $opts['sort_prefix'] );
      open_th( 'center solidright', "colspan='3'", 'Soll' );
      open_th( 'center solidright', "colspan='3'", 'Haben' );
      open_th( 'center solidright', '', 'Aktionen' );
    foreach( $buchungen as $b ) {
      if( $b['nr'] < $limits['limit_from'] )
        continue;
      if( $b['nr'] > $limits['limit_to'] )
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
        $td_hborderclass = ( $i == 0 ) ? ' solidtop smallpaddingtop' : ' notop';
        $td_hborderclass .= ( $i == $nMax-1 ) ? ' solidbottom smallpaddingbottom' : ' nobottom';
        if( $i == 0 ) {
          open_td( 'center top solidleft solidright'.$td_hborderclass, '', $b['nr'] );
          open_td( 'center top solidleft solidright'.$td_hborderclass, '', $b['buchungen_id'] );
          open_td( 'center top solidleft solidright'.$td_hborderclass, '', inlink( 'buchungen', array(
            'class' => 'href', 'text' => $b['buchungsdatum'], 'buchungsdatum' => $b['buchungsdatum']
          ) ) );
          open_td( 'center top solidleft solidright'.$td_hborderclass, '' );
            echo inlink( 'buchungen', array(
              'class' => 'href', 'text' => $geschaeftsjahr, 'geschaeftsjahr' => $geschaeftsjahr
            ) );
            echo ' / ';
            echo inlink( 'buchungen', array(
              'class' => 'href', 'valuta' => $b['valuta'], 'text' => ( $b['valuta'] == 100 ? 'Vortrag' : monthday_view( $b['valuta'] ) )
            ) );
        } else {
          open_td( 'solidleft solidright'.$td_hborderclass );
          open_td( 'solidleft solidright'.$td_hborderclass );
          open_td( 'solidleft solidright'.$td_hborderclass );
          open_td( 'solidleft solidright'.$td_hborderclass );
        }
        if( $i < $nS ) {
          $p = & $pS[$i];
          open_td( 'left solidleft'.$td_hborderclass );
            echo inlink( 'hauptkonto', array(
              'class' => 'href', 'hauptkonten_id' => $p['hauptkonten_id'], 'text' => "<b>{$p['kontoart']} {$p['seite']}</b> {$p['titel']}"
            ) );
          open_td( 'left'.$td_hborderclass , '', inlink( 'unterkonto', array( 'class' => 'href', 'text' => $p['cn'], 'unterkonten_id' => $p['unterkonten_id'] ) ) );
          open_td( 'number solidright'.$td_hborderclass, '', price_view( $p['betrag'] )) ;
        } else {
          open_td( $td_hborderclass, "colspan='3'", ' ' );
        }
        if( $i < $nH ) {
          $p = & $pH[$i];
          open_td( 'left solidleft'.$td_hborderclass );
            echo inlink( 'hauptkonto', array(
              'class' => 'href', 'hauptkonten_id' => $p['hauptkonten_id'], 'text' => "<b>{$p['kontoart']} {$p['seite']}</b> {$p['titel']}"
            ) );
          open_td( 'left'.$td_hborderclass, '', inlink( 'unterkonto', array( 'class' => 'href', 'text' => $p['cn'], 'unterkonten_id' => $p['unterkonten_id'] ) ) );
          open_td( 'number solidright'.$td_hborderclass, '', price_view( $p['betrag'] ) );
        } else {
          open_td( $td_hborderclass, "colspan='3'", ' ' );
        }
        if( $i == 0 ) {
          open_td( 'top solidright solidleft'.$td_hborderclass, "rowspan='1'" );
            echo inlink( 'buchung', array( 'class' => 'record', 'buchungen_id' => $id ) );
            echo postaction(
              array( 'class' => 'drop', 'confirm' => 'wirklich loeschen?', 'update' => 1 )
            , array( 'action' => 'deleteBuchung', 'message' => $id )
            );
          close_td();
        } else {
          open_td( 'solidleft solidright'.$td_hborderclass );
        }
      }
    }
  close_table();
}

function geschaeftsjahrelist_view( $filters = array(), $opts = true ) {
  global $geschaeftsjahr_abgeschlossen;

  $opts = handle_list_options( $opts, array( 'gj' => 'geschaeftsjahr' ) );

  if( ! ( $geschaeftsjahre = sql_geschaeftsjahre( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'Keine Geschaeftsjahre vorhanden' );
    return;
  }
  $count = count( $geschaeftsjahre );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list hfill', '', $opts );
    open_tr( 'solidbottom solidtop' );
      open_th( 'center solidright solidleft', '', 'Jahr', 'gj', $opts['sort_prefix'] );
      open_th( 'center solidright', '', 'Hauptkonten' );
      open_th( 'center solidright', '', 'Unterkonten' );
      open_th( 'center solidright', '', 'Buchungen' );
      open_th( 'center solidright', '', 'Posten' );
      open_th( 'center solidright', '', 'Jahresergebnis' );
      open_th( 'center solidright', '', 'Bilanzsumme' );
      open_th( 'center solidright', '', 'Status' );
      // open_th( 'center solidright', '', 'Aktionen' );
    foreach( $geschaeftsjahre as $g ) {
      if( $g['nr'] < $limits['limit_from'] )
        continue;
      if( $g['nr'] > $limits['limit_to'] )
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
                /* - sql_unterkonten_saldo( array( 'seite' => 'A', 'kontoart' => 'B', 'geschaeftsjahr' => $j ) ); */
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

function darlehenlist_view( $filters, $opts = true ) {

  $opts = handle_list_options( $opts, array( 'cn' => 'people_cn', 'zinssatz' => 'zins_prozent' ) );

  if( ! ( $darlehen = sql_darlehen( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'Keine Darlehen vorhanden' );
    return;
  }
  $count = count( $darlehen );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list hfill oddeven', '', $opts );
    open_tr();
      open_th( '', '', 'Kreditor', 'cn', $opts['sort_prefix'] );
      open_th( '', '', 'Darlehenkonto' );
      open_th( '', '', 'Zinskonto' );
      open_th( '', '', 'zugesagt' );
      open_th( '', '', 'abgerufen' );
      open_th( '', '', 'Zinssatz', 'zinssatz', $opts['sort_prefix'] );
      open_th( '', '', 'Aktionen' );
    foreach( $darlehen as $d ) {
      if( $person['nr'] < $limits['limit_from'] )
        continue;
      if( $p['nr'] > $limits['limit_to'] )
        break;
      open_tr();
        open_td( 'left', '', inlink( 'person', array(
          'class' => 'href', 'people_id' => $d['people_id'], 'text' => $d['people_cn'] 
        ) ) );
        open_td( 'left', '', inlink( 'unterkonto', array(
          'class' => 'href', 'unterkonten_id' => $d['darlehen_unterkonten_id'], 'text' => $d['darlehen_unterkonten_cn'] 
        ) ) );
        open_td( 'left', '', inlink( 'unterkonto', array(
          'class' => 'href', 'unterkonten_id' => $d['zins_unterkonten_id'], 'text' => $d['zins_unterkonten_cn'] 
        ) ) );
        open_td( 'left', '', price_view( $d['betrag_zugesagt'] ) );
        open_td( 'left', '', price_view( $d['betrag_abgerufen'] ) );
        open_td( 'left', '', price_view( $d['zins_prozent'] ) );
        open_td();
    }
  close_table();
}

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


// logbook:
//

function logbook_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, array( 
    'session' => 'sessions_id', 'timestamp' => 'timestamp', 'logbook_id' => 'logbook_id'
  , 'thread' => 'thread', 'window' => 'window' , 'script' => 'script'
  ) );

  if( ! ( $logbook = sql_logbook( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'Keine Eintraege vorhanden' );
    return;
  }
  $count = count( $logbook );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list hfill oddeven', '', $opts );
    open_tr();
      open_th( 'center',"rowspan='2'", 'id', 'logbook_id', $opts['sort_prefix'] );
      open_th( 'center',"rowspan='2'", 'session', 'session', $opts['sort_prefix'] );
      open_th( 'center',"rowspan='2'", 'timestamp', 'timestamp', $opts['sort_prefix'] );
      open_th( 'center','', 'thread', 'thread', $opts['sort_prefix'] );
      open_th( 'center','', 'window', 'window', $opts['sort_prefix'] );
      open_th( 'center','', 'script', 'script', $opts['sort_prefix'] );
      open_th( 'left',"rowspan='2'", 'event' );
      open_th( 'left',"rowspan='2'", 'note' );
      // open_th( 'left',"rowspan='2'", 'details' );
      open_th( 'center',"rowspan='2'", 'Aktionen' );
    open_tr();
      open_th( 'small center','', 'parent' );
      open_th( 'small center','', 'parent' );
      open_th( 'small center','', 'parent' );

    foreach( $logbook as $l ) {
      if( $l['nr'] < $limits['limit_from'] )
        continue;
      if( $l['nr'] > $limits['limit_to'] )
        break;
      open_tr();
        open_td( 'number', '', $l['logbook_id'] );
        open_td( 'number', '', $l['sessions_id'] );
        open_td( 'right', '', $l['timestamp'] );
        open_td( 'center' );
          open_div( 'center', '', $l['thread'] );
          open_div( 'center small', '', $l['parent_thread'] );
        open_td( 'center' );
          open_div( 'center', '', $l['window'] );
          open_div( 'center small', '', $l['parent_window'] );
        open_td( 'center' );
          open_div( 'center', '', $l['script'] );
          open_div( 'center small', '', $l['parent_script'] );
        open_td( 'left', '', $l['event'] );
        open_td( 'left' );
          if( strlen( $l['note'] ) > 100 )
            $s = substr( $l['note'], 0, 100 ).'...';
          else
            $s = $l['note'];
          if( $l['stack'] )
            $s .= ' [stack]';
          echo inlink( 'logentry', array( 'class' => 'card', 'text' => $s, 'logbook_id' => $l['logbook_id'] ) );
//         open_td();
//           if( $l['stack'] ) {
//             echo inlink( 'logentry', array( 'class' => 'card', 'text' => '', 'logbook_id' => $l['logbook_id'] ) );
//           } else {
//             echo '-';
//           }
        open_td();
          echo postaction( array( 'class' => 'button', 'text' => 'prune', 'update' => 1 )
                         , array( 'action' => 'prune', 'message' => $l['logbook_id'] ) );
    }
  close_table();
}



?>
