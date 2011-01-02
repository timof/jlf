<?php

need_http_var( 'things_id', 'u', 0, true );
$thing = sql_one_thing( $things_id );

open_fieldset( 'small_form', '', 'Stammdaten Gegenstand' );
  thing_view( $things_id );
close_fieldset();

open_fieldset( 'small_form', '', 'Wertentwicklung', 'off' );
  postenlist_view( array( 'things_id' => $things_id ) );
close_fieldset();

?>
