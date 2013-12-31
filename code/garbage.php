<?php
//
// functions for maintenance; in particular: garbage collection
//

function sql_prune_logbook( $opts = array() ) {
  global $now_unix, $info_messages, $jlf_application_name;

  $opts = parameters_explode( $opts );
  $application = adefault( $opts, 'application', $jlf_application_name );
  $log_keep_seconds = adefault( $opts, 'log_keep_seconds', $GLOBALS['log_keep_seconds'] );
  $thresh = datetime_unix2canonical( $now_unix - $log_keep_seconds );
  $action = adefault( $opts, 'action', 'soft' );
  $prune_errors = adefault( $opts, 'prune_errors' );

  $filters = array( 'utc <' => $thresh );
  if( $application ) {
    $filters['application'] = $application;
  }
  if( $prune_errors ) {
    $filters['level'] = LOG_LEVEL_ERROR;
    $t = 'error';
  } else {
    $filters['level <'] = LOG_LEVEL_ERROR;
    $t = 'logbook (non-error)';
  }
  $rv = sql_delete_logbook( $filters, "action=$action,quick=1" );
  if( ( $count = $rv['deleted'] ) ) {
    $info_messages[] = "sql_prune_logbook(): $count $t entries deleted";
    logger( "sql_prune_logbook(): $count $t entries deleted", LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance' );
  }
  return $rv;
}

function sql_prune_changelog( $opts = array() ) {
  global $now_unix, $info_messages, $tables;

  $opts = parameters_explode( $opts );
  $log_keep_seconds = adefault( $opts, 'log_keep_seconds', $GLOBALS['log_keep_seconds'] );
  $thresh = datetime_unix2canonical( $now_unix - $log_keep_seconds );
  $action = adefault( $opts, 'action', 'soft' );

  // prune by age:
  //
  $rv = sql_delete_changelog( "ctime < $thresh", "action=$action,quick=1" );

//   // delete orphaned entries - maybe not?
//   foreach( $tables as $tname => $props ) {
//     if( $tname === 'changelog' ) {
//       continue;
//     }
//     if( ! isset( $props['cols']['changelog_id'] ) ) {
//       continue;
//     }
//     $rv = sql_delete_changelog( "`$tname.{$tname}_id IS NULL" , array(
//       'joins' => "LEFT $tname USING ( changelog_id )"
//     , 'action' => $action
//     , 'quick' => 1
//     , 'rv' => $rv
//     ) );
//   }
  if( ( $count = $rv['deleted'] ) ) {
    $info_messages[] = "sql_prune_changelog(): $count changelog entries deleted";
    logger( "sql_prune_changelog(): $count changelog entries deleted", LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance' );
  }
  return $rv;
}

function sql_prune_transactions( $opts = array() ) {
  global $jlf_application_name;

  $opts = parameters_explode( $opts );
  $application = adefault( $opts, 'application', $jlf_application_name );
  $action = adefault( $opts, 'action', 'soft' );

  $rv = init_rv_delete_action();
  $filters = array( 'sessions.valid' => 0, 'sessions.application' => $application );
  if( $action === 'dryrun' ) {
    $rv['deletable']  = ( $rv['deletable_invalid'] = sql_query( 'transactions', array( 'filters' => $filters, 'joins' => 'LEFT sessions', 'single_field' => 'COUNT' ) ) );
    $rv['deletable'] += ( $rv['deletable_orphans'] = sql_query( 'transactions', 'filters=`sessions.sessions_id IS NULL,joins=LEFT sessions,single_field=COUNT' ) );
  } else {
    $rv['deleted']  = ( $rv['deleted_invalid'] = sql_delete( 'transactions', $filters, 'joins=LEFT sessions' ) );
    $rv['deleted'] += ( $rv['deleted_orphans'] = sql_delete( 'transactions', '`sessions.sessions_id IS NULL', 'joins=LEFT sessions' ) );
    if( ( $count = $rv['deleted'] ) ) {
      logger(
        "sql_prune_transactions(): entries deleted: invalid:{$rv['deleted_invalid']} orphans:{$rv['deleted_orphans']} total:$count"
      , LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance'
      );
      $info_messages[] = "sql_prune_transactions(): $count transactions deleted";
    }
  }
  return $rv;
}

function sql_prune_persistentvars( $opts = array() ) {
  global $jlf_application_name;

  $opts = parameters_explode( $opts );
  $application = adefault( $opts, 'application', $jlf_application_name );
  $action = adefault( $opts, 'action', 'soft' );

  $rv = init_rv_delete_action();
  $filters = array( 'sessions.valid' => 0, 'sessions.application' => $application );
  if( $action === 'dryrun' ) {
    $rv['deletable']  = ( $rv['deletable_invalid'] = sql_query( 'persistentvars', array( 'filters' => $filters, 'joins' => 'LEFT sessions', 'single_field' => 'COUNT' ) ) );
    $rv['deletable'] += ( $rv['deletable_orphans'] = sql_query( 'persistentvars', 'filters=`sessions.sessions_id IS NULL,joins=LEFT sessions,single_field=COUNT' ) );
  } else {
    $rv['deleted']  = ( $rv['deleted_invalid'] = sql_delete( 'persistentvars', $filters, 'joins=LEFT sessions' ) );
    $rv['deleted'] += ( $rv['deleted_orphans'] = sql_delete( 'persistentvars', '`sessions.sessions_id IS NULL', 'joins=LEFT sessions' ) );
    if( ( $count = $rv['deleted'] ) ) {
      logger(
        "sql_prune_persistentvars(): entries deleted: invalid:{$rv['deleted_invalid']} orphans:{$rv['deleted_orphans']} total:$count"
      , LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance'
      );
      $info_messages[] = "sql_prune_persistentvars(): $count persistentvars deleted";
    }
  }
  return $rv;
}

// sql_expire_sessions():
// will expire sessions unused for longer than $session_lifetime_seconds
// options:
//   session_lifetime_seconds: override the global configuration variable
//   action: if 'dryrun', just count what can be expired
//   application: act on sessions of this application; overrides global $jlf_application_name
//
function sql_expire_sessions( $opts = array() ) {
  global $now_unix, $login_sessions_id, $info_messages, $jlf_application_name;

  $opts = parameters_explode( $opts );
  $application = adefault( $opts, 'application', $jlf_application_name );
  $action = adefault( $opts, 'action', 'soft' );

  $session_lifetime_seconds = adefault( $opts, 'session_lifetime_seconds', $GLOBALS['session_lifetime_seconds'] );
  $thresh = datetime_unix2canonical( $now_unix - $session_lifetime_seconds );
  $rv = init_rv_delete_action();
  $filters = array(
    'valid'
  , 'sessions_id !=' => $login_sessions_id
  , 'atime <' => $thresh
  , 'application' => $application
  );
  if( $action === 'dryrun' ) {
    $rv['invalidatable'] = sql_query( 'sessions', array( 'filters' => $filters, 'single_field' => 'COUNT' ) );
  } else {
    $rv['invalidated']   = sql_update( 'sessions', $filters , 'valid=0' );
    if( $rv['invalidated'] ) {
      logger(
        "sql_expire_sessions(): {$rv['invalidated']} sessions expired"
      , LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance'
      );
    }
  }
  return $rv;
}


// sql_prune_sessions():
// will delete sessions that are expired and unused longer than $log_keep_seconds
// options:
//   log_keep_seconds: override the global configuration variable
//   action: if 'dryrun', just count what can be expired and deleted
//   application: act on sessions of this application; overrides global $jlf_application_name
//
function sql_prune_sessions( $opts = array() ) {
  global $now_unix, $login_sessions_id, $info_messages, $jlf_application_name;

  $opts = parameters_explode( $opts );
  $application = adefault( $opts, 'application', $jlf_application_name );
  $action = adefault( $opts, 'action', 'soft' );

  $log_keep_seconds = adefault( $opts, 'log_keep_seconds', $GLOBALS['log_keep_seconds'] );
  $thresh = datetime_unix2canonical( $now_unix - $log_keep_seconds );

  $rv = sql_delete_sessions( "valid=0,application=$application,atime<$thresh", array( 'action' => $action ) );
  if( ( $count = $rv['deleted'] ) ) {
    logger( "sql_prune_sessions(): $count sessions deleted", LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance' );
    $info_messages[] = "sql_prune_sessions(): $count sessions deleted";
  }

  return $rv;
}


function sql_garbage_collection_generic( $opts = array() ) {
  global $jlf_application_name;

  $opts = parameters_explode( $opts );
  $application = adefault( $opts, 'application', $jlf_application_name );

  logger( "start: garbage collection (generic) for $application", LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );
  sql_expire_sessions( $opts );
  sql_prune_sessions( $opts );
  sql_prune_transactions( $opts );
  sql_prune_persistentvars( $opts );
  sql_prune_changelog( $opts );
  sql_delete( 'debug', true );
  sql_delete( 'profile', true );
  sql_prune_logbook( $opts ); // deliberately do _not_ prune errors from log
  logger( "finished: garbage collection (generic) for $application", LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );
}

?>