<?php // /code/maintenance.php

echo html_tag( 'h1', '', 'persistent vars' );

sql_transaction_boundary('*');

need_priv('*','*');


$fields = array(
  'application' => "W64,initval=,allow_null=,global=1"
, 'sessions_id' => array( 'auto' => 1, 'allow_null' => '0', 'initval' => '0' )
, 'thread' => "auto=1,initval=$thread,cgi_name=F_thread,default=0"
, 'window' => 'auto=1,cgi_name=F_window'
, 'script' => 'auto=1,cgi_name=F_script'
, 'self' => 'B,auto=1'
);
$fields['sessions_id']['min'] = sql_query( 'persistentvars', 'single_field=min_id,selects=MIN(sessions_id) as min_id,groupby=' );
$fields['sessions_id']['max'] = $fields['sessions_id']['initval'] = sql_query( 'persistentvars', 'single_field=max_id,selects=MAX(sessions_id) as max_id,groupby=' );

$fields = init_fields( $fields, 'tables=persistentvars' );

$filters = & $fields['_filters'];



handle_actions( array( 'deletePersistentVar' ) );

if( $action ) switch( $action ) {
//   case 'deleteByFilterPersistentVar':
//     need( $message );
//     sql_delete_persistent_vars( $filters );
//     break;
  case 'deletePersistentVar':
    $v = init_var( 'persistentvars_id', 'type=U,sources=http' );
    sql_delete_persistent_vars( $v['value'] );
    break;
//
}

// flush_all_messages();

open_div('menubox');
  open_table( 'css filters' );
    open_caption( '', filter_reset_button( $fields ) . 'Filter' );
    open_tr();
      open_th( '', 'application:' );
      open_td( '', filter_application( $fields['application'] ) );
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


persistent_vars_view( $fields['_filters'] );

?>
