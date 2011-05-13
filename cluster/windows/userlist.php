<?php

assert( $angemeldet ) or exit();

echo "<h1>users</h1>";

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
    open_select( 'accountdomain', 'autoreload' );
      echo options_accountdomains( $accountdomain, ' (any) ' );
    close_select();
  open_tr();
    open_td('', '', 'host:' );
    open_td();
    open_select( 'hosts_id', 'autoreload' );
      echo options_hosts( $hosts_id, false, ' (any) ' );
    close_select();
close_table();

bigskip();


medskip();

users_view( $keys, $orderby );

?>
