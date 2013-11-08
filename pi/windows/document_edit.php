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
    , 'note_de' => 'lines=4,cols=80'
    , 'note_en' => 'lines=4,cols=80'
    , 'tag' => 'size=20'
    , 'type' => 'size=20'
    , 'valid_from' => 'u8,size=8,min=19000000,max=29991231,class=number,allow_null=0'
    , 'programme_id' => 'auto=1'
    , 'url' => 'size=80'
    , 'pdf' => 'set_scopes='
    , 'flag_current' => 'text='.we('document is current version','Datei ist aktuelle Fassung')
    , 'flag_publish' => 'text='.we('publish document on public pages',"Datei auf {$oUML}ffentlichen Seiten anzeigen")
    )
  , $opts
  );
  $reinit = false;

  handle_action( array( 'save', 'template', 'deletePdf', 'deleteDocument' ) ); 

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

  
  open_fieldset( '', we('Description','Beschreibung') );

    open_fieldset( 'line'
    , label_element( $f['cn_de'], '', 'Bezeichnung (deutsch):' )
    , string_element( $f['cn_de'] )
    );
    open_fieldset( 'line'
    , label_element( $f['note_de'], '', 'Beschreibung (deutsch):' )
    , textarea_element( $f['note_de'] )
    );
  
    open_fieldset( 'line'
    , label_element( $f['cn_en'], '', 'Name (english):' )
    , string_element( $f['cn_en'] )
    );
    open_fieldset( 'line'
    , label_element( $f['note_en'], '', 'Description (english):' )
    , textarea_element( $f['note_en'] )
    );

    open_fieldset( 'line'
    , label_element( $f['tag'], '', we('unique short name (for internal use only; format: C-identifier):', "eindeutige Kurzbezeichnung (nur f{$uUML}r internen Gebrauch; Format: C-Bezeichner):" ) )
    , string_element( $f['tag'] )
    );
    open_fieldset( 'line'
    , label_element( $f['type'], '', we('type of document:', "Typ der Datei:" ) )
    , selector_documenttype( $f['type'] )
    );
    open_fieldset( 'line'
    , label_element( $f['valid_from'], '', we('valid from (format: YYYYMMDD):', "g{$uUML}tig ab (Format: JJJJMMTT):" ) )
    , string_element( $f['valid_from'] )
    );

    open_fieldset( 'line', we('publish',"Ver{$oUML}ffentlichen"), checkbox_element( $f['flag_publish'] ) );

    open_fieldset( 'line', we('currentness',"Aktualit{$aUML}t") );
      open_div( 'oneline', checkbox_element( $f['flag_current'] ) );
      open_ul( 'kommentar' );
        open_li( '', "aktuelle Versionen werden auf den {$oUML}ffentlichen Seiten vorrangig angezeigt" );
        open_li( '', "nicht-aktuelle Versionen sind in der Regel nur {$uUML}ber im Download-Bereich / Archiv erreichbar" );
        open_li( '', "f{$uUML}r jeden Studiengang sollte normalerweise jeweils eine Studienordnung aktuell sein" );
        open_li( '', "von den Vorlesungsverzeichnissen k{$uUML}nnen in der Regel die beiden neuesten als aktuell markiert sein" );
      close_ul();
    close_fieldset();

    open_fieldset( 'line', label_element( $f['programme_id'], '', we('relevant for (check all that apply):','relevant für (alle zutreffenden ankreuzen):') ) );
      $a = $f['programme_id'];
      open_ul('plain');
      foreach( $programme_text as $programme_id => $programme_cn ) {
        $a['mask'] = $programme_id;
        $a['text'] = $programme_cn;
        open_li( '', checkbox_element( $a ) );
      }
      close_ul();
    close_fieldset();

  close_fieldset();

  open_fieldset( '', we('File','Datei') );

    open_span('comment');
      echo we('Please...','Bitte...');
      open_ul();
        open_li('', we('either specify a URL to access the document','entweder eine URL zur Datei eingeben') );
        open_li();
          echo we('or upload the document itself here','oder die Datei selbst hier hochladen');
          if( ! $documents_id ) {
            echo we( ' (upload will be possible after saving the entry)', " (Hochladen ist erst nach dem ersten Speichern m{$oUML}glich)" );
          }
        close_li();
      close_ul();
    close_span();
  
  
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

  close_fieldset();

  open_div('right bigskipt');
    if( $documents_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button qquads'
      , 'action' => 'deleteDocument'
      , 'text' => we('delete document','Datei löschen')
      , 'confirm' => we('really delete?','wirklich löschen?')
      , 'inactive' => sql_delete_documents( $documents_id, 'action=dryrun' )
      ) );
      echo inlink( 'document_view', array(
        'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
      , 'documents_id' => $documents_id
      ) );
      if( have_priv('documents','create') ) {
        echo template_button_view();
      }
    }
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view();
  close_div();

close_fieldset();

if( $action === 'deleteDocument' ) {
  need( $documents_id );
  sql_delete_documents( $documents_id, 'action=hard' );
  js_on_exit( "flash_close_message($H_SQ".we('document deleted','Stelle gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
