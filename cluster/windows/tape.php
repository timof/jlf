<?php

init_global_var( 'tapes_id', 'u', 'http,persistent', 0, 'self' );
if( $tapes_id ) {
  $tape = sql_one_tape( $tapes_id );
  $tape['oid_t'] = oid_canonical2traditional( $tape['oid'] );
} else { 
  $tape = false;
}
row2global( 'tapes', $tape );

$fields = array(
  'cn' => 'W'
, 'type_tape' => '/^[a-zA-Z0-9-]+$/'
, 'oid_t' => '/^[0-9.]+$/'
, 'good' => '^[01]$'
, 'retired' => '^[01]$'
, 'location' => 'H'
);
$changes = array();
$problems = array();
foreach( $fields as $fieldname => $type ) {
  init_global_var( $fieldname, $type, 'http,persistent,keep', '', 'self' );
  if( $tapes_id ) {
    if( $GLOBALS[ $fieldname ] !== $tape[ $fieldname ] ) {
      $changes[ $fieldname ] = 'modified';
    }
  }
}

handle_action( array( 'update', 'save', 'init', 'template' ) );
switch( $action ) {
  case 'template':
    $tapes_id = 0;
    break;

  case 'init':
    $tapes_id = 0;
    $oid_t = $oid_prefix;
    $good = 1;
    break;

  case 'save':
    $values = array();
    foreach( $fields as $fieldname => $type ) {
      if( checkvalue( $$fieldname, $type ) !== NULL ) {
        $values[ $fieldname ] = $$fieldname;
      } else {
        $problems[ $fieldname ] = 'type mismatch';
      }
    }
    if( ! in_array( $values['type_tape'], $tape_types ) ) {
      $problems['type_tape'] = 'not in list';
    }
    if( ! $problems ) {
      $values['oid'] = oid_traditional2canonical( $values['oid_t'] );
      unset( $values['oid_t'] );
      if( $tapes_id ) {
        sql_update( 'tapes', $tapes_id, $values );
      } else {
        $tapes_id = sql_insert( 'tapes', $values );
      }
    }
    break;
}


// open_form( 'name=update_form', "action=save" );
  if( $tapes_id ) {
    open_fieldset( 'small_form', '', 'edit tape' );
  } else {
    open_fieldset( 'small_form', 'modified', 'new tape' );
  }
    open_table('small_form hfill');
      form_row_text( 'cn: ', 'cn', 10, $cn );
      open_tr();
        $c = field_class('type_tape');
        open_td( "label $c", '', 'type: ' );
        open_td( "kbd $c", "colspan='2'" );
          selector_type_tape();
      form_row_text( 'location: ', 'location', 20, $location );
      form_row_text( 'oid: ', 't_oid', 30, $t_oid );
      open_tr();
        open_td();
        open_td( 'left', "colspan='2'" );
          $checked = ( $good ? 'checked' : '' );
          open_span( 'qquad', '', "<input type='checkbox' name='good' $checked> good" );
          $checked = ( $retired ? 'checked' : '' );
          open_span( 'qquad', '', "<input type='checkbox' name='retired' $checked> retired" );
      open_tr();
        open_td( 'right', "colspan='3'" );
          if( $changes || ! $tapes_id ) {
            submission_button();
          } else {
            echo inlink( 'update,action=template,text=use as template,class=button' );
          }
    close_table();
  close_fieldset();
// close_form();

if( $tapes_id ) {
  open_fieldset( 'small_form', '', 'chunks', 'on' );
    tapechunkslist_view( array( 'tapes_id' => $tapes_id ), false );
  close_fieldset();
}

?>
