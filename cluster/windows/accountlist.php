<?php

echo "<h1>accounts</h1>";

$filters = handle_filters( array( 'accountdomain', 'hosts_id' ) );

open_table('menu');
    open_th('', "colspan='2'", 'filters' );
  open_tr();
    open_td('', '', 'accountdomain:' );
    open_td();
    open_select( 'accountdomain', '', html_options_accountdomains( $accountdomain, ' (any) ' ), 'reload' );
  open_tr();
    open_td('', '', 'host:' );
    open_td();
    open_select( 'hosts_id', '', html_options_hosts( $hosts_id, false, ' (any) ' ), 'reload' );
close_table();

bigskip();

accountslist_view( $filters, '' );

?>
