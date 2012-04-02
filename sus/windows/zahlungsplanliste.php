<?php

echo html_tag( 'h1', '', 'Zahlungsplan' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );
$jahr_max = $geschaeftsjahr_thread + 99;

$fields = init_fields( array(
  'people_id'
, 'geschaeftsjahr' => "global=1,sources=http self,set_scopes=self,default=$geschaeftsjahr_thread,max=$jahr_max" ) );

// debug( $fields['geschaeftsjahr'], 'gj' );

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', 'Geschaeftsjahr:' );
    open_td();
      filter_geschaeftsjahr( $fields['geschaeftsjahr'] );
  open_tr();
    open_th( '', 'Kreditor:' );
    open_td();
      filter_person( $fields['people_id'] );
  open_tr();
    open_th( 'center,colspan=2', 'Aktionen' );
  open_tr();
close_table();

medskip();

handle_action( array( 'update', 'deleteZahlungsplan' ) );
switch( $action ) {
  case 'update':
    //nop
    break;

  case 'deleteZahlungsplan':
    need( $message, 'kein zahlungsplan gewaehlt' );
    sql_delete_zahlungsplan( $message );
    break;
}

// foreach( sql_darlehen() as $d ) {
//   sql_zahlungsplan_berechnen( $d['darlehen_id'], 'delete' );
// }

zahlungsplanlist_view( $fields['_filters'], '' );

?>
