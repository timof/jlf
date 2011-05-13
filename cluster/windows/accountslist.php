<?php

echo "<h1>accounts</h1>";

$filters = handle_filters( array( 'accountdomain', 'hosts_id' ) );

open_table('menu');
    open_th('', "colspan='2'", 'filters' );
  open_tr();
    open_td('', '', 'accountdomain:' );
    open_td();
      filter_accountdomain();
  open_tr();
    open_td('', '', 'host:' );
    open_td();
      filter_host();
close_table();

bigskip();

accountslist_view( $filters, '' );

?>
