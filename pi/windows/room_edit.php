<?php

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'rooms_id', 'global,type=u,sources=self http,set_scopes=self' );

need_priv( 'rooms', 'edit', $rooms_id );

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
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'rooms,init' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'rooms'
  , 'failsafe' => false   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $rooms_id ) {
    $room = sql_one_room( $rooms_id );
    $opts['rows'] = array( 'rooms' => $room );
  }

  $f = init_fields( array(
      'roomnumber' => 'size=20'
    , 'note' => 'lines=10,cols=60'
    , 'groups_id'
    , 'contact_people_id' => 'u'
    , 'contact2_people_id' => 'u'
    , 'flag_lab' => 'sources=initval,initval=1' // only labs, for the time being
    )
  , $opts
  );
  $reinit = false;

  handle_action( array( 'reset', 'save', 'update', 'init', 'template', 'deleteRoom' ) ); 
  switch( $action ) {
    case 'template':
      $rooms_id = 0;
      break;

    case 'save':
      if( $f['_problems'] ) {
        $error_messages[] = we('saving failed','Speichern fehlgeschlagen' );
      } else {

        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            if( $fieldname['source'] !== 'initval' ) // no need to write existing blob
              $values[ $fieldname ] = $r['value'];
        }
        // debug( strlen( $values['pdf'] ), 'size of pdf' );
        // debug( $values, 'values' );

        $error_messages = sql_save_room( $rooms_id, $values, 'check' );
        if( ! $error_messages ) {
          $rooms_id = sql_save_room( $rooms_id, $values );
        $info_messages[] = we('entry was saved','Eintrag wurde gespeichert');
        unset( $f );
        js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
        reinit('reset');
      }
      break;

    }

  }

} // while $reinit


if( $rooms_id ) {
  open_fieldset( 'old table td:qquads;smallskips', we( 'Room', 'Raum' ) );
} else {
  open_fieldset( 'new table td:qquads;smallskips', we( 'New room', 'Neuer Raum' ) );
}
  open_tr();
    open_td( '', label_element( $f['roomnumber'], '', we('Room number:','Raumnummer:') ) );
    open_td( '', string_element( $f['roomnumber'] ) );

  $filters = array();
  if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    $filters['groups_id'] = $login_groups_ids;
  }
  open_tr();
    open_td( 'oneline', label_element( $f['groups_id'], '', we('room belongs to group:','zugeordnet zu Gruppe:') ) );
    open_td( $f['groups_id'], selector_groups( $f['groups_id'], array( 'filters' => $filters ) ) );

if( $f['groups_id']['value'] ) {
    open_tr();
      open_td( 'oneline right', label_element( $f['contact_people_id'], '', we('responsible person:','verantwortliche Person:' ) ) );
      open_td( '', selector_people( $f['contact_people_id'], array( 'filters' => array( 'groups_id' => $f['groups_id']['value'] ), 'office' => 1 ) ) );

    open_tr();
      open_td( 'oneline right', label_element( $f['contact2_people_id'], '', we('deputy:','Stellvertretung:' ) ) );
      open_td( '', selector_people( $f['contact2_people_id'], array( 'filters' => array( 'groups_id' => $f['groups_id']['value'] ), 'office' => 1 ) ) );
}

  open_tr();
    open_td( '', label_element( $f['note'], '', we('Notes:','Hinweise:') ) );
    open_td( '', textarea_element( $f['note'] ) );

  open_tr('bigskip');
    open_td();
    open_td('right oneline');
      if( $rooms_id ) {
        echo inlink( 'self', array(
          'class' => 'drop button qquads'
        , 'action' => 'deleteRoom'
        , 'text' => we('delete room','Raum löschen')
        , 'confirm' => we('really delete?','wirklich löschen?')
        , 'inactive' => sql_delete_rooms( $rooms_id, 'check' )
        ) );
        echo inlink( 'room_view', array(
          'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
        , 'rooms_id' => $rooms_id
        ) );
        echo template_button_view();
      }
      echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
      echo save_button_view();

close_fieldset();

if( $action === 'deleteRoom' ) {
  need( $rooms_id );
  sql_delete_rooms( $rooms_id );
  js_on_exit( "flash_close_message($H_SQ".we('room deleted','Raum gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
