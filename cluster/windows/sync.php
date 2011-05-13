<?php

echo "<h1>sync</h1>";
$editable = true;

$ldap_users = ldap_users();
$ldap_hosts = ldap_entries( 'ou=hosts,' . LDAP_BASEDN, array( 'objectclass' => 'physikhost' ) );

handle_action( array( 'sync_users', 'sync_accountdomains' ) );
switch( $action ) {

  case 'sync_users':
    doSql( 'DELETE FROM users WHERE true' );

    break;

  case 'sync_accountdomains':
    doSql( 'DELETE FROM accountdomains WHERE true' );
    doSql( 'DELETE FROM accountdomains_hosts_relation WHERE true' );
    doSql( 'DELETE FROM accountdomains_users_relation WHERE true' );

    break;

  default:
  case '':
  case 'nop':
    break;
}


open_table( 'list' );

open_tr();
  open_th( 'left', "colspan='2'", 'users:' );

  for( $n = 0; $n < $ldap_users['count'] ; $n++ ) {
    open_tr();
    $user = $ldap_users[$n];
    // prettydump( $user );
    // exit(1);
    $hosts_id = 0;
    if( isset( $user['physikautomountinformation'] ) ) {
      $i = $user['physikautomountinformation'][0];
      $i = preg_replace( '/^.* /', '', $i );
      $fqhostname = preg_replace( '&:/.*$&', '', $i );
      $host = sql_host( array( 'fqhostname' => $fqhostname ), true );
      if( $host )
        $hosts_id = $host['hosts_id'];
    }
    $users_id = sql_insert( 'users', array(
      'uid' => $user['uid'][0]
    , 'uidnumber' => $user['uidnumber'][0]
    , 'cn' => $user['cn'][0]
    , 'hosts_id' => $hosts_id
    ) );
    open_td( '', '', $user['uid'][0] );
    open_td();
    if( isset( $user['accountdomain'] ) ) {
      for( $i = 0; $i < $user['accountdomain']['count'] ; $i++ ) {
        $a = $user['accountdomain'][$i];
        $id = sql_insert( 'accountdomains', array( 'accountdomain' => $a ), true );
        sql_insert( 'accountdomains_users_relation', array( 'users_id' => $users_id, 'accountdomains_id' => $id ) );
        echo " $a ";
      }
    }
  }

open_tr();
  open_th( '', "colspan='2'", 'hosts:' );

  for( $n = 0; $n < $ldap_hosts['count'] ; $n++ ) {
    open_tr();
    $host = $ldap_hosts[$n];
    $fqhostname = $host['cn'][0];
    open_td( '', '', $fqhostname );
    $sql_hosts = sql_hosts( array( 'fqhostname' => $fqhostname ) );
    if( ! $sql_hosts ) {
      open_tr( 'alert', '', 'not in sql table' );
      continue;
    }
    open_td();
    $sql_host = current( $sql_hosts );
    if( isset( $host['accountdomain'] ) ) {
      for( $i = 0; $i < $host['accountdomain']['count'] ; $i++ ) {
        $a = $host['accountdomain'][$i];
        $id = sql_insert( 'accountdomains', array( 'accountdomain' => $a ), true );
        sql_insert( 'accountdomains_hosts_relation', array( 'hosts_id' => $sql_host['hosts_id'], 'accountdomains_id' => $id ) );
        echo " $a ";
      }
    }
  }



close_table();


bigskip();

open_table( 'menu' );
  if( $action ) {
    open_tr();
    open_td( '', '', inlink( '', 'class=bigbutton', 'text=reload' ) );
  } else {
    open_tr();
      open_th( '', "colspan='2'", 'sync actions' );
  }
close_table( 'menu' );

?>
