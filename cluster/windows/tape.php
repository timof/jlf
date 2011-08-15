<?php

init_global_var( 'tapes_id', 'u', 'http,persistent', 0, 'self' );
if( $tapes_id ) {
  $tape = sql_one_tape( $tapes_id );
  $oid_t = $tape['oid_t'] = oid_canonical2traditional( $tape['oid'] );
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


if( $tapes_id ) {
  open_fieldset( 'small_form', 'edit tape' );
} else {
  open_fieldset( 'small_form new', 'new tape' );
}
  open_table( 'hfill' );
  open_table( 'hfill,colgroup=20% 30% 50%' );
    open_tr();
      open_td();
        open_label( 'cn', '', 'cn:' );
      open_td( '', string_view( $cn, 'cn', 10 ) );
      open_td( 'qquad' );
        open_label( 'type_tape', '', 'type:' );
          selector_type_tape();

    open_tr();
      open_td();
        open_label( 'location', '', 'location: ' );
      open_td( 'colspan=2', string_view( $location, 'location', 30 ) );

    open_tr();
      open_td();
        open_label( 'oid_t', '', 'oid: ' );
      open_td( 'colspan=2', string_view( $oid_t, 'oid_t', 30 ) );

    open_tr();
      open_td();
        open_label( 'good', '', 'good: ' );
      open_td( '', checkbox_view( $good, 'good' ) );

      open_td( 'qquad' );
        open_label( 'retired', '', 'retired: ' );
        echo checkbox_view( $retired, 'retired' );

    open_tr();
      open_td( 'right,colspan=3' );
        if( ! $changes )
          template_button();
        submission_button();
  close_table();
close_fieldset();

if( $tapes_id ) {
  open_fieldset( 'small_form', 'chunks', 'on' );
    tapechunkslist_view( array( 'tapes_id' => $tapes_id ), false );
  close_fieldset();
}

?>
