<?php // /code/logbook.php

need_priv( '*', '*' );
sql_transaction_boundary('logbook,sessions');

// put these gadgets here - don't need them (yet?) in any other scripts:
//
function selector_log_level( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'level' );
  }
  $opts = parameters_explode( $opts );
  $field += array(
    'choices' => adefault( $opts, 'choices', array() ) + $GLOBALS['log_level_text']
  , 'default_display' => ' - select level - '
  );
  return select_element( $field );
}

function filter_log_level( $field, $opts = array() ) {
  return selector_log_level( $field, add_filter_default( $opts, $field ) );
}

echo html_tag( 'h1', '', 'logbook' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = array(
  'sessions_id' => array( 'auto' => 1, 'allow_null' => '0' )
, 'thread' => 'auto=1'
, 'flags' => 'auto=1'
, 'level' => array( 'u2', 'relation' => '>=' )
, 'fscript' => 'w64,auto=1,sql_name=script'
, 'REGEX_tags' => 'h,size=40,auto=1'
, 'REGEX_note' => 'h,size=40,auto=1'
);
$fields['sessions_id']['min'] = sql_query( 'logbook', 'single_field=min_id,selects=MIN(sessions_id) as min_id,groupby=' );
$fields['sessions_id']['max'] = $fields['sessions_id']['initval'] = sql_query( 'logbook', 'single_field=max_id,selects=MAX(sessions_id) as max_id,groupby=' );

$fields = init_fields( $fields, 'tables=logbook,cgi_prefix=' );

handle_actions( array( 'update', 'deleteLogentry' ) );
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
      open_th( 'right', 'script:' );
      open_td( '', filter_script( $fields['fscript'], 'filters=tables=logbook' ) );
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

$filters = $fields['_filters'];

$list_options = handle_list_options( true, 'log', array( 
  'nr' => 't'
, 'id' => 't,s=logbook_id DESC'
, 'session' => 't,s=sessions_id'
, 'level' => 't,s'
, 'login_people_id' => 't,s'
, 'login_remote_addr' => array( 't', 's' => "CONCAT( login_remote_ip, ':', login_remote_port )" )
, 'utc' => 't,s'
, 'thread' => 't,s', 'window' => 't,s', 'script' => 't,s'
, 'parent' => array( 't', 's' => "CONCAT( parent_thread, parent_window, parent_script )" )
, 'flags' => 't'
, 'tags' => 't,s'
, 'links' => 't='. ( ( $global_format === 'html' ) ? '1' : 'off' )
, 'note' => 't,s'
) );

if( ! ( $logbook = sql_logbook( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
  open_div( '', 'no matching entries' );
  return;
}
$count = count( $logbook );
$limits = handle_list_limits( $list_options, $count );
$list_options['limits'] = & $limits;

open_list( $list_options );
  open_list_row('header');
    open_list_cell( 'nr' );
    open_list_cell( 'id' );
    open_list_cell( 'session' );
    open_list_cell( 'level' );
    open_list_cell( 'login_people_id' );
    open_list_cell( 'login_remote_addr' );
    open_list_cell( 'utc' );
    open_list_cell( 'thread' );
    open_list_cell( 'window' );
    open_list_cell( 'script' );
    open_list_cell( 'parent' );
    open_list_cell( 'flags' );
    open_list_cell( 'tags' );
    open_list_cell( 'links' );
    open_list_cell( 'note');
    // open_list_cell( 'left',"rowspan='2'", 'details' );
    // open_list_cell( 'actions' );

  foreach( $logbook as $l ) {
    if( $l['nr'] < $limits['limit_from'] )
      continue;
    if( $l['nr'] > $limits['limit_to'] )
      break;
    open_list_row();
      $id = $l['logbook_id'];
      open_list_cell( 'nr', inlink( 'logentry', "logbook_id=$id,text={$l['nr']}", 'class=number' ) );
      open_list_cell( 'id', any_link( 'logbook', $id, "text=$id" ), 'class=number' );
      $t = $l['sessions_id'];
      open_list_cell( 'session', inlink( '', "sessions_id=$t,text=$t", 'class=number' ), 'class=number' );
      $t = $l['level'];
      $s = adefault( $log_level_text, $l['level'], 'unknown' );
      open_list_cell( 'level', inlink( '', "level=$t,text=$s" ) );
      open_list_cell( 'login_people_id'
                    , inlink( 'person_view', array( 'class' => 'href', 'text' => $l['login_people_id'], 'people_id' => $l['login_people_id'] ) )
                    , 'class=number'
      );
      open_list_cell( 'login_remote_addr', "{$l['login_remote_ip']}:{$l['login_remote_port']}", 'class=number' );
      open_list_cell( 'utc', $l['utc'], 'class=right' );

      open_list_cell( 'thread', $l['thread'], 'class=number' );
      open_list_cell( 'window', $l['window'] );
      $t = $l['script'];
      open_list_cell( 'script', inlink( '', "script=$t,text=$t" ) );
      open_list_cell( 'parent', $l['parent_thread'].'/'.$l['parent_window'].'/'.$l['parent_script'] );

      $t = '';
      for( $i = 1; isset( $log_flag_text[ $i ] ) ; $i <<= 1 ) {
        if( $l['flags'] & $i )
          $t .= span_view( 'block center', $log_flag_text[ $i ] );
      }
      open_list_cell( 'flags', $t );
      open_list_cell( 'tags', $l['tags'] );
      open_list_cell( 'links', inlinks_view( $l['links'] ), 'class=left' );
      if( strlen( $l['note'] ) > 100 ) {
        $s = substr( $l['note'], 0, 100 ).'...';
      } else {
        $s = $l['note'];
      }
      if( $l['stack'] ) {
        $s .= span_view( 'quads underline bold', '[stack]' );
      }
      $t = inlink( 'logentry', array( 'class' => 'card', 'text' => $s, 'logbook_id' => $l['logbook_id'] ) );
      open_list_cell( 'note', $t );

      // $t = inlink( '!', 'class=drop,text=,action=deleteLogentry,confirm=are you sure?,message='. $l['logbook_id'] );
      // open_list_cell( 'actions', $t );
  }
close_list();

?>
