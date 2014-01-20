<?php

echo html_tag( 'h1', '', we('People','Mitarbeiter') );

sql_transaction_boundary('*');

$f = init_fields( array( 'groups_id' , 'REGEX' => 'size=40,auto=1' ), '' );

open_div('menubox');
  open_table( 'css');
    open_caption( '', filter_reset_button( $f ) . 'Filter' );
    open_tr();
      open_th( '', we('Group:','Bereich:') );
      open_td( '', filter_group( $f['groups_id'] ) );
    open_tr();
      open_th( '', we('Search:','Suche:') );
      open_td( '', ' / '.string_element( $f['REGEX'] ).' / ' );
  close_table();
close_div();

peoplelist_view( $f['_filters'], 'regex_filter=1,insert=1,select=1' );

?>
