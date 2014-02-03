<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', 'Darlehen' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields( array( 'people_id', 'geschaeftsjahr' => "global=1,sources=http self,set_scopes=self,initval=$geschaeftsjahr_thread" ) );

// debug( $fields['geschaeftsjahr'], 'gj' );

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', 'Geschaeftsjahr:' );
    open_td( '', filter_geschaeftsjahr( $fields['geschaeftsjahr'] ) );
  open_tr();
    open_th( '', 'Kreditor:' );
    open_td( '', filter_person( $fields['people_id'] ) );
  open_tr();
    open_th( 'center,colspan=2', 'Aktionen' );
  open_tr();
    open_td( 'center,colspan=2', inlink( 'darlehen', array( 
      'class' => 'bigbutton', 'text' => 'Neues Darlehen', 'people_id' => $fields['people_id']['value'], 'geschaeftsjahr' => ( $geschaeftsjahr ? $geschaeftsjahr : $geschaeftsjahr_thread )
    ) ) );
close_table();

medskip();

handle_actions( array( 'deleteDarlehen' ) );
switch( $action ) {
  case 'deleteDarlehen':
    need( $message, 'kein darlehen gewaehlt' );
    sql_delete_darlehen( $message );
    break;
}

darlehenlist_view( $fields['_filters'] );

?>
