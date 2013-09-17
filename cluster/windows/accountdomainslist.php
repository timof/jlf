<?php

sql_transaction_boundary('*');
echo html_tag( 'h1', '', 'accountdomains' );

$filters = array();

open_div( 'menubox' );
  open_table('css filters');
    open_caption('', 'Filter');
  close_table();
close_div();

bigskip();

accountdomainslist_view( $filters, '' );

?>
