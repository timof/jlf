<?php

echo html_tag( 'h1', '', 'backupprofiles' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

init_var( 'profile', 'global,sources=http persistent,set_scopes=self' );

$fields = init_fields( array(
    'hosts_id' => 'u'
  , 'target' => array( 'type' => 'a1024', 'relation' => '~=', 'size' => '40' )
) );
$filters = & $fields['_filters'];

handle_action( array( 'update', 'deleteBackupjob', 'addBackupjob' ) );
switch( $action ) {
  case 'deleteBackupjob':
    need( $message > 0 );
    sql_delete_backupjobs( $message );
    break;
  case 'addBackupjob':
    init_var( 'new_hosts_id', 'global,type=u' );
    init_var( 'new_target', 'global,type=a1024' );
    need( sql_hosts( $new_hosts_id ), 'no such host' );
    sql_save_backupjob( 0, array(
      'hosts_id' => $new_hosts_id
    , 'target' => $new_target
    ) );
}

open_table( 'menu' );
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'host:' );
    open_td();
      filter_host( $fields['hosts_id'] );
  open_tr();
    open_td( '', 'target:' );
    open_td();
      echo string_element( $fields['target'] );
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'backupprofile', 'class=bigbutton,text=new backupprofile,backupprofiles_id=0' ) );
close_table();

bigskip();

backupprofileslist_view( $filters );

if( $profile ) {
  backupjobslist_view( array( 'profile' => $profile ) );
}

?>
