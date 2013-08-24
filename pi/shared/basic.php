<?php // pi/basic.php

// we need to define constants early (so they are available when functions are parsed);
// the textual representation goes to common.php (as we() may need to be called)

define( 'PROGRAMME_BSC', 0x1 );
define( 'PROGRAMME_BED', 0x2 );
define( 'PROGRAMME_MSC', 0x4 );
define( 'PROGRAMME_MED', 0x8 );
define( 'PROGRAMME_PHD', 0x10 );
define( 'PROGRAMME_SECOND',  0x20 );
define( 'PROGRAMME_INTERNSHIP', 0x40 );
define( 'PROGRAMME_ASSISTANT', 0x80 );


define( 'PERSON_PRIV_USER', 0x01 );
define( 'PERSON_PRIV_COORDINATOR', 0x02 );
define( 'PERSON_PRIV_ADMIN', 0x04 );

define( 'OPTION_TEACHING_EDIT', 1 );

define( 'GROUPS_FLAG_INSTITUTE', 0x001 ); // to be considered member of institute
define( 'GROUPS_FLAG_ACTIVE', 0x002 );    // whether it still exists
define( 'GROUPS_FLAG_LIST', 0x004 );      // to be listed on official institute list

function have_minimum_person_priv( $priv, $people_id = 0 ) {
  if( $people_id ) {
    $person = sql_person( $people_id );
    $p = $person['privs'];
  } else {
    $p = $GLOBALS['login_privs'];
  }
  return ( $p >= $priv );
}

function restrict_view_filters( $filters, $section ) {
  global $login_privs, $login_people_id, $logged_in, $login_groups_ids;

  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
    return $filters;

  switch( $section ) {
    case 'people':
    case 'person':
      $restrict = array( '&&', array( '!', 'flag_virtual' ), array( '!', 'flag_deleted' ) );
      break;
    case 'groups':
    case 'rooms':
    case 'affiliations':
    case 'positions':
    case 'exams':
    case 'publications':
      return $filters;
    case 'teaching':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) )
        return $filters;
      if( ! $login_people_id )
        return '0'; // will never match primary key
      $restrict = array( '||'
      , array( 'signer_groups_id' => $login_groups_ids )
      , array( 'creator_people_id' => $login_people_id )
      );
      break;
    case 'surveys':
    case 'surveysubmissions':
    case 'surveyfields':
    case 'surveyreplies':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) )
        return $filters;
      if( ! $login_people_id )
        return '0'; // will never match primary key
      $restrict = array( 'creator_affiliations.groups_id' => $login_groups_ids );
      break;
    case 'references':
    case 'logbook':
    case 'changelog':
    default:
      return '0';
  }

  if( $restrict && $filters ) {
    $filters = array( '&&', $filters, $restrict );
  } else if( $restrict ) {
    $filters = $restrict;
  }
  return $filters;
}

function have_priv( $section, $action, $item = 0 ) {
  global $login_privs, $login_people_id, $logged_in, $login_groups_ids, $boards;
  global $teaching_survey_open, $teaching_survey_year, $teaching_survey_term;

  need( $section );
  need( $action );
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    return true;
  }
  if( $section === '*' ) {
    return false;
  }
  if( $action === '*' ) {
    return false;
  }
  if( ! $logged_in ) {
    return false;
  }
  if( $action === 'create' ) {
    $item = parameters_explode( $item );
  }

  switch( "$section,$action" ) {

    case 'offices,read':
    case 'config,read':
      return true;

    case 'config,write':
    case 'offices,write':
    case 'offices,delete':
      $item = adefault( $item, 'board', $item );
      switch( "$item" ) {
        case '_LEHRERFASSUNG':
          if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
            return true;
          }
          return false;
        default:
          if( ! isset( $boards[ $item ] ) ) {
            return false;
          }
          if( have_minimum_person_priv( adefault( $boards[ $item ], '_MINPRIV', PERSON_PRIV_ADMIN ) ) ) {
            return true;
          }
          return false;
      }

      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        return true;
      }
      return false;

    case 'person,create':
      return true;
    case 'person,edit':
      if( $item ) {
        $person = ( is_array( $item ) ? $item : sql_person( $item ) );
        if( $person['flag_deleted'] || $person['flag_virtual'] ) {
          return false;
        }
        if( $person['privs'] < PERSON_PRIV_ADMIN ) {
          return true;
        }
      }
      return false;
    case 'person,delete':
      if( $item ) {
        $person = ( is_array( $item ) ? $item : sql_person( $item ) );
        if( $person['flag_deleted'] ) { // already a zombie
          return true;
        }
        if( $person['people_id'] === $login_people_id ) {
          return false;
        }
        if( $person['flag_virtual'] ) {
          return false;
        }
        if( $login_privs > $person['privs'] ) {
          return true;
        }
      }
      return false;
    case 'person,teaching_obligation':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        return true;
      }
      return false;
    case 'person,position':
      if( have_minimum_person_priv( PERSON_PRIV_USER ) )
        return true;
      return false;
    case 'person,positionBudget':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) )
        return true;
      return false;
    case 'person,account':
      return false;
    case 'person,password':
      if( $item ) {
        $person = ( is_array( $item ) ? $item : sql_person( $item ) );
        if( $person['people_id'] === $login_people_id ) {
          return true;
        }
        if( $person['privs'] <= PERSON_PRIV_USER ) {
          return have_minimum_person_priv( PERSON_PRIV_COORDINATOR );
        }
      }
      return false;
    case 'person,affiliations':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        return true;
      }
      if( $item ) {
        $person = ( is_array( $item ) ? $item : sql_person( $item ) );
        if( $person['privs'] < PERSON_PRIV_USER ) {
          return true;
        }
      }
      return false;

    case 'groups,create':
    case 'groups,delete':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        return true;
      }
      return false;
    case 'groups,edit':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        return true;
      }
      if( $item ) {
        $group = ( is_array( $item ) ? $item : sql_one_group( $item ) );
        if( in_array( $group['groups_id'], $login_groups_ids ) ) {
          return true;
        }
      }
      return false;

    case 'teaching,create':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        return true;
      }
      if( ! $teaching_survey_open ) {
        return false;
      }
      if( adefault( $item, 'year', $teaching_survey_year ) !== $teaching_survey_year ) {
        return false;
      }
      if( adefault( $item, 'term', $teaching_survey_term ) !== $teaching_survey_term ) {
        return false;
      }
      return true;
    case 'teaching,edit':
    case 'teaching,delete':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        return true;
      }
      if( ! $teaching_survey_open ) {
        return false;
      }
      if( $item ) {
        $teaching = ( is_array( $item ) ? $item : sql_one_teaching( $item ) );
        if( ( $teaching['year'].$teaching['term'] ) !== ( $teaching_survey_year.$teaching_survey_term ) ) {
          return false;
        }
        // $teaching['teacher_groups_id'] ... doesnt matter: entry will count for signer, so check that:
        if( in_array( $teaching['signer_groups_id'], $login_groups_ids ) ) {
          return true;
        }
        if( (int)( $teaching['creator_people_id'] ) === (int)$login_people_id ) {
          return true;
        }
      }
      return false;
    case 'teaching,list':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        return true;
      }
      if( $item ) {
        $teaching = ( is_array( $item ) ? $item : sql_one_teaching( $item ) );
        if( in_array( $teaching['signer_groups_id'], $login_groups_ids ) ) {
          return true;
        }
        if( (int)( $teaching['creator_people_id'] ) === (int)$login_people_id ) {
          return true;
        }
      }
      return false;

    case 'positions,create':
      return true;
    case 'positions,edit':
    case 'positions,delete':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        return true;
      }
      if( $item ) {
        $position = ( is_array( $item ) ? $item : sql_one_position( $item ) );
        if( in_array( $position['groups_id'], $login_groups_ids ) ) {
          return true;
        }
      }
      return false;
    
    case 'rooms,create':
      return true;
    case 'rooms,edit':
    case 'rooms,delete':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        return true;
      }
      if( $item ) {
        $room = ( is_array( $item ) ? $item : sql_one_room( $item ) );
        if( in_array( $room['groups_id'], $login_groups_ids ) ) {
          return true;
        }
      }
      return false;

    case 'publications,create':
      return true;
    case 'publications,edit':
    case 'publications,delete':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        return true;
      }
      if( $item ) {
        $publication = ( is_array( $item ) ? $item : sql_one_publication( $item ) );
        if( in_array( $publication['groups_id'], $login_groups_ids ) ) {
          return true;
        }
      }
      return false;

    case 'references,list':
    case 'logbook,list':
      return false;

    default:
      error( "undefined priv query: [$section,$action]", LOG_FLAG_CODE, 'privs' );
  }
  
  return false;
}

function need_priv( $section, $action, $item = 0 ) {
  if( ! have_priv( $section, $action, $item ) ) {
    error( we('insufficient privileges','keine Berechtigung'), LOG_FLAG_AUTH | LOG_FLAG_USER, 'privs' );
  }
}


function init_session( $login_sessions_id ) {
  global $login_affiliations, $login_people_id, $login_person, $login_groups_ids;

  if( $login_sessions_id && $login_people_id ) {
    $login_person = sql_person( $login_people_id );
    $login_affiliations = sql_affiliations( "people_id=$login_people_id" );
  } else {
    $login_person = $login_affiliations = array();
  }
  $login_groups_ids = array();
  foreach( $login_affiliations as $g ) {
    $login_groups_ids[] = $g['groups_id'];
  }
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    $GLOBALS['show_debug_button'] = true;
  }
}


?>
