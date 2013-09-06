<?php

need_priv( 'logbook', 'list' );

// put these gadgets here - don't need them (yet?) in any other scripts:
//
function selector_log_level( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'level' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'items' => adefault( $opts, 'items', array() ) + $GLOBALS['log_level_text']
  , 'default_display' => ' - select level - '
  );
  return select_element( $field );
}

function filter_log_level( $field, $opts = array() ) {
  return selector_log_level( $field, add_filter_default( $opts ) );
}

echo html_tag( 'h1', '', 'logbook' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = array(
  'sessions_id' => array( 'auto' => 1, 'allow_null' => '0' )
, 'thread' => 'auto=1'
, 'flags' => 'auto=1'
, 'level' => array( 'u2', 'relation' => '>=' )
, 'REGEX_tags' => 'h,size=40,auto=1'
, 'REGEX_note' => 'h,size=40,auto=1'
);
$fields['sessions_id']['min'] = sql_query( 'logbook', 'single_field=min_id,selects=MIN(sessions_id) as min_id,groupby=' );
$fields['sessions_id']['max'] = $fields['sessions_id']['initval'] = sql_query( 'logbook', 'single_field=max_id,selects=MAX(sessions_id) as max_id,groupby=' );

$fields = init_fields( $fields, 'tables=logbook,cgi_prefix=' );

handle_action( array( 'update', 'deleteLogentry' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
  case 'deleteLogentry':
    menatwork();
}

open_div('menubox');
  open_table( 'css filters' );
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
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
      open_th( 'right', 'thread:' );
      open_td( '', filter_thread( $fields['thread'] ) );
    open_tr();
      open_th( 'right', 'level:' );
      open_td( '', filter_log_level( $fields['level'] ) );
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
      open_td( '', filter_reset_button( $fields['REGEX_tags'] ) . ' / '. string_element( $fields['REGEX_tags'] ) .' /  ' );
    open_tr();
      open_th( 'right', 'note:' );
      open_td( '', filter_reset_button( $fields['REGEX_note'] ) . ' / '. string_element( $fields['REGEX_note'] ) .' /  ' );
  close_table();
close_div();

logbook_view( $fields['_filters'] );

?>
