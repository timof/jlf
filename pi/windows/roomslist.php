<?php

echo html_tag( 'h1', '', we('Labs','Labore' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array( 'groups_id', 'degree_id', 'REGEX' => 'size=40,auto=1' ) , '' );

open_div('menu');
  open_table('css hfill');
    open_caption( 'center th', html_span( 'floatright', filter_reset_button( $f ) ) . 'Filter' );
    open_tr();
      open_th( '', we('Group:','Gruppe:') );
      open_td( '', filter_group( $f['groups_id'] ) );
  close_table();

  if( have_priv( 'rooms', 'create' ) ) {
      open_div( 'center th', we('Actions','Aktionen') );
      open_div( 'center', inlink( 'room_edit', 'class=bigbutton,text='.we('Insert New Lab','Neues Labor erfassen' ) ) );
  }
close_div();
bigskip();


roomslist_view( $f['_filters'], '' );

?>
