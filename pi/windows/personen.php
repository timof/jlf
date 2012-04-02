<?php

echo html_tag( 'h1', '', we('People','Personen') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array( 'groups_id' ) , '' );

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', we('Group:','Gruppe:') );
    open_td();
      echo filter_group( $f['groups_id'] );
  if( have_priv( 'person', 'create' ) ) {
    open_tr();
      open_th( 'center,colspan=2', we('Actions','Aktionen') );
    open_tr();
      open_td( 'center,colspan=2', inlink( 'person', 'class=bigbutton,text='.we('New Person','Neue Person') ) );
  }
close_table();

bigskip();


handle_action( array( 'update', 'deletePerson' ) );
switch( $action ) {
  case 'deletePerson':
    need( $message > 0, 'keine person ausgewaehlt' );
    need_priv( 'person', 'delete', $message );
    sql_delete_people( $message );
    break;
}

medskip();

peoplelist_view( $f['_filters'], '' );

?>
