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


?>
