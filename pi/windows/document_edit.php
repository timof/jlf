<?php // /pi/windows/document_edit.php

sql_transaction_boundary('*');

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'documents_id', 'global,type=u,sources=self http,set_scopes=self' );

need_priv( 'documents', $documents_id ? 'edit' : 'create', $documents_id );

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
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'documents,init' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'documents'
  , 'failsafe' => false   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $documents_id ) {
    $document = sql_one_document( $documents_id );
    $opts['rows'] = array( 'documents' => $document );
  }

  $f = init_fields( array(
      'cn_de' => 'size=80'
    , 'cn_en' => 'size=80'
    , 'note' => 'lines=4,cols=80'
    , 'tag' => 'size=20'
    , 'programme_id' => 'auto=1'
    , 'type' 
    , 'pdf' => 'set_scopes='
    )
  , $opts
  );
  $reinit = false;

  handle_action( array( 'save', 'template', 'deletePdf', 'deletedocument' ) ); 

  switch( $action ) {
    case 'template':
      $documents_id = 0;
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
        $error_messages = sql_save_document( $documents_id, $values, 'action=dryrun' );
        if( ! $error_messages ) {
          $documents_id = sql_save_document( $documents_id, $values, 'action=hard' );
          js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
          reinit('reset');
          $info_messages[] = we('entry was saved','Eintrag wurde gespeichert');
        }
      }
      break;

    case 'deletePdf':
      need( $documents_id );
      sql_update( 'documents', $documents_id, array( 'pdf' => '' ) );
      reinit('self');
      break;

  }

} // while $reinit


if( $documents_id ) {
  open_fieldset( 'old', we( 'document', 'Datei' ) );
} else {
  open_fieldset( 'new', we( 'New document', 'Neue Datei' ) );
}
  flush_all_messages();

  

  open_fieldset( 'line'
  , label_element( $f['cn_de'], '', 'Bezeichnung (deutsch)' )
  , string_element( $f['cn_de'] )
  );
  open_fieldset( 'line'
  , label_element( $f['note_de'], '', 'Beschreibung (deutsch)' )
  , textarea_element( $f['note_de'] )
  );

  open_fieldset( 'line'
  , label_element( $f['cn_en'], '', 'Name (english)' )
  , string_element( $f['cn_en'] )
  );
  open_fieldset( 'line'
  , label_element( $f['note_en'], '', 'Description (english)' )
  , textarea_element( $f['note_en'] )
  );

  open_fieldset( 'line', label_element( $f['programme_id'], '', we('relevant for (check all that apply):','relevant für (alle zutreffenden ankreuzen):') ) );
    $a = $f['programme_id'];
    foreach( $programme_text as $programme_id => $programme_cn ) {
      $a['mask'] = $programme_id;
      $a['text'] = $programme_cn;
      open_span( 'quadr', checkbox_element( $a ) );
    }
  close_fieldset();


  open_fieldset( 'line'
  , label_element( $f['note'], '', we('Description:','Beschreibung:') )
  , textarea_element( $f['note'] )
  );

  open_fieldset( 'line'
  , label_element( $f['url'], 'td', 'Web link:' )
  , string_element( $f['url'] )
  );

if( $documents_id ) {
    if( $f['pdf']['value'] ) {
      open_fieldset( 'line', we('available document:', 'vorhandene Datei:' ) );
        // echo download_link( 'documents_pdf', $documents_id, 'class=file,text=download .pdf' );
        echo inlink( 'download', "item=documents_id,id=$documents_id,class=file,text=download .pdf" );
        quad();
        echo inlink( '', 'action=deletePdf,class=drop,title='.we('delete PDF','PDF löschen') );
      close_fieldset();
    } else {
      open_fieldset( 'line'
      , label_element( $f['pdf'], '', 'PDF upload:' )
      , file_element( $f['pdf'] )
      );
    }
}

  open_div('right bigskipt');
    if( $documents_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button qquads'
      , 'action' => 'deletedocument'
      , 'text' => we('delete topic/document','Thema/Stelle löschen')
      , 'confirm' => we('really delete?','wirklich löschen?')
      , 'inactive' => sql_delete_documents( $documents_id, 'action=dryrun' )
      ) );
      echo inlink( 'document_view', array(
        'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
      , 'documents_id' => $documents_id
      ) );
      if( have_priv('document','create') ) {
        echo template_button_view();
      }
    }
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view();
  close_div();

close_fieldset();

if( $action === 'deletedocument' ) {
  need( $documents_id );
  sql_delete_documents( $documents_id, 'action=hard' );
  js_on_exit( "flash_close_message($H_SQ".we('document deleted','Stelle gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
