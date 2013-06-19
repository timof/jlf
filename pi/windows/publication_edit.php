<?php

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'publications_id', 'global,type=u,sources=self http,set_scopes=self' );

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
  , 'tables' => 'publications'
  , 'failsafe' => false   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $publications_id ) {
    $publication = sql_one_publication( $publications_id );
    $opts['rows'] = array( 'publications' => $publication );
  }

  $f = init_fields( array(
      'title' => 'size=80'
    , 'authors' => 'size=80'
    , 'abstract' => 'lines=10,cols=80'
    , 'journal' => 'size=80'
    , 'url' => 'size=80'
    , 'groups_id'
    , 'pdf' => 'set_scopes='
    , 'jpegphoto' => 'set_scopes='
    )
  , $opts
  );
  $reinit = false;

  handle_action( array( 'reset', 'save', 'update', 'init', 'template', 'deletePdf', 'deleteJpg', 'deletePublication' ) ); 
  switch( $action ) {
    case 'template':
      $publications_id = 0;
      break;

    case 'save':
      if( ! $f['_problems'] ) {

        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            if( $fieldname['source'] !== 'initval' ) // no need to write existing blob
              $values[ $fieldname ] = $r['value'];
        }
        if( $publications_id ) {
          sql_update( 'publications', $ppublications_id, $values );
        } else {
          $pubpications_id = sql_insert( 'publications', $values );
        }
        reinit('reset');
        js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
      }
      break;

    case 'deletePdf':
      need( $publications_id );
      sql_update( 'publications', $publications_id, array( 'pdf' => '' ) );
      reinit('self');
      break;

    case 'deleteJpg':
      need( $publications_id );
      sql_update( 'publications', $publications_id, array( 'jpegphoto' => '' ) );
      reinit('self');
      break;
  }

} // while $reinit


if( $publications_id ) {
  open_fieldset( 'old', we( 'Publication', 'Veröffentlichung' ) );
} else {
  open_fieldset( 'new', we( 'New publication', 'Neue Veröffentlichung' ) );
}
  flush_all_messages();

  open_table('td:smallskips');

    open_tr();
      open_td( '', label_element( $f['title'], '', we('Title:','Titel:') ) );
      open_td( '', string_element( $f['title'] ) );

    open_tr();
      open_td( '', label_element( $f['authors'], '', we('Authors:','Autoren:') ) );
      open_td( '', string_element( $f['authors'] ) );

    open_tr();
      open_td( '', label_element( $f['abstract'], '', 'Abstract:' ) );
      open_td( '', textarea_element( $f['abstract'] ) );

    open_tr();
      open_td( '', label_element( $f['groups_id'], '', we('Group:','Arbeitsgruppe:') ) );
      open_td( '', selector_groups( $f['groups_id'] ) );
  
    open_tr();
      open_td( '', label_element( $f['url'], 'td', 'Web link:' ) );
      open_td( '', string_element( $f['url'] ) );
  
  if( $publications_id ) {
      if( $f['pdf']['value'] ) {
        open_tr();
          open_td( '', we('available document:', 'vorhandene Datei:' ) );
          open_td('oneline');
            // echo download_link( 'positions_pdf', $positions_id, 'class=file,text=download .pdf' );
            echo inlink( 'download', "item=positions_id,id=$positions_id,class=file,text=download .pdf" );
            quad();
            echo inlink( '', 'action=deletePdf,class=drop,title='.we('delete PDF','PDF löschen') );
  
      }
      open_tr();
        open_td( '', label_element( $f['pdf'], '', 'PDF upload:' ) );
        open_td( '', file_element( $f['pdf'] ) );
  }

  close_table();

  open_div('right bigskipt');
    if( $publications_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button qquads'
      , 'action' => 'deletePublication'
      , 'text' => we('delete publication','Verföffentlichung löschen')
      , 'confirm' => we('really delete?','wirklich löschen?')
      , 'inactive' => sql_delete_publications( $publications_id, 'check' )
      ) );
      echo inlink( 'publication_view', array(
        'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
      , 'positions_id' => $positions_id
      ) );
      echo template_button_view();
    }
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view();

  close_div();

close_fieldset();

if( $action === 'deletePublication' ) {
  need( $publications_id );
  sql_delete_publications( $publications_id );
  js_on_exit( "flash_close_message($H_SQ".we('publication deleted','Veröffentlichung gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
