<?php



////////////////////////////////////
//
// people-funktionen:
//
////////////////////////////////////

function has_status( $status, $people_id = 0 ) {
  if( ! $people_id )
    $people_id = $GLOBALS['login_people_id'];
  if( $people_id ) {
    $person = sql_person( $people_id );
    return ( ( $person['status'] & $status ) == $status );
  } else {
    return false;
  }
}

function sql_query_people( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'people' );
  $joins = array(
    'affiliations' => 'people_id'
//  , 'LEFT groups' => 'groups_id'
  );
  // the following doesn't work well if we filter on groups_id :-(
  // $selects[] = " ( SELECT GROUP_CONCAT( groups.kurzname SEPARATOR ' ' ) from affiliations join groups on groups_id where affiliations.people_id = people.people_id ) as gruppenzugehoerigkeit ";
  $selects[] = " TRIM( CONCAT( title, ' ', gn, ' ', sn ) ) AS cn ";
  $selects[] = " ( SELECT roomnumber from affiliations where ( affiliations.people_id = people.people_id ) and ( priority = 0 ) ) as primary_roomnumber ";
  $selects[] = " ( SELECT telephonenumber from affiliations where ( affiliations.people_id = people.people_id ) and ( priority = 0 ) ) as primary_telephonenumber";
  $selects[] = " ( SELECT mail from affiliations where ( affiliations.people_id = people.people_id ) and ( priority = 0 ) ) as primary_mail ";
  $selects[] = " ( SELECT kurzname from groups join affiliations using ( groups_id ) where ( affiliations.people_id = people.people_id ) and ( priority = 0 ) ) as primary_groupname ";
  $groupby = 'people.people_id';

  $filters = sql_canonicalize_filters( 'people,affiliations', $filters_in );

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = false;
      $groupby = false;
      break;
    default:
      error( "undefined op: $op" );
  }
  $s = sql_query( $op, 'people', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_people( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'people.cn';
  $sql = sql_query_people( 'SELECT', $filters, array(), $orderby );
  // debug( $sql, 'sql people' );
  // return array();
  return mysql2array( sql_do( $sql ) );
}

function sql_delete_people( $filters, $check = false ) {
  $problems = array();
  $people = sql_people( $filters );
  foreach( $people as $p ) {
    $people_id = $p['people_id'];
    if( sql_pruefungen( "dozent_people_id=$people_id" ) )
      $problems[] = "Person kann nicht geloescht werden - Pruefungen vorhanden";
  }
  if( $check ) 
    return $problems;
  need( ! $problems, $problems );
  foreach( $people as $p ) {
    $people_id = $p['people_id'];
    sql_delete( 'affiliations', array( 'people_id' => $people_id ) );
    sql_delete( 'people', $people_id );
  }
}

////////////////////////////////////
//
// affiliations-funktionen:
//
////////////////////////////////////

function sql_query_affiliations( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'affiliations,people,groups' );
  $joins = array(
    'people' => 'people_id'
  , 'LEFT groups' => 'groups_id'
  );
  $groupby = 'affiliations_id';

  $filters = sql_canonicalize_filters( 'affiliations,people,groups', $filters_in );

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = false;
      $groupby = false;
      break;
    default:
      error( "undefined op: $op" );
  }
  $s = sql_query( $op, 'affiliations', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_affiliations( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'affiliations.priority,groups.cn';
  $sql = sql_query_affiliations( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_delete_affiliations( $filters, $check = false ) {
  $problems = array();
  if( $check )
    return $problems;
  if( $check === NULL ) { // just do it - even if we leave people without affiliation!
    sql_delete( 'affiliations', $filters );
  }
}


////////////////////////////////////
//
// groups-funktionen:
//
////////////////////////////////////

function sql_query_groups( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'groups' );
  $joins = array(
    'LEFT affiliations' => 'groups_id'
  , 'LEFT people' => 'people_id'
  );
  $selects[] = ' COUNT(*) AS mitgliederzahl';
  $groupby = 'groups.groups_id';
  $selects[] = '( SELECT cn FROM people WHERE people.people_id = groups.head_people_id ) AS head_cn';
  $selects[] = '( SELECT people_id FROM people WHERE people.people_id = groups.head_people_id ) AS head_people_id';
  $selects[] = "( SELECT gn FROM people WHERE people.people_id = groups.head_people_id ) AS head_gn";
  $selects[] = "( SELECT CONCAT( gn, ' ', sn ) FROM people WHERE people.people_id = groups.head_people_id ) AS head_cn";
  $selects[] = '( SELECT people_id FROM people WHERE people.people_id = groups.secretary_people_id ) AS secretary_people_id';
  $selects[] = "( SELECT gn FROM people WHERE people.people_id = groups.secretary_people_id ) AS secretary_gn";
  $selects[] = "( SELECT CONCAT( gn, ' ', sn ) FROM people WHERE people.people_id = groups.secretary_people_id ) AS secretary_cn";

  $filters = sql_canonicalize_filters( 'groups,people', $filters_in );

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = false;
      $groupby = false;
      break;
    default:
      error( "undefined op: $op" );
  }
  $s = sql_query( $op, 'groups', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_groups( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'groups.cn';
  $sql = sql_query_groups( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_group( $filters = array(), $default = false ) {
  $sql = sql_query_groups( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_groups( $filters, $check = false ) {
  $problems = array();
  $groups = sql_groups( $filters );
  foreach( $groups as $g ) {
    $groups_id = $g['groups_id'];
    if( sql_people( "groups_id=$groups_id" ) ) {
      $problems[] = "Gruppe(n) koennen nicht geloescht werden - Mitglieder vorhanden!";
      break;
    }
    if( sql_bamathemen( "groups_id=$groups_id" ) ) {
      $problems[] = "Gruppe(n) koennen nicht geloescht werden - offene BaMa-Themen vorhanden!";
      break;
    }
  }
  if( $check )
    return $problems;
  need( ! $problems, $problems );
  sql_delete( 'groups', $filters );
}

////////////////////////////////////
//
// bamathemen-funktionen:
//
////////////////////////////////////


function sql_query_bamathemen( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'bamathemen,groups', array( 'groups.cn' => 'groups_cn', 'groups.url' => 'groups_url' ) );
  $joins = array(
    'LEFT groups' => 'groups_id'
  , 'LEFT people' => 'people.people_id = ansprechpartner_people_id'
  );
  $groupby = 'bamathemen.bamathemen_id';

  $filters = sql_canonicalize_filters( 'bamathemen,groups', $filters_in );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      case 'abschluss_id':
        need( $rel == '=' );
        $key = "( bamathemen.abschluss & $val )";
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
      $joins = false;
      $groupby = false;
      break;
    default:
      error( "undefined op: $op" );
  }
  $s = sql_query( $op, 'bamathemen', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_bamathemen( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'groups.cn,bamathemen.cn';
  $sql = sql_query_bamathemen( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_bamathema( $filters = array(), $default = false ) {
  $sql = sql_query_bamathemen( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_bamathemen( $filters, $check = false ) {
  $problems = array();
  if( $check )
    return $problems;
  need( ! $problems );
  sql_delete( 'bamathemen', $filters );
}

////////////////////////////////////
//
// pruefungen-funktionen:
//
////////////////////////////////////

function sql_query_pruefungen( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'pruefungen' );
  $joins = array(
    'LEFT people' => 'dozent_people_id = people.people_id'
  );
  $groupby = 'pruefungen_id';
  $selects[] = " TRIM( CONCAT( people.title, ' ', people.gn, ' ', people.sn ) ) as dozent_cn ";
  $selects[] = "substr(utc,1,4) as year";
  $selects[] = "substr(utc,5,2) as month";
  $selects[] = "substr(utc,7,2) as day";
  $selects[] = "substr(utc,9,2) as hour";
  $selects[] = "substr(utc,11,2) as minute";

  $filters = sql_canonicalize_filters( 'pruefungen', $filters_in );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      case 'jahr_von':
        $key = 'SUBSTR( pruefungen.utc, 1, 4 )';
        $rel = '>=';
        break;
      case 'jahr_bis':
        $key = 'SUBSTR( pruefungen.utc, 1, 4 )';
        $rel = '<=';
        break;
      case 'kw_von':
        // WEEK with mode 1 should match iso 8601 (week starts with monday, week 1 is first week with 4 or more days in this year),
        // except that last week of previous year may be returned as week 0 of this year
        $key = 'WEEK( CONCAT( SUBSTR( pruefungen.utc, 1, 4 ), '-', SUBSTR( pruefungen.utc, 5, 2 ), '-', SUBSTR( pruefungen.utc, 7, 2 ) ) , 1 )';
        $rel = '>=';
        break;
      case 'kw_bis':
        // week
        $key = 'WEEK( CONCAT( SUBSTR( pruefungen.utc, 1, 4 ), '-', SUBSTR( pruefungen.utc, 5, 2 ), '-', SUBSTR( pruefungen.utc, 7, 2 ) ) , 1 )';
        $rel = '<=';
        break;
      case 'studiengang_id':
        need( $rel == '=' );
        $key = "( pruefungen.studiengang & $val )";
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
      $joins = false;
      $groupby = false;
      break;
    default:
      error( "undefined op: $op" );
  }
  $s = sql_query( $op, 'pruefungen', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_pruefungen( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'utc,semester';
  $sql = sql_query_pruefungen( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_pruefung( $filters = array(), $default = false ) {
  $sql = sql_query_pruefungen( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_pruefungen( $filters, $check = false ) {
  $problems = array();
  if( $check )
    return $problems;
  need( ! $problems );
  sql_delete( 'pruefungen', $filters );
}

////////////////////////////////////
//
// umfragen-funktionen:
//
////////////////////////////////////

function sql_query_umfragen( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'umfragen' );
  $joins = array(
    'people' => 'initiator_people_id = people.people_id'
  );
  $groupby = 'umfragen_id';
  $selects[] = " ( COUNT(*) from umfragefelder where umfragefelder.umfragen_id = umfragen.umfragen_id ) as umfragefelder_count ";
  $selects[] = " ( COUNT(*) from umfrageteilnehmer where umfrageteilnehmer.umfragen_id = umfragen.umfragen_id ) as umfrageteilnehmer_count ";
  $selects[] = " TRIM( CONCAT( people.title, ' ', people.gn, ' ', people.sn ) ) AS initiator_cn ";

  $filters = sql_canonicalize_filters( 'umfragen,people', $filters_in );

  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as count';
      $joins = false;
      $groupby = false;
      break;
    default:
      error( "undefined op: $op" );
  }
  $s = sql_query( $op, 'umfragen', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_umfragen( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'utc,semester';
  $sql = sql_query_pruefungen( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_umfrage( $filters = array(), $default = false ) {
  $sql = sql_query_umfragen( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_umfragen( $filters, $check = false ) {
  $problems = array();
  if( $check )
    return $problems;
  need( ! $problems );
  sql_delete( 'umfragen', $filters );
}



?>
