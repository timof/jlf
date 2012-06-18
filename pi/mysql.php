<?php



////////////////////////////////////
//
// people functions:
//
////////////////////////////////////

function sql_query_people( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'people' );
  $joins = array(
    'LEFT affiliations' => 'people_id'
  , 'LEFT affiliations AS primary_affiliation' => '( ( primary_affiliation.people_id = people.people_id ) AND ( primary_affiliation.priority = 0 ) )'
  , 'LEFT groups AS primary_group' => '( primary_group.groups_id = primary_affiliation.groups_id )'
  );
  $selects[] = " TRIM( CONCAT( title, ' ', gn, ' ', sn ) ) AS cn ";
  $selects[] = " primary_affiliation.groups_id AS primary_groups_id ";
  $selects[] = " primary_group.acronym AS primary_groupname ";
  $selects[] = " primary_affiliation.telephonenumber AS primary_telephonenumber ";
  $selects[] = " primary_affiliation.mail AS primary_mail ";
  $selects[] = " primary_affiliation.roomnumber AS primary_roomnumber ";
  $groupby = 'people.people_id';

  $filters = sql_canonicalize_filters( 'people,affiliations'
  , $filters_in
  , $joins
  , array(
      'REGEX' => array( '~=', "CONCAT( sn, ';', title, ';', gn, ';'
                                     , primary_affiliation.roomnumber, ';', primary_affiliation.telephonenumber, ';'
                                     , primary_affiliation.mail, ';', primary_affiliation.facsimiletelephonenumber )" )
    , 'INSTITUTE' => array( '=', '(people.flags & '.PEOPLE_FLAG_INSTITUTE.')', PEOPLE_FLAG_INSTITUTE )
    , 'NOPERSON' => array( '=', '(people.flags & '.PEOPLE_FLAG_NOPERSON.')', PEOPLE_FLAG_NOPERSON )
    )
  );

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
      error( "undefined op: [$op]", LOG_FLAG_CODE, 'people,sql' );
  }
  $s = sql_query( $op, 'people', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_people( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'people.sn, people.gn';
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
    if( sql_exams( "teacher_people_id=$people_id" ) )
      $problems[] = "Person kann nicht geloescht werden - exams vorhanden";
  }
  if( $check ) 
    return $problems;
  need( ! $problems, $problems );
  foreach( $people as $p ) {
    $people_id = $p['people_id'];
    logger( "delete person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'people' );
    sql_delete( 'affiliations', array( 'people_id' => $people_id ) );
    sql_delete( 'people', $people_id );
  }
}

////////////////////////////////////
//
// affiliations functions:
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
      error( "undefined op: [$op]", LOG_FLAG_CODE, 'affiliations,sql' );
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
// groups functions:
//
////////////////////////////////////

function sql_query_groups( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $selects = sql_default_selects( array( 'groups', 'head' => 'table=people,prefix=head', 'secretary' => 'table=people,prefix=secretary' ) );
  $joins = array(
    'LEFT people AS head' => '( head.people_id = groups.head_people_id )'
  , 'LEFT people AS secretary' => '( secretary.people_id = groups.secretary_people_id )'
  );
  $selects[] = ' COUNT(*) AS mitgliederzahl';
  $groupby = 'groups.groups_id';
  $selects[] = 'head.sn  AS head_sn';
  $selects[] = 'head.gn  AS head_gn';
  $selects[] = "CONCAT( head.gn, ' ', head.sn ) AS head_cn";
  // // $selects[] = '( SELECT people_id FROM people WHERE people.people_id = groups.head_people_id ) AS head_people_id';
  // $selects[] = "( SELECT gn FROM people WHERE people.people_id = groups.head_people_id ) AS head_gn";
  // $selects[] = "( SELECT CONCAT( gn, ' ', sn ) FROM people WHERE people.people_id = groups.head_people_id ) AS head_cn";
  // $selects[] = '( SELECT people_id FROM people WHERE people.people_id = groups.secretary_people_id ) AS secretary_people_id';
  $selects[] = "secretary.gn AS secretary_gn";
  $selects[] = "secretary.sn AS secretary_sn";
  $selects[] = "CONCAT( secretary.gn, ' ', secretary.sn ) AS secretary_cn";
  if( $GLOBALS['language'] == 'D' ) {
    $selects[] = "groups.cn AS cn_we";
    $selects[] = "groups.url AS url_we";
    $selects[] = "groups.note AS note_we";
  } else {
    $selects[] = "IF( groups.cn_en != '', groups.cn_en, groups.cn ) AS cn_we";
    $selects[] = "IF( groups.url_en != '', groups.url_en, groups.url ) AS url_we";
    $selects[] = "IF( groups.note_en != '', groups.note_en, groups.note ) AS note_we";
  }

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
      error( "undefined op: [$op]", LOG_FLAG_CODE, 'groups,sql' );
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
      $problems[] = we('cannot delete group(s) - members exist','Gruppe(n) koennen nicht gelöscht werden - Mitglieder vorhanden!');
      break;
    }
    if( sql_positions( "groups_id=$groups_id" ) ) {
      $problems[] = we('cannot delete group(s) - positions exist', 'Gruppe(n) koennen nicht gelöscht werden - offene Stellen vorhanden!');
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
// positions functions:
//
////////////////////////////////////


function sql_query_positions( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'positions,groups', array( 'groups.cn' => 'groups_cn', 'groups.url' => 'groups_url' ) );
  $joins = array(
    'LEFT groups' => 'groups_id'
  , 'LEFT people' => 'people.people_id = contact_people_id'
  );
  $groupby = 'positions.positions_id';

  $filters = sql_canonicalize_filters( 'positions,groups', $filters_in );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      case 'degree_id':
        need( $rel == '=' );
        $key = "( positions.degree & $val )";
        break;
      default:
        error( "unexpected key: [$key]", LOG_FLAG_CODE, 'positions,sql' );
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
      error( "undefined op: [$op]", LOG_FLAG_CODE, 'positions,sql' );
  }
  $s = sql_query( $op, 'positions', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_positions( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'groups.cn,positions.cn';
  $sql = sql_query_positions( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_position( $filters = array(), $default = false ) {
  $sql = sql_query_positions( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_positions( $filters, $check = false ) {
  $problems = array();
  if( $check )
    return $problems;
  need( ! $problems );
  sql_delete( 'positions', $filters );
}

////////////////////////////////////
//
// exams functions
//
////////////////////////////////////

function sql_query_exams( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'exams' );
  $joins = array(
    'LEFT people' => 'teacher_people_id = people.people_id'
  );
  $groupby = 'exams_id';
  $selects[] = " TRIM( CONCAT( people.title, ' ', people.gn, ' ', people.sn ) ) as teacher_cn ";
  $selects[] = "substr(utc,1,4) as year";
  $selects[] = "substr(utc,5,2) as month";
  $selects[] = "substr(utc,7,2) as day";
  $selects[] = "substr(utc,9,2) as hour";
  $selects[] = "substr(utc,11,2) as minute";

  $filters = sql_canonicalize_filters( 'exams', $filters_in );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      case 'year_from':
        $key = 'SUBSTR( exams.utc, 1, 4 )';
        $rel = '>=';
        break;
      case 'year_to':
        $key = 'SUBSTR( exams.utc, 1, 4 )';
        $rel = '<=';
        break;
      case 'week_from':
        // WEEK with mode 1 should match iso 8601 (week starts with monday, week 1 is first week with 4 or more days in this year),
        // except that last week of previous year may be returned as week 0 of this year
        $key = 'WEEK( CONCAT( SUBSTR( exams.utc, 1, 4 ), '-', SUBSTR( exams.utc, 5, 2 ), '-', SUBSTR( exams.utc, 7, 2 ) ) , 1 )';
        $rel = '>=';
        break;
      case 'week_to':
        // week
        $key = 'WEEK( CONCAT( SUBSTR( exams.utc, 1, 4 ), '-', SUBSTR( exams.utc, 5, 2 ), '-', SUBSTR( exams.utc, 7, 2 ) ) , 1 )';
        $rel = '<=';
        break;
      case 'studiengang_id':
        need( $rel == '=' );
        $key = "( exams.studiengang & $val )";
        break;
      default:
        error( "unexpected key: [$key]", LOG_FLAG_CODE, 'exams,sql' );
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
      error( "undefined op: [$op]", LOG_FLAG_CODE, 'exams,sql' );
  }
  $s = sql_query( $op, 'exams', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_exams( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'utc,semester';
  $sql = sql_query_exams( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_exam( $filters = array(), $default = false ) {
  $sql = sql_query_exams( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_exams( $filters, $check = false ) {
  $problems = array();
  if( $check )
    return $problems;
  need( ! $problems );
  sql_delete( 'exams', $filters );
}

////////////////////////////////////
//
// survey functions:
//
////////////////////////////////////

function sql_query_surveys( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'surveys' );
  $joins = array(
    'people' => 'initiator_people_id = people.people_id'
  );
  $groupby = 'surveys_id';
  $selects[] = " ( SELECT COUNT(*) FROM surveyfields WHERE surveyfields.surveys_id = surveys.surveys_id ) AS surveyfields_count ";
  $selects[] = " ( SELECT COUNT(*) FROM surveysubmissions WHERE surveysubmissions.surveys_id = surveys.surveys_id ) AS surveysubmisssions_count ";
  $selects[] = " TRIM( CONCAT( people.title, ' ', people.gn, ' ', people.sn ) ) AS initiator_cn ";

  $filters = sql_canonicalize_filters( 'surveys,people', $filters_in );

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
      error( "undefined op: [$op]", LOG_FLAG_CODE, 'surveys,sql' );
  }
  $s = sql_query( $op, 'surveys', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_surveys( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'closed,deadline,ctime';
  $sql = sql_query_surveys( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_survey( $filters = array(), $default = false ) {
  $sql = sql_query_surveys( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_surveys( $filters, $check = false ) {
  $problems = array();
  if( $check )
    return $problems;
  $surveys = sql_surveys( $filters );
  need( ! $problems );
  foreach( $surveys as $s ) {
    $surveys_id = $s['surveys_id'];
    sql_delete_surveysubmissions( "surveys_id=$surveys_id" );
    sql_delete_surveyfields( "surveys_id=$surveys_id" );
    sql_delete( 'surveys', "surveys_id=$surveys_id" );
  }
}

////////////////////////////////////
//
// surveyfields functions:
//
////////////////////////////////////

function sql_query_surveyfields( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'surveyfields,surveys' );
  $joins = array(
    'surveys' => 'surveys_id'
  , 'people' => 'initiator_people_id = people.people_id'
  );
  $groupby = 'surveyfields_id';

  $filters = sql_canonicalize_filters( 'surveyfields,surveys,people', $filters_in );

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
      error( "undefined op: [$op]", LOG_FLAG_CODE, 'surveyfields,sql' );
  }
  $s = sql_query( $op, 'surveyfields', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_surveyfields( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'surveys.deadline,surveyfields.surveys_id,surveyfields.priority';
  $sql = sql_query_surveyfields( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_surveyfield( $filters = array(), $default = false ) {
  $sql = sql_query_surveyfields( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_surveyfields( $filters, $check = false ) {
  $problems = array();
  $surveyfields = sql_surveyfields( $filters );
  if( $check )
    return $problems;
  need( ! $problems );
  
  foreach( $surveyfields as $f ) {
    $surveyfields_id = $f['surveyfields_id'];
    sql_delete_surveyreplies( "surveyfields_id=$surveyfields_id" );
    sql_delete( 'surveyfields', "surveyfields_id=$surveyfields_id" );
  }
}


////////////////////////////////////
//
// surveysubmissions functions:
//
////////////////////////////////////

function sql_query_surveysubmissions( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'surveysubmissions,surveys' );
  $joins = array(
    'surveys' => 'surveys_id'
  , 'LEFT sessions AS creator_sesssion' => 'creator_sessions_id = sessions.sessions_id'
  , 'LEFT people AS creator' => 'creator_session.login_people_id = creator.people_id'
  );
  $groupby = 'surveysubmissions_id';
  $selects[] = " TRIM( CONCAT( creator.title, ' ', creator.gn, ' ', creator.sn ) ) AS creator_cn ";

  $filters = sql_canonicalize_filters( 'surveysubmissions,surveys,people', $filters_in );

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
      error( "undefined op: [$op]", LOG_FLAG_CODE, 'surveysubmissions,sql' );
  }
  $s = sql_query( $op, 'surveysubmissions', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_surveysubmissions( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'surveys.deadline,creator_cn';
  $sql = sql_query_surveysubmissions( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_surveysubmission( $filters = array(), $default = false ) {
  $sql = sql_query_surveysubmissions( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_surveysubmissions( $filters, $check = false ) {
  $problems = array();
  $submissions = sql_surveysubmissions( $filters );
  if( $check )
    return $problems;
  need( ! $problems );
  
  foreach( $submissions as $s ) {
    $surveysubmissions_id = $s['surveysubmissions_id'];
    sql_delete_surveyreplies( "surveysubmissions_id=$surveysubmissions_id" );
    sql_delete( 'surveysubmissions', "surveysubmissions_id=$surveysubmissions_id" );
  }
}


////////////////////////////////////
//
// surveyreplies functions:
//
////////////////////////////////////

function sql_query_surveyreplies( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'surveyreplies,surveysubmissions,surveyfields,surveys' );
  $joins = array(
    'surveysubmissions' => 'surveysubmissions_id'
  , 'surveyfields' => 'surveyfields_id'
  , 'surveys' => 'surveys_id'
  , 'LEFT sessions AS creator_sesssion' => 'creator_sessions_id = sessions.sessions_id'
  , 'LEFT people AS creator' => 'creator_session.login_people_id = creator.people_id'
  );
  $groupby = 'surveyfields_id';

  $filters = sql_canonicalize_filters( 'surveyreplies,surveyfields,surveys,surveysubmissions', $filters_in );

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
      error( "undefined op: [$op]", LOG_FLAG_CODE, 'surveyreplies,sql' );
  }
  $s = sql_query( $op, 'surveyfields', $filters, $selects, $joins, $orderby, $groupby );
  return $s;
}

function sql_surveyreplies( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'surveys.deadline,creator_cn,surveyfields.priority';
  $sql = sql_query_surveyreplies( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_surveyreply( $filters = array(), $default = false ) {
  $sql = sql_query_surveyreplies( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_surveyreplies( $filters, $check = false ) {
  $problems = array();
  if( $check )
    return $problems;
  need( ! $problems );
  sql_delete( 'surveyreplies', $filters );
}

////////////////////////////////////
//
// teaching functions:
//
////////////////////////////////////

function sql_query_teaching( $op, $filters_in = array(), $using = array(), $orderby = false ) {

  $selects = sql_default_selects( 'teaching' );
  $joins = array(
    'LEFT people AS teacher' => 'teacher_people_id = teacher.people_id'
  // , 'LEFT affiliations AS teacher_affiliations' => 'teacher_people_id = teacher_affiliations.people_id'
  , 'LEFT people AS signer' => 'signer_people_id = signer.people_id'
  // , 'LEFT affiliations AS signer_affiliations' => 'signer_people_id = signer_affiliations.people_id'
  , 'LEFT sessions AS creator_sesssion' => 'creator_sessions_id = sessions.sessions_id'
  , 'LEFT people AS creator' => 'creator_session.login_people_id = creator.people_id'
  , 'LEFT affiliations AS creator_affiliations' => 'creator_session.login_people_id = creator_affiliations.people_id'
  );
  $groupby = 'teaching_id';
  $selects[] = "CONCAT( IF( teaching.term = 'W', 'WiSe', 'SoSe' ), ' ', teaching.year, IF( teaching.term = 'W', teaching.year - 1999, '' ) ) as yearterm";
  $selects[] = " ( SELECT acronym FROM groups WHERE groups.groups_id = teaching.teacher_groups_id ) AS teacher_group_acronym ";
  $selects[] = " ( SELECT acronym FROM groups WHERE groups.groups_id = teaching.signer_groups_id ) AS signer_group_acronym ";
  $selects[] = " TRIM( CONCAT( creator.title, ' ', creator.gn, ' ', creator.sn ) ) AS creator_cn ";
  $selects[] = " TRIM( CONCAT( teacher.title, ' ', teacher.gn, ' ', teacher.sn ) ) AS teacher_cn ";
  $selects[] = " TRIM( CONCAT( signer.title, ' ', signer.gn, ' ', signer.sn ) ) AS signer_cn ";

  $filters = sql_canonicalize_filters( 'teaching'
  , $filters_in
  , $joins
  , array(
      'REGEX' => array( '~=' , "CONCAT(
        teacher.sn, ';', teacher.title, ';', teacher.gn, ';'
      , signer.sn, ';', signer.gn, ';', creator.sn, ';', creator.gn, ';'
      , course_title, ';', course_number, ';', module_number )"
      )
    , 'creator_groups_id' => 'creator_affiliations.groups_id'
  ) );

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
      error( "undefined op: [$op]", LOG_FLAG_CODE, 'teaching,sql' );
  }
  $s = sql_query( $op, 'teaching', $filters, $selects, $joins, $orderby, $groupby );
  // debug( $s, 's' );
  return $s;
}

function sql_teaching( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'year,term';
  $sql = sql_query_teaching( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_teaching( $filters = array(), $default = false ) {
  $sql = sql_query_teaching( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_teaching( $filters, $check = false ) {
  $problems = array();
  if( $check )
    return $problems;
  need( ! $problems );
  logger( "delete teaching [$filters]", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'teaching' );
  sql_delete( 'teaching', $filters );
}





?>
