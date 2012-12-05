<?php

echo html_tag( 'h1', '', 'backupprofiles' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
define( 'OPTION_DO_EDIT', 0x01 );

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );

$f_fields = init_fields( array(
    'F_host_current_tri' => 'u1,auto=1'
  , 'F_hosts_id' => 'u'
  , 'F_profile' => 'type=a1024,default=0'
  , 'F_targets' => array( 'type' => 'a1024', 'relation' => '~=', 'size' => '40' )
  )
, 'global,set_scopes=self'
);
$filters = & $f_fields['_filters'];

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {
  $problems = array();
  // debug( $reinit, 'reinit' );

  switch( $reinit ) {
    case 'init':
      $sources = 'http self init default';
      break;
    case 'self':
      $sources = 'self init default';  // need 'init' here for big blobs!
      break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'init default';
      break;
    default:
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'person,init' );
  }

  $f_backupjobs_id = init_var( 'backupjobs_id', 'global,type=u,cgi_name=backupjobs_id,sources=http persistent,set_scopes=self' );

  $backupjob = array();
  if( $backupjobs_id ) {
    $backupjob = sql_one_backupjob( $backupjobs_id, array() );
    if( ! $backupjob ) {
      $backupjobs_id = 0;
    }
  }

  $opts = array(
    'flag_problems' => & $flag_problems 
  , 'flag_modified' => 1
  , 'tables' => 'backupjobs'    // db tables to check for patterns and defaults
  , 'rows' => array( 'backupjobs' => $backupjob )
  , 'sources' => $sources
  , 'failsafe' => 0
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  $fields = init_fields(
    array( 'profile' => array( 'size' => '20', 'uid_choices' => choices_backupprofiles() )
    , 'keyname' => 'size=60'
    , 'keyhashfunction' => 'size=6'
    , 'keyhashvalue' => 'size=48'
    , 'cryptcommand' => 'size=60'
    , 'hosts_id' => 'default=' . ( $f_fields['F_hosts_id']['value'] ? $f_fields['F_hosts_id']['value'] : '' )
    , 'targets' => array( 'size' => 60, 'default' => ( $f_fields['F_targets']['value'] ? $f_fields['F_targets']['value'] : '' ) )
    )
  , $opts
  );

  // debug( $opts, 'opts' );
  // debug( $fields, 'fields' );
  $reinit = false;

  handle_action( array( 'update', 'deleteBackupjob', 'save', 'template', 'reset' ) );
  switch( $action ) {
    case 'template':
      $backupjobs_id = 0;
      reinit('self');
      break;
    case 'deleteBackupjob':
      need( $message > 0 );
      sql_delete_backupjobs( $message );
      $backupjobs_id = 0;
      reinit('self');
      break;
    case 'save':
      if( ! $fields['_problems'] ) {
        $values = array();
        foreach( $fields as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            $values[ $fieldname ] = $fields[ $fieldname ]['value'];
        }
        if( ! ( $problems = sql_save_backupjob( $backupjobs_id, $values, 'check' ) ) ) {
          $backupjobs_id = sql_save_backupjob( $backupjobs_id, $values );
          need( isnumber( $backupjobs_id ) && ( $backupjobs_id > 0 ) );
          reinit('reset');
        }
      }
  }
}


open_table( 'menu' );
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'profile:' );
    open_td();
      echo filter_backupprofile( $f_fields['F_profile'] );
  open_tr();
    open_td( '', 'host:' );
    open_td();
      open_div('oneline smallskipb');
        open_span( 'qquadr', radiobutton_element( $f_fields['F_host_current_tri'], 'value=1,text=current' ) );
        open_span( 'qquadr', radiobutton_element( $f_fields['F_host_current_tri'], 'value=2,text=outdated' ) );
        open_span( 'qquadr', radiobutton_element( $f_fields['F_host_current_tri'], 'value=0,text=both' ) );
      close_div();
      open_div();
        filter_host( $f_fields['F_hosts_id'], array( 'filters' => parameters_explode( $filters, 'keep=host_current_tri' ) ) );
      close_div();
  open_tr();
    open_td( '', 'targets:' );
    open_td();
      echo string_element( $f_fields['F_targets'] );
  open_tr();
    open_th( 'colspan=2', 'actions' );
  if( ! ( $options & OPTION_DO_EDIT ) ) {
    open_tr();
      open_td( 'colspan=2', inlink( '', 'class=bigbutton,text=new job,options=' . ( $options | OPTION_DO_EDIT ) ) );
  }
close_table();

if( $options & OPTION_DO_EDIT ) {

  bigskip();
  if( $backupjobs_id ) {
    open_fieldset( 'small_form old,style=display:inline;', "edit job [$backupjobs_id]" );
  } else  {
    open_fieldset( 'small_form new', 'new job' );
   }
    flush_problems();
    open_table();
      open_tr();
        open_td( array( 'label' => $fields['hosts_id'] ), 'host:' );
        open_td();
          selector_host( $fields['hosts_id'] );

      open_tr();
        open_td( array( 'label' => $fields['profile'] ), 'profile:' );
        open_td( '', string_element( $fields['profile'] ) );

      open_tr();
        open_td( array( 'label' => $fields['targets'] ), 'targets:' );
        open_td( '', string_element( $fields['targets'] ) );

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
}

bigskip();

// open_div( 'medskips' );
//   echo "selected: ";
//   echo $selected_profile['value'] ? $selected_profile['value'] : '-';
//   echo ' / ';
//   if( $backupjobs_id ) {
//     echo inlink( '', array( 'options' => $options | OPTION_DO_EDIT, 'text' => $backupjobs_id, 'class' => 'edit' ) );
//   } else {
//     echo '-';
//   }
// close_div();
//
// backupprofileslist_view( $filters, array( 'select' => $selected_profile ) );

backupjobslist_view( $filters );

?>
