<?php // sus/views.php

require_once('code/views.php');


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
  global $global_format;
  $red = '';
  if( $saldo < 0 ) {
    $red = 'rednumber';
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
  $s = sprintf( '%.02lf', $saldo )." $s";
  return ( ( $global_format === 'html' ) ? html_tag( 'span', "price number $red", $s ) : $s );
}

function kontoattribute_view( $k, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $a = array();
  if( adefault( $k, 'flag_personenkonto' ) ) {
    $t = 'Personenkonto';
    if( ( $people_id = adefault( $k, 'people_id' ) ) ) {
      $t = inlink( 'person', array( 'people_id' => $people_id, 'class' => 'href', 'text' => $t ) );
    }
    $a[] = $t;
  }
  if( adefault( $k, 'flag_sachkonto' ) ) {
    $a[] = 'Sachkonto';
  }
  if( adefault( $k, 'flag_bankkonto' ) ) {
    $a[] = 'Bankkonto';
  }
  if( adefault( $k, 'flag_steuerkonto' ) ) {
    $a[] = 'Steuerkonto';
  }
  if( adefault( $k, 'flag_zinskonto' ) ) {
    $a[] = 'Zinskonto';
  }
  if( ! adefault( $k, 'flag_steuerbilanzrelevant', 1 ) ) {
    $a[] = 'nicht steuerbilanzrelevant';
  }
  if( ( ! adefault( $k, 'flag_unterkonto_offen', 1 ) ) || ( ! adefault( $k, 'flag_hauptkonto_offen', 1 ) ) ) {
    $a[] = 'geschlossen';
  }
  if( ( $t = adefault( $k, 'vortragskonto' ) ) ) {
    if( $t == '1' ) {
      $a[] = 'Vortragskonto';
    } else {
      $a[] = "Vortragskonto $t";
    }
  }
  return implode( ', ', $a );
}


//////////////////
// table views:
//



function kontoklassen_view( $rows ) {
  global $aUML;

  $list_options = handle_list_options( true, 'kontoklassen', array(
    'id' => 't'
  , 'cn' => ''
  , 'kontenkreis' => 't'
  , 'seite' => 't'
  , 'flag_bankkonto' => 't'
  , 'flag_personenkonto' => 't'
  , 'flag_sachkonto' => 't'
  , 'flag_steuerkonto' => 't'
  , 'flag_steuerbilanzrelevant' => 't'
  , 'vortragskonto' => 't'
  , 'geschaeftsbereich' => 't'
  ) );

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'id' );
      open_list_cell( 'cn', 'Name' );
      open_list_cell( 'Kontenkreis' );
      open_list_cell( 'Seite' );
      open_list_cell( 'geschaeftsbereich', "Gesch{$aUML}ftsbereich" );
      open_list_cell( 'flag_personenkonto', 'Personenkonto' );
      open_list_cell( 'flag_sachkonto', 'Sachkonto' );
      open_list_cell( 'flag_bankkonto', 'Bankkonto' );
      open_list_cell( 'flag_steuerkonto', 'Bankkonto' );
      open_list_cell( 'flag_steuerbilanzrelevant', 'steuerbilanzrelevant' );
      open_list_cell( 'vortragskonto', 'Vortragskonto' );
    foreach( $rows as $r ) {
      open_list_row();
        open_list_cell( 'id', $r['kontoklassen_id'], 'class=number' );
        open_list_cell( 'cn', $r['cn'] );
        open_list_cell( 'kontenkreis', $r['kontenkreis'] );
        open_list_cell( 'seite', $r['seite'] );
        open_list_cell( 'geschaeftsbereich', $r['geschaeftsbereich'] );
        open_list_cell( 'flag_personenkonto', $r['flag_personenkonto'] );
        open_list_cell( 'flag_sachkonto', $r['flag_sachkonto'] );
        open_list_cell( 'flag_bankkonto', $r['flag_bankkonto'] );
        open_list_cell( 'flag_steuerkonto', $r['flag_steuerkonto'] );
        open_list_cell( 'flag_steuerbilanzrelevant', $r['flag_steuerbilanzrelevant'] );
        open_list_cell( 'vortragskonto', $r['vortragskonto'] );
    }
  close_list();
}




// people:
//

function peoplelist_view( $filters = array(), $opts = array() ) {
  global $script;

  $list_options = handle_list_options( $opts, 'people', array(
      'id' => 's=people_id,t=0'
    , 'cn' => 's,t', 'gn' => 's,t', 'sn' => 's,t'
    , 'phone' => 's=telephonenumber,t', 'mail' => 's,t'
    , 'dusie' => 's,t', 'genus' => 's,t', 'jperson' => 's,t', 'uid' => 's,t'
    , 'bank' => 's,t=0' , 'konto' => 's,t=0' , 'blz' => 's,t=0'
    , 'iban' => 's,t=0' 
    , 'status' => 's=status_person,t'
    , 'personenkonten' => 't'
  ) );

  if( ! ( $people = sql_people( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', 'Keine Personen vorhanden' );
    return;
  }
  $count = count( $people );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  // $selected_people_id = adefault( $GLOBALS, $opts['select'], 0 );
  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      open_list_cell( 'id' );
      open_list_cell( 'jperson', 'juristisch' );
      open_list_cell( 'genus', 'Genus' );
      open_list_cell( 'dusie', 'Anrede' );
      open_list_cell( 'cn' );
      open_list_cell( 'gn', 'Vorname' );
      open_list_cell( 'sn', 'Nachname' );
      open_list_cell( 'status', 'Status' );
      open_list_cell( 'phone', 'Telefon' );
      open_list_cell( 'mail', 'Email' );
      open_list_cell( 'uid' );
      open_list_cell( 'bank', 'Bank' );
      open_list_cell( 'blz', 'BLZ' );
      open_list_cell( 'konto', 'Konto-Nr' );
      open_list_cell( 'iban', 'IBAN' );
      open_list_cell( 'Personenkonten' );

    foreach( $people as $person ) {
      if( $person['nr'] < $limits['limit_from'] )
        continue;
      if( $person['nr'] > $limits['limit_to'] )
        break;
      $people_id = $person['people_id'];
//       if( $opts['select'] ) {
//         open_tr( 'selectable' );
//           open_td(
//             'left ' .( $people_id == $selected_people_id ? 'selected' : 'unselected' )
//  ///// , "onclick=\"".inlink( '', array( 'context' => 'js', $opts['select'] => $people_id ) ) ."\";"
//           , $person['nr']
//           );
//       } else {
//         open_tr();
//           open_td( 'right', $person['nr'] );
//       }
      open_list_row();
        open_list_cell( 'nr', $person['nr'] );
        open_list_cell( 'id', $people_id );
        open_list_cell( 'jperson', $person['jperson'] );
        open_list_cell( 'genus', $person['genus'] );
        open_list_cell( 'dusie', $person['dusie'] );
        open_list_cell( 'cn', inlink( 'person', array( 'class' => 'href', 'people_id' => $people_id, 'text' => $person['cn'] ) ) );
        open_list_cell( 'gn', $person['gn'] );
        open_list_cell( 'sn', $person['sn'] );
        open_list_cell( 'status', adefault( $GLOBALS['choices_status_person'], $person['status_person'], 'unbekannt' ) );
        open_list_cell( 'phone', $person['telephonenumber'] );
        open_list_cell( 'mail', $person['mail'] );
        open_list_cell( 'uid', $person['uid'] );
        open_list_cell( 'bank', $person['bank_cn'] );
        open_list_cell( 'blz', $person['bank_blz'] );
        open_list_cell( 'konto', $person['bank_kontonr'] );
        open_list_cell( 'iban', $person['bank_iban'] );
        $t = '';
        $unterkonten = sql_unterkonten( array( 'flag_personenkonto' => 1, 'people_id' => $people_id ) );
        foreach( $unterkonten as $uk ) {
          $t .= inlink( 'unterkonto', array(
            'class' => 'href alink inline_block', 'text' => $uk['cn'], 'unterkonten_id' => $uk['unterkonten_id']
          ) );
        }
        open_list_cell( 'personenkonten', $t );
    }
  close_list();
}


// // things:
// //
// 
// function thingslist_view( $filters = array(), $opts = array() ) {
//   global $geschaeftsjahr_thread;
// 
//   $list_options = handle_list_options( $opts, 'things', array(
//     'id' => 's=things_id,t', 'cn' => 's,t', 'aj' => 's=anschaffungsjahr,t'
//     , 'wert' => 's,t', 'Sachkonten' => 't'
//   ) );
//   if( ! ( $things = sql_things( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
//     open_div( '', 'Keine Gegenstaende vorhanden' );
//     return;
//   }
//   $count = count( $things );
//   $limits = handle_list_limits( $list_options, $count );
//   $list_options['limits'] = & $limits;
// 
//   open_list( $list_options );
//     open_list_row('header');
//       open_list_cell( 'nr' );
//       open_list_cell( 'id' );
//       open_list_cell( 'cn', 'Name' );
//       open_list_cell( 'aj', 'Anschaffungsjahr' );
//       open_list_cell( 'wert', 'Restwert' );
//       open_list_cell( 'Sachkonten' );
//     foreach( $things as $th ) {
//       $id = $th['things_id'];
//       open_list_row();
//         open_list_cell( 'nr', $th['nr'], 'class=right' );
//         open_list_cell( 'id', $th['things_id'], 'class=right' );
//         open_list_cell( 'cn', $th['cn'], 'class=right' );
//         open_list_cell( 'aj',  $th['anschaffungsjahr'], 'class=center' );
//         open_list_cell( 'wert', price_view( $th['wert'] ), 'class=number' );
//         $t = '';
//         $konten = sql_unterkonten( array( 'sachkonto' => 1, 'things_id' => $id ) );
//         if( $konten ) {
//           $t .= inlink( 'unterkonten', array(
//             'class' => 'cash', 'text' => '', 'title' => 'Konten', 'things_id' => $id
//           ) );
//         }
//         $t = '';
//         $unterkonten = sql_unterkonten( array( 'sachkonto' => 1, 'things_id' => $id ) );
//         foreach( $unterkonten as $uk ) {
//           $t .= inlink( 'unterkonto', array(
//             'class' => 'href alink inline_block', 'text' => $uk['cn'], 'unterkonten_id' => $uk['unterkonten_id']
//           ) );
//         }
//         open_list_cell( 'Sachkonten', $t );
//     }
//   close_list();
// }
// 
// 
// function thing_view( $things_id, $opts = array() ) {
//   $filters = array( 'things_id' => $things_id );
//   // if( $stichtag )
//   //   $filters['posten.valuta'] = " <= $stichtag";
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
// 

function hauptkontenlist_view( $filters = array(), $opts = array() ) {
  global $geschaeftsjahr_thread;

  $opts = parameters_explode( $opts );
  $geschaeftsjahr = adefault( $opts, 'geschaeftsjahr', $geschaeftsjahr_thread );
  unset( $opts['geschaeftsjahr'] );

  $list_options = handle_list_options( $opts, 'hk', array(
      'id' => 's=hauptkonten_id,t=0'
    , 'kontenkreis' => 's,t'
    , 'seite' => 's,t'
    , 'rubrik' => 's,t'
    , 'titel' => 's'
    , 'gb' => array( 't', 's' => 'CONCAT( kontenkreis, vortragskonto, geschaeftsbereich )' )
    , 'klasse' => 's=kontoklassen.kontoklassen_id,t'
    , 'hgb' => 's=hgb_klasse,t=0'
    , 'attribute' => array( 's' => 'CONCAT( bankkonto, personenkonto, sachkonto, vortragskonto )', 't' )
    , 'saldo' => 's,t'
    , 'saldo_geplant' => 's,t'
    , 'saldo_alle' => 's,t'
  ) );

  if( ! ( $hauptkonten = sql_hauptkonten( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', 'Keine Hauptkonten vorhanden' );
    return;
  }
  $count = count( $hauptkonten );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  $toggle_saldo = ( $list_options['cols']['saldo']['toggle'] == '1' );
  $toggle_saldo_geplant = ( $list_options['cols']['saldo_geplant']['toggle'] == '1' );
  $toggle_saldo_alle = ( $list_options['cols']['saldo_alle']['toggle'] == '1' );
  $summieren = $toggle_saldo || $toggle_saldo_geplant || $toggle_saldo_alle;
  $seite = $hauptkonten[0]['seite'];
  $kontenkreis = $hauptkonten[ 0 ]['kontenkreis'];
  foreach( $hauptkonten as $hk ) {
    if( $hk['kontenkreis'].$hk['seite'] !== "$kontenkreis$seite" ) {
      $summieren = false;
    }
  }

  $saldo_summe = $saldo_geplant_summe = $saldo_alle_summe = 0;
  $saldo_total_count = 0;
  $saldo_listed_count = 0;

//  $selected_hauptkonten_id = adefault( $GLOBALS, $opts['select'], 0 );
  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      open_list_cell( 'id' );
      open_list_cell( 'kontenkreis', 'Kreis' );
      open_list_cell( 'Seite' );
      open_list_cell( 'Klasse' );
      open_list_cell( 'hgb', 'HGB-Klasse' );
      open_list_cell( 'gb', 'Gesch'.H_AMP.'auml;ftsbereich' );
      open_list_cell( 'Rubrik' );
      open_list_cell( 'Titel' );
      open_list_cell( 'Attribute' );
      $cols_before_saldo = current_list_col_number();
      open_list_cell( 'saldo', "Saldo $geschaeftsjahr" );
      open_list_cell( 'saldo_geplant', "geplant $geschaeftsjahr" );
      open_list_cell( 'saldo_alle', "gesamt $geschaeftsjahr" );

    foreach( $hauptkonten as $hk ) {
      $hauptkonten_id = $hk['hauptkonten_id'];
      if( $toggle_saldo ) {
        $saldo = sql_unterkonten_saldo( "hauptkonten_id=$hauptkonten_id,geschaeftsjahr=$geschaeftsjahr,flag_ausgefuehrt=1" );
        $saldo_summe += $saldo;
      }
      if( $toggle_saldo_geplant ) {
        $saldo_geplant = sql_unterkonten_saldo( "hauptkonten_id=$hauptkonten_id,geschaeftsjahr=$geschaeftsjahr,flag_ausgefuehrt=0" );
        $saldo_geplant_summe += $saldo_geplant;
      }
      if( $toggle_saldo_alle ) {
        $saldo_alle = sql_unterkonten_saldo( "hauptkonten_id=$hauptkonten_id,geschaeftsjahr=$geschaeftsjahr" );
        $saldo_alle_summe += $saldo_alle;
      }
      $saldo_total_count++;
      if( $hk['nr'] < $limits['limit_from'] ) {
        continue;
      }
      if( $hk['nr'] > $limits['limit_to'] ) {
        continue;
      }
      $saldo_listed_count++;
//           , array( 'class' => 'right'
//                  , 'onclick' => inlink( '!submit', array( 'context' => 'js', $opts['select'] => $hauptkonten_id ) ) )
//           );
//       } else {
      open_list_row();
          open_list_cell( 'nr', inlink( 'hauptkonto', array( 'text' => $hk['nr'], 'hauptkonten_id' => $hauptkonten_id ) ), 'class=number' );
//      }
        open_list_cell( 'id', any_link( 'hauptkonten', $hauptkonten_id, "text=$hauptkonten_id" ), 'class=number' );
//        open_list_cell( 'geschaeftsjahr', $hk['geschaeftsjahr'], 'class=center' );
        switch( $hk['kontenkreis'] ) {
          case 'E':
            $t = inlink( 'erfolgskonten', 'text=E,class=href' );
            break;
          case 'B':
            $t = inlink( 'bestandskonten', 'text=B,class=href' );
            break;
        }
        open_list_cell( 'kontenkreis', $t, 'class=center' );
        open_list_cell( 'seite', $hk['seite'], 'class=center' );
        open_list_cell( 'klasse', inlink( 'hauptkontenliste', array(
          'class' => 'href', 'text' => $hk['kontoklassen_cn']
        , 'kontoklassen_id' => $hk['kontoklassen_id']
        ) ) );
        if( $hk['hgb_klasse'] ) {
          $t = inlink( 'hauptkontenliste', array( 'class' => 'href', 'text' => $hk['hgb_klasse'] , 'hgb_klasse' => $hk['hgb_klasse'] ) );
        } else {
          $t = "(keine)";
        }
        open_list_cell( 'hgb', $t );
        if( $hk['kontenkreis'] == 'E' ) {
          $t = inlink( 'hauptkonten', array(
            'class' => 'href', 'text' => $hk['geschaeftsbereich'] , 'kontenkreis' => 'E'
          , 'geschaeftsbereiche_id' => value2uid( $hk['geschaeftsbereich'] )
          ) );
        } else {
          $t = $hk['vortragskonto'] ? "Vortrag ".$hk['vortragskonto'] : '-';
        }
        open_list_cell( 'gb', $t );
        open_list_cell( 'rubrik', $hk['rubrik'] );
        open_list_cell( 'titel', inlink( 'hauptkonto', array(
          'class' => 'href', 'text' => $hk['titel'] , 'hauptkonten_id' => $hk['hauptkonten_id']
        ) ) );
        open_list_cell( 'attribute', kontoattribute_view( $hk ) );
        open_list_cell( 'saldo', saldo_view( $hk['seite'], $saldo ), 'class=number' );
        open_list_cell( 'saldo_geplant', saldo_view( $hk['seite'], $saldo_geplant ), 'class=number' );
        open_list_cell( 'saldo_alle', saldo_view( $hk['seite'], $saldo_alle ), 'class=number' );
    }
    if( $summieren ) {
      open_list_row( 'sum' );
        $diff = $saldo_total_count - $saldo_listed_count;
        open_list_cell( '', "Saldo gesamt" . ( $diff ? " ($diff nicht gezeigte Konten)" : '' ) .':', "colspan=$cols_before_saldo" );
        open_list_cell( 'saldo', saldo_view( $seite, $saldo_summe ), 'number' );
        open_list_cell( 'saldo_geplant', saldo_view( $seite, $saldo_geplant_summe ), 'number' );
        open_list_cell( 'saldo_alle', saldo_view( $seite, $saldo_alle_summe ), 'number' );
    }

  close_list();
}


// unterkonten
//
function unterkontenlist_view( $filters = array(), $opts = array() ) {
  global $table_level, $geschaeftsjahr_thread, $ust_satz_1_prozent, $ust_satz_2_prozent;

  $opts = parameters_explode( $opts );
  $saldo_filters = adefault( $opts, 'saldo_filters', "geschaeftsjahr=$geschaeftsjahr_thread" );
  unset( $opts['saldo_filters'] );

  $list_options = handle_list_options( $opts, 'uk', array(
      'id' => 's=unterkonten_id,t=0'
    , 'kontenkreis' => 's,t', 'seite' => 's,t', 'rubrik' => 's,t'
    , 'titel' => 's,t'
    , 'cn' => 's'
    , 'skrnummer' => 's,t=0'
    , 'gb' => array( 't', 's' => 'CONCAT( kontenkreis, vortragskonto, geschaeftsbereich )' )
    , 'klasse' => 's=kontoklassen.kontoklassen_id,t'
    , 'hgb' => 's=hgb_klasse,t=0'
    , 'attribute' => array( 's' => 'CONCAT( bankkonto, personenkonto, sachkonto, vortragskonto, zinskonto )', 't' )
    , 'ust' => array( 's' => '( ust_satz + ust_faktor_prozent / 100.0 )', 't' )
    , 'saldo' => 's,t'
    , 'saldo_geplant' => 's,t'
    , 'saldo_alle' => 's,t'
  ) );

  $opts = array( 'orderby' => $list_options['orderby_sql'], 'more_selects' => array() );
  if( ( $toggle_saldo = ( $list_options['cols']['saldo']['toggle'] == '1' ) ) ) {
    $opts['more_selects'][] = 'saldo';
  }
  if( ( $toggle_saldo_geplant = ( $list_options['cols']['saldo_geplant']['toggle'] == '1' ) ) ) {
    $opts['more_selects'][] = 'saldo_geplant';
  }
  if( ( $toggle_saldo_alle = ( $list_options['cols']['saldo_alle']['toggle'] == '1' ) ) ) {
    $opts['more_selects'][] = 'saldo_alle';
  }
  If( $toggle_saldo || $toggle_saldo_geplant || $toggle_saldo_alle ) {
    $opts['more_joins'] = 'posten,buchungen';
  }

  if( ! ( $unterkonten = sql_unterkonten( $filters, $opts ) ) ) {
    open_div( '', 'Keine Unterkonten vorhanden' );
    return;
  }
  $count = count( $unterkonten );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  $summieren = $toggle_saldo || $toggle_saldo_geplant || $toggle_saldo_alle;
  $seite = $unterkonten[0]['seite'];
  $kontenkreis = $unterkonten[0]['kontenkreis'];
  foreach( $unterkonten as $uk ) {
    if( $uk['kontenkreis'].$uk['seite'] !== "$kontenkreis$seite" ) {
      $summieren = false;
    }
  }

  $saldo_summe = $saldo_geplant_summe = $saldo_alle_summe = 0;
  $saldo = $saldo_geplant = $saldo_alle = 0; // need init to avoid undef variable warnings even if toggled off
  $saldo_total_count = 0;
  $saldo_listed_count = 0;

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      open_list_cell( 'id' );
      open_list_cell( 'kontenkreis', 'Kreis' );
      open_list_cell( 'Seite' );
      open_list_cell( 'Klasse' );
      open_list_cell( 'hgb', 'HGB-Klasse' );
      open_list_cell( 'gb', 'Gesch'.H_AMP.'auml;ftsbereich' );
      open_list_cell( 'Rubrik' );
      open_list_cell( 'titel', 'Hauptkonto' );
      open_list_cell( 'cn', 'Unterkonto' );
      open_list_cell( 'SKRnummer' );
      open_list_cell( 'Attribute' );
      open_list_cell( 'USt' );
      $cols_before_saldo = current_list_col_number();
      open_list_cell( 'saldo', "Saldo" );
      open_list_cell( 'saldo_geplant', "geplant" );
      open_list_cell( 'saldo_alle', "gesamt" );

    foreach( $unterkonten as $uk ) {
      $unterkonten_id = $uk['unterkonten_id'];
       if( $summieren && (  $uk['nr'] == $limits['limit_from'] ) && $saldo_total_count ) {
         open_list_row( 'sum' );
           open_list_cell( "colspan=$cols_before_saldo", "Zwischensumme $saldo_total_count nicht gezeigter Konten:"  );
           open_list_cell( 'saldo', saldo_view( $seite, $saldo ), 'number' );
           open_list_cell( 'saldo_geplant', saldo_view( $seite, $saldo_geplant ), 'number' );
           open_list_cell( 'saldo_alle', saldo_view( $seite, $saldo_alle ), 'number' );
      }
      if( $toggle_saldo ) {
        $saldo = sql_unterkonten_saldo( array( '&&', $saldo_filters, 'unterkonten_id' => $unterkonten_id, 'flag_ausgefuehrt' => 1 ) );
        $saldo_summe += $saldo;
      }
      if( $toggle_saldo_geplant ) {
        $saldo_geplant = sql_unterkonten_saldo( array( '&&', $saldo_filters, 'unterkonten_id' => $unterkonten_id, 'flag_ausgefuehrt' => 0 ) );
        $saldo_geplant_summe += $saldo_geplant;
      }
      if( $toggle_saldo_alle ) {
        $saldo_alle = sql_unterkonten_saldo( array( '&&', $saldo_filters, 'unterkonten_id' => $unterkonten_id ) );
        $saldo_alle_summe += $saldo_alle;
      }
      $saldo_total_count++;
      if( $uk['nr'] < $limits['limit_from'] ) {
        continue;
      }
      if( $uk['nr'] > $limits['limit_to'] ) {
        continue;
      }
      $saldo_listed_count++;
//       if( $opts['select'] ) {
//         open_tr( array(
//           'class' => 'trselectable' . ( ( $GLOBALS[ $opts['select'] ] == $unterkonten_id ) ? ' trselected' : '' )
//         , 'onclick' => inlink( '!submit', array( 'context' => 'js', $opts['select'] => $unterkonten_id ) )
//         ) );
//       } else {
        open_list_row();
//      }
        open_list_cell( 'nr', inlink( 'unterkonto', array( 'text' => $uk['nr'], 'unterkonten_id' => $unterkonten_id ), 'class=number' ) );
        open_list_cell( 'id', any_link( 'unterkonten', $unterkonten_id, "text=$unterkonten_id" ), 'class=number' );
        switch( $uk['kontenkreis'] ) {
          case 'E':
            $t = inlink( 'erfolgskonten', 'text=E,class=href' );
            break;
          case 'B':
            $t = inlink( 'bestandskonten', 'text=B,class=href' );
            break;
        }
        open_list_cell( 'kontenkreis', $t, 'class=center' );
        open_list_cell( 'seite', $uk['seite'], 'class=center' );

        open_list_cell( 'klasse', inlink( 'unterkontenliste', array(
          'class' => 'href', 'text' => $uk['kontoklassen_cn']
        , 'kontoklassen_id' => $uk['kontoklassen_id']
        ) ) );

        if( $uk['hgb_klasse'] ) {
          $t = inlink( 'unterkontenliste', array( 'class' => 'href', 'text' => $uk['hgb_klasse'] , 'hgb_klasse' => $uk['hgb_klasse'] ) );
        } else {
          $t = '(keine)';
        }
        open_list_cell( 'hgb', $t );

        if( $uk['kontenkreis'] == 'E' ) {
          $t = inlink( 'unterkontenliste', array(
            'class' => 'href', 'text' => $uk['geschaeftsbereich'] , 'kontenkreis' => 'E'
          , 'geschaeftsbereiche_id' => value2uid( $uk['geschaeftsbereich'] )
          ) );
        } else {
          $t = ( $uk['vortragskonto'] ? "Vortrag ".$uk['vortragskonto'] : 'n/a' );
        }
        open_list_cell( 'gb', $t );

        open_list_cell( 'rubrik', $uk['rubrik'] );
        open_list_cell( 'titel', inlink( 'hauptkonto', array(
          'class' => 'href', 'text' => $uk['titel'] , 'hauptkonten_id' => $uk['hauptkonten_id']
        ) ) );
        open_list_cell( 'cn', inlink( 'unterkonto', array(
          'unterkonten_id' => $unterkonten_id, 'class' => 'href' , 'text' => $uk['cn']
        ) ) );
        open_list_cell( 'skrnummer', $uk['skrnummer'] );
        open_list_cell( 'attribute', kontoattribute_view( $uk ) );
        switch( $uk['ust_satz'] ) {
          case '0':
            $t = '-';
            break;
          case '1':
            $t = $ust_satz_1_prozent;
            break;
          case '2':
            $t = $ust_satz_2_prozent;
            break;
        }
        if( ( $t != '-' ) && ( $uk['ust_faktor_prozent'] < 99.9 ) && ( $uk['seite'] == 'A' ) ) {
          $t .= sprintf( ' (%.2f%%)', $uk['ust_faktor_prozent'] );
        }
        open_list_cell( 'ust', $t );
        open_list_cell( 'saldo', saldo_view( $uk['seite'], $saldo ), 'class=number' );
        open_list_cell( 'saldo_geplant', saldo_view( $uk['seite'], $saldo_geplant ), 'class=number' );
        open_list_cell( 'saldo_alle', saldo_view( $uk['seite'], $saldo_alle ), 'class=number' );
        if( ( $uk['nr'] == $limits['limit_to'] ) && $summieren && ( $limits['limit_to'] + 1 < $count ) ) {
          open_list_row( 'sum' );
            open_list_cell( '', 'Zwischenssumme:', "colspan=$cols_before_saldo" );
            open_list_cell( 'saldo', saldo_view( $seite, $saldo_summe ), 'class=number' );
            open_list_cell( 'saldo_geplant', saldo_view( $seite, $saldo_geplant_summe ), 'class=number' );
            open_list_cell( 'saldo_alle', saldo_view( $seite, $saldo_alle_summe ), 'class=number' );
        }
    }
    if( $summieren ) {
      open_list_row( 'sum' );
        open_list_cell( '', 'Summe gesamt:', "colspan=$cols_before_saldo" );
        open_list_cell( 'saldo', saldo_view( $seite, $saldo_summe ), 'class=number' );
        open_list_cell( 'saldo_geplant', saldo_view( $seite, $saldo_geplant_summe ), 'class=number' );
        open_list_cell( 'saldo_alle', saldo_view( $seite, $saldo_alle_summe ), 'class=number' );
    }

  close_list();
}

// posten
//
function postenlist_view( $filters = array(), $opts = array() ) {
  global $script, $NBSP, $aUML, $ust_satz_1_prozent, $ust_satz_2_prozent, $global_format;

  $opts = parameters_explode( $opts );
  $saldieren = adefault( $opts, 'saldieren', true );
  $geschaeftsjahr_zeigen = adefault( $opts, 'geschaeftsjahr_zeigen', 1 );
  $authorized = adefault( $opts, 'authorized', 0 );
  $books_read = have_priv( 'books', 'read' );

//   $action_mark = adefault( $actions, 'mark', false );
//   if( $action_mark ) {
//     $action_mark = parameters_explode( $action_mark, array(
//       'default_key' => 'prefix', 'keep' => 'prefix=posten_,values=01,opts='
//     ) );
//   }

  $cols = array(
    'nr' => 't=0'
  , 'id' => 't=0,s=posten_id'
  , 'fqvaluta' => 't,s'
  , 'buchung' => 't=0,s=buchungen.ctime'
  , 'beleg' => 't,s'
  , 'hauptkonto' => 't,s=titel'
  , 'unterkonto' => 't,s=cn'
  , 'srknummer' => 't,s'
  , 'kontenkreis' => 't,s'
  , 'seite' => 't,s'
  , 'skrnummer' => 't=0,s'
  , 'vorfall' => 't=0,s'
  , 'referenz' => 't=0,s'
  , 'soll' => array( 's' => 'art DESC, betrag' )
  , 'haben' => array( 's' => 'art, betrag' )
  , 'ust_satz' => 't=0,s'
  , 'netto' => 't=0'
  , 'ust_betrag' => 't=0'
  , 'vorsteuer_betrag' => 't=0'
  , 'saldo' => 't=1'
  , 'soll_geplant' => array( 't' => 0, 's' => 'art DESC, betrag' )
  , 'haben_geplant' => array( 't' => 0, 's' => 'art, betrag' )
  , 'saldo_geplant' => 't=0'
  , 'aktionen' => 't'
  );
  if( adefault( $filters, 'unterkonten_id', 0 ) > 0 ) {
    $cols['hauptkonto'] = 't=0,s=titel';
    $cols['unterkonto'] = 't=0,s=cn';
    $cols['kontenkreis'] = 't=0,s';
    $cols['seite'] = 't=0,s';
  }
  if( adefault( $filters, 'hauptkonten_id', 0 ) > 0 ) {
    $cols['hauptkonto'] = 't=0,s=cn';
    $cols['kontenkreis'] = 't=0,s';
    $cols['seite'] = 't=0,s';
  }
  // if( adefault( $filters, 'geschaeftsjahr', 0 ) > 0 ) {
  //  $cols['geschaeftsjahr'] = 't=0,s';
  // }
  $opts['orderby'] = adefault( $opts, 'orderby', 'fqvaluta' );

  $list_options = handle_list_options( $opts, 'po', $cols );
  if( ! ( $posten = sql_posten( $filters, array( 'orderby' => $list_options['orderby_sql'], 'authorized' => $authorized ) ) ) ) {
    open_div( '', 'Keine Posten vorhanden' );
    return;
  }
  $count = count( $posten );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  $saldoS = 0.0;
  $saldoH = 0.0;
  $saldoS_geplant = 0.0;
  $saldoH_geplant = 0.0;
  $saldo_total_count = 0;
  $saldo_posten_count = 0;

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      open_list_cell( 'id' );
      open_list_cell( 'fqvaluta', 'Valuta' );
      open_list_cell( 'Buchung' );
      open_list_cell( 'Beleg' );
      open_list_cell( 'Vorfall' );
      open_list_cell( 'Referenz' );
      open_list_cell( 'kontenkreis', 'Kreis' );
      open_list_cell( 'Seite' );
      open_list_cell( 'Hauptkonto' );
      open_list_cell( 'Unterkonto' );
      open_list_cell( 'SKRnummer' );
      open_list_cell( 'ust_satz', 'USt Satz' );
      open_list_cell( 'Netto' );
      open_list_cell( 'ust_betrag', 'USt Betrag' );
      open_list_cell( 'vorsteuer_betrag', 'Vorsteuer Betrag' );
      $cols_before_soll = current_list_col_number();
      open_list_cell( 'Soll' );
      open_list_cell( 'Haben' );
      open_list_cell( 'Saldo' );
      open_list_cell( 'soll_geplant', 'Soll geplant' );
      open_list_cell( 'haben_geplant', 'Haben geplant' );
      open_list_cell( 'saldo_geplant', 'Saldo geplant' );
      open_list_cell( 'Aktionen' );
    foreach( $posten as $p ) {
      $is_vortrag = ( $p['valuta'] == 100 );
      $is_ergebnisverwendung = ( $p['valuta'] == 1299 );
      if( $p['nr'] == $limits['limit_from'] ) {
        open_list_row( 'sum' );
          $t = "Anfangssaldo" . ( $saldo_posten_count ? " ($saldo_posten_count nicht gezeigte Posten)" : '' ) .':';
          open_list_cell( '', $t, "colspan=$cols_before_soll" );

          open_list_cell( 'soll', price_view( $saldoS ), 'number' );
          open_list_cell( 'haben', price_view( $saldoH ), 'number' );
          open_list_cell( 'saldo', ( ( $saldoH > $saldoS ) ?  price_view( $saldoH - $saldoS ) . ' H' : price_view( $saldoS - $saldoH ) . ' S' ), 'class=number oneline' );

          open_list_cell( 'soll_geplant', price_view( $saldoS_geplant ), 'number' );
          open_list_cell( 'haben_geplant', price_view( $saldoH_geplant ), 'number' );
          open_list_cell( 'saldo_geplant', ( ( $saldoH_geplant > $saldoS_geplant ) ?  price_view( $saldoH_geplant - $saldoS_geplant ) . ' H' : price_view( $saldoS_geplant - $saldoH_geplant ) . ' S' ), 'class=number oneline' );

          open_list_cell( 'aktionen', ' ' );
          $saldo_posten_count = 0;
      }
      switch( $p['art'] ) {
        case 'S':
          if( 'flag_ausgefuehrt' ) {
            $saldoS += $p['betrag'];
          } else {
            $saldoS_geplant += $p['betrag'];
          }
          break;
        case 'H':
          if( 'flag_ausgefuehrt' ) {
            $saldoH += $p['betrag'];
          } else {
            $saldoH_geplant += $p['betrag'];
          }
          break;
      }
      $saldo_posten_count++;
      if( ( $p['nr'] >= $limits['limit_from'] ) && ( $p['nr'] <= $limits['limit_to'] ) ) {
        $posten_id = $p['posten_id'];
        $tr_attr['class'] = ( $is_vortrag ? 'solidtop' : '' );
//         if( $opts['select'] ) {
//           $tr_attr['class'] .= ' trselectable';
//           if( adefault( $GLOBALS, $opts['select'], 0 ) == $posten_id ) {
//             $tr_attr['class'] .= ' trselected';
//           }
//           $tr_attr['onclick'] .= inlink( '!submit', array( 'context' => 'js', $opts['select'] => $posten_id ) );
//         }
        open_list_row( $tr_attr );
          $t = $p['nr'];
          if( $books_read && ( $global_format === 'html' ) ) {
            $t = inlink( 'buchung', array( 'text' => $t, 'buchungen_id' => $p['buchungen_id'] ) );
          }
          open_list_cell( 'nr', $t, 'class=number' );
          $t = $posten_id;
          if( $books_read && ( $global_format === 'html' ) ) {
            $t = any_link( 'posten', $posten_id );
          }
          open_list_cell( 'id', $t, 'class=number' );
          // open_list_cell( 'geschaeftsjahr', $p['geschaeftsjahr'], 'class=right' );
          $t = ( $geschaeftsjahr_zeigen ? $p['geschaeftsjahr'] . " $NBSP " : '');
          if( $is_vortrag ) {
            $t .= ' VT ';
          } else if( $is_ergebnisverwendung ) {
            $t .= ' EV ';
          } else {
            $t .= sprintf( '%04u', $p['valuta'] );
          }
          open_list_cell( 'fqvaluta', $t, 'class=center' );
          open_list_cell( 'buchung', $p['buchungsdatum'], array( 'class' => 'right' ) );
          open_list_cell( 'beleg', $p['beleg'] );
          $t = $p['vorfall'];
          if( strlen( $t ) > 30 ) {
            $t = substr( $t, 0, 30 ) . '...';
          }
          open_list_cell( 'vorfall', $t );
          open_list_cell( 'referenz', $p['referenz'] );
          open_list_cell( 'kontenkreis', $p['kontenkreis'], 'class=center' );
          open_list_cell( 'seite', $p['seite'], 'class=center' );
          $t = $p['titel'];
          if( $books_read && ( $global_format === 'html' ) ) {
            $t = inlink( 'hauptkonto', array( 'class' => 'href', 'hauptkonten_id' => $p['hauptkonten_id'] , 'text' => $t ) );
          }
          open_list_cell( 'hauptkonto', $t );
          $t = $p['cn'];
          if( $global_format === 'html' ) {
            $t = inlink( 'unterkonto', array( 'class' => 'href', 'unterkonten_id' => $p['unterkonten_id'] , 'text' => $t ) );
          }
          open_list_cell( 'unterkonto', $t );
          open_list_cell( 'skrnummer', $p['skrnummer'] );

          switch( $p['ust_satz'] ) {
            case '0':
              $t = '-';
              break;
            case '1':
              $t = $ust_satz_1_prozent;
              break;
            case '2':
              $t = $ust_satz_2_prozent;
              break;
          }
          $netto = $p['betrag'];
          $brutto = $p['betrag'];
          $ust_betrag = 0;
          $vorsteuer_betrag = '-';
          if( $t != '-' ) {
            $ust_betrag = $netto * $t / 100.0;
            $brutto = $netto + $ust_betrag;
            if( ( $p['art'] == 'S' ) && ( $p['seite'] == 'A' ) ) {
              $vorsteueranteil = $p['ust_faktor_prozent'] / 100.0;
              if( $vorsteueranteil > 0.995 ) {
                $vorsteuer_betrag = $ust_betrag;
              } else {
                // der komplizierte fall:
                $t .= sprintf( ' (%.2f%%)', $p['ust_faktor_prozent'] );
                $netto = $netto / ( 1 + $t / 100.0 * ( 1 - $vorsteueranteil ) );
                $ust_betrag = $netto * $t / 100.0;
                $vorsteuer_betrag = $ust_betrag * $vorsteueranteil;
              }
            }
          }
          open_list_cell( 'ust_satz', $t, 'class=number oneline' );
          open_list_cell( 'netto', sprintf( '%.2f', $netto ), 'class=number' );
          open_list_cell( 'ust_betrag', sprintf( '%.2f', $ust_betrag ), 'class=number' );
          open_list_cell( 'vorsteuer_betrag', sprintf( '%.2f', $vorsteuer_betrag ), 'class=number' );

          $b = price_view( $p['betrag'] );
          $t_S = $t_H = $t_Sg = $t_Hg = '';
          if( $p['flag_ausgefuehrt'] ) {
            switch( $p['art'] ) {
              case 'S':
                $t_S = $b;
                break;
              case 'H':
                $t_H = $b;
                break;
            }
          } else {
            switch( $p['art'] ) {
              case 'S':
                $t_Sg = $b;
                break;
              case 'H':
                $t_Hg = $b;
                break;
            }
          }
          open_list_cell( 'soll', $t_S, 'number' );
          open_list_cell( 'haben', $t_H, 'number' );
          open_list_cell( 'saldo', ( ( $saldoH > $saldoS ) ?  price_view( $saldoH - $saldoS ) . ' H' : price_view( $saldoS - $saldoH ) . ' S' ), 'class=number' );

          open_list_cell( 'soll_geplant', $t_Sg, 'number' );
          open_list_cell( 'haben_geplant', $t_Hg, 'number' );
          open_list_cell( 'saldo_geplant', ( ( $saldoH_geplant > $saldoS_geplant ) ?  price_view( $saldoH_geplant - $saldoS_geplant ) . ' H' : price_view( $saldoS_geplant - $saldoH_geplant ) . ' S' ), 'class=number' );

          $t = '-';
          if( $books_read && ( $global_format === 'html' ) ) {
            $t = inlink( 'buchung', "buchungen_id={$p['buchungen_id']},text=,class=icon edit" );
          }
          open_list_cell( 'aktionen', $t );
      }
      if( $p['nr'] == $limits['limit_to'] ) {
        if( ( $limits['limit_to'] + 1 < $count ) ) {
          open_list_row( 'sum' );
            open_list_cell( '', 'Zwischensaldo:', "colspan=$cols_before_soll" );

            open_list_cell( 'soll', price_view( $saldoS ), 'number' );
            open_list_cell( 'haben', price_view( $saldoH ), 'number' );
            open_list_cell( 'saldo', ( ( $saldoH > $saldoS ) ?  price_view( $saldoH - $saldoS ) . ' H' : price_view( $saldoS - $saldoH ) . ' S' ), 'class=number' );

            open_list_cell( 'soll_geplant', price_view( $saldoS_geplant ), 'number' );
            open_list_cell( 'haben_geplant', price_view( $saldoH_geplant ), 'number' );
            open_list_cell( 'saldo_geplant', ( ( $saldoH_geplant > $saldoS_geplant ) ?  price_view( $saldoH_geplant - $saldoS_geplant ) . ' H' : price_view( $saldoS_geplant - $saldoH_geplant ) . ' S' ), 'class=number' );

            open_list_cell( 'aktionen', ' ' );
        }
        $saldo_posten_count = 0;
      }
    }
    open_list_row( 'sum' );
      open_list_cell( '', "Saldo gesamt" . ( $saldo_posten_count ? " (mit $saldo_posten_count nicht gezeigen Posten)" : '' ) .':', "colspan=$cols_before_soll" );
      open_list_cell( 'soll', price_view( $saldoS ), 'number' );
      open_list_cell( 'haben', price_view( $saldoH ), 'number' );
      open_list_cell( 'saldo', ( ( $saldoH > $saldoS ) ?  price_view( $saldoH - $saldoS ) . ' H' : price_view( $saldoS - $saldoH ) . ' S' ), 'class=number' );

      open_list_cell( 'soll_geplant', $t_Sg, 'number' );
      open_list_cell( 'haben_geplant', $t_Hg, 'number' );
      open_list_cell( 'saldo_geplant', ( ( $saldoH_geplant > $saldoS_geplant ) ?  price_view( $saldoH_geplant - $saldoS_geplant ) . ' H' : price_view( $saldoS_geplant - $saldoH_geplant ) . ' S' ), 'class=number' );

      open_list_cell( 'aktionen', ' ' );
  close_list();
}

// buchungen
//
function buchungenlist_view( $filters = array(), $opts = array() ) {
  global $table_level, $table_options_stack;

  $opts = parameters_explode( $opts );
  $opts['orderby'] = adefault( $opts, 'orderby', 'buchung-R' );
  $list_options = handle_list_options( $opts, 'bu', array(
    'id' => 't=0,s=buchungen_id'
  , 'fqvaluta' => 't,s'
  , 'buchung' => 's=ctime,t'
  , 'vorfall' => 's,t'
  , 'beleg' => 's,t'
  , 'soll' => 't', 'haben' => 't'
  , 'aktionen' => 't'
  ) );

  if( ! ( $buchungen = sql_buchungen( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', 'Keine Buchungen vorhanden' );
    return;
  }
  $count = count( $buchungen );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr', 'nr', 'class=center solidright solidleft' );
      open_list_cell( 'id', 'id', 'class=center solidright solidleft' );
      open_list_cell( 'buchung', 'Buchung', 'class=center solidright solidleft' );
      open_list_cell( 'fqvaluta', 'Gesch'.H_AMP.'auml;ftsjahr / Valuta', 'class=center solidright solidleft' );
      open_list_cell( 'vorfall', 'Vorfall', 'class=center solidright solidleft' );
      open_list_cell( 'beleg', 'beleg', 'class=center solidright solidleft' );
      open_list_cell( 'soll', 'Soll', 'class=center solidright,colspan=3' );
      open_list_cell( 'haben', 'Haben', 'class=center solidright,colspan=3' );
      open_list_cell( 'aktionen', 'Aktionen', 'class=center solidright' );
    foreach( $buchungen as $b ) {
      if( $b['nr'] < $limits['limit_from'] ) {
        continue;
      }
      if( $b['nr'] > $limits['limit_to'] ) {
        break;
      }
      $id = $b['buchungen_id'];
      $pS = sql_posten( array( 'buchungen_id' => $id, 'art' => 'S' ) );
      $pH = sql_posten( array( 'buchungen_id' => $id, 'art' => 'H' ) );
      $nS = count( $pS );
      $nH = count( $pH );
      $nMax = ( $nS > $nH ? $nS : $nH );
      $geschaeftsjahr = $pS[0]['geschaeftsjahr'];
      for( $i = 0; $i < $nMax; $i++ ) {
        // $table_options_stack[ $table_level ]['row_number'] = $b['nr'];
        $current_list['row_number_body'] = $b['nr'];
        open_list_row( $i == $nMax-1 ? 'solidbottom' : '' );
        $td_hborderclass = ( $i == 0 ) ? ' solidtop smallskipt' : ' notop';
        $td_hborderclass .= ( $i == $nMax-1 ) ? ' solidbottom smallskipb' : ' nobottom';
        if( $i == 0 ) {
          open_list_cell( 'nr', $b['nr'], 'class=center top solidleft solidright'.$td_hborderclass );
          open_list_cell( 'id', $b['buchungen_id'], 'class=center top solidleft solidright'.$td_hborderclass );
          open_list_cell( 'buchung'
          , inlink( 'buchungen', array( 'class' => 'href', 'text' => $b['buchungsdatum'], 'buchungsdatum' => $b['buchungsdatum'] ) )
          , 'class=center top solidleft solidright'.$td_hborderclass
          );
          open_list_cell( 'fqvaluta'
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
          open_list_cell( 'beleg', $b['beleg']
          , array( 'class' => 'left top solidleft'.$td_hborderclass , 'rowspan' => $nMax )
          );
        } else {
          open_list_cell( 'nr', '', 'class=solidleft solidright'.$td_hborderclass );
          open_list_cell( 'id', '', 'class=solidleft solidright'.$td_hborderclass );
          open_list_cell( 'buchung', '', 'class=solidleft solidright'.$td_hborderclass );
          open_list_cell( 'fqvaluta', '', 'class=solidleft solidright'.$td_hborderclass );
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
          $t = $p['cn'];
          if( $p['skrnummer'] ) {
            $t = "{$p['skrnummer']} $t";
          }
          open_list_cell( 'haben'
          , inlink( 'unterkonto', array( 'class' => 'href', 'text' => $t, 'unterkonten_id' => $p['unterkonten_id'] ) )
          , 'class=left'.$td_hborderclass . ( $i > 0 ? ' dottedtop' : '' ) 
          );
          open_list_cell( 'haben', price_view( $p['betrag'] ), 'class=number solidright'.$td_hborderclass . ( $i > 0 ? ' dottedtop' : '' ) );
        } else {
          open_list_cell( 'haben', '', "class=$td_hborderclass,colspan=3" );
        }
        if( $i == 0 ) {
          open_list_cell( 'aktionen'
          , inlink( 'buchung', array( 'class' => 'icon edit', 'text' => '', 'buchungen_id' => $id ) )
          , 'class=top solidright solidleft'.$td_hborderclass
          );
        } else {
          open_list_cell( 'aktionen', '', 'class=solidleft solidright'.$td_hborderclass );
        }
      }
    }
  close_list();
}


function saldenlist_view( $filters = array(), $opts = array() ) {
  global $geschaeftsjahr_min, $geschaeftsjahr_max;

  $filters = parameters_explode( $filters, array( 'allow' => 'unterkonten_id,hauptkonten_id,seite,kontenkreis' ) );

  $opts = parameters_explode( $opts );
  $authorized = adefault( $opts, 'authorized', 0 );

  $list_options = handle_list_options( $opts, 'buchungen', array(
    'jahr' => 't=on'
  , 'vortrag_buchungen' => 'h=buchungen ohne vortrag'
  , 'buchungen' => 't'
  , 'vortrag_geplant' => 't'
  , 'vortrag_ausgefuehrt' => 't'
  , 'saldo_ausgefuehrt' => 't'
  , 'saldo_geplant' => 't'
  , 'saldo_alle' => 't'
  ) );
  $select_jahr = adefault( $opts, 'select_jahr' );

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'Jahr' );
      open_list_cell( 'Vortragsbuchungen' );
      open_list_cell( 'buchungen', 'sonstige Buchungen' );
      open_list_cell( 'vortrag_geplant', 'Vortrag geplant' );
      open_list_cell( 'vortrag_ausgefuehrt', 'Vortrag ausgefuehrt' );
      open_list_cell( 'saldo_geplant', 'Saldo geplant' );
      open_list_cell( 'saldo_ausgefuehrt', 'Saldo ausgefuehrt' );
      open_list_cell( 'saldo_alle', 'Saldo gesamt' );

  $j = $geschaeftsjahr_min;
  while( true ) {
    open_list_row();

      open_list_cell( 'jahr', $select_jahr ? inlink( '!', array( $select_jahr => $j, 'text' => $j ) ) : $j );

      $filters['geschaeftsjahr'] = $j;

      $rf = $filters;
      $rf['valuta_von'] = 100;
      $rf['valuta_bis'] = 100;
      $text = sql_buchungen( $rf, "single_field=COUNT,authorized=$authorized" );
      $link = inlink( 'journal', array( 'text' => $text ) + $rf );
      open_list_cell( 'vortragsbuchungen', $link );

      $rf = $filters;
      $rf['valuta_von'] = 101;
      $rf['valuta_bis'] = 1299;
      $text = sql_buchungen( $rf, "single_field=COUNT,authorized=$authorized" );
      $link = inlink( 'journal', array( 'text' => $text ) + $rf );
      open_list_cell( 'buchungen', $link );

      $rf = $filters;
      $rf['valuta_von'] = 100;
      $rf['valuta_bis'] = 100;
      $rf['flag_ausgefuehrt'] = 0;
      $text = sql_unterkonten_saldo( $rf, "authorized=$authorized" );
      $link = inlink( 'posten', array( 'text' => $text ) + $rf );
      open_list_cell( 'vortrag_geplant', $text );

      $rf = $filters;
      $rf['valuta_von'] = 100;
      $rf['valuta_bis'] = 100;
      $rf['flag_ausgefuehrt'] = 1;
      $text = sql_unterkonten_saldo( $rf, "authorized=$authorized" );
      $link = inlink( 'posten', array( 'text' => $text ) + $rf );
      open_list_cell( 'vortrag_ausgefuehrt', $text );

      $rf = $filters;
      $rf['flag_ausgefuehrt'] = 0;
      $text = sql_unterkonten_saldo( $rf, "authorized=$authorized" );
      $link = inlink( 'posten', array( 'text' => $text ) + $rf );
      open_list_cell( 'saldo_geplant', $text );

      $rf = $filters;
      $rf['flag_ausgefuehrt'] = 1;
      $text = sql_unterkonten_saldo( $rf, "authorized=$authorized" );
      $link = inlink( 'posten', array( 'text' => $text ) + $rf );
      open_list_cell( 'saldo_ausgefuehrt', $text );

      $rf = $filters;
      $text = sql_unterkonten_saldo( $rf, "authorized=$authorized" );
      $link = inlink( 'posten', array( 'text' => $text ) + $rf );
      open_list_cell( 'saldo_alle', $text );

    if( ++$j > $geschaeftsjahr_max ) { 
      unset( $filters['geschaeftsjahr'] );
      $rf = array( '&&', "geschaeftsjahr>=$j", $filters );
      $n = sql_buchungen( $rf, "single_field=COUNT,authorized=$authorized" );
      if( $n < 1 ) {
        break;
      }
    }
  }

  close_list();
}


function geschaeftsjahrelist_view( $filters = array(), $opts = array() ) {
  global $geschaeftsjahr_abgeschlossen, $aUML;

  $list_options = handle_list_options( $opts, 'gj', array(
    'gj' => 't'
  , 'buchungen_ausgefuehrt' => 't', 'posten_ausgefuehrt' => 't'
  , 'buchungen_geplant' => 't', 'posten_geplant' => 't'
  , 'ergebnis_ausgefuehrt' => 't'
  , 'ergebnis_geplant' => 't'
  , 'ergebnis_alle' => 't'
  , 'bilanzsumme_ausgefuehrt' => 't'
  , 'bilanzsumme_geplant' => 't'
  , 'bilanzsumme_alle' => 't'
  , 'status' => 't'
  ) );

  $geschaeftsjahre = sql_buchungen( $filters, array( 'orderby' => 'geschaeftsjahr', 'groupby' => 'buchungen.geschaeftsjahr' ) );
  if( ! $geschaeftsjahre ) {
    open_div( '', "Keine Gesch{$aUML}ftsjahre vorhanden" );
    return;
  }
  $count = count( $geschaeftsjahre );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'gj', 'Jahr', 'class=center solidright solidleft' );
//      open_list_cell( 'hauptkonten', 'Hauptkonten', 'class=center solidright' );
//      open_list_cell( 'unterkonten', 'Unterkonten', 'class=center solidright' );
      open_list_cell( 'buchungen_ausgefuehrt', 'Buchungen ausgefuehrt', 'class=center solidright' );
      open_list_cell( 'posten_ausgefuehrt', 'Posten ausgefuehrt', 'class=center solidright' );
      open_list_cell( 'buchungen_geplant', 'Buchungen geplant', 'class=center solidright' );
      open_list_cell( 'posten_geplant', 'Posten geplant', 'class=center solidright' );
      open_list_cell( 'ergebnis_ausgefuehrt', 'Ergebnis ausgefuehrt', 'class=center solidright' );
      open_list_cell( 'bilanzsumme_ausgefuehrt', 'Bilanzsumme ausgefuehrt', 'class=center solidright' );
      open_list_cell( 'ergebnis_geplant', 'Ergebnis geplant', 'class=center solidright' );
      open_list_cell( 'bilanzsumme_geplant', 'Bilanzsumme geplant', 'class=center solidright' );
      open_list_cell( 'ergebnis_alle', 'Ergebnis gesamt', 'class=center solidright' );
      open_list_cell( 'bilanzsumme_alle', 'Bilanzsumme gesamt', 'class=center solidright' );
      open_list_cell( 'status', 'Status', 'class=center solidright' );
      // open_th( 'center solidright', '', 'Aktionen' );
    foreach( $geschaeftsjahre as $g ) {
      if( $g['nr'] < $limits['limit_from'] ) {
        continue;
      }
      if( $g['nr'] > $limits['limit_to'] ) {
        break;
      }
      $j = $g['geschaeftsjahr'];
      open_list_row();
        open_list_cell( 'gj', $j, 'class=top' );
//        open_list_cell( 'hauptkonten', $g['hauptkonten_count'] );
//        open_list_cell( 'unterkonten', inlink( 'unterkontenliste', array( 'geschaeftsjahr' => $j, 'text' => $g['unterkonten_count'] ) ) );

        $buchungen_count = count( sql_buchungen( "geschaeftsjahr=$j,flag_ausgefuehrt=1", 'selects=buchungen_id' ) );
        open_list_cell( 'buchungen_ausgefuehrt', inlink( 'journal', array( 'geschaeftsjahr' => $j, 'text' => $buchungen_count ) ) );
        $posten_count = count( sql_posten( "geschaeftsjahr=$j,flag_ausgefuehrt=1", 'selects=posten_id' ) );
        open_list_cell( 'posten_ausgefuehrt', inlink( 'journal', array( 'geschaeftsjahr' => $j, 'text' => $posten_count ) ) );

        $buchungen_count = count( sql_buchungen( "geschaeftsjahr=$j,flag_ausgefuehrt=0", 'selects=buchungen_id' ) );
        open_list_cell( 'buchungen_geplant', inlink( 'journal', array( 'geschaeftsjahr' => $j, 'text' => $buchungen_count ) ) );
        $posten_count = count( sql_posten( "geschaeftsjahr=$j,flag_ausgefuehrt=0", 'selects=posten_id' ) );
        open_list_cell( 'posten_geplant', inlink( 'journal', array( 'geschaeftsjahr' => $j, 'text' => $posten_count ) ) );

        $saldoE = sql_unterkonten_saldo( array( 'seite' => 'P', 'kontenkreis' => 'E', 'geschaeftsjahr' => $j, 'flag_ausgefuehrt' => 1 ) )
                - sql_unterkonten_saldo( array( 'seite' => 'A', 'kontenkreis' => 'E', 'geschaeftsjahr' => $j, 'flag_ausgefuehrt' => 1 ) );
        open_list_cell( 'ergebnis_ausgefuehrt', inlink( 'erfolgskonten', array( 'geschaeftsjahr' => $j, 'text' => saldo_view( 'P', $saldoE ) ) ), 'class=number top' );

        $saldoP = sql_unterkonten_saldo( array( 'seite' => 'P', 'kontenkreis' => 'B', 'geschaeftsjahr' => $j, 'flag_ausgefuehrt' => 1 ) )
                + $saldoE;
        open_list_cell( 'bilanzsumme_ausgefuehrt', inlink( 'bestandskonten', array( 'geschaeftsjahr' => $j, 'text' => saldo_view( 'P', $saldoP ) ) ), 'class=number top' );

        $saldoE = sql_unterkonten_saldo( array( 'seite' => 'P', 'kontenkreis' => 'E', 'geschaeftsjahr' => $j, 'flag_ausgefuehrt' => 0 ) )
                - sql_unterkonten_saldo( array( 'seite' => 'A', 'kontenkreis' => 'E', 'geschaeftsjahr' => $j, 'flag_ausgefuehrt' => 0 ) );
        open_list_cell( 'ergebnis_geplant', inlink( 'erfolgskonten', array( 'geschaeftsjahr' => $j, 'text' => saldo_view( 'P', $saldoE ) ) ), 'class=number top' );

        $saldoP = sql_unterkonten_saldo( array( 'seite' => 'P', 'kontenkreis' => 'B', 'geschaeftsjahr' => $j, 'flag_ausgefuehrt' => 0 ) )
                + $saldoE;
        open_list_cell( 'bilanzsumme_geplant', inlink( 'bestandskonten', array( 'geschaeftsjahr' => $j, 'text' => saldo_view( 'P', $saldoP ) ) ), 'class=number top' );

        $saldoE = sql_unterkonten_saldo( array( 'seite' => 'P', 'kontenkreis' => 'E', 'geschaeftsjahr' => $j ) )
                - sql_unterkonten_saldo( array( 'seite' => 'A', 'kontenkreis' => 'E', 'geschaeftsjahr' => $j ) );
        open_list_cell( 'ergebnis_alle', inlink( 'erfolgskonten', array( 'geschaeftsjahr' => $j, 'text' => saldo_view( 'P', $saldoE ) ) ), 'class=number top' );

        $saldoP = sql_unterkonten_saldo( array( 'seite' => 'P', 'kontenkreis' => 'B', 'geschaeftsjahr' => $j ) )
                + $saldoE;
        open_list_cell( 'bilanzsumme_alle', inlink( 'bestandskonten', array( 'geschaeftsjahr' => $j, 'text' => saldo_view( 'P', $saldoP ) ) ), 'class=number top' );

        open_list_cell( 'status', $j > $geschaeftsjahr_abgeschlossen ? 'offen' : 'abgeschlossen' );
        // open_td( '', '', '' );  // aktionen
    }
  close_list();
}


// darlehen
//
function darlehenlist_view( $filters = array(), $opts = array() ) {

  $list_options = handle_list_options( $opts, 'dl', array(
    'nr' => 't', 'id' => 's=darlehen_id,t=0'
  , 'kreditor' => 't,s=people_cn'
  , 'cn' => 't,s'
  , 'darlehenkonto' => 't,s=darlehen_unterkonten_cn', 'zinskonto' => 't,s=zins_unterkonten_cn'
  , 'gj_darlehen' => 't,s=geschaeftsjahr_darlehen'
  , 'gj_zinslauf_start' => 't,s=geschaeftsjahr_zinslauf_start'
  , 'gj_zinsauszahlung_start' => 't,s=geschaeftsjahr_zinsauszahlung_start'
  , 'gj_tilgung_start' => 't,s=geschaeftsjahr_tilgung_start', 'gj_tilgung_ende' => 't,s=geschaeftsjahr_tilgung_ende'
  , 'zugesagt' => 't,s=betrag_zugesagt', 'abgerufen' => 't,s=betrag_abgerufen'
  , 'zinssatz' => 't,s=zins_prozent', 'aktionen' => 't'
  ) );

  if( ! ( $darlehen = sql_darlehen( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', 'Keine Darlehen vorhanden' );
    return;
  }
  $count = count( $darlehen );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  open_table( $list_options );
    open_tr();
      open_list_cell( 'nr' );
      open_list_cell( 'id' );
      open_list_cell( 'Kreditor' );
      open_list_cell( 'cn' );
      open_list_cell( 'Darlehenkonto' );
      open_list_cell( 'Zinskonto' );
      open_list_cell( 'gj_darlehen', 'Darlehen jahr' );
      open_list_cell( 'gj_zinslauf_start', 'Zinslauf ab' );
      open_list_cell( 'gj_zinsauszahlung_start', 'Zinsauszahlung ab' );
      open_list_cell( 'gj_tilgung_start', 'Tilgung ab' );
      open_list_cell( 'gj_tilgung_ende', 'Tilgung bis' );
      open_list_cell( 'zugesagt' );
      open_list_cell( 'abgerufen' );
      open_list_cell( 'Zinssatz' );
      open_list_cell( 'Aktionen' );
    foreach( $darlehen as $d ) {
      if( $d['nr'] < $limits['limit_from'] ) {
        continue;
      }
      if( $d['nr'] > $limits['limit_to'] ) {
        break;
      }
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
        open_list_cell( 'gj_zinsauszahlung_start', $d['geschaeftsjahr_zinsauszahlung_start'], 'class=number' );
        open_list_cell( 'gj_tilgung_start', $d['geschaeftsjahr_tilgung_start'], 'class=number' );
        open_list_cell( 'gj_tilgung_ende', $d['geschaeftsjahr_tilgung_ende'], 'class=number' );
        open_list_cell( 'zugesagt', price_view( $d['betrag_zugesagt'] ), 'class=number' );
        open_list_cell( 'abgerufen', price_view( $d['betrag_abgerufen'] ), 'class=number' );
        open_list_cell( 'zinssatz', price_view( $d['zins_prozent'] ), 'class=number' );
        open_list_cell( 'aktionen' );
          echo inlink( 'darlehen', "class=edit,text=,darlehen_id=$id" );
          echo inlink( '!submit', "class=drop,confirm=wirklich loeschen?,action=deleteDarlehen,message=$id" );
    }
  close_list();
}

// zahlungsplan
//
function zahlungsplanlist_view( $filters = array(), $opts = array() ) {

  $darlehen_id = adefault( $filters, 'darlehen_id', false );

  $opts = parameters_explode( $opts );
  $actions = adefault( $opts, 'actions', true );
  if( $actions === true ) {
    $actions = 'delete';
  }
  $actions = parameters_explode( $actions );
  $action_delete = adefault( $actions, 'delete', false );

  $list_options = handle_list_options( $opts, 'zp', array(
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

  if( ! ( $zp = sql_zahlungsplan( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', 'Kein Zahlungsplan vorhanden' );
    return;
  }
  $count = count( $zp );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  $saldoS = 0.0;
  $saldoH = 0.0;
  $saldo_posten_count = 0;

  open_table( $list_options );
    open_tr();
      open_list_cell( 'Nr' );
      open_list_cell( 'Id' );
      open_list_cell( 'Darlehen' );
      open_list_cell( 'Kreditor' );
      open_list_cell( 'Valuta' );
      open_list_cell( 'Zins' );
      open_list_cell( 'Kommentar' );
      open_list_cell( 'Konto' );
      $cols_before_soll = current_list_col_number();
      open_list_cell( 'Soll' );
      open_list_cell( 'Haben' );
      open_list_cell( 'Buchung' );
      open_list_cell( 'Aktionen' );

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
              echo action_link( 'script=buchung,text=buchen...', $buchungssatz );
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
  close_list();
}


// main menu
//
function mainmenu_view( $opts = array() ) {
  global $logged_in, $login_people_id, $aUML;

  $field = init_var( 'geschaeftsjahr_thread', array(
    'type' => 'u'
  , 'set_scopes' => 'thread'
  , 'initval' => $GLOBALS['geschaeftsjahr_current']
  , 'min' => $GLOBALS['geschaeftsjahr_min']
  , 'max' => $GLOBALS['geschaeftsjahr_max']
  ) );

  $menu = array();

  if( $logged_in ) {

    if( have_priv( 'books', 'read' ) ) {
      $menu[] = array( 'script' => "bestandskonten",
           "title" => "Bilanz",
           "text" => "Bestandskonten" );
      
      $menu[] = array( 'script' => "erfolgskonten",
           "title" => "GV-Rechnung",
           "text" => "Erfolgskonten" );
      
      $menu[] = array( 'script' => "hauptkontenliste",
           "title" => "Hauptkonten",
           "text" => "Hauptkonten" );
      
      $menu[] = array( 'script' => "unterkontenliste",
           "title" => "Unterkonten",
           "text" => "Unterkonten" );
      
      $menu[] = array( 'script' => "journal",
           "title" => "Journal",
           "text" => "Journal" );
      
      $menu[] = array( 'script' => "posten",
           "title" => "Posten",
           "text" => "Posten" );
      
      $menu[] = array( 'script' => "personen",
           "title" => "Personen",
           "text" => "Personen" );
      
  //     $menu[] = array( 'script' => "zahlungsplanliste",
  //          "title" => "Zahlungsplan",
  //          "text" => "Zahlungsplan" );
  //     
  //     $menu[] = array( 'script' => "things",
  //          "title" => 'Gegenst'.H_AMP.'auml;nde',
  //          "text" => 'Gegenst'.H_AMP.'auml;nde' );
  //     
      if( have_priv('*','*') ) {
  
        $menu[] = array( 'script' => "geschaeftsjahre",
             "title" => 'Gesch'.H_AMP.'auml;ftsjahre',
             "text" => 'Gesch'.H_AMP.'auml;ftsjahre' );
      
        $menu[] = array( 'script' => "darlehenliste",
             "title" => "Darlehen",
             "text" => "Darlehen" );
        
        $menu[] = array( 'script' => "kontenrahmen",
             "title" => "Kontenrahmen",
             "text" => "Kontenrahmen" );
        
  //       $menu[] = array( 'script' => "ka",
  //            "title" => "ka",
  //            "text" => "ka" );
  //       
  //       $menu[] = array( 'script' => "logbook",
  //            "title" => "Logbuch",
  //            "text" => "Logbuch" );
  //       
        $menu[] = array( 'script' => "config",
             "title" => "Konfiguration",
             "text" => "Konfiguration" );
      }
    } else {  // ! have_priv( 'books', 'read' )

      $unterkonten = sql_unterkonten( "flag_personenkonto,people_id=$login_people_id" );
      foreach( $unterkonten as $uk ) {
        if( have_priv( 'unterkonten', 'read', $uk ) ) {
          $menu[] = array( 'script' => "unterkonto",
               "title" => "eigenes Konto: {$uk['cn']}",
               "text" => "eigenes Konto: {$uk['cn']}" );
        }
      }

    }

    $menu[] = html_div( 'quads smallpads', "Gesch{$aUML}ftsjahr: ". selector_int( $field ) );
  
    $menu[] = array( 'script' => ''
    , 'title' => 'Abmelden'
    , 'text' => 'Abmelden'
    , 'login' => 'logout' );

  } else {  // not logged in

    $menu[] = array( 'script' => ''
    , 'title' => 'Anmelden'
    , 'text' => 'Anmelden'
    , 'login' => 'login'
    );
  }
 
  return menu_view( $menu, $opts );
}

// function mainmenu_header() {
//   global $mainmenu;
//   foreach( $mainmenu as $h ) {
//     open_li( '', inlink( $h['script'], array(
//       'text' => $h['text'], 'title' => $h['title'] , 'class' => 'href'
//     ) ) );
//   }
// }



?>
