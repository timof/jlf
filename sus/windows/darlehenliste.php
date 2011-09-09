<?php

echo html_tag( 'h1', '', 'Darlehen' );

init_var( 'options', 'global,pattern=u,sources=http persistent,default=0,set_scopes=window' );

$fields = prepare_filters( array( 'people_id' ) );

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', 'Kreditor:' );
    open_td();
      filter_person( $fields['people_id'] );
  open_tr();
    open_th( 'center,colspan=2', 'Aktionen' );
  open_tr();
    open_td( 'center,colspan=2', inlink( 'darlehen', array( 
      'class' => 'bigbutton', 'text' => 'Neues Darlehen', 'people_id' => $people_id
    ) ) );
close_table();

medskip();

handle_action( array( 'update', 'deleteDarlehen' ) );
switch( $action ) {
  case 'update':
    //nop
    break;

  case 'deleteDarlehen':
    need( $message, 'kein darlehen gewaehlt' );
    sql_delete_darlehen( $message );
    break;
}

darlehenlist_view( $fields['_filters'], '' );

?>
