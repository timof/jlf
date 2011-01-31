<?php

echo "<h1>tape chunks</h1>";

get_http_var( 'options', 'u', 0, true );

$filters = handle_filters( array( 'hosts_id', 'paths_id', 'tapes_id', 'backups_id', 'backupprofiles_id' ) );

handle_action( array( 'update', 'delete' ) );
switch( $action ) {
  case 'delete':
    need( $message > 0 );
    sql_delete_tapechunk( $message );
    break;
}

open_table('menu');
  open_tr();
    open_th('', "colspan='2'", 'filters' );
  open_tr();
    open_td('', '', 'hosts:' );
    open_td();
    open_select( 'hosts_id', '', html_options_hosts( $hosts_id, ' (all) ' ), 'reload' );
  open_tr();
    open_td('', '', 'path:' );
    open_td();
    open_select( 'locations_id', '', html_options_paths( $path_id, ' (all) ' ), 'reload' );
  open_tr();
    open_th('', "colspan='2'", 'actions' );
  open_tr();
    open_td( '', "colspan='2'", inlink( 'backupprofile', 'class=bigbutton,text=new backupprofile,backupprofiles_id=0' ) );
close_table();

bigskip();

tapechunkslist_view( $filters );


?>
