<?php // /code/maintenance.php

echo html_tag( 'h1', '', 'maintenance' );

sql_transaction_boundary('*');

need_priv('*','*');


$option_fields = array(
  'application' => "W64,initval=$jlf_application_name,global=1"
, 'keep_log_days' => 'type=u,size=3,global=1,sources=http persistent initval,set_scopes=self,auto=1,initval='. floor( $keep_log_seconds / 24 / 3600 )
, 'session_lifetime_days' => 'type=u,size=3,global=1,sources=http persistent initval,set_scopes=self,auto=1,initval='. floor( $session_lifetime_seconds / 24 / 3600 )
);
$option_fields = init_fields( $option_fields );
need( $application );

$prune_opts = array( 
  'application' => $application
, 'session_lifetime_seconds' => $session_lifetime_days * 3600 * 24
, 'keep_log_seconds' => $keep_log_days * 3600 * 24
);

handle_actions( array(
  'pruneTransactions'
, 'prunePersistentvars'
, 'pruneSessions'
, 'pruneChangelog'
, 'pruneLogMessages'
, 'pruneLogErrors'
, 'pruneDebug'
, 'pruneProfile'
, 'garbageCollection'
// , 'resetDanglingLinks'
) );
if( $action ) switch( $action ) {
//
  case 'pruneTransactions':
    sql_prune_transactions( $prune_opts );
    break;
  case 'prunePersistentvars':
    sql_prune_persistentvars( $prune_opts );
    break;
  case 'pruneSessions':
    sql_prune_sessions( $prune_opts );
    break;
  case 'pruneChangelog':
    sql_prune_changelog( $prune_opts );
    break;
  case 'pruneLogMessages':
    sql_prune_logbook( $prune_opts );
    break;
  case 'pruneLogErrors':
    sql_prune_logbook( $prune_opts + array( 'prune_errors' => 1 ) );
    break;
  case 'pruneDebug':
    sql_delete( 'debug', true );
    break;
  case 'pruneProfile':
    sql_delete( 'profile', true );
    break;
  case 'garbageCollection':
    garbage_collection();
    break;
//   case 'resetDanglingLinks':
//     init_var( 'reset_table', 'type=W64,global=1,sources=http' );
//     init_var( 'reset_col', 'type=W64,global,sources=http' );
//     init_var( 'reset_id', 'type=u,global,sources=http' );
//     sql_reset_dangling_links( $reset_table, $reset_col, $reset_id );
//     break;
}


flush_all_messages();

open_div('menubox');
  open_table('css filters');
    open_caption( '', 'Options' );
    open_tr();
      open_th( '', 'application:' );
      open_td( '', selector_application( $option_fields['application'] ) );
    open_tr();
      open_th( '', 'keep days: ' );
      open_td( '', int_element( $option_fields['keep_log_days'] ) );
    open_tr();
      open_th( '', 'lifetime days: ' );
      open_td( '', int_element( $option_fields['session_lifetime_days'] ) );
  close_table();
close_div();

open_table('list td:smallskips;qquads');

  open_tr();
    open_th('','table');
    open_th('','entries');
    open_th('','invalid');
    open_th('','orphans');
    open_th('','invalidatable');
    open_th('','deletable');
    open_th('','actions');

  open_tr('medskip');
    open_th( 'colspan=7', 'affects all applications' );

  open_tr('medskip');

    open_td('', inlink( 'anylist', 'text=profile,table=profile' ) );

    $n_total = sql_query( 'profile', 'single_field=COUNT' );

    open_td('number', $n_total );
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
    open_td('number', $rv['deletable'] );
    open_td('', inlink( '', 'action=pruneChangelog,text=prune changelog,class=button' ) );

  open_tr('medskip');
    open_th( 'colspan=7', "affects application $application only" );

  open_tr('medskip');

    open_td('', inlink( 'anylist', "text=sessions,table=sessions,application=$application" ) );

    $n_total = sql_sessions( "application=$application", 'single_field=COUNT' );
    $n_invalid = sql_sessions( "application=$application,valid=0", 'single_field=COUNT' );

    $rv = sql_prune_sessions( $prune_opts + array( 'action' => 'dryrun' ) );

    $n_invalidatable = $rv['invalidatable'];
    $n_deletable = $rv['deletable'];

    open_td('number', $n_total );
    open_td('number', $n_invalid );
    open_td('number', '' );
    open_td('number', $n_invalidatable );
    open_td('number', $n_deletable );
    open_td('', inlink( '!', 'action=pruneSessions,text=prune sessions,class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', "text=persistentvars,table=persistentvars,application=$application" ) );
    $n_total = sql_persistent_vars( "application=$application", 'single_field=COUNT' );
    $rv = sql_prune_persistentvars( $prune_opts + array( 'action' => 'dryrun' ) );

    open_td('number', $n_total );
    open_td('number', $rv['deletable_invalid'] );
    open_td('number', $rv['deletable_orphans'] );
    open_td('number', '' );
    open_td('number', $rv['deletable'] );
    open_td('', inlink( '!', 'action=prunePersistentvars,text=prune persistent vars,class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', "text=transactions,table=transactions,application=$application" ) );
    $n_total = sql_query( 'transactions', "filters=application=$application,joins=sessions,single_field=COUNT" );
    $rv = sql_prune_transactions( $prune_opts + array( 'action' => 'dryrun' ) );

    open_td('number', $n_total );
    open_td('number', $rv['deletable_invalid'] );
    open_td('number', $rv['deletable_orphans'] );
    open_td('number', '' );
    open_td('number', $rv['deletable'] );
    open_td('', inlink( '!', 'action=pruneTransactions,text=prune transactions,class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', "text=logbook,table=logbook,application=$application" ) );

    $n_total = sql_logbook( "application=$application,level<".LOG_LEVEL_ERROR, 'single_field=COUNT' );
    $rv = sql_prune_logbook( $prune_opts + array( 'action' => 'dryrun' ) );

    open_td('number', $n_total );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', $rv['deletable'] );
    open_td('', inlink( '', 'action=pruneLogMessages,text=prune logbook (other),class=button' ) );

  open_tr('medskip');

    open_td('', inlink( 'anylist', "text=logbook,table=logbook,application=$application" ) );

    $n_total = sql_logbook( "application=$application,level=".LOG_LEVEL_ERROR, 'single_field=COUNT' );
    $rv = sql_prune_logbook( $prune_opts + array( 'action' => 'dryrun', 'prune_errors' => 1 ) );

    open_td('number', $n_total );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', '' );
    open_td('number', $rv['deletable'] );
    open_td('', inlink( '', 'action=pruneLogErrors,text=prune logbook (errors),class=button' ) );

  open_tr('medskip');
    open_td( 'colspan=7,right', inlink( '', 'action=garbageCollection,text=garbage collection,class=button' ) );

close_table();

?>
