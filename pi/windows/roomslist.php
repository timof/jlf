<?php

echo html_tag( 'h1', '', we('Labs','Labore' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array( 'groups_id', 'REGEX' => 'size=40,auto=1' ) , '' );

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', we('Group:','Gruppe:') );
      open_td( '', filter_group( $f['groups_id'] ) );
  close_table();

  if( have_priv( 'rooms', 'create' ) ) {
    open_table('css actions' );
      open_caption( '', we('Actions','Aktionen') );
      open_tr( '', inlink( 'room_edit', 'class=bigbutton,text='.we('Insert New Lab','Neues Labor erfassen' ) ) );
    close_table();
  }
close_div();

roomslist_view( $f['_filters'], '' );

?>
