<?php

echo "<h1>Personen</h1>";

get_http_var( 'options', 'u', 0, true );

// $orderby_sql = handle_orderby( array( 'cn', 'gn', 'sn', 'jperson', 'phone' => 'telephonenumber', 'mail', 'uid' ) );

$filters = handle_filters( array( 'jperson' ) );

open_table('menu');
  open_tr();
    open_th( '', "colspan='2'", 'Filter' );
  open_tr();
    open_th( '', '', 'Art:' );
    open_td();
      filter_jperson();
  open_tr();
    open_th( 'center', "colspan='1'", 'Optionen' );
    open_td( 'center', "colspan='1'", inlink( 'person', 'class=bigbutton,text=Neue Person' ) );
close_table();

bigskip();


get_http_var('action','w','');
$readonly and $action = '';
switch( $action ) {
  case 'delete':
    need_http_var( 'message','U' );
    sql_person_delete( $message );
    break;
}

medskip();

people_view( $filters, '' );

?>
