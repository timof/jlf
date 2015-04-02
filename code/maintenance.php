<?php // /code/maintenance.php

echo html_tag( 'h1', '', 'maintenance' );

sql_transaction_boundary('*');

need_priv('*','*');

$applications = uid_choices_applications();
$applications[ value2uid( $jlf_application_name ) ] = $jlf_application_name;

$app_field = init_var( 'application', array( 'type' => 'W64', 'initval' => $jlf_application_name, 'set_scopes' => 'self', 'global' => 1, 'pattern' => $applications ) );
need( $application );

require_once('code/garbage.php');

if( is_readable( "$application/garbage.php" ) ) {
  require_once( "$application/garbage.php" );
}

$app_log_keep_seconds = sql_query( 'leitvariable', "filters=name=log_keep_seconds-$application,single_field=value" );
$app_session_lifetime_seconds = sql_query( 'leitvariable', "filters=name=session_lifetime_seconds-$application,single_field=value" );

$app_option_fields = array(
  'log_keep_seconds' => "type=u,size=8,global=1,sources=http persistent,set_scopes=self,auto=1,default=$app_log_keep_seconds"
, 'session_lifetime_seconds' => "type=u,size=8,global=1,sources=http persistent,set_scopes=self,auto=1,default=$app_session_lifetime_seconds"
);
$app_option_fields = init_fields( $app_option_fields );

$prune_opts = array( 
  'application' => $application
, 'session_lifetime_seconds' => $app_option_fields['session_lifetime_seconds']['value']
, 'log_keep_seconds' => $app_option_fields['log_keep_seconds']['value']
, 'robots_keep_seconds' => $app_option_fields['log_keep_seconds']['value']
);

$actions = array(
  'pruneTransactions'
, 'prunePersistentvars'
, 'expireSessions'
, 'pruneSessions'
, 'pruneChangelog'
, 'pruneLogDebug'
, 'pruneLogMessages'
, 'pruneLogErrors'
, 'pruneDebug'
, 'pruneProfile'
, 'pruneRobots'
, 'garbageCollectionGenericCommon'
, 'garbageCollectionGenericApp'
// , 'resetDanglingLinks'
);
$actions = array_merge( $actions, adefault( $GLOBALS, "maintenance_actions_$application", array() ) );
handle_actions( $actions );
if( $action ) switch( $action ) {
//
  case 'pruneTransactions':
    sql_prune_transactions( $prune_opts );
    break;
  case 'prunePersistentvars':
    sql_prune_persistentvars( $prune_opts );
    break;
  case 'expireSessions':
    sql_expire_sessions( $prune_opts );
    break;
  case 'pruneSessions':
    sql_prune_sessions( $prune_opts );
    break;
  case 'pruneChangelog':
    sql_prune_changelog( $prune_opts );
    break;
  case 'pruneLogDebug':
    sql_prune_logbook( $prune_opts + array( 'prune_level' => LOG_LEVEL_DEBUG ) );
    break;
  case 'pruneLogMessages':
    sql_prune_logbook( $prune_opts + array( 'prune_level' => LOG_LEVEL_WARNING ) );
    break;
  case 'pruneLogErrors':
    sql_prune_logbook( $prune_opts + array( 'prune_level' => LOG_LEVEL_ERROR ) );
    break;
  case 'pruneDebug':
    sql_delete( 'debug', true );
    break;
  case 'pruneProfile':
    sql_delete( 'profile', true );
    break;
  case 'pruneRobots':
    sql_prune_robots( $prune_opts );
    break;
  case 'garbageCollectionGenericCommon':
    sql_garbage_collection_generic_common( $prune_opts );
    break;
  case 'garbageCollectionGenericApp':
    sql_garbage_collection_generic_app( $application, $prune_opts );
    break;
//   case 'resetDanglingLinks':
//     init_var( 'reset_table', 'type=W64,global=1,sources=http' );
//     init_var( 'reset_col', 'type=W64,global,sources=http' );
//     init_var( 'reset_id', 'type=u,global,sources=http' );
//     sql_reset_dangling_links( $reset_table, $reset_col, $reset_id );
//     break;
  default:
    $handler = "handle_maintenance_action_$application";
    if( function_exists( $handler ) ) {
      $handler( $action, $prune_opts );
    }
    break;
}

// flush_all_messages();

open_div('menubox bigskipb');
  open_table('css filters');
    open_caption( '', filter_reset_button( $app_option_fields ) . 'Options' );
    open_tr();
      open_th( '', 'application:' );
      open_td( 'bold', ( count( $applications ) > 1 ) ? selector_application( $app_field, array( 'uid_choices' => $applications ) ) : $application );
    open_tr();
      open_th( '', 'keep log [seconds]: ' );
      open_td( '', int_element( $app_option_fields['log_keep_seconds'] ) );
    open_tr();
      open_th( '', 'session lifetime [seconds]: ' );
      open_td( '', int_element( $app_option_fields['session_lifetime_seconds'] ) );
  close_table();
  open_table('css actions');
    open_caption( '', 'Actions' );
    open_tr( '', inlink( '!', 'class=big button,action=garbageCollectionGeneric,text=generic garbage collection' ) );
    $handler = "maintenance_action_buttons_$application";
    if( function_exists( $handler ) ) {
      $handler( $action );
    }
  close_table();
close_div();


open_table('list td:smallskips;qquads');

  open_tr();
    open_th('','table');
    open_th('','entries');
    open_th('','invalid');
    open_th('','orphans');
    open_th('','invalidatable');
    open_th('','considered');
    open_th('','undeletable');
    open_th('','deletable');
    open_th('','actions');

  open_tr('medskip');
    open_th( 'colspan=9,left', 'generic operations affecting all applications' );

  open_tr('medskip');

    open_td('', inlink( 'anylist', 'text=profile,table=profile' ) );

    $n_total = sql_query( 'profile', 'single_field=COUNT' );

    open_td('number', $n_total );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', $n_total );
    open_td('', inlink( '', 'action=pruneProfile,text=prune profile,class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', 'text=debug,table=debug' ) );

    $n_total = sql_query( 'debug', 'single_field=COUNT' );

    open_td('number', $n_total );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', $n_total );
    open_td('', inlink( '', 'action=pruneDebug,text=prune debug,class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', 'text=changelog,table=changelog' ) );

    $n_total = sql_query( 'changelog', 'single_field=COUNT' );
    $rv = sql_prune_changelog( $prune_opts + array( 'action' => 'dryrun' ) );

    open_td('number', $n_total );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', $rv['deleted'] );
    open_td('', inlink( '', 'action=pruneChangelog,text=prune changelog,class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', 'text=robots,table=robots' ) );

    $n_total = sql_query( 'robots', 'single_field=COUNT' );
    $rv = sql_prune_robots( $prune_opts + array( 'action' => 'dryrun' ) );

    open_td('number', $n_total );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', $rv['deleted'] );
    open_td('', inlink( '', 'action=pruneRobots,text=prune robots,class=button' ) );

  open_tr();
    open_td('colspan=9,right', inlink( '!', 'class=big button,action=garbageCollectionGenericCommon,text=garbage collection: generic/common' ) );

  open_tr();
    open_th( 'colspan=9,left', "generic operations affecting application $application only" );

  open_tr();

    open_td('', inlink( 'anylist', "text=sessions,table=sessions,application=$application" ) );

    $n_total = sql_sessions( "application=$application", 'single_field=COUNT' );
    $n_invalid = sql_sessions( "application=$application,valid=0", 'single_field=COUNT' );

    $rv = sql_expire_sessions( $prune_opts + array( 'action' => 'dryrun' ) );
    $n_invalidatable = $rv['invalidatable'];

    $rv = sql_prune_sessions( $prune_opts + array( 'action' => 'dryrun' ) );
    $n_deletable = $rv['deleted'];
    $n_undeletable = count( $rv['undeletable'] );
    $n_considered = count( $rv['considered'] );

    open_td('number', $n_total );
    open_td('number', $n_invalid );
    open_td('number', '' );
    open_td('number', $n_invalidatable );
    open_td('number', $n_considered );
    open_td('number', $n_undeletable );
    open_td('number', $n_deletable );
    open_td();
      echo html_span('block smallskipb', inlink( '!', 'action=expireSessions,text=expire sessions,class=button' ) );
      echo html_span('block', inlink( '!', 'action=pruneSessions,text=prune sessions,class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', "text=persistentvars,table=persistentvars,application=$application" ) );
    $n_total = sql_persistent_vars( "application=$application", 'single_field=COUNT' );
    $rv = sql_prune_persistentvars( $prune_opts + array( 'action' => 'dryrun' ) );

    open_td('number', $n_total );
    open_td('number', $rv['deleted_invalid'] );
    open_td('number', $rv['deleted_orphans'] );
    open_td('number', '' );
    open_td('number', count( $rv['considered'] ) );
    open_td('number', count( $rv['undeletable'] ) );
    open_td('number', $rv['deleted'] );
    open_td('', inlink( '!', 'action=prunePersistentvars,text=prune persistent vars,class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', "text=transactions,table=transactions,application=$application" ) );
    $n_total = sql_query( 'transactions', "filters=application=$application,joins=sessions,single_field=COUNT" );
    $rv = sql_prune_transactions( $prune_opts + array( 'action' => 'dryrun' ) );

    open_td('number', $n_total );
    open_td('number', $rv['deleted_invalid'] );
    open_td('number', $rv['deleted_orphans'] );
    open_td('number', '' );
    open_td('number', count( $rv['considered'] ) );
    open_td('number', count( $rv['undeletable'] ) );
    open_td('number', $rv['deleted'] );
    open_td('', inlink( '!', 'action=pruneTransactions,text=prune transactions,class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', "text=logbook (debug),table=logbook,application=$application" ) );

    $n_total = sql_logbook( "application=$application,level<=".LOG_LEVEL_DEBUG, 'single_field=COUNT' );
    $rv = sql_prune_logbook( $prune_opts + array( 'action' => 'dryrun', 'prune_level' => LOG_LEVEL_DEBUG ) );

    open_td('number', $n_total );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', count( $rv['considered'] ) );
    open_td('number', count( $rv['undeletable'] ) );
    open_td('number', $rv['deleted'] );
    open_td('', inlink( '', 'action=pruneLogDebug,text=prune logbook (debug),class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', "text=logbook (other),table=logbook,application=$application" ) );

    $n_total = sql_logbook( "application=$application,level<=".LOG_LEVEL_WARNING, 'single_field=COUNT' );
    $rv = sql_prune_logbook( $prune_opts + array( 'action' => 'dryrun', 'prune_level' => LOG_LEVEL_WARNING ) );

    open_td('number', $n_total );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', count( $rv['considered'] ) );
    open_td('number', count( $rv['undeletable'] ) );
    open_td('number', $rv['deleted'] );
    open_td('', inlink( '', 'action=pruneLogMessages,text=prune logbook (warn),class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', "text=logbook (errors),table=logbook,application=$application" ) );

    $n_total = sql_logbook( "application=$application,level<=".LOG_LEVEL_ERROR, 'single_field=COUNT' );
    $rv = sql_prune_logbook( $prune_opts + array( 'action' => 'dryrun', 'prune_level' => LOG_LEVEL_ERROR ) );

    open_td('number', $n_total );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', count( $rv['considered'] ) );
    open_td('number', count( $rv['undeletable'] ) );
    open_td('number' );
      echo html_span( 'block number', $rv['deleted'] );
      echo html_span( 'block smaller', '(manual prune only)' );
    open_td('', inlink( '', 'action=pruneLogErrors,text=prune logbook (errors),class=button' ) );

  open_tr();
    open_td('colspan=9,right', inlink( '!', "class=big button,action=garbageCollectionGenericApp,text=garbage collection: generic/for $application" ) );

  $handler = "maintenance_table_rows_$application";
  if( function_exists( $handler ) ) {
    open_tr('medskip');
      open_th( 'colspan=9,left', "specific operations provided by application $application" );
      $handler();
  }

close_table();

?>
