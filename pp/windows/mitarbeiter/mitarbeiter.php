<?php

echo html_tag( 'h1', '', we('People','Personen am Institut') );

sql_transaction_boundary('*');

$f = init_fields( array( 'groups_id' , 'SEARCH' => 'size=40,auto=1,relation=%=' ), '' );

open_div('menubox');
  open_table( 'css');
    open_caption( '', filter_reset_button( $f ) . 'Filter' );
    open_tr();
      open_th( '', we('Group:','Bereich:') );
      open_td( '', filter_group( $f['groups_id'] ) );
    open_tr();
      open_th( '', we('Search:','Suche:') );
      open_td( '', string_element( $f['SEARCH'] ) );
  close_table();
close_div();

$filters = $f['_filters'];
if( adefault( $filters, 'SEARCH %=' ) ) {
  unset( $filters['SEARCH %='] );
  $filters['SEARCH %='] = "%{$f['_filters']['SEARCH %=']}%";
}
peoplelist_view( $filters, 'search_filter=P2_SEARCH,insert=1,select=1' );

?>
