<?php // /pi/windows/position_edit.php

sql_transaction_boundary('*');

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'positions_id', 'global,type=u,sources=self http,set_scopes=self' );

need_priv( 'positions', $positions_id ? 'edit' : 'create', $positions_id );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {

  switch( $reinit ) {
    case 'init':
      $sources = 'http self initval default';
      break;
    case 'self':
      $sources = 'self initval default';  // need 'initval' here for big blobs!
      break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'initval default';
      break;
    default:
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'positions,init' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'positions'
  , 'failsafe' => false   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $positions_id ) {
    $position = sql_one_position( $positions_id );
    $opts['rows'] = array( 'positions' => $position );
  }

  $f = init_fields( array(
      'cn' => 'size=80'
    , 'note' => 'lines=10,cols=80'
    , 'url' => 'size=60'
    , 'programme_flags' => 'auto=1'
    , 'groups_id' => 'U'
    , 'contact_people_id' => 'U'
    , 'pdf' => 'set_scopes='
    )
  , $opts
  );
  $reinit = false;

  handle_actions( array( 'reset', 'save', 'init', 'template', 'deletePdf', 'deletePosition' ) ); 
  if( $action ) switch( $action ) {
    case 'template':
      $positions_id = 0;
      break;

    case 'save':
      if( ! $f['_problems'] ) {

        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' ) {
            if( $r['source'] !== 'initval' ) {
              $values[ $fieldname ] = $r['value'];
            }
          }
        }
        $error_messages = sql_save_position( $positions_id, $values, 'action=dryrun' );
        if( ! $error_messages ) {
          $positions_id = sql_save_position( $positions_id, $values, 'action=hard' );
          js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
          reinit('reset');
          $info_messages[] = we('entry was saved','Eintrag wurde gespeichert');
        }
      }
      break;

    case 'deletePdf':
      need( $positions_id );
      sql_update( 'positions', $positions_id, array( 'pdf' => '' ) );
      reinit('self');
      break;

  }

} // while $reinit


if( $positions_id ) {
  open_fieldset( 'old', we( 'Position / Thesis topic', 'Stelle / Thema' ) );
} else {
  open_fieldset( 'new', we( 'New position / topic', 'Neue Stelle / Thema' ) );
}
  open_fieldset( 'line'
  , label_element( $f['cn'], '', we('Title:','Titel:') )
  , string_element( $f['cn'] )
  );

  open_fieldset( 'line', label_element( $f['programme_flags'], '', we('Type / Degree (check all that apply):','Art / Abschluss (alle zutreffenden ankreuzen):') ) );
    $a = $f['programme_flags'];
    open_ul('plain');
      foreach( $programme_text as $programme_flags => $programme_cn ) {
        $a['mask'] = $programme_flags;
        $a['text'] = $programme_cn;
        open_li( '', checkbox_element( $a ) );
      }
    close_ul();
  close_fieldset();

  $filters = array();
  if( ! have_priv( 'positions', 'edit' ) ) {
    $filters['groups_id'] = $login_groups_ids;
  }
  open_fieldset( 'line'
  , label_element( $f['groups_id'], '', we('Group:','Gruppe:') )
  , selector_groups( $f['groups_id'], array( 'filters' => $filters ) )
  );

if( $f['groups_id']['value'] ) {
  open_fieldset( 'line'
  , label_element( $f['contact_people_id'], '', we('Contact:','Ansprechpartner:' ) )
  , selector_people( $f['contact_people_id'], array( 'filters' => array( 'groups_id' => $f['groups_id']['value'] ) ) )
  );
}

  open_fieldset( 'line'
  , label_element( $f['note'], '', we('Description:','Beschreibung:') )
  , textarea_element( $f['note'] )
  );

  open_fieldset( 'line'
  , label_element( $f['url'], 'td', 'Web link:' )
  , string_element( $f['url'] )
  );

if( $positions_id ) {
    if( $f['pdf']['value'] ) {
      open_fieldset( 'line', we('available document:', 'vorhandene Datei:' ) );
        // echo download_link( 'positions_pdf', $positions_id, 'class=file,text=download .pdf' );
        echo inlink( 'download', "item=positions_id,id=$positions_id,class=file,text=download .pdf" );
        quad();
        echo inlink( '', 'action=deletePdf,class=icon drop,title='.we('delete PDF','PDF löschen') );
      close_fieldset();
    } else {
      open_fieldset( 'line'
      , label_element( $f['pdf'], '', 'PDF upload:' )
      , file_element( $f['pdf'] )
      );
    }
}

  open_div('right bigskipt');
    if( $positions_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button qquads'
      , 'action' => 'deletePosition'
      , 'text' => we('delete topic/position','Thema/Stelle löschen')
      , 'confirm' => we('really delete?','wirklich löschen?')
      , 'inactive' => sql_delete_positions( $positions_id, 'action=dryrun' )
      ) );
      echo inlink( 'position_view', array(
        'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
      , 'positions_id' => $positions_id
      ) );
      if( have_priv('positions','create') ) {
        echo template_button_view();
      }
    }
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view();
  close_div();

close_fieldset();

if( $action === 'deletePosition' ) {
  need( $positions_id );
  sql_delete_positions( $positions_id, 'action=hard' );
  js_on_exit( "flash_close_message($H_SQ".we('position deleted','Stelle gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
