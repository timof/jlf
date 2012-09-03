<?php

echo html_tag( 'h1', '', 'logbook' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = array(
  'sessions_id' => array( 'auto' => 1 )
, 'thread' => 'auto=1'
, 'flags' => 'auto=1'
, 'REGEX_tags' => 'h,size=40,auto=1'
, 'REGEX_note' => 'h,size=40,auto=1'
);
$fields['sessions_id']['min'] = sql_query( 'logbook', 'single_field=min_id,selects=MIN(sessions_id) as min_id,groupby=' );
$fields['sessions_id']['max'] = $fields['sessions_id']['default'] = sql_query( 'logbook', 'single_field=max_id,selects=MAX(sessions_id) as max_id,groupby=' );

$fields = init_fields( $fields, 'tables=logbook,cgi_prefix=' );

handle_action( array( 'update', 'prune' ) );
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
    open_th( 'right', 'thread:' );
    open_td();
      filter_thread( $fields['thread'] );
  open_tr();
    open_th( 'right', 'flags:' );
    open_td();
      foreach( $log_flag_text as $mask => $text ) {
        $fields['flags']['text'] = $text;
        $fields['flags']['mask'] = $mask;
        echo checkbox_element( $fields['flags'] );
      }
  open_tr();
    open_th( 'right', 'tags:' );
    open_td( '', string_element( $fields['REGEX_tags'] ) );
  open_tr();
    open_th( 'right', 'note:' );
    open_td( '', string_element( $fields['REGEX_note'] ) );
close_table();

bigskip();

logbook_view( $fields['_filters'] );

?>
