<?php

need_priv( '*','*' );


echo html_tag( 'h1', '', 'profile' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = array(
  'script' => 'auto=1'
, 'REGEX_sql' => 'h,size=40,auto=1'
, 'REGEX_stack' => 'h,size=40,auto=1'
);

$fields = init_fields( $fields, 'tables=profile' );

handle_action( array( 'update' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
}

open_div('menubox');
  open_table( 'css filters' );
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( 'right', 'script:' );
      open_td();
        echo filter_script( $fields['script'] );
    open_tr();
      open_th( 'right', 'stack:' );
      open_td( '', filter_reset_button( $fields['REGEX_stack'] ) . ' / '. string_element( $fields['REGEX_stack'] ) .' /  ' );
    open_tr();
      open_th( 'right', 'sql:' );
      open_td( '', filter_reset_button( $fields['REGEX_sql'] ) . ' / '. string_element( $fields['REGEX_sql'] ) .' /  ' );
  close_table();
close_div();


$list_options = handle_list_options( true, 'profile', array(
  'nr' => 't'
, 'id' => 't,s'
, 'utc' => 't,s'
, 'script' => 't,s'
, 'sql' => 't,s'
, 'stack' => 't'
, 'rows_returned' => 't,s'
, 'wallclock_seconds' => 't,s'
) );
  
$rows = sql_query( 'profile', array( 'filters' => $fields['_filters'], 'orderby' => $list_options['orderby_sql'] ) );
if( ! $rows ) {
  open_div( '', 'no matching entries' );
  return;
}
$count = count( $rows );
$limits = handle_list_limits( $list_options, $count );
$list_options['limits'] = & $limits;

open_list( $list_options );
  open_list_row('header');
    open_list_cell( 'nr' );
    open_list_cell( 'id' );
    open_list_cell( 'utc' );
    open_list_cell( 'script' );
    open_list_cell( 'wallclock_seconds', 'secs' );
    open_list_cell( 'rows_returned', 'rows' );
    open_list_cell( 'sql' );
    open_list_cell( 'stack' );
  foreach( $rows as $r ) {
    if( $r['nr'] < $limits['limit_from'] )
      continue;
    if( $r['nr'] > $limits['limit_to'] )
      break;
    open_list_row();
      $id = $r['profile_id'];
      open_list_cell( 'nr', inlink( 'profileentry', "profile_id=$id,text={$r['nr']}", 'class=number' ) );
      open_list_cell( 'id', inlink( 'profileentry', "profile_id=$id,text=$id", 'class=number' ) );
      open_list_cell( 'utc', $r['utc'] );
      open_list_cell( 'script', $r['script'] );
      open_list_cell( 'wallclock_seconds', sprintf( '%8.3lf', $r['wallclock_seconds'] ), 'number' );
      open_list_cell( 'rows_returned', $r['rows_returned'], 'number' );
      open_list_cell( 'sql', substr( $r['sql'], 0, 300 ) );
      $stack = json_decode( $r['stack'], 1 );
      $t = '';
      foreach( $stack as $s ) {
        $t .= span_view( 'qquadr', $s['function'] ). ' ';
      }
      open_list_cell( 'stack', $t );
  }
close_list();

?>
