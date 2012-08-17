<?php

echo html_tag( 'h1', '', 'backupprofiles' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );
define( 'OPTION_DO_EDIT', 0x01 );

$f_fields = init_fields( array(
    'f_hosts_id' => 'u'
  , 'f_target' => array( 'type' => 'a1024', 'relation' => '~=', 'size' => '40' )
  )
, 'global'
);
$filters = & $fields['_filters'];

$f_backupjobs_id = init_var( 'backupjobs_id', 'global,type=u,cgi_name=backupjobs_id,sources=http persistent,set_scopes=window' );
if( $backupjobs_id ) {
  $backupjob = sql_backupjob( $backupjobs_id );
  $selected_profile = init_var( 'selected_profile', array(
    'type' => 'a128'
  , 'sources' => 'default'
  , 'set_scopes' => 'window'
  , 'default' => $backupjob['profile']
  , 'cgi_name' => 'selected_profile' // needed for select-mechanism in list view
  ) );
} else {
  $backupjob = array();
  $selected_profile = init_var( 'selected_profile', 'type=a128,sources=http persistent,set_scopes=window,cgi_name=selected_profile' );
}

$opts = array(
  'flag_problems' => & $flag_problems 
, 'flag_modified' => & $flag_modified
, 'tables' => 'backupjobs'    // db tables to check for patterns and defaults
, 'rows' => array( 'backupjobs' => $backupjob )
, 'failsafe' => false
);
if( $action === 'save' ) {
  $flag_problems = 1;
}
if( $action === 'reset' ) {
  $opts['reset'] = 1;
  $flag_problems = 0;
}
$fields = init_fields(
  array( 'profile' => 'size=20'
  , 'keyname' => 'size=40,type=A128'
  , 'keyhashfunction' => 'size=10'
  , 'keyhashvalue' => 'size=20'
  , 'cryptcommand' => 'size=40'
  , 'hosts_id' => 'type=U,default=' . $f_hosts_id
  , 'target' => 'type=A1024,size=40,default=' . $f_target
  )
, $opts
);

handle_action( array( 'update', 'deleteBackupjob', 'save' ) );
switch( $action ) {
  case 'deleteBackupjob':
    need( $message > 0 );
    sql_delete_backupjobs( $message );
    break;
  case 'save':
    if( ! $fields['_problems'] ) {
      $values = array();
      foreach( $fields as $fieldname => $r ) {
        if( $fieldname[ 0 ] !== '_' )
          $values[ $fieldname ] = $fields[ $fieldname ]['value'];
      }
      sql_save_backupjob( $backupjobs_id, $values );
    }
}

// debug( $fields['keyhashvalue'] );

open_table( 'menu' );
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'host:' );
    open_td();
      filter_host( $f_fields['f_hosts_id'] );
  open_tr();
    open_td( '', 'target:' );
    open_td();
      echo string_element( $f_fields['f_target'] );
  open_tr();
    open_th( 'colspan=2', 'actions' );
  if( ! ( $options & OPTION_DO_EDIT ) ) {
    open_tr();
      open_td( '', inlink( '', 'class=bigbutton,text=new job,options=' . ( $options | OPTION_DO_EDIT ) ) );
  }
close_table();

if( $options & OPTION_DO_EDIT ) {

  bigskip();
  if( $backupjobs_id ) {
    open_fieldset( 'small_form old', "edit job [$backupjobs_id]" );
  } else  {
    open_fieldset( 'small_form new', 'new job' );
  }
    open_table();
      open_tr();
        open_td( array( 'label' => $fields['profile'] ), 'profile:' );
        open_td( '', string_element( $fields['profile'] ) );

      open_tr();
        open_td( array( 'label' => $fields['hosts_id'] ), 'host:' );
        open_td();
          selector_host( $fields['hosts_id'] );

      open_tr();
        open_td( array( 'label' => $fields['target'] ), 'target:' );
        open_td( '', string_element( $fields['target'] ) );

      open_tr();
        open_td( array( 'label' => $fields['keyname'] ), 'keyname:' );
        open_td( '', string_element( $fields['keyname'] ) );

      open_tr();
        open_td( array( 'label' => $fields['keyhashfunction'] ), 'hash function:' );
        open_td();
          open_span( 'quadr', string_element( $fields['keyhashfunction'] ) );
          open_label( $fields['keyhashvalue'], 'value:' );
          echo string_element( $fields['keyhashvalue'] );

      open_tr();
        open_td( array( 'label' => $fields['cryptcommand'] ), 'crypt command:' );
        open_td( '', string_element( $fields['cryptcommand'] ) );

      open_tr();
        open_td( 'right,colspan=2' );
        if( $backupjobs_id && ! $fields['_changes'] )
          template_button();
        echo inlink( '', 'class=button,text=abort,options=' . ( $options & ~OPTION_DO_EDIT ) );
        submission_button();
    close_table();
  close_fieldset();
  bigskip();
}



backupprofileslist_view( $filters, array( 'select' => $selected_profile ) );

if( $selected_profile['value'] ) {
  backupjobslist_view( array( 'profile' => $selected_profile['value'] ), array( 'select' => $f_backupjobs_id ) );
}

?>
