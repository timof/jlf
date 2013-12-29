<?php

sql_transaction_boundary('*');
echo html_tag( 'h1', '', 'backup chunks' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields( array(
  'hosts_id'
, 'tapes_id'
, 'type_tape'
, 'targets' => 'type=a1024,relation=~,size=40'
) );
$filters = $fields['_filters'];

handle_actions( array( 'deleteBackupchunk' ) );
if( $action ) switch( $action ) {
  case 'deleteBackupchunk':
    need( $message > 0 );
    sql_delete_backupchunks( $message );
    break;
}

open_div( 'menubox' );
  open_table('css filters');
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
    open_tr();
      open_td( '', 'hosts:' );
      open_td( '', filter_host( $fields['hosts_id'] ) );
    open_tr();
      open_td( '', 'targets:' );
      open_td( '', '/'.string_element( $fields['targets'] ).'/' );
    open_tr();
      open_th( 'colspan=2', 'actions' );
    open_tr();
      open_td( 'colspan=2', inlink( 'backupchunk', 'class=bigbutton,text=new backupchunk,backupchunks_id=0' ) );
  close_table();
close_div();

backupchunkslist_view( $filters );

?>
