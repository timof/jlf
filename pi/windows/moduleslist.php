<?php // /pi/windows/moduleslist.php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Modules','Module' ) );

$f = init_fields( array( 'programme_flags', 'REGEX' => 'size=40,auto=1' ) , '' );

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', we('programme:','Studiengang:') );
      open_td( '', filter_programme( $f['programme_flags'] ) );
    open_tr();
      open_th( '', we('search:','suche:') );
      open_td( '', filter_reset_button( $f['REGEX'] ) . ' / '.string_element( $f['REGEX'] ).' / ' );
  close_table();

  if( have_priv( 'rooms', 'create' ) ) {
    open_table('css actions' );
      open_caption( '', we('Actions','Aktionen') );
      open_tr( '', inlink( 'module_edit', 'class=big button,text='.we('Insert New Module','Neues Modul erfassen' ) ) );
    close_table();
  }
close_div();

moduleslist_view( $f['_filters'], '' );

?>