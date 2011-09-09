<?php

echo html_tag( 'h1', '', 'backup chunks' );

init_var( 'options', 'global,pattern=u,sources=http persistent,default=0,set_scopes=window' );

$fields = prepare_filters( 'hosts_id,tapes_id' );
$filters = $fields['_filters'];

handle_action( array( 'update', 'deleteBackupchunk' ) );
switch( $action ) {
  case 'deleteBackupchunk':
    need( $message > 0 );
    sql_delete_backupchunks( $message );
    break;
}

open_table( 'menu' );
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'hosts:' );
    open_td();
      filter_host( $fields['hosts_id'] );
  open_tr();
    open_td( '', 'path:' );
    open_td();
      // filter_path();
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'backupchunk', 'class=bigbutton,text=new backupchunk,backupchunks_id=0' ) );
close_table();

bigskip();

backupchunkslist_view( $filters );


?>
