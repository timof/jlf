<?php // /code/debuglist.php

need_priv( '*', '*' );

sql_transaction_boundary( 'debug', 'persistentvars' );
echo html_tag( 'h1', '', 'debug entries' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = array(
  'fscript' => 'type=w64,auto=1,sql_name=script'
, 'facility' => 'type=a256,size=40,auto=1,relation=~'
, 'object' => 'type=a256,size=40,auto=1,relation=~'
);

$fields = init_fields( $fields, 'tables=debug' );

open_div('menubox');
  open_table( 'css filters' );
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( 'right', 'script:' );
      open_td();
        echo filter_script( $fields['fscript'], 'filters=tables=debug' );
    open_tr();
      open_th( 'right', 'facility:' );
      open_td( '', filter_reset_button( $fields['facility'] ) . ' / '. string_element( $fields['facility'] ) .' /  ' );
    open_tr();
      open_th( 'right', 'note:' );
      open_td( '', filter_reset_button( $fields['object'] ) . ' / '. string_element( $fields['object'] ) .' /  ' );
  close_table();
close_div();

if( ! $fields['fscript']['value'] ) {
  open_div( '', '(please select script)' );
  return;
}

handle_actions( 'saveDebugInstructions' );

switch( $action ) {
  case 'saveDebugInstructions':
    
    

  break;
}


  


$filters = $fields['_filters'];

$list_options = handle_list_options( true, 'log', array( 
  'nr' => 't'
, 'id' => 't,s=debug_id DESC'
, 'script' => 't,s'
, 'utc' => 't,s'
, 'facility' => 't,s'
, 'object' => 't,s'
, 'comment' => 't,s'
, 'value' => 't,s'
, 'stack' => 't,s'
) );

$entries = sql_debug( $filters, array( 'orderby' => $list_options['orderby_sql'] ) );

if( ! $entries ) {
  open_div( '', 'no matching entries' );
  return;
}
$count = count( $entries );
$limits = handle_list_limits( $list_options, $count );
$list_options['limits'] = & $limits;

open_list( $list_options );
  open_list_row('header');
    open_list_cell( 'nr' );
    open_list_cell( 'id' );
    open_list_cell( 'script' );
    open_list_cell( 'utc' );
    open_list_cell( 'facility' );
    open_list_cell( 'object' );
    open_list_cell( 'comment' );
    open_list_cell( 'value' );
    open_list_cell( 'stack' );

  foreach( $entries as $d ) {
    if( $d['nr'] < $limits['limit_from'] )
      continue;
    if( $d['nr'] > $limits['limit_to'] )
      break;
    open_list_row();
      $id = $d['debug_id'];
      open_list_cell( 'nr', inlink( 'debugentry', "debug_id=$id,text={$d['nr']}", 'class=number' ) );
      open_list_cell( 'id', any_link( 'debug', $id, "text=$id" ), 'class=number' );
      $t = $d['script'];
      open_list_cell( 'script', inlink( '', "fscript=$t,text=$t" ) );
      open_list_cell( 'utc', $d['utc'] );
      $t = $d['facility'];
      open_list_cell( 'facility', inlink( '', array( 'facility' => $t, 'text' => $t ) ) );
      $t = $d['object'];
      open_list_cell( 'object', inlink( '', array( 'object' => $t, 'text' => $t ) ) );
      open_list_cell( 'comment', $d['comment'] );
      open_list_cell( 'value', substr( $d['value'], 0, 20 ) );
      $t = json_decode( $d['stack'], 1 );
      if( ! $t ) {
        $t = '-';
      } else if( isarray( $t ) ) {
        $t = count( $t );
      } else {
        $t = '?';
      }
      open_list_cell( 'stack', $t );
  }
close_list();

?>
