<?php

sql_transaction_boundary('*');
echo html_tag( 'h1', '', 'backups' );

menatwork();

init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window' );

$fields = init_fields( 'hosts_id,paths_id,profile,typeoftapes_id,tapes_id' );
$filters = & $fields['_filters'];

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
    open_td( '', filter_host( $fields['hosts_id'] ) );
  open_tr();
    open_td( '', 'path:' );
    open_td( '', filter_location( $fields['locations_id'] ) );
  open_tr();
    open_td( '', 'profile:' );
    open_td( '', filter_backupprofiles( $fields['profile'] ) );
  open_tr();
    open_td( '', 'tape type:' );
    open_td( '', filter_type_tape( $fields['type_tape'] ) );
if( $type_tape ) {
  open_tr();
    open_td( '', 'tape:' );
    open_td( '', filter_tape( $fields['tapes_id'] ) );
}
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'backup', 'class=bigbutton,text=new backup,backups_id=0' ) );
close_table();

bigskip();

backupslist_view( $filters, true, 'backups_id' );

init_var( 'backups_id', 'global,type=u,sources=http persistent,set_scopes=self' );
if( $backups_id ) {
  backupchunkslist_view( "backups_id=$backups_id" );
}

?>
