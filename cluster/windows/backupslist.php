<?php

echo "<h1>backups</h1>";

init_global_var( 'options', 'u', 'http,persistent', 0, 'window' );

$filters = handle_filters( array( 'hosts_id', 'paths_id', 'backupprofiles_id', 'typeoftapes_id', 'tapes_id' ) );

handle_action( array( 'update', 'deleteBackup' ) );
switch( $action ) {
  case 'deleteBackup':
    need( $message > 0 );
    sql_delete_backup( $message );
    break;
}

open_table('menu');
  open_tr();
    open_th('', "colspan='2'", 'filters' );
  open_tr();
    open_td('', '', 'host:' );
    open_td();
    open_select( 'hosts_id', '', html_options_hosts( $hosts_id, ' (all) ' ), 'reload' );
  open_tr();
    open_td('', '', 'path:' );
    open_td();
    open_select( 'locations_id', '', html_options_paths( $path_id, ' (all) ' ), 'reload' );
  open_tr();
    open_td('', '', 'profile:' );
    open_td();
    open_select( 'hosts_id', '', html_options_backupprofiles( $backupprofiles_id, ' (all) ' ), 'reload' );
  open_tr();
    open_td('', '', 'tape type:' );
    open_td();
    open_select( 'hosts_id', '', html_options_typeoftapes( $typeoftapes_id, ' (all) ' ), 'reload' );
if( $typeoftapes_id ) {
  open_tr();
    open_td('', '', 'tape:' );
    open_td();
    open_select( 'hosts_id', '', html_options_tapes( $tapes_id, array( 'typeoftapes_id' => $typeoftapes_id ), ' (all) ' ), 'reload' );
}
  open_tr();
    open_th('', "colspan='2'", 'actions' );
  open_tr();
    open_td( '', "colspan='2'", inlink( 'backup', 'class=bigbutton,text=new backup,backups_id=0' ) );
close_table();

bigskip();

backupslist_view( $filters, true, 'backups_id' );

init_global_var( 'backups_id', 'u', 'http,persistent', 0, 'self' );
if( $backups_id ) {
  backupchunkslist_view( "backups_id=$backups_id" );
}

?>
