<?php // /pi/windows/eventslist.php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Events','Veranstaltungen' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
    'groups_id'
  , 'REGEX' => 'size=40,auto=1'
  )
, ''
);

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', we('Group:','Gruppe:') );
      open_td( '', filter_group( $f['groups_id'] ) );
    open_tr();
      open_th( '', we('Search:','Suche:') );
      open_td( 'oneline', '/'.string_element( $f['REGEX'] ).'/ ' . filter_reset_button( $f['REGEX'], '/floatright//' ) );
  close_table();

  if( have_priv( 'events', 'create' ) ) {
    open_table('css actions' );
      open_caption( '', we('Actions','Aktionen') );
      open_tr( '', inlink( 'event_edit', 'class=big button,text='.we('New event','Neue Veranstaltung' ) ) );
    close_table();
  }
close_div();

eventslist_view( $f['_filters'], '' );

?>
