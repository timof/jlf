<?php

init_var( 'disks_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
init_var( 'flag_problems', 'type=u,sources=persistent,default=0,global,set_scopes=self' );

do {
  $reinit = false;
  $problems = array();
  // allow to re-initialize after actions like 'save', by setting $reinit = true;
  // we could stuff this into an init()-function, but that will cause endless trouble
  // with global vars and assigning references to them.

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
  , 'tables' => 'disks'             // db tables to check for patterns and defaults
  , 'rows' => array( 'disks' => $disk )  // db rows to take current values from
  , 'failsafe' => false             // allow 'value' => NULL and return offending 'raw'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $action === 'reset' ) {
    $opts['reset'] = 1;
    $flag_problems = 0;
  }

  $f = init_fields( array(
      'cn' => 'size=10,default='
    , 'type_disk'
    , 'interface_disk'
    , 'description' => 'lines=+3,cols=80'
    , 'oid_t' => 'type=Toid,size=30'
    , 'sizeGB' => 'size=6,default=0'
    , 'location' => array( 'type' => 'H', 'size' => '20', 'uid_choices' => choices_locations( 'disks' ) )
    , 'hosts_id'
    , 'year_manufactured' => 'size=4'
    , 'year_decommissioned' => 'size=4'
    )
  , $opts
  );

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

  handle_action( array( 'update', 'save', 'reset', 'template', 'download' ) );
  switch( $action ) {

    case 'template':
      $disks_id = 0;
      reinit();
      break;

  //  case 'init':
  //    $disks_id = 0;
  //    reinit();
  //    break;

    case 'save':
      if( ! $f['_problems'] ) {
        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            $values[ $fieldname ] = $f[ $fieldname ]['value'];
        }
        if( ! ( $problems = sql_save_disk( $disks_id, $values, 'check' ) ) ) {
          $disks_id = sql_save_disk( $disks_id, $values );
          need( isnumber( $disks_id ) && ( $disks_id > 0 ) );
          reinit('reset');
        }
      }
      break;
    case 'download':
      need( $disk, 'no disk selected' );
      switch( $global_format ) {
        case 'pdf':
          echo tex2pdf( 'disk.tex', array( 'loadfile', 'row' => $disk ) );
          break;
        case 'ldif':
          echo ldif_encode( $disk );
          break;
        default:
          error( "unsupported format: [$global_format]" );
      }
      return;
  }

} while( $reinit );


if( $disks_id ) {
  open_fieldset( 'small_form old', "edit disk [$disks_id] " . html_span( 'qquad', download_button( 'pdf,ldif' ) ) );
} else {
  open_fieldset( 'small_form new', 'new disk' );
}
  flush_all_messages();
 
  open_table('css=1,td:smallskips;quads');

    open_tr();
      open_td( '', label_element( $f['cn'], '', 'cn:' ) );
      open_td( '', string_element( $f['cn'] ) );
      open_td( 'qquad oneline' );
        echo label_element( $f['sizeGB'], '', 'size:' );
        echo int_element( $f['sizeGB'] ).'GB';

    open_tr();
      open_td( '', label_element( $f['interface_disk'], '', 'interface:' ) );
      open_td( '', selector_interface_disk( $f['interface_disk'] ) );
      open_td( 'qquad oneline' );
        echo label_element( $f['type_disk'], '', 'type:' );
        echo selector_type_disk( $f['type_disk'] );

    open_tr();
      open_td( '', label_element( $f['oid_t'], '', 'oid:' ) );
      open_td( 'colspan=2', string_element( $f['oid_t'] ) );

    open_tr();
      open_td( '', label_element( $f['hosts_id'], '', 'host:' ) );
      open_td( 'colspan=2,oneline' );
        echo selector_host( $f['hosts_id'], array( 'choices' => array( '0' => ' (none) ' ) ) );
        if( $f['hosts_id']['value'] ) {
          open_span( 'qquad', inlink( 'host', "class=href inlink,text=host...,hosts_id={$f['hosts_id']['value']}" ) );
        }

    open_tr();
      open_td( '', label_element( $f['location'], '', 'location:' ) );
      open_td( 'colspan=2', string_element( $f['location'] ) );

    open_tr();
      open_td( '', label_element( $f['year_manufactured'], '', 'manufactured:' ) );
      open_td( '', int_element( $f['year_manufactured'] ) );
      open_td( 'qquad oneline' );
        echo label_element( $f['year_decommissioned'], '', 'decommissioned:' );
        echo int_element( $f['year_decommissioned'] );

    open_tr();
      open_td( array( 'label' => $f['description'] ), 'description:' );
      open_td( 'colspan=2', textarea_element( $f['description'] ) );

  close_table();

  open_div( 'right' );
    if( $disks_id )
      echo template_button_view();
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view( $f['_changes'] ? '' : 'display=none' );
  close_div();

close_fieldset();

?>
