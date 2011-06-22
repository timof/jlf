<?php


////////////////////////////////////
//
// people-funktionen:
//
////////////////////////////////////


function sql_query_people( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $selects = sql_default_selects( 'people' );
  // $selects[] = '( SELECT COUNT(*) FROM people_people_relation WHERE needle_people_id = people_id ) AS haystack_count';
  // $selects[] = '( SELECT COUNT(*) FROM people_people_relation WHERE haystack_people_id = people_id ) AS needle_count';
  $joins = array();
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
