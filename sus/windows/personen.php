<?php

echo html_tag( 'h1', '', 'Personen' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields( array( 'jperson' ) );

open_table('menu');
  open_tr();
    open_th( 'colspan=2', 'Filter' );
  open_tr();
    open_th( '', 'Art:' );
    open_td();
      filter_jperson( $fields['jperson'] );
  open_tr();
    open_th( 'center', 'Aktionen' );
    open_td( 'center', inlink( 'person', 'class=bigbutton,text=Neue Person' ) );
close_table();

bigskip();


handle_action( array( 'update', 'deletePerson' ) );
switch( $action ) {
  case 'deletePerson':
    need( $message > 0, 'keine person ausgewaehlt' );
    sql_delete_people( $message );
    break;
}

medskip();

peoplelist_view( $fields['_filters'], '' );

?>
