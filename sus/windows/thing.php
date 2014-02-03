<?php

sql_transaction_boundary('*');

init_var( 'things_id', 'global,type=U,sources=http persistent,set_scopes=self' );
$thing = sql_one_thing( $things_id );

open_fieldset( 'small_form', 'Stammdaten Gegenstand' );
  thing_view( $things_id );
close_fieldset();

open_fieldset( 'small_form', 'Wertentwicklung', 'off' );
  postenlist_view( array( 'things_id' => $things_id ) );
close_fieldset();

?>
