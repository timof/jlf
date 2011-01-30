<?php

assert( $logged_in ) or exit();

$editable = true;

get_http_var( 'tapes_id', 'u', 0, true );

if( $tapes_id ) {
  $tape = sql_tape( $tapes_id );
  $cn = $tape['cn'];
  $type_tape = $tape['type_tape'];
  $oid = $tape['oid'];
  $good = $tape['good'];
  $retired = $tape['retired'];
  $location = $tape['location'];
} else {
  $cn = '';
  $type_tape = '';
  $oid = "$oid_prefix.";
  $good = 1;
  $retired = 0;
  $location = '';
}

get_http_var( 'action', 'w', '' );
$editable or $action = '';
switch( $action ) {
  case 'save':
    need_http_var( 'cn', 'W' );
    need_http_var( 'type_tape', '/^[a-zA-Z0-9-]+$/' );
    need_http_var( 'oid', '/^[0-9.]+$/' );
    get_http_var( 'good', 'u', 0 );
    get_http_var( 'retired', 'u', 0 );
    need_http_var( 'location', 'h' );

    $values = array(
      'cn' => "$cn"
    , 'type_tape' => $type_tape
    , 'oid' => $oid
    , 'location' => $location
    , 'good' => $good
    , 'retired' => $retired
    );
    if( $tapes_id ) {
      sql_update( 'tapes', $tapes_id, $values );
    } else {
      $tapes_id = sql_insert( 'tapes', $values );
      persistent_var( 'tapes_id', 'self' );
    }
    break;
}

open_form( '', "action=save" );
  open_fieldset( 'small_form', '', ( $tapes_id ? 'edit tape' : 'new tape' ) );
    open_table('small_form hfill');
      form_row_text( 'cn: ', 'cn', 10, $cn );
        open_span( 'quad' );
          echo 'type: ';
          open_select( 'type_tape', '', options_type_tape( $type_tape ) );
        close_span();
        open_span( 'quad', '', 'location: '. string_view( $location, 10, 'location' ) );
      form_row_text( 'oid: ', 'oid', 30, $oid );
      open_tr();
        open_td( '', "colspan='2'" );
          $checked = ( $good ? 'checked' : '' );
          open_span( 'qquad', '', "<input type='checkbox' name='good' $checked> good" );
          $checked = ( $retired ? 'checked' : '' );
          open_span( 'qquad', '', "<input type='checkbox' name='retired' $checked> retired" );
      open_tr();
      open_td( 'right', "colspan='2'" );
        submission_button();
    close_table();
  close_fieldset();
close_form();

?>
