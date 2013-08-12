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
    , 'online' => 'type=b,default=1'
    , 'mac' => 'type=a17,default=,size=17'
    , 'ip4_t' => 'type=a15,pattern=/^[0-9.]*$/,default=,size=20'
    , 'ip6' => 'type=a64,pattern=/^[0-9:]*$/,default=,size=30'
    , 'oid_t' => 'type=a240,pattern=/^[0-9.]+$/,size=30,default='.$oid_prefix
    , 'processor' => 'type=a128,size=20'
    , 'os' => 'type=H32,default=,size=10'
    , 'invlabel' => 'type=W20,default=C,size=8'
    , 'year_manufactured' => 'type=u,size=4'
    , 'year_decommissioned' => 'type=u,size=4'
    , 'year_inservice' => 'type=u,size=4'
    , 'year_outservice' => 'type=u,size=4'
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
  open_fieldset( 'old', "edit host [$hosts_id] {$f['hostname']}" );
} else  {
  open_fieldset( 'new', 'new host' );
}
  flush_all_messages();

  open_fieldset('hardware:');

    open_fieldset('line', 'identification:' );
      echo label_element( $f['oid_t'], '', 'oid: ' . string_element( $f['oid_t'] ) );
      echo label_element( $f['invlabel'], '', 'invlabel: ' . string_element( $f['invlabel'] ) );
    close_fieldset();

    open_fieldset('line'
    , label_element( $f['mac'], '', 'primary MAC:' )
    , string_element( $f['mac'] )
    );

    open_fieldset('line', 'hardware life:' );
      echo label_element( $f['year_manufactured'], '', 'manufactured: '. int_element( $f['year_manufactured'] ) );
      echo label_element( $f['year_decommissioned'], '', 'decommissioned: ' . int_element( $f['year_decommissioned'] ) );
    close_fieldset();

    open_fieldset('line', 'cpu:' );
      echo label_element( $f['processor'], '', 'processor: '. string_element( $f['processor'] ) );
      echo label_element( $f['ram'], '', 'RAM: '. string_element( $f['ram'] ) );
    close_fieldset();

  close_fieldset();

  open_fieldset('', 'service:' );

    open_fieldset('line', span_view( 'bold', 'name:' ) );
      echo label_element( $f['hostname'], 'oneline', 'fqhostname: '. string_element( $f['hostname'] ) . ' . '. string_element( $f['domain' ] ) );
      echo label_element( $f['sequential_number'], '', '#: ', int_element( $f['sequential_number'] ) );
    close_fieldset();

    open_fieldset('line', 'service lifetime:' );
      echo label_element( $f['year_inservice'], '', 'in service: '.int_element( $f['year_inservice'] ) );
      echo label_element( $f['year_outservice'], '', 'out of service: '.int_element( $f['year_outservice'] ) );
    close_fieldset();

    open_fieldset('line'
    , label_element( $f['online'], '', 'status:' )
    , radiolist_element( $f['online'], 'choices=:offline:online' )
    );

    open_fieldset('line'
    , label_element( $f['os'], '', 'os: ' )
    , string_element( $f['os'] )
    );

    open_fieldset('line'
    , label_element( $f['ip4_t'], 'oneline', 'ip4: ' )
    , string_element( $f['ip4_t'] )
    );
    open_fieldset('line'
    , label_element( $f['ip6_t'], 'oneline', 'ip6: ' )
    , string_element( $f['ip6_t'] )
    );

    open_fieldset('line'
    , label_element( $f['location'], '', 'location: ' )
    , string_element( $f['location'] )
    );

    open_fieldset('line'
    , label_element( $f['description'], '', 'notes:' )
    , textarea_element( $f['description'] )
    );

  close_fieldset();

  open_div( 'right medskips' );
    if( $hosts_id && ! $f['_changes'] )
      echo template_button_view();
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view( $f['_changes'] ? '' : 'display=none' );
  close_div();


if( $hosts_id ) {

  open_fieldset( 'small_form', 'history hostname', 'on' );
    hostslist_view( array( 'fqhostname' => $host['fqhostname'] ), 'orderby=sequential_number' );
  close_fieldset();

  if( $host['invlabel'] ) {
    open_fieldset( 'small_form', 'history hardware', 'on' );
      hostslist_view( array( 'invlabel' => $host['invlabel'] ), 'orderby=fqhostname' );
    close_fieldset();
  }

  open_fieldset( 'toggle=on', 'disks' );
    diskslist_view( array( 'hosts_id' => $hosts_id ) );
  close_fieldset();

  open_fieldset( 'toggle=on', 'accounts' );
    accountslist_view( array( 'hosts_id' => $hosts_id ) );
  close_fieldset();

  open_fieldset( 'toggle=on', 'services' );
    serviceslist_view( array( 'hosts_id' => $hosts_id ) );
  close_fieldset();
}

close_fieldset();

?>
