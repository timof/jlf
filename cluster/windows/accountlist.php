<?php

assert( $logged_in ) or exit();

echo "<h1>accounts</h1>";

$editable = true;

get_http_var( 'options', 'u', 0, true );
get_http_var( 'orderby', 'w', 'uidnumber', true );
need( in_array( $orderby, array( 'cn', 'uid', 'uidnumber', 'fqhostname' ) ) );

$keys = array();
get_http_var( 'accountdomain', 'w', '', true );
if( $accountdomain )
  $keys['accountdomain'] = $accountdomain;

get_http_var( 'hosts_id', 'u', '0', true );
if( $hosts_id )
  $keys['hosts_id'] = $hosts_id;

open_table('menu');
    open_th('', "colspan='2'", 'options' );
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


medskip();

accounts_view( $keys, $orderby );

?>
