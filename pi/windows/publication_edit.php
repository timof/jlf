<?php // /pi/windows/publication_edit.php

sql_transaction_boundary('*');

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'publications_id', 'global,type=u,sources=self http,set_scopes=self' );

need_priv( 'publications', $publications_id ? 'edit' : 'create', $publications_id );

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
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'publications,init' );
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
    , 'cn_en' => 'size=80'
    , 'cn_de' => 'size=80'
    , 'summary_en' => 'lines=3,cols=80'
    , 'summary_de' => 'lines=3,cols=80'
    , 'authors' => 'size=80'
    , 'abstract' => 'lines=10,cols=80'
    , 'journal' => 'size=80'
    , 'volume' => 'size=8'
    , 'page' => 'size=32'
    , 'year' => "type=U4,default=$current_year"
    , 'journal_url' => 'size=80'
    , 'info_url' => 'size=80'
    , 'groups_id' => "type=U"
//    , 'pdf' => 'set_scopes='
    , 'jpegphoto' => 'set_scopes='
    , 'jpegphotorights_people_id' => "default=$login_people_id"
    )
  , $opts
  );
  $reinit = false;

  handle_actions( array( 'reset', 'save', 'init', 'template', 'deleteJpg', 'deletePublication' ) ); 
  if( $action ) switch( $action ) {
    case 'template':
      $publications_id = 0;
      break;

    case 'save':
      if( ! $f['_problems'] ) {

        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' ) {
            if( $r['source'] !== 'initval' ) { // no need to write existing blob
              $values[ $fieldname ] = $r['value'];
            }
          }
        }
        $error_messages = sql_save_publication( $publications_id, $values, 'action=dryrun' );
        if( ! $error_messages ) {
          $publications_id = sql_save_publication( $publications_id, $values, 'action=hard' );
          js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
          reinit('reset');
          $info_messages[] = we('entry was saved','Eintrag wurde gespeichert');
        }
      }
      break;

//     case 'deletePdf':
//       need( $publications_id );
//       sql_update( 'publications', $publications_id, array( 'pdf' => '' ) );
//       reinit('self');
//       break;

    case 'deleteJpg':
      need( $publications_id );
      sql_update( 'publications', $publications_id, array( 'jpegphoto' => '', 'jpegphotorights_people_id' => 0 ) );
      $f['jpegphotorights_people_id']['value'] = 0;
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

  open_fieldset( '', we('Headline','Schlagzeile') );

    open_fieldset( 'line'
    , label_element( $f['cn_de'], '', 'Kurzer Titel (deutsch):' )
    , string_element( $f['cn_de'] )
    );

    open_fieldset( 'line'
    , label_element( $f['summary_de'], '', 'Kurze Zusammenfassung (deutsch):' )
    , textarea_element( $f['summary_de'] )
    );

    open_fieldset( 'line smallskipt'
    , label_element( $f['cn_en'], '', 'Short title (English):' )
    , string_element( $f['cn_en'] )
    );

    open_fieldset( 'line'
    , label_element( $f['summary_en'], '', 'Short Summary (English):' )
    , textarea_element( $f['summary_en'] )
    );

    open_fieldset( 'line medskipt'
    , label_element( $f['groups_id'], '', we('Group:','Arbeitsgruppe:') )
    , selector_groups( $f['groups_id'] )
    );

  close_fieldset();


  open_fieldset( '', we('Journal reference','Literaturangabe') );

    open_fieldset( 'line'
    , label_element( $f['title'], '', we('Title:','Titel:') )
    , string_element( $f['title'] )
    );
    open_fieldset( 'line'
    , label_element( $f['authors'], '', we('Authors:','Autoren:') )
    , string_element( $f['authors'] )
    );
    open_fieldset( 'line'
    , label_element( $f['abstract'], '', 'Abstract:' ) 
    , textarea_element( $f['abstract'] )
    );

    open_fieldset( 'line medskipt'
    , label_element( $f['journal'], '', we('Journal:','Zeitschrift:') )
    , string_element( $f['journal'] )
    );
  
    open_fieldset( 'line'
    , label_element( $f['journal'], '', we('Volume:','Band:') )
    , string_element( $f['volume'] )
    );
  
    open_fieldset( 'line'
    , label_element( $f['journal'], '', we('Page(s):','Seite(n):') )
    , string_element( $f['page'] )
    );
  
    open_fieldset( 'line'
    , label_element( $f['year'], '', we('Year:','Jahr:') )
    , selector_year( $f['year'] )
    );

    open_fieldset( 'line medskipt'
    , label_element( $f['journal_url'], 'td', we('Web link to article (optional):','Web Link zum Artikel (optional):' ) )
    , string_element( $f['journal_url'] )
    );

  close_fieldset();


  open_fieldset( '', we('Additional Information (optional)','Weitere Informationen (optional)') );

    open_fieldset( 'line medskipt'
    , label_element( $f['info_url'], 'td', we('Web link to more info:','Web Link für weitere Informationen:' ) )
    , string_element( $f['info_url'] )
    );

    if( $publications_id ) {
  
  //     if( $f['pdf']['value'] ) {
  //       open_tr('td:medskipt');
  //         open_td( '', we('available document:', 'vorhandene Datei:' ) );
  //         open_td('oneline');
  //           echo inlink( 'publication_view', "f=pdf,i=pdf,publications_id=$publications_id,class=file,text=download .pdf" );
  //           quad();
  //           echo inlink( '', array(
  //             'action' => 'deletePdf', 'class' => 'button drop'
  //           , 'text' => we('delete PDF','PDF löschen')
  //           , 'confirm' => we('really delete PDF?','PDF wirklich löschen?')
  //           ) );
  // 
  //     } else {
  //       open_tr('td:medskipt');
  //         open_td( '', label_element( $f['pdf'], '', 'Artikel (.pdf) upload:' ) );
  //         open_td( '', file_element( $f['pdf'] ) );
  //     }
  
      if( $f['jpegphoto']['value'] ) {
        open_fieldset( 'line medskipt', we('existing photo:','vorhandenes Foto:' ) );
          open_div();
            open_div('oneline');
              echo html_tag( 'img', array( 'height' => '100' , 'src' => 'data:image/jpeg;base64,' . $f['jpegphoto']['value'] ), NULL );
              echo inlink( '', array( 'action' => 'deleteJpg', 'class' => 'icon drop'
                , 'text' => we('delete photo','Foto löschen')
                , 'confirm' => we('really delete photo?','Foto wirklich löschen?')
              ) );
            close_div();
            open_div('oneline smallskipt');
              echo label_element( $f['jpegphotorights_people_id'], '', we('Photo copyright by: ','Bildrechte: ' ) );
              echo selector_people( $f['jpegphotorights_people_id'] );
            close_div();
          close_div();
        close_fieldset();
      } else {
        open_fieldset( 'line medskipt'
        , label_element( $f['jpegphoto'], '', we('upload photo (optional):','Foto hochladen (optional):') )
        , file_element( $f['jpegphoto'] ) . ' (jpeg, max. 200kB)'
        );
      }
  
    } else {
      open_div( 'medskipt comment', we('(after saving the publication entry, you can upload a Photo here)','(Nach dem Speichern können sie hier zusätzlich ein Bild hochladen) ') );
    }
 
  close_fieldset();
  
  open_div('right bigskipt');
    if( $publications_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button'
      , 'action' => 'deletePublication'
      , 'text' => we('delete publication','Veröffentlichung löschen')
      , 'confirm' => we('really delete?','wirklich löschen?')
      , 'inactive' => sql_delete_publications( $publications_id, 'action=dryrun' )
      ) );
      echo inlink( 'publication_view', array(
        'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
      , 'publications_id' => $publications_id
      ) );
      if( have_priv('publications','create') ) {
        echo template_button_view();
      }
    }
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view();

  close_div();

close_fieldset();

if( $action === 'deletePublication' ) {
  need( $publications_id );
  sql_delete_publications( $publications_id, 'action=hard' );
  js_on_exit( "flash_close_message($H_SQ".we('publication deleted','Veröffentlichung gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
