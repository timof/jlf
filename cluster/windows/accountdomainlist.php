<?php

assert( $angemeldet ) or exit();

echo "<h1>accountdomains</h1>";

$editable = true;

get_http_var( 'options', 'u', 0, true );

// get_http_var( 'orderby', 'w', 'uidnumber', true );
// need( in_array( $orderby, array( 'cn', 'uid', 'uidnumber' ) ) );

$keys = array();

open_table('menu');
    open_th('', "colspan='2'", 'options' );
  open_tr();
    open_td('', '', 'accountdomain:' );
    open_td();
      open_select( 'accountdomain', 'autoreload' );
        echo options_accountdomains( $accountdomain, ' (all) ' );
      close_select();
close_table();

bigskip();


medskip();

accountdomains_view( $keys, $orderby );

?>
