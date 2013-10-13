<?php

sql_transaction_boundary('*');

$f = init_fields( array(
  'groups_id'
, 'REGEX' => 'size=40,auto=1'
, 'year' => 'u4'
), '' );

$f['year']['choices'] = sql_query( 'publications', 'key_col=year,val_col=year,orderby=year DESC' );

open_div('menubox');
  open_table('css menubox');
    open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', we('Group:','Bereich:') );
      open_td( '', filter_group( $f['groups_id'] ) );
    open_tr();
      open_th( '', we('Year:','Jahr:') );
      open_td( '', filter_year( $f['year'] ) );
    open_tr();
      open_th( '', we('Search:','Suche:') );
      open_td( '', ' / '.string_element( $f['REGEX'] ).' / ' );
  close_table();
close_div();

publicationslist_view( $f['_filters'], 'allow_download=1' );

?>
