<?php


echo html_tag( 'h1', '', 'maintenance' );

$f_prune_days = init_var( 'prune_days', 'type=u,size=3,global=1,sources=http persistent,set_scoped=self,default=8,auto=1' );
init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );
define( 'OPTION_SHOW_PERSISTENT_VARS', 0x01 );
define( 'OPTION_SHOW_GARBAGE', 0x02 );

$fields = array(
  'sessions_id' => array( 'auto' => 1, 'allow_null' => '0' )
, 'thread' => 'auto=1'
, 'window' => 'auto=1'
, 'script' => 'auto=1'
, 'self' => 'b,auto=1'
);
$fields['sessions_id']['min'] = sql_query( 'persistent_vars', 'single_field=min_id,selects=MIN(sessions_id) as min_id,groupby=' );
$fields['sessions_id']['max'] = $fields['sessions_id']['default'] = sql_query( 'persistent_vars', 'single_field=max_id,selects=MAX(sessions_id) as max_id,groupby=' );

$fields = init_fields( $fields, 'tables=persistent_vars,cgi_prefix=' );

$filters = & $fields['_filters'];

handle_action( array( 'update', 'deletePersistentVar', 'deleteByFilterPersistentVars', 'pruneSessions' ) );
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
    // will also prune transactions and persistent_vars beloning to sessions!
    prune_sessions( $prune_days );
    break;
  case 'pruneChangelog':
    prune_changelog( $prune_days );
    break;
  case 'pruneLogbook':
    prune_logbok( $prune_days );
    break;
}

open_table( 'menu' );
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( 'right', 'session:' );
    open_td( 'oneline' );
      if( $fields['sessions_id']['value'] ) {
        selector_int( $fields['sessions_id'] );
        open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "all", 'P2_sessions_id' => 0 ) ) );
      } else {
        open_span( 'quads', '(all)' );
        open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'filter...', 'P2_sessions_id' => $fields['sessions_id']['max'] ) ) );
      }
  open_tr();
    open_th( 'right', 'window:' );
    open_td( 'oneline' );
      filter_window( $fields['window'] );
  open_tr();
    open_th( 'right', 'thread:' );
    open_td( 'oneline' );
      filter_thread( $fields['thread'] );
  open_tr();
    open_th( 'right', 'script:' );
    open_td();
      filter_script( $fields['script'] );
      open_span( 'qquad bold', 'self: '.checkbox_element( $fields['self'], 'text=self' ) );
close_table();

bigskip();

if( $options & OPTION_SHOW_PERSISTENT_VARS ) {
  open_fieldset( '', inlink( '', array(
    'options' => ( $options & ~OPTION_SHOW_PERSISTENT_VARS )
  , 'class' => 'close_small'
  , 'text' => ''
  ) ) . ' persistent vars' );
    open_div( 'right smallskipb', action_button_view( '', 'text=delete by filter,class=drop button,action=deleteByFilterPersistentVars' ) );
    persistent_vars_view( $fields['_filters'] );
  close_fieldset();
} else {
  open_div( 'left smallskipb', action_button_view( '', array(
    'options' => ( $options | OPTION_SHOW_PERSISTENT_VARS )
  , 'text' => 'persistent vars...'
  ) ) );
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
    open_table('list');
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
        open_td('', action_button_view( array( 'action' => 'pruneSessions', 'text' => 'prune sessions' ) ) );
    
      open_tr('medskip');
        $n_total = sql_logbook( '', 'single_field=COUNT' );
        $n_prune = sql_logbook( 'utc < '.datetime_unix2canonical( $now_unix - $prune_days * 24 * 3600 ), 'single_field=COUNT' );
        open_td('', 'logbook' );
        open_td('number', $n_total );
        open_td('number', $n_prune );
        open_td('', action_button_view( array( 'action' => 'pruneLogbook', 'text' => 'prune logbook' ) ) );
    
      open_tr('medskip');
        $n_total = sql_query( 'changelog', 'single_field=COUNT' );
        $n_prune = sql_query( 'changelog', array( 'single_field' => 'COUNT', 'filters' => 'ctime < '.datetime_unix2canonical( $now_unix - $prune_days * 24 * 3600 ) ) );
        open_td('', 'changelog' );
        open_td('number', $n_total );
        open_td('number', $n_prune );
        open_td('', action_button_view( array( 'action' => 'pruneChangelog', 'text' => 'prune changelog' ) ) );
    
    close_table();
  close_fieldset();
} else {
  open_div( 'left smallskipb', action_button_view( '', array(
    'options' => ( $options | OPTION_SHOW_GARBAGE )
  , 'text' => 'garbage collection...'
  ) ) );
}

?>
