<?php

init_global_var( 'disks_id', 'u', 'http,persistent', 0, 'self' );
if( $disks_id ) {
  $disk = sql_one_disk( $disks_id );
  $disk['oid_t'] = oid_canonical2traditional( $disk['oid'] );
} else {
  $disk = false;
}
row2global( 'disks', $disk );

// $oid || ( $oid = $oid_prefix );

$fields = array(
   'cn' => 'W'
,  'type_disk' => 'h'
,  'interface_disk' => 'h'
,  'description' => 'h'
,  'oid_t' => '/^[0-9.]+$/'
,  'sizeGB' => 'U'
,  'location' => 'h'
,  'hosts_id' => 'u'
);
$changes = array();
$problems = array();

foreach( $fields as $fieldname => $type ) {
  init_global_var( $fieldname, $type, 'http,persistent,keep', '', 'self' );
  if( $disks_id ) {
    if( $GLOBALS[ $fieldname ] !== $disk[ $fieldname ] ) {
      $changes[ $fieldname ] = 'modified';
    }
  }
}

handle_action( array( 'update', 'save', 'init', 'template' ) );
switch( $action ) {
  case 'template':
    $disks_id = 0;
    break;

  case 'init':
    $disks_id = 0;
    $oid_t = $oid_prefix;
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
    if( ! in_array( $values['type_disk'], $disk_types ) ) {
      $problems['type_disk'] = 'not in list';
    }
    if( ! in_array( $values['interface_disk'], $disk_interfaces ) ) {
      $problems['interface_disk'] = 'not in list';
    }
    if( ! $problems ) {
      $values['oid'] = oid_traditional2canonical( $values['oid_t'] );
      unset( $values['oid_t'] );
      if( $disks_id ) {
        sql_update( 'disks', $disks_id, $values );
      } else {
        $disks_id = sql_insert( 'disks', $values );
      }
    }
    break;
}

if( $disks_id ) {
  open_fieldset( 'small_form', '', 'edit disk' );
} else {
  open_fieldset( 'small_form new', '', 'new disk' );
}
  open_table('small_form hfill');
    form_row_text( 'cn: ', 'cn', 10, $cn );
    open_tr();
      $c = field_class('type_disk');
      open_td( "label $c", "id='label_type_disk'", 'type:' );
      open_td( "$c", "id='input_type_disk'", false, 2 );
        selector_type_disk();
    open_tr();
      $c = field_class('interface_disk');
      open_td( "label $c", "id='label_interface_disk'", 'interface:' );
      open_td( "$c", "id='input_interface_disk'", false, 2 );
        selector_interface_disk();
    open_tr();
      $c = field_class('sizeGB');
      open_td( "label $c", "id='label_sizeGB'", 'size: ' );
      open_td( "oneline kbd $c", "id='input_sizeGB'", int_view( $sizeGB, 'sizeGB', 5 ).'GB', 2 );
    form_row_text( 'description: ', 'description', 30, $description );
    form_row_text( 'oid: ', 'oid_t', 30, $oid_t );
    form_row_text( 'location: ', 'location', 10, $location );
    open_tr();
      $c = field_class('hosts_id');
      open_td( "label $c",  "id='label_host'", 'host: ' );
      open_td( "$c", "id='input_host'", false, 2 );
        selector_host( 'hosts_id', $hosts_id, '', '(none)' );
    open_tr();
      open_td( 'right', '', false, 3 );
        if( $changes || ! $disks_id ) {
          submission_button();
        } else {
          echo inlink( 'update,action=template,text=use as template,class=button' );
        }
  close_table();
close_fieldset();

?>
