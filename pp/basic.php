<?php // pp/basic.php

// we need to define constants early (so they are available when functions are parsed);
// the textual representation goes to common.php (as we() may need to be called)

require_once('shared/basic.php');

function have_minimum_person_priv( $priv, $people_id = 0 ) {
  return false;
}

function restrict_view_filters( $filters, $section ) {
  global $login_privs, $login_people_id, $logged_in, $login_groups_ids;

  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    return $filters;
  }

  $restrict = NULL;
  switch( $section ) {
    case 'people':
    case 'person':
      $restrict = array( '&&', array( '!', 'flag_virtual' ), array( '!', 'flag_deleted' ), 'flag_institute' );
      break;
    case 'groups':
      $restrict = 'flag_publish';
      break;
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

  if( $restrict !== NULL ) {
    $filters = array( '&&', $filters, $restrict );
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
