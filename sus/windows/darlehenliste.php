<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

echo html_tag( 'h1', '', 'Darlehen' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields( array( 'people_id', 'geschaeftsjahr' => "global=1,sources=http self,set_scopes=self,initval=$geschaeftsjahr_thread" ) );

// debug( $fields['geschaeftsjahr'], 'gj' );

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
  open_tr();
    open_th( '', 'Geschaeftsjahr:' );
    open_td( '', filter_geschaeftsjahr( $fields['geschaeftsjahr'] ) );
  open_tr();
    open_th( '', 'Kreditor:' );
    open_td( '', filter_person( $fields['people_id'] ) );
  close_table();
  open_table('css actions');
    open_caption( '', 'Aktionen' );
    open_tr();
      open_td( 'center,colspan=2', inlink( 'darlehen', array( 
        'class' => 'big button', 'text' => 'Neues Darlehen', 'people_id' => $fields['people_id']['value'], 'geschaeftsjahr' => ( $geschaeftsjahr ? $geschaeftsjahr : $geschaeftsjahr_thread )
      ) ) );
  close_table();
close_div();

medskip();

darlehenlist_view( $fields['_filters'] );

?>
