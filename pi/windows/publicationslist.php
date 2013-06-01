<?php

echo html_tag( 'h1', '', we('Publications','Publikationen' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array( 'groups_id', 'REGEX' => 'size=40,auto=1' ) , '' );

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', html_span( 'floatright', filter_reset_button( $f ) ) . 'Filter' );
  open_tr();
    open_th( '', we('Group:','Gruppe:') );
    open_td( '', filter_group( $f['groups_id'] ) );
  open_tr();
    open_th( '', we('search:','Suche:') );
    open_td( '', '/'.string_element( $f['REGEX'] ).'/ ' . filter_reset_button( $f['REGEX'] ) );
  if( have_priv( 'positions', 'create' ) ) {
    open_tr();
      open_th( 'center,colspan=1', we('Actions','Aktionen') );
      open_td( 'center,colspan=1', inlink( 'position_edit', 'class=bigbutton,text='.we('New Publication','Neue Publikation' ) ) );
  }
close_table();

bigskip();

publicationslist_view( $f['_filters'], '' );

?>
