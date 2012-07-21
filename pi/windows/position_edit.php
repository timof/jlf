<?php

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'positions_id', 'global,type=u,sources=self http,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {

  switch( $reinit ) {
    case 'init':
      $sources = 'http self keep default';
      break;
    case 'self':
      $sources = 'self keep default';  // need keep here for big blobs!
      break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'keep default';
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
    , 'degree' => 'auto=1'
    , 'groups_id'
    , 'contact_people_id'
    , 'pdf' => 'set_scopes='
    )
  , $opts
  );
  $reinit = false;

  handle_action( array( 'reset', 'save', 'update', 'init', 'template', 'deletePdf' ) ); 
  switch( $action ) {
    case 'template':
      $positions_id = 0;
      break;

    case 'save':
      if( ! $f['_problems'] ) {

        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            if( $fieldname['source'] !== 'keep' ) // no need to write existing blob
              $values[ $fieldname ] = $r['value'];
        }
        // debug( strlen( $values['pdf'] ), 'size of pdf' );
        // debug( $values, 'values' );
        if( $positions_id ) {
          sql_update( 'positions', $positions_id, $values );
        } else {
          $positions_id = sql_insert( 'positions', $values );
        }
        reinit('reset');
        js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
      }
      break;

    case 'deletePdf':
      need( $positions_id );
      sql_update( 'positions', $positions_id, array( 'pdf' => '' ) );
      reinit('self');
      break;

  }

} // while $reinit

// debug( $_POST, '_POST' );
// debug( $f['pdf']['source'], 'pdf source' );
// debug( strlen( $f['pdf']['value'] ), 'sizeof pdf value' );

if( $positions_id ) {
  open_fieldset( 'small_form old', we( 'Position / Thesis topic', 'Stelle / Thema' ) );
} else {
  open_fieldset( 'small_form new', we( 'New position / topic', 'Neue Stelle / Thema' ) );
}
  open_table('small_form hfill');
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['degree'] ), we('Type / Degree:','Art / Abschluss:') );
      open_td( 'oneline' );
        $a = $f['degree'];
        foreach( $degree_text as $degree_id => $degree_cn ) {
          $a['mask'] = $degree_id;
          $a['text'] = $degree_cn;
          open_span( 'quadr', checkbox_element( $a ) );
        }
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['cn'] ), we('Topic:','Thema:') );
      open_td( '', string_element( $f['cn'] ) );
    open_tr();
      open_td( array( 'label' => $f['note'] ), we('Description:','Beschreibung:') );
      open_td( '', textarea_element( $f['note'] ) );
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['url'] ), we('Web page:','Webseite:') );
      open_td( '', string_element( $f['url'] ) );
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['groups_id'] ), we('Group:','Gruppe:') );
      open_td();
        selector_groups( $f['groups_id'] );
if( $f['groups_id']['value'] ) {
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['contact_people_id'] ), we('Contact:','Ansprechpartner:' ) );
      open_td();
        selector_people( $f['contact_people_id'], array( 'filters' => array( 'groups_id' => $f['groups_id']['value'] ) ) );
}
if( $positions_id ) {
    if( $f['pdf']['value'] ) {
      open_tr( 'bigskip' );
        open_td( '', we('available document:', 'vorhandene Datei:' ) );
        open_td('oneline');
          echo download_link( 'positions_pdf', $positions_id, 'class=file,text=download .pdf' );
          quad();
          echo inlink( '', 'action=deletePdf,class=drop,title='.we('delete PDF','PDF loeschen') );

    }
    open_tr( 'bigskip' );
      open_td( array( 'label' => $f['pdf'] ), 'PDF upload:' );
      open_td( '', file_element( $f['pdf'] ) );
}

    open_tr( 'bigskip' );
      open_td( 'right,colspan=2' );
        if( $positions_id ) {
          echo inlink( 'position_view', array(
            'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
          , 'positions_id' => $positions_id
          ) );
        }
        reset_button( $f['_changes'] ? '' : 'display=none' );
        submission_button();
        if( $positions_id && ! $f['_changes'] )
          template_button();
        reset_button( $f['_changes'] ? '' : 'display=none' );
        // submission_button( $f['_changes'] ? '' : 'display=none' );
        submission_button();
  close_table();

close_fieldset();

handle_action( array( 'deletePerson' ) );
  switch( $action ) {
    case 'deletePerson': // can be triggered from person_edit
      need( $people_id );
      // sql_delete_people( $people_id );
      js_on_exit( "flash_close_message($H_SQ".we('person deleted','Person geloescht')."$H_SQ );" );
      debug( $people_id, 'p' );
      return;
  }

?>
