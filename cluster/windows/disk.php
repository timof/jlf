<?php

init_global_var( 'disks_id', 'u', 'http,persistent', 0, 'self' );
if( $disks_id ) {
  $disk = sql_one_disk( $disks_id );
  $oid_t = $disk['oid_t'] = oid_canonical2traditional( $disk['oid'] );
} else {
  $disk = false;
  $oid_t = $oid_prefix;
}
row2global( 'disks', $disk );

// $oid || ( $oid = $oid_prefix );

$fields = array(
  'cn' => 'W'
, 'type_disk' => 'h'
, 'interface_disk' => 'h'
, 'description' => 'h'
, 'oid_t' => '/^[0-9.]+$/'
, 'sizeGB' => 'U'
, 'location' => 'h'
, 'hosts_id' => 'u'
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
  open_fieldset( 'small_form', 'edit disk' );
} else {
  open_fieldset( 'small_form new', 'new disk' );
}
  open_table( 'hfill,colgroup=20% 30% 50%' );
    open_tr();
      open_td();
        open_label( 'cn', '', 'cn:' );
      open_td( '', string_view( $cn, 'cn', 10 ) );
      open_td( 'qquad' );
        open_label( 'sizeGB', 'quads', 'size:' );
        echo int_view( $sizeGB, 'sizeGB', 5 ).'GB';

    open_tr();
      open_td();
        open_label( 'interface_disk', '', 'interface:' );
      open_td();
        selector_interface_disk();
      open_td( 'qquad' );
        open_label( 'type_disk', 'quads', 'type:' );
        selector_type_disk();

    open_tr();
      open_td();
        open_label( 'oid_t', '', 'oid:' );
      open_td( 'colspan=2', string_view( $oid_t, 'oid_t', 30 ) );

    open_tr();
      open_td();
        open_label( 'hosts_id', '', 'host:' );
      open_td( 'colspan=2' );
        selector_host( 'hosts_id', $hosts_id, '', '(none)' );

    open_tr();
      open_td( 'qquad' );
        open_label( 'location', '', 'location:' );
      open_td( 'colspan=2', string_view( $location, 'location', 10 ) );

    open_tr();
      open_td();
        open_label( 'description', '', 'description:' );
      open_td( 'colspan=2', string_view( $description, 'description', 40 ) );

    open_tr();
      open_td( 'right,colspan=3' );
        if( ! $changes )
          template_button();
        submission_button();

  close_table();
close_fieldset();

?>
