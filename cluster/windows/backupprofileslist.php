<?php

echo html_tag( 'h1', '', 'backupprofiles' );

init_global_var( 'options', 'u', 'http,persistent', 0, 'window' );

$filters = handle_filters( array( 'hosts_id', 'paths_id' ) );

handle_action( array( 'update', 'deleteBackupprofile' ) );
switch( $action ) {
  case 'deleteBackupprofile':
    need( $message > 0 );
    sql_delete_backupprofile( $message );
    break;
}

open_table( 'menu' );
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'hosts:' );
    open_td();
      filter_host();
  open_tr();
    open_td( '', 'path:' );
    open_td();
      filter_path();
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'backupprofile', 'class=bigbutton,text=new backupprofile,backupprofiles_id=0' ) );
close_table();

bigskip();

backupprofileslist_view( $filters, true, 'backupprofiles_id' );

init_global_var( 'backupprofiles_id', 'u', 'http,persistent', 0, 'self' );
if( $backupprofiles_id ) {
  backupslist_view( "backupprofiles_id=$backupprofiles_id" );
}

?>
