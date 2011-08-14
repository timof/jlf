<?php

init_global_var( 'things_id', 'U', 'http,persistent', NULL, 'self' );
$thing = sql_one_thing( $things_id );

open_fieldset( 'small_form', 'Stammdaten Gegenstand' );
  thing_view( $things_id );
close_fieldset();

open_fieldset( 'small_form', 'Wertentwicklung', 'off' );
  postenlist_view( array( 'things_id' => $things_id ) );
close_fieldset();

?>
