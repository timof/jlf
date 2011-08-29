<?php

echo html_tag( 'h1', '', 'accountdomains' );

$filters = array();

open_table( 'menu' );
  open_th( 'colspan=2', 'options' );
close_table();

bigskip();

accountdomainslist_view( $filters, '' );

?>
