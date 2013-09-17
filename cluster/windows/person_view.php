<?php

sql_transaction_boundary('*');
init_var( 'people_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $people_id ) {
  open_div( 'warn', 'no person selected' );
  return;
}

$person = sql_person( $people_id );

open_fieldset( 'small_form old', 'person' );
  open_table('small_form hfill');
    open_tr();
      open_td( '', 'name:' );
      open_td( 'kbd', $person['cn'] );
    open_tr();
      open_td( '', 'uid:' );
      open_td( 'kbd', $person['uid'] );
    open_tr();
      open_td( '', 'authentication methods:' );
      open_td( 'kbd', $person['authentication_methods'] );

  close_table();
close_fieldset();


?>
