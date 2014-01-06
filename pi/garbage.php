<?php // pi/maintenance.php

function sql_prune_people( $opts = array() ) {
  global $info_messages;
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'soft' );
  $rv = sql_delete_people( 'flag_deleted', "action=$action" );
  if( ( $count = $rv['deleted'] ) ) {
    logger( "prune_people: $count zombies deleted physically", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'people' );
    $info_messages[] = "sql_prune_people(): $count zombies deleted physically";
  }
  return $rv;
}

function sql_prune_affiliations( $opts = array() ) {
  global $info_messages;
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'soft' );
  $rv = sql_delete_affiliations( '`people.people_id IS NULL', "action=$action" );
  if( ( $count = $rv['deleted'] ) ) {
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

function maintenance_action_buttons_pi() {
  open_tr( '', inlink( '!', 'class=big button,action=garbageCollectionPi,text=garbage collection (pi)' ) );
}

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
    open_td('number', $rv['deletable'] );
    open_td('', inlink( '', 'action=prunePeople,text=prune people,class=button' ) );

  open_tr('medskip');
    open_td('', inlink( 'anylist', 'text=affiliations,table=affiliations' ) );
    $n_total = sql_query( 'affiliations', 'single_field=COUNT' );
    $n_orphans = sql_query( 'affiliations', 'filters=`people.people_id is NULL,joins=LEFT people,single_field=COUNT' );
    $rv = sql_prune_affiliations( 'action=dryrun' );
    open_td('number', $n_total );
    open_td('number', $rv['deletable'] );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', $rv['deletable'] );
    open_td('', inlink( '', 'action=pruneAffiliations,text=prune affiliations,class=button' ) );
}





?>
