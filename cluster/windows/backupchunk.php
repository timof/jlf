<?php

sql_transaction_boundary('*');
init_var( 'backupchunks_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
init_var( 'flag_problems', 'type=u,sources=persistent,default=0,global,set_scopes=self' );

need( $backupchunks_id, 'no backupchunk selected' ); // makes no sense to 

$backupchunk = sql_one_backupchunk( $backupchunks_id );
$oid_t = $backupchunk['oid_t'] = oid_canonical2traditional( $backupchunk['oid'] );
$copies_count = $backupchunk['copies_count'];

$actions = array();
if( $copies_count < 1 ) {
  $actions[] = 'delete';
}

handle_actions( $actions );
if( $action ) switch( $action ) {


} 


open_fieldset( 'small_form old', "backupchunk [$backupchunks_id]" );

  open_table( 'hfill,colgroup=30% 70%' );
    open_tr();
      open_td( 'label', 'oid:' );
      open_td( '', string_view( $oid_t ) );

    open_tr();
      open_td( 'label', 'sizeGB:' );
      open_td( '', string_view( $backupchunk['sizeGB'] ) );

    open_tr();
      open_td( 'label', 'clearhash:' );
      open_td( '', string_view( sprintf( "{%s}%s", $backupchunk['clearhashfunction'], $backupchunk['clearhashvalue'] ) ) );

    open_tr();
      open_td( 'label', 'crypthash:' );
      open_td( '', string_view( sprintf( "{%s}%s", $backupchunk['crypthashfunction'], $backupchunk['crypthashvalue'] ) ) );

    open_tr();
      open_td( 'label', 'host:' );
      open_td( '', html_alink_host( $backupchunk['hosts_id'] ) );

    open_tr();
      open_td( 'label', 'targets:' );
      open_td( '', string_view( $backupchunk['targets'] ) );

    open_tr();
      open_td( 'colspan=2' );
        printf( 'stored in %d copies:', $backupchunk['copies_count'] );

  close_table();
  if( $copies_count < 1 ) {
    open_div( 'warn oneline' );
      echo "orphaned chunk - no copies on tape" . qquad();
      echo inlink( '', "class=drop button,text=delete chunk,action=delete,backupchunks_id=$backupchunks_id" );
    close_div();
  } else {
    open_div();
      open_span( 'oneline', "stored in $copies_count copies:" );
      tapechunkslist_view( "backupchunks_id=$backupchunks_id" );
    close_div();
  }

close_fieldset();

?>
