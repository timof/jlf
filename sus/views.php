<?php


function window_title() {
  return $GLOBALS['window'] . '/' . $GLOBALS['thread'] .'/'. $GLOBALS['login_sessions_id'];
}

function window_subtitle() {
  global $geschaeftsjahr, $geschaeftsjahr_thread, $geschaeftsjahr_current;
  if( $geschaeftsjahr_thread ) {
    init_var( 'geschaeftsjahr', "global,type=u,sources=http persistent,default=$geschaeftsjahr_thread" );
    if( isset( $geschaeftsjahr ) && ( $geschaeftsjahr != $geschaeftsjahr_thread ) )
      return 'Gesch'.H_AMP.'auml;ftsjahr: '.html_tag( 'span', 'alert quads', $geschaeftsjahr )." ($geschaeftsjahr_thread)";
    else
      return 'Gesch'.H_AMP."auml;ftsjahr: $geschaeftsjahr_thread";
  } else {
    return '(kein Gesch'.H_AMP.'auml;ftsjahr gew'.H_AMP.'auml;hlt)';
  }
}

//////////////////
// small pieces:
//

function saldo_view( $seite, $saldo ) {
  $red = '';
  if( $saldo < 0 ) {
    $red = 'red';
    $saldo = -$saldo;
  }
  switch( $seite ) {
    case 'A':
      $s = ( $red ? 'H' : 'S' );
      break;
    case 'P':
      $s = ( $red ? 'S' : 'H' );
      break;
  }
  return html_tag( 'span', "price number $red", sprintf( '%.02lf', $saldo )." $s" );
}


//////////////////
// table views:
//


// people:
//

function people_view( $filters = array(), $opts = true ) {
  global $script, $login_people_id;

  $opts = handle_list_options( $opts, 'people', array(
      'id' => 's=people_id,t=0'
    , 'cn' => 's,t', 'gn' => 's,t', 'sn' => 's,t'
    , 'phone' => 's=telephonenumber,t', 'mail' => 's,t'
    , 'dusie' => 's,t', 'genus' => 's,t', 'jperson' => 's,t', 'uid' => 's,t'
    , 'bank' => 's,t' , 'konto' => 's,t' , 'blz' => 's,t'
    , 'aktionen' => 't'
  ) );

  if( ! ( $people = sql_people( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', 'Keine Personen vorhanden' );
    return;
  }
  $count = count( $people );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $selected_people_id = adefault( $GLOBALS, $opts['select'], 0 );
  $opts['class'] = 'list hfill oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_list_head( 'nr' );
    open_list_head( 'id' );
    open_list_head( 'jperson', 'juristisch' );
    open_list_head( 'genus', 'Genus' );
    open_list_head( 'dusie', 'Anredeart' );
    open_list_head( 'cn' );
    open_list_head( 'gn', 'Vorname' );
    open_list_head( 'sn', 'Nachname' );
    open_list_head( 'phone', 'Telefon' );
    open_list_head( 'mail', 'Email' );
    open_list_head( 'uid' );
    open_list_head( 'bank', 'Bank' );
    open_list_head( 'blz', 'BLZ' );
    open_list_head( 'konto', 'Konto-Nr' );
    open_list_head( 'Aktionen' );

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
 ///// , "onclick=\"".inlink( '', array( 'context' => 'js', $opts['select'] => $people_id ) ) ."\";"
          , $person['nr']
          );
      } else {
        open_tr();
          open_td( 'right', $person['nr'] );
      }
        open_list_cell( 'id', $people_id );
        open_list_cell( 'jperson', $person['jperson'] );
        open_list_cell( 'genus', $person['genus'] );
        open_list_cell( 'dusie', $person['dusie'] );
        open_list_cell( 'cn', inlink( 'person', array( 'class' => 'href', 'people_id' => $people_id, 'text' => $person['cn'] ) ) );
        open_list_cell( 'gn', $person['gn'] );
        open_list_cell( 'sn', $person['sn'] );
        open_list_cell( 'phone', $person['telephonenumber'] );
        open_list_cell( 'mail', $person['mail'] );
        open_list_cell( 'uid', $person['uid'] );
        open_list_cell( 'bank', $person['bank_cn'] );
        open_list_cell( 'blz', $person['bank_blz'] );
        open_list_cell( 'konto', $person['bank_kontonr'] );
        open_list_cell( 'aktionen' );
          // echo inlink( 'person', "class=edit,text=,people_id=$people_id" );
          if( ( $script == 'personen' ) && ( $people_id != $login_people_id ) ) {
            echo inlink( '!submit', "class=drop,confirm=Person loeschen?,action=deletePerson,message=$people_id" );
          }
          $unterkonten = sql_unterkonten( array( 'personenkonto' => 1, 'people_id' => $people_id ) );
          if( $unterkonten ) {
            echo inlink( 'unterkonten', array(
              'class' => 'cash', 'text' => '', 'title' => 'Konten', 'people_id' => $people_id
            ) );
          }
    }
  close_table();
}

function person_view( $people_id ) {
  $person = sql_people( $people_id );
  open_table( 'list oddeven' );
    if( $person['jperson'] == 'J' ) {
      open_tr();
        open_td( '', 'Firma:' );
        open_td( '', $person['cn'] );
      open_tr();
        open_td( '', 'Ansprechpartner:' );
        open_td( '', "{$person['title']} {$person['vorname']} {$person['nachname']}" );
    } else {
      open_tr();
        open_td( '', 'Anrede:' );
        open_td( '', $person['title'] );
      open_tr();
        open_td( '', 'Vorname:' );
        open_td( '', $person['vorname'] );
      open_tr();
        open_td( '', 'Nachname:' );
        open_td( '', $person['nachname'] );
    }
    open_tr();
      open_td( '', 'Email:' );
      open_td( '', $person['email'] );
    open_tr();
      open_td( '', 'Telefon:' );
      open_td( '', $person['telephonenumber'] );
  close_table();
}

// things:
//

function thingslist_view( $filters = array(), $opts = true ) {
  global $geschaeftsjahr_thread;

  $opts = handle_list_options( $opts, 'things', array(
    'id' => 's=things_id,t', 'cn' => 's,t', 'aj' => 's=anschaffungsjahr,t'
    , 'wert' => 's,t', 'aktionen' => 't'
  ) );
  if( ! ( $things = sql_things( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', 'Keine Gegenstaende vorhanden' );
    return;
  }
  $count = count( $things );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'cn', 'Name' );
      open_list_head( 'aj', 'Anschaffungsjahr' );
      open_list_head( 'wert', 'Restwert' );
      open_list_head( 'Aktionen' );
    foreach( $things as $th ) {
      $id = $th['things_id'];
      open_tr();
        open_list_cell( 'nr', $th['nr'], 'class=right' );
        open_list_cell( 'id', $th['things_id'], 'class=right' );
        open_list_cell( 'cn', $th['cn'], 'class=right' );
        open_list_cell( 'aj',  $th['anschaffungsjahr'], 'class=center' );
        open_list_cell( 'wert', price_view( $th['wert'] ), 'class=number' );
        open_list_cell( 'aktionen' );
          $konten = sql_unterkonten( array( 'sachkonto' => 1, 'things_id' => $id ) );
          if( $konten ) {
            echo inlink( 'unterkonten', array(
              'class' => 'cash', 'text' => '', 'title' => 'Konten', 'things_id' => $id
            ) );
          }
    }
  close_table();
}

// function thing_view( $things_id, $stichtag = false ) {
//   $filters = array( 'things_id' => $things_id );
//   if( $stichtag )
//     $filters['posten.valuta'] = " <= $stichtag";
//   $thing = sql_one_thing( $filters );
//   open_table( 'list' );
//     open_tr();
//       open_td( '', '', 'Name:' );
//       open_td( '', '', $thing['cn'] );
//     open_tr();
//       open_td( '', '', 'Anschaffungsjahr:' );
//       open_td( '', '', $thing['anschaffungsjahr'] );
//     open_tr();
//       open_td( '', '', 'Abschreibungszeit:' );
//       open_td( '', '', $thing['abschreibungszeit'] );
//     open_tr();
//       open_td( '', '', 'Restwert:' );
//       open_td( '', '', price_view( $thing['wert'] ) );
//     open_tr();
//       open_td( '', '', inlink( 'thing', "class=edit,text=edieren,things_id=$things_id" ) );
//       open_td( '', '', inlink( 'unterkonto', "class=browse,text=Wertentwicklung,things_id=$things_id" ) );
//   close_table();
// }

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


function hauptkontenlist_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, 'hk', array(
      'id' => 's=hauptkonten_id,t=0'
    , 'geschaeftsjahr' => 's,t'
    , 'kontenkreis' => 's,t', 'seite' => 's,t', 'rubrik' => 's,t'
    , 'titel' => 's,t'
    , 'gb' => array( 't', 's' => 'CONCAT( kontenkreis, vortragskonto, geschaeftsbereich )' )
    , 'klasse' => 's=kontoklassen.kontoklassen_id,t'
    , 'hgb' => 's=hgb_klasse,t=0'
    , 'saldo' => 's,t'
    , 'attribute' => array( 's' => 'CONCAT( bankkonto, personenkonto, sachkonto, vortragskonto )', 't' )
    , 'aktionen' => 't'
  ) );

  if( ! ( $hauptkonten = sql_hauptkonten( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', 'Keine Hauptkonten vorhanden' );
    return;
  }
  $count = count( $hauptkonten );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $saldieren = ( $opts['cols']['saldo']['toggle'] == '1' );
  $seite = $hauptkonten[0]['seite'];
  $kontenkreis = $hauptkonten[0]['kontenkreis'];
  foreach( $hauptkonten as $hk ) {
    if( $hk['kontenkreis'].$hk['seite'] !== "$kontenkreis$seite" )
      $saldieren = false;
  }

  $saldo = 0;
  $saldo_konten_count = 0;
  $selected_hauptkonten_id = adefault( $GLOBALS, $opts['select'], 0 );

  $opts['class'] = 'list hfill oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'geschaeftsjahr', 'Jahr' );
      open_list_head( 'kontenkreis', 'Kreis' );
      open_list_head( 'Seite' );
      open_list_head( 'Klasse' );
      open_list_head( 'hgb', 'HGB-Klasse' );
      open_list_head( 'gb', 'Gesch'.H_AMP.'auml;ftsbereich' );
      open_list_head( 'Rubrik' );
      open_list_head( 'Titel' );
      open_list_head( 'Attribute' );
      $cols_before_saldo = current_table_col_number();
      open_list_head( 'Saldo' );
      open_list_head( 'Aktionen' );
    foreach( $hauptkonten as $hk ) {
      $hauptkonten_id = $hk['hauptkonten_id'];
      if( $saldieren && (  $hk['nr'] == $limits['limit_from'] ) ) {
        open_tr( 'sum' );
          open_td( "colspan=$cols_before_saldo" );
          echo "Anfangssaldo" . ( $saldo_konten_count ? " ($saldo_konten_count nicht gezeigte Konten)" : '' ) .':';
          open_td( 'number', saldo_view( $seite, $saldo ) );
          open_list_cell( 'aktionen', '', '' );
      }
      $saldo += sql_unterkonten_saldo( "hauptkonten_id=$hauptkonten_id" );
      $saldo_konten_count++;

      if( $hk['nr'] < $limits['limit_from'] )
        continue;
      if( $hk['nr'] > $limits['limit_to'] )
        continue;
      if( $opts['select'] ) {
        open_tr( 'trselectable' . ( ( $GLOBALS[ $opts['select']  ] == $hauptkonten_id ) ? ' trselected' : '' ) );
          open_list_cell( 'nr', $hk['nr']
          , array( 'class' => 'right'
                 , 'onclick' => inlink( '!submit', array( 'context' => 'js', $opts['select'] => $hauptkonten_id ) ) )
          );
      } else {
        open_tr();
          open_list_cell( 'nr', $hk['nr'], 'class=number' );
      }
        open_list_cell( 'id', $hauptkonten_id, 'class=number' );
        open_list_cell( 'geschaeftsjahr', $hk['geschaeftsjahr'], 'class=center' );
        open_list_cell( 'kontenkreis', false, 'class=center' );
          switch( $hk['kontenkreis'] ) {
            case 'E':
              echo inlink( 'erfolgskonten', 'text=E,class=href' );
              break;
            case 'B':
              echo inlink( 'bestandskonten', 'text=B,class=href' );
              break;
          }
        open_list_cell( 'seite', $hk['seite'], 'class=center' );
        open_list_cell( 'klasse', inlink( 'hauptkontenliste', array(
          'class' => 'href', 'text' => $hk['kontoklassen_cn']
        , 'kontoklassen_id' => $hk['kontoklassen_id']
        ) ) );
        open_list_cell( 'hgb' );
        if( $hk['hgb_klasse'] )
          echo inlink( 'hauptkontenliste', array( 'class' => 'href', 'text' => $hk['hgb_klasse'] , 'hgb_klasse' => $hk['hgb_klasse'] ) );
        else
          echo "(keine)";
        open_list_cell( 'gb' );
          if( $hk['kontenkreis'] == 'E' ) {
            echo inlink( 'hauptkonten', array(
              'class' => 'href', 'text' => $hk['geschaeftsbereich'] , 'kontenkreis' => 'E'
            , 'geschaeftsbereiche_id' => sql_unique_id( 'kontoklassen', 'geschaeftsbereich', $hk['geschaeftsbereich'] )
            ) );
          } else {
            echo $hk['vortragskonto'] ? "Vortrag ".$hk['vortragskonto'] : 'n/a';
          }
        open_list_cell( 'rubrik', $hk['rubrik'] );
        open_list_cell( 'titel', inlink( 'hauptkonto', array(
          'class' => 'href', 'text' => $hk['titel'] , 'hauptkonten_id' => $hk['hauptkonten_id']
        ) ) );
        open_list_cell( 'attribute' );
          if( $hk['personenkonto'] )
            echo "Personenkonten";
          if( $hk['sachkonto'] )
            echo "Sachkonten";
          if( $hk['bankkonto'] )
            echo "Bankkonten";
          if( $hk['vortragskonto'] )
            echo 'Vortrag';
          $saldo = sql_unterkonten_saldo( array( 'hauptkonten_id' => $hauptkonten_id ) );
        open_list_cell( 'saldo', saldo_view( $hk['seite'], $saldo ), 'class=number' );
        open_list_cell( 'aktionen' );
          if( $hk['hauptkonto_geschlossen'] ) {
            echo "geschlossen";
          } else {
            if( ! sql_delete_hauptkonten( $hauptkonten_id, 'check' ) ) {
              echo inlink( '!submit', "class=drop,confirm=Hauptkonto wirklich loeschen?,action=deleteHauptkonto,message=$hauptkonten_id" );
            }
            if( ! sql_hauptkonto_schliessen( $hauptkonten_id, 'check' ) ) {
              echo inlink( '!submit', "class=lock,confirm=Hauptkonto wirklich schliessen?,action=hauptkontoSchliessen,message=$hauptkonten_id" );
            }
          }
      if( $hk['nr'] == $limits['limit_to'] ) {
        if( $saldieren && ( $limits['limit_to'] + 1 < $count ) ) {
          open_tr( 'sum' );
            open_td( "colspan=$cols_before_saldo", 'Zwischensaldo:' );
            open_td( 'number', saldo_view( $seite, $saldo ) );
            open_list_cell( 'aktionen', '', '' );
        }
        $saldo_konten_count = 0;
      }
    }
    if( $saldieren ) {
      open_tr( 'sum' );
        open_td( "colspan=$cols_before_saldo" );
        echo "Saldo gesamt" . ( $saldo_konten_count ? " ($saldo_konten_count nicht gezeigte Konten)" : '' ) .':';
        open_td( 'number', saldo_view( $seite, $saldo ) );
        open_list_cell( 'aktionen', '', '' );
    }

  close_table();
}


// unterkonten
//
function unterkontenlist_view( $filters = array(), $opts = true ) {
  global $table_options_stack, $table_level;

  $opts = handle_list_options( $opts, 'uk', array(
      'id' => 's=unterkonten_id,t=0'
    , 'geschaeftsjahr' => 's,t'
    , 'kontenkreis' => 's,t', 'seite' => 's,t', 'rubrik' => 's,t'
    , 'titel' => 's,t'
    , 'cn' => 's,t'
    , 'gb' => array( 't', 's' => 'CONCAT( kontenkreis, vortragskonto, geschaeftsbereich )' )
    , 'klasse' => 's=kontoklassen.kontoklassen_id,t'
    , 'hgb' => 's=hgb_klasse,t=0'
    , 'saldo' => 's,t'
    , 'attribute' => array( 's' => 'CONCAT( bankkonto, personenkonto, sachkonto, vortragskonto, zinskonto )', 't' )
    , 'aktionen' => 't'
  ) );

  if( ! ( $unterkonten = sql_unterkonten( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', 'Keine Unterkonten vorhanden' );
    return;
  }
  $count = count( $unterkonten );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $saldieren = ( $opts['cols']['saldo']['toggle'] == '1' );
  $seite = $unterkonten[0]['seite'];
  $kontenkreis = $unterkonten[0]['kontenkreis'];
  foreach( $unterkonten as $uk ) {
    if( $uk['kontenkreis'].$uk['seite'] !== "$kontenkreis$seite" )
      $saldieren = false;
  }

  $saldo = 0;
  $saldo_konten_count = 0;

  $opts['class'] = 'list hfill oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'geschaeftsjahr', 'Jahr' );
      open_list_head( 'kontenkreis', 'Kreis' );
      open_list_head( 'Seite' );
      open_list_head( 'Klasse' );
      open_list_head( 'hgb', 'HGB-Klasse' );
      open_list_head( 'gb', 'Gesch'.H_AMP.'auml;ftsbereich' );
      open_list_head( 'Rubrik' );
      open_list_head( 'titel', 'Hauptkonto' );
      open_list_head( 'cn', 'Unterkonto' );
      open_list_head( 'Attribute' );
      $cols_before_saldo = current_table_col_number();
      open_list_head( 'Saldo' );
      open_list_head( 'Aktionen' );
    foreach( $unterkonten as $uk ) {
      $unterkonten_id = $uk['unterkonten_id'];
      if( $saldieren && (  $uk['nr'] == $limits['limit_from'] ) ) {
        open_tr( 'sum' );
          open_td( "colspan=$cols_before_saldo" );
          echo "Anfangssaldo" . ( $saldo_konten_count ? " ($saldo_konten_count nicht gezeigte Konten)" : '' ) .':';
          open_td( 'number', saldo_view( $seite, $saldo ) );
          open_list_cell( 'aktionen', '', '' );
      }
      $saldo += $uk['saldo'];
      $saldo_konten_count++;

      if( $uk['nr'] < $limits['limit_from'] )
        continue;
      if( $uk['nr'] > $limits['limit_to'] )
        continue;
      if( $opts['select'] ) {
        open_tr( array(
          'class' => 'trselectable' . ( ( $GLOBALS[ $opts['select'] ] == $unterkonten_id ) ? ' trselected' : '' )
        , 'onclick' => inlink( '!submit', array( 'context' => 'js', $opts['select'] => $unterkonten_id ) )
        ) );
      } else {
        open_tr();
      }
        open_list_cell( 'nr', $uk['nr'], 'class=number' );
        open_list_cell( 'id', $unterkonten_id, 'class=number' );
        open_list_cell( 'geschaeftsjahr', $uk['geschaeftsjahr'], 'class=center' );
        open_list_cell( 'kontenkreis', false, 'class=center' );
          switch( $uk['kontenkreis'] ) {
            case 'E':
              echo inlink( 'erfolgskonten', 'text=E,class=href' );
              break;
            case 'B':
              echo inlink( 'bestandskonten', 'text=B,class=href' );
              break;
          }
        open_list_cell( 'seite', $uk['seite'], 'class=center' );
        open_list_cell( 'klasse', inlink( 'unterkontenliste', array(
          'class' => 'href', 'text' => $uk['kontoklassen_cn']
        , 'kontoklassen_id' => $uk['kontoklassen_id']
        ) ) );
        open_list_cell( 'hgb' );
        if( $uk['hgb_klasse'] )
          echo inlink( 'unterkontenliste', array( 'class' => 'href', 'text' => $uk['hgb_klasse'] , 'hgb_klasse' => $uk['hgb_klasse'] ) );
        else
          echo "(keine)";
        open_list_cell( 'gb' );
          if( $uk['kontenkreis'] == 'E' ) {
            echo inlink( 'unterkontenliste', array(
              'class' => 'href', 'text' => $uk['geschaeftsbereich'] , 'kontenkreis' => 'E'
            , 'geschaeftsbereiche_id' => sql_unique_id( 'kontoklassen', 'geschaeftsbereich', $uk['geschaeftsbereich'] )
            ) );
          } else {
            echo $uk['vortragskonto'] ? "Vortrag ".$uk['vortragskonto'] : 'n/a';
          }
        open_list_cell( 'rubrik', $uk['rubrik'] );
        open_list_cell( 'titel', inlink( 'hauptkonto', array(
          'class' => 'href', 'text' => $uk['titel'] , 'hauptkonten_id' => $uk['hauptkonten_id']
        ) ) );
        open_list_cell( 'cn', inlink( 'unterkonto', array(
          'unterkonten_id' => $unterkonten_id, 'class' => 'href' , 'text' => $uk['cn']
        ) ) );
        open_list_cell( 'attribute' );
          if( $uk['personenkonto'] )
            echo inlink( 'person', array( 'people_id' => $uk['people_id'], 'text' => 'Personenkonto' ) );
          if( $uk['sachkonto'] )
            echo 'Sachkonto';
          if( $uk['bankkonto'] )
            echo 'Bankkonto';
          if( $uk['zinskonto'] )
            echo ' Zinskonto';
          if( $uk['vortragskonto'] )
            echo 'Vortrag aus '.$uk['vortragsjahr'];
        open_list_cell( 'saldo', saldo_view( $uk['seite'], $uk['saldo'] ), 'class=number' );
        open_list_cell( 'aktionen' );
          if( $uk['unterkonto_geschlossen'] ) {
            echo "geschlossen";
          } else {
            if( ! sql_delete_unterkonten( $unterkonten_id, 'check' ) ) {
              echo inlink( '!submit', "class=drop,confirm=Unterkonto wirklich loeschen?,action=deleteUnterkonto,message=$unterkonten_id" );
            }
            if( ! sql_unterkonto_schliessen( $unterkonten_id, 'check' ) ) {
              echo inlink( '!submit', "class=lock,confirm=Unterkonto wirklich schliessen?,action=unterkontoSchliessen,message=$unterkonten_id" );
            }
          }
      if( $uk['nr'] == $limits['limit_to'] ) {
        if( $saldieren && ( $limits['limit_to'] + 1 < $count ) ) {
          open_tr( 'sum' );
            open_td( "colspan=$cols_before_saldo", 'Zwischensaldo:' );
            open_td( 'number', saldo_view( $seite, $saldo ) );
            open_list_cell( 'aktionen', '', '' );
        }
        $saldo_konten_count = 0;
      }
    }
    if( $saldieren ) {
      open_tr( 'sum' );
        open_td( "colspan=$cols_before_saldo" );
        echo "Saldo gesamt" . ( $saldo_konten_count ? " ($saldo_konten_count nicht gezeigte Konten)" : '' ) .':';
        open_td( 'number', saldo_view( $seite, $saldo ) );
        open_list_cell( 'aktionen', '', '' );
    }

  close_table();
}

// posten
//
function postenlist_view( $filters = array(), $opts = array() ) {
  global $script;

  $opts = parameters_explode( $opts );
  $saldieren = adefault( $opts, 'saldieren', true );
  $opts = tree_merge( array( 'orderby' => 'valuta,hauptkonto,unterkonto' ), $opts );

  $actions = adefault( $opts, 'actions', true );
  if( $actions === true ) {
    $actions = 'buchung';
  }
  $actions = parameters_explode( $actions );
  $action_buchung = adefault( $actions, 'buchung', true );
  $action_mark = adefault( $actions, 'mark', false );
  if( $action_mark ) {
    $action_mark = parameters_explode( $action_mark, array(
      'default_key' => 'prefix', 'keep' => 'prefix=posten_,values=01,opts='
    ) );
  }

  $cols = array(
    'id' => 't=0,s=posten_id'
  , 'valuta' => array( 't', 's' => 'CONCAT( geschaeftsjahr, 1000 + valuta )' ) // make sure valuta has 4 digits
  , 'buchung' => 't=0,s=buchungsdatum'
  , 'hauptkonto' => 't,s=titel'
  , 'unterkonto' => 't,s=cn'
  , 'kontenkreis' => 't,s', 'seite' => 't,s'
  , 'vorfall' => 't=0,s' , 'beleg' => 't=0,s'
  , 'soll' => array( 's' => 'art DESC, betrag' )
  , 'haben' => array( 's' => 'art, betrag' )
  , 'saldo' => 't=0'
  , 'aktionen' => 't'
  );
  if( adefault( $filters, 'unterkonten_id', 0 ) > 0 ) {
    $cols['unterkonto'] = 't=0,s=cn';
    $cols['hauptkonto'] = 't=0,s=cn';
  }
  if( adefault( $filters, 'hauptkonten_id', 0 ) > 0 ) {
    $cols['hauptkonto'] = 't=0,s=cn';
  }

  $opts = handle_list_options( $opts, 'po', $cols );
  if( ! ( $posten = sql_posten( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', 'Keine Posten vorhanden' );
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

  $opts['class'] = 'list hfill oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'valuta', 'Valuta', "colspan=2" );
      open_list_head( 'Buchung' );
      switch( $script ) {
        case 'unterkonto':
        case 'hauptkonto':
        default:
          open_list_head( 'Vorfall' );
          open_list_head( 'Beleg' );
          open_list_head( 'kontenkreis', 'Kreis' );
          open_list_head( 'Seite' );
          open_list_head( 'Hauptkonto' );
          open_list_head( 'Unterkonto' );
      }
      $cols_before_soll = current_table_col_number();
      open_list_head( 'Soll' );
      open_list_head( 'Haben' );
      open_list_head( 'Saldo' );
      open_list_head( 'Aktionen' );
    foreach( $posten as $p ) {
      $is_vortrag = ( $p['valuta'] == 100 );
      if( $saldieren && ( $p['nr'] == $limits['limit_from'] ) ) {
        open_tr( 'sum' );
          open_td( "colspan=$cols_before_soll" );
          echo "Anfangssaldo" . ( $saldo_posten_count ? " ($saldo_posten_count nicht gezeigte Posten)" : '' ) .':';
          if( $saldoS > $saldoH ) {
            open_td( 'number', price_view( $saldoS - $saldoH ) );
            open_td( '', ' ' );
          } else {
            open_td( '', ' ' );
            open_td( 'number', price_view( $saldoH - $saldoS ) );
          }
          open_list_cell( 'saldo', '', ' ' );
          open_list_cell( 'aktionen', '', ' ' );
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
        $posten_id = $p['posten_id'];
        $tr_attr['class'] = ( $is_vortrag ? 'solidtop' : '' );
        if( $opts['select'] ) {
          $tr_attr['class'] .= ' trselectable';
          if( adefault( $GLOBALS, $opts['select'], 0 ) == $posten_id ) {
            $tr_attr['class'] .= ' trselected';
          }
          $tr_attr['onclick'] .= inlink( '!submit', array( 'context' => 'js', $opts['select'] => $posten_id ) );
        }
        open_tr( $tr_attr );
          open_list_cell( 'nr', $p['nr'], 'class=number' );
          open_list_cell( 'id', $posten_id, 'class=number' );
          open_list_cell( 'valuta', $p['geschaeftsjahr'], 'class=right' );
          open_list_cell( 'valuta', ( $is_vortrag ? 'Vortrag' : monthday_view( $p['valuta'] ) ), 'class=left' );
          open_list_cell( 'buchung', $p['buchungsdatum'], array( 'class' => 'right' ) );
          switch( $script ) {
            case 'unterkonto':
            case 'hauptkonto':
            default:
              open_list_cell( 'vorfall', $p['vorfall'] );
              open_list_cell( 'beleg', $p['beleg'] );
            // break;
              open_list_cell( 'kontenkreis', $p['kontenkreis'], 'class=center' );
              open_list_cell( 'seite', $p['seite'], 'class=center' );
              open_list_cell( 'hauptkonto', inlink( 'hauptkonto', array(
                'class' => 'href', 'hauptkonten_id' => $p['hauptkonten_id']
              , 'text' => $p['titel']
              ) ) );
              open_list_cell( 'unterkonto', inlink( 'unterkonto', array(
                'class' => 'href', 'unterkonten_id' => $p['unterkonten_id']
              , 'text' => "{$p['cn']}"
              ) ) );
          }
          if( $saldoH > $saldoS )
            $title = sprintf( 'Zwischensaldo: %.02lf H', $saldoH - $saldoS );
          else
            $title = sprintf( 'Zwischensaldo: %.02lf S', $saldoS - $saldoH );
          switch( $p['art'] ) {
            case 'S':
              open_list_cell( 'soll', price_view( $p['betrag'] ), array( 'class' => 'number', 'title' => $title ) );
              open_list_cell( 'haben', '' );
              break;
            case 'H':
              open_list_cell( 'soll', '', ' ' );
              open_list_cell( 'haben', price_view( $p['betrag'] ), array( 'class' => 'number', 'title' => $title ) );
              break;
          }
          open_list_cell( 'saldo', ( ( $saldoH > $saldoS ) ?  price_view( $saldoH - $saldoS ) . ' H' : price_view( $saldoS - $saldoH ) . ' S' ), 'class=number' );
          open_list_cell( 'aktionen' );
            if( $action_buchung ) {
              echo inlink( 'buchung', array( 'class' => 'record', 'buchungen_id' => $p['buchungen_id'] ) );
            }
            if( $action_mark ) {
              echo inlink( 'buchung', array( 'class' => 'record', 'buchungen_id' => $p['buchungen_id'] ) );
            }
      }
      if( $p['nr'] == $limits['limit_to'] ) {
        if( $saldieren && ( $limits['limit_to'] + 1 < $count ) ) {
          open_tr( 'sum' );
            open_td( "colspan=$cols_before_soll", 'Zwischensaldo:' );
            if( $saldoS > $saldoH ) {
              open_td( 'number', price_view( $saldoS - $saldoH ) );
              open_td( '', ' ' );
            } else {
              open_td( '', ' ' );
              open_td( 'number', price_view( $saldoH - $saldoS ) );
            }
            open_list_cell( 'saldo', '', ' ' );
            open_list_cell( 'aktionen', '', ' ' );
        }
        $saldo_posten_count = 0;
      }
    }
    if( $saldieren ) {
      open_tr( 'sum' );
        open_td( "colspan=$cols_before_soll" );
        echo "Saldo gesamt" . ( $saldo_posten_count ? " (mit $saldo_posten_count nicht gezeigen Posten)" : '' ) .':';
        if( $saldoS > $saldoH ) {
          open_td( 'number', price_view( $saldoS - $saldoH ) );
          open_td( '', ' ' );
        } else {
          open_td( '', ' ' );
          open_td( 'number', price_view( $saldoH - $saldoS ) );
        }
        open_list_cell( 'saldo', '', ' ' );
        open_list_cell( 'aktionen', '', ' ' );
    }
  close_table();
}

// buchungen
//
// function buchung_view( $buchungen_id ) {
//   open_div();
//     open_div( 'bold', "Buchung $buchungen_id:" );
//     postenlist_view( "buchungen_id=$buchungen_id,art=S", 'seite,kontoklassen_id' );
//     open_div( 'bold', "an" );
//     postenlist_view( "buchungen_id=$buchungen_id,art=H", 'seite,kontoklassen_id' );
//   close_div();
// }

function buchungenlist_view( $filters = array(), $opts = true ) {
  global $table_level, $table_options_stack;

  $opts = handle_list_options( $opts, 'bu', array(
    'id' => 't=0,s=buchungen_id'
  , 'valuta' => array( 't', 's' => 'CONCAT( geschaeftsjahr, 1000 + valuta )' )
  , 'buchung' => 's=buchungsdatum,t=0'
  , 'vorfall' => 's,t'
  , 'soll' => 't', 'haben' => 't'
  , 'aktionen' => 't'
  ) );

  if( ! ( $buchungen = sql_buchungen( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', 'Keine Buchungen vorhanden' );
    return;
  }
  $count = count( $buchungen );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list hfill oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr( 'solidbottom solidtop' );
      open_list_head( 'nr', 'nr', 'class=center solidright solidleft' );
      open_list_head( 'id', 'id', 'class=center solidright solidleft' );
      open_list_head( 'buchung', 'Buchung', 'class=center solidright solidleft' );
      open_list_head( 'valuta', 'Gesch'.H_AMP.'auml;ftsjahr / Valuta', 'class=center solidright solidleft' );
      open_list_head( 'vorfall', 'Vorfall', 'class=center solidright solidleft' );
      open_list_head( 'soll', 'Soll', 'class=center solidright,colspan=3' );
      open_list_head( 'haben', 'Haben', 'class=center solidright,colspan=3' );
      open_list_head( 'aktionen', 'Aktionen', 'class=center solidright' );
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
        $table_options_stack[ $table_level ]['row_number'] = $b['nr'];
        open_tr( $i == $nMax-1 ? 'solidbottom' : '' );
        $td_hborderclass = ( $i == 0 ) ? ' solidtop smallpaddingtop' : ' notop';
        $td_hborderclass .= ( $i == $nMax-1 ) ? ' solidbottom smallpaddingbottom' : ' nobottom';
        if( $i == 0 ) {
          open_list_cell( 'nr', $b['nr'], 'class=center top solidleft solidright'.$td_hborderclass );
          open_list_cell( 'id', $b['buchungen_id'], 'class=center top solidleft solidright'.$td_hborderclass );
          open_list_cell( 'buchung'
          , inlink( 'buchungen', array( 'class' => 'href', 'text' => $b['buchungsdatum'], 'buchungsdatum' => $b['buchungsdatum'] ) )
          , 'class=center top solidleft solidright'.$td_hborderclass
          );
          open_list_cell( 'valuta'
          , inlink( 'buchungen', array(
              'class' => 'href', 'text' => $geschaeftsjahr, 'geschaeftsjahr' => $geschaeftsjahr
            ) )
            . ' / ' .
            inlink( 'buchungen', array(
              'class' => 'href', 'valuta' => $b['valuta'], 'text' => ( $b['valuta'] == 100 ? 'Vortrag' : monthday_view( $b['valuta'] ) )
            ) )
          , 'class=center top solidleft solidright'.$td_hborderclass
          );
          open_list_cell( 'vorfall'
          , inlink( 'buchung', array( 'class' => 'href', 'text' => $b['vorfall'], 'buchungen_id' => $b['buchungen_id'] ) )
          , array( 'class' => 'left top solidleft'.$td_hborderclass , 'rowspan' => $nMax )
          );
        } else {
          open_list_cell( 'nr', '', 'class=solidleft solidright'.$td_hborderclass );
          open_list_cell( 'id', '', 'class=solidleft solidright'.$td_hborderclass );
          open_list_cell( 'buchung', '', 'class=solidleft solidright'.$td_hborderclass );
          open_list_cell( 'valuta', '', 'class=solidleft solidright'.$td_hborderclass );
          // open_list_cell( 'vorfall', '', 'class=solidleft solidright'.$td_hborderclass );
        }
        if( $i < $nS ) {
          $p = & $pS[$i];
          open_list_cell( 'soll'
          , inlink( 'hauptkonto', array(
              'class' => 'href', 'hauptkonten_id' => $p['hauptkonten_id']
            , 'text' => html_tag( 'span', 'bold', "{$p['kontenkreis']} {$p['seite']}" ) . " {$p['titel']}"
            ) )
          , 'class=left solidleft'.$td_hborderclass . ( $i > 0 ? ' dottedtop' : '' )
          );
          open_list_cell( 'soll'
          , inlink( 'unterkonto', array( 'class' => 'href', 'text' => $p['cn'], 'unterkonten_id' => $p['unterkonten_id'] ) )
          , 'class=left'.$td_hborderclass . ( $i > 0 ? ' dottedtop' : '' ) 
          );
          open_list_cell( 'soll', price_view( $p['betrag'] ), 'class=number solidright'.$td_hborderclass . ( $i > 0 ? ' dottedtop' : '' ) );
        } else {
          open_list_cell( 'soll', '', "class=$td_hborderclass,colspan=3" );
        }
        if( $i < $nH ) {
          $p = & $pH[$i];
          open_list_cell( 'haben'
          , inlink( 'hauptkonto', array(
              'class' => 'href', 'hauptkonten_id' => $p['hauptkonten_id']
            , 'text' => html_tag( 'span', 'bold', "{$p['kontenkreis']} {$p['seite']}" ) ." {$p['titel']}"
            ) )
          , 'class=left solidleft'.$td_hborderclass . ( $i > 0 ? ' dottedtop' : '' )
          );
          open_list_cell( 'haben'
          , inlink( 'unterkonto', array( 'class' => 'href', 'text' => $p['cn'], 'unterkonten_id' => $p['unterkonten_id'] ) )
          , 'class=left'.$td_hborderclass . ( $i > 0 ? ' dottedtop' : '' ) 
          );
          open_list_cell( 'haben', price_view( $p['betrag'] ), 'class=number solidright'.$td_hborderclass . ( $i > 0 ? ' dottedtop' : '' ) );
        } else {
          open_list_cell( 'haben', '', "class=$td_hborderclass,colspan=3" );
        }
        if( $i == 0 ) {
          open_list_cell( 'aktionen'
          , inlink( 'buchung', array( 'class' => 'record', 'buchungen_id' => $id ) )
            . inlink( '!submit', "class=drop,confirm=wirklich loeschen?,action=deleteBuchung,message=$id" )
          , 'class=top solidright solidleft'.$td_hborderclass
          );
        } else {
          open_list_cell( 'aktionen', '', 'class=solidleft solidright'.$td_hborderclass );
        }
      }
    }
  close_table();
}

function geschaeftsjahrelist_view( $filters = array(), $opts = true ) {
  global $geschaeftsjahr_abgeschlossen;

  $opts = handle_list_options( $opts, 'gj', array(
    'gj' => 't'
  , 'buchungen' => 't', 'posten' => 't'
  , 'ergebnis' => 't', 'bilanzsumme' => 't', 'status' => 't', 'aktionen' => 't'
  ) );

  if( ! ( $geschaeftsjahre = sql_geschaeftsjahre( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', 'Keine Gesch'.H_AMP.'auml;ftsjahre vorhanden' );
    return;
  }
  $count = count( $geschaeftsjahre );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list hfill oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr( 'solidbottom solidtop' );
      open_list_head( 'gj', 'Jahr', 'class=center solidright solidleft' );
      open_list_head( 'hauptkonten', 'Hauptkonten', 'class=center solidright' );
      open_list_head( 'unterkonten', 'Unterkonten', 'class=center solidright' );
      open_list_head( 'buchungen', 'Buchungen', 'class=center solidright' );
      open_list_head( 'posten', 'Posten', 'class=center solidright' );
      open_list_head( 'ergebnis', 'Jahresergebnis', 'class=center solidright' );
      open_list_head( 'bilanzsumme', 'Bilanzsumme', 'class=center solidright' );
      open_list_head( 'status', 'Status', 'class=center solidright' );
      // open_th( 'center solidright', '', 'Aktionen' );
    foreach( $geschaeftsjahre as $g ) {
      if( $g['nr'] < $limits['limit_from'] )
        continue;
      if( $g['nr'] > $limits['limit_to'] )
        break;
      $j = $g['geschaeftsjahr'];
      open_tr();
        open_list_cell( 'gj', $j, 'class=top' );
        open_list_cell( 'hauptkonten', $g['hauptkonten_count'] );
        open_list_cell( 'unterkonten', inlink( 'unterkontenliste', array( 'geschaeftsjahr' => $j, 'text' => $g['unterkonten_count'] ) ) );

        $buchungen_count = count( sql_buchungen( array( 'geschaeftsjahr' => $j ) ) );
        open_list_cell( 'buchungen', inlink( 'journal', array( 'geschaeftsjahr' => $j, 'text' => $buchungen_count ) ) );

        $posten_count = count( sql_posten( array( 'geschaeftsjahr' => $j ) ) );
        open_list_cell( 'posten', inlink( 'journal', array( 'geschaeftsjahr' => $j, 'text' => $posten_count ) ) );

        $saldoE = sql_unterkonten_saldo( array( 'seite' => 'P', 'kontenkreis' => 'E', 'geschaeftsjahr' => $j ) )
                - sql_unterkonten_saldo( array( 'seite' => 'A', 'kontenkreis' => 'E', 'geschaeftsjahr' => $j ) );
        open_list_cell( 'ergebnis', inlink( 'erfolgskonten', array( 'geschaeftsjahr' => $j, 'text' => saldo_view( 'P', $saldoE ) ) ), 'class=number top' );

        $saldoP = sql_unterkonten_saldo( array( 'seite' => 'P', 'kontenkreis' => 'B', 'geschaeftsjahr' => $j ) )
                /* - sql_unterkonten_saldo( array( 'seite' => 'A', 'kontenkreis' => 'B', 'geschaeftsjahr' => $j ) ); */
                + $saldoE;
        open_list_cell( 'bilanzsumme', inlink( 'bestandskonten', array( 'geschaeftsjahr' => $j, 'text' => saldo_view( 'P', $saldoP ) ) ), 'class=number top' );
        open_list_cell( 'status', $j > $geschaeftsjahr_abgeschlossen ? 'offen' : 'abgeschlossen' );
        // open_td( '', '', '' );  // aktionen
    }
  close_table();
}


// darlehen
//

function darlehenlist_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, 'dl', array(
    'nr' => 't', 'id' => 's=darlehen_id,t=0'
  , 'kreditor' => 't,s=people_cn'
  , 'cn' => 't,s'
  , 'darlehenkonto' => 't,s=darlehen_unterkonten_cn', 'zinskonto' => 't,s=zins_unterkonten_cn'
  , 'gj_darlehen' => 't,s=geschaeftsjahr_darlehen', 'gj_zinslauf_start' => 't,s=geschaeftsjahr_zinslauf_start'
  , 'gj_tilgung_start' => 't,s=geschaeftsjahr_tilgung_start', 'gj_tilgung_ende' => 't,s=geschaeftsjahr_tilgung_ende'
  , 'zugesagt' => 't,s=betrag_zugesagt', 'abgerufen' => 't,s=betrag_abgerufen'
  , 'zinssatz' => 't,s=zins_prozent', 'aktionen' => 't'
  ) );

  if( ! ( $darlehen = sql_darlehen( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', 'Keine Darlehen vorhanden' );
    return;
  }
  $count = count( $darlehen );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list hfill oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'Kreditor' );
      open_list_head( 'cn' );
      open_list_head( 'Darlehenkonto' );
      open_list_head( 'Zinskonto' );
      open_list_head( 'gj_darlehen', 'Darlehen jahr' );
      open_list_head( 'gj_zinslauf_start', 'Zinslauf ab' );
      open_list_head( 'gj_tilgung_start', 'Tilgung ab' );
      open_list_head( 'gj_tilgung_ende', 'Tilgung bis' );
      open_list_head( 'zugesagt' );
      open_list_head( 'abgerufen' );
      open_list_head( 'Zinssatz' );
      open_list_head( 'Aktionen' );
    foreach( $darlehen as $d ) {
      if( $d['nr'] < $limits['limit_from'] )
        continue;
      if( $d['nr'] > $limits['limit_to'] )
        break;
      $id = $d['darlehen_id'];
      open_tr();
        open_list_cell( 'nr', $d['nr'], 'class=number' );
        open_list_cell( 'id', $id, 'class=number' );
        open_list_cell( 'kreditor', inlink( 'person', array( 'class' => 'href', 'people_id' => $d['people_id'], 'text' => $d['people_cn'] ) ) );
        open_list_cell( 'cn', $d['cn'] );
        open_list_cell( 'darlehenkonto' );
          if( $d['darlehen_unterkonten_id'] )
            echo inlink( 'unterkonto', array( 'class' => 'href', 'unterkonten_id' => $d['darlehen_unterkonten_id'], 'text' => $d['darlehen_unterkonten_cn'] ) );
          else
            echo ' - ';
        open_list_cell( 'zinskonto' );
          if( $d['zins_unterkonten_id'] )
            echo inlink( 'unterkonto', array( 'class' => 'href', 'unterkonten_id' => $d['zins_unterkonten_id'], 'text' => $d['zins_unterkonten_cn'] ) );
          else
            echo ' - ';
        open_list_cell( 'gj_darlehen', $d['geschaeftsjahr_darlehen'], 'class=number' );
        open_list_cell( 'gj_zinslauf_start', $d['geschaeftsjahr_zinslauf_start'], 'class=number' );
        open_list_cell( 'gj_tilgung_start', $d['geschaeftsjahr_tilgung_start'], 'class=number' );
        open_list_cell( 'gj_tilgung_ende', $d['geschaeftsjahr_tilgung_ende'], 'class=number' );
        open_list_cell( 'zugesagt', price_view( $d['betrag_zugesagt'] ), 'class=number' );
        open_list_cell( 'abgerufen', price_view( $d['betrag_abgerufen'] ), 'class=number' );
        open_list_cell( 'zinssatz', price_view( $d['zins_prozent'] ), 'class=number' );
        open_list_cell( 'aktionen' );
          echo inlink( 'darlehen', "class=edit,text=,darlehen_id=$id" );
          echo inlink( '!submit', "class=drop,confirm=wirklich loeschen?,action=deleteDarlehen,message=$id" );
    }
  close_table();
}

function zahlungsplanlist_view( $filters = array(), $opts = array() ) {

  $darlehen_id = adefault( $filters, 'darlehen_id', false );
  if( ! isnumeric( $darlehen_id ) )
    $darlehen_id = false;

  $opts = parameters_explode( $opts );
  $actions = adefault( $opts, 'actions', true );
  if( $actions === true ) {
    $actions = 'delete';
  }
  $actions = parameters_explode( $actions );
  $action_delete = adefault( $actions, 'delete', false );

  $opts = handle_list_options( $opts, 'zp', array(
    'nr' => 't', 'id' => 's=zahlungsplan_id,t=0'
  , 'darlehen' => 's,t='.( $darlehen_id ? '0' : 1 )
  , 'kreditor' => 's=people_cn,t='.( $darlehen_id ? '0' : 1 )
  , 'cn' => 't,s'
  , 'valuta' => array( 't', 's' => 'CONCAT( geschaeftsjahr, 1000 + zahlungsplan.valuta )' )
  , 'konto' => 't,s=unterkonten_cn'
  , 'soll' => array( 's' => 'art DESC, betrag' )
  , 'haben' => array( 's' => 'art, betrag DESC' )
  , 'zins' => 't,s'
  , 'buchung' => 't,s=(posten_id!=0)'

  , 'aktionen' => 't=0'
  ) );

  if( ! ( $zp = sql_zahlungsplan( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', 'Kein Zahlungsplan vorhanden' );
    return;
  }
  $count = count( $zp );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $saldoS = 0.0;
  $saldoH = 0.0;
  $saldo_posten_count = 0;

  $opts['class'] = 'list hfill oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr();
      open_list_head( 'Nr' );
      open_list_head( 'Id' );
      open_list_head( 'Darlehen' );
      open_list_head( 'Kreditor' );
      open_list_head( 'Valuta' );
      open_list_head( 'Zins' );
      open_list_head( 'Kommentar' );
      open_list_head( 'Konto' );
      $cols_before_soll = current_table_col_number();
      open_list_head( 'Soll' );
      open_list_head( 'Haben' );
      open_list_head( 'Buchung' );
      open_list_head( 'Aktionen' );

    foreach( $zp as $p ) {

      if( ( $p['nr'] == $limits['limit_from'] ) ) {
        open_tr( 'sum' );
          open_td( "colspan=$cols_before_soll" );
          echo "Anfangssaldo" . ( $saldo_posten_count ? " ($saldo_posten_count nicht gezeigte Posten)" : '' ) .':';
          open_list_cell( 'soll', price_view( $saldoS ), 'class=number' );
          open_list_cell( 'haben', price_view( $saldoH ), 'class=number' );
          open_list_cell( 'buchung', '', ' ' );
          open_list_cell( 'aktionen', '', ' ' );
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
        $id = $p['zahlungsplan_id'];
        $jahr = $p['geschaeftsjahr'];
        $art = $p['art'];
        $uk_id = sql_get_folge_unterkonten_id( $p['unterkonten_id'], $jahr );
        open_tr();
          open_list_cell( 'nr', $p['nr'] );
          open_list_cell( 'id', $id );
          open_list_cell( 'darlehen', inlink( 'darlehen', array(
            'class' => 'edit'
          , 'darlehen_id' => $p['darlehen_id']
          , 'text' => $p['cn']
          ) ) );
          open_list_cell( 'kreditor', inlink( 'person', array( 'class' => 'href', 'people_id' => $p['people_id'], 'text' => $p['people_cn'] ) ) );
          open_list_cell( 'valuta', $jahr .' / '. monthday_view( $p['valuta'] ), 'class=oneline' );
          open_list_cell( 'zins', $p['zins'] ? 'Zins' : '-' );
          open_list_cell( 'kommentar', $p['kommentar'] );
          open_list_cell( 'konto', $uk_id
              ? inlink( 'unterkonto', array( 'class' => 'href', 'unterkonten_id' => $uk_id, 'text' => $p['unterkonten_cn'] ) )
              : '-'
          );
          open_list_cell( 'soll', ( $p['art'] === 'S' ? price_view( $p['betrag'] ) : ' ' ), 'class=number' );
          open_list_cell( 'haben', ( $p['art'] === 'H' ? price_view( $p['betrag'] ) : ' ' ), 'class=number' );
          open_list_cell( 'buchung', $p['buchungen_id']
            ? inlink( 'buchung', "buchungen_id={$p['buchungen_id']}" )
            : '-'
          );
          open_list_cell( 'aktionen' );
            echo inlink( 'zahlungsplan', "zahlungsplan_id=$id,class=edit,text=" );
            if( $uk_id && ! $p['buchungen_id'] ) {
              $buchungssatz = array( 'action' => 'init'
              , 'geschaeftsjahr' => $p['geschaeftsjahr'], 'valuta' => $p['valuta']
              , 'vorfall' => "{$p['people_cn']} / {$p['kommentar']}"
              , 'nS' => 1, 'nH' => 1
              , "pS0_betrag" => $p['betrag'] , "pH0_betrag" => $p['betrag']
              , "p{$art}0_unterkonten_id" => $uk_id
              );
              if( $art === 'H' && $p['zins'] ) {
                $buchungssatz['pS0_kontenkreis'] = 'E';
                $buchungssatz['pS0_seite'] = 'A';
              }
              // debug( $buchungssatz, 'buchungssatz' );
              echo action_button_view( 'script=buchung,text=buchen...', $buchungssatz );
            }
            if( $action_delete ) {
              echo inlink( '!submit', "class=drop,confirm=wirklich loeschen?,action=deleteZahlungsplan,message=$id" );
            }

            // echo inlink( 'darlehen', "class=edit,text=,darlehen_id=$id" );
            // echo inlink( '!submit', "class=drop,confirm=wirklich loeschen?,action=deleteDarlehen,message=$darlehen_id" );
      }

      if( $p['nr'] == $limits['limit_to'] ) {
        if( $limits['limit_to'] + 1 < $count ) {
          open_tr( 'sum' );
            open_td( "colspan=$cols_before_soll", 'Zwischensaldo:' );
            open_list_cell( 'soll', price_view( $saldoS ), 'class=number' );
            open_list_cell( 'haben', price_view( $saldoH ), 'class=number' );
            open_list_cell( 'buchung', '', ' ' );
            open_list_cell( 'aktionen', '', ' ' );
        }
        $saldo_posten_count = 0;
      }
    }
    open_tr( 'sum' );
      open_td( "colspan=$cols_before_soll" );
      echo "Saldo gesamt" . ( $saldo_posten_count ? " (mit $saldo_posten_count nicht gezeigen Posten)" : '' ) .':';
      open_list_cell( 'soll', price_view( $saldoS ), 'class=number' );
      open_list_cell( 'haben', price_view( $saldoH ), 'class=number' );
      open_list_cell( 'buchung', '', ' ' );
      open_list_cell( 'aktionen', '', ' ' );
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
//     zahlungsplanlist_view( );
//   close_fieldset();
// }
// 



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

$mainmenu[] = array( 'script' => "hauptkontenliste",
     "title" => "Hauptkonten",
     "text" => "Hauptkonten" );

$mainmenu[] = array( 'script' => "unterkontenliste",
     "title" => "Unterkonten",
     "text" => "Unterkonten" );

$mainmenu[] = array( 'script' => "journal",
     "title" => "Journal",
     "text" => "Journal" );

$mainmenu[] = array( 'script' => "posten",
     "title" => "Posten",
     "text" => "Posten" );

$mainmenu[] = array( 'script' => "geschaeftsjahre",
     "title" => 'Gesch'.H_AMP.'auml;ftsjahre',
     "text" => 'Gesch'.H_AMP.'auml;ftsjahre' );

$mainmenu[] = array( 'script' => "personen",
     "title" => "Personen",
     "text" => "Personen" );

$mainmenu[] = array( 'script' => "darlehenliste",
     "title" => "Darlehen",
     "text" => "Darlehen" );

$mainmenu[] = array( 'script' => "zahlungsplanliste",
     "title" => "Zahlungsplan",
     "text" => "Zahlungsplan" );

$mainmenu[] = array( 'script' => "things",
     "title" => 'Gegenst'.H_AMP.'auml;nde',
     "text" => 'Gegenst'.H_AMP.'auml;nde' );

$mainmenu[] = array( 'script' => "ka",
     "title" => "ka",
     "text" => "ka" );

$mainmenu[] = array( 'script' => "logbook",
     "title" => "Logbuch",
     "text" => "Logbuch" );

$mainmenu[] = array( 'script' => "config",
     "title" => "Konfiguration",
     "text" => "Konfiguration" );



function mainmenu_fullscreen() {
  global $mainmenu;
  foreach( $mainmenu as $h ) {
    open_tr();
      open_td( 'colspan=2', inlink( $h['script'], array(
        'text' => $h['text'], 'title' => $h['title'] , 'class' => 'bigbutton'
      ) ) );
  }
}

function mainmenu_header() {
  global $mainmenu;
  foreach( $mainmenu as $h ) {
    open_li( '', inlink( $h['script'], array(
      'text' => $h['text'], 'title' => $h['title'] , 'class' => 'href'
    ) ) );
  }
}



?>
