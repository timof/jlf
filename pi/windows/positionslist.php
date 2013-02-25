<?php

echo html_tag( 'h1', '', we('Open positions / Topics for Theses','Offene Stellen / Themen fuer Bachelor/Master/...-Arbeiten' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array( 'groups_id', 'degree_id', 'REGEX' => 'size=40,auto=1' ) , '' );

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', html_span( 'floatright', filter_reset_button( $f ) ) . 'Filter' );
  open_tr();
    open_th( '', we('Group:','Gruppe:') );
    open_td( '', filter_group( $f['groups_id'] ) );
  open_tr();
    open_th( '', we('Degree:','Abschluss:' ) );
    open_td( '', filter_degree( $f['degree_id'] ) );
  open_tr();
    open_th( '', we('search:','Suche:') );
    open_td( '', '/'.string_element( $f['REGEX'] ).'/ ' . filter_reset_button( $f['REGEX'] ) );
  if( have_priv( 'positions', 'create' ) ) {
    open_tr();
      open_th( 'center,colspan=1', we('Actions','Aktionen') );
      open_td( 'center,colspan=1', inlink( 'position_edit', 'class=bigbutton,text='.we('New Position / Topic','Neue Stelle / Thema' ) ) );
  }
close_table();

bigskip();


// handle_action( array( 'update', 'deletePosition' ) );
// switch( $action ) {
//   case 'deletePosition':
//     need( $message > 0, 'kein Thema ausgewaehlt' );
//     sql_delete_positions( $message );
//     break;
// }
// 

positionslist_view( $f['_filters'], '' );

?>
