<?php // /pi/windows/room_edit.php

sql_transaction_boundary('*');

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'modules_id', 'global,type=u,sources=self http,set_scopes=self' );

need_priv( 'modules', $modules_id ? 'edit' : 'create', $modules_id );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {

  switch( $reinit ) {
    case 'init':
      $sources = 'http self initval default';
      break;
    case 'self':
      $sources = 'self default';
      break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'initval default';
      break;
    default:
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'modules,init' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'modules'
  , 'failsafe' => false   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $modules_id ) {
    $module = sql_one_module( $modules_id );
    $opts['rows'] = array( 'modules' => $module );
  }

  $fields = init_fields( array(
      'tag' => 'size=20'
    , 'cn' => 'size=80'
    , 'programme_flags' => 'u,auto=1'
    , 'note' => 'lines=40,cols=80'
    , 'contact_people_id' => 'u'
    )
  , $opts
  );
  $fields_contact = array(
    'contact_groups_id' => array( 'type' => 'U', 'basename' => 'groups_id' )
  , 'contact_people_id' => array( 'type' => 'U' ,'basename' => 'people_id' )
  );
  $opts['merge'] = & $fields;
  $f = filters_person_prepare( $fields_contact, $opts );

  $reinit = false;

  handle_actions( array( 'reset', 'save', 'init', 'template', 'deleteModule' ) ); 
  switch( $action ) {
    case 'template':
      $modules_id = 0;
      break;

    case 'save':
      if( $f['_problems'] ) {
        $error_messages[] = we('saving failed','Speichern fehlgeschlagen' );
      } else {

        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' ) {
            if( $r['source'] !== 'initval' ) { // no need to write existing blob
              $values[ $fieldname ] = $r['value'];
            }
          }
        }

        $error_messages = sql_save_module( $modules_id, $values, 'action=dryrun' );
        if( ! $error_messages ) {
          $modules_id = sql_save_module( $modules_id, $values, 'action=hard' );
          js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
          $info_messages[] = we('entry was saved','Eintrag wurde gespeichert');
          reinit('reset');
        }
      }
      break;

  }

} // while $reinit


if( $modules_id ) {
  open_fieldset( 'old', we( 'Module', 'Modul' ) );
} else {
  open_fieldset( 'new', we( 'New module', 'Neues Modul' ) );
}
  open_fieldset( 'line'
  , label_element( $f['tag'], '', we('Module:','Modul:') )
  , string_element( $f['tag'] )
  );

  open_fieldset( 'line'
  , label_element( $f['cn'], '', we('Title:','Titel:') )
  , string_element( $f['cn'] )
  );

  open_fieldset( 'line', label_element( $f['programme_flags'], '', we('Programme (check all that apply):','Studiengang (alle zutreffenden ankreuzen):') ) );
    $a = $f['programme_flags'];
    open_ul('plain');
      foreach( $programme_text as $programme_flags => $programme_cn ) {
        $a['mask'] = $programme_flags;
        $a['text'] = $programme_cn;
        open_li( '', checkbox_element( $a ) );
      }
    close_ul();
  close_fieldset();

  open_fieldset( 'line', we('Contact:','Modulverantwortlicher:') );
    open_span('oneline', label_element( $f['contact_groups_id'], '', we('group:', 'Gruppe:') ) . selector_groups( $f['contact_groups_id'] ) );
    if( ( $cgi = $f['contact_groups_id']['value'] ) ) {
      open_span('oneline', label_element( $f['contact_people_id'], ''
      , we('person:','Person:') . selector_people( $f['contact_people_id'], "filters=groups_id=$cgi,flag_deleted=0,flag_publish" )
      ) );
    }
  close_fieldset();

menatwork();
if( $f['groups_id']['value'] ) {
  open_fieldset( 'line qquadl'
  , label_element( $f['contact_people_id'], '', we('responsible person:','verantwortliche Person:' ) )
  , selector_people( $f['contact_people_id'], array( 'filters' => array( 'groups_id' => $f['groups_id']['value'] ), 'office' => 1 ) )
  );

}

  open_fieldset( 'line'
  , label_element( $f['note'], '', we('Notes:','Hinweise:') )
  , textarea_element( $f['note'] )
  );

  open_div('right bigskipt');
    if( $modules_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button qquads'
      , 'action' => 'deleteModule'
      , 'text' => we('delete module','Modul löschen')
      , 'confirm' => we('really delete?','wirklich löschen?')
      , 'inactive' => sql_delete_modules( $modules_id, 'action=dryrun' )
      ) );
      echo inlink( 'module_view', array(
        'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
      , 'modules_id' => $modules_id
      ) );
      if( have_priv('modules','create') ) {
        echo template_button_view();
      }
    }
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view();
  close_div();

close_fieldset();

if( $action === 'deleteModule' ) {
  need( $modules_id );
  sql_delete_modules( $modules_id, 'action=hard' );
  js_on_exit( "flash_close_message($H_SQ".we('module deleted','Modul gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
