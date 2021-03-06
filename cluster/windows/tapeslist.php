<?php

echo html_tag( 'h1', '', 'tapes' );

$fields = init_fields( array( 'type_tape', 'locations_id' ) );

handle_action( array( 'update', 'deleteTape' ) );
switch( $action ) {
  case 'deleteTape':
    need( $message > 0 );
    sql_delete_tapes( $message );
    break;
}

open_table( 'menu' );
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_th( '', 'type:' );
    open_td();
      filter_type_tape( $fields['type_tape'] );
  open_tr();
    open_th( '', 'location:' );
    open_td();
      filter_location( $fields['locations_id'] );
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'tape', 'class=bigbutton,text=new tape' ) );
close_table();

bigskip();

tapeslist_view( $fields['_filters'], '' );

?>
