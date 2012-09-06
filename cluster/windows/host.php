<?php

init_var( 'hosts_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
init_var( 'flag_problems', 'type=u,sources=persistent,default=0,global,set_scopes=self' );

do {
  $reinit = false;
  $problems = array();

  if( $hosts_id ) {
    $host = sql_one_host( $hosts_id );
    $host['oid_t'] = oid_canonical2traditional( $host['oid'] );
    $host['ip4_t'] = oid_canonical2traditional( $host['ip4'] );
    $host['hostname'] = $host['fqhostname'];
    $host['domain'] = '';
    if( ( $n = strpos( $host['hostname'], '.' ) ) !== false ) {
      $host['domain'] = substr( $host['hostname'], $n + 1 );
      $host['hostname'] = substr( $host['hostname'], 0, $n );
    }
    $flag_modified = 1;
  } else { 
    $host = array();
    $flag_modified = 0;
  }
  $opts = array(
    'flag_problems' => & $flag_problems 
  , 'flag_modified' => & $flag_modified
  , 'tables' => 'hosts'    // db tables to check for patterns and defaults
  , 'rows' => array( 'hosts' => $host )
  , 'failsafe' => false
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $action === 'reset' ) {
    $opts['reset'] = 1;
    $flag_problems = 0;
  }

  $f = init_fields( array(
      'hostname' => 'type=W32,pattern=/^[a-z0-9-]+$/,default=,size=10'
    , 'domain' => 'type=a64,pattern=/^[a-z0-9.-]+$/,default=,size=25'
    , 'sequential_number' => 'type=U,default=1,size=3'
    , 'ip4_t' => 'type=a15,pattern=/^[0-9.]*$/,default=,size=20'
    , 'ip6' => 'type=a64,pattern=/^[0-9:]*$/,default=,size=30'
    , 'oid_t' => 'type=a240,pattern=/^[0-9.]+$/,size=30,default='.$oid_prefix
    , 'processor' => 'type=a128,size=20'
    , 'os' => 'type=H32,default=,size=10'
    , 'invlabel' => 'type=W20,default=C,size=8'
    , 'year_manufactured' => 'type=u,size=4'
    , 'year_decommissioned' => 'type=u,size=4'
    , 'location' => array( 'type' => 'H', 'size' => '20', 'uid_choices' => choices_locations( 'hosts' ) )
    , 'description' => 'type=h,lines=6,cols=80'
    )
  , $opts
  );

  if( $flag_problems ) {
    // check for additional problems which can prevent saving:
  }
  
  handle_action( array( 'update', 'save', 'reset', 'template' ) );
  switch( $action ) {
    case 'template':
      $hosts_id = 0;
      reinit();
      break;

//     case 'init':
//       $hosts_id = 0;
//       $oid_t = $oid_prefix;
//       $ip4_t = $ip4_prefix;
//       $domain = $default_domain;
//       break;

    case 'save':
      if( ! $f['_problems'] ) {
        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            $values[ $fieldname ] = $f[ $fieldname ]['value'];
        }
        if( ! ( $problems = sql_save_host( $hosts_id, $values, 'check' ) ) ) {
          $hosts_id = sql_save_host( $hosts_id, $values );
          need( isnumber( $hosts_id ) && ( $hosts_id > 0 ) );
          reinit('reset');
        }
      }
      break;
  }

} while( $reinit );

if( $hosts_id ) {
  open_fieldset( 'small_form old', "edit host [$hosts_id]" );
} else  {
  open_fieldset( 'small_form new', 'new host' );
}
  flush_problems();
  open_table( 'hfill,colgroup=20% 30% 50%' );
    open_tr();
      open_td( array( 'label' => $f['hostname'] ), 'fqhostname:' );
      open_td( 'oneline', string_element( $f['hostname'] ) . ' . '. string_element( $f['domain' ] ) );
      open_td( 'qquads online' );
        open_label( $f['sequential_number'], '#: ' );
        echo int_element( $f['sequential_number'] );

    open_tr();
      open_td( array( 'label' => $f['ip4_t'] ), 'ip4:' );
      open_td( 'colspan=2', string_element( $f['ip4_t'] ) );

    open_tr();
      open_td( array( 'label' => $f['ip6'] ), 'ip6:' );
      open_td( 'colspan=2', string_element( $f['ip6'] ) );

    open_tr();
      open_td( array( 'label' => $f['oid_t'] ),  'oid: ' );
      open_td( '', string_element( $f['oid_t'] ) );
      open_td( 'qquads' );
        open_label( $f['invlabel'], 'invlabel: ' );
        echo string_element( $f['invlabel'] );

    open_tr();
      open_td( array( 'label' => $f['processor'] ), 'processor: ' );
      open_td( '', string_element( $f['processor'] ) );
      open_td( 'qquad' );
        open_label( $f['os'], 'os: ' );
        echo string_element( $f['os'] );

    open_tr();
      open_td( array( 'label' => $f['location'] ), 'location: ' );
      open_td( 'colspan=2', string_element( $f['location'] ) );

    open_tr();
      open_td( array( 'label' => $f['year_manufactured'] ), 'manufactured:' );
      open_td( '', int_element( $f['year_manufactured'] ) );
      open_td( 'qquad oneline' );
        open_label( $f['year_decommissioned'], 'decommissioned:' );
        echo int_element( $f['year_decommissioned'] );

    open_tr();
      open_td( array( 'label' => $f['description'], 'colspan' => 3 ), 'notes:' );
    open_tr();
      open_td( 'colspan=3', textarea_element( $f['description'] ) );

    open_tr( 'medskip' );
    open_td( 'right,colspan=3' );
      if( $hosts_id && ! $f['_changes'] )
        template_button();
      reset_button( $f['_changes'] ? '' : 'display=none' );
      submission_button( $f['_changes'] ? '' : 'display=none' );

  close_table();
close_fieldset();


if( $hosts_id ) {
  open_fieldset( 'small_form', 'disks', 'on' );
    diskslist_view( array( 'hosts_id' => $hosts_id ) );
  close_fieldset();

  open_fieldset( 'small_form', 'accounts', 'on' );
    accountslist_view( array( 'hosts_id' => $hosts_id ) );
  close_fieldset();

  open_fieldset( 'small_form', 'services', 'on' );
    serviceslist_view( array( 'hosts_id' => $hosts_id ) );
  close_fieldset();
}

?>
