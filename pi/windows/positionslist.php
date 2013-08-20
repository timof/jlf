<?php

echo html_tag( 'h1', '', we('Open positions / Topics for Theses','Offene Stellen / Themen fuer Bachelor/Master/...-Arbeiten' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array( 'groups_id', 'programme_id', 'REGEX' => 'size=40,auto=1' ) , '' );

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', we('Group:','Gruppe:') );
      open_td( '', filter_group( $f['groups_id'] ) );
    open_tr();
      open_th( '', we('Programme/Degree:','Studiengang/Abschluss:' ) );
      open_td( '', filter_programme( $f['programme_id'] ) );
    open_tr();
      open_th( '', we('search:','Suche:') );
      open_td( '', '/'.string_element( $f['REGEX'] ).'/ ' . filter_reset_button( $f['REGEX'] ) );
  close_table();
  if( have_priv( 'positions', 'create' ) ) {
    open_table('css actions' );
      open_caption( '', we('Actions','Aktionen') );
      open_tr( '', inlink( 'position_edit', 'class=bigbutton,text='.we('New Position / Topic','Neue Stelle / Thema' ) ) );
    close_table();
  }
close_div();

positionslist_view( $f['_filters'], '' );

?>
