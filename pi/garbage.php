<?php // pi/garbage.php

function sql_prune_people( $opts = array() ) {
  global $info_messages, $utc, $now_unix;

  need_priv( 'people', 'delete' );
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'soft' );

  $rv = sql_delete_people( "flag_deleted,gc_nextcheck_utc<$utc", array( 'action' => $action, 'authorized' => 1 ) );
  $count_deleted = $rv['deleted'];
  $count_undeletable = count( $rv['undeletable'] );
  if( ( $action !== 'dryrun' ) && ( $count_deleted || $count_undeletable ) ) {
    logger( "prune_people: $count_deleted zombies deleted physically", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'people' );
    $info_messages[] = "sql_prune_people(): $count_deleted zombies deleted physically, $count_undeletable zombies were considered but not deletable";
    foreach( $rv['undeletable'] as $people_id ) {
      $ctime = sql_query( 'people', "filters=$people_id,single_field=ctime,default=0,authorized=1" );
      if( ! $ctime ) {
        continue;
      }
      $ctime_unix = datetime_canonical2unix( $ctime );
      $delay = min( ( $now_unix - $ctime_unix ), 3000000 );
      $delay = (int) ( ( $delay / 500.0 ) * hexdec( random_hex_string( 1 ) ) );
      $nextcheck_utc = datetime_unix2canonical( $now_unix + $delay );
      sql_update( 'people', $people_id, array( 'gc_nextcheck_utc' => $nextcheck_utc ), AUTH );
    }
  }

  return $rv;
}

function sql_prune_affiliations( $opts = array() ) {
  global $info_messages;

  need_priv( 'affiliations', 'delete' );
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'soft' );

  $rv = sql_delete_affiliations( '`people.people_id IS NULL', "action=$action,authorized=1" );
  if( ( $action !== 'dryrun' ) && ( $count = $rv['deleted'] ) ) {
    logger( "prune_affiliations(): deleted $count orphaned affiliations", LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance' );
    $info_messages[] = "sql_prune_affiliations(): $count orphaned affiliations deleted";
  }
  return $rv;
}

function sql_garbage_collection_pi( $opts = array() ) {
  global $jlf_application_name;

  need( $jlf_application_name === 'pi' );
  $opts = parameters_explode( $opts );
  $opts['application'] = $jlf_application_name;

  logger( 'start: garbage collection (specific) for pi', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );
  sql_prune_people( $opts );
  sql_prune_affiliations( $opts );
  logger( 'finished: garbage collection (specific) for pi', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );
}

$maintenance_actions_pi = array( 'prunePeople', 'pruneAffiliations', 'garbageCollectionPi' );

function handle_maintenance_action_pi( $action, $prune_opts ) {
  switch( $action ) {
    case 'prunePeople':
      sql_prune_people( $prune_opts );
      break;
    case 'pruneAffiliations':
      sql_prune_affiliations( $prune_opts );
      break;
    case 'garbageCollectionPi':
      sql_garbage_collection_pi( $prune_opts );
      break;
  }
}

// function maintenance_action_buttons_pi() {
//   open_tr( '', inlink( '!', 'class=big button,action=garbageCollectionPi,text=garbage collection (pi)' ) );
// }

function maintenance_table_rows_pi() {
  open_tr('medskip');
    open_td('', inlink( 'anylist', 'text=people,table=people' ) );
    $n_total = sql_query( 'people', 'single_field=COUNT' );
    $n_invalid = sql_query( 'people', 'filters=flag_deleted,single_field=COUNT' );
    $rv = sql_prune_people( 'action=dryrun' );
    open_td('number', $n_total );
    open_td('number', $n_invalid );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', count( $rv['considered'] ) );
    open_td('number', count( $rv['undeletable'] ) );
    open_td('number', $rv['deleted'] );
    open_td('', inlink( '', 'action=prunePeople,text=prune people,class=button' ) );

  open_tr('medskip');
    open_td('', inlink( 'anylist', 'text=affiliations,table=affiliations' ) );
    $n_total = sql_query( 'affiliations', 'single_field=COUNT' );
    $n_orphans = sql_query( 'affiliations', 'filters=`people.people_id is NULL,joins=LEFT people,single_field=COUNT' );
    $rv = sql_prune_affiliations( 'action=dryrun' );
    open_td('number', $n_total );
    open_td('number', $rv['deleted'] );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', count( $rv['considered'] ) );
    open_td('number', count( $rv['undeletable'] ) );
    open_td('number', $rv['deleted'] );
    open_td('', inlink( '', 'action=pruneAffiliations,text=prune affiliations,class=button' ) );

  open_tr();
    open_td('colspan=9,right', inlink( '!', 'class=big button,action=garbageCollectionPi,text=garbage collection: special/pi' ) );
}





?>
