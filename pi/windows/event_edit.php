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
    , 'flag_detailview' => 'b,text='.we('detail view','Detailanzeige')
    , 'flag_publish' => 'b,text='.we('publish',"ver{$oUML}ffentlichen")
    , 'flag_ticker' => 'b,text='.we('show in ticker','im Ticker anzeigen')
    )
  , $opts
  );
  $reinit = false;

  handle_action( array( 'reset', 'save', 'update', 'init', 'template', 'deleteEvent' ) ); 
  switch( $action ) {
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

  }

} // while $reinit


if( $events_id ) {
  open_fieldset( 'old', we( 'event', 'Veranstaltung' ) );
} else {
  open_fieldset( 'new', we( 'New event', 'Neue Veranstaltung' ) );
}
  flush_all_messages();

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
  
    open_fieldset( 'line smallskipt'
    , label_element( $f['url'], '', we('URL (for more info)','URL (fuer weitere Information)' ) )
    , string_element( $f['url'] )
    );

  close_fieldset();

  open_fieldset( '', we('coordinates','Koordinaten') );

    open_fieldset( 'line'
    , label_element( $f['location'], '', we('location:','Ort:') )
    , string_element( $f['location'] )
    );
    open_fieldset( 'line'
    , label_element( $f['date'], '', we('date:','Datum:') )
    , string_element( $f['date'] )
    );
    open_fieldset( 'line'
    , label_element( $f['time'], '', we('time:','Zeit:') )
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

  open_fieldset( '', we('Attributes','Attribute') );

    open_fieldset( 'line medskipt', label_element( $f['flag_publish'] ), checkbox_element( $f['flag_publish'] ) );
    open_fieldset( 'line medskipt', label_element( $f['flag_ticker'] ), checkbox_element( $f['flag_ticker'] ) );
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
