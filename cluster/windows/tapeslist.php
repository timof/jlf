<?php

echo "<h1>tapes</h1>";

$filters = handle_filters( array( 'type_tape', 'locations_id' ) );

handle_action( array( 'update', 'deleteTape' ) );
switch( $action ) {
  case 'deleteTape':
    need( $message > 0 );
    sql_delete_tapes( $message );
    break;
}

open_table('menu');
  open_tr();
    open_th('', "colspan='2'", 'filters' );
  open_tr();
    open_th( '', 'type:' );
    open_td();
      open_select( 'type_tape', '', html_options_type_tape( $type_tape, ' (any) ' ), 'reload' );
  open_tr();
    open_th( '', 'location:' );
    open_td();
      open_select( 'locations_id', '', html_options_locations( $locations_id, ' (any) ' ), 'reload' );
  open_tr();
    open_th('', "colspan='2'", 'actions' );
  open_tr();
    open_td( '', "colspan='2'", inlink( 'tape', 'class=bigbutton,text=new tape,action=init' ) );
close_table();

bigskip();

tapes_view( $filters, '' );

?>
