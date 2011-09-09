<?php

echo html_tag( 'h1', '', 'accounts' );

$fields = prepare_filters( 'accountdomain,hosts_id' );
$filters = $fields['_filters'];

open_table( 'menu' );
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'accountdomain:' );
    open_td();
      filter_accountdomain( $fields['accountdomain'] );
  open_tr();
    open_td( '', 'host:' );
    open_td();
      filter_host( $fields['hosts_id'] );
close_table();

bigskip();

accountslist_view( $filters, '' );

?>
