<?php // /code/maintenance.php

echo html_tag( 'h1', '', 'maintenance' );

sql_transaction_boundary('*');

need_priv('*','*');

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );
define( 'OPTION_SHOW_PERSISTENT_VARS', 0x01 );
define( 'OPTION_SHOW_GARBAGE', 0x02 );
define( 'OPTION_SHOW_DANGLING', 0x04 );
define( 'OPTION_SHOW_TESTS', 0x08 );

$fields = array(
  'sessions_id' => array( 'auto' => 1, 'allow_null' => '0', 'default' => '0' )
, 'thread' => "auto=1,initval=$thread,cgi_name=F_thread,default=0"
, 'window' => 'auto=1,cgi_name=F_window'
, 'script' => 'auto=1,cgi_name=F_script'
, 'self' => 'B,auto=1'
);
$fields['sessions_id']['min'] = sql_query( 'persistentvars', 'single_field=min_id,selects=MIN(sessions_id) as min_id,groupby=' );
$fields['sessions_id']['max'] = $fields['sessions_id']['initval'] = sql_query( 'persistentvars', 'single_field=max_id,selects=MAX(sessions_id) as max_id,groupby=' );

$fields = init_fields( $fields, 'tables=persistentvars' );

$filters = & $fields['_filters'];

$option_fields = array(
  'application' => "A64,initval=$jlf_application_name"
, 'prune_days' => 'type=u,size=3,global=1,sources=http persistent,set_scoped=self,default=8,auto=1'
);
$option_fields = init_fields( $option_fields );
$prune_days = $option_fields['prune_days']['value'];

handle_action( array( 'update', 'deletePersistentVar', 'deleteByFilterPersistentVars', 'pruneSessions', 'pruneLogbook', 'pruneChangelog', 'garbageCollection', 'resetDanglingLinks' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
  case 'deleteByFilterPersistentVar':
    need( $message );
    sql_delete_persistent_vars( $filters );
    break;
  case 'deletePersistentVar':
    need( $message );
    sql_delete_persistent_vars( $message );
    break;
  case 'pruneSessions':
    // will also prune transactions and persistent_vars belonging to sessions!
    sql_prune_sessions( array( 'maxage_seconds' => $prune_days * 3600 * 24 ) );
    break;
  case 'pruneChangelog':
    sql_prune_changelog( array( 'maxage_seconds' => $prune_days * 3600 * 24 ) );
    break;
  case 'pruneLogbook':
    sql_prune_logbook( array( 'maxage_seconds' => $prune_days * 3600 * 24 ) );
    break;
  case 'garbageCollection':
    garbage_collection();
    break;
  case 'resetDanglingLinks':
    init_var( 'reset_table', 'type=W64,global=1,sources=http' );
    init_var( 'reset_col', 'type=W64,global,sources=http' );
    init_var( 'reset_id', 'type=u,global,sources=http' );
    sql_reset_dangling_links( $reset_table, $reset_col, $reset_id );
    break;
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
      open_td( '', int_element( $option_fields['prune_days'] ) );
  close_table();
  open_table( 'css filters' );
  open_caption( '', filter_reset_button( $fields ) . 'Filter' );
  open_tr();
    open_th( 'right', 'session:' );
    open_td( 'oneline' );
      if( $fields['sessions_id']['value'] ) {
        echo selector_int( $fields['sessions_id'] );
        open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "all", 'P2_sessions_id' => 0 ) ) );
      } else {
        open_span( 'quads', '(all)' );
        open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'filter...', 'P2_sessions_id' => $fields['sessions_id']['max'] ) ) );
      }
  open_tr();
    open_th( 'right', 'window:' );
    open_td( 'oneline', filter_window( $fields['window'] ) );
  open_tr();
    open_th( 'right', 'thread:' );
    open_td( 'oneline', filter_thread( $fields['thread'] ) );
  open_tr();
    open_th( 'right', 'script:' );
    open_td( 'oneline', filter_script( $fields['script'] ) );
  open_tr();
    open_th( 'right', 'self:' );
    open_td();
      open_span( 'qquad bold', 'self: '.checkbox_element( $fields['self'], 'text=self' ) );
  close_table();
close_div();

need( ( $application = $option_fields['application']['value'] ) );


if( $options & OPTION_SHOW_TESTS ) {
  // for experimental code
  open_fieldset( '', inlink( '', array(
    'options' => ( $options & ~OPTION_SHOW_TESTS )
  , 'class' => 'icon close'
  , 'text' => ''
  ) ) . ' tests' );

    foreach( array( 'drop', 'lock', 'edit', 'uparrow', 'downarrow', 'plus', 'equal', 'close', 'open', 'plain' ) as $s ) {
    //// 'record', 'browse', 'people', 'cash', 'chart',
      open_div();
        open_tag( 'a', "class=$s icon quads tinyskips,style=font-size:6pt", '' );
        open_tag( 'a', "class=$s button quads tinyskips,style=font-size:6pt", "test: $s" );
        open_tag( 'a', "class=$s icon quads tinyskips,style=font-size:10pt;", '' );
        open_tag( 'a', "class=$s button quads tinyskips,style=font-size:10pt;", "test: $s" );
        open_tag( 'a', "class=$s big button quads tinyskips,style=font-size:10pt;", "test: $s" );
      close_div();
    }
    foreach( array( 'fant', 'file', 'leftarrow', 'rightarrow', 'outlink', 'plain' ) as $s ) {
      open_div();
        open_tag( 'a', "class=$s quads tinyskips,style=font-size:6pt;outline:1px dashed red;", '' );
        open_tag( 'a', "class=$s quads tinyskips,style=font-size:6pt;outline:1px dashed red;", "test: $s" );
        qquad();
        open_tag( 'a', "class=$s icon quads tinyskips,style=font-size:6pt", '' );
        open_tag( 'a', "class=$s quads tinyskips,style=font-size:6pt", "test: $s" );
        open_tag( 'a', "class=$s icon quads tinyskips,style=font-size:10pt", '' );
        open_tag( 'a', "class=$s quads tinyskips,style=font-size:10pt", "test: $s" );
      close_div();
    }
  close_fieldset();
} else {
  open_div( 'left smallskipb', inlink( '', array( 'options' => ( $options | OPTION_SHOW_TESTS ) , 'text' => 'tests...', 'class' => 'button' ) ) );
}

bigskip();


if( $options & OPTION_SHOW_PERSISTENT_VARS ) {
  open_fieldset( '', inlink( '', array(
    'options' => ( $options & ~OPTION_SHOW_PERSISTENT_VARS )
  , 'class' => 'icon close'
  , 'text' => ''
  ) ) . ' persistent vars' );


//    open_div( 'right smallskipb', inlink( '', 'text=delete by filter,class=drop button,action=deleteByFilterPersistentVars' ) );
    persistent_vars_view( $fields['_filters'] );
  close_fieldset();
} else {
  open_div( 'left smallskipb', inlink( '', array( 'options' => ( $options | OPTION_SHOW_PERSISTENT_VARS ) , 'text' => 'persistent vars...', 'class' => 'button' ) ) );
}

bigskip();

if( $options & OPTION_SHOW_GARBAGE ) {
  open_fieldset( '', inlink( '', array(
    'options' => ( $options & ~OPTION_SHOW_GARBAGE )
  , 'class' => 'icon close'
  , 'text' => ''
  ) ) . ' garbage collection' );
  
    open_table('list td:smallskips;qquads');
    
      open_tr();
        open_th('','table');
        open_th('','entries');
        open_th('','invalid');
        open_th('','invalidatable');
        open_th('','orphans');
        open_th('','deletable');
        open_th('','actions');


      open_tr('medskip');

        open_td('', 'sessions' );

        $n_total = sql_sessions( "application=$application", 'single_field=COUNT' );
        $n_invalid = sql_sessions( "application=$application,valid=0", 'single_field=COUNT' );

        $rv = sql_prune_sessions( array(
          'action' => 'dryrun'
        , 'application' => $application
        // use globally configured value:      , 'session_lifetime' => $prune_days * 24 * 3600
        , 'keep_log_seconds' => $prune_days * 24 * 3600
        ) );

        $n_invalidatable = $rv['count_invalidate_sessions'];
        $n_deletable = $rv['deletable'];

        open_td('number', $n_total );
        open_td('number', $n_invalid );
        open_td('number', $n_invalidatable );
        open_td('number', 'n/a' );
        open_td('number', $n_deletable );
        open_td('', inlink( '', 'action=pruneSessions,text=prune sessions,class=button' ) );

      open_tr('medskip');

        open_td('', 'persistentvars' );
        $n_total = sql_persistent_vars( "application=$application", 'single_field=COUNT' );
        open_td('number', $n_total );
        open_td('number', $rv['count_delete_persistentvars_invalid'] );
        open_td('number', 'n/a' );
        open_td('number', $rv['count_delete_persistentvars_orphans'] );
        open_td('number', $rv['count_delete_persistentvars'] );
        open_td('','');

      open_tr('medskip');

        open_td('', 'transactions' );
        $n_total = sql_query( 'transactions', "joins=sessions,application=$application,single_field=COUNT" );
        open_td('number', $n_total );
        open_td('number', $rv['count_delete_transactions_invalid'] );
        open_td('number', 'n/a' );
        open_td('number', $rv['count_delete_transactions_orphans'] );
        open_td('number', $rv['count_delete_transactions'] );
        open_td('','');

      open_tr('medskip');

        open_td('', 'logbook' );

        $n_total = sql_logbook( "application=$application", 'single_field=COUNT' );
        $rv = sql_prune_logbook( array(
          'action' => 'dryrun'
        , 'application' => $application
        , 'keep_log_seconds' => $prune_days * 24 * 3600
        ) );

        open_td('number', $n_total );
        open_td('number', 'n/a' );
        open_td('number', 'n/a' );
        open_td('number', 'n/a' );
        open_td('number', $rv['deletable'] );
        open_td('', inlink( '', 'action=pruneLogbook,text=prune logbook,class=button' ) );
    
      open_tr('medskip');

        open_td('', 'changelog' );

        $n_total = sql_query( 'changelog', 'single_field=COUNT' );
        $rv = sql_delete_changelog( 'ctime < '.datetime_unix2canonical( $now_unix - $prune_days * 24 * 3600 ), 'action=dryrun' );

        open_td('number', $n_total );
        open_td('number', 'n/a' );
        open_td('number', 'n/a' );
        open_td('number', 'n/a' );
        open_td('number', $rv['deletable'] );
        open_td('', inlink( '', 'action=pruneChangelog,text=prune changelog,class=button' ) );

      open_tr('medskip');
        open_td( 'colspan=7,right', inlink( '', 'action=garbageCollection,text=garbage collection,class=button' ) );

    close_table();
  close_fieldset();
} else {
  open_div( 'left smallskipb', inlink( ''
  , array( 'options' => ( $options | OPTION_SHOW_GARBAGE ) , 'text' => 'garbage collection...', 'class' => 'button' )
  ) );
}
// 
// if( $options & OPTION_SHOW_DANGLING ) {
//   open_fieldset( '', inlink( '', array(
//     'options' => ( $options & ~OPTION_SHOW_DANGLING )
//   , 'class' => 'close_small'
//   , 'text' => ''
//   ) ) . ' dangling links' );
//   
//     $f = init_fields( array( 'table' => 'global=table,type=w,sources=http persistent,set_scopes=self' ) );
//     open_div('menubox');
//       open_table('css filters');
//         open_caption( '', 'options' );
//         open_tr('td:smallpads;qquads');
//           open_th( '', 'table:' );
//           open_td( 'oneline', selector_table( $f['table'] ) . filter_reset_button( $f['table'], '/floatright//' ) );
//       close_table();
//     close_div();
// 
//     dangling_links_view('actionReset=1');
// 
//   close_fieldset();
// } else {
//   open_div( 'left smallskipb', inlink( ''
//   , array( 'options' => ( $options | OPTION_SHOW_DANGLING ) , 'text' => 'dangling links...', 'class' => 'button' )
//   ) );
// }
// 

?>
