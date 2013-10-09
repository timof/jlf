<?php // pp/basic.php

// we need to define constants early (so they are available when functions are parsed);
// the textual representation goes to common.php (as we() may need to be called)

require_once('shared/basic.php');

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

define( 'GROUPS_STATUS_PROFESSOR', 1 );
define( 'GROUPS_STATUS_SPECIAL', 2 );
define( 'GROUPS_STATUS_JOINT', 3 );
define( 'GROUPS_STATUS_EXTERNAL', 4 );
define( 'GROUPS_STATUS_OTHER', 5 );

function have_minimum_person_priv( $priv, $people_id = 0 ) {
  return false;
}

function restrict_view_filters( $filters, $section ) {
  global $login_privs, $login_people_id, $logged_in, $login_groups_ids;

  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    return $filters;
  }

  switch( $section ) {
    case 'people':
    case 'person':
      $restrict = array( '&&', array( '!', 'flag_virtual' ), array( '!', 'flag_deleted' ), 'flag_institute' );
      break;
    case 'groups':
    case 'rooms':
    case 'affiliations':
    case 'positions':
    case 'exams':
    case 'publications':
      return $filters;
    case 'documents':
      $restrict = 'flag_publish';
      break;
    case 'teaching':
    case 'surveys':
    case 'surveysubmissions':
    case 'surveyfields':
    case 'surveyreplies':
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
  return false;
}

function need_priv( $section, $action, $item = 0 ) {
  if( ! have_priv( $section, $action, $item ) ) {
    error( we('insufficient privileges','keine Berechtigung'), LOG_FLAG_AUTH | LOG_FLAG_USER, 'privs' );
  }
}


function init_session( $login_sessions_id ) {
  global $login_affiliations, $login_people_id, $login_person, $login_groups_ids;

  $login_person = $login_affiliations = array();
  $login_groups_ids = array();
}


?>
