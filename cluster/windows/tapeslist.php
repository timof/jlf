<?php

sql_transaction_boundary('*');
echo html_tag( 'h1', '', 'tapes' );

$fields = init_fields( array( 'type_tape' => 'allow_null=0,default=0', 'location' => 'a64', 'REGEX' => 'size=20,auto=1' ) );

handle_actions( array( 'deleteTape' ) );
if( $action ) switch( $action ) {
  case 'deleteTape':
    need( $message > 0 );
    sql_delete_tapes( $message );
    break;
}

open_div( 'menubox' );
  open_table( 'css filters' );
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', 'type:' );
      open_td( '', filter_type_tape( $fields['type_tape'] ) );
    open_tr();
      open_th( '', 'location:' );
      open_td( '', filter_location( $fields['location'], 'filters=tapes' ) );
    open_tr();
      open_th( '', 'search:' );
      open_td( '', string_element( $fields['REGEX'] ) );
  close_table();
  open_table('css actions');
    open_caption( '', 'actions' );
    open_tr( '', inlink( 'tape', 'class=bigbutton,text=new tape' ) );
  close_table();
close_div();

bigskip();

tapeslist_view( $fields['_filters'], '' );

?>
