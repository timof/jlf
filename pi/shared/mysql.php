<?php // pi/mysql.php

// for most tables, we have functions
// sql_save_<table>( $id, $values, $opts )
// - depending on $id, the function will insert or update an entry
// - option 'action' supports the following values:
//   - 'dryrun': just check for problems, don't write anything. returns array of problems detected; an empty array indicates that no problems were found
//   - 'hard': try to write and abort on any serious problem. if the function returns, the operation has succeeded and the primary key will be returned
//   - 'soft': try to write but handle problems gracefully. will return the primary key (numeric) on success, or array of problems in case of failure.
//             if any errors are returned, the db will be unchanged.
//             in case of late errors (after changing the db), the function will abort by calling error(), which will cause a ROLLBACK to undo any changes.



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
  , 'offices' => 'LEFT offices ON ( people.people_id = offices.people_id )'
  );
  $selects = sql_default_selects( 'people' );
  $selects['cn'] = "TRIM( CONCAT( title, ' ', gn, ' ', sn ) )";
  $selects['primary_groups_id'] = " primary_affiliation.groups_id ";
  $selects['primary_groupname'] = " primary_group.acronym";
  $selects['primary_telephonenumber'] = " primary_affiliation.telephonenumber";
  $selects['primary_mail'] = 'primary_affiliation.mail';
  $selects['primary_roomnumber'] = 'primary_affiliation.roomnumber';
  // $selects['teaching_obligation'] = 'SUM( affiliations.teaching_obligation )';
  //  ^ doesnt work (JOIN creates _cartesian_product_ containing multiple copies of same affiliation!), thus:
  $selects['teaching_obligation'] = ' ( SELECT SUM( teaching_obligation ) FROM affiliations AS teacher1 WHERE affiliations.people_id = people.people_id ) ';
  $selects['teaching_reduction'] = ' ( SELECT SUM( teaching_reduction ) FROM affiliations AS teacher2 WHERE affiliations.people_id = people.people_id ) ';
  $selects['typeofposition'] = "GROUP_CONCAT( DISTINCT affiliations.typeofposition SEPARATOR ', ' )";
  $selects['affiliations_groups_ids'] = "GROUP_CONCAT( DISTINCT groups.groups_id SEPARATOR ',' )";

  $opts = default_query_options( 'people', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'people.sn, people.gn'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'people,affiliations,offices'
  , $filters
  , $opts['joins']
  , $selects
  , array(
      'REGEX' => array( '~=', "CONCAT( title, ' ', gn, ' ', sn
                                     , ';', primary_affiliation.roomnumber
                                     , ';', primary_affiliation.telephonenumber
                                     , ';', primary_affiliation.mail
                                     , ';', primary_affiliation.facsimiletelephonenumber )" )
    // , 'INSTITUTE' => array( '=', '(people.flags & '.PEOPLE_FLAG_INSTITUTE.')', PEOPLE_FLAG_INSTITUTE ) )
    // , 'VIRTUAL' => array( '=', '(people.flags & '.PEOPLE_FLAG_VIRTUAL.')', PEOPLE_FLAG_VIRTUAL ) )
    //
    // predicate 'HEAD' works like this:
    // 'HEAD' -> array( '!0', 'HEAD', '' ) -> array( '!0', '(groups.head_people_id = people.people_id)', '' ) -> "(groups.head_people_id = people.people_id)"
    //
    , 'HEAD' => 'groups.head_people_id=people.people_id'
    , 'SECRETARY' => 'groups.secretary_people_id=people.people_id'
    , 'USER' => array( '>=', 'people.privs', PERSON_PRIV_USER )
    )
  );

  $s = sql_query( 'people', $opts );
  return $s;
}

// sql_save_person():
//   $aff_values: n-array of affiliation records:
//   - must be indexed by groups_id
//   - all desired group affiliations must be members in this array (but only columns to be updated need be set)
//
function sql_save_person( $people_id, $values, $aff_values = array(), $opts = array() ) {
  global $login_people_id;

  if( $people_id ) {
    logger( "start: update person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'person', array( 'person_view' => "people_id=$people_id" ) );
    $problems = priv_problems( 'person', 'edit', $people_id );

    $person = sql_person( $people_id );
    $edit_affiliations = have_priv( 'person', 'affiliations', $people_id );
    $rows = sql_affiliations( "people_id=$people_id", 'orderby=priority' );
    $aff_old = array();
    foreach( $rows as $r ) {
      $aff_old[ $r['groups_id'] ] = $r;
    }
  } else {
    logger( "start: insert person", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'person' );
    $problems = priv_problems( 'person', 'create' );

    $person = array();
    $edit_affiliations = true; // implied when creating a new person
    $aff_old = array();
  }

  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'hard' );

  //
  // normalize and validate 'people' record:
  //

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

  if( ! isset( $values['cn'] ) ) {
    $values['cn'] = trim( $values['gn'] . ' ' . $values['sn'] );
  }

  if( $people_id ) {
    if( ! have_priv( 'person', 'name', $people_id ) ) {
      unset( $values['sn'] );
      unset( $values['gn'] );
      unset( $values['cn'] );
    }
    if( adefault( $values, 'jpegphoto' ) ) {
      if( ! adefault( $values, 'jpegphotorights_people_id' ) ) {
        $values['jpegphotorights_people_id'] = $people_id;
      }
    }
  } else {
    unset( $values['jpegphoto'] );
    unset( $values['jpegphotorights_people_id'] );
  }
  if( ! have_priv( 'person', 'account', $people_id ) ) {
    unset( $values['uid'] );
    unset( $values['privs'] );
    unset( $values['authentication_methods'] );
  }
  if( ! have_priv( 'person', 'specialflags', $people_id ) ) {
    unset( $values['flag_virtual'] );
    unset( $values['flag_deleted'] );
  }

  $problems = validate_row( 'people', $values, "update=$people_id,action=soft" );

  //
  // normalize and validate affiliations:
  //

  foreach( $aff_values as $g_id => $aff ) {
    $aff_values[ $g_id ]['groups_id'] = $g_id;
  }

  if( ! have_priv( 'person', 'teaching_obligation', $people_id ) ) {
    foreach( $aff_values as $g_id => $aff ) {
      unset( $aff_values[ $g_id ]['teaching_obligation'] );
      unset( $aff_values[ $g_id ]['teaching_reduction'] );
      unset( $aff_values[ $g_id ]['teaching_reduction_reason'] );
    }
  }
  if( ! have_priv( 'person', 'position' ) ) {
    foreach( $aff_values as $g_id => $aff ) {
      unset( $aff_values[ $g_id ]['typeofposition'] );
    }
  } else if( ! have_priv( 'person', 'positionBudget' ) ) {
    foreach( $aff_values as $g_id => $aff ) {
      if( adefault( $aff, 'typeofposition' ) == 'H' ) {
        unset( $aff_values[ $g_id ]['typeofposition'] );
      }
    }
    foreach( $aff_old as $g_id => $aff ) {
      if( $aff['typeofposition'] == 'H' ) {
        if( ! isset( $aff_values[ $g_id ] ) ) {
          $problems += new_problem( we('no privilege to delete permanent position','Haushaltsstelle - kann nicht geloescht werden') );
        } else {
          unset( $aff_values[ $g_id ]['typeofposition'] );
        }
      }
    }
  }

  if( ! $edit_affiliations ) {
    if( count( $aff_values ) < count( $aff_old ) ) {
      $problems += new_problem('cannot delete affiliation');
    }
  }

  foreach( $aff_values as $g_id => $v ) {
    if( ! sql_one_group( $g_id, NULL ) ) {
      $problems += new_problem('no such group');
    }
    if( isset( $aff_old[ $g_id ] ) ) {
      $opts['update'] = $aff_old[ $g_id ]['affiliations_id'];
    } else {
      if( ! $edit_affiliations ) {
        $problems += new_problem('cannot create affiliation');
      }
      $opts['update'] = 0;
    }
    $problems += validate_row( 'affiliations', $v, $opts ); // may partially overwrite: we only get last error per aff column
  } 

  switch( $action ) {
    case 'hard':
      if( $problems ) {
        error( "sql_save_person() [$people_id]: ".reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_INPUT, 'people' );
      }
    case 'soft':
      if( ! $problems ) {
        continue;
      }
    case 'dryrun':
      return $problems;
    default:
      error( "sql_save_person() [$people_id]: unsupported action requested: [$action]", LOG_FLAG_CODE, 'people' );
  }

  if( $people_id ) {
    sql_update( 'people', $people_id, $values );

    foreach( $aff_values as $g_id => $v ) {
      unset( $v['affiliations_id'] );
      $v['people_id'] = $people_id;
      if( isset( $aff_old[ $g_id ] ) ) {
        $id = $aff_old[ $g_id ]['affiliations_id'];
        sql_update( 'affiliations', $id, $v );
        unset( $aff_old[ $g_id ] );
      } else {
        sql_insert( 'affiliations', $v );
      }
    }
    foreach( $aff_old as $a ) {
      sql_delete_affiliations( $a['affiliations_id'] );
    }

  } else {

    $people_id = sql_insert( 'people', $values );
    foreach( $aff_values as $v ) {
      unset( $v['affiliations_id'] );
      $v['people_id'] = $people_id;
      sql_insert( 'affiliations', $v );
    }
    logger( "new person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'person', array( 'person_view' => "people_id=$people_id" ) );
  }

  return $people_id;
}

function sql_delete_people( $filters, $opts = array() ) {
  global $login_people_id;

  $opts = parameters_explode( $opts, 'action' );
  $action = adefault( $opts, 'action', 'hard' );
  $logical = adefault( $opts, 'logical', '0' );
  $rv = init_rv_delete_action( adefault( $opts, 'rv' ) );
  $people = sql_people( $filters );
  foreach( $people as $p ) {
    $people_id = $p['people_id'];
    $problems = priv_problems( 'person', 'delete', $p );
    if( $people_id === $login_people_id ) {
      $problems += new_problem( we( 'cannot delete yourself', 'eigener account nicht löschbar' ) );
    }
    if( ! $problems ) {
      if( ! $logical ) {
        $problems = sql_references( 'people', $people_id, "return=report,delete_action=$action,prune=affiliations:people_id,ignore=people:$people_id" ); 
      }
    }
    $rv = sql_handle_delete_action( 'people', $people_id, $action, $problems, $rv, "logical=$logical,log=1" );
  }
  return $rv;
}

function sql_prune_people() {
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'try' );
  $rv = sql_delete_people( 'flag_deleted', "action=$action" );
  if( ( $count = $rv['deleted'] ) ) {
    logger( "prune_people: $count zombies deleted physically", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'people' );
  }
  return $rv;
}

////////////////////////////////////
//
// affiliations functions:
//
////////////////////////////////////

function sql_affiliations( $filters = array(), $opts = array() ) {
  global $language_suffix;

  $selects = sql_default_selects( array( 'affiliations' , 'people' => 'prefix=1' , 'groups' => 'prefix=1' ) );
  if( $language_suffix ) {
    $selects['groups_cn'] = "groups.cn_$language_suffix";
    $selects['groups_url'] = "groups.url_$language_suffix";
  }
  $opts = default_query_options( 'affiliations', $opts, array(
    'joins' => array( 'LEFT people USING ( people_id )', 'LEFT groups USING ( groups_id )' )
  , 'selects' => $selects
  , 'orderby' => 'affiliations.priority,groups.acronym'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'affiliations,people,groups', $filters, $opts['joins'], $opts['selects'], array(
    'HEAD' => 'groups.head_people_id=people.people_id'
  , 'SECRETARY' => 'groups.secretary_people_id=people.people_id'
  ) );

  $s = sql_query( 'affiliations', $opts );
  return $s;
}

function sql_delete_affiliations( $filters, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'hard' );
  $rows = sql_affiliations( $filters );
  $rv = init_rv_delete_action( adefault( $opts, 'rv' ) );
  foreach( $rows as $r ) {
    $id = $r['affiliations_id'];
    $people_id = $r['people_id'];
    $problems = priv_problems( 'person', 'affiliations', $people_id );
    if( ! $problems ) {
      $problems = sql_references( 'affiliations', $id, "return=report,delete_action=$action" );
    }
    $rv = sql_handle_delete_action( 'affiliations', $id, $action, $problems, $rv );
  }
  return $rv;
}

function sql_prune_affiliations( $opts = array() ) {
  // garbage collection only - no privilege check required
  // 
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'try' );
  $rv = sql_delete_affiliations( '`people.people_id IS NULL', "action=$action" );
  if( ( $count = $rv['deleted'] ) ) {
    logger( "prune_affiliations(): deleted $count orphaned affiliations", LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance' );
  }
  return $rv;
}


////////////////////////////////////
//
// groups functions:
//
////////////////////////////////////

function sql_groups( $filters = array(), $opts = array() ) {
  global $language_suffix;

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
  $selects['professor_groups_id'] = " IF( groups.status = ".GROUPS_STATUS_PROFESSOR." , groups.groups_id, groups.professor_groups_id ) ";

  $selects['cn'] = "groups.cn_$language_suffix";
  $selects['url'] = "groups.url_$language_suffix";
  $selects['note'] = "groups.note_$language_suffix";
//   if( $GLOBALS['language'] == 'D' ) {
//     $selects['cn'] = "IF( groups.cn_de != '', groups.cn_de, groups.cn_en )";
//     $selects['url'] = "IF( groups.url_de != '', groups.url_de, groups.url_en )";
//     $selects['note'] = "IF( groups.note_de != '', groups.note_de, groups.note_en )";
//   } else {
//     $selects['cn'] = "IF( groups.cn_en != '', groups.cn_en, groups.cn_de )";
//     $selects['url'] = "IF( groups.url_en != '', groups.url_en, groups.url_de )";
//     $selects['note'] = "IF( groups.note_en != '', groups.note_en, groups.note_de )";
//   }
  $opts = default_query_options( 'groups', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => "( groups.flags & '.GROUPS_FLAG_INSTITUTE.') DESC,groups.cn_$language_suffix"
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
  global $login_people_id;

  if( $groups_id ) {
    logger( "start: update group [$groups_id]", LOG_LEVEL_DEBUG, LOG_FLAG_UPDATE, 'group', array( 'group_view' => "groups_id=$groups_id" ) );
    need_priv( 'groups', 'edit', $groups_id );
  } else {
    logger( "start: insert group", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'group' );
    need_priv( 'groups', 'create' );
  }
  $opts = parameters_explode( $opts );
  $opts['update'] = $groups_id;
  $action = adefault( $opts, 'action', 'hard' );
  $problems = validate_row('groups', $values, $opts );

  if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    unset( $values['flags'] );
  }
  if( $groups_id ) {
    if( adefault( $values, 'jpegphoto' ) ) {
      if( ! adefault( $values, 'jpegphotorights_people_id' ) ) {
        $values['jpegphotorights_people_id'] = $login_people_id;
      }
      if( ! sql_person( $values['jpegphotorights_people_id'], 0 ) ) {
        $problems['jpegphotorights_people_id'] = 'no such person';
      }
    }
  } else {
    unset( $values['jpegphoto'] );
  }
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
  $group = $values;
  if( $groups_id && ! $problems ) {
    $group = array_merge( sql_one_group( $groups_id ), $values );
    $status = $group['status'];
    if( $status == GROUPS_STATUS_PROFESSOR ) {
      $values['professor_groups_id'] = $groups_id;
    }
  }
  if( ( $id = adefault( $values, 'professor_groups_id' ) ) ) {
    if( sql_one_group( array( 'groups_id' => "$id" ), NULL ) === NULL ) {
      logger( "professorship [$groups_id] not found", LOG_LEVEL_ERROR, LOG_FLAG_INPUT );
      $problems['professor_groups_id'] = 'selected professor not found';
    }
  }

  switch( $action ) {
    case 'hard':
      if( $problems ) {
        error( "sql_save_group() [$groups_id]: ".reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_INPUT, 'groups' );
      }
    case 'soft':
      if( ! $problems ) {
        continue;
      }
    case 'dryrun':
      return $problems;
    default:
      error( "sql_save_group() [$groups_id]: unsupported action requested: [$action]", LOG_FLAG_CODE, 'groups' );
  }

  if( $groups_id ) {
    sql_update( 'groups', $groups_id, $values );
    logger( "updated group [$groups_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'group', array( 'group_view' => "groups_id=$groups_id" ) );
  } else {
    $groups_id = sql_insert( 'groups', $values );
    if( $values['status'] == GROUPS_STATUS_PROFESSOR ) {
      sql_update( 'groups', $groups_id, "professor_groups_id=$groups_id" );
    }
    logger( "new group [$groups_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'group', array( 'group_view' => "groups_id=$groups_id" ) );
  }
  return $groups_id;
}

function sql_delete_groups( $filters, $opts = array() ) {
  return sql_delete_generic( 'groups', $filters, $opts );
}


////////////////////////////////////
//
// offices functions:
//
////////////////////////////////////

function sql_offices( $filters = array(), $opts = array() ) {
  global $language_suffix;

  $joins = array(
    'people' => 'LEFT people ON people.people_id = offices.people_id'
  , 'primary_affiliation' => 'LEFT affiliations ON ( ( primary_affiliation.people_id = people.people_id ) AND ( primary_affiliation.priority = 0 ) )'
  , 'primary_group' => 'LEFT groups ON ( primary_group.groups_id = primary_affiliation.groups_id )'
  );
  $selects = sql_default_selects( array(
    'offices'
  , 'people' => array( 'aprefix' => '' )
  , 'primary_group' => array( 'table' => 'groups', ".cn_$language_suffix" => 'groups_cn', ".url_$language_suffix" => 'groups_url', 'aprefix' => '' )
  , 'primary_affiliation' => array( 'table' => 'affiliations', 'aprefix' => '' )
  ) );
  $opts = default_query_options( 'offices', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'offices.board,offices.function,offices.rank'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'offices,people', $filters, $opts['joins'], $opts['selects'] );

  $s = sql_query( 'offices', $opts );
  return $s;
}

function sql_one_office( $filters = array(), $default = false ) {
  return sql_offices( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_delete_offices( $filters, $opts= array() ) {
  return sql_delete_generic( 'offices', $filters, $opts );
}

function sql_save_office( $board, $function, $rank, $values, $opts = array() ) {
  global $boards;

  $problems = priv_problems( 'offices', 'write', $board );
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'hard' );

  if( ! isset( $boards[ $board ][ $function ] ) ) {
    $problems += new_problem('no such function');
  }
  $rank = (int)$rank;
  if( $rank < 1 ) {
    $problems += new_problem('illegal rank');
  }
  if( $boards[ $board ][ $function ]['count'] != '*' ) {
    if( $rank > $boards[ $board ][ $function ]['count'] ) {
      $problems += new_problem('illegal rank');
    }
  }
  $problems += validate_row( 'offices', $values, "update=yes,action=$action" );
  switch( $action ) {
    case 'hard':
      if( $problems ) {
        error( "sql_save_office() [$board, $function, $rank]: ".reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_INPUT, 'offices' );
      }
    case 'soft':
      if( ! $problems ) {
        continue;
      }
    case 'dryrun':
      return $problems;
    default:
      error( "sql_save_office() [$board, $function, $rank]: unsupported action requested: [$action]", LOG_FLAG_CODE, 'offices' );
  }

  $values['board'] = $board;
  $values['function'] = $function;
  $values['rank'] = $rank;

  $row = sql_one_office( array( 'board' => $board, 'function' => $function, 'rank' => $rank ), 0 );
  if( $row ) {
    $offices_id = $row['offices_id'];
    $values['changelog_id'] = copy_to_changelog( 'offices', $offices_id );
    sql_update( 'offices', $offices_id, $values );
    logger( "save office: update: [$offices_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'office' );
  } else {
    $offices_id = sql_insert( 'offices', $values /* cant work with changelog: , 'update_cols' */ );
    logger( "save office: new: [$offices_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'office' );
  }
  return $offices_id;
}

////////////////////////////////////
//
// positions functions:
//
////////////////////////////////////

function sql_positions( $filters = array(), $opts = array() ) {
  global $language_suffix;

  $joins = array(
    'LEFT groups USING ( groups_id )'
  , 'LEFT people ON people.people_id = contact_people_id'
  );
  $selects = sql_default_selects( array(
    'positions'
  , 'groups' => array( we('.cn_en','.cn_de') => 'groups_cn', we('.url_en','.url_de') => 'groups_url', 'aprefix' => '' )
  , 'people' => array( 'aprefix' => '' )
  ) );
  $opts = default_query_options( 'positions', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => "groups.cn_$language_suffix,positions.cn"
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'positions,groups', $filters, $opts['joins'], $opts['selects'], array(
      'REGEX' => array( '~=', "CONCAT( ';', positions.cn, ';', groups.cn, ';', IFNULL( people.cn, '' ) , ';' )" )
  ) );
  foreach( $opts['filters'][ 1 ] as $index => & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' ) {
      continue;
    }
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      case 'programme_id':
        need( $rel == '=' );
        $key = "( positions.programme & $val )";
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

function sql_delete_positions( $filters, $opts = array() ) {
  return sql_delete_generic( 'positions', $filters, $opts );
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
  $opts['update'] = $positions_id;
  $action = adefault( $opts, 'action', 'hard' );
  $problems = validate_row('positions', $values, $opts );

  switch( $action ) {
    case 'hard':
      if( $problems ) {
        error( "sql_save_position() [$positions_id]: ".reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_INPUT, 'positions' );
      }
    case 'soft':
      if( ! $problems ) {
        continue;
      }
    case 'dryrun':
      return $problems;
    default:
      error( "sql_save_position() [$positions_id]: unsupported action requested: [$action]", LOG_FLAG_CODE, 'positions' );
  }
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
// rooms functions:
//
////////////////////////////////////

function sql_rooms( $filters = array(), $opts = array() ) {
  global $language_suffix;

  $joins = array(
    'owning_group' => 'LEFT groups ON ( owning_group.groups_id = rooms.groups_id )' // GROUP is reserved word
  , 'contact' => 'LEFT people ON contact.people_id = rooms.contact_people_id'
  , 'contact2' => 'LEFT people ON contact2.people_id = rooms.contact2_people_id'
  );
  $selects = sql_default_selects( array(
    'rooms'
  , 'owning_group' => 'table=groups,prefix=owning_group_'
  , 'contact' => 'table=people,prefix=contact_,.people_id='
  , 'contact2' => 'table=people,prefix=contact2_,.people_id='
  ) );
  $selects['contact_cn'] = "TRIM( CONCAT( contact.title, ' ', contact.gn, ' ', contact.sn ) )";
  $selects['contact2_cn'] = "TRIM( CONCAT( contact2.title, ' ', contact2.gn, ' ', contact2.sn ) )";
  $selects['owning_group_cn'] = "owning_group.cn_$language_suffix";
  $selects['owning_group_url'] = "owning_group.url_$language_suffix";

  $opts = default_query_options( 'rooms', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'rooms.roomnumber'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'rooms,groups', $filters, $opts['joins'], $opts['selects'], array(
      'REGEX' => array( '~=', "CONCAT( ';', rooms.roomnumber, ';', groups.cn, ';', IFNULL( contact.cn, '' ) , ';', IFNULL( contact2.cn, '' ) )" )
  ) );

  return sql_query( 'rooms', $opts );
}

function sql_one_room( $filters = array(), $default = false ) {
  return sql_rooms( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_delete_rooms( $filters, $opts = array() ) {
  return sql_delete_generic( 'rooms', $filters, $opts );
}

function sql_save_room( $rooms_id, $values, $opts = array() ) {
  if( $rooms_id ) {
    logger( "start: update room [$rooms_id]", LOG_LEVEL_DEBUG, LOG_FLAG_UPDATE, 'room', array( 'room_edit' => "rooms_id=$rooms_id" ) );
    need_priv( 'rooms', 'edit', $rooms_id );
  } else {
    logger( "start: insert room", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'room' );
    need_priv( 'rooms', 'create' );
  }
  $opts = parameters_explode( $opts );
  $opts['update'] = $rooms_id;
  $action = adefault( $opts, 'action', 'hard' );
  $problems = validate_row( 'rooms', $values, $opts );
  switch( $action ) {
    case 'hard':
      if( $problems ) {
        error( "sql_save_room() [$rooms_id]: ".reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_INPUT, 'rooms' );
      }
    case 'soft':
      if( ! $problems ) {
        continue;
      }
    case 'dryrun':
      return $problems;
    default:
      error( "sql_save_room() [$rooms_id]: unsupported action requested: [$action]", LOG_FLAG_CODE, 'rooms' );
  }

  if( $rooms_id ) {
    sql_update( 'rooms', $rooms_id, $values );
    logger( "updated position [$rooms_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'room', array( 'room_edit' => "rooms_id=$rooms_id" ) );
  } else {
    $rooms_id = sql_insert( 'rooms', $values );
    logger( "new room [$rooms_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'room', array( 'room_edit' => "rooms_id=$rooms_id" ) );
  }
  return $rooms_id;
}

////////////////////////////////////
//
// publications functions:
//
////////////////////////////////////

function sql_publications( $filters = array(), $opts = array() ) {

  $joins = array(
    'LEFT groups USING ( groups_id )'
  );
  $selects = sql_default_selects( array(
    'publications'
  , 'groups' => array( '.cn' => 'groups_cn', '.url' => 'groups_url', 'aprefix' => '' )
  ) );
  if( $GLOBALS['language'] == 'D' ) {
    $selects['cn'] = 'publications.cn_de';
    $selects['summary'] = 'publications.summary_de';
  } else {
    $selects['cn'] = 'publications.cn_en';
    $selects['summary'] = 'publications.summary_en';
  }

  $opts = default_query_options( 'publications', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'year,groups.cn,publications.title'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'publications,groups', $filters, $opts['joins'], $opts['selects'], array(
      'REGEX' => array( '~=', "CONCAT( publications.title
                                , ';', publications.cn
                                , ';', groups.cn
                                , ';', publications.year
                                , ';', publications.journal
                                , ';', publications.authors )"
                      )
  ) );

  $s = sql_query( 'publications', $opts );
  return $s;
}

function sql_one_publication( $filters = array(), $default = false ) {
  return sql_publications( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_save_publication( $publications_id, $values, $opts = array() ) {
  global $login_people_id;

  if( $publications_id ) {
    logger( "start: update publication [$publications_id]", LOG_LEVEL_DEBUG, LOG_FLAG_UPDATE, 'publication', array( 'publication_view' => "publications_id=$publications_id" ) );
    need_priv( 'publications', 'edit', $publications_id );
  } else {
    logger( "start: insert publication", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'publication' );
    need_priv( 'publications', 'create' );
  }
  $opts = parameters_explode( $opts );
  $opts['update'] = $publications_id;
  $action = adefault( $opts, 'action', 'hard' );
  $problems = validate_row('publications', $values, $opts );

  if( $publications_id ) {
    if( adefault( $values, 'jpegphoto' ) ) {
      if( ! adefault( $values, 'jpegphotorights_people_id' ) ) {
        $values['jpegphotorights_people_id'] = $login_people_id;
      }
      if( ! sql_person( $values['jpegphotorights_people_id'], 0 ) ) {
        $problems['jpegphotorights_people_id'] = 'no such person';
      }
    }
  } else {
    unset( $values['jpegphoto'] );
  }
  switch( $action ) {
    case 'hard':
      if( $problems ) {
        error( "sql_save_publication() [$publications_id]: ".reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_INPUT, 'publications' );
      }
    case 'soft':
      if( ! $problems ) {
        continue;
      }
    case 'dryrun':
      return $problems;
    default:
      error( "sql_save_publication() [$publications_id]: unsupported action requested: [$action]", LOG_FLAG_CODE, 'publications' );
  }
  if( $publications_id ) {
    sql_update( 'publications', $publications_id, $values );
    logger( "updated publication [$publications_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'publication', array( 'publication_view' => "publications_id=$publications_id" ) );
  } else {
    $publications_id = sql_insert( 'publications', $values );
    logger( "new publication [$publications_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'publication', array( 'publication_view' => "publications_id=$publications_id" ) );
  }
  return $publications_id;
}

function sql_delete_publications( $filters, $opts = array() ) {
  return sql_delete_generic( 'publications', $filters, $opts );
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
  $selects = sql_default_selects( array( 'teaching', 'teacher' => 'people,aprefix=', 'teacher_group' => 'groups,aprefix=' ) );
  // $selects['yearterm'] = "CONCAT( IF( teaching.term = 'W', 'WiSe', 'SoSe' ), ' ', teaching.year, IF( teaching.term = 'W', teaching.year - 1999, '' ) )";
  $selects['teacher_group_acronym'] = "teacher_group.acronym";
  $selects['signer_group_acronym'] = "signer_group.acronym";
  $selects['creator_cn'] = " TRIM( CONCAT( creator.title, ' ', creator.gn, ' ', creator.sn ) )";
  $selects['teacher_cn'] = " IF( teaching.extern, teaching.extteacher_cn, TRIM( CONCAT( teacher.title, ' ', teacher.gn, ' ', teacher.sn ) ) )";
  $selects['teacher_sn'] = " IF( teaching.extern, teaching.extteacher_cn, teacher.sn )";
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

function sql_delete_teaching( $filters, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $opts['log'] = 1;
  return sql_delete_generic( 'teaching', $filters, $opts );
}

function sql_save_teaching( $teaching_id, $values, $opts = array() ) {
  global $login_people_id, $login_groups_ids;

  if( ! $teaching_id ) {
    if( ! isset( $values['year'] ) ) {
      $values['year'] = $GLOBALS['teaching_survey_year'];
    }
    if( ! isset( $values['term'] ) ) {
      $values['term'] = $GLOBALS['teaching_survey_term'];
    }
  }
  if( $teaching_id ) {
    logger( "start: update teaching [$teaching_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'teaching', array( 'teachinglist' => "teaching_id=$teaching_id,options=".OPTION_TEACHING_EDIT ) );
    need_priv( 'teaching', 'edit', $teaching_id );
    $old = sql_one_teaching( $teaching_id );
  } else {
    logger( "start: insert teaching", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'teaching' );
    need_priv( 'teaching', 'create', $values );
  }

  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'hard' );
  $problems = array();
  $opts['update'] = $teaching_id;

  if( ! isset( $values['extern'] ) ) {
    $problems += new_problem("missing flag 'extern'");
  } else if( $values['extern'] ) {
    $values['teacher_groups_id'] = $values['teacher_people_id'] = 0;
    $values['teaching_obligation'] = $values['teaching_reduction'] = 0;
    $values['teaching_reduction_reason'] = '';
    $values['typeofposition'] = 'o';
    if( ! $values['extteacher_cn']['value'] ) {
      $problems += new_problem('no external teacher specified');
    }
  } else {
    $values['extteacher_cn'] = '';
    $p_id = adefault( $values, 'teacher_people_id', 0 );
    $g_id = adefault( $values, 'teacher_groups_id', 0 );
    if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
      // only coordinator may save person who is not (probably: no longer) group member:
      $aff = sql_affiliations( "people_id=$p_id,groups_id=$g_id,flag_deleted=0", 'single_row=1,default=0' );
      if( ! $aff ) {
        $problems += new_problem('no valid teacher selected');
      } else {
        $values['teaching_obligation'] = $aff['teaching_obligation'];
        $values['teaching_reduction'] = $aff['teaching_reduction'];
        $values['teaching_reduction_reason'] = $aff['teaching_reduction_reason'];
        $values['typeofposition'] = $aff['typeofposition'];
      }
    }
    if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
      $p_id = adefault( $values, 'signer_people_id', 0 );
      $g_id = adefault( $values, 'signer_groups_id', 0 );
      // only coordinator may save person who is not (probably: no longer) group member:
      if( ! sql_affiliations( "people_id=$p_id,groups_id=$g_id,flag_deleted=0", 'single_row=1,default=0' ) ) {
        $problems += new_problem('no valid signer selected');
      }
    }
  }

  if( ! isset( $values['lesson_type'] ) ) {
    $problems += new_problem("missing field 'lesson_type'");
  } else switch( $values['lesson_type'] ) {
    case 'X':
    case 'N':
      $values['hours_per_week'] = '0.0';
      $values['credit_factor'] = '1.000'; // must be string or decimals will be dropped!
      break;
    case 'GP':
      $values['course_title'] = 'GP';
      $values['credit_factor'] = '0.500'; // ...but FP has funny sws values instead!
      $values['teaching_factor'] = 1;
      $values['teachers_number'] = 1;
      break;
    case 'P':
      $values['credit_factor'] = '0.500'; // must be string or decimals will be dropped!
      $values['teaching_factor'] = 1;
      $values['teachers_number'] = 1;
      break;
    case 'FP':
      $values['course_title'] = $values['lesson_type'];
      $values['credit_factor'] = '1.000'; // ...but FP has funny sws values instead!
      $values['teaching_factor'] = 1;
      $values['teachers_number'] = 1;
      break;
    default:
      $values['credit_factor'] = '1.000'; // must be string or decimals will be dropped!
      break;
  }

  if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    if( ! in_array( $values['signer_groups_id'], $login_groups_ids ) ) {
      $problems += new_problem('insufficient privileges');
    }
  }
  if( ! $problems ) {
    $problems += validate_row( 'teaching', $values, $opts );
  }

  switch( $action ) {
    case 'hard':
      if( $problems ) {
        error( "sql_save_teaching() [$teaching_id]: ".reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_INPUT, 'teaching' );
      }
    case 'soft':
      if( ! $problems ) {
        continue;
      }
    case 'dryrun':
      return $problems;
    default:
      error( "sql_save_teaching() [$teaching_id]: unsupported action requested: [$action]", LOG_FLAG_CODE, 'teaching' );
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



////////////////////////////////////
//
// functions for garbage collection:
//
////////////////////////////////////


function garbage_collection( $opts = array() ) {
  logger( 'start: garbage collection', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );

  sql_garbage_collection_generic();
  sql_prune_people();
  sql_prune_affiliations();
  logger( 'finished: garbage collection', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );

}


//
// below this line: untested / unfinished / unused code:
//

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
    if( adefault( $atom, -1 ) !== 'raw_atom' ) {
      continue;
    }
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
  return sql_delete_generic( 'exams', $filters, $opts );
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




?>
