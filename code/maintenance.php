<?php

echo html_tag( 'h1', '', 'maintenance' );

need_priv('*','*');

$f_prune_days = init_var( 'prune_days', 'type=u,size=3,global=1,sources=http persistent,set_scoped=self,default=8,auto=1' );
init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );
define( 'OPTION_SHOW_PERSISTENT_VARS', 0x01 );
define( 'OPTION_SHOW_GARBAGE', 0x02 );
define( 'OPTION_SHOW_DANGLING', 0x04 );

$fields = array(
  'sessions_id' => array( 'auto' => 1, 'allow_null' => '0', 'default' => '0' )
, 'thread' => 'auto=1'
, 'window' => 'auto=1'
, 'script' => 'auto=1'
, 'self' => 'b,auto=1'
);
$fields['sessions_id']['min'] = sql_query( 'persistentvars', 'single_field=min_id,selects=MIN(sessions_id) as min_id,groupby=' );
$fields['sessions_id']['max'] = $fields['sessions_id']['initval'] = sql_query( 'persistentvars', 'single_field=max_id,selects=MAX(sessions_id) as max_id,groupby=' );

$fields = init_fields( $fields, 'tables=persistentvars,cgi_prefix=' );

$filters = & $fields['_filters'];

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
    prune_sessions( $prune_days * 3600 * 24 );
    break;
  case 'pruneChangelog':
    prune_changelog( $prune_days * 3600 * 24 );
    break;
  case 'pruneLogbook':
    prune_logbook( $prune_days * 3600 * 24 );
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
  open_table( 'css filters' );
  open_caption( '', 'Filter' . filter_reset_button( $fields, 'floatright' ) );
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
    open_td();
      echo filter_script( $fields['script'] );
      open_span( 'qquad bold', 'self: '.checkbox_element( $fields['self'], 'text=self' ) );
  close_table();
close_div();

if( $options & OPTION_SHOW_PERSISTENT_VARS ) {
  open_fieldset( '', inlink( '', array(
    'options' => ( $options & ~OPTION_SHOW_PERSISTENT_VARS )
  , 'class' => 'close_small'
  , 'text' => ''
  ) ) . ' persistent vars' );
    open_div( 'right smallskipb', inlink( '', 'text=delete by filter,class=drop button,action=deleteByFilterPersistentVars' ) );
    persistent_vars_view( $fields['_filters'] );
  close_fieldset();
} else {
  open_div( 'left smallskipb', inlink( '', array( 'options' => ( $options | OPTION_SHOW_PERSISTENT_VARS ) , 'text' => 'persistent vars...', 'class' => 'button' ) ) );
}

bigskip();

if( $options & OPTION_SHOW_GARBAGE ) {
  open_fieldset( '', inlink( '', array(
    'options' => ( $options & ~OPTION_SHOW_GARBAGE )
  , 'class' => 'close_small'
  , 'text' => ''
  ) ) . ' garbage collection' );
  
    open_div('smallskipb');
      echo 'keep days: ' . int_element( $f_prune_days );
    close_div();
    open_table('list td:smallskips;qquads');
      open_tr();
        open_th('','table');
        open_th('','entries');
        open_th('','to be pruned');
        open_th('','actions be pruned');
    
      open_tr('medskip');
        $n_total = sql_sessions( '', 'single_field=COUNT' );
        $n_prune = sql_sessions( 'atime < '.datetime_unix2canonical( $now_unix - $prune_days * 24 * 3600 ), 'single_field=COUNT' );
        open_td('', 'sessions' );
        open_td('number', $n_total );
        open_td('number', $n_prune );
        open_td('', inlink( '', 'action=pruneSessions,text=prune sessions,class=button' ) );
    
      open_tr('medskip');
        $n_total = sql_logbook( '', 'single_field=COUNT' );
        $n_prune = sql_logbook( 'utc < '.datetime_unix2canonical( $now_unix - $prune_days * 24 * 3600 ), 'single_field=COUNT' );
        open_td('', 'logbook' );
        open_td('number', $n_total );
        open_td('number', $n_prune );
        open_td('', inlink( '', 'action=pruneLogbook,text=prune logbook,class=button' ) );
    
      open_tr('medskip');
        $n_total = sql_query( 'changelog', 'single_field=COUNT' );
        $n_prune = sql_query( 'changelog', array( 'single_field' => 'COUNT', 'filters' => 'ctime < '.datetime_unix2canonical( $now_unix - $prune_days * 24 * 3600 ) ) );
        open_td('', 'changelog' );
        open_td('number', $n_total );
        open_td('number', $n_prune );
        open_td('', inlink( '', 'action=pruneChangelog,text=prune changelog,class=button' ) );
    
      open_tr('medskip');
        open_td( 'colspan=4,right', inlink( '', 'action=garbageCollection,text=garbage collection,class=button' ) );

    close_table();
  close_fieldset();
} else {
  open_div( 'left smallskipb', inlink( ''
  , array( 'options' => ( $options | OPTION_SHOW_GARBAGE ) , 'text' => 'garbage collection...', 'class' => 'button' )
  ) );
}

if( $options & OPTION_SHOW_DANGLING ) {
  open_fieldset( '', inlink( '', array(
    'options' => ( $options & ~OPTION_SHOW_DANGLING )
  , 'class' => 'close_small'
  , 'text' => ''
  ) ) . ' dangling links' );
  
    dangling_links_view('actionReset=1');

  close_fieldset();
} else {
  open_div( 'left smallskipb', inlink( ''
  , array( 'options' => ( $options | OPTION_SHOW_DANGLING ) , 'text' => 'dangling links...', 'class' => 'button' )
  ) );
}


?>
