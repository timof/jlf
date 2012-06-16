<?php



define( 'PERSON_PRIV_USER', 0x01 );
define( 'PERSON_PRIV_COORDINATOR', 0x02 );
define( 'PERSON_PRIV_ADMIN', 0x04 );

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
  global $login_privs, $login_people_id, $logged_in;

  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
    return $filters;

  switch( $section ) {
    case 'people':
    case 'person':
      $restrict = array( '&&', 'INSTITUTE', array( '!', 'NOPERSON' ) );
      break;
    case 'groups':
    case 'affiliations':
    case 'positions':
    case 'exams':
      return $filters;
    case 'teaching':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) )
        return $filters;
      if( ! $login_people_id )
        return '0'; // will never match primary key
      $restrict = array( 'submitter_people_id' => $login_people_id );
      break;
    case 'surveys':
    case 'surveysubmissions':
    case 'surveyfields':
    case 'surveyreplies':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) )
        return $filters;
      if( ! $login_people_id )
        return '0'; // will never match primary key
      $restrict = array( 'submitter_people_id' => $login_people_id );
      break;
    case 'logbook':
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
  global $login_privs, $login_people_id, $logged_in;

  // debug( "$section,$action,$item", 'have_priv' );

  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
    return true;

  if( ! $logged_in )
    return false;

  switch( "$section,$action" ) {
    case 'person,create':
      return true;
    case 'person,edit':
      if( $item ) {
        $person = ( is_array( $item ) ? $item : sql_person( $item ) );
        if( $person['privs'] < PERSON_PRIV_ADMIN ) {
          return true;
        }
      }
      return false;
    case 'person,delete':
      if( $item ) {
        $person = ( is_array( $item ) ? $item : sql_person( $item ) );
        if( $person['people_id'] === $login_people_id )
          return false;
        if( $login_privs >= $person['privs'] ) {
          return true;
        }
      }
      return false;
    case 'person,account':
      return false;
    case 'person,password':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) )
        return true;
      if( $item ) {
        $person = ( is_array( $item ) ? $item : sql_person( $item ) );
        if( $person['people_id'] === $login_people_id ) {
          return true;
        }
      }
      return false;

    case 'groups,create':
    case 'groups,delete':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) )
        return true;
      return false;
    case 'groups,edit':
      return true;

    case 'teaching,create':
      return true;
    case 'teaching,list':
    case 'teaching,edit':
    case 'teaching,delete':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) )
        return true;
      if( $item ) {
        $teaching = ( is_array( $item ) ? $item : sql_one_teaching( $item ) );
        if( $teaching['submitter_people_id'] = $login_people_id ) {
          return true;
        }
      }
      return false;

    case 'positions,create':
      return true;
    case 'positions,edit':
    case 'positions,delete':
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) )
        return true;
      if( $item ) {
        $position = ( is_array( $item ) ? $item : sql_one_position( $item ) );
        if( $position['submitter_people_id'] = $login_people_id ) {
          return true;
        }
      }
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
}


?>
