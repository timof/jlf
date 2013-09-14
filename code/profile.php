<?php

sql_transaction_boundary('profile');

need_priv( '*','*' );

echo html_tag( 'h1', '', 'profile' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields(
  array(
    'script' => 'auto=1'
  , 'sql' => 'h,size=40,auto=1,relation=~'
  , 'stack' => 'h,size=40,auto=1,relation=~'
  )
, 'tables=profile'
);

open_div('menubox');
  open_table( 'css filters' );
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( 'right', 'script:' );
      open_td();
        echo filter_script( $fields['script'], 'filters=tables=profile' );
    open_tr();
      open_th( 'right', 'stack:' );
      open_td( '', filter_reset_button( $fields['stack'] ) . ' / '. string_element( $fields['stack'] ) .' /  ' );
    open_tr();
      open_th( 'right', 'sql:' );
      open_td( '', filter_reset_button( $fields['sql'] ) . ' / '. string_element( $fields['sql'] ) .' /  ' );
  close_table();
close_div();


$list_options = handle_list_options( true, 'profile', array(
  'nr' => 't'
, 'id' => 't,s=profile_id'
, 'utc' => 't,s'
, 'script' => 't,s'
, 'sql' => 't,s'
, 'stack' => 't'
, 'rows_returned' => 't,s'
, 'wallclock_seconds' => 't,s'
) );

if( ! $fields['script']['value'] ) {
  open_div( '', 'please select a script' );
  return;
}

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
    $s = $r['wallclock_seconds'];
    if( $s >= 1 ) {
      $class = 'redd;bold';
    } else if( $s >= 0.1 ) {
      $class= 'dgreen;bold';
    } else {
      $class = '';
    }
    open_list_row( "class=td:$class" );
      $id = $r['profile_id'];
      open_list_cell( 'nr', inlink( 'profileentry', "profile_id=$id,text={$r['nr']}" ), 'class=number' );
      open_list_cell( 'id', any_link( 'profile', $id ), 'class=number' );
      open_list_cell( 'utc', $r['utc'] );
      open_list_cell( 'script', $r['script'] );
      open_list_cell( 'wallclock_seconds', sprintf( '%8.3lf', $s ), 'number' );
      open_list_cell( 'rows_returned', $r['rows_returned'], 'number' );
      open_list_cell( 'sql', substr( $r['sql'], 0, 300 ) );
      $stack = json_decode( $r['stack'], 1 );
      $t = '';
      if( isarray( $stack ) ) {
        foreach( $stack as $s ) {
          $t .= span_view( 'qquadr', $s['function'] ). ' ';
        }
      } else {
        $t = $stack;
      }
      open_list_cell( 'stack', $t );
  }
close_list();

?>
