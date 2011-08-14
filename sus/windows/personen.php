<?php

echo "<h1>Personen</h1>";

init_global_var( 'options', 'u', 'http,self', 0, 'self' );

$filters = handle_filters( array( 'jperson' ) );

open_table('menu');
  open_tr();
    open_th( 'colspan=2', 'Filter' );
  open_tr();
    open_th( '', 'Art:' );
    open_td();
      filter_jperson();
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

people_view( $filters, '' );

?>
