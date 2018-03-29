<?php

sql_transaction_boundary('*');

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('h28innenhof');
    echo html_tag( 'h1', '', we('People / contact information','Personen / Details zur Person') );
  close_div();
close_div();

init_var( 'people_id', 'global,type=U6,sources=http persistent,set_scopes=self url' );

if( ! $person = sql_person( "people_id=$people_id,flag_publish,flag_deleted=0,flag_virtual=0", 'default=0' ) ) {
  open_div( 'warn', we('query failed - no such person','Abfrage fehlgeschlagen - keine Person gefunden' ) );
  return;
}

open_ccbox( '', we('contact information', 'Visitenkarte' ) );
  echo person_visitenkarte_view( $person );
  open_div('clear','');
close_ccbox();


?>
