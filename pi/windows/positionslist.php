<?php

echo html_tag( 'h1', '', we('Open positions / Topics for Theses','Offene Stellen / Themen fuer Bachelor/Master/...-Arbeiten' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array( 'groups_id', 'degree_id' ) , '' );

open_table('menu');
  open_tr();
    open_th( 'colspan=2', 'Filter' );
  open_tr();
    open_th( '', we('Group:','Gruppe:') );
    open_td();
      echo filter_group( $f['groups_id'] );
  open_tr();
    open_th( '', we('Degree:','Abschluss:' ) );
    open_td();
      echo filter_degree( $f['degree_id'] );
  if( have_priv( 'positions', 'create' ) ) {
    open_tr();
      open_th( 'center,colspan=1', we('Actions','Aktionen') );
      open_td( 'center,colspan=1', inlink( 'position_edit', 'class=bigbutton,text='.we('New Position / Topic','Neue Stelle / Thema' ) ) );
  }
close_table();

bigskip();


handle_action( array( 'update', 'deletePosition' ) );
switch( $action ) {
  case 'deletePosition':
    need( $message > 0, 'kein Thema ausgewaehlt' );
    sql_delete_positions( $message );
    break;
}

medskip();

positionslist_view( $f['_filters'], '' );

?>
