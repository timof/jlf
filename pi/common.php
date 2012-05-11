<?php

define( 'DEGREE_BACHELOR', 0x1 );
define( 'DEGREE_MASTER', 0x2 );
define( 'DEGREE_PHD', 0x4 );
define( 'DEGREE_INTERNSHIP', 0x8 );
define( 'DEGREE_ASSISTANT', 0x10 );
$degree_text = array(
  DEGREE_BACHELOR => 'Bachelor'
, DEGREE_MASTER => 'Master'
, DEGREE_PHD => 'PhD'
, DEGREE_INTERNSHIP => we('research internship','Forschungspraktikum')
, DEGREE_ASSISTANT => we('student assistant','HiWi')
);

define( 'PROGRAMME_BSC',  0x100 );
define( 'PROGRAMME_BED',  0x200 );
define( 'PROGRAMME_MSC',  0x400 );
define( 'PROGRAMME_MED' , 0x800 );
define( 'PROGRAMME_SECOND',  0x1000 );
define( 'PROGRAMME_OTHER',  0x2000 );
$programme_text = array(
  PROGRAMME_BSC => 'BSc'
, PROGRAMME_BED => 'BEd'
, PROGRAMME_MSC => 'MSc'
, PROGRAMME_MED => 'MEd'
, PROGRAMME_SECOND => we('second subject', 'Nebenfach')
, PROGRAMME_OTHER => we('other','sonstige')
);


define( 'PERSON_PRIV_USER', 0x01 );
define( 'PERSON_PRIV_COORDINATOR', 0x02 );
define( 'PERSON_PRIV_ADMIN', 0x04 );

function has_privs( $privs, $people_id = 0 ) {
  if( ! $people_id )
    $people_id = $GLOBALS['login_people_id'];
  if( $people_id ) {
    $person = sql_person( $people_id );
    return ( ( $person['privs'] & $privs ) == $privs );
  } else {
    return ( ( $GLOBALS['login_privs'] & $privs ) == $privs );
  }
}

function restrict_view_filters( $filters, $section ) {
  global $login_privs, $login_people_id, $logged_in;

  if( has_privs( PERSON_PRIV_ADMIN ) )
    return $filters;

  switch( $section ) {
    case 'people':
    case 'person':
    case 'groups':
    case 'affiliations':
    case 'positions':
    case 'exams':
      return $filters;
    case 'teaching':
      if( has_privs( PERSON_PRIV_COORDINATOR ) )
        return $filters;
      if( ! $login_people_id )
        return '0'; // will never match primary key
      $restrict = array( 'submitter_people_id' => $login_people_id );
      break;
    case 'surveys':
    case 'surveysubmissions':
    case 'surveyfields':
    case 'surveyreplies':
      if( has_privs( PERSON_PRIV_COORDINATOR ) )
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
  }
  return $filters;
}

function have_priv( $section, $action, $item = 0 ) {
  global $login_privs, $login_people_id, $logged_in;

  // debug( "$section,$action,$item", 'have_priv' );

  if( has_privs( PERSON_PRIV_ADMIN ) )
    return true;

  if( ! $logged_in )
    return false;

  switch( "$section,$action" ) {
    case 'person,create':
      return true;
    case 'person,edit':
      if( $item ) {
        $person = ( is_array( $item ) ? $item : sql_person( $item ) );
        if( $login_privs >= $person['privs'] ) {
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
      if( has_privs( PERSON_PRIV_COORDINATOR ) )
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
      if( has_privs( PERSON_PRIV_COORDINATOR ) )
        return true;
      return false;
    case 'groups,edit':
      return true;

    case 'teaching,create':
      return true;
    case 'teaching,list':
    case 'teaching,edit':
    case 'teaching,delete':
      if( has_privs( PERSON_PRIV_COORDINATOR ) )
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
      if( has_privs( PERSON_PRIV_COORDINATOR ) )
        return true;
      if( $item ) {
        $position = ( is_array( $item ) ? $item : sql_one_position( $item ) );
        if( $position['submitter_people_id'] = $login_people_id ) {
          return true;
        }
      }
      return false;
  }
  
  return false;
}

function need_priv( $section, $action, $item = 0 ) {
  if( ! have_priv( $section, $action, $item ) ) {
    error( we('insufficient privileges','keine Berechtigung') );
  }
}

function init_session( $login_sessions_id ) {
  global $login_groups, $login_people_id, $login_person;

  if( $login_sessions_id && $login_people_id ) {
    $login_person = sql_person( $login_people_id );
    $login_groups = sql_groups( "people_id=$login_people_id" );
  } else {
    $login_person = $login_groups = array();
  }
}

function is_memberof( $groups_id, $people_id ) {

}

?>
