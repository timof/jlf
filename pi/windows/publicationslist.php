<?php // /pi/windows/publicationslist.php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Publications','Publikationen' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
    'groups_id'
  , 'REGEX' => 'size=40,auto=1'
  , 'year' => 'u4,min=2012,max=2999,allow_null=0,initval='.$current_year
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
      open_th( '', we('Year:','Jahr:') );
      open_td( '', filter_int( $f['year'] ) );
    open_tr();
      open_th( '', we('Search:','Suche:') );
      open_td( 'oneline', '/'.string_element( $f['REGEX'] ).'/ ' . filter_reset_button( $f['REGEX'], '/floatright//' ) );
  close_table();

  if( have_priv( 'publications', 'create' ) ) {
    open_table('css actions' );
      open_caption( '', we('Actions','Aktionen') );
      open_tr( '', inlink( 'publication_edit', 'class=bigbutton,text='.we('New Publication','Neue Publikation' ) ) );
    close_table();
  }
close_div();

publicationslist_view( $f['_filters'], '' );

?>
