<?php

////////////////////////////////////
//
// things-funktionen:
//
////////////////////////////////////

function sql_query_things( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $filters = array();
  $groupby = 'things.things_id';

  $selects = sql_default_selects('things');
  $joins['LEFT unterkonten'] = 'things_id';
  $joins['LEFT posten'] = 'unterkonten_id';
  // $joins['LEFT hauptkonten'] = 'hauptkonten_id';
  // $joins['LEFT kontoklassen'] = 'kontoklassen_id';
  $selects[] = 'IFNULL( SUM(posten.betrag), 0.0 ) AS wert';
  $selects[] = 'unterkonten.unterkonten_id';

  foreach( sql_canonicalize_filters( 'things', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'things.', 7 ) == 0 ) { 
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      case 'posten.valuta':
      case 'valuta':
        $filters['posten.valuta'] = $cond;
        break;
      case 'where':
        $filters[] = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
  }
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

function sql_one_thing( $filters = array(), $allownull = false ) {
  $sql = sql_query_things( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allownull );
}

function sql_things_wert( $filters = array() ) {
  $sql = sql_query_things( 'WERT', $filters );
  return sql_do_single_field( $sql, 'wert' );
}

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
  $filters = array();

  $selects = sql_default_selects('kontoklassen');

  foreach( sql_canonicalize_filters( 'kontoklassen', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'kontoklassen.', 13 ) == 0 ) { 
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {
      case 'geschaeftsbereiche_id':
        $filters['geschaeftsbereich'] = sql_unique_value( 'kontoklassen', 'geschaeftsbereich', $cond );
        break;
      default:
        error( "undefined key: $key" );
    }
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

function sql_one_kontoklasse( $filters = array(), $allownull = false ) {
  $sql = sql_query_kontoklassen( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allownull );
}


////////////////////////////////////
//
// bankkonten-funktionen:
//
////////////////////////////////////

function sql_query_bankkonten( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $filters = array();
  $groupby = 'bankkonten.bankkonten_id';

  $selects = sql_default_selects(
    array( 'bankkonten', 'kontoklassen', 'unterkonten' )
  , array( 'kontoklassen.cn' => 'kontoklassen_cn', 'unterkonten.cn' => 'unterkonten_cn' )
  );
  $joins['LEFT unterkonten'] = 'bankkonten_id';
  $joins['LEFT hauptkonten'] = 'hauptkonten_id';
  $joins['LEFT kontoklassen'] = 'kontoklassen_id';
  $joins['LEFT posten'] = 'unterkonten_id';
  $selects[] = 'IFNULL( SUM(posten.betrag), 0.0 ) AS saldo';

  foreach( sql_canonicalize_filters( 'bankkonten', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'bankkonten.', 11 ) == 0 ) { 
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      case 'posten.valuta':
      case 'valuta':
        $filters['posten.valuta'] = $cond;
        break;
      case 'where':
        $filters[] = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
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
      $op = 'SELECT';
      $groupby = '1';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'bankkonten', $filters, $selects, $joins, $orderby, $groupby );
}

function sql_bankkonten( $filters = array(), $orderby = 'bankkonten.cn' ) {
  if( $orderby === true )
    $orderby = 'bankkonten.cn';
  $sql = sql_query_bankkonten( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_bankkonto( $filters = array(), $allownull = false ) {
  $sql = sql_query_bankkonten( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allownull );
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

function sql_query_hauptkonten( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $filters = array();
  $groupby = 'hauptkonten.hauptkonten_id';

  $joins['kontoklassen'] = 'kontoklassen_id';
  $selects = sql_default_selects(
    array( 'hauptkonten', 'kontoklassen' )
  , array( 'kontoklassen.cn' => 'kontoklassen_cn' )
  );
  $selects[] = "( SELECT COUNT(*) FROM unterkonten WHERE unterkonten.hauptkonten_id
                                                       = hauptkonten.hauptkonten_id ) as unterkonten_count";

  foreach( sql_canonicalize_filters( 'hauptkonten', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'hauptkonten.', 12 ) == 0 ) { 
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      case 'geschaeftsbereiche_id':
        $key = 'geschaeftsbereich';
        $cond = sql_unique_value( 'kontoklassen', $key, $cond );
      case 'seite':
      case 'kontoart':
      case 'personenkonto':
      case 'sachkonto':
      case 'bankkonto':
      case 'vortragskonto':
        $filters[$key] = $cond;
        break;
      case 'where':
        $filters[] = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
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

function sql_hauptkonten( $filters = array(), $orderby = 'kontoklassen.seite, hauptkonten.rubrik, hauptkonten.titel' ) {
  if( $orderby === true )
    $orderby = 'kontoklassen.seite, hauptkonten.rubrik, hauptkonten.titel';
  $sql = sql_query_hauptkonten( 'SELECT', $filters, array(), $orderby );
  $a = mysql2array( sql_do( $sql ) );
  // prettydump( $a );
  return $a;
}

function sql_one_hauptkonto( $filters = array(), $allownull = false ) {
  $sql = sql_query_hauptkonten( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allownull );
}


function sql_delete_hauptkonten( $filters, $check = false ) {

  $hauptkonten = sql_hauptkonten( $filters, 'geschaeftsjahr' );
  $problems = '';

  foreach( $hauptkonten as $hauptkonto ) {
    $hauptkonten_id = $hauptkonto['hauptkonten_id'];

    if( sql_hauptkonten( array( 'folge_hauptkonten_id' => $hauptkonten_id ) ) ) {
      $problems = 'loeschen nicht moeglich: konto ist folgekonto';
    }

    for( $id = $hauptkonten_id; $id; $id = $hk['folge_hauptkonten_id'] ) {
      $hk = sql_one_hauptkonto( $id );
      if( $hk['unterkonten_count'] > 0 ) {
        $problems = 'loeschen nicht moeglich: unterkonten vorhanden';
      }
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

function sql_hauptkonto_close( $hauptkonten_id, $check = false ) {

  $problems = '';

  if( sql_unterkonten( array( 'hauptkonten_id' => $hauptkonten_id, 'unterkonto_geschlossen' => 0 ) ) ) {
    $problems = 'schliessen nicht moeglich: unterkonten noch nicht geschlossen';
  }
  $hk = sql_one_hauptkonto( $hauptkonten_id );
  $folge_hk_id = $hk['folge_hauptkonten_id'];
  for( $id = $folge_hk_id; $id; $hk = sql_one_hauptkonto( $id ), $id = $hk['folge_hauptkonten_id'] ) {
    if( sql_unterkonten( array( 'hauptkonten_id' => $id ) ) ) {
      $problems = 'schliessen nicht moeglich: folgekonten mit unterkonten vorhanden';
    }
  }

  if( $check ) {
    return $problems;
  }

  logger( "sql_hauptkonto_close: $hauptkonten_id: [$problems]" );
  need( ! $problems, $problems );

  sql_update( 'hauptkonten', $hauptkonten_id, array( 'hauptkonto_geschlossen' => 1, 'folge_hauptkonten_id' => 0 ) );
  if( $folge_hk_id ) {
    sql_delete_hauptkonten( $folge_hk_id );
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
  $filters = array();
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
  $selects[] = 'people.cn as people_cn';
  $selects[] = 'things.cn as things_cn';
  $selects[] = 'bankkonten.bank as bankkonten_bank';
  $selects[] = "IFNULL( SUM( posten.betrag * IF( posten.art = 'S', 1, 0 ) ), 0.0 ) as saldoS";
  $selects[] = "IFNULL( SUM( posten.betrag * IF( posten.art = 'H', 1, 0 ) ), 0.0 ) as saldoH";
  $selects[] = "( IFNULL( SUM( posten.betrag * IF( posten.art = 'H', 1, -1 ) ), 0.0 ) 
                * IF( kontoklassen.seite = 'P', 1, -1 ) ) as saldo";

  foreach( sql_canonicalize_filters( 'unterkonten', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'unterkonten.', 12 ) == 0 ) { 
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      case 'geschaeftsbereiche_id':
        $filters['geschaeftsbereich'] = sql_unique_value( 'kontoklassen', 'geschaeftsbereich', $cond );
        break;
      case 'seite':
      case 'kontoart':
      case 'personenkonto':
      case 'sachkonto':
      case 'bankkonto':
      case 'vortragskonto':
        $filters['kontoklassen.'.$key] = $cond;
        break;
      case 'kontoklassen_id':
        $filters['hauptkonten.kontoklassen_id'] = $cond;
        break;
      case 'where':
        $filters[] = $cond;
        break;
      case 'geschaeftsjahr':
        $filters['hauptkonten.geschaeftsjahr'] = $cond;
        break;
      case 'stichtag':
        $filters[] = "buchungen.valuta <= '$cond'";
        break;
      default:
        error( "undefined key: $key" );
    }
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
      // $selects = 'IFNULL( SUM(posten.betrag), 0.0 ) AS saldo';
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

function sql_one_unterkonto( $filters = array(), $allownull = false ) {
  $sql = sql_query_unterkonten( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allownull );
}

function sql_unterkonten_saldo( $filters = array() ) {
  $sql = sql_query_unterkonten( 'SALDO', $filters );

  $saldo = sql_do_single_field( $sql, 'saldo', true );

  if(0){
  open_div( 'warn' );
    echo 'sql_unterkonten_saldo';
    prettydump( $sql );
    prettydump( $saldo );
    prettydump( $saldoS );
    prettydump( $saldoH );
  close_div();
  }

  if( ! $saldo )
    return 0.0;

  return $saldo;

  if( ! $row )
    return 0.0;
  switch( $row['seite'] ) {
    case 'A':
      $saldo = $row['saldoS'] - $row['saldoH'];
      break;
    case 'P':
      $saldo = $row['saldoH'] - $row['saldoS'];
      break;
  }
  return $saldo;
}

function sql_unterkonto_close( $unterkonten_id, $check = false ) {

  $problems = '';

  $uk = sql_one_unterkonto( $unterkonten_id );
  $folge_uk_id = $uk['folge_unterkonten_id'];
  if( abs( $uk['saldo'] ) > 0.005 ) {
    $problems = 'schliessen nicht moeglich: konto nicht ausgeglichen';
  }
  for( $id = $folge_uk_id; $id; $uk = sql_one_unterkonto( $id ), $id = $uk['folge_unterkonten_id'] ) {
    if( sql_posten( array( 'unterkonten_id' => $id ) ) ) {
      $problems = 'schliessen nicht moeglich: folgekonten mit posten vorhanden';
    }
  }
  if( $check ) {
    return $problems;
  }

  logger( "sql_unterkonto_close: $unterkonten_id: [$problems]" );
  need( ! $problems, $problems );

  sql_update( 'unterkonten', $unterkonten_id, array( 'unterkonto_geschlossen' => 1, 'folge_unterkonten_id' => 0 ) );
  if( $folge_uk_id ) {
    sql_delete_unterkonten( $folge_uk_id );
  }
}

function sql_delete_unterkonten( $filters, $check = false ) {

  $unterkonten = sql_unterkonten( $filters, 'geschaeftsjahr' );
  $problems = '';

  foreach( $unterkonten as $uk ) {
    $unterkonten_id = $uk['unterkonten_id'];

    if( sql_unterkonten( array( 'folge_unterkonten_id' => $unterkonten_id ) ) ) {
      $problems = 'loeschen nicht moeglich: konto ist folgekonto';
    }

    for( $id = $unterkonten_id; $id; $id = $uk['folge_unterkonten_id'] ) {
      $uk = sql_one_unterkonto( $id );
      if( sql_posten( array( 'unterkonten_id' => $id ) ) ) {
        $problems = 'loeschen nicht moeglich: posten vorhanden';
      }
      // need( ! sql_darlehen( array( 'unterkonten_id' => $uk['unterkonten_id'] ) ), 'loeschen nicht moeglich: darlehen vorhanden' );
    }
  }

  if( $check ) {
    return $problems;
  }

  need( ! $problems, $problems );

  $things = array();
  $bankkonten = array();
  foreach( $unterkonten as $uk ) {
    $unterkonten_id = $uk['unterkonten_id'];
    logger( "sql_delete_unterkonten: $unterkonten_id", 'delete' );
    while( $unterkonten_id ) {
      $uk = sql_one_unterkonto( $unterkonten_id );
      sql_delete( 'unterkonten', $unterkonten_id );
      if( $uk['bankkonto'] )
        $bankkonten[] = $uk['bankkonten_id'];
      if( $uk['sachkonto'] )
        $things[] = $uk['things_id'];
      $unterkonten_id = $uk['folge_unterkonten_id'];
    }
  }

  // garbage collection:
  //
  foreach( $things as $things_id ) {
    sql_delete_things( $things_id, 'if_dangling' );
  }
  foreach( $bankkonten as $bankkonten_id ) {
    sql_delete_bankkonten( $bankkonten_id, 'if_dangling' );
  }
}

function sql_unterkonto_folgekonto_anlegen( $unterkonten_id ) {
  global $tables;

  logger( "sql_unterkonto_folgekonto_anlegen: $unterkonten_id" );

  $uk = sql_one_unterkonto( $unterkonten_id );
  $hk = sql_one_hauptkonto( $uk['hauptkonten_id'] );

  need( ! $uk['unterkonto_geschlossen'], 'Unterkonto ist geschlossen' );

  $hk_neu_id = $hk['folge_hauptkonten_id'];
  need( $hk_neu_id, 'Hauptkonto hat noch kein Folgekonto' );

  $uk_neu = array();
  foreach( $tables['unterkonten']['cols'] as $k => $v ) {
    $uk_neu[ $k ] = $uk[ $k ];
  }
  $uk_neu['hauptkonten_id'] = $hk_neu_id;
  unset( $uk_neu['folge_unterkonten_id'] );
  unset( $uk_neu['unterkonten_id'] );

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
      $uk = sql_one_unterkonto( array( 'folge_unterkonten_id' => $uk['unterkonten_id'] ), true );
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
  $filters = array();
  $groupby = 'buchungen.buchungen_id';

  $selects = sql_default_selects( 'buchungen' );
  $selects[] = "( SELECT COUNT(*) FROM posten WHERE ( posten.buchungen_id = buchungen.buchungen_id ) AND ( posten.art = 'S' ) ) as postenS_count";
  $selects[] = "( SELECT COUNT(*) FROM posten WHERE ( posten.buchungen_id = buchungen.buchungen_id ) AND ( posten.art = 'H' ) ) as postenH_count";
  $selects[] = "IF( valuta <= 100, 1, 0 ) as vortrag";
  $joins['posten'] = 'buchungen_id';
  $joins['unterkonten'] = 'unterkonten_id';
  $joins['hauptkonten'] = 'hauptkonten_id';
  $joins['kontoklassen'] = 'kontoklassen_id';

  foreach( sql_canonicalize_filters( 'buchungen', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'buchungen.', 10 ) == 0 ) { 
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      case 'valuta_von':
        $filters[] = "valuta >= '$cond'";
        break;
      case 'valuta_bis':
        $filters[] = "valuta <= '$cond'";
        break;
      case 'buchungsdatum_von':
        $filters[] = "buchungsdatum >= '$cond'";
        break;
      case 'buchungsdatum_bis':
        $filters[] = "buchungsdatum <= '$cond'";
        break;
      case 'unterkonten_id':
      case 'hauptkonten_id':
      case 'seite':
      case 'kontoart':
      case 'kontoklassen_id':
        $filters[$key] = $cond;
        break;
      case 'geschaeftsbereiche_id':
        $filters['geschaeftsbereich'] = sql_unique_value( 'kontoklassen', 'geschaeftsbereich', $cond );
        break;
      case 'geschaeftsjahr':
        $filters['hauptkonten.geschaeftsjahr'] = $cond;
        break;
      case 'where':
        $filters[] = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
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

function sql_one_buchung( $filters = array(), $allownull = false ) {
  $sql = sql_query_buchungen( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allownull );
}

function sql_delete_buchungen( $filters ) {
  foreach( sql_buchungen( $filters ) as $buchung ) {
    $buchungen_id = $buchung['buchungen_id'];
    sql_delete( 'posten', array( 'buchungen_id' => $buchungen_id ) );
    sql_delete( 'buchungen', array( 'buchungen_id' => $buchungen_id ) );
  }
}

function sql_buche( $buchungen_id, $valuta, $kommentar, $posten ) {
  global $mysqlheute, $geschaeftsjahr_max, $geschaeftsjahr_abgeschlossen;

  logger( "sql_buche: $buchungen_id" );

  $geschaeftsjahr = 0;
  $saldoH = 0;
  $saldoS = 0;
  $is_vortrag = 0;
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
        $saldoH += $p['betrag'];
        break;
      case 'S':
        $saldoS += $p['betrag'];
        break;
      default:
        error( 'sql_buche: undefinierter Posten' );
    }
  }
  need( $geschaeftsjahr > $geschaeftsjahr_abgeschlossen, 'buchung nicht moeglich: geschaeftsjahr ist abgeschlossen' );
  $values_buchungen = array(
    'valuta' => $valuta
  , 'kommentar' => $kommentar
  , 'buchungsdatum' => $mysqlheute
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
  $filters = array();
  $groupby = 'hauptkonten.geschaeftsjahr';

  $joins['kontoklassen'] = 'kontoklassen_id';
  $selects = sql_default_selects(
    array( 'hauptkonten', 'kontoklassen' )
  , array( 'kontoklassen.cn' => 'kontoklassen_cn' )
  );
  $selects[] = "COUNT(*) AS hauptkonten_count";
  $selects[] = "SUM( ( SELECT COUNT(*) FROM unterkonten WHERE unterkonten.hauptkonten_id = hauptkonten.hauptkonten_id ) ) AS unterkonten_count";

  foreach( sql_canonicalize_filters( 'hauptkonten', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'hauptkonten.', 12 ) == 0 ) { 
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      case 'where':
        $filters[] = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
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
}


function sql_saldenvortrag_buchen( $jahr ) {
  global $geschaeftsjahr_max, $mysqlheute;

  logger( "sql_saldenvortrag_buchen: $jahr", 'vortrag' );

  $vortrag = 0.0;
  $posten = array();

  need( $jahr < $geschaeftsjahr_max );

  $vortragshauptkonten = sql_hauptkonten( array( 'geschaeftsjahr' => $jahr + 1, 'vortragskonto' => 1 ) );
  need( count( $vortragshauptkonten ) == 1, 'kein eindeutiges vortrags-hauptkonto angelegt' );
  $vortrags_hk_id = $vortragshauptkonten[0]['hauptkonten_id'];

  $unterkonten = sql_unterkonten( array( 'geschaeftsjahr' => $jahr ) );
  foreach( $unterkonten as $uk ) {
    if( $uk['unterkonto_geschlossen'] )
      continue;
    $saldo = $uk['saldo'];
    $uk_neu_id = $uk['folge_unterkonten_id'];
    need( $uk_neu_id, 'kein folgekonto vorhanden' );
    switch( $uk['kontoart'].$uk['seite'] ) {
      case 'BA':
        $posten[] = array(
          'beleg' => "Vortrag aus $jahr am $mysqlheute"
        , 'art' => ( $saldo > 0 ? 'S' : 'H' )
        , 'betrag' => abs( $saldo )
        , 'unterkonten_id' => $uk_neu_id
        );
        break;
      case 'BP':
        $posten[] = array(
          'beleg' => "Vortrag aus $jahr am $mysqlheute"
        , 'art' => ( $saldo > 0 ? 'H' : 'S' )
        , 'betrag' => abs( $saldo )
        , 'unterkonten_id' => $uk_neu_id
        );
        break;
      case 'EA':
        $vortrag -= $saldo;
        break;
      case 'EP':
        $vortrag += $saldo;
        break;
      default: 
        error( 'kontoart/seite: undefinierter Wert' );
    }
  }

  // suche vortragskonten, die keine folgekonten sind:
  //
  $vortragsunterkonten = sql_unterkonten( array(
    'geschaeftsjahr' => $jahr+1, 'vortragskonto' => 1, 'vortragsjahr' => $jahr
  ) ); 
  if( $vortragsunterkonten ) {
    $vortrags_uk_id = $vortragsunterkonten[0]['unterkonten_id'];
  } else {
    $vortrags_uk_id = sql_insert( 'unterkonten', array(
      'cn' => "Vortrag aus Jahr $jahr"
    , 'hauptkonten_id' => $vortrags_hk_id
    , 'vortragsjahr' => $jahr
    ) );
  }
  $posten[] = array(
    'beleg' => "Jahresergebnis $jahr"
  , 'art' => ( $vortrag >= 0 ? 'H' : 'S' )
  , 'betrag' => abs( $vortrag )
  , 'unterkonten_id' => $vortrags_uk_id
  );
  sql_buche( 0, 100, "Vortrag aus $jahr", $posten ); // loest ggf. weitere vortraege aus!
}

////////////////////////////////////
//
// posten-funktionen
//
////////////////////////////////////

function sql_query_posten( $op, $filters_in = array(), $using = array(), $orderby = false, $limit_from = 0, $limit_count = 0 ) {
  $joins = array();
  $filters = array();
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

  foreach( sql_canonicalize_filters( 'posten', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'posten.', 7 ) == 0 ) {
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      case 'unterkonten_id':
        $filters['unterkonten.unterkonten_id'] = $cond;
        break;
      case 'hauptkonten_id':
        $filters['hauptkonten.hauptkonten_id'] = $cond;
        break;
      case 'kontoklassen_id':
        $filters['kontoklassen.kontoklassen_id'] = $cond;
        break;
      case 'seite':
        $filters['kontoklassen.seite'] = $cond;
        break;
      case 'kontoart':
        $filters['kontoklassen.kontoart'] = $cond;
        break;
      case 'geschaeftsbereiche_id':
        $filters['geschaeftsbereich'] = sql_unique_value( 'kontoklassen', 'geschaeftsbereich', $cond );
        break;
      case 'valuta':
        $filters['buchungen.valuta'] = $cond;
        break;
      case 'valuta_von':
        $filters[] = "buchungen.valuta >= '$cond'";
        break;
      case 'valuta_bis':
        $filters[] = "buchungen.valuta <= '$cond'";
        break;
      case 'geschaeftsjahr':
        $filters['hauptkonten.geschaeftsjahr'] = $cond;
        break;
      case 'where':
        $filters[] = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
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

function sql_one_posten( $filters = array(), $allownull = false ) {
  $sql = sql_query_posten( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allownull );
}

function sql_posten_saldo( $filters = array() ) {
  $sql = sql_query_posten( 'SALDO', $filters );
  return sql_do_single_field( $sql, 'saldo' );
}


////////////////////////////////////
//
// darlehen-funktionen:
//
////////////////////////////////////


function sql_query_darlehen( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $filters = array();
  $groupby = 'darlehen.darlehen_id';

  // $joins['LEFT zahlungsplan'] = 'darlehen_id';
  $joins['LEFT people'] = 'people_id';
  $joins[] = 'LEFT unterkonten AS darlehenkonto ON darlehenkonto.unterkonten_id = darlehen.darlehen_unterkonten_id';
  $joins[] = 'LEFT unterkonten AS zinskonto ON zinskonto.unterkonten_id = darlehen.zins_unterkonten_id';

  $selects = sql_default_selects('darlehen');
  $selects[] = 'people.cn as people_cn';
  
  // $selects[] = '( SELECT cn FROM unterkonten WHERE unterkonten_id = darlehen_unterkonten_id ) AS darlehen_unterkonten_cn';
  // $selects[] = '( SELECT cn FROM unterkonten WHERE unterkonten_id = zins_unterkonten_id ) AS zins_unterkonten_cn';
  $selects[] = 'darlehenkonto.cn as darlehen_unterkonten_cn';
  $selects[] = 'zinskonto.cn as zins_unterkonten_cn';

  foreach( sql_canonicalize_filters( 'darlehen', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'darlehen.', 9 ) == 0 ) { 
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      case 'where':
        $filters[] = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
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
  return sql_query( $op, 'darlehen', $filters, $selects, $joins, $orderby, $groupby );
}

function sql_darlehen( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'cn';
  $sql = sql_query_darlehen( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_darlehen( $filters = array(), $allownull = false ) {
  $sql = sql_query_darlehen( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allownull );
}

function sql_delete_darlehen( $darlehen_id ) {
  menatwork();
}

////////////////////////////////////
//
// zahlungsplan-funktionen
//
////////////////////////////////////

function sql_query_zahlungsplan( $op, $filters_in = array(), $using = array(), $orderby = 'geschaeftsjahr, valuta', $limit_from = 0, $limit_count = 0 ) {
  $joins = array();
  $filters = array();
  $groupby = 'zahlungsplan.zahlungsplan_id';

  $joins['darlehen'] = 'zahlungsplan_id';

  $selects = sql_default_selects(
    array( 'zahlungsplan', 'darlehen' )
  , array( 'darlehen.kommentar' => 'darlehen_kommentar' )
  );

  foreach( sql_canonicalize_filters( 'zahlungsplan', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'zahlungsplan.', 13 ) == 0 ) {
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      case 'valuta_von':
        $filters[] = "valuta >= '$cond'";
        break;
      case 'valuta_bis':
        $filters[] = "valuta <= '$cond'";
        break;
      case 'where':
        $filters[] = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
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
 
function sql_zahlungsplan( $filters = array(), $orderby = true, $limit_from = 0, $limit_count = 0 ) {
  if( $orderby === true )
    $orderby = 'valuta';
  $sql = sql_query_zahlungsplan( 'SELECT', $filters, array(), $orderby, $limit_from, $limit_count );
  return mysql2array( sql_do( $sql ) );
}

function sql_delete_zahlungsplan( $filters ) {
  foreach( sql_zahlungsplan( $filters ) as $zp ) {
    sql_delete( 'zahlungsplan', $zp['zahlungsplan_id'] );
  }
}

function sql_zahlungsplan_berechnen( $darlehen_id ) {
  $darlehen = sql_one_darlehen( $darlehen_id );

  $darlehen_unterkonten_id = $darlehen['darlehen_unterkonten_id'];
  $zins_unterkonten_id = $darlehen['zins_unterkonten_id'];

  // berechnen:

  $vorlauf_jahre = $darlehen['geschaeftsjahr_tilgung_start'] - $darlehen['geschaeftsjahr_zinslauf_start'];
  $tilgung_jahre = $darlehen['geschaeftsjahr_tilgung_ende'] - $darlehen['geschaeftsjahr_tilgung_start'] + 1;

  $z = 1.0 + $darlehen['zins_prozent'] / 100.0;
  $annuitaet_faktor = $z ^ ( $vorlauf_jahre + $tilgung_jahr - 1 ) * ( $z - 1 ) / ( $z ^ $tilgung_jahre - 1 );
  prettydump( $annuitaet_faktor, 'annuitaet_faktor' );

  $j = $darlehen['geschaeftsjahr_zinslauf_start'];
  $zahlungsplan = array();
  $s = $darlehen['betrag_abgerufen'];

  while( $j <= $darlehen['geschaeftsjahr_tilgung_ende'] ) {
    $a = 0;
    if( $j == $darlehen['geschaeftsjahr_tilgung_ende'] ) {
      $a = $s;
    } else if( $j >= $darlehen['geschaeftsjahr_tilgung_start'] ) {
      $n = $darlehen['geschaeftsjahr_tilgung_ende'] - $jahr + 1; // anzahl verbleibende raten
      $a = $s * $z ^ ( $n - 1 ) * ( $z - 1 ) / ( $z ^ $n - 1 );
    }
    if( $a ) {
      $zahlungsplan[] = array(
  
      );
      $s -= $a;
    }
    if( $j < $darlehen['geschaeftsjahr_tilgung_ende'] ) {
      $zins_betrag = $s * $darlehen['zins_prozent'] / 100.0;
      $zahlungsplan[] = array(
  
      );
      $s += $zins_betrag;
    }
  }

  $darlehen_uk_id = sql_get_folge_unterkonten_id( $darlehen['darlehen_unterkonten_id'], $geschaeftsjahr_start );
  need( $darlehen_uk_id, 'kein Darlehenskonto angelegt' );

  $saldo_darlehen = sql_unterkonten_saldo( array( 'unterkonten_id' => $darlehen_uk_id, 'valuta_bis' => 100 ) );

  $zins_uk_id = sql_get_folge_unterkonten_id( $darlehen['zins_unterkonten_id'], $geschaeftsjahr_start );
  if( $darlehen['zins_prozent'] > 0.005 ) {
    need( $zins_uk_id, 'kein Zinskonto angelegt' );
    $saldo_zins = sql_unterkonten_saldo( array( 'unterkonten_id' => $zins_uk_id, 'valuta_bis' => 100 ) );
  } else {
    $saldo_zins = 0.0;
  }

  while( $saldo_darlehen + $saldo_zins > 0.005 ) {

    // tilgung: immer zu jahresbeginn (valuta 0101)
    //
    if( $darlehen['tilgungsbeginn_jahr'] <= $jahr ) 

       $zahlungsplan[] = array(
       );


     //zins: immer am jahresende (valuta 1231)
     //
    if( $darlehen['geschaeftsjahr_zinslauf_start'] < $geschaeftsjahr ) {
      $valuta_zinslauf_start = 101;
    } else {
      $valuta_zinslauf_start = $darlehen['valuta_zinslauf_start'];
    }

  }


}


////////////////////////////////////
//
// people-funktionen:
//
////////////////////////////////////


function sql_query_people( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $selects = sql_default_selects( 'people' );
  $selects[] = '( SELECT COUNT(*) FROM people_people_relation WHERE needle_people_id = people_id ) AS haystack_count';
  $selects[] = '( SELECT COUNT(*) FROM people_people_relation WHERE haystack_people_id = people_id ) AS needle_count';
  $joins = array();
  // $joins['LEFT unterkonten'] = 'unterkonten_id';
  // $joins['LEFT hauptkonten'] = 'hauptkonten_id';
  // $joins['LEFT kontoklassen'] = 'kontoklassen_id';
  $groupby = 'people.people_id';
  $filters = array();

  foreach( sql_canonicalize_filters( 'people', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'people.', 7 ) == 0 ) { 
      if( $key == 'people.jperson' ) {
        switch( $cond ) {
          case 'J':
            $cond = 1;
            break;
          case 'N':
            $cond = 0;
            break;
          default:
            break;
        }
      }
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      case 'relation':
        switch( tolower( $cond ) ) {
          case 'kreditor':
            $joins['LEFT unterkonten'] = 'unterkonten_id';
            $joins['LEFT hauptkonten'] = 'hauptkonten_id';
            $joins['LEFT kontoklassen'] = 'kontoklassen_id';
            $filters[] = "kontoart = 'B'";
            $filters[] = "seite = 'P'";
            break;
          case 'debitor':
            $joins['LEFT unterkonten'] = 'unterkonten_id';
            $joins['LEFT hauptkonten'] = 'hauptkonten_id';
            $joins['LEFT kontoklassen'] = 'kontoklassen_id';
            $filters[] = "kontoart = 'B'";
            $filters[] = "seite = 'A'";
            break;
        } 
      case 'where':
        $filters[] = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
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
  $s = sql_query( $op, 'people', $filters, $selects, $joins, $orderby );
  return $s;
}


////////////////////////////////////
//
// logbook-funktionen:
//
////////////////////////////////////

function sql_query_logbook( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $joins['LEFT sessions'] = 'sessions_id';
  $groupby = 'logbook.logbook_id';
  $selects = sql_default_selects( array( 'logbook', 'sessions' ), array( 'sessions.sessions_id' => false ) );
  //   this is totally silly, but MySQL insists on this "disambiguation"     ^ ^ ^
  $filters = array();
  foreach( sql_canonicalize_filters( 'logbook', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'logbook.', 8 ) == 0 ) { 
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      // allow prefix f_ to avoid clash with global variables:
      case 'f_thread':
      case 'f_window':
      case 'f_script':
      case 'f_sessions_id':
        $filters[ substr( $key, 2 ) ] = $cond;
        break;
      case 'where':
        $filters[] = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
  }

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      break;
    case 'MAX':
      $op = 'SELECT';
      $selects = 'MAX( logbook_id ) as max_logbook_id';
      break;
    default:
      error( "undefined op: $op" );
  }
  $s = sql_query( $op, 'logbook', $filters, $selects, $joins, $orderby );
  return $s;
}

function sql_logbook( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'sessions_id,timestamp';
  $sql = sql_query_logbook( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_logentry( $logbook_id ) {
  $sql = sql_query_logbook( 'SELECT', $logbook_id );
  return sql_do_single_row( $sql, true );
}

function sql_logbook_max_logbook_id() {
  $sql = sql_query_logbook( 'MAX' );
  return sql_do_single_field( $sql, 'max_logbook_id' );
}


function sql_delete_logbook( $filters ) {
  foreach( sql_logbook( $filters ) as $l ) {
    sql_delete( 'logbook', $l['logbook_id'] );
  }
}

?>
