<?php // pi/maintenance.php

function sql_prune_people() {
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'soft' );
  $rv = sql_delete_people( 'flag_deleted', "action=$action" );
  if( ( $count = $rv['deleted'] ) ) {
    logger( "prune_people: $count zombies deleted physically", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'people' );
  }
  return $rv;
}

function sql_prune_affiliations( $opts = array() ) {
  // garbage collection only - no privilege check required
  // 
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'soft' );
  $rv = sql_delete_affiliations( '`people.people_id IS NULL', "action=$action" );
  if( ( $count = $rv['deleted'] ) ) {
    logger( "prune_affiliations(): deleted $count orphaned affiliations", LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance' );
  }
  return $rv;
}

function garbage_collection_pi( $opts = array() ) {
  global $jlf_application_name;

  need( $jlf_application_name === 'pi' );
  logger( 'start: garbage collection (pi)', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );

  $session_lifetime_pp = sql_query( 'leitvariable', 'name=session_lifetime_seconds,application=pp', 'single_value=value' );
  $session_lifetime_pp = sql_query( 'leitvariable', 'name=session_lifetime_seconds,application=pp', 'single_value=value' );

  sql_prune_sessions( "application=pp,session_lifetime_seconds=$session_lifetime_pp" );

  sql_garbage_collection_generic();
  sql_prune_people();
  sql_prune_affiliations();
  logger( 'finished: garbage collection', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );

}

$maintenance_actions_pi = array( 'prunePeople', 'pruneAffiliations' );

function handle_maintenance_action_pi( $action ) {
  switch( $action ) {
    case 'prunePeople':
      prune_people();
      break;
    case 'pruneAffiliations':
      prune_affiliations();
      break;
  }
}

function maintenance_table_rows_pi() {
  open_tr('medskip');
    open_td('', inlink( 'anylist', 'text=people,table=people' ) );
    $n_total = sql_query( 'people', 'single_field=COUNT' );
    $rv = sql_prune_people( 'action=dryrun' );
    open_td('number', $n_total );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', $rv['deleted'] );
    open_td('', inlink( '', 'action=prunePeople,text=prune people,class=button' ) );

  open_tr('medskip');
    open_td('', inlink( 'anylist', 'text=affiliations,table=affiliations' ) );
    $n_total = sql_query( 'affiliations', 'single_field=COUNT' );
    $rv = sql_prune_affiliations( 'action=dryrun' );
    open_td('number', $n_total );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', $rv['deleted'] );
    open_td('', inlink( '', 'action=pruneAffiliations,text=prune affiliations,class=button' ) );
}





?>
