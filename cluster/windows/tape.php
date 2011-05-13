<?php

init_global_var( 'tapes_id', 'u', 'http,persistent', 0, 'self' );

$tape = ( $tapes_id ? sql_one_tape( $tapes_id ) : false );
row2global( 'tapes', $tape );

$problems = array();

$fields = array(
  'cn' => 'W'
, 'type_tape' => '/^[a-zA-Z0-9-]+$/'
, 'oid' => '/^[0-9.]+$/'
, 'good' => 'u'
, 'retired' => 'u'
, 'location' => 'H'
);
foreach( $fields as $fieldname => $type )
  init_global_var( $fieldname, $type, 'http,persistent,keep', '', 'self' );

handle_action( array( 'update', 'save', 'init', 'template' ) );
switch( $action ) {
  case 'template':
    $tapes_id = 0;
    break;

  case 'init':
    $tapes_id = 0;
    $oid = $oid_prefix;
    $good = 1;
    break;
  case 'save':
    $values = array();
    foreach( $fields as $fieldname => $type ) {
      if( checkvalue( $fieldname, $type ) !== NULL )
        $values[ $fieldname ] = $$fieldname;
      else
        $problems[] = $fieldname;
    }
    if( ! $problems ) {
      if( $tapes_id ) {
        sql_update( 'tapes', $tapes_id, $values );
      } else {
        $tapes_id = sql_insert( 'tapes', $values );
      }
    }
    break;
}

open_form( '', "action=save" );
  open_fieldset( 'small_form', '', ( $tapes_id ? 'edit tape' : 'new tape' ) );
    open_table('small_form hfill');
      form_row_text( 'cn: ', 'cn', 10, $cn );
      open_tr();
        open_td( problem_class('type_tape'), '', 'type: ' );
        open_td();
          selector_type_tape();
      open_tr();
        open_td( problem_class('location'), '', 'location: ' );
        open_td( string_view( $location, 20, 'location' ) );
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

if( $tapes_id ) {
  open_fieldset( 'small_form', '', 'chunks', 'on' );
    tapechunkslist_view( array( 'tapes_id' => $tapes_id ), false );
  close_fieldset();
}

?>
