<?php

echo html_tag( 'h1', '', we('Groups','Gruppen') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

open_table('menu');
  if( have_priv( 'groups', 'create' ) ) {
    open_tr();
      open_th( 'center,colspan=1', we('Actions','Aktionen') );
      open_td( 'center,colspan=1', inlink( 'group_edit', 'class=bigbutton,text='.we('Create new Group','Neue Gruppe anlegen') ) );
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

groupslist_view();

?>
