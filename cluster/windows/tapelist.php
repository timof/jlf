<?php

assert( $angemeldet ) or exit();

echo "<h1>tapes</h1>";
$editable = true;

get_http_var( 'options', 'u', 0, true );
get_http_var( 'orderby', 'w', 'cn', true );
need( in_array( $orderby, array( 'cn', 'type', 'oid', 'location' ) ) );

$keys = array();

open_table('menu');
    open_th('', '', 'options' );
  open_tr();
    open_td();
    echo fc_link( 'tape', 'class=bigbutton,text=new tape,tapes_id=0' );
close_table();

bigskip();


get_http_var('action','w','');
$readonly and $action = '';
switch( $action ) {
  case 'delete':
    need_http_var( 'message','U' );
    sql_delete_tape( $message );
    break;
}

medskip();

tapes_view( $keys, $orderby );

?>
