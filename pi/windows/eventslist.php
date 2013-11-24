<?php // /pi/windows/eventslist.php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Events','Veranstaltungen' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
    'groups_id'
  , 'year' => "global=1,type=U4,min=2012,max=$current_year,initval=$current_year,allow_null=0"
  , 'REGEX' => 'size=40,auto=1'
  , 'flag_highlight' => 'type=B,auto=1,default=2'
  , 'flag_detailview' => 'type=B,auto=1,default=2'
  , 'flag_publish' => 'type=B,auto=1,default=2'
  )
, ''
);

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', we('Year:','Jahr:') );
      open_td( '', filter_year( $f['year'] ) );
    open_tr();
      open_th( '', we('Group:','Gruppe:') );
      open_td( '', filter_group( $f['groups_id'] ) );
    open_tr();
      open_th( '', we('Attributes:','Attribute:') );
      open_td();
        open_div( '', radiolist_element( $f['flag_publish'], 'choices='.we(':not published:published:both',":nicht ver{$oUML}ffentlicht:ver{$oUML}ffentlicht:alle" ) ) );
        open_div( '', radiolist_element( $f['flag_highlight'], 'choices='.we(':not in ticker:in ticker:both',":keine Tickermeldung:Tickermeldung:alle" ) ) );
        open_div( '', radiolist_element( $f['flag_detailview'], 'choices='.we(':no detail view:detailview:both',":keine Detailansicht:Detailansicht:alle" ) ) );
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
