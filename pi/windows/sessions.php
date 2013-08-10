<?php // pi/sessions.php

// different from global sessions.php: here we have groups and can filter on group!

need_priv('*','*');

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

echo html_tag( 'h1', '', "sessions:" );


$fields = init_fields( array(
  'groups_id' => 'u'
, 'people_id' => 'u'
, 'REGEX' => 'a,size=40,auto=1'
) );

$fields = filters_person_prepare( true, array( 'auto_select_unique' => 1, 'merge' => $fields ) );

handle_action( array( 'update' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
  case 'deleteLogentry':
    menatwork();
}



open_div('menubox');
  open_table('css filters');
    open_caption( 'center th', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', 'group:' );
      open_td( '', filter_group( $fields['groups_id'] ) );
if( ( $g_id = $fields['groups_id']['value'] ) ) {
    open_tr();
      open_th( '', 'person:' );
      open_td( '', filter_person( $fields['people_id'], array( 'filters' => "groups_id=$g_id" ) ) );
}
    open_tr();
      open_th( '', 'search:' );
      open_td( '', string_element( $fields['REGEX'] ) );
  close_table();
close_div();


$list_options = handle_list_options( true, 'sessions', array(
  'id' => 's,t'
, 'nr' => 't'
, 'ctime' => 's,t'
, 'people_cn' => 's,t,h=user'
, 'login_remote_ip' => 's,t,h=IP'
, 'logentries_count' => 't,h=log entries'
) );
$sessions = sql_sessions( $fields['_filters'], array(
  'orderby' => $list_options['orderby_sql']
, 'more_joins' => array( 'affiliations' => 'LEFT affiliations on ( affiliations.people_id = people.people_id )' )
) );

$count = count( $sessions );
$limits = handle_list_limits( $list_options, $count );
$list_options['limits'] = & $limits;
open_list( $list_options );
  open_list_row('header');
    open_list_cell('nr');
    open_list_cell('id');
    open_list_cell('ctime');
    open_list_cell('people_cn');
    open_list_cell('login_remote_ip');
    open_list_cell('logentries_count');
  foreach( $sessions as $s ) {
    $id = $s['sessions_id'];
    open_list_row();
      open_list_cell( 'nr', $s['nr'], 'class=number' );
      open_list_cell( 'id', any_link( 'sessions', $id, "text=$id" ), 'class=number' );
      open_list_cell( 'ctime', $s['ctime'] );
      if( ( $p_id = $s['login_people_id'] ) ) {
        $t = any_link( 'people', $s['login_people_id'], array( 'text' => $s['people_cn'] ) );
      } else {
        $t = '(public)';
      }
      open_list_cell( 'people_cn', $t );
      open_list_cell( 'login_remote_ip', $s['login_remote_ip'] );
      open_list_cell( 'logentries_count', inlink('logbook', array( 'sessions_id' => $id, 'text' => $s['logentries_count'] ) ) );
    }

close_list();

?>
