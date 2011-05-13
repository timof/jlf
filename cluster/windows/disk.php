<?php

init_global_var( 'disks_id', 'u', 'http,persistent', 0, 'self' );
$disk = ( $disks_id ? sql_one_disk( $disks_id ) : false );
row2global( 'disks', $disk );

$oid || ( $oid = $oid_prefix );
$oid = oid_canonical2traditional( $oid );

$problems = array();

$fields = array(
   'cn' => 'W'
,  'type_disk' => 'h'
,  'interface_disk' => 'h'
,  'description' => 'h'
,  'oid' => '/^[0-9.]+$/'
,  'sizeGB' => 'U'
,  'location' => 'h'
,  'hosts_id' => 'u'
);
foreach( $fields as $fieldname => $type )
  init_global_var( $fieldname, $type, 'http,persistent,keep', '', 'self' );

handle_action( array( 'update', 'save', 'init', 'template' ) );
switch( $action ) {
  case 'template':
    $disks_id = 0;
    break;

  case 'init':
    $disks_id = 0;
    $oid = $oid_prefix;
    break;

  case 'save':
    foreach( $fields as $fieldname => $type ) {
      if( checkvalue( $fieldname, $type ) !== NULL )
        $values[ $fieldname ] = $$fieldname;
      else
        $problems[] = $fieldname;
    }
    if( ! $problems ) {
      $values['oid'] = oid_traditional2canonical( $values['oid'] );
      if( $disks_id ) {
        sql_update( 'disks', $disks_id, $values );
      } else {
        $disks_id = sql_insert( 'disks', $values );
      }
    }
    break;
}

open_form( 'name=update_form', "action=save" );
  open_fieldset( 'small_form', '', ( $disks_id ? 'edit disk' : 'new disk' ) );
    open_table('small_form hfill');
      form_row_text( 'cn: ', 'cn', 10, $cn );
      open_tr();
        open_td( 'label '.problem_class('type_disk') , '', 'type:' );
        open_td();
          selector_type_disk();
      open_tr();
        open_td( 'label '.problem_class('interface_disk'), '', 'interface:' );
        open_td();
          selector_interface_disk();
      open_tr();
        open_td( 'label '.problem_class('sizeGB'), '', 'size: ' );
        open_td( 'oneline', '', int_view( $sizeGB, 'sizeGB', 5 ).'GB' );
      form_row_text( 'description: ', 'description', 30, $description );
      form_row_text( 'oid: ', 'oid', 30, $oid );
      form_row_text( 'location: ', 'location', 10, $location );
      open_tr();
        open_td( 'label '.problem_class('hosts_id'),  '', 'host: ' );
        open_td();
          selector_host( 'hosts_id', $hosts_id, '', '(none)' );
      open_tr();
      open_td( 'right', "colspan='2'" );
        submission_button();
    close_table();
  close_fieldset();
close_form();

?>
