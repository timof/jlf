<?php

init_var( 'disks_id', 'global,type=u,sources=http persistent,default=0,set_scope=self' );

init_var( 'flag_problems', 'type=u,sources=persistent,default=0,global,set_scope=self' );

$disks_fields = array(
  'cn' => 'W'
, 'type_disk' => 'E,'.implode( ',', $disk_types )
, 'interface_disk' => 'E,' . implode( ',', $disk_interfaces )
, 'description' => 'h'
, 'oid_t' => '/^[0-9.]+$/'
, 'sizeGB' => 'U'
, 'location' => 'h'
, 'hosts_id' => 'u'
);

function init() {
  global $disks_id, $disks_fields, $flag_problems, $action;

  if( $disks_id ) {
    $disk = sql_one_disk( $disks_id );
    $disk['oid_t'] = oid_canonical2traditional( $disk['oid'] );
    $flag_modified = 1;
  } else {
    $disk = array();
    $disk['oid_t'] = $GLOBALS['oid_prefix'];
    $flag_modified = 0;
  }

  $opts = array( 'flag_problems' => & $flag_problems, 'flag_modified' => & $flag_modified );
  if( $action === 'save' )
    $flag_problems = 1;
  if( $action === 'reset' ) {
    $opts['reset'] = 1;
    $flag_problems = 0;
  }

  // _need_ $GLOBALS to assign ref!
  $GLOBALS['fields'] = & init_form_fields( $disks_fields, array( 'disks' => $disk ), $opts );
}

init();

handle_action( array( 'update', 'save', 'reset', 'init', 'template' ) );
switch( $action ) {

  case 'template':
    $disks_id = 0;
    init();
    break;

  case 'init':
    $disks_id = 0;
    init();
    break;

  case 'save':
    if( ! $fields['_problems'] ) {
      $values = array();
      foreach( $disks_fields as $fieldname => $r ) {
        $values[ $fieldname ] = $fields[ $fieldname ]['value'];
        $fields[ $fieldname ]['field_class'] = 'justsaved';
      }
      $values['oid'] = oid_traditional2canonical( $values['oid_t'] );
      unset( $values['oid_t'] );
      if( $disks_id ) {
        sql_update( 'disks', $disks_id, $values );
      } else {
        $disks_id = sql_insert( 'disks', $values );
      }
      init();
    }
    break;
}

if( $disks_id ) {
  open_fieldset( 'small_form old', "edit disk [$disks_id]" );
} else {
  open_fieldset( 'small_form new', 'new disk' );
}
  open_table( 'hfill,colgroup=20% 30% 50%' );
    open_tr();
      open_td();
        open_label( 'cn', '', 'cn:' );
      open_td( '', string_element( 'cn', 'size=10' ) );
      open_td( 'qquad' );
        open_label( 'sizeGB', 'quads', 'size:' );
        echo int_element( 'sizeGB', 'size=5' ).'GB';

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
      open_td( 'colspan=2', string_element( 'oid_t', 'size=30' ) );

    open_tr();
      open_td();
        open_label( 'hosts_id', '', 'host:' );
      open_td( 'colspan=2' );
        selector_host( 'hosts_id', $hosts_id, '', '(none)' );

    open_tr();
      open_td( 'qquad' );
        open_label( 'location', '', 'location:' );
      open_td( 'colspan=2', string_element( 'location', 'size=10' ) );

    open_tr();
      open_td();
        open_label( 'description', '', 'description:' );
      open_td( 'colspan=2', textarea_element( 'description', 'rows=4,cols=40' ) );

    open_tr();
      open_td( 'right,colspan=3' );
        if( $disks_id && ! $fields['_changes'] )
          template_button();
        reset_button( $fields['_changes'] ? '' : 'display=none' );
        submission_button( $fields['_changes'] ? '' : 'display=none' );

  close_table();
close_fieldset();

?>
