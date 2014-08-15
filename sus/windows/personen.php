<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

echo html_tag( 'h1', '', 'Personen' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields( array( 'jperson', 'REGEX' => 'type=h,size=20,auto=1,relation=~' ) );

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
  open_tr();
    open_th( '', 'Art:' );
    open_td( '', filter_jperson( $fields['jperson'] ) );
  open_tr();
    open_th( '', we('search:','suche:') );
    open_td( '', string_element( $fields['REGEX'] ) );
  close_table();
  if( have_priv( 'people', 'create' ) ) {
    open_table('css actions');
      open_caption( '', 'Aktionen' );
      open_tr();
        open_td( 'center', inlink( 'person', 'class=big button,text=Neue Person' ) );
    close_table();
  }
close_div();

bigskip();

peoplelist_view( $fields['_filters'] );

?>
