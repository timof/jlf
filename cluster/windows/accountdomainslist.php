<?php

echo "<h1>accountdomains</h1>";

$filters = array();

open_table( 'menu' );
  open_th( 'colspan=2', 'options' );
close_table();

bigskip();

accountdomainslist_view( $filters, '' );

?>
