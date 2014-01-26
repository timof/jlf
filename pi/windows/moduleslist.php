<?php // /pi/windows/moduleslist.php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Modules','Module' ) );

$f = init_fields( array(
  'programme_flags'
, 'year_valid_from' => 'type=U,min=2000,max=2099,initval=0,allow_null=0'
, 'SEARCH' => 'size=40,auto=1,relation=~='
) , '' );

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', we('programme:','Studiengang:') );
      open_td( '', filter_programme( $f['programme_flags'] ) );
    open_tr();
      open_th( '', we('valid from:', "g{$uUML}ltig ab:") );
      open_td( '', filter_int( $f['year_valid_from'] ) );
    open_tr();
      open_th( '', we('search:','suche:') );
      open_td( '', filter_reset_button( $f['SEARCH'] ) . ' / '.string_element( $f['SEARCH'] ).' / ' );
  close_table();

  if( have_priv( 'modules', 'create' ) ) {
    open_table('css actions' );
      open_caption( '', we('Actions','Aktionen') );
      open_tr( '', inlink( 'module_edit', 'class=big button,text='.we('Insert New Module','Neues Modul erfassen' ) ) );
    close_table();
  }
close_div();

moduleslist_view( $f['_filters'], '' );

?>
