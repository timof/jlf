<?php

////////////////////////////////////
//
// things-funktionen:
//
////////////////////////////////////

function sql_things( $filters = array(), $opts = array() ) {

  $joins = array(
    'unterkonten' => 'LEFT unterkonten USING ( things_id )'
  , 'hauptkonten' => 'LEFT hauptkonten USING ( hauptkonten_id )'
  , 'kontoklassen' => 'LEFT kontoklassen USING ( kontoklassen_id )'
  , 'posten' => 'LEFT posten USING ( unterkonten_id )'
  );

  $selects = sql_default_selects('things');
  $selects['wert'] = 'IFNULL( SUM(posten.betrag), 0.0 )';
  $selects['unterkonten_id'] = 'unterkonten.unterkonten_id';

  $opts = default_query_options( 'things', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'things.cn,things.anschaffungsjahr'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'things', $filters, $opts['joins'] );
  return sql_query( 'things', $opts );
}

function sql_one_thing( $filters = array(), $default = false ) {
  return sql_things( $filters, array( 'default' => $default, 'single_row' => true ) );
}

// function sql_things_wert( $filters = array() ) {
//   $sql = sql_query_things( 'WERT', $filters );
//   return sql_do_single_field( $sql, 'wert' );
// }

function sql_delete_things( $filters, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $problems = array();
  $things = sql_things( $filters );
  foreach( $things as $thing ) {
    $things_id = $thing['things_id'];
    if( ( $references = sql_references( 'things', $things_id ) ) ) {
      $problems[] = we('cannot delete: references exist: ','nicht löschbar: Verweise vorhanden: ').implode( ', ', array_keys( $references ) );
    }
    if( ! have_priv( 'things', 'delete', $things_id ) ) {
      $problems[] = we('insufficient privileges to delete','keine Berechtigung zum Loeschen').": [$things_id]";
    }
  }
  if( adefault( $opts, 'check' ) ) {
    return $problems;
  }
  need( ! $problems, $problems );
  foreach( $things as $thing ) {
    sql_delete( 'things', $things_id );
  }
}

function sql_save_thing( $things_id, $values, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $problems = array();
  if( $things_id ) {
    logger( "start: update thing [$things_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'thing', array( 'thing' => "things_id=$things_id" ) );
    if( ! have_priv( 'things', 'edit', $things_id ) ) {
      $problems[] = we('insufficient privileges to edit','keine Berechtigung zum Edieren').": [$things_id]";
    }
    $opts['update'] = 1;
  } else {
    logger( "start: insert thing", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'thing' );
    if( ! have_priv( 'things', 'create' ) ) {
      $problems[] = we('insufficient privileges to create','keine Berechtigung fuer Neueintrag');
    }
    $opts['update'] = 0;
  }
  $problems += validate_row( 'things', $values, $opts );
  if( adefault( $opts, 'check' ) ) {
    return $problems;
  }
  need( ! $problems, $problems );
  if( $things_id ) {
    sql_update( 'things', $things_id, $values );
   } else {
    $things_id = sql_insert( 'things', $values );
    logger( "new thing [$things_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'thing', array( 'thing' => "things_id=$things_id" ) );
  }

  return $things_id;
}


////////////////////////////////////
//
// people-funktionen
//
////////////////////////////////////

// sql_people(): use the default for the time being


function sql_delete_people( $filters, $opts = array() ) {
  global $login_people_id;

  $opts = parameters_explode( $opts, 'default_key=check' );

  $problems = array();
  $people = sql_people( $filters );
  foreach( $people as $p ) {
    $people_id = $p['people_id'];
    if( ! have_priv( 'person', 'delete', $people_id ) ) {
      $problems[] = we( 'insufficient privileges to delete person ','keine Berechtigung zum Löschen der Person' );
    }
    if( $people_id === $login_people_id ) {
      $problems[] = we( 'cannot delete yourself','eigener account nicht löschbar' );
    }
    $references = sql_references( 'people', $people_id, 'ignore=persistentvars changelog sessions' );
    if( $references ) {
      $problems[] = we('cannot delete: references exist: ','nicht löschbar: Verweise vorhanden: ').implode( ', ', array_keys( $references ) );
    }
  }
  if( adefault( $opts, 'check' ) ) {
    return $problems;
  }
  need( ! $problems, $problems );
  foreach( $people as $p ) {
    $people_id = $p['people_id'];
    $references = sql_references( 'people', $people_id, 'prune=persistentvars,ignore=sessions changelog' ); 
    need( ! $references, $references );
    logger( "delete person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'people' );
    sql_delete( 'people', $people_id );
  }
}

function sql_save_person( $people_id, $values, $opts = array() ) {
  if( $people_id ) {
    logger( "start: update person [$people_id]", LOG_LEVEL_DEBUG, LOG_FLAG_UPDATE, 'people', array( 'person' => "people_id=$people_id" ) );
    need_priv( 'people', 'edit', $people_id );
  } else {
    logger( "start: insert person", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'people' );
    need_priv( 'people', 'create' );
  }
  $opts = parameters_explode( $opts, 'default_key=check' );
  $opts['update'] = $people_id;
  $check = adefault( $opts, 'check' );

  $have_auth_flags = false;
  $authentication_methods = ',';
  foreach( array( 'simple', 'ssl' ) as $name ) {
    if( ( $a = adefault( $values, "authentication_method_$name" ) ) ) {
      $have_auth_flags = true;
      if( $a['value'] ) {
        $authentication_methods .= "$name,";
      }
    }
    unset( $values[ "authentication_method_$name" ] );
  }
  if( $have_auth_flags && ! isset( $values['authentication_methods'] ) ) {
    $values['authentication_methods'] = $authentication_methods;
  }

  if( ! ( $problems = validate_row( 'people', $values, $opts ) ) ) {
    // more checks?
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems, $problems );
  if( $people_id ) {
    sql_update( 'people', $people_id, $values );
    logger( "updated person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'people', array( 'person' => "people_id=$people_id" ) );
  } else {
    $people_id = sql_insert( 'people', $values );
    logger( "new person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'people' );
  }
  return $people_id;
}


////////////////////////////////////
//
// kontoklassen-funktionen:
//
////////////////////////////////////

function sql_kontoklassen( $filters = array(), $opts = array() ) {

  $selects = sql_default_selects('kontoklassen');
  $opts = default_query_options( 'kontoklassen', $opts, array( 'selects' => $selects ) );
  $opts['filters'] = sql_canonicalize_filters( 'kontoklassen', $filters );

  return sql_query( 'kontoklassen', $opts );
}

function sql_one_kontoklasse( $filters = array(), $default = false ) {
  return sql_kontoklassen( $filters, array( 'default' => $default, 'single_row' => true ) );
}


////////////////////////////////////
//
// bankkonten-funktionen:
//
////////////////////////////////////

function sql_bankkonten( $filters = array(), $opts = array() ) {

  $selects = sql_default_selects( array(
    'bankkonten'
  , 'kontoklassen' => array( '.cn' => 'kontoklassen_cn' )
  , 'unterkonten' => array( '.cn' => 'unterkonten_cn' )
  ) );
  $selects['saldo'] = 'IFNULL( SUM( posten.betrag ), 0.0 ) ';
  $joins = array(
    'unterkonten' => 'LEFT unterkonten USING ( bankkonten_id )'
  , 'hauptkonten' => 'LEFT hauptkonten USING ( hauptkonten_id )'
  , 'kontoklassen' => 'LEFT kontoklassen USING ( kontoklassen_id )'
  , 'posten' => 'LEFT posten USING ( unterkonten_id )'
  );
  $opts = default_query_options( 'bankkonten', $opts, array(
    'joins' => $joins
  , 'selects' => $selects
  , 'orderby' => 'bank, blz, kontonr'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'bankkonten', $filters, $opts['joins'] );

  return sql_query( 'bankkonten', $opts );
}

function sql_one_bankkonto( $filters = array(), $default = false ) {
  $sql = sql_query_bankkonten( 'SELECT', $filters );
  return sql_bankkonten( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_delete_bankkonten( $filters, $if_dangling = false ) {
  foreach( sql_bankkonten( $filters ) as $bankkonto ) {
    $bankkonten_id = $bankkonto['bankkonten_id'];
    if( sql_unterkonten( array( 'bankkonto' => 1, 'bankkonten_id' => $bankkonten_id ) ) ) {
      if( $if_dangling )
        menatwork(); // needs review!!! /// continue;
      else
        error( 'bankkonto: loeschen nicht moeglich: unterkonto vorhanden', LOG_FLAG_CODE | LOG_FLAG_USER | LOG_FLAG_ABORT | LOG_FLAG_DELETE, 'bankkonten' );
    }
    sql_delete( 'bankkonten', $bankkonten_id );
  }
}


////////////////////////////////////
//
// hauptkonten-funktionen:
//
////////////////////////////////////

function sql_hauptkonten( $filters = array(), $opts = array() ) {
  $joins = array( 'kontoklassen' => 'kontoklassen USING ( kontoklassen_id )' );
  $selects = sql_default_selects( array( 'hauptkonten', 'kontoklassen' => array( 'aprefix' => 'kontoklassen_' ) ) );
  $selects['hgb_klasse'] = "hauptkonten.hauptkonten_hgb_klasse";
  $selects['unterkonten_count'] = "( SELECT COUNT(*) FROM unterkonten WHERE unterkonten.hauptkonten_id = hauptkonten.hauptkonten_id )";

  $opts = default_query_options( 'hauptkonten', $opts, array(
    'joins' => $joins
  , 'selects' => $selects
  , 'orderby' => 'geschaeftsjahr, kontoklassen.seite, hauptkonten.rubrik, hauptkonten.titel, kontoklassen.geschaeftsbereich'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'hauptkonten', $filters, $joins, $selects );

  return sql_query( 'hauptkonten', $opts );
}

function sql_one_hauptkonto( $filters = array(), $default = false ) {
  $sql = sql_hauptkonten( $filters, array( 'default' => $default, 'single_row' => true ) );
}

// hauptkonto schliessen: 
// - schliesst ein konto, loescht alle folgekonten
// - moeglich, wenn alle unterkonten geschlossen und alle folgekonten loeschbar sind
//
function sql_hauptkonto_schliessen( $hauptkonten_id, $options = array() ) {
  $opts = parameters_explode( $opts );
  $problems = array();

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

  if( adefault( $opts, 'check' ) ) {
    return $problems;
  }

  need( ! $problems, $problems );
  logger( "sql_hauptkonto_schliessen: [$hauptkonten_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'hauptkonten' );

  $hk = sql_one_hauptkonto( $hauptkonten_id );
  $folge_hk_id = $hk['folge_hauptkonten_id'];
  sql_update( 'hauptkonten', $hauptkonten_id, array( 'hauptkonto_geschlossen' => 1, 'folge_hauptkonten_id' => 0 ) );
  if( $folge_hk_id ) {
    sql_delete_hauptkonten( $folge_hk_id );
  }
  return $problems;
}

// hauptkonto oeffnen: 
// - oeffnet ein hauptkonto, legt alle folge-hauptkonten bis geschaeftsjahr_max an
// - moeglich, wenn geschaeftsjahr noch offen
//
function sql_hauptkonto_oeffnen( $hauptkonten_id, $options = array() ) {
  $opts = parameters_explode( $opts );
  $problems = array();

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

  if( adefault( $opts, 'check' ) ) {
    return $problems;
  }

  need( ! $problems, $problems );
  logger( "sql_hauptkonto_schliessen: [$hauptkonten_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'hauptkonten' );

  $hk = sql_one_hauptkonto( $hauptkonten_id );
  $folge_hk_id = $hk['folge_hauptkonten_id'];
  sql_update( 'hauptkonten', $hauptkonten_id, array( 'hauptkonto_geschlossen' => 1, 'folge_hauptkonten_id' => 0 ) );
  if( $folge_hk_id ) {
    sql_delete_hauptkonten( $folge_hk_id );
  }
  return $problems;
}


// hauptkonto_loeschen: loescht auch alle folgekonten
// moeglich, wenn 
// - keine unterkonten vorhanden bei diesem und allen folgekonten
// - konto ist kein folgekonto oder folgekonto eines abgeschlossenen kontos
// alle folgekonten werden ebenfalls geloescht
//
function sql_delete_hauptkonten( $filters, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $problems = array();

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
    logger( "sql_delete_hauptkonten: $hauptkonten_id", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'hauptkonten' );
    while( $hauptkonten_id ) {
      $hk = sql_one_hauptkonto( $hauptkonten_id );
      sql_delete( 'hauptkonten', $hauptkonten_id );
      $hauptkonten_id = $hk['folge_hauptkonten_id'];
    }
  }
  return $problems();
}


function sql_hauptkonto_folgekonto_anlegen( $hauptkonten_id ) {
  global $tables;

  logger( "sql_hauptkonto_folgekonto_anlegen: $hauptkonten_id", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'hauptkonten' );

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

function sql_unterkonten( $filters = array(), $opts = array() ) {
  $joins = array(
    'hauptkonten' => 'hauptkonten USING ( hauptkonten_id )'
  , 'kontoklassen' => 'kontoklassen USING ( kontoklassen_id )'
  , 'people' => 'LEFT people USING ( people_id )'
  , 'things' => 'LEFT things USING ( things_id )'
  , 'posten' => 'LEFT posten USING ( unterkonten_id )'
  , 'buchungen' => 'LEFT buchungen USING ( buchungen_id )'
  , 'bankkonten' => 'LEFT bankkonten USING ( bankkonten_id )'
  );
  $selects = sql_default_selects( array(
    'unterkonten'
  , 'hauptkonten' => array( 'aprefix' => 'hauptkonten_' )
  , 'kontoklassen' => array( 'aprefix' => 'kontoklassen_' )
  ) );
  $selects['people_cn'] = 'people.cn';
  $selects['things_cn'] = 'things.cn';
  $selects['bankkonten_bank'] = 'bankkonten.bank';
  // hauptkonten_hgb_klasse overrides unterkonten_hgb_klasse:
  $selects['hgb_klasse'] = "IF( hauptkonten_hgb_klasse = '', unterkonten_hgb_klasse, hauptkonten_hgb_klasse )";
  $selects['saldoS'] = "IFNULL( SUM( posten.betrag * IF( posten.art = 'S', 1, 0 ) ), 0.0 )";
  $selects['saldoH'] = "IFNULL( SUM( posten.betrag * IF( posten.art = 'H', 1, 0 ) ), 0.0 )";
  $selects['saldo'] = "( IFNULL(
                  ( SUM( posten.betrag * IF( posten.art = 'H', 1, -1 ) ) * IF( kontoklassen.seite = 'P', 1, -1 ) )
                , 0.0 ) )";

  $opts = default_query_options( 'unterkonten', $opts, array(
    'joins' => $joins
  , 'selects' => $selects
  , 'orderby' => 'seite, rubrik, titel, unterkonten.unterkonten_id'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'unterkonten', $filters, $joins, $selects , array( 'stichtag' => array( '<=', 'buchungen.valuta' ) ) );

//       case 'hgb_klasse':
//         $key = "IF( hauptkonten_hgb_klasse != '', hauptkonten_hgb_klasse, unterkonten_hgb_klasse )";
//         $val = '^'.preg_replace( '/[.]/', '[.]', $val );  // sic!
//         $rel = '~=';
//         break;

  return sql_query( 'unterkonten', $opts );
}

function sql_one_unterkonto( $filters = array(), $default = false ) {
  $sql = sql_unterkonten( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_unterkonten_saldo( $filters = array() ) {
  return sql_unterkonten( $filters, 'group_by=*,single_field=saldo,default=0.0' );
}

// konto schliessen: moeglich, wenn
// - saldo == 0 oder erfolgskonto und
// - eventuelle folgekonten koennen _geloescht_(!) werden
//
function sql_unterkonto_schliessen( $unterkonten_id, $opts = array() ) {
  $problems = array();

  $opts = parameters_explode( $opts, 'default_key=check' );
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
    if( sql_delete_unterkonten( $folge_uk_id, 'check=vorgaenger_ignorieren' ) ) {
      $problems[] = "unterkonto [$unterkonten_id]: schliessen nicht moeglich: folgekonto [$folge_uk_id] nicht loeschbar";
    }
  }
  if( adefault( $opts, 'check' ) ) {
    return $problems;
  }

  need( ! $problems, $problems );
  logger( "sql_unterkonto_schliessen: [$unterkonten_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'unterkonten' );

  sql_update( 'unterkonten', $unterkonten_id, array( 'unterkonto_geschlossen' => 1, 'folge_unterkonten_id' => 0 ) );
  if( $folge_uk_id ) {
    sql_delete_unterkonten( $folge_uk_id );
  }
}

// unterkonto loeschen: moeglich, wenn
// - keine posten vorhanden im konto und folgekonten
// - kein zahlungsplan involviert konto oder folgekonten
// - konto ist nicht folgekonto
// option 'check': wenn nicht null: test auf loeschbarkeit
// spezialfall: $check === 'vorgaenger_ignorieren': check auf "ist nicht folgekonto" auslassen
//
function sql_delete_unterkonten( $filters, $opts = array() ) {

  $opts = parameters_explode( $opts, 'default_key=check' );
  $check = adefault( $opts, 'check' );
  $unterkonten = sql_unterkonten( $filters, 'geschaeftsjahr' );
  $problems = array();

  foreach( $unterkonten as $uk ) {
    $unterkonten_id = $uk['unterkonten_id'];

    if( $check !== 'vorgaenger_ignorieren' ) {
      if( sql_unterkonten( array( 'folge_unterkonten_id' => $unterkonten_id ) ) ) {
        $problems[] = "unterkonto [$unterkonten_id]: loeschen nicht moeglich: konto ist folgekonto";
      }
    }

    for( $id = $unterkonten_id; $id; $id = $k2['folge_unterkonten_id'] ) {
      if( sql_posten( "unterkonten_id=$id" ) ) {
        $problems[] = "unterkonto [$id]: loeschen nicht moeglich: posten vorhanden";
      }
      if( sql_darlehen( array( '||', "darlehen_unterkonten_id=$id", "zins_unterkonten_id=$id" ) ) ) {
        $problems[] = "unterkonto [$id]: loeschen nicht moeglich: darlehen vorhanden";
      }
      if( sql_zahlungsplan( "unterkonten_id=$id" ) ) {
        $problems[] = "unterkonto [$id]: loeschen nicht moeglich: zahlungsplan vorhanden";
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
    need( ! $pred, 'loeschen nicht moeglich: unterkonto ist folgekonto' );
    logger( "sql_delete_unterkonten: $unterkonten_id", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'unterkonten' );
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

  logger( "sql_unterkonto_folgekonto_anlegen: $unterkonten_id", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'unterkonten' );

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

function sql_buchungen( $filters = array(), $opts = array() ) {
  $groupby = 'buchungen.buchungen_id';

  $selects = sql_default_selects( 'buchungen' );
  $joins = array(
    'posten' => 'posten USING ( buchungen_id )'
  , 'unterkonten' => 'unterkonten USING ( unterkonten_id )'
  , 'hauptkonten' => 'hauptkonten USING ( hauptkonten_id )'
  , 'kontoklassen' => 'kontoklassen USING ( kontoklassen_id )'
  );
  $opts = default_query_options( 'buchungen', $opts, array(
    'joins' => $joins
  , 'selects' => $selects
  , 'orderby' => 'valuta'
  ) );
  $opts['filters'] = sql_canonicalize_filters(
    'buchungen', $filters, $joins, $selects
  , array(
    'valuta_von' => array( '>=', 'buchungen.valuta' )
  , 'valuta_bis' => array( '<=', 'buchungen.valuta' )
  , 'buchungsdatum_von' => array( '>=', 'buchungen.buchungsdatum' )
  , 'buchungsdatum_bis' => array( '<=', 'buchungen.buchungsdatum' )
  )
  );

  return sql_query( 'buchungen', $opts );
}

function sql_one_buchung( $filters = array(), $default = false ) {
  return sql_buchungen( $filters, array( 'single_row' => true, 'default' => $default ) );
}

function sql_delete_buchungen( $filters ) {
  foreach( sql_buchungen( $filters ) as $buchung ) {
    $buchungen_id = $buchung['buchungen_id'];
    sql_delete_posten( array( 'buchungen_id' => $buchungen_id ) );
    sql_delete( 'buchungen', array( 'buchungen_id' => $buchungen_id ) );
    logger( "sql_delete_buchungen: buchung $buchungen_id geloescht", LOG_LEVEL_NOTICE, LOG_FLAG_DELETE, 'buchungen' );
  }
}

function sql_buche( $buchungen_id, $valuta, $vorfall, $posten ) {
  global $geschaeftsjahr_max, $geschaeftsjahr_abgeschlossen;

  logger( "sql_buche: [$buchungen_id]", LOG_LEVEL_DEBUG, $buchungen_id ? LOG_FLAG_UPDATE: LOG_FLAG_INSERT, 'buchungen' );

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
        error( 'sql_buche: undefinierter Posten', LOG_FLAG_CODE | LOG_FLAG_DATA, 'posten,buchungen' );
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
    logger( "sql_buche: update: [$buchungen_id]", LOG_LEVEL_DEBUG, LOG_FLAG_UPDATE, 'buchung_update' );
  } else {
    $buchungen_id = sql_insert( 'buchungen', $values_buchungen );
    logger( "sql_buche: inserted: [$buchungen_id]", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'buchung_neu' );
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

// sql_geschaeftsjahre: hauptkonten, with 'group by geschaeftsjahr'!
//
// function sql_geschaeftsjahre( $filters = array(), $opts = array() ) {
//   $joins = array( 'kontoklassen' =>  'kontoklassen USING ( kontoklassen_id )';
//   $selects = sql_default_selects( array(
//     'hauptkonten'
//   , 'kontoklassen' => array( '.cn' => 'kontoklassen_cn' )
//   ) );
//   $selects['hauptkonten_count'] = "COUNT( * )";
//   $selects['unterkonten_count'] = "SUM( ( SELECT COUNT(*) FROM unterkonten WHERE unterkonten.hauptkonten_id = hauptkonten.hauptkonten_id ) )";
//   $opts = default_query_options( 'hauptkonten', $opts, array(
//     'selects' => $selects
//   , 'joins' => $joins
//   , 'orderby' => 'hauptkonten.geschaeftsjahr'
// 
//   $opts['filters'] = sql_canonicalize_filters( 'hauptkonten', $filters, $joins );
// 
//   return sql_query( 'hauptkonten', $opts );
// }

function sql_saldenvortrag_loeschen( $jahr ) {
  global $geschaeftsjahr_max;

  logger( "sql_saldenvortrag_loeschen: $jahr", LOG_LEVEL_NOTICE, LOG_FLAG_DELETE, 'vortrag' );

  if( $jahr < $geschaeftsjahr_max )
    sql_saldenvortrag_loeschen( $jahr + 1 );

  $buchungen = sql_buchungen( array( 'geschaeftsjahr' => $jahr, 'valuta' => 100 ) );
  foreach( $buchungen as $b ) {
    sql_delete( 'posten', array( 'buchungen_id' => $b['buchungen_id'] ) );
    sql_delete( 'buchungen', $b['buchungen_id'] );
  }
  logger( "sql_saldenvortrag_loeschen: ".count( $buchungen )." Vortragsbuchungen in $jahr geloescht", LOG_LEVEL_DEBUG, LOG_FLAG_DELETE, 'vortrag' );
}


function sql_saldenvortrag_buchen( $jahr ) {
  global $geschaeftsjahr_max;

  logger( "sql_saldenvortrag_buchen: $jahr", LOG_LEVEL_NOTICE, LOG_FLAG_INSERT, 'vortrag' );

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
      logger( "sql_saldenvortrag_buchen: unterkonto $vortrags_uk_id fuer vortrag $gb aus $jahr angelegt", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'vortrag' );
    }
    $posten[] = array(
      'beleg' => "Jahresergebnis $jahr"
    , 'art' => ( $saldo >= 0 ? 'H' : 'S' )
    , 'betrag' => abs( $saldo )
    , 'unterkonten_id' => $vortrags_uk_id
    );
  }
  sql_buche( 0, 100, "Vortrag aus $jahr", $posten ); // loest ggf. weitere vortraege aus!
  logger( "sql_saldenvortrag_buchen: ".count( $posten )." Vortragsposten gebucht", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'vortrag' );
}

////////////////////////////////////
//
// posten-funktionen
//
////////////////////////////////////

function sql_posten( $filters = array(), $opts = array() ) {
  $joins = array(
    'buchungen' => 'buchungen USING ( buchungen_id )'
  , 'unterkonten' => 'unterkonten USING ( unterkonten_id )'
  , 'hauptkonten' => 'hauptkonten USING ( hauptkonten_id )'
  , 'kontoklassen' => 'kontoklassen USING ( kontoklassen_id )'
  , 'people' => 'people USING ( people_id )'
  , 'things' => 'things USING ( things_id )'
  );

  $selects = sql_default_selects( array(
    'posten'
  , 'unterkonten' => array( '.kommentar' => 'unterkonten_kommentar' )
  , 'hauptkonten' => array( '.kommentar' => 'hauptkonten_kommentar' )
  , 'kontoklassen' => array( '.cn' => 'kontoklassen_cn' )
  , 'buchungen'
  ) );
  $selects['people_cn'] = 'people.cn';
  $selects['things_cn'] = 'things.cn';
  // $selects['is_vortrag'] = "IF( buchungen.valuta <= '100', 1, 0 )";
  // $selects['saldo'] = "IFNULL( SUM( betrag ), 0.0 )";

  $opts = default_query_options( 'posten', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'buchungen.valuta,buchungen.buchungen_id,art DESC'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'posten', $filters, $joins, $selects, array(
    'valuta_von' => array( '>=', 'buchungen.valuta' )
  , 'valuta_bis' => array( '<=', 'buchungen.valuta' )
  , 'buchungsdatum_von' => array( '>=', 'buchungen.buchungsdatum' )
  , 'buchungsdatum_bis' => array( '<=', 'buchungen.buchungsdatum' )
  ) );
  return sql_query( 'posten', $opts );
}
 
function sql_one_posten( $filters = array(), $default = false ) {
  return sql_posten( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_posten_saldo( $filters = array() ) {
  return sql_posten( $filters, 'single_field=saldo,groupby=*' );
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

function sql_darlehen( $filters = array(), $opts = array() ) {
  $joins = array(
    'darlehenkonto' => 'LEFT unterkonten ON darlehenkonto.unterkonten_id = darlehen.darlehen_unterkonten_id '
  , 'hauptkonten' => 'LEFT hauptkonten ON hauptkonten.hauptkonten_id = darlehenkonto.hauptkonten_id'
  , 'kontoklassen' => 'LEFT kontoklassen  ON hauptkonten.kontoklassen_id = kontoklassen.kontoklassen_id'
  , 'zinskonto' => 'LEFT unterkonten ON zinskonto.unterkonten_id = darlehen.zins_unterkonten_id'
  , 'people' => 'LEFT people ON people.people_id = darlehenkonto.people_id'
  );

  $selects = sql_default_selects( array(
    'darlehen'
  , 'people' => array( '.cn' => 'people_cn' )
  , 'hauptkonten' => array( '.kommentar' => false )
  , 'darlehenkonto' => array( 'prefix' => 'darlehenkonto_', 'table' => 'unterkonten' )
  , 'zinskonto' => array( 'prefix' => 'zinskonto_', 'table' => 'unterkonten' )
  , 'kontoklassen' => array( '.cn' => 'kontoklassen_cn' )
  ) );
  $selects['zahlungsplan_count'] = "( SELECT COUNT(*) FROM zahlungsplan WHERE ( zahlungsplan.darlehen_id = darlehen.darlehen_id ) )";

  $opts = default_query_options( 'darlehen', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'geschaeftsjahr,people_cn'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'darlehen,hauptkonten,people', $filters, $joins );

  return sql_query( 'darlehen', $opts );
}

function sql_one_darlehen( $filters = array(), $default = false ) {
  return sql_darlehen( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_delete_darlehen( $filters ) {
  foreach( sql_darlehen( $filters ) as $d ) {
    $id = $d['darlehen_id'];
    logger( "sql_delete_darlehen: [$id]", LOG_LEVEL_NOTICE, LOG_FLAG_DELETE, 'darlehen' );
    sql_delete( 'darlehen', $id );
    sql_delete( 'zahlungsplan', array( 'darlehen_id' => $id ) );
  }
}

////////////////////////////////////
//
// zahlungsplan-funktionen
//
////////////////////////////////////

function sql_zahlungsplan( $filters = array(), $opts = array() ) {
  $joins = array(
    'darlehen' => 'darlehen USING ( darlehen_id )'
  , 'unterkonten' => 'LEFT unterkonten USING ( unterkonten_id )'
  , 'people' => 'LEFT people USING ( people_id )'
  , 'posten' => 'LEFT posten USING ( posten_id )'
  , 'buchungen' => 'LEFT buchungen USING ( buchungen_id )'
  );

  $selects = sql_default_selects( array(
    'zahlungsplan'
  , 'darlehen' => array( '.kommentar' => 'darlehen_kommentar' )
  , 'buchungen' => array( '.valuta' => 'buchungen_valuta' )
  ) );
  $selects['unterkonten_cn'] = 'unterkonten.cn';
  $selects['people_cn'] = 'people.cn';
  $selects['people_id'] = 'people.people_id';
  $selects['saldo'] = 'IFNULL( SUM( betrag ), 0.0 )';
  $opts = default_query_options( 'zahlungsplan', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'valuta,art,zins'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'zahlungsplan', $filters, $joins, $selects, array(
    'valuta_von' => array( '>=', 'zahlungsplan.valuta' )
  , 'valuta_bis' => array( '<=', 'zahlungsplan.valuta' )
  ) );
  return sql_query( 'zahlungsplan', $opts );
}

function sql_one_zahlungsplan( $filters = array(), $default = false ) {
  return sql_zahlungsplan( $filters, array( 'default' => $default, 'single_row' => true ) );
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
  logger( "sql_zahlungsplan_berechnen: [$darlehen_id]", LOG_FLAG_INFO, LOG_LEVEL_INSERT, 'zahlungsplan' );

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


// ////////////////////////////////////
// //
// // people-funktionen:
// //
// ////////////////////////////////////
// 
// 
// function sql_query_people( $op, $filters_in = array(), $using = array(), $orderby = false ) {
//   $selects = sql_default_selects( 'people' );
//   // $selects[] = '( SELECT COUNT(*) FROM people_people_relation WHERE group_people_id = people_id ) AS groupmembers_count';
//   // $selects[] = '( SELECT COUNT(*) FROM people_people_relation WHERE member_people_id = people_id ) AS memberships_count';
//   $joins = array();
//   // $joins['LEFT unterkonten'] = 'unterkonten_id';
//   // $joins['LEFT hauptkonten'] = 'hauptkonten_id';
//   // $joins['LEFT kontoklassen'] = 'kontoklassen_id';
//   $groupby = 'people.people_id';
// 
//   $filters = sql_canonicalize_filters( 'people', $filters_in );
// 
//   switch( $op ) {
//     case 'SELECT':
//       break;
//     case 'COUNT':
//       $selects = 'COUNT(*) as count';
//       break;
//     default:
//       error( "undefined op: [$op]", LOG_FLAG_CODE, 'sql,people' );
//   }
//   $s = sql_query( 'people', array( 'filters' => $filters, 'selects' => $selects, 'joins' => $joins, 'orderby' => $orderby ) );
//   return $s;
// }


?>
