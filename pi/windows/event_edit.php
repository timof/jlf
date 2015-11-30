<?php // /pi/windows/event_edit.php

sql_transaction_boundary('*');

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'events_id', 'global,type=u,sources=self http,set_scopes=self' );

need_priv( 'events', $events_id ? 'edit' : 'create', $events_id );

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
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'events,init' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'events'
  , 'failsafe' => false   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $events_id ) {
    $event = sql_one_event( $events_id );
    $opts['rows'] = array( 'events' => $event );
  }

  $f = init_fields( array(
      'groups_id'
    , 'people_id'
    , 'cn_en' => 'size=80'
    , 'cn_de' => 'size=80'
    , 'note_en' => 'lines=3,cols=80'
    , 'note_de' => 'lines=3,cols=80'
    , 'date' => 'size=8'
    , 'time' => 'size=4'
    , 'location' => 'size=80'
    , 'url' => 'size=80'
    , 'pdf' => 'set_scopes='
    , 'pdf_caption_de' => 'size=80'
    , 'pdf_caption_en' => 'size=80'
    , 'jpegphoto' => 'set_scopes='
    , 'jpegphotorights_people_id'
    , 'url_class' => 'default=outlink'
    , 'flag_detailview' => 'b,text='.we('detail view','Detailanzeige')
    , 'flag_publish' => 'b,text='.we('publish',"ver{$oUML}ffentlichen")
    , 'flag_ticker' => 'b,text='.we('show in ticker','im Ticker anzeigen')
    , 'flag_highlight' => 'b,text=highlight'
    )
  , $opts
  );
  $reinit = false;

  handle_actions( array( 'reset', 'save', 'init', 'template', 'deleteEvent', 'deletePhoto', 'deletePdf' ) ); 
  if( $action ) switch( $action ) {
    case 'template':
      $events_id = 0;
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
        $error_messages = sql_save_event( $events_id, $values, 'action=dryrun' );
        if( ! $error_messages ) {
          $events_id = sql_save_event( $events_id, $values, 'action=hard' );
          js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
          reinit('reset');
          $info_messages[] = we('entry was saved','Eintrag wurde gespeichert');
        }
      }
      break;

    case 'deletePhoto':
      need( $events_id );
      sql_update( 'events', $events_id, array( 'jpegphoto' => '', 'jpegphotorights_people_id' => 0 ) );
      $f['jpegphotorights_people_id']['value'] = 0;
      reinit('self');
      break;

    case 'deletePdf':
      need( $events_id );
      sql_update( 'events', $events_id, array( 'pdf' => '', 'pdf_caption_de' => '', 'pdf_caption_en' => '' ) );
      reinit('self');
      break;
  }

} // while $reinit


if( $events_id ) {
  open_fieldset( 'old', we( 'event', 'Veranstaltung' ) );
} else {
  open_fieldset( 'new', we( 'New event', 'Neue Veranstaltung' ) );
}
  open_fieldset( '', we('event','Veranstaltung') );
  
    open_fieldset( 'line'
    , label_element( $f['cn_de'], '', 'Titel (deutsch):' )
    , string_element( $f['cn_de'] )
    );
  
    open_fieldset( 'line'
    , label_element( $f['note_de'], '', 'Beschreibung (deutsch):' )
    , textarea_element( $f['note_de'] )
    );
  
    open_fieldset( 'line smallskipt'
    , label_element( $f['cn_en'], '', 'Title (English):' )
    , string_element( $f['cn_en'] )
    );
  
    open_fieldset( 'line'
    , label_element( $f['note_en'], '', 'Description (English):' )
    , textarea_element( $f['note_en'] )
    );
  
  close_fieldset();

  open_fieldset( 'line smallskipt', we('URL (for more info)', "URL (f{$uUML}r weitere Informationen)" ) );

    open_fieldset( 'line smallskipt'
    , label_element( $f['url'], '', 'URL' )
    , string_element( $f['url'] )
    );
    open_fieldset( 'line smallskipt'
    , label_element( $f['url_class'], '', 'type' )
    , select_element( $f['url_class'], array( 'choices' => $tables['events']['cols']['url_class']['pattern']  ) )
    );

  close_fieldset();

  open_fieldset( '', we('coordinates','Koordinaten') );

    open_fieldset( 'line'
    , label_element( $f['location'], '', we('location:','Ort:') )
    , string_element( $f['location'] )
    );
    open_fieldset( 'line'
    , label_element( $f['date'], '', we('date:','Datum (YYYYMMDD):') )
    , string_element( $f['date'] )
    );
    open_fieldset( 'line'
    , label_element( $f['time'], '', we('time:','Zeit (hhmm):') )
    , string_element( $f['time'] )
    );

  close_fieldset();

  open_fieldset( '', we('Contact','Ansprechpartner / Veranstalter') );

    open_fieldset( 'line medskipt'
    , label_element( $f['groups_id'], '', we('Group:','Arbeitsgruppe:') )
    , selector_groups( $f['groups_id'], array( 'choices' => array( '0' => we('--- none ---','--- keine Gruppe ---') ) ) )
    );

    if( ( $g_id = $f['groups_id']['value'] ) ) {
      open_fieldset( 'line medskipt'
      , label_element( $f['people_id'], '', 'Person:' )
      , selector_people( $f['people_id'], array( 'filters' => "groups_id=$g_id", 'choices' => array( '0' => we('--- none ---','--- keine Person ---') ) ) )
      );
    }

  close_fieldset();

  if( $events_id ) {
    open_fieldset( '', we('document for download','Dokument zum Download') );
      if( $f['pdf']['value'] ) {
        open_fieldset( 'line', we('available document:', 'vorhandene Datei:' ) );
          // echo download_link( 'positions_pdf', $positions_id, 'class=file,text=download .pdf' );
          echo inlink( 'event_view', "i=attachment,events_id=$events_id,class=file,text=download .pdf" );
          quad();
          echo inlink( '', 'action=deletePdf,class=icon drop,title='.we('delete PDF','PDF löschen') );
        close_fieldset();
        open_fieldset( 'line', 'caption (deutsch):', string_element( $f['pdf_caption_de'] ) );
        open_fieldset( 'line', 'caption (english):', string_element( $f['pdf_caption_en'] ) );
      } else {
        open_fieldset( 'line', label_element( $f['pdf'], '', 'PDF upload:' ), file_element( $f['pdf'] ) );
      }
    close_fieldset();

    open_fieldset( '', 'Photo' );
      if( $f['jpegphoto']['value'] ) {
        open_fieldset( 'line medskip', we('stored photo:','gespeichertes Foto:' ) );
          open_div('oneline', html_tag( 'img', array(
                'height' => '100'
              , 'src' => 'data:image/jpeg;base64,' . $f['jpegphoto']['value']
              ), NULL
            )
          . inlink( '', array(
              'action' => 'deletePhoto', 'class' => 'icon drop qquadl medskips'
            , 'title' => we('delete photo','Foto löschen')
            , 'confirm' => we('really delete photo?','Foto wirklich löschen?')
            ) )
          );
          open_div('oneline smallskipt');
            echo label_element( $f['jpegphotorights_people_id'], '', we('Photo copyright by: ','Bildrechte: ' ) );
            echo selector_people( $f['jpegphotorights_people_id'] );
          close_div();
        close_fieldset();
      } else {
        open_fieldset( 'line medskip'
        , label_element( $f['jpegphoto'], '', we('upload photo:','Foto hochladen:') )
        , file_element( $f['jpegphoto'] ) . ' (jpeg, max. 200kB)'
        );
      }

    close_fieldset();
  }


  open_fieldset( '', we('Attributes','Attribute') );

    open_fieldset( 'line medskipt', label_element( $f['flag_publish'] ), checkbox_element( $f['flag_publish'] ) );
    open_fieldset( 'line medskipt', label_element( $f['flag_ticker'] ), checkbox_element( $f['flag_ticker'] ) );
    open_fieldset( 'line medskipt', label_element( $f['flag_highlight'] ), checkbox_element( $f['flag_highlight'] ) );
    open_fieldset( 'line medskipt', label_element( $f['flag_detailview'] ),  checkbox_element( $f['flag_detailview'] ) );

  close_fieldset();
  
  open_div('right bigskipt');
    if( $events_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button'
      , 'action' => 'deleteEvent'
      , 'text' => we('delete event','Veranstaltung löschen')
      , 'confirm' => we('really delete?','wirklich löschen?')
      , 'inactive' => sql_delete_events( $events_id, 'action=dryrun' )
      ) );
      echo inlink( 'event_view', array(
        'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
      , 'events_id' => $events_id
      ) );
      if( have_priv('events','create') ) {
        echo template_button_view();
      }
    }
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view();

  close_div();

close_fieldset();

if( $action === 'deleteEvent' ) {
  need( $events_id );
  sql_delete_events( $events_id, 'action=hard' );
  js_on_exit( "flash_close_message($H_SQ".we('event deleted','Veranstaltung gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
