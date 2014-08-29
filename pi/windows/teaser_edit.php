<?php // /pi/windows/teaser_edit.php

sql_transaction_boundary('*');

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'teaser_id', 'global,type=u,sources=self http,set_scopes=self' );

need_priv( 'teaser', $teaser_id ? 'edit' : 'create', $teaser_id );

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
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'teaser,init' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'teaser'
  , 'failsafe' => false   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $teaser_id ) {
    $teaser = sql_one_teaser( $teaser_id );
    $opts['rows'] = array( 'teaser' => $teaser );
  }

  $f = init_fields( array(
      'note_de' => 'lines=10,cols=80'
    , 'note_en' => 'lines=10,cols=80'
    , 'tags' => 'size=60'
    , 'jpegphoto' => 'set_scopes='
    , 'jpegphotorights_people_id' => "default=$login_people_id"
    )
  , $opts
  );
  $reinit = false;

  handle_actions( array( 'reset', 'save', 'init', 'template', 'deletePhoto', 'deleteTeaser' ) ); 
  if( $action ) switch( $action ) {
    case 'template':
      $teaser_id = 0;
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
        $error_messages = sql_save_teaser( $teaser_id, $values, 'action=dryrun' );
        if( ! $error_messages ) {
          $teaser_id = sql_save_teaser( $teaser_id, $values, 'action=hard' );
          js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
          reinit('reset');
          $info_messages[] = we('entry was saved','Eintrag wurde gespeichert');
        }
      }
      break;

    case 'deletePhoto':
      need( $teaser_id );
      sql_update( 'teaser', $teaser_id, array( 'jpegphoto' => '', 'jpegphotorights_people_id' => 0 ) );
      $f['jpegphotorights_people_id']['value'] = 0;
      reinit('self');
      break;

  }

} // while $reinit


if( $teaser_id ) {
  open_fieldset( 'old', 'teaser' );
} else {
  open_fieldset( 'new', 'New teaser' );
}

  open_fieldset( 'line'
  , label_element( $f['note_de'], '', 'Text (deutsch)' )
  , textarea_element( $f['note_de'] )
  );

  open_fieldset( 'line'
  , label_element( $f['note_en'], '', 'Text (englisch)' )
  , textarea_element( $f['note_en'] )
  );

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

  open_fieldset( 'line'
  , label_element( $f['tags'], '', 'tags' )
  , string_element( $f['tags'] )
  );


  open_div('right bigskipt');
    if( $teaser_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button qquads'
      , 'action' => 'deleteteaser'
      , 'text' => we('delete teaser','Teaser löschen')
      , 'confirm' => we('really delete?','wirklich löschen?')
      , 'inactive' => sql_delete_teaser( $teaser_id, 'action=dryrun' )
      ) );
//      echo inlink( 'teaser_view', array(
//        'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
//      , 'teaser_id' => $teaser_id
//      ) );
      if( have_priv('teaser','create') ) {
        echo template_button_view();
      }
    }
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view();
  close_div();

close_fieldset();

if( $action === 'deleteTeaser' ) {
  need( $teaser_id );
  sql_delete_teaser( $teaser_id, 'action=hard' );
  js_on_exit( "flash_close_message($H_SQ".we('teaser deleted','Teaser gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
