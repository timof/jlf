<?php


echo html_tag( 'h1', '', 'maintenance' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = array(
  'sessions_id' => array( 'auto' => 1 )
, 'thread' => 'auto=1'
, 'window' => 'auto=1'
, 'script' => 'auto=1'
, 'self' => 'b,auto=1'
);
$fields['sessions_id']['min'] = sql_query( 'persistent_vars', 'single_field=min_id,selects=MIN(sessions_id) as min_id,groupby=' );
$fields['sessions_id']['max'] = $fields['sessions_id']['default'] = sql_query( 'persistent_vars', 'single_field=max_id,selects=MAX(sessions_id) as max_id,groupby=' );

$fields = init_fields( $fields, 'tables=persistent_vars,cgi_prefix=' );

handle_action( array( 'update', 'deletePersistentVar', 'deleteByFilterPersistentVars' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
  case 'prune':
    menatwork();
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

open_fieldset( '', 'persistent vars', 'on' );
  persistent_vars_view( $fields['_filters'] );
close_fieldset();

bigskip();

open_fieldset( '', 'garbage collection', 'on' );
init_var( 'prune_days', 'type=u,global=1,sources=http persistent,set_scoped=self,default=8' );

open_table('list');
  open_tr();
    open_th('','table');
    open_th('','entries');
    open_th('','to be pruned');
    open_th('','actions be pruned');

  open_tr();
    $n_total = count( sql_sessions() );
    $n_prune = count( sql_sessions( 'atime < '.datetime_unix2canonical( $now_unix - $prune_days * 24 * 3600 ) ) );
    open_td('', 'sessions' );
    open_td('number', $n_total );
    open_td('number', $n_prune );
    open_td('', action_button_view( array( 'action' => 'pruneSessions', 'text' => 'prune sessions' ) ) );

close_table();



close_fieldset();

?>
