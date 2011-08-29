<?php

echo html_tag( 'h1', '', 'backups' );

init_global_var( 'options', 'u', 'http,persistent', 0, 'window' );

$filters = handle_filters( array( 'hosts_id', 'paths_id', 'backupprofiles_id', 'typeoftapes_id', 'tapes_id' ) );

handle_action( array( 'update', 'deleteBackup' ) );
switch( $action ) {
  case 'deleteBackup':
    need( $message > 0 );
    sql_delete_backup( $message );
    break;
}

open_table( 'menu' );
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'host:' );
    open_td();
      filter_host();
  open_tr();
    open_td( '', 'path:' );
    open_td();
      filter_location();
  open_tr();
    open_td( '', 'profile:' );
    open_td();
      filter_backupprofiles();
  open_tr();
    open_td( '', 'tape type:' );
    open_td();
      filter_type_tape();
if( $type_tape ) {
  open_tr();
    open_td( '', 'tape:' );
    open_td();
      filter_tape();
}
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'backup', 'class=bigbutton,text=new backup,backups_id=0' ) );
close_table();

bigskip();

backupslist_view( $filters, true, 'backups_id' );

init_global_var( 'backups_id', 'u', 'http,persistent', 0, 'self' );
if( $backups_id ) {
  backupchunkslist_view( "backups_id=$backups_id" );
}

?>
