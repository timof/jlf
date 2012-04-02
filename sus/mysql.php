<?php

////////////////////////////////////
//
// things-funktionen:
//
////////////////////////////////////

function sql_query_things( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $groupby = 'things.things_id';

  $selects = sql_default_selects('things');
  $joins['LEFT unterkonten'] = 'things_id';
  $joins['LEFT hauptkonten'] = 'hauptkonten_id';
  $joins['LEFT kontoklassen'] = 'kontoklassen_id';
  $joins['LEFT posten'] = 'unterkonten_id';
  // $joins['LEFT hauptkonten'] = 'hauptkonten_id';
  // $joins['LEFT kontoklassen'] = 'kontoklassen_id';
  $selects[] = 'IFNULL( SUM(posten.betrag), 0.0 ) AS wert';
  $selects[] = 'unterkonten.unterkonten_id';

  $filters = sql_canonicalize_filters( 'things', $filters_in, $joins );
  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = '';
      break;
    case 'WERT':
      $op = 'SELECT';
      $groupby = '1';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'things', $filters, $selects, $joins, $orderby, $groupby );
}

function sql_things( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'things.cn,things.anschaffungsjahr';
  $sql = sql_query_things( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_thing( $filters = array(), $default = false ) {
  $sql = sql_query_things( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

// function sql_things_wert( $filters = array() ) {
//   $sql = sql_query_things( 'WERT', $filters );
//   return sql_do_single_field( $sql, 'wert' );
// }

function sql_delete_things( $filters, $if_dangling = false ) {
  foreach( sql_things( $filters ) as $thing ) {
    $things_id = $thing['things_id'];
    if( sql_unterkonten( array( 'sachkonto' => 1, 'things_id' => $things_id ) ) ) {
      if( $if_dangling )
        continue;
      else
        error( 'thing: loeschen nicht moeglich: unterkonto vorhanden' );
    }
    sql_delete( 'things', $things_id );
  }
}


////////////////////////////////////
//
// kontoklassen-funktionen:
//
////////////////////////////////////

function sql_query_kontoklassen( $op, $filters_in = array(), $using = array(), $orderby = 'kontoklassen.kontoklassen_id' ) {

  $selects = sql_default_selects('kontoklassen');
  $filters = sql_canonicalize_filters( 'kontoklassen', $filters_in );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      case 'geschaeftsbereiche_id':
        $val = sql_unique_value( 'kontoklassen', 'geschaeftsbereich', $val );
        $key = 'kontoklassen.geschaeftsbereich';
        break;
      default:
        error( "undefined key: $key" );
    }
    $atom[ -1 ] = 'cooked_atom';
  }

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'kontoklassen', $filters, $selects, array(), $orderby );
}

function sql_kontoklassen( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'kontoklassen.kontoklassen_id';
  $sql = sql_query_kontoklassen( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_kontoklasse( $filters = array(), $default = false ) {
  $sql = sql_query_kontoklassen( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}


////////////////////////////////////
//
// bankkonten-funktionen:
//
////////////////////////////////////

function sql_query_bankkonten( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $groupby = 'bankkonten.bankkonten_id';

  $selects = sql_default_selects(
    array( 'bankkonten', 'kontoklassen', 'unterkonten' )
  , array( 'kontoklassen.cn' => 'kontoklassen_cn', 'unterkonten.cn' => 'unterkonten_cn' )
  );
  $joins['LEFT unterkonten'] = 'bankkonten_id';
  $joins['LEFT hauptkonten'] = 'hauptkonten_id';
  $joins['LEFT kontoklassen'] = 'kontoklassen_id';
  $joins['LEFT posten'] = 'unterkonten_id';
  $selects[] = 'IFNULL( SUM( posten.betrag ), 0.0 ) AS saldo';

  $filters = sql_canonicalize_filters( 'bankkonten', $filters_in, $joins );

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = '';
      break;
    case 'SALDO':
      $op = 'SELECT';
      $groupby = '1';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'bankkonten', $filters, $selects, $joins, $orderby, $groupby );
}

function sql_bankkonten( $filters = array(), $orderby = 'bank, blz, kontonr' ) {
  if( $orderby === true )
    $orderby = 'bankkonten.cn';
  $sql = sql_query_bankkonten( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_bankkonto( $filters = array(), $default = false ) {
  $sql = sql_query_bankkonten( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_bankkonten_saldo( $filters = array() ) {
  $sql = sql_query_bankkonten( 'SALDO', $filters );
  return sql_do_single_fielft( $sql, 'saldo' );
}

function sql_delete_bankkonten( $filters, $if_dangling = false ) {
  foreach( sql_bankkonten( $filters ) as $bankkonto ) {
    $bankkonten_id = $bankkonto['bankkonten_id'];
    if( sql_unterkonten( array( 'bankkonto' => 1, 'bankkonten_id' => $bankkonten_id ) ) ) {
      if( $if_dangling )
        continue;
      else
        error( 'bankkonto: loeschen nicht moeglich: unterkonto vorhanden' );
    }
    sql_delete( 'bankkonten', $bankkonten_id );
  }
}


////////////////////////////////////
//
// hauptkonten-funktionen:
//
////////////////////////////////////

function sql_query_hauptkonten( $op, $filters_in = array(), $using = array(), $orderby = false, $groupby = 'hauptkonten.hauptkonten_id' ) {
  $joins = array();

  $joins['kontoklassen'] = 'kontoklassen_id';
  $selects = sql_default_selects(
    array( 'hauptkonten', 'kontoklassen' )
  , array( 'kontoklassen.cn' => 'kontoklassen_cn' )
  );
  $selects[] = "hauptkonten.hauptkonten_hgb_klasse AS hgb_klasse";
  $selects[] = "( SELECT COUNT(*) FROM unterkonten WHERE unterkonten.hauptkonten_id
                                                       = hauptkonten.hauptkonten_id ) as unterkonten_count";

  $filters = sql_canonicalize_filters( 'hauptkonten', $filters_in, $joins );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      case 'geschaeftsbereiche_id':
        $key = 'kontoklassen.geschaeftsbereich';
        $val = sql_unique_value( 'kontoklassen', $key, $val );
        break;
      case 'rubriken_id':
        $key = 'hauptkonten.rubrik';
        $val = sql_unique_value( 'hauptkonten', $key, $val );
        break;
      case 'titel_id':
        $key = 'hauptkonten.titel';
        $val = sql_unique_value( 'hauptkonten', $key, $val );
        break;
      case 'is_vortragskonto':
        $key = 'kontoklassen.vortragskonto';
        $rel = ( $val ? '!=' : '=' );
        $val = '';
        break;
      case 'hgb_klasse':
        $key = 'hauptkonten_hgb_klasse';
        $val = '^'.preg_replace( '/[.]/', '[.]', $val );  // sic!
        $atom[ 0 ] = '~=';
        break;
      default:
        error( "undefined key: $key" );
    }
    $atom[ -1 ] = 'cooked_atom';
  }

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = '';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'hauptkonten', $filters, $selects, $joins, $orderby, $groupby );
}

function sql_hauptkonten( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'geschaeftsjahr, kontoklassen.seite, hauptkonten.rubrik, hauptkonten.titel, kontoklassen.geschaeftsbereich';
  $sql = sql_query_hauptkonten( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_rubriken( $filters = array(), $orderby = 'kontenkreis, seite, rubrik' ) {
  $sql = sql_query_hauptkonten( 'SELECT', $filters, array(), $orderby, 'rubrik' );
  $table = mysql2array( sql_do( $sql ) );
  $rubriken = array();
  foreach( $table as $row ) {
    $rubriken[] = array(
      'nr' => $row['nr']
    , 'rubrik' => $row['rubrik']
    , 'rubriken_id' => md5( $row['rubrik'] )
    );
  }
  return $rubriken;
}

function sql_titel( $filters = array(), $orderby = 'kontenkreis, seite, rubrik, titel' ) {
  $sql = sql_query_hauptkonten( 'SELECT', $filters, array(), $orderby, 'titel' );
  $table = mysql2array( sql_do( $sql ) );
  $titel = array();
  foreach( $table as $row ) {
    $titel[] = array(
      'nr' => $row['nr']
    , 'titel' => $row['titel']
    , 'titel_id' => md5( $row['titel'] )
    );
  }
  return $titel;
}


function sql_one_hauptkonto( $filters = array(), $default = false ) {
  $sql = sql_query_hauptkonten( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

// hauptkonto schliessen: 
// - schliesst ein konto, loescht alle folgekonten
// - moeglich, wenn alle unterkonten geschlossen und alle folgekonten loeschbar sind
//
function sql_hauptkonto_schliessen( $hauptkonten_id, $check = false ) {

  $problems = array();
  $hk = sql_one_hauptkonto( $hauptkonten_id );
  if( $hk['hauptkonto_geschlossen'] )
    return array();

  if( sql_unterkonten( "hauptkonten_id=$hauptkonten_id,unterkonto_geschlossen=0" ) ) {
    $problems[] = "hauptkonto [$hauptkonten_id]: schliessen nicht moeglich: offenes unterkonto vorhanden";
  }

  for( $id = $hk['folge_hauptkonten_id']; $id; $id = $hk['folge_hauptkonten_id'] ) {
    $problems += sql_delete_hauptkonten( $id, 'check: vorgaenger_ignorieren' );
    $hk = sql_one_hauptkonto( $id );
  }

  if( $check ) {
    return $problems;
  }

  need( ! $problems, $problems );
  logger( "sql_hauptkonto_schliessen: [$hauptkonten_id]" );

  $hk = sql_one_hauptkonto( $hauptkonten_id );
  $folge_hk_id = $hk['folge_hauptkonten_id'];
  sql_update( 'hauptkonten', $hauptkonten_id, array( 'hauptkonto_geschlossen' => 1, 'folge_hauptkonten_id' => 0 ) );
  if( $folge_hk_id ) {
    sql_delete_hauptkonten( $folge_hk_id );
  }
}

// hauptkonto_loeschen: loescht auch alle folgekonten
// moeglich, wenn 
// - keine unterkonten vorhanden bei diesem und allen folgekonten
// - konto ist kein folgekonto oder folgekonto eines abgeschlossenen kontos
// alle folgekonten werden ebenfalls geloescht
//
function sql_delete_hauptkonten( $filters, $check = false ) {

  $hauptkonten = sql_hauptkonten( $filters, 'geschaeftsjahr' );
  $problems = array();

  foreach( $hauptkonten as $hauptkonto ) {
    $hauptkonten_id = $hauptkonto['hauptkonten_id'];

    if( $check !== 'check: vorgaenger_ignorieren' ) {
      if( sql_hauptkonten( "folge_hauptkonten_id=$hauptkonten_id" ) ) {
        $problems[] = "hauptkonto [$hauptkonten_id]: loeschen nicht moeglich: konto ist folgekonto";
      }
    }

    while( $hauptkonten_id ) {
      $hk = sql_one_hauptkonto( $hauptkonten_id );
      if( $hk['unterkonten_count'] > 0 ) {
        $problems[] = "hauptkonto [$hauptkonten_id]: loeschen nicht moeglich: unterkonten vorhanden";
      }
      $hauptkonten_id = $hk['folge_hauptkonten_id'];
    }
  }

  if( $check ) {
    return $problems;
  }

  need( ! $problems, $problems );

  foreach( $hauptkonten as $hauptkonto ) {
    $hauptkonten_id = $hauptkonto['hauptkonten_id'];
    logger( "sql_delete_hauptkonten: $hauptkonten_id", 'delete' );
    while( $hauptkonten_id ) {
      $hk = sql_one_hauptkonto( $hauptkonten_id );
      sql_delete( 'hauptkonten', $hauptkonten_id );
      $hauptkonten_id = $hk['folge_hauptkonten_id'];
    }
  }
}


function sql_hauptkonto_folgekonto_anlegen( $hauptkonten_id ) {
  global $tables;

  logger( "sql_hauptkonto_folgekonto_anlegen: $hauptkonten_id" );

  $hk = sql_one_hauptkonto( $hauptkonten_id );
  need( ! $hk['hauptkonto_geschlossen'], 'folgekonto anlegen nicht moeglich: konto ist geschlossen' );

  $hk_neu = array();
  foreach( $tables['hauptkonten']['cols'] as $k => $v ) {
    $hk_neu[ $k ] = $hk[ $k ];
  }
  $hk_neu['geschaeftsjahr'] = $hk['geschaeftsjahr'] + 1;
  unset( $hk_neu['hauptkonten_id'] );
  unset( $hk_neu['folge_hauptkonten_id'] ); // don't copy or reset - use default or keep existing value!

  if( $hk['folge_hauptkonten_id'] ) {
    $hk_neu_id = $hk['folge_hauptkonten_id'];
    sql_update( 'hauptkonten', $hk_neu_id, $hk_neu );
  } else {
    $hk_neu_id = sql_insert( 'hauptkonten', $hk_neu );
    sql_update( 'hauptkonten', $hauptkonten_id, array( 'folge_hauptkonten_id' => $hk_neu_id ) );
  }
  return $hk_neu_id;
}


////////////////////////////////////
//
// unterkonten-funktionen:
//
////////////////////////////////////

function sql_query_unterkonten( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $groupby = 'unterkonten.unterkonten_id';

  $joins['hauptkonten'] = 'hauptkonten_id';
  $joins['kontoklassen'] = 'kontoklassen_id';
  $joins['LEFT people'] = 'people_id';
  $joins['LEFT things'] = 'things_id';
  $joins['LEFT posten'] = 'unterkonten_id';
  $joins['LEFT buchungen'] = 'buchungen_id';
  $joins['LEFT bankkonten'] = 'bankkonten_id';
  $selects = sql_default_selects(
    array( 'unterkonten', 'hauptkonten', 'kontoklassen' )
  , array( 'hauptkonten.kommentar' => 'hauptkonten_kommentar', 'kontoklassen.cn' => 'kontoklassen_cn' )
  );
  $selects[] = 'people.cn AS people_cn';
  $selects[] = 'things.cn AS things_cn';
  $selects[] = 'bankkonten.bank AS bankkonten_bank';
  // hauptkonten_hgb_klasse overrides unterkonten_hgb_klasse:
  $selects[] = "IF( hauptkonten_hgb_klasse = '', unterkonten_hgb_klasse, hauptkonten_hgb_klasse ) AS hgb_klasse";
  $selects[] = "IFNULL( SUM( posten.betrag * IF( posten.art = 'S', 1, 0 ) ), 0.0 ) AS saldoS";
  $selects[] = "IFNULL( SUM( posten.betrag * IF( posten.art = 'H', 1, 0 ) ), 0.0 ) AS saldoH";
  $selects[] = "( IFNULL(
                  ( SUM( posten.betrag * IF( posten.art = 'H', 1, -1 ) ) * IF( kontoklassen.seite = 'P', 1, -1 ) )
                , 0.0 ) ) AS saldo";

  $filters = sql_canonicalize_filters( 'unterkonten', $filters_in, $joins );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {  // otherwise, check for special cases:
      case 'geschaeftsbereiche_id':
        $key = 'kontoklassen.geschaeftsbereich';
        $val = sql_unique_value( 'kontoklassen', $key, $val );
        break;
      case 'rubriken_id':
        $key = 'hauptkonten.rubrik';
        $val = sql_unique_value( 'hauptkonten', $key, $val );
        break;
      case 'titel_id':
        $key = 'hauptkonten.titel';
        $val = sql_unique_value( 'hauptkonten', $key, $val );
        break;
      case 'is_vortragskonto':
        $key = 'kontoklassen.vortragskonto';
        $rel = ( $val ? '!=' : '=' );
        $val = '';
        break;
      case 'hgb_klasse':
        $key = "IF( hauptkonten_hgb_klasse != '', hauptkonten_hgb_klasse, unterkonten_hgb_klasse )";
        $val = '^'.preg_replace( '/[.]/', '[.]', $val );  // sic!
        $rel = '~=';
        break;
      case 'stichtag':
        need( $rel === '=' );
        $rel = '<=';
        $key = 'buchungen.valuta';
        break;
      default:
        error( "undefined key: $key" );
    }
    $atom[ -1 ] = 'cooked_atom';
  }

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = '';
      break;
    case 'SALDO':
      // need( isset( $filters['unterkonten_id'] ) || isset( $filters['hauptkonten_id'] ) || isset( $filters['seite'] ) );
      $op = 'SELECT';
      // $joins['LEFT posten'] = 'unterkonten_id';
      // $selects = 'IFNULL( SUM( posten.betrag ), 0.0 ) AS saldo';
      $groupby = 'kontoklassen.seite';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'unterkonten', $filters, $selects, $joins, $orderby, $groupby );
}

function sql_unterkonten( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'seite, rubrik, titel, unterkonten.unterkonten_id';
  $sql = sql_query_unterkonten( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_unterkonto( $filters = array(), $default = false ) {
  $sql = sql_query_unterkonten( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_unterkonten_saldo( $filters = array() ) {
  $sql = sql_query_unterkonten( 'SALDO', $filters );
  $saldo = sql_do_single_field( $sql, 'saldo', 0.0 );
  return $saldo ? $saldo : 0.0;
}

// konto schliessen: moeglich, wenn
// - saldo == 0 oder erfolgskonto und
// - eventuelle folgekonten koennen _geloescht_(!) werden
//
function sql_unterkonto_schliessen( $unterkonten_id, $check = false ) {
  $problems = array();

  $uk = sql_one_unterkonto( $unterkonten_id );
  if( $uk['unterkonto_geschlossen'] )
    return array();

  if( ( $uk['kontenkreis'] !== 'E' ) && ( abs( $uk['saldo'] ) > 0.005 ) ) {
    $problems[] = "unterkonto [$unterkonten_id]: schliessen nicht moeglich: bestandskonto nicht ausgeglichen";
  }
  // if( sql_zahlungsplan( "unterkonten_id=$unterkonten_id,posten_id=0" ) ) {
  //   $problems[] = "unterkonto [$unterkonten_id]: schliessen nicht moeglich: ungebuchter zahlungsplan vorhanden";
  // }
  if( ( $folge_uk_id = $uk['folge_unterkonten_id'] ) ) {
    if( sql_delete_unterkonten( $folge_uk_id, 'check: vorgaenger_ignorieren' ) ) {
      $problems[] = "unterkonto [$unterkonten_id]: schliessen nicht moeglich: folgekonto [$folge_uk_id] nicht loeschbar";
    }
  }
  if( $check ) {
    return $problems;
  }

  need( ! $problems, $problems );
  logger( "sql_unterkonto_schliessen: [$unterkonten_id]" );

  sql_update( 'unterkonten', $unterkonten_id, array( 'unterkonto_geschlossen' => 1, 'folge_unterkonten_id' => 0 ) );
  if( $folge_uk_id ) {
    sql_delete_unterkonten( $folge_uk_id );
  }
}

// unterkonto loeschen: moeglich, wenn
// - keine posten vorhanden im konto und folgekonten
// - kein zahlungsplan involviert konto oder folgekonten
// - konto ist nicht folgekonto
// parameter 'check': wenn nicht null: test auf loeschbarkeit
// spezialfall: $check === 'check: vorgaenger_ignorieren": check auf "ist nicht folgekonto" auslassen
//
function sql_delete_unterkonten( $filters, $check = false ) {

  $unterkonten = sql_unterkonten( $filters, 'geschaeftsjahr' );
  $problems = array();

  foreach( $unterkonten as $uk ) {
    $unterkonten_id = $uk['unterkonten_id'];

    if( $check !== 'check: vorgaenger_ignorieren' ) {
      if( sql_unterkonten( array( 'folge_unterkonten_id' => $unterkonten_id ) ) ) {
        $problems[] = "unterkonto [$unterkonten_id]: loeschen nicht moeglich: konto ist folgekonto";
      }
    }

    for( $id = $unterkonten_id; $id; $id = $k2['folge_unterkonten_id'] ) {
      if( sql_posten( "unterkonten_id=$id" ) ) {
        $problems[] = 'unterkonto [$id]: loeschen nicht moeglich: posten vorhanden';
      }
      if( sql_darlehen( array( '||', "darlehen_unterkonten_id=$id", "zins_unterkonten_id=$id" ) ) ) {
        $problems[] = 'unterkonto [$id]: loeschen nicht moeglich: darlehen vorhanden';
      }
      if( sql_zahlungsplan( "unterkonten_id=$id" ) ) {
        $problems[] = 'unterkonto [$id]: loeschen nicht moeglich: zahlungsplan vorhanden';
      }
      $k2 = sql_one_unterkonto( $id );
    }
  }

  if( $check ) {
    return $problems;
  }

  need( ! $problems, $problems );

  $things = array();
  $bankkonten = array();
  foreach( $unterkonten as $uk ) {
    $id = $uk['unterkonten_id'];
    $pred = sql_unterkonten( array( 'folge_unterkonten_id' => $id ) );
    need( ! $vorgaenger, 'loeschen nicht moeglich: unterkonto ist folgekonto' );
    logger( "sql_delete_unterkonten: $unterkonten_id", 'delete' );
    while( $id ) {
      $k2 = sql_one_unterkonto( $id );
      sql_delete( 'unterkonten', $id );
//       sql_update( 'darlehen'
//       , array( 'darlehen_unterkonten_id' => $id )
//       , array( 'darlehen_unterkonten_id' => 0 )
//       );
//       sql_update( 'darlehen'
//       , array( 'zins_unterkonten_id' => $unterkonten_id )
//       , array( 'zins_unterkonten_id' => 0 )
//       );
      if( $k2['bankkonto'] )
        $bankkonten[] = $k2['bankkonten_id'];
      if( $k2['sachkonto'] )
        $things[] = $k2['things_id'];
      $id = $k2['folge_unterkonten_id'];
    }
  }

  // garbage collection:
  //
  // foreach( $things as $things_id ) {
  //   $problems .= sql_delete_things( $things_id, 'if_dangling' );
  // }
  foreach( $bankkonten as $bankkonten_id ) {
    $problems .= sql_delete_bankkonten( $bankkonten_id, 'if_dangling' );
  }
  return $problems;
}

function sql_unterkonto_folgekonto_anlegen( $unterkonten_id ) {
  global $tables;

  logger( "sql_unterkonto_folgekonto_anlegen: $unterkonten_id" );

  $uk = sql_one_unterkonto( $unterkonten_id );
  $hk = sql_one_hauptkonto( $uk['hauptkonten_id'] );

  need( ! $uk['unterkonto_geschlossen'], 'Unterkonto ist geschlossen' );
  // need( ! $uk['folge_unterkonten_id'], 'Folgekonto bereits vorhanden' );

  $hk_neu_id = $hk['folge_hauptkonten_id'];
  need( $hk_neu_id, 'Hauptkonto hat noch kein Folgekonto' );

  $uk_neu = array();
  foreach( $tables['unterkonten']['cols'] as $k => $v ) {
    $uk_neu[ $k ] = $uk[ $k ];
  }
  $uk_neu['hauptkonten_id'] = $hk_neu_id;
  unset( $uk_neu['unterkonten_id'] );
  unset( $uk_neu['folge_unterkonten_id'] );

  if( $uk['folge_unterkonten_id'] ) {
    $uk_neu_id = $uk['folge_unterkonten_id'];
    sql_update( 'unterkonten', $uk_neu_id, $uk_neu );
  } else {
    $uk_neu_id = sql_insert( 'unterkonten', $uk_neu );
    sql_update( 'unterkonten', $unterkonten_id, array( 'folge_unterkonten_id' => $uk_neu_id ) );
  }

  return $uk_neu_id;
}

function sql_get_folge_unterkonten_id( $unterkonten_id, $jahr ) {
  $uk = sql_one_unterkonto( $unterkonten_id );
  while( $uk ) {
    if( $uk['geschaeftsjahr'] == $jahr ) {
      return $uk['unterkonten_id'];
    } else if( $uk['geschaeftsjahr'] < $jahr ) {
      if( ( $unterkonten_id = $uk['folge_unterkonten_id'] ) )
        $uk = sql_one_unterkonto( $unterkonten_id );
      else
        return 0;
    } else {
      $uk = sql_one_unterkonto( array( 'folge_unterkonten_id' => $uk['unterkonten_id'] ), NULL );
    }
  }
  return 0;
}

////////////////////////////////////
//
// buchungen-funktionen
//
////////////////////////////////////

function sql_query_buchungen( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $groupby = 'buchungen.buchungen_id';

  $selects = sql_default_selects( 'buchungen' );
  $selects[] = "( SELECT COUNT(*) FROM posten WHERE ( posten.buchungen_id = buchungen.buchungen_id ) AND ( posten.art = 'S' ) ) as postenS_count";
  $selects[] = "( SELECT COUNT(*) FROM posten WHERE ( posten.buchungen_id = buchungen.buchungen_id ) AND ( posten.art = 'H' ) ) as postenH_count";
  $selects[] = "IF( valuta <= 100, 1, 0 ) as vortrag";
  $joins['posten'] = 'buchungen_id';
  $joins['unterkonten'] = 'unterkonten_id';
  $joins['hauptkonten'] = 'hauptkonten_id';
  $joins['kontoklassen'] = 'kontoklassen_id';

  $filters = sql_canonicalize_filters( 'buchungen,posten', $filters_in, $joins );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ]; $key = & $atom[ 1 ]; $val = & $atom[ 2 ];
    switch( $key ) {  // otherwise, check for special cases:
      case 'valuta_von':
        $rel = '>=';
        $key = 'buchungen.valuta';
        break;
      case 'valuta_bis':
        $rel = '<=';
        $key = 'buchungen.valuta';
        break;
      case 'buchungsdatum_von':
        $rel = '<=';
        $key = 'buchungen.buchungsdatum';
        break;
      case 'buchungsdatum_bis':
        $rel = '>=';
        $key = 'buchungen.buchungsdatum';
        break;
      case 'geschaeftsbereiche_id':
        $key = 'kontoklassen.geschaeftsbereich';
        $val = sql_unique_value( 'kontoklassen', $key, $val );
        break;
      default:
        error( "undefined key: $key" );
    }
    $atom[ -1 ] = 'cooked_atom';
  }

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = '';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'buchungen', $filters, $selects, $joins, $orderby, $groupby );
}

function sql_buchungen( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'valuta';
  $sql = sql_query_buchungen( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_buchung( $filters = array(), $default = false ) {
  $sql = sql_query_buchungen( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_buchungen( $filters ) {
  foreach( sql_buchungen( $filters ) as $buchung ) {
    $buchungen_id = $buchung['buchungen_id'];
    sql_delete_posten( array( 'buchungen_id' => $buchungen_id ) );
    sql_delete( 'buchungen', array( 'buchungen_id' => $buchungen_id ) );
    logger( "sql_delete_buchungen: buchung $buchungen_id geloescht", 'delete' );
  }
}

function sql_buche( $buchungen_id, $valuta, $vorfall, $posten ) {
  global $geschaeftsjahr_max, $geschaeftsjahr_abgeschlossen;

  logger( "sql_buche: $buchungen_id", 'buchung' );

  $geschaeftsjahr = 0;
  $saldoH = 0;
  $saldoS = 0;
  $is_vortrag = 0;
  $nS = $nH = 0;
  foreach( $posten as $p ) {
    $uk = sql_one_unterkonto( $p['unterkonten_id'] );
    need( ! $uk['unterkonto_geschlossen'], 'buchung nicht moeglich: konto ist geschlossen' );
    if( $uk['vortragskonto'] ) {
      $is_vortrag = 1;
      $valuta = 100;
    }
    if( $geschaeftsjahr ) {
      need( $geschaeftsjahr == $uk['geschaeftsjahr'], "buchung nicht moeglich: unterschiedliche geschaeftsjahre involviert ({$uk['unterkonten_id']})" );
    } else {
      $geschaeftsjahr = $uk['geschaeftsjahr'];
    }
    switch( $p['art'] ) {
      case 'H':
        $nH++;
        $saldoH += $p['betrag'];
        break;
      case 'S':
        $nS++;
        $saldoS += $p['betrag'];
        break;
      default:
        error( 'sql_buche: undefinierter Posten' );
    }
  }
  need( $nS && $nH, 'buchung nicht moeglich: brauche S und H posten' );
  need( $geschaeftsjahr > $geschaeftsjahr_abgeschlossen, 'buchung nicht moeglich: geschaeftsjahr ist abgeschlossen' );
  $values_buchungen = array(
    'valuta' => $valuta
  , 'vorfall' => $vorfall
  , 'buchungsdatum' => $GLOBALS['today_mysql']
  );
  if( $buchungen_id ) {
    sql_update( 'buchungen', $buchungen_id, $values_buchungen );
    sql_delete( 'posten', array( 'buchungen_id' => $buchungen_id ) );
    logger( "sql_buche: update: $buchungen_id", 'buchung_update' );
  } else {
    $buchungen_id = sql_insert( 'buchungen', $values_buchungen );
    logger( "sql_buche: inserted: $buchungen_id", 'buchung_neu' );
  }
  foreach( $posten as $v ) {
    $v = parameters_explode( $v, array( 'keep' => 'betrag=0.0,unterkonten_id,art,beleg=' ) );
    $v['buchungen_id'] = $buchungen_id;
    sql_insert( 'posten', $v );
  }
  if( ! $is_vortrag )
    sql_update( 'leitvariable', array( 'name' => 'valuta_letzte_buchung' ), array( 'value' => $valuta ) );

  if( $geschaeftsjahr < $geschaeftsjahr_max ) {
    sql_saldenvortrag_loeschen( $geschaeftsjahr + 1 );
    sql_saldenvortrag_buchen( $geschaeftsjahr );
  }
  return $buchungen_id;
}

////////////////////////////////////
//
// geschaeftsjahre-funktionen
//
////////////////////////////////////

function sql_query_geschaeftsjahre( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $groupby = 'hauptkonten.geschaeftsjahr';

  $joins['kontoklassen'] = 'kontoklassen_id';
  $selects = sql_default_selects(
    array( 'hauptkonten', 'kontoklassen' )
  , array( 'kontoklassen.cn' => 'kontoklassen_cn' )
  );
  $selects[] = "COUNT(*) AS hauptkonten_count";
  $selects[] = "SUM( ( SELECT COUNT(*) FROM unterkonten WHERE unterkonten.hauptkonten_id = hauptkonten.hauptkonten_id ) ) AS unterkonten_count";

  $filters = sql_canonicalize_filters( 'hauptkonten', $filters_in, $joins );

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = '';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'hauptkonten', $filters, $selects, $joins, $orderby, $groupby );
}

function sql_geschaeftsjahre( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'hauptkonten.geschaeftsjahr';
  $sql = sql_query_geschaeftsjahre( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}


function sql_saldenvortrag_loeschen( $jahr ) {
  global $geschaeftsjahr_max;

  logger( "sql_saldenvortrag_loeschen: $jahr", 'delete' );

  if( $jahr < $geschaeftsjahr_max )
    sql_saldenvortrag_loeschen( $jahr + 1 );

  $buchungen = sql_buchungen( array( 'geschaeftsjahr' => $jahr, 'valuta' => 100 ) );
  foreach( $buchungen as $b ) {
    sql_delete( 'posten', array( 'buchungen_id' => $b['buchungen_id'] ) );
    sql_delete( 'buchungen', $b['buchungen_id'] );
  }
  logger( "sql_saldenvortrag_loeschen: ".count( $buchungen )." Vortragsbuchungen in $jahr geloescht", 'delete' );
}


function sql_saldenvortrag_buchen( $jahr ) {
  global $geschaeftsjahr_max;

  logger( "sql_saldenvortrag_buchen: $jahr", 'vortrag' );

  $vortrag = array();
  $posten = array();

  need( $jahr < $geschaeftsjahr_max );

  $unterkonten = sql_unterkonten( array( 'geschaeftsjahr' => $jahr ) );
  foreach( $unterkonten as $uk ) {
    $saldo = $uk['saldo'];

    if( $uk['kontenkreis'] === 'B' ) {
      if( $uk['unterkonto_geschlossen'] ) {
        continue;
      }
      need( ( $uk_neu_id = $uk['folge_unterkonten_id'] ), 'kein folgekonto vorhanden' );
      $posten[] = array(
        'beleg' => "Vortrag aus $jahr am " . $GLOBALS['today_mysql']
      , 'art' => ( ( ( $saldo > 0 ) Xor ( $uk['seite'] === 'A' ) ) ? 'H' : 'S' )
      , 'betrag' => abs( $saldo )
      , 'unterkonten_id' => $uk_neu_id
      );

    } else {
      if( abs( $saldo ) < 0.005 ) {
        continue;
      }
      $gb = $uk['geschaeftsbereich'];
      $vortrag[ $gb ] = adefault( $vortrag, $gb, 0.0 ) + ( ( $uk['seite'] === 'P' ) ? $saldo : - $saldo );
    }
  }

  foreach( $vortrag as $gb => $saldo ) {
    $vortragshauptkonten = sql_hauptkonten( array( 'geschaeftsjahr' => $jahr + 1, 'vortragskonto' => $gb ) );
    need( count( $vortragshauptkonten ) == 1, "kein eindeutiges vortrags-hauptkonto angelegt fuer geschaeftsbereich $gb" );
    $vortrags_hk_id = $vortragshauptkonten[0]['hauptkonten_id'];
    // suche vortragskonten, die keine folgekonten sind:
    //
    $vortragsunterkonten = sql_unterkonten( array( 'hauptkonten_id' => $vortrags_hk_id, 'vortragsjahr' => $jahr ) );
    if( $vortragsunterkonten ) {
      $vortrags_uk_id = $vortragsunterkonten[0]['unterkonten_id'];
    } else {
      $vortrags_uk_id = sql_insert( 'unterkonten', array(
        'cn' => "Vortrag aus Jahr $jahr"
      , 'hauptkonten_id' => $vortrags_hk_id
      , 'vortragsjahr' => $jahr
      ) );
      logger( "sql_saldenvortrag_buchen: unterkonto $vortrags_uk_id fuer vortrag $gb aus $jahr angelegt", 'vortrag' );
    }
    $posten[] = array(
      'beleg' => "Jahresergebnis $jahr"
    , 'art' => ( $saldo >= 0 ? 'H' : 'S' )
    , 'betrag' => abs( $saldo )
    , 'unterkonten_id' => $vortrags_uk_id
    );
  }
  sql_buche( 0, 100, "Vortrag aus $jahr", $posten ); // loest ggf. weitere vortraege aus!
  logger( "sql_saldenvortrag_buchen: ".count( $posten )." Vortragsposten gebucht", 'vortrag' );
}

////////////////////////////////////
//
// posten-funktionen
//
////////////////////////////////////

function sql_query_posten( $op, $filters_in = array(), $using = array(), $orderby = false, $limit_from = 0, $limit_count = 0 ) {
  $joins = array();
  $groupby = 'posten.posten_id';

  $joins['buchungen'] = 'buchungen_id';
  $joins['unterkonten'] = 'unterkonten_id';
  $joins['hauptkonten'] = 'hauptkonten_id';
  $joins['kontoklassen'] = 'kontoklassen_id';
  $joins['LEFT people'] = 'people_id';
  $joins['LEFT things'] = 'things_id';

  $selects = sql_default_selects(
    array( 'posten', 'unterkonten', 'hauptkonten', 'kontoklassen', 'buchungen' )
  , array( 'kontoklassen.cn' => 'kontoklassen_cn', 'buchungen.beleg' => 'buchungen_beleg'
         , 'unterkonten.kommentar' => 'unterkonten_kommentar'
         , 'hauptkonten.kommentar' => 'hauptkonten_kommentar'
    )
  );
  $selects[] = 'people.cn as people_cn';
  $selects[] = 'things.cn as things_cn';

  $filters = sql_canonicalize_filters( 'posten', $filters_in, $joins );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ]; $key = & $atom[ 1 ]; $val = & $atom[ 2 ];
    switch( $key ) {  // otherwise, check for special cases:
      case 'geschaeftsbereiche_id':
        $key = 'kontoklassen.geschaeftsbereich';
        $val = sql_unique_value( 'kontoklassen', $key, $val );
        break;
      case 'valuta_von':
        $rel = '>=';
        $key = 'buchungen.valuta';
        break;
      case 'valuta_bis':
        $rel = '<=';
        $key = 'buchungen.valuta';
        break;
      default:
        error( "undefined key: $key" );
    }
    $atom[ -1 ] = 'cooked_atom';
  }

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = '';
      break;
    case 'SALDO':
      need( isset( $filters['art'] ) );
      $op = 'SELECT';
      $groupby = '1';
      $selects = 'IFNULL( SUM(betrag), 0.0 ) AS saldo';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'posten', $filters, $selects, $joins, $orderby, $groupby, $limit_from, $limit_count );
}
 
function sql_posten( $filters = array(), $orderby = true, $limit_from = 0, $limit_count = 0 ) {
  if( $orderby === true )
    $orderby = 'valuta';
  $sql = sql_query_posten( 'SELECT', $filters, array(), $orderby, $limit_from, $limit_count );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_posten( $filters = array(), $default = false ) {
  $sql = sql_query_posten( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_posten_saldo( $filters = array() ) {
  $sql = sql_query_posten( 'SALDO', $filters );
  return sql_do_single_field( $sql, 'saldo' );
}

function sql_delete_posten( $filters = array() ) {
  foreach( sql_posten( $filters ) as $p ) {
    $id = $p['posten_id'];
    sql_update( 'zahlungsplan' , array( 'posten_id' => $id ) , array( 'posten_id' => 0 ) );
    sql_delete( 'posten', $id );
  }
}

////////////////////////////////////
//
// darlehen-funktionen:
//
////////////////////////////////////

function sql_query_darlehen( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $groupby = 'darlehen.darlehen_id';

  $joins[] = 'LEFT unterkonten AS darlehenkonto ON darlehenkonto.unterkonten_id = darlehen.darlehen_unterkonten_id';
  $joins[] = 'LEFT hauptkonten ON hauptkonten.hauptkonten_id = darlehenkonto.hauptkonten_id';
  $joins[] = 'LEFT kontoklassen  ON hauptkonten.kontoklassen_id = kontoklassen.kontoklassen_id';
  $joins[] = 'LEFT unterkonten AS zinskonto ON zinskonto.unterkonten_id = darlehen.zins_unterkonten_id';
  $joins[] = 'LEFT people ON darlehenkonto.people_id = people.people_id';

  $selects = sql_default_selects(
    'darlehen,people,hauptkonten,kontoklassen'
  , array( 'hauptkonten.kommentar' => false, 'unterkonten.kommentar' => false
  , 'kontoklassen.cn' => 'kontoklassen_cn', 'people.cn' => 'people_cn'
  ) );
  // debug( $selects, 'selects' );

  // $selects[] = '( SELECT cn FROM unterkonten WHERE unterkonten_id = darlehen_unterkonten_id ) AS darlehen_unterkonten_cn';
  // $selects[] = '( SELECT cn FROM unterkonten WHERE unterkonten_id = zins_unterkonten_id ) AS zins_unterkonten_cn';
  $selects[] = 'darlehenkonto.cn as darlehen_unterkonten_cn';
  $selects[] = 'zinskonto.cn as zins_unterkonten_cn';
  $selects[] = 'people.cn as people_cn';
  $selects[] = "( SELECT COUNT(*) FROM zahlungsplan WHERE ( zahlungsplan.darlehen_id = darlehen.darlehen_id ) ) as zahlungsplan_count";

  $filters = sql_canonicalize_filters( 'darlehen,hauptkonten,people', $filters_in, $joins );

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = '';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'darlehen', $filters, $selects, $joins, $orderby, $groupby );
}

function sql_darlehen( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'geschaeftsjahr,people_cn';
  $sql = sql_query_darlehen( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_darlehen( $filters = array(), $default = false ) {
  $sql = sql_query_darlehen( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_darlehen( $filters ) {
  foreach( sql_darlehen( $filters ) as $d ) {
    $id = $d['darlehen_id'];
    logger( "sql_delete_darlehen: [$id]", 'delete' );
    sql_delete( 'darlehen', $id );
    sql_delete( 'zahlungsplan', array( 'darlehen_id' => $id ) );
  }
}

////////////////////////////////////
//
// zahlungsplan-funktionen
//
////////////////////////////////////

function sql_query_zahlungsplan( $op, $filters_in = array(), $using = array(), $orderby = 'geschaeftsjahr, valuta', $limit_from = 0, $limit_count = 0 ) {
  $joins = array();
  $groupby = 'zahlungsplan.zahlungsplan_id';

  $joins['darlehen'] = 'darlehen_id';
  $joins['LEFT unterkonten'] = 'unterkonten_id';
  $joins['LEFT people'] = 'people_id';
  $joins['LEFT posten'] = 'posten_id';
  $joins['LEFT buchungen'] = 'buchungen_id';

  $selects = sql_default_selects(
    array( 'zahlungsplan', 'darlehen', 'buchungen' )
  , array( 'darlehen.kommentar' => 'darlehen_kommentar', 'buchungen.valuta' => 'buchungen_valuta' )
  );
  $selects[] = 'unterkonten.cn as unterkonten_cn';
  $selects[] = 'people.cn as people_cn';
  $selects[] = 'people_id';
  $selects['buchungen.valuta'] = false;

  $filters = sql_canonicalize_filters( 'zahlungsplan', $filters_in, $joins );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    switch( $key ) {  // otherwise, check for special cases:
      case 'valuta_von':
        $rel = '>=';
        $key = 'zahlungsplan.valuta';
        break;
      case 'valuta_bis':
        $rel = '<=';
        $key = 'zahlungsplan.valuta';
        break;
      default:
        error( "undefined key: $key" );
    }
    $atom[ -1 ] = 'cooked_atom';
  }

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = '';
      break;
    case 'SALDO':
      need( isset( $filters['art'] ) );
      $op = 'SELECT';
      $groupby = '1';
      $selects = 'IFNULL( SUM(betrag), 0.0 ) AS saldo';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'zahlungsplan', $filters, $selects, $joins, $orderby, $groupby, $limit_from, $limit_count );
}
 
function sql_zahlungsplan( $filters = array(), $orderby = true, $limit_from = 0, $limit_count = 0 ) {
  if( $orderby === true )
    $orderby = 'valuta,art,zins';
  $sql = sql_query_zahlungsplan( 'SELECT', $filters, array(), $orderby, $limit_from, $limit_count );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_zahlungsplan( $filters = array(), $default = false ) {
  $sql = sql_query_zahlungsplan( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_zahlungsplan( $filters ) {
  foreach( sql_zahlungsplan( $filters ) as $zp ) {
    sql_delete( 'zahlungsplan', $zp['zahlungsplan_id'] );
  }
}

// sql_zahlungsplan_berechnen: bisher: nur annuitaetendarlehen: theorie:
// (!! nur korrekt ohne vorzeitige zinsausschuettung, also fuer i == s !!)
//   s : jahr zinslauf start
//   i : jahr zinsauszahlung start (i >= s)
//   t : jahr tilgung start (t >= s)
//   e : jahr tilgung ende (e >= t)
//   k_j: schuld am anfang jahr j
//   f := t - s  anzahl tilgungsfreie jahre (f >= 0)
//   n := e - t + 1  anzahl raten (n >= 1)
//   z : zinssatz
//   a : annuitaet
// es gilt:
//   k_t = k_s * (1+z)^f
//   k_{t+1} = k_t * (1+z) - a
//   ...
//   k_{t+n} = k_t * (1+z)^n - a * \sum_{l=0}^{n-1} (1+z)^l
//           = k_s * (1+z)^(f+n) - a * ((1+z)^n - 1) / z
// mit der bedingung k_{t+n} == k_{e+1} = 0 folgt:
//   a = k_s * z * (1+z)^{f+n} / ( (1+z)^n - 1 )
//
function sql_zahlungsplan_berechnen( $darlehen_id, $opts = array() ) {
  logger( "sql_zahlungsplan_berechnen: [$darlehen_id]", 'zahlungsplan' );

  $opts = parameters_explode( $opts );
  $darlehen = sql_one_darlehen( $darlehen_id );

  $darlehen_unterkonten_id = $darlehen['darlehen_unterkonten_id'];
  if( ! ( $zins_unterkonten_id = $darlehen['zins_unterkonten_id'] ) )
    $zins_unterkonten_id = $darlehen_unterkonten_id;

  // berechnen:

  $jahr_start = max( adefault( $opts, 'jahr_start', 0 ), $darlehen['geschaeftsjahr_darlehen'] );

  $s = max( $jahr_start, $darlehen['geschaeftsjahr_zinslauf_start'] );
  $i = max( $s, $darlehen['geschaeftsjahr_zinsauszahlung_start'] );
  $t = max( $s, $darlehen['geschaeftsjahr_tilgung_start'] );
  $e = $darlehen['geschaeftsjahr_tilgung_ende'];
  $z = $darlehen['zins_prozent'] / 100.0;

  $f = $t - $s;
  $n = $e - $t + 1;

  need( $f >= 0 );
  need( $n > 0 );

  need( ( $darlehen_unterkonten_id = sql_get_folge_unterkonten_id( $darlehen_unterkonten_id, $jahr_start ) ) );
  need( ( $zins_unterkonten_id = sql_get_folge_unterkonten_id( $zins_unterkonten_id, $jahr_start ) ) );

  $zahlungsplan = array();
  if( $jahr_start == $darlehen['geschaeftsjahr_darlehen'] ) {
    $stand_darlehen = $darlehen['betrag_abgerufen'];
    $stand_zins = 0.0;
    $zahlungsplan[] = array(
      'darlehen_id' => $darlehen_id
    , 'geschaeftsjahr' => $darlehen['geschaeftsjahr_darlehen']
    , 'kommentar' => "Einzug Darlehensbetrag"
    , 'betrag' => $stand_darlehen
    , 'unterkonten_id' => $darlehen_unterkonten_id
    , 'art' => 'H'
    , 'zins' => 0
    , 'valuta' => $darlehen['valuta_betrag_abgerufen']
    );
  } else {
    $stand_darlehen = sql_unterkonten_saldo( "$darlehen_unterkonten_id,valuta=0100" );
    if( $zins_unterkonten_id != $darlehen_unterkonten_id ) {
      $stand_zins = sql_unterkonten_saldo( "$zins_unterkonten_id,valuta=0100" );
    } else {
      $stand_zins = 0.0;
    }
  }

  // $a berechnen - nur korrekt ohne vorzeitige ausschuettung, daher unten in der schleife nochmal!
  //   $k_s = $stand_darlehen + $stand_zins;
  //   if( $z >= 0.0001 ) {
  //     $a = r2( $k_s * pow( 1.0 + $z, $f + $n ) * $z / ( pow( 1.0 + $z, $n ) - 1.0 ) );
  //   } else {
  //     $a = r2( $k_s / $n );
  //   }

  
  if( adefault( $opts, 'delete' ) ) {
    sql_delete_zahlungsplan( "darlehen_id=$darlehen_id,geschaeftsjahr>=$jahr_start" );
  } else {
    need( ! sql_zahlungsplan( "darlehen_id=$darlehen_id,geschaeftsjahr>=$jahr_start" ), 'bereits zahlungsplan vorhanden' );
  }

  for( $j = $s; $j <= $e; $j++ ) {
    debug( $j, 'j' );
    debug( $stand_darlehen, 'stand_darlehen' );
    debug( $stand_zins, 'stand_zins' );
  //   debug( $z, 'z' );
  //   debug( $f, 'f' );
  //   debug( $n, 'n' );
  //   debug( $a, 'a' );
    // need( ( $darlehen_unterkonten_id = sql_get_folge_unterkonten_id( $darlehen_unterkonten_id, $j ) ) );
    // need( ( $zins_unterkonten_id = sql_get_folge_unterkonten_id( $zins_unterkonten_id, $j ) ) );

    $zins_neu = 0.0;
    if( ( $j >= $s ) && ( $z >= 0.0001 ) ) {
      $zins_neu = r2 ( 0.001 + ( $stand_zins + $stand_darlehen ) * $z );
      $zahlungsplan[] = array(
        'darlehen_id' => $darlehen_id
      , 'geschaeftsjahr' => $j
      , 'kommentar' => "Zinsgutschrift Jahr $j"
      , 'betrag' => $zins_neu
      , 'unterkonten_id' => $zins_unterkonten_id   // aus darlehen - folgekonto braucht noch nicht zu existieren!
      , 'art' => 'H'
      , 'zins' => 1
      , 'valuta' => '1231'
      );
    }

    if( $j >= $darlehen['geschaeftsjahr_tilgung_start'] ) {

      if( $j == $e ) {
        $tilgung = $stand_darlehen + $stand_zins + $zins_neu;
      } else {
        $k_s = $stand_darlehen + $stand_zins;
        $n = $e - $j + 1;
        if( $j == $t ) {
          if( $z >= 0.0001 ) {
            $a = r2( $k_s * pow( 1.0 + $z, $n ) * $z / ( pow( 1.0 + $z, $n ) - 1.0 ) );
          } else {
            $a = r2( $k_s / $n );
          }
        }
        $tilgung = $a;
      }
      $tilgung_alt = $tilgung - $zins_neu;
      need( $tilgung_alt > 0, 'ueberschuldet!' );
      $tilgung_zins_alt = r2( $stand_zins / ( $stand_darlehen + $stand_zins ) * $tilgung_alt );

      $tilgung_darlehen = $tilgung_alt - $tilgung_zins_alt;
      $tilgung_zins = $tilgung_zins_alt + $zins_neu;

      if( $tilgung_darlehen > 0.005 ) {
        $zahlungsplan[] = array(
          'darlehen_id' => $darlehen_id
        , 'geschaeftsjahr' => $j
        , 'kommentar' => "Tilgung Darlehen Jahr $j"
        , 'betrag' => $tilgung_darlehen
        , 'unterkonten_id' => $darlehen_unterkonten_id
        , 'art' => 'S'
        , 'zins' => 0
        , 'valuta' => '1231'
        );
      }
      if( $tilgung_zins > 0.005 ) {
        $zahlungsplan[] = array(
          'darlehen_id' => $darlehen_id
        , 'geschaeftsjahr' => $j
        , 'kommentar' => "Auszahlung Zins Jahr $j"
        , 'betrag' => $tilgung_zins
        , 'unterkonten_id' => $zins_unterkonten_id
        , 'art' => 'S'
        , 'zins' => 1
        , 'valuta' => '1231'
        );
      }
      $stand_darlehen -= $tilgung_darlehen;
      $stand_zins = $stand_zins + $zins_neu - $tilgung_zins;

    } else if( $j >= $darlehen['geschaeftsjahr_zinsauszahlung_start'] ) {

      $tilgung_zins = $zins_neu;
      if( $tilgung_zins > 0.005 ) {
        $zahlungsplan[] = array(
          'darlehen_id' => $darlehen_id
        , 'geschaeftsjahr' => $j
        , 'kommentar' => "Auszahlung Zins Jahr $j"
        , 'betrag' => $tilgung_zins
        , 'unterkonten_id' => $zins_unterkonten_id
        , 'art' => 'S'
        , 'zins' => 1
        , 'valuta' => '1231'
        );
      }

    } else {
      $stand_zins += $zins_neu;

    }

  }

  foreach( $zahlungsplan as $zp ) {
    sql_insert( 'zahlungsplan', $zp );
  }

}


////////////////////////////////////
//
// people-funktionen:
//
////////////////////////////////////


function sql_query_people( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $selects = sql_default_selects( 'people' );
  $selects[] = '( SELECT COUNT(*) FROM people_people_relation WHERE group_people_id = people_id ) AS groupmembers_count';
  $selects[] = '( SELECT COUNT(*) FROM people_people_relation WHERE member_people_id = people_id ) AS memberships_count';
  $joins = array();
  // $joins['LEFT unterkonten'] = 'unterkonten_id';
  // $joins['LEFT hauptkonten'] = 'hauptkonten_id';
  // $joins['LEFT kontoklassen'] = 'kontoklassen_id';
  $groupby = 'people.people_id';

  $filters = sql_canonicalize_filters( 'people', $filters_in );

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      break;
    default:
      error( "undefined op: $op" );
  }
  $s = sql_query( $op, 'people', $filters, $selects, $joins, $orderby );
  return $s;
}


?>
