<?php

init_global_var( 'disks_id', 'u', 'http,persistent', 0, 'self' );
$disk = ( $disks_id ? sql_disk( $disks_id ) : false );
row2global( 'disks', $disk );

$oid || ( $oid = $oid_prefix );

$problems = array();

$fields = array(
   'cn' => 'W'
,  'type_disk' => 'W'
,  'description' => 'h'
,  'oid' => '/^[0-9.]+$/'
,  'sizeGB' => 'U'
,  'location' => 'h'
,  'hosts_id' => 'u'
);
foreach( $fields as $fieldname => $type )
  init_global_var( $fieldname, $type, 'http,persistent,keep', '', 'self' );

handle_actions( array( 'update', 'save', 'init', 'template' ) );
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
        open_span( 'quad '.problem_class('type_disk') );
          echo 'type: ';
          open_select( 'type_disk', '', html_options_type_disk( $type_disk ) );
        close_span();
        open_span( 'quad '.problem_class('sizeGB'), '', 'size: '. int_view( $sizeGB, 'sizeGB', 5 ).'GB' );
      form_row_text( 'description: ', 'description', 30, $description );
      form_row_text( 'oid: ', 'oid', 30, $oid );
      form_row_text( 'location: ', 'location', 10, $location );
        open_span( 'qquad' );
          echo 'host: ';
          open_select( 'hosts_id', '', html_options_hosts( $hosts_id, false, ' (none) ' ) );
        close_span();
      open_tr();
      open_td( 'right', "colspan='2'" );
        submission_button();
    close_table();
  close_fieldset();
close_form();

?>
