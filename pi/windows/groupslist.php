<?php

echo html_tag( 'h1', '', we('Groups','Gruppen') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
  'show_extern' => array( 'type' => 'b', 'text' => we('show extern groups','externe Gruppen zeigen'), 'auto' => 1 )
) );

open_table('menu');
  if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    open_tr();
      open_th( 'center,colspan=1', we('Filters','Filter') );
      open_td( 'center,colspan=1', inlink( 'group_edit', 'class=bigbutton,text='.we('Create new Group','Neue Gruppe anlegen') ) );
  }
  if( have_priv( 'groups', 'create' ) ) {
    open_tr();
      open_th( 'center,colspan=1', we('Actions','Aktionen') );
      open_td( 'center,colspan=1', checkbox_element( $f['show_extern'] ) );
  }
close_table();

bigskip();


handle_action( array( 'update', 'deleteGroup' ) );
switch( $action ) {
  case 'deleteGroup':
    need( $message > 0, 'keine Gruppe ausgewaehlt' );
    sql_delete_groups( $message );
    break;
}

medskip();

$filters = array();
if( ! $f['show_extern']['value'] ) {
  $filters[] = array( 'INSTITUTE' );
}

groupslist_view( $filters );

?>
