<?php

do {
  $reinit = false;
  // allow to re-initialize after actions like 'save', by setting $reinit = true;
  // we could stuff this into an init()-function, but that will cause endless trouble
  // with global vars and assigning references to them.

  init_var( 'disks_id', 'global,pattern=u,sources=http persistent,default=0,set_scopes=self' );
  init_var( 'flag_problems', 'pattern=u,sources=persistent,default=0,global,set_scopes=self' );

  $disks_fields = l2a( array(
    'cn' => 'size=10,default='
  , 'type_disk'
  , 'interface_disk'
  , 'description' => 'rows=4,cols=40'
  , 'oid_t' => 'pattern=Toid,size=30'
  , 'sizeGB' => 'size=6'
  , 'location' => 'size=10'
  , 'hosts_id'
  ) );

  if( $disks_id ) {
    $disk = sql_one_disk( $disks_id );
    $disk['oid_t'] = oid_canonical2traditional( $disk['oid'] );
    $flag_modified = 1;
  } else {
    $disk = array();
    $disk['oid_t'] = $oid_prefix;
    $flag_modified = 0;
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => & $flag_modified
  , 'tables' => 'disks'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $action === 'reset' ) {
    $opts['reset'] = 1;
    $flag_problems = 0;
  }

  $f = init_form_fields( $disks_fields, array( 'disks' => $disk ), $opts );

  if( $flag_problems ) {
    // check for additional problems which can prevent saving:
    if( ( $hosts_id = $f['hosts_id']['value'] ) ) {
      if( ! sql_one_host( $hosts_id, NULL ) ) {
        $problems[] = 'host not found';
        $f['_problems']['hosts_id'] = $hosts_id;
        $f['hosts_id']['class'] = 'problem';
      }
    }
  }

  handle_action( array( 'update', 'save', 'reset', 'template' ) );
  switch( $action ) {

    case 'template':
      $disks_id = 0;
      init();
      break;

  //  case 'init':
  //    $disks_id = 0;
  //    init();
  //    break;

    case 'save':
      if( ! $f['_problems'] ) {
        $values = array();
        foreach( $disks_fields as $fieldname => $r ) {
          $values[ $fieldname ] = $f[ $fieldname ]['value'];
          $f[ $fieldname ]['class'] = 'justsaved';
        }
        $values['oid'] = oid_traditional2canonical( $values['oid_t'] );
        unset( $values['oid_t'] );
        if( $disks_id ) {
          sql_update( 'disks', $disks_id, $values );
        } else {
          $disks_id = sql_insert( 'disks', $values );
        }
        reinit();
      }
      break;
  }

} while( $reinit );


if( $disks_id ) {
  open_fieldset( 'small_form old', "edit disk [$disks_id]" );
} else {
  open_fieldset( 'small_form new', 'new disk' );
}
  open_table( 'hfill,colgroup=20% 30% 50%' );
    open_tr();
      open_td( 'label=cn', 'cn:' );
      open_td( '', string_element( $f['cn'] ) );
      open_td( 'qquad' );
        open_label( 'name=sizeGB,class=quads', 'size:' );
        echo int_element( $f['sizeGB'] ).'GB';

    open_tr();
      open_td( 'label=interface_disk', 'interface:' );
      open_td();
        selector_interface_disk();
      open_td( 'qquad' );
        open_label( 'name=type_disk,class=quads', 'type:' );
        selector_type_disk();

    open_tr();
      open_td( 'label=oid_t', 'oid:' );
      open_td( 'colspan=2', string_element( $f['oid_t'] ) );

    open_tr();
      open_td( 'label=hosts_id', 'host:' );
      open_td( 'colspan=2' );
        selector_host( $f['hosts_id'], NULL, '', '(none)' );
        if( $hosts_id ) {
          open_div( '', inlink( 'host', "text= > host...,hosts_id=$hosts_id" ) );
        }

    open_tr();
      open_td( 'qquad,label=location', 'location:' );
      open_td( 'colspan=2', string_element( $f['location'] ) );

    open_tr();
      open_td( 'label=description', 'description:' );
      open_td( 'colspan=2', textarea_element( $f['description'] ) );

    if( $problems ) {
      open_tr( 'smallskips' );
        open_td( 'left,colspan=3' );
          open_ul( 'problem' );
            flush_problems();
          close_ul();
    }
    open_tr();
      open_td( 'right,colspan=3' );
        if( $disks_id && ! $f['_changes'] )
          template_button();
        reset_button( $f['_changes'] ? '' : 'display=none' );
        submission_button( $f['_changes'] ? '' : 'display=none' );

  close_table();
close_fieldset();

debug( $f, 'f' );

?>
