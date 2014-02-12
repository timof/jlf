<?php

sql_transaction_boundary('*');

init_var( 'people_id', 'global,type=U6,sources=http persistent,set_scopes=self url' );

if( ! $person = sql_person( "people_id=$people_id,flag_publish,flag_deleted=0,flag_virtual=0", 'default=0' ) ) {
  open_div( 'warn', we('query failed - no such person','Abfrage fehlgeschlagen - keine Person gefunden' ) );
  return;
}

echo person_visitenkarte_view( $person );

?>
