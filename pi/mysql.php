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

  $filters = sql_canonicalize_filters( 'people', $filters_in );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1, 'cooked_atom' ) ) {
      if( $atom[ 1 ] === 'people.jperson' ) {
        switch( $atom[ 2 ] ) {
          case 'J':
            $atom[ 2 ]  = 1;
            break;
          case 'N':
            $atom[ 2 ]  = 0;
            break;
          default:
            break;
        }
      }
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
