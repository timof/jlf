<?php // code/sessions.php

need_priv('*','*');

sql_transaction_boundary('*');

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

echo html_tag( 'h1', '', "sessions:" );

$have_groups = isset( $tables['groups'] );
$have_affiliations = ( isset( $tables['affiliations']['cols']['groups_id'] ) && isset( $tables['affiliations']['cols']['people_id'] ) );

$fields =  array( 'REGEX' => 'a,size=40,auto=1' );
if( function_exists( 'filter_person' ) ) {
  $fields['people_id'] = 'u';
  if( $have_groups && function_exists( 'filter_group' ) ) {
    $fields['groups_id'] = 'u';
  }
}

$f = init_fields( $fields );

handle_action( array( 'update' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
}


open_div('menubox');
  open_table('css filters');
    open_caption( 'center th', filter_reset_button( $f, 'floatright' ) . 'Filter' );
if( isset( $f['groups_id'] ) ) {
    open_tr();
      open_th( '', 'group:' );
      open_td( '', filter_group( $f['groups_id'] ) );
  if( ( $g_id = $f['groups_id']['value'] ) ) {
    open_tr();
      open_th( '', 'person:' );
      open_td( '', filter_person( $f['people_id'], array( 'filters' => "groups_id=$g_id" ) ) );
  }
} else if( isset( $f['people_id'] ) ) {
    open_tr();
      open_th( '', 'person:' );
      open_td( '', filter_person( $f['people_id'] ) );
}
    open_tr();
      open_th( '', 'search:' );
      open_td( '', string_element( $f['REGEX'] ) );
  close_table();
close_div();

$opts = array();
if( $have_affiliations ) {
  $opts['more_joins'] = array( 'affiliations' => 'LEFT affiliations on ( affiliations.people_id = people.people_id )' );
}
$opts['list_options'] = array( 'orderby' => 'sessions_id DESC' );

sessions_view( $f['_filters'], $opts );

?>
