<?php

init_var( 'backupchunks_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
need( $backupchunks_id, 'no backupchunk selected' );

$backupchunk = sql_one_backupchunk( $backupchunks_id );
$oid_t = $backupchunk['oid_t'] = oid_canonical2traditional( $backupchunk['oid'] );

handle_action( array( 'update' ) );

switch( $action ) {


} 

open_fieldset( 'small_form old', "backupchunk [$backupchunks_id]" );

  open_table( 'hfill,colgroup=30% 70%' );
    open_tr();
      open_td( 'label', 'oid:' );
      open_td( '', string_view( $backupchunk['oid_t'] ) );

    open_tr();
      open_td( 'label', 'oid:' );
      open_td( '', string_view( $backupchunk['backuplabel'] ) );

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
      open_td( 'colspan=2' );
        printf( 'contains %d chunklabels:', $backupchunk['labels_count'] );
        chunklabelslist_view( "backupchunks_id=$backupchunks_id" );

    open_tr();
      open_td( 'colspan=2' );
        printf( 'stored in %d copies:', $backupchunk['copies_count'] );
        tapechunkslist_view( "backupchunks_id=$backupchunks_id" );

  close_table();
close_fieldset();

?>
