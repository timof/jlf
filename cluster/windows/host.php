<?php

init_var( 'hosts_id', 'global,pattern=u,sources=http persistent,default=0,set_scopes=self' );
init_var( 'flag_problems', 'pattern=u,sources=persistent,default=0,global,set_scopes=self' );

do {
  $reinit = false;

  $opts = array( 'tables' => 'hosts' );
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
    $opts['flag_modified'] = 1;
  } else { 
    $host = array();
    $opts['flag_modified'] = 0;
  }

  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $action === 'reset' ) {
    $opts['reset'] = 1;
    $flag_problems = 0;
  }
  $opts['flag_problems'] = $flag_problems;

  $f = init_form_fields( array(
      'hostname' => '/^[a-z0-9-]+$/,default=,size=15'
    , 'domain' => '/^[a-z0-9.-]+$/,default=,size=25'
    , 'sequential_number' => 'U,default=1,size=3'
    , 'ip4_t' => '/^[0-9.]*$/,default=,size=20'
    , 'ip6' => '/^[0-9:]*$/,default=,size=30'
    , 'oid_t' => '/^[0-9.]+$/,size=30,default='.$oid_prefix
    , 'processor' => 'size=20'
    , 'os' => 'H,default=,size=20'
    , 'invlabel' => 'W,default=C,size=10'
    , 'active'
    , 'location' => 'H,default=,size=20'
    )
  , array( 'hosts' => $host )
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
        $values['ip4'] = ip4_traditional2canonical( $values['ip4_t'] );
        unset( $values['ip4_t'] );
        $values['oid'] = oid_traditional2canonical( $values['oid_t'] );
        unset( $values['oid_t'] );
        $values['fqhostname'] = "{$values['hostname']}.{$values['domain']}";
        unset( $values['hostname'] );
        unset( $values['domain'] );
        if( $hosts_id ) {
          sql_update( 'hosts', $hosts_id, $values );
        } else {
          $hosts_id = sql_insert( 'hosts', $values );
        }
        reinit();
      }
      break;
  }

} while( $reinit );

if( $hosts_id ) {
  open_fieldset( 'small_form old', "edit host [$hosts_id]" );
} else  {
  open_fieldset( 'small_form new', 'new host' );
}
  open_table( 'hfill,colgroup=20% 30% 50%' );
    open_tr();
      open_td( array( 'label' => $f['hostname'] ), 'fqhostname:' );
      open_td( 'oneline,colspan=2', string_element( $f['hostname'] ) . ' . '. string_element( $f['domain' ] ) );

    open_tr();
      open_td( array( 'label' => $f['ip4_t'] ), 'ip4:' );
      open_td( 'colspan=2', string_element( $f['ip4_t'] ) );

    open_tr();
      open_td( array( 'label' => $f['ip6'] ), 'ip6:' );
      open_td( 'colspan=2', string_element( $f['ip6'] ) );

    open_tr();
      open_td( array( 'label' => $f['oid_t'] ),  'oid: ' );
      open_td( 'colspan=2', string_element( $f['oid_t'] ) );

    open_tr();
      open_td( array( 'label' => $f['sequential_number'] ), '#: ' );
      open_td( '', int_element( $f['sequential_number'] ) );
      open_td( 'qquad' );
        open_label( $f['active'], 'active: ' );
        echo checkbox_element( $f['active'] );

    open_tr();
      open_td( array( 'label' => $f['processor'] ), 'processor: ' );
      open_td( '', string_element( $f['processor'] ) );
      open_td( 'qquad' );
        open_label( $f['os'], 'os: ' );
        echo string_element( $f['os'] );

    open_tr();
      open_td( array( 'label' => $f['location'] ), 'location: ' );
      open_td( '', string_element( $f['location'] ) );
      open_td( 'qquad' );
        open_label( array( 'label' => $f['invlabel'] ), 'invlabel: ' );
        echo string_element( $f['invlabel'] );

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
