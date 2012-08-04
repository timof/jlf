<?php

echo html_tag( 'h1', '', 'backupprofiles' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$f_fields = init_fields( array(
    'f_hosts_id' => 'u'
  , 'f_target' => array( 'type' => 'a1024', 'relation' => '~=', 'size' => '40' )
) );
$filters = & $fields['_filters'];

$fields = init_fields(
  array( 'profile', 'target', 'hosts_id', 'keyname', 'keyhashfunction', 'keyhashvalue', 'cryptcommand' )
, array( 'tables' => 'backupjobs' )
);

$selected_profile = init_var( 'selected_profile', 'a128,sources=http persistent,set_scopes=window' );
$selected_job = init_var( 'backup_jobs_id', 'u,sources=http persistent,set_scopes=window' );

handle_action( array( 'update', 'deleteBackupjob', 'addBackupjob' ) );
switch( $action ) {
  case 'deleteBackupjob':
    need( $message > 0 );
    sql_delete_backupjobs( $message );
    break;
  case 'saveBackupjob':
    if( ! $fields['_problems'] ) {
      need( sql_hosts( $new_hosts_id ), 'no such host' );
      sql_save_backupjob( 0, array(
        'hosts_id' => $hosts_id
      , 'target' => $target
      , 'profile' => $profile
      , 'keyname' => $keyname
      , 'keyhashfunction' => $keyhashfunction
      , 'keyhashvalue' => $keyhashvalue
      , 'cryptcommand' => $keyhashvalue
      ) );
    }
}

open_table( 'menu' );
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'host:' );
    open_td();
      filter_host( $fields['f_hosts_id'] );
  open_tr();
    open_td( '', 'target:' );
    open_td();
      echo string_element( $fields['f_target'] );
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'backupprofile', 'class=bigbutton,text=new backupprofile,backupprofiles_id=0' ) );
close_table();

bigskip();
open_fieldset( 'small_form', 'new backupjob', 'off' );
  open_table();
    open_tr();
      open_td( array( 'label' => $fields['profile'] ), 'profile:' );
      open_td( '', string_element( $fields['profile'] ) );

    open_tr();
      open_td( array( 'label' => $fields['target'] ), 'target:' );
      open_td( '', string_element( $fields['target'] ) );

  close_table();
close_fieldset();
bigskip();

backupprofileslist_view( $filters, array( 'select' => $selected_profile ) );

if( $selected_profile['value'] ) {
  backupjobslist_view( array( 'profile' => $selected_profile['value'] ), array( 'select' => $selected_job ) );
}

?>
