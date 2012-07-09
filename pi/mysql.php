<?php



////////////////////////////////////
//
// people functions:
//
////////////////////////////////////

function sql_people( $filters = array(), $opts = array() ) {

  $joins = array(
    'affiliations' => 'LEFT affiliations USING ( people_id )'
  , 'groups' => 'LEFT groups USING ( groups_id )'
  , 'primary_affiliation' => 'LEFT affiliations ON ( ( primary_affiliation.people_id = people.people_id ) AND ( primary_affiliation.priority = 0 ) )'
  , 'primary_group' => 'LEFT groups ON ( primary_group.groups_id = primary_affiliation.groups_id )'
  );
  $selects = sql_default_selects( 'people' );
  $selects[] = " TRIM( CONCAT( title, ' ', gn, ' ', sn ) ) AS cn ";
  $selects[] = " primary_affiliation.groups_id AS primary_groups_id ";
  $selects[] = " primary_group.acronym AS primary_groupname ";
  $selects[] = " primary_affiliation.telephonenumber AS primary_telephonenumber ";
  $selects[] = " primary_affiliation.mail AS primary_mail ";
  $selects[] = " primary_affiliation.roomnumber AS primary_roomnumber ";

  $opts = default_query_options( 'people', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'people.sn, people.gn'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'people,affiliations'
  , $filters
  , $opts['joins']
  , array(
      'REGEX' => array( '~=', "CONCAT( sn, ';', title, ';', gn, ';'
                                     , primary_affiliation.roomnumber, ';', primary_affiliation.telephonenumber, ';'
                                     , primary_affiliation.mail, ';', primary_affiliation.facsimiletelephonenumber )" )
    , 'INSTITUTE' => array( '=', '(people.flags & '.PEOPLE_FLAG_INSTITUTE.')', PEOPLE_FLAG_INSTITUTE )
    , 'NOPERSON' => array( '=', '(people.flags & '.PEOPLE_FLAG_NOPERSON.')', PEOPLE_FLAG_NOPERSON )
    , 'USER' => array( '>=', 'people.privs', PERSON_PRIV_USER )
    , 'HEAD' => 'groups.head_people_id=people.people_id'
    , 'SECRETARY' => 'groups.secretary_people_id=people.people_id'
    )
  );

  $s = sql_query( 'people', $opts );
  return $s;
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

function sql_save_person( $people_id, $values, $aff_values = array() ) {
  global $login_people_id;

  if( $people_id ) {
    logger( "update person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'person', array( 'person_view' => "people_id=$people_id" ) );
  } else {
    logger( "insert person", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'person' );
  }

  // check privileges:

  need( have_minimum_person_priv( PERSON_PRIV_USER ), 'insufficient privileges' );

  if( ! have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    // only admin can create or change accounts and privileges:
    unset( $values['uid'] );
    unset( $values['privs'] );
    unset( $values['authentication_methods'] );
    // only admin and self can change passwords
    if( (int)$people_id !== (int)$login_people_id ) {
      unset( $values['password_hashvalue'] );
      unset( $values['password_hashfunction'] );
      unset( $values['salt'] );
    }
  }

  if( $people_id ) {

    if( ! have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
      // only admin can change status flags:
      unset( $values['flags'] );
    }

    $person = sql_person( $people_id );
    $aff = sql_affiliations( "people_id=$people_id" );
    if( $person['privs'] >= PERSON_PRIV_ADMIN ) {
      // only admin can modify admin:
      need( have_minimum_person_priv( PERSON_PRIV_ADMIN ), 'insufficient privileges' );
    } else if( $person['privs'] >= PERSON_PRIV_USER ) {
      if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        // restrict changes to accounts:
        unset( $values['sn'] );
        unset( $values['gn'] );
        unset( $values['cn'] );

        // only coordinator and admin can change group affiliations for accounts,
        // because access to many items depends on group affiliation:
        //
        need( count( $aff ) === count( $aff_values ) );
        for( $j = 0; $j < count( $aff ); $j++ ) {
          unset( $aff_values[ $j ]['groups_id'] );
        }
      }
    }

    sql_update( 'people', $people_id, $values );

    if( count( $aff ) === count( $aff_values ) ) {
      $j = 0;
      foreach( $aff_values as $v ) {
        $id = $aff[ $j ]['affiliations_id'];
        $v['people_id'] = $people_id;
        $v['priority'] = $j++;
        sql_update( 'affiliations', $id, $v );
      }

    } else {
      sql_delete_affiliations( "people_id=$people_id", NULL );
      $j = 0;
      foreach( $aff_values as $v ) {
        $v['people_id'] = $people_id;
        $v['priority'] = $j++;
        sql_insert( 'affiliations', $v );
      }
    }
  } else {

    if( ! have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
      $flags = adefault( $values, 'flags', PEOPLE_FLAG_INSTITUTE );
      // only admin can create pure accounts:
      $values['flags'] = $flags & ~PEOPLE_FLAG_NOPERSON;
    }

    $people_id = sql_insert( 'people', $values );
    $j = 0;
    foreach( $aff_values as $v ) {
      $v['people_id'] = $people_id;
      $v['priority'] = $j++;
      sql_insert( 'affiliations', $v );
    }
    logger( "new person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'person', array( 'person_view' => "people_id=$people_id" ) );
  }
}


////////////////////////////////////
//
// affiliations functions:
//
////////////////////////////////////

function sql_affiliations( $filters = array(), $opts = array() ) {

  $opts = default_query_options( 'affiliations', $opts, array(
    'joins' => array( 'people USING ( people_id )', 'LEFT groups USING ( groups_id )' )
  , 'selects' => sql_default_selects( 'affiliations,people,groups' )
  , 'orderby' => 'affiliations.priority,groups.cn'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'affiliations,people,groups', $filters );

  $s = sql_query( 'affiliations', $opts );
  return $s;
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

function sql_groups( $filters = array(), $opts = array() ) {

  $joins = array(
    'head' => 'LEFT people ON ( head.people_id = groups.head_people_id )'
  , 'secretary' => 'LEFT people ON ( secretary.people_id = groups.secretary_people_id )'
  );
  $selects = sql_default_selects( array( 'groups', 'head' => 'table=people,prefix=head', 'secretary' => 'table=people,prefix=secretary' ) );
  $selects[] = ' COUNT(*) AS mitgliederzahl';
  $groupby = 'groups.groups_id';
  $selects[] = 'head.sn  AS head_sn';
  $selects[] = 'head.gn  AS head_gn';
  $selects[] = "CONCAT( head.gn, ' ', head.sn ) AS head_cn";
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
  $opts = default_query_options( 'groups', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'groups.cn'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'groups,people', $filters );

  $s = sql_query( 'groups', $opts );
  return $s;
}

function sql_one_group( $filters = array(), $default = false ) {
  return sql_groups( $filters, array( 'default' => $default, 'single_row' => true ) );
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


function sql_positions( $filters = array(), $opts = array() ) {

  $joins = array(
    'groups USING ( groups_id )'
  , 'people ON people.people_id = contact_people_id'
  );
  $selects = sql_default_selects( 'positions,groups', array( 'groups.cn' => 'groups_cn', 'groups.url' => 'groups_url' ) );
  $opts = default_query_options( 'positions', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'groups.cn,positions.cn'
  ) );

  $opts['filters']= sql_canonicalize_filters( 'positions,groups', $filters );
  foreach( $opts['filters'] as & $atom ) {
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

  $s = sql_query( 'positions', $opts );
  return $s;
}

function sql_one_position( $filters = array(), $default = false ) {
  return sql_positions( $filters, array( 'default' => $default, 'single_row' => true ) );
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

function sql_exams( $filters = array(), $opts = array() ) {

  $joins = array( 'LEFT people ON teacher_people_id = people.people_id' );
  $selects = sql_default_selects( 'exams' );
  $selects[] = " TRIM( CONCAT( people.title, ' ', people.gn, ' ', people.sn ) ) as teacher_cn ";
  $selects[] = "substr(utc,1,4) as year";
  $selects[] = "substr(utc,5,2) as month";
  $selects[] = "substr(utc,7,2) as day";
  $selects[] = "substr(utc,9,2) as hour";
  $selects[] = "substr(utc,11,2) as minute";

  $opts = default_query_options( 'exams', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'exams.utc,exams.semester'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'exams', $filters );
  foreach( $opts['filters'] as & $atom ) {
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

  $s = sql_query( 'exams', $opts );
  return $s;
}
function sql_one_exam( $filters = array(), $default = false ) {
  return sql_exams( $filters, array( 'single_row' => true, 'default' => $default ) );
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

function sql_surveys( $filters = array(), $opts = array() ) {

  $joins = array( 'people ON initiator_people_id = people.people_id' );
  $selects = sql_default_selects( 'surveys' );
  $selects[] = " ( SELECT COUNT(*) FROM surveyfields WHERE surveyfields.surveys_id = surveys.surveys_id ) AS surveyfields_count ";
  $selects[] = " ( SELECT COUNT(*) FROM surveysubmissions WHERE surveysubmissions.surveys_id = surveys.surveys_id ) AS surveysubmissions_count ";
  $selects[] = " TRIM( CONCAT( people.title, ' ', people.gn, ' ', people.sn ) ) AS initiator_cn ";
  $opts = default_query_options( 'surveys', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'closed,deadline,ctime'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'surveys,people', $filters );

  $s = sql_query( 'surveys', $opts );
  return $s;
}

function sql_one_survey( $filters = array(), $default = false ) {
  return sql_surveys(  $filters, array( 'single_row' => true, 'default' => $default ) );
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

function sql_surveyfields( $filters = array(), $opts = array() ) {

  $selects = sql_default_selects( 'surveyfields,surveys' );
  $joins = array(
    'surveys USING ( surveys_id )'
  , 'people ON initiator_people_id = people.people_id'
  );
  $opts = default_query_options( 'surveyfields', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'surveys.deadline,surveyfields.surveys_id,surveyfields.priority'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'surveyfields,surveys,people', $filters );

  $s = sql_query( 'surveyfields', $opts );
  return $s;
}

function sql_one_surveyfield( $filters = array(), $default = false ) {
  return sql_surveyfields( $filters, array( 'single_row' => true, 'default' => $default ) );
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

function sql_surveysubmissions( $filters = array(), $opts = array() ) {

  $joins = array(
    'surveys' => 'surveys USING ( surveys_id )'
  , 'creator_session' => 'LEFT sessions ON creator_sessions_id = sessions.sessions_id'
  , 'creator' => 'LEFT people ON surveysubmissions.creator_people_id = creator.people_id'
  );
  $selects = sql_default_selects( 'surveysubmissions,surveys' );
  $selects[] = " TRIM( CONCAT( creator.title, ' ', creator.gn, ' ', creator.sn ) ) AS creator_cn ";
  $opts = default_query_options( 'surveysubmissions', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'surveys.deadline,creator_cn'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'surveysubmissions,surveys,people', $filters );

  $s = sql_query( 'surveysubmissions', $opts );
  return $s;
}

function sql_one_surveysubmission( $filters = array(), $default = false ) {
  return sql_surveysubmissions( $filters, array( 'single_row' => true, 'default' => $default ) );
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

function sql_surveyreplies( $filters = array(), $opts = array() ) {

  $selects = sql_default_selects( 'surveyreplies,surveysubmissions,surveyfields,surveys' );
  $joins = array(
    'surveysubmissions' => 'surveysubmissions USING ( surveysubmissions_id )'
  , 'surveyfields' => 'surveyfields USING ( surveyfields_id )'
  , 'surveys' => 'surveys USING ( surveys_id )'
  , 'creator_session' => 'LEFT sessions ON creator_sessions_id = sessions.sessions_id'
  , 'creator' => 'LEFT people ON surveyreplies.creator_people_id = creator.people_id'
  );
  $opts = default_query_options( 'surveyreplies', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'surveys.deadline,creator_cn,surveyfields.priority'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'surveyreplies,surveyfields,surveys,surveysubmissions', $filters );

  $s = sql_query( 'surveyfields', $opts );
  return $s;
}

function sql_one_surveyreply( $filters = array(), $default = false ) {
  return sql_surveyreplies( $filters, array( 'single_row' => true, 'default' => $default ) );
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

function sql_teaching( $filters  = array(), $opts = array() ) {

  $joins = array(
    'teacher' => 'LEFT people ON teaching.teacher_people_id = teacher.people_id'
  , 'signer' => 'LEFT people ON teaching.signer_people_id = signer.people_id'
  , 'creator_session' => 'LEFT sessions ON teaching.creator_sessions_id = creator_session.sessions_id'
  , 'creator' => 'LEFT people ON teaching.creator_people_id = creator.people_id'
  // , 'LEFT affiliations AS creator_affiliations' => 'creator_session.login_people_id = creator_affiliations.people_id'
  );
  $selects = sql_default_selects( 'teaching' );
  $selects[] = "CONCAT( IF( teaching.term = 'W', 'WiSe', 'SoSe' ), ' ', teaching.year, IF( teaching.term = 'W', teaching.year - 1999, '' ) ) as yearterm";
  $selects[] = " ( SELECT acronym FROM groups WHERE groups.groups_id = teaching.teacher_groups_id ) AS teacher_group_acronym ";
  $selects[] = " ( SELECT acronym FROM groups WHERE groups.groups_id = teaching.signer_groups_id ) AS signer_group_acronym ";
  $selects[] = " TRIM( CONCAT( creator.title, ' ', creator.gn, ' ', creator.sn ) ) AS creator_cn ";
  $selects[] = " IF( teaching.extern, teaching.extteacher_cn, TRIM( CONCAT( teacher.title, ' ', teacher.gn, ' ', teacher.sn ) ) ) AS teacher_cn ";
  $selects[] = " TRIM( CONCAT( signer.title, ' ', signer.gn, ' ', signer.sn ) ) AS signer_cn ";

  $opts = default_query_options( 'teaching', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'year,term'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'teaching'
  , $filters
  , $joins
  , array(
      'REGEX' => array( '~=' , "CONCAT(
        if( teaching.extern, teaching.extteacher_cn, concat( teacher.sn, ';', teacher.title, ';', teacher.gn ) ), ';'
      , if( signer.people_id is null, '', concat( signer.sn, ';', signer.gn, ';' ) )
      , if( creator.people_id is null, '', concat( creator.sn, ';', creator.gn, ';' ) )
      , course_title, ';', course_number, ';', module_number )"
      )
    , 'creator_groups_id' => 'creator_affiliations.groups_id'
  ) );

  $s = sql_query( 'teaching', $opts );
  // debug( $s, 's' );
  return $s;
}

function sql_one_teaching( $filters = array(), $default = false ) {
  return sql_teaching( $filters, array( 'single_row' => true, 'default' => $default ) );
}

function sql_delete_teaching( $filters, $check = false ) {
  $problems = array();
  if( $check )
    return $problems;
  need( ! $problems );
  logger( "delete teaching [$filters]", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'teaching' );
  sql_delete( 'teaching', $filters );
}


function sql_save_teaching( $teaching_id, $values ) {
  global $login_people_id;
  // todo: check privileges
  if( $teaching_id ) {
    logger( "update teaching [$teaching_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'teaching', array(
      'teachinglist' => "teaching_id=$teaching_id,options=".OPTION_TEACHING_EDIT
    , "script=person_view,people_id={$values['teacher_people_id']},text=teacher"
    , "script=person_view,people_id=$login_people_id,text=updater"
    , "script=person_view,people_id={$values['signer_people_id']},text=signer"
    ) );
    sql_update( 'teaching', $teaching_id, $values );
  } else {
    logger( "insert teaching", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'teaching' );
    $teaching_id = sql_insert( 'teaching', $values );
    logger( "new teaching [$teaching_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'teaching', array(
      'teachinglist' => "teaching_id=$teaching_id,options=".OPTION_TEACHING_EDIT
    , "script=person_view,people_id={$values['teacher_people_id']},text=teacher"
    , "script=person_view,people_id=$login_people_id,text=updater"
    , "script=person_view,people_id={$values['signer_people_id']},text=signer"
    ) );
  }
  return $teaching_id;
}



?>
