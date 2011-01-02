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

function sql_things( $filters = array(), $orderby = 'things.cn,things.anschaffungsjahr' ) {
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

function sql_delete_thing( $things_id ) {
  need( ! sql_unterkonten( array( 'things_id' => $things_id ) ), 'Unterkonto vorhanden - bitte erst loeschen!' );
  return sql_delete( 'things', $things_id );
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

function sql_kontoklassen( $filters = array(), $orderby = 'kontoklassen.kontoklassen_id' ) {
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
  $sql = sql_query_hauptkonten( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_hauptkonto( $filters = array(), $allownull = false ) {
  $sql = sql_query_hauptkonten( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allownull );
}


function sql_delete_hauptkonto( $hauptkonten_id ) {
  need( ! sql_unterkonten( array( 'hauptkonten_id' => $hauptkonten_id ) ), 'loeschen nicht moeglich: unterkonten vorhanden' );
  return sql_delete( 'hauptkonten', $hauptkonten_id );
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
  $selects[] = "( IFNULL( SUM( posten.betrag * IF( posten.art = 'H', 1, -1 ) * IF( posten.zins, 1, 0 ) ), 0.0 ) 
                * IF( kontoklassen.seite = 'P', 1, -1 ) ) as saldo_zins";

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
        $filters['kontoklassen.seite'] = $cond;
        break;
      case 'kontoart':
        $filters['kontoklassen.kontoart'] = $cond;
        break;
      case 'kontoklassen_id':
        $filters['hauptkonten.kontoklassen_id'] = $cond;
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

function sql_unterkonten( $filters = array(), $orderby = 'seite, rubrik, titel, unterkonten.unterkonten_id' ) {
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

function sql_delete_unterkonto( $unterkonten_id ) {
  need( ! sql_posten( array( 'unterkonten_id' => $unterkonten_id ) ), 'loeschen nicht moeglich: posten vorhanden' );
  need( ! sql_darlehen( array( 'unterkonten_id' => $unterkonten_id ) ), 'loeschen nicht moeglich: darlehen vorhanden' );
  $uk = sql_one_unterkonto( $unterkonten_id );
  if( $uk['bankkonto'] && $uk['bankkonten_id'] )
    sql_delete( 'bankkonten', $uk['bankkonten_id'] );
  if( $uk['sachkonto'] && $uk['things_id'] )
    sql_delete( 'things', $uk['things_id'] );
  sql_delete( 'unterkonten', array( 'unterkonten_id' => $unterkonten_id ) );
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
  $joins['posten'] = 'buchungen_id';
  $joins['unterkonten'] ='unterkonten_id';
  $joins['hauptkonten'] ='hauptkonten_id';
  $joins['kontoklassen'] ='kontoklassen_id';

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

function sql_buchungen( $filters = array(), $orderby = 'valuta' ) {
  $sql = sql_query_buchungen( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_buchung( $filters = array(), $allownull = false ) {
  $sql = sql_query_buchungen( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allownull );
}

function sql_delete_buchung( $buchungen_id ) {
  sql_delete( 'posten', array( 'buchungen_id' => $buchungen_id ) );
  sql_delete( 'buchungen', array( 'buchungen_id' => $buchungen_id ) );
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
      case 'seite':
        $filters['hauptkonten.seite'] = $cond;
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
 
function sql_posten( $filters = array(), $orderby = 'valuta', $limit_from = 0, $limit_count = 0 ) {
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

  $selects = sql_default_selects('darlehen');
  $joins['LEFT zahlungsplan'] = 'darlehen_id'; // plan: so _solllte_ gezahlt werden
  $joins['LEFT peopleposten'] = 'darlehen_id'; // ist: so _wurde_ gezahlt

  $selects[] = "IFNULL( SUM( peopleposten.soll ), 0.0 ) as soll";
  $selects[] = "IFNULL( SUM( IF( (peopleposten.soll > 0) and (peopleposten.typ = ".TYPE_POSTEN_DARLEHEN."), peopleposten.soll, 0 ) ), 0.0 ) as beansprucht";
  $selects[] = "IFNULL( SUM( IF( (peopleposten.soll < 0) and (peopleposten.typ = ".TYPE_POSTEN_DARLEHEN."), peopleposten.soll, 0 ) ), 0.0 ) as getilgt";
  $selects[] = "IFNULL( SUM( IF( (peopleposten.typ = ".TYPE_POSTEN_ZINS."), peopleposten.soll, 0 ) ), 0.0 ) as zins";
  $selects[] = "IFNULL( SUM( IF( (zahlungsplan.soll > 0) and (zahlungsplan.typ = ".TYPE_POSTEN_DARLEHEN."), zahlungsplan.soll, 0 ) ), 0.0 ) as plan_beansprucht";
  $selects[] = "IFNULL( SUM( IF( (zahlungsplan.soll < 0) and (zahlungsplan.typ = ".TYPE_POSTEN_DARLEHEN."), zahlungsplan.soll, 0 ) ), 0.0 ) as plan_getilgt";
  $selects[] = "IFNULL( SUM( IF( (zahlungsplan.typ = ".TYPE_POSTEN_ZINS."), zahlungsplan.soll, 0 ) ), 0.0 ) as plan_zins";

  foreach( sql_canonicalize_filters( 'darlehen', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'darlehen.', 9 ) == 0 ) { 
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {  // otherwise, check for special cases:
      case 'peopleposten_id':
        $filters['peopleposten.peopleposten_id'] = $cond;
        break;
      case 'valuta':
        $filters['peopleposten.valuta'] = $cond;
        break;
      case 'faellig':
        $filters['zahlungsplan.faellig'] = $cond;
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
    case 'SOLL':
      $selects = 'SUM(soll) as soll';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'darlehen', $filters, $selects, $joins, $orderby, $groupby );
}

function sql_darlehen( $filters = array(), $orderby = 'cn' ) {
  $sql = sql_query_darlehen( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}



function sql_delete_darlehen( $darlehen_id ) {
  menatwork();
}


////////////////////////////////////
//
// people-funktionen:
//
////////////////////////////////////


function sql_query_people( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $selects = sql_default_selects( 'people' );
  $joins = array();
  // $joins['LEFT unterkonten'] = 'unterkonten_id';
  // $joins['LEFT hauptkonten'] = 'hauptkonten_id';
  // $joins['LEFT kontoklassen'] = 'kontoklassen_id';
  $groupby = 'people.people_id';

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
            $filter[] = "kontoart = 'B'";
            $filter[] = "seite = 'P'";
            break;
          case 'debitor':
            $joins['LEFT unterkonten'] = 'unterkonten_id';
            $joins['LEFT hauptkonten'] = 'hauptkonten_id';
            $joins['LEFT kontoklassen'] = 'kontoklassen_id';
            $filter[] = "kontoart = 'B'";
            $filter[] = "seite = 'A'";
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




?>
