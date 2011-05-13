<?php

assert( $angemeldet ) or exit();

echo "<h1>hosts</h1>";

$editable = true;

get_http_var( 'options', 'u', 0, true );
get_http_var( 'orderby', 'w', 'ip4', true );
need( in_array( $orderby, array( 'ip4', 'fqhostname', 'oid', 'location', 'invlabel' ) ) );

$keys = array();
get_http_var( 'accountdomain', 'w', '', true );
if( $accountdomain )
  $keys['accountdomain'] = $accountdomain;

get_http_var( 'location', '/[a-zA-Z0-9.]*/', '', true );
if( $location )
  $keys['location'] = $location;

open_table('menu');
    open_th('', "colspan='2'", 'options' );
  open_tr();
    open_td( '', "colspan='2'", fc_link( 'host', 'class=bigbutton,text=new host,hosts_id=0' ) );
  open_tr();
    open_td('', '', 'accountdomain:' );
    open_td();
    open_select( 'accountdomain', 'autoreload' );
      echo options_accountdomains( $accountdomain, ' (all) ' );
    close_select();
  open_tr();
    open_td('', '', 'location:' );
    open_td();
    open_select( 'location', 'autoreload' );
      echo options_locations( $location, ' (all) ' );
    close_select();
close_table();

bigskip();


// ggf. Aktionen durchführen (z.B. Gruppe löschen...)
get_http_var('action','w','');
$readonly and $action = '';
switch( $action ) {
  case 'delete':
    need_http_var( 'message','U' );
    sql_delete_host( $message );
    break;
}

medskip();

hosts_view( $keys, $orderby );

?>
