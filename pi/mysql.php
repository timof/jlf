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
  $selects['cn'] = "TRIM( CONCAT( title, ' ', gn, ' ', sn ) )";
  $selects['primary_groups_id'] = " primary_affiliation.groups_id ";
  $selects['primary_groupname'] = " primary_group.acronym";
  $selects['primary_telephonenumber'] = " primary_affiliation.telephonenumber";
  $selects['primary_mail'] = 'primary_affiliation.mail';
  $selects['primary_roomnumber'] = 'primary_affiliation.roomnumber';

  $opts = default_query_options( 'people', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'people.sn, people.gn'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'people,affiliations'
  , $filters
  , $opts['joins']
  , $selects
  , array(
      'REGEX' => array( '~=', "CONCAT( sn, ';', title, ';', gn, ';'
                                     , primary_affiliation.roomnumber, ';', primary_affiliation.telephonenumber, ';'
                                     , primary_affiliation.mail, ';', primary_affiliation.facsimiletelephonenumber )" )
    // , 'INSTITUTE' => array( '=', '(people.flags & '.PEOPLE_FLAG_INSTITUTE.')', PEOPLE_FLAG_INSTITUTE )
    // , 'VIRTUAL' => array( '=', '(people.flags & '.PEOPLE_FLAG_VIRTUAL.')', PEOPLE_FLAG_VIRTUAL )
    , 'USER' => array( '>=', 'people.privs', PERSON_PRIV_USER )
    , 'HEAD' => 'groups.head_people_id=people.people_id'
    , 'SECRETARY' => 'groups.secretary_people_id=people.people_id'
    )
  );

  $s = sql_query( 'people', $opts );
  return $s;
}

function sql_delete_people( $filters, $check = false ) {
  global $login_people_id;

  $problems = array();
  $people = sql_people( $filters );
  foreach( $people as $p ) {
    $people_id = $p['people_id'];
    $problems = sql_delete_affiliations( "people_id=$people_id", 'check' );
    if( ! have_priv( 'person', 'delete', $people_id ) ) {
      $problems[] = we( 'insufficient privileges to delete person ','keine Berechtigung zum Löschen der Person' );
    }
    if( $people_id === $login_people_id ) {
      $problems[] = we( 'cannot delete yourself','eigener account nicht löschbar' );
    }
    $references = sql_references( 'people', $people_id, 'ignore=persistent_vars changelog affiliations' );
    if( $references ) {
      $problems[] = we('cannot delete: references exist: ','nicht löschbar: Verweise vorhanden: ').implode( ', ', array_keys( $references ) );
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems, $problems );
  foreach( $people as $p ) {
    $people_id = $p['people_id'];
    sql_delete_affiliations( "people_id=$people_id" );
    $references = sql_references( 'people', $people_id, 'ignore=changelog,prune=persistent_vars' ); 
    if( $references ) {
      sql_update( 'people', $people_id, array( 'flag_deleted' => 1 ) );
      logger( "delete person [$people_id]: marked as deleted due to existing references", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'people' );
    } else {
      $references = sql_references( 'people', $people_id, 'reset=changelog' ); 
      need( ! $references );
      sql_delete( 'people', $people_id );
      logger( "delete person [$people_id]: deleted physically", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'people' );
    }
  }
}

function sql_save_person( $people_id, $values, $aff_values = array(), $opts = array() ) {
  global $login_people_id;

  $opts = parameters_explode( $opts, 'check' );
  $check = adefault( $opts, 'check', false );
  $problems = array();
  $opts['update'] = $people_id;

  if( $people_id ) {
    logger( "start: update person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'person', array( 'person_view' => "people_id=$people_id" ) );
    $problems = priv_problems( 'person', 'edit', $people_id );
  } else {
    logger( "start: insert person", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'person' );
    $problems = priv_problems( 'person', 'create' );
  }

  if( ! isset( $values['authentication_methods'] ) ) {
    if( isset( $values['authentication_method_simple'] ) && isset( $values['authentication_method_ssl'] ) ) {
      $values['authentication_methods'] = ',';
      if( $values['authentication_method_simple'] ) {
        $values['authentication_methods'] .= 'simple,';
      }
      if( $values['authentication_method_ssl'] ) {
        $values['authentication_methods'] .= 'ssl,';
      }
    }
  }
  unset( $values['authentication_method_simple'] );
  unset( $values['authentication_method_ssl'] );

  // use auth_set_password() to set or change password:
  //
  unset( $values['password_hashvalue'] );
  unset( $values['password_hashfunction'] );
  unset( $values['password_salt'] );

  if( ! have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    // only admin can create or change accounts and privileges:
    unset( $values['uid'] );
    unset( $values['privs'] );
    unset( $values['authentication_methods'] );
    // only admin can change status flags:
    unset( $values['flag_virtual'] );
    unset( $values['flag_deleted'] );
  }

  if( ! $problems ) {
    $problems = validate_row( 'people', $values, $opts );
    foreach( $aff_values as $v ) {
      $problems += validate_row( 'affiliations', $v, $opts );
    }
  }
  if( ! $problems ) {
    if( $people_id ) {
      $person = sql_person( $people_id );
      $aff = sql_affiliations( "people_id=$people_id" );
      if( $person['privs'] >= PERSON_PRIV_ADMIN ) {
        // only admin can modify admin:
        have_minimum_person_priv( PERSON_PRIV_ADMIN ) || ( $problems[] = 'insufficient privileges' );
      } else if( $person['privs'] >= PERSON_PRIV_USER ) {
        if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
          // restrict changes to accounts:
          unset( $values['sn'] );
          unset( $values['gn'] );
          unset( $values['cn'] );
  
          // only coordinator and admin can change group affiliations for accounts,
          // because access to many items depends on group affiliation:
          //
          ( count( $aff ) === count( $aff_values ) ) || ( $problems[] = 'person with account - insufficient privileges to change affiliations' );
          for( $j = 0; $j < count( $aff ); $j++ ) {
            unset( $aff_values[ $j ]['groups_id'] );
          }
        }
      }
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems, $problems );

  if( $people_id ) {
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
      sql_delete_affiliations( "people_id=$people_id" );
      $j = 0;
      foreach( $aff_values as $v ) {
        $v['people_id'] = $people_id;
        $v['priority'] = $j++;
        sql_insert( 'affiliations', $v );
      }
    }
  } else {

    $people_id = sql_insert( 'people', $values );
    $j = 0;
    foreach( $aff_values as $v ) {
      $v['people_id'] = $people_id;
      $v['priority'] = $j++;
      sql_insert( 'affiliations', $v );
    }
    logger( "new person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'person', array( 'person_view' => "people_id=$people_id" ) );
  }

  return $people_id;
}


////////////////////////////////////
//
// affiliations functions:
//
////////////////////////////////////

function sql_affiliations( $filters = array(), $opts = array() ) {

  $opts = default_query_options( 'affiliations', $opts, array(
    'joins' => array( 'people USING ( people_id )', 'LEFT groups USING ( groups_id )' )
  , 'selects' => sql_default_selects( array( 'affiliations', 'people' => 'prefix=1', 'groups' => 'prefix=1' ) )
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
  sql_delete( 'affiliations', $filters );
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
  $selects = sql_default_selects( array( 'groups', 'head' => 'table=people,prefix=head_,.people_id=', 'secretary' => 'table=people,prefix=secretary_,.people_id=' ) );
  $selects['head_sn'] = 'head.sn';
  $selects['head_gn'] = 'head.gn';
  $selects['head_cn'] = "TRIM( CONCAT( head.title, ' ', head.gn, ' ', head.sn ) )";
  $selects['secretary_sn'] = 'secretary.sn';
  $selects['secretary_gn'] = 'secretary.gn';
  $selects['secretary_cn'] = "TRIM( CONCAT( secretary.title, ' ', secretary.gn, ' ', secretary.sn ) )";

  if( $GLOBALS['language'] == 'D' ) {
    $selects['cn_we'] = "groups.cn";
    $selects['url_we'] = "groups.url";
    $selects['note_we'] = "groups.note";
  } else {
    $selects['cn_we'] = "IF( groups.cn_en != '', groups.cn_en, groups.cn )";
    $selects['url_we'] = "IF( groups.url_en != '', groups.url_en, groups.url )";
    $selects['note_we'] = "IF( groups.note_en != '', groups.note_en, groups.note )";
  }
  $opts = default_query_options( 'groups', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => '( groups.flags & '.GROUPS_FLAG_INSTITUTE.') DESC,groups.cn'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'groups,people', $filters, $joins, $selects, array(
    'INSTITUTE' => array( '=', '(groups.flags & '.GROUPS_FLAG_INSTITUTE.')', GROUPS_FLAG_INSTITUTE )
  , 'ACTIVE' => array( '=', '(groups.flags & '.GROUPS_FLAG_ACTIVE.')', GROUPS_FLAG_ACTIVE )
  , 'LIST' => array( '=', '(groups.flags & '.GROUPS_FLAG_LIST.')', GROUPS_FLAG_LIST )
  ) );

  $s = sql_query( 'groups', $opts );
  return $s;
}

function sql_one_group( $filters = array(), $default = false ) {
  return sql_groups( $filters, array( 'default' => $default, 'single_row' => true ) );
}


function sql_save_group( $groups_id, $values, $opts = array() ) {
  if( $groups_id ) {
    logger( "start: update group [$groups_id]", LOG_LEVEL_DEBUG, LOG_FLAG_UPDATE, 'group', array( 'group_view' => "groups_id=$groups_id" ) );
    need_priv( 'groups', 'edit', $groups_id );
  } else {
    logger( "start: insert group", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'group' );
    need_priv( 'groups', 'create' );
  }
  $opts = parameters_explode( $opts );
  $opts['update'] = $groups_id;
  $check = adefault( $opts, 'check' );

  if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    unset( $values['flags'] );
  }
  if( ! ( $problems = validate_row( 'groups', $values, $opts ) ) ) {
    if( ( $id = adefault( $values, 'head_people_id' ) ) ) {
      if( sql_person( array( 'people_id' => "$id", 'groups_id' => $groups_id ), NULL ) === NULL ) {
        logger( "head [$id] not found in group", LOG_LEVEL_ERROR, LOG_FLAG_INPUT );
        $problems['head_people_id'] = 'selected head not found in group';
      }
    }
    if( ( $id = adefault( $values, 'secretary_people_id' ) ) ) {
      if( sql_person( array( 'people_id' => "$id", 'groups_id' => $groups_id ), NULL ) === NULL ) {
        logger( "secretary [$id] not found in group", LOG_LEVEL_ERROR, LOG_FLAG_INPUT );
        $problems['secretary_people_id'] = 'selected secretary not found in group';
      }
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems );
  if( $groups_id ) {
    sql_update( 'groups', $groups_id, $values );
    logger( "updated group [$groups_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'group', array( 'group_view' => "groups_id=$groups_id" ) );
  } else {
    $groups_id = sql_insert( 'groups', $values );
    logger( "new group [$groups_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'group', array( 'group_view' => "groups_id=$groups_id" ) );
  }
  return $groups_id;
}

function sql_delete_groups( $filters, $check = false ) {
  $problems = array();
  $groups = sql_groups( $filters );
  foreach( $groups as $g ) {
    $groups_id = $g['groups_id'];
    $problems += priv_problems( 'groups', 'delete', $groups_id );
    if( ! $problems ) {
      if( sql_people( "groups_id=$groups_id" ) ) {
        $problems[] = we('cannot delete group(s) - members exist','Gruppe(n) koennen nicht gelöscht werden - Mitglieder vorhanden!');
        break;
      }
      if( sql_positions( "groups_id=$groups_id" ) ) {
        $problems[] = we('cannot delete group(s) - positions exist', 'Gruppe(n) koennen nicht gelöscht werden - offene Stellen vorhanden!');
        break;
      }
    }
    if( ! $problems ) {
      $references = sql_references( 'groups', $groups_id, 'ignore=changelog' );
      if( $references ) {
        $problems[] = we('cannot delete: references exist: ','nicht löschbar: Verweise vorhanden: ').implode( ', ', array_keys( $references ) );
      }
    }
  }
  if( $check )
    return $problems;
  need( ! $problems, $problems );
  foreach( $groups as $g ) {
    $groups_id = $g['groups_id'];
    $references = sql_references( 'people', $people_id, 'reset=changelog' ); 
    need( ! $references );
    sql_delete( 'groups', $groups_id );
    logger( "delete group [$groups_id]: deleted", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'groups' );
  }
}


////////////////////////////////////
//
// positions functions:
//
////////////////////////////////////

function sql_positions( $filters = array(), $opts = array() ) {

  $joins = array(
    'LEFT groups USING ( groups_id )'
  , 'LEFT people ON people.people_id = contact_people_id'
  );
  $selects = sql_default_selects( array(
    'positions'
  , 'groups' => array( '.cn' => 'groups_cn', '.url' => 'groups_url', 'aprefix' => '' )
  , 'people' => array( 'aprefix' => '' )
  ) );
  $opts = default_query_options( 'positions', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'groups.cn,positions.cn'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'positions,groups', $filters, $opts['joins'], $opts['selects'], array(
      'REGEX' => array( '~=', "CONCAT( ';', positions.cn, ';', groups.cn, ';', IFNULL( people.cn, '' ) , ';' )" )
  ) );
  foreach( $opts['filters'][ 1 ] as $index => & $atom ) {
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
    unset( $opts['filters'][ 1 ][ $index ] );
    $opts['filters'][ 2 ][] = & $atom;
  }

  $s = sql_query( 'positions', $opts );
  return $s;
}

function sql_one_position( $filters = array(), $default = false ) {
  return sql_positions( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_delete_positions( $filters, $check = false ) {
  $problems = array();
  $positions = sql_positions( $filters );
  foreach( $positions as $p ) {
    $positions_id = $p['positions_id'];
    $problems += priv_problems( 'positions', 'delete', $positions_id );
    $references = sql_references( 'positions', $positions_id, 'ignore=changelog' );
    if( $references ) {
      $problems[] = we('cannot delete: references exist: ','nicht löschbar: Verweise vorhanden: ').implode( ', ', array_keys( $references ) );
    }
  }
  if( $check )
    return $problems;
  need( ! $problems );
  foreach( $positions as $p ) {
    $positions_id = $p['positions_id'];
    $references = sql_references( 'positions', $positions_id, 'reset=changelog' );
    need( ! $references );
    sql_delete( 'positions', $positions_id );
    logger( "delete position [$positions_id]: deleted", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'positions' );
  }
}

function sql_save_position( $positions_id, $values, $opts = array() ) {
  if( $positions_id ) {
    logger( "start: update position [$positions_id]", LOG_LEVEL_DEBUG, LOG_FLAG_UPDATE, 'position', array( 'position_view' => "positions_id=$positions_id" ) );
    need_priv( 'positions', 'edit', $positions_id );
  } else {
    logger( "start: insert position", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'position' );
    need_priv( 'positions', 'create' );
  }
  $opts = parameters_explode( $opts );
  $opts['update'] = $groups_id;
  $check = adefault( $opts, 'check' );
  $problems = validate_row('positions', $values, $opts );
  if( $check ) {
    return $problems;
  }
  need( ! $problems );
  if( $positions_id ) {
    sql_update( 'positions', $positions_id, $values );
    logger( "updated position [$positions_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'position', array( 'position_view' => "positions_id=$positions_id" ) );
  } else {
    $positions_id = sql_insert( 'positions', $values );
    logger( "new position [$positions_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'position', array( 'position_view' => "positions_id=$positions_id" ) );
  }
  return $positions_id;
}


////////////////////////////////////
//
// exams functions
//
////////////////////////////////////

function sql_exams( $filters = array(), $opts = array() ) {

  $joins = array( 'LEFT people ON teacher_people_id = people.people_id' );
  $selects = sql_default_selects( 'exams' );
  $selects['teacher_cn'] = " TRIM( CONCAT( people.title, ' ', people.gn, ' ', people.sn ) )";
  $selects['year'] = "substr(utc,1,4)";
  $selects['month'] = "substr(utc,5,2)";
  $selects['day'] = "substr(utc,7,2)";
  $selects['hour'] = "substr(utc,9,2)";
  $selects['minute'] = "substr(utc,11,2)";

  $opts = default_query_options( 'exams', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'exams.utc,exams.semester'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'exams', $filters );
  foreach( $opts['filters'][ 1 ] as & $atom ) {
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
        need( $rel === '=' );
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

  $joins = array( 'initiator' => 'people ON initiator_people_id = initiator.people_id' );
  $selects = sql_default_selects( 'surveys' );
  $selects['surveyfields_count'] = " ( SELECT COUNT(*) FROM surveyfields WHERE surveyfields.surveys_id = surveys.surveys_id )";
  $selects['surveysubmissions_count'] = " ( SELECT COUNT(*) FROM surveysubmissions WHERE surveysubmissions.surveys_id = surveys.surveys_id )";
  $selects['initiator_cn'] = " TRIM( CONCAT( initiator.title, ' ', initiator.gn, ' ', initiator.sn ) )";
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
  , 'initiator' => 'people ON initiator_people_id = initiator.people_id'
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
  $selects['creator_cn'] = "TRIM( CONCAT( creator.title, ' ', creator.gn, ' ', creator.sn ) )";
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
  , 'teacher_group' => 'LEFT groups ON teaching.teacher_groups_id = teacher_group.groups_id'
  , 'signer' => 'LEFT people ON teaching.signer_people_id = signer.people_id'
  , 'signer_group' => 'LEFT groups ON teaching.signer_groups_id = signer_group.groups_id'
  , 'creator_session' => 'LEFT sessions ON teaching.creator_sessions_id = creator_session.sessions_id'
  , 'creator' => 'LEFT people ON teaching.creator_people_id = creator.people_id'
  , 'creator_affiliations' => 'LEFT affiliations ON teaching.creator_people_id = creator_affiliations.people_id'
  );
  $selects = sql_default_selects( 'teaching' );
  // $selects['yearterm'] = "CONCAT( IF( teaching.term = 'W', 'WiSe', 'SoSe' ), ' ', teaching.year, IF( teaching.term = 'W', teaching.year - 1999, '' ) )";
  $selects['teacher_group_acronym'] = "teacher_group.acronym";
  $selects['signer_group_acronym'] = "signer_group.acronym";
  $selects['creator_cn'] = " TRIM( CONCAT( creator.title, ' ', creator.gn, ' ', creator.sn ) )";
  $selects['teacher_cn'] = " IF( teaching.extern, teaching.extteacher_cn, TRIM( CONCAT( teacher.title, ' ', teacher.gn, ' ', teacher.sn ) ) )";
  $selects['signer_cn'] = " TRIM( CONCAT( signer.title, ' ', signer.gn, ' ', signer.sn ) )";

  $opts = default_query_options( 'teaching', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'year,term'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'teaching'
  , $filters
  , $joins
  , $selects
  , array(
      'REGEX' => array( '~=' , "CONCAT(
        IF( teaching.extern, teaching.extteacher_cn, concat( teacher.sn, ';', teacher.title, ';', teacher.gn ) ), ';'
      , IF( signer.people_id is null, '', concat( signer.sn, ';', signer.gn, ';' ) )
      , IF( creator.people_id is null, '', concat( creator.sn, ';', creator.gn, ';' ) )
      , course_title, ';', course_number, ';', module_number )"
      )
    , 'INSTITUTE' => 'teacher_group.flags & '.GROUPS_FLAG_INSTITUTE
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
  $teaching = sql_teaching( $filters );
  foreach( $teaching as $t ) {
    $teaching_id = $t['teaching_id'];
    $problems += priv_problems( 'teaching', 'delete', $teaching_id );
    if( ! $problems ) {
      $references = sql_references( 'teaching', $teaching_id, 'ignore=changelog' );
      if( $references ) {
        $problems[] = we('cannot delete: references exist: ','nicht löschbar: Verweise vorhanden: ').implode( ', ', array_keys( $references ) );
      }
    }
  }
  if( $check )
    return $problems;
  need( ! $problems );
  logger( "delete teaching [$filters]", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'teaching' );
  foreach( $teaching as $t ) {
    $teaching_id = $t['teaching_id'];
    $references = sql_references( 'teaching', $teaching_id, 'reset=changelog' );
    need( ! $references );
    sql_delete( 'teaching', $teaching_id );
    logger( "delete teaching [$teaching_id]: deleted", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'teaching' );
  }
}


function sql_save_teaching( $teaching_id, $values, $opts = array() ) {
  global $login_people_id;

  if( $teaching_id ) {
    logger( "start: update teaching [$teaching_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'teaching', array( 'teachinglist' => "teaching_id=$teaching_id,options=".OPTION_TEACHING_EDIT ) );
    need_priv( 'teaching', 'edit', $teaching_id );
  } else {
    logger( "start: insert teaching", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'teaching' );
    need_priv( 'teaching', 'create' );
  }

  if( $values['extern'] ) {
    $values['teacher_groups_id'] = $values['teacher_people_id'] = 0;
  } else {
    $values['extteacher_cn'] = '';
  }
  if( $teaching_id ) {
    sql_update( 'teaching', $teaching_id, $values );
    logger( "updated teaching [$teaching_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'teaching', array(
      'teachinglist' => "teaching_id=$teaching_id,options=".OPTION_TEACHING_EDIT
    , "script=person_view,people_id={$values['teacher_people_id']},text=teacher"
    , "script=person_view,people_id=$login_people_id,text=updater"
    , "script=person_view,people_id={$values['signer_people_id']},text=signer"
    ) );
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
