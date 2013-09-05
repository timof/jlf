<?php

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'groups_id', 'global,type=u,sources=self http,set_scopes=self' );

need_priv( 'groups', $groups_id ? 'edit' : 'create', $groups_id );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {
  $problems = array();

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
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'groups,init' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'groups'
  , 'failsafe' => false   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $groups_id ) {
    $group = sql_one_group( $groups_id );
    $opts['rows'] = array( 'groups' => $group );
  }

  $f = init_fields( array(
      'acronym' => 'size=8'
    , 'cn_de' => 'size=60'
    , 'url_de' => 'size=60'
    , 'note_de' => 'lines=8,cols=60'
    , 'cn_en' => 'size=60'
    , 'url_en' => 'size=60'
    , 'note_en' => 'lines=8,cols=60'
    , 'flags' => 'type=u,auto=1,default='. ( GROUPS_FLAG_INSTITUTE | GROUPS_FLAG_ACTIVE | GROUPS_FLAG_LIST )
    , 'head_people_id'
    , 'secretary_people_id'
    , 'jpegphoto' => 'set_scopes='
    , 'jpegphotorights_people_id' => "default=$login_people_id"
    )
  , $opts
  );
  $reinit = false;

  handle_action( array( 'reset', 'save', 'update', 'init', 'template', 'deleteGroup', 'deletePhoto' ) ); 
  switch( $action ) {
    case 'template':
      $groups_id = 0;
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
        if( ! ( $error_messages = sql_save_group( $groups_id, $values, 'action=dryrun' ) ) ) {
          $groups_id = sql_save_group( $groups_id, $values, 'action=hard' );
          js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
          reinit('reset');
          $info_messages[] = we('entry was saved','Eintrag wurde gespeichert');
        }
      }
      break;

    case 'deleteGroup':
      // handled at end of script
      break;

    case 'deletePhoto':
      need( $groups_id );
      sql_update( 'groups', $groups_id, array( 'jpegphoto' => '', 'jpegphotorights_people_id' => 0 ) );
      $f['jpegphotorights_people_id']['value'] = 0;
      reinit('self');
      break;
  }

} // while $reinit


if( $groups_id ) {
  open_fieldset( 'old', we('edit group','Gruppe bearbeiten') );
} else {
  open_fieldset( 'new', we('new group','neue Gruppe') );
}
  flush_all_messages();

  open_fieldset( '', we('Properties','Stammdaten') );

    open_fieldset( 'line'
    , label_element( $f['acronym'], '', we('Short Name:','Kurzname:') )
    , string_element( $f['acronym'] )
    );

  if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    open_fieldset( 'line' , we('Attributes:','Attribute:') );
      $f['flags']['mask'] = GROUPS_FLAG_INSTITUTE;
      $f['flags']['text'] = we('member of institute','Institutsmitglied');
      open_span( 'qquad',  checkbox_element( $f['flags'] ) );

      $f['flags']['mask'] = GROUPS_FLAG_LIST;
      $f['flags']['text'] = we('list on public site','öffentlich anzeigen');
      open_span( 'qquad',  checkbox_element( $f['flags'] ) );

      $f['flags']['mask'] = GROUPS_FLAG_ACTIVE;
      $f['flags']['text'] = we('group still active','Gruppe noch aktiv');
      open_span( 'qquad',  checkbox_element( $f['flags'] ) );
    close_fieldset();
  }

if( $groups_id ) {
    open_fieldset( 'line medskip'
    , label_element( $f['head_people_id'], '', we('Group leader:','Leiter der Gruppe:' ) )
    , selector_people( $f['head_people_id'], array(
        'filters' => "groups_id=$groups_id" , 'choices' => array( '0' => we(' - vacant - ',' - vakant - ' ) ) )
    ) );

    open_fieldset( 'line medskip'
    , label_element( $f['secretary_people_id'], '', we('Secretary:','Sekretariat:' ) )
    , selector_people( $f['secretary_people_id'], array(
      'filters' => "groups_id=$groups_id" , 'choices' => array( '0' => we(' - vacant - ',' - vakant - ' ) ) )
    ) );

    if( $f['jpegphoto']['value'] ) {
      open_fieldset( 'line medskip', we('stored photo:','gespeichertes Foto:' ) );
        open_div('oneline', html_tag( 'img', array(
              'height' => '100'
            , 'src' => 'data:image/jpeg;base64,' . $f['jpegphoto']['value']
            ), NULL
          )
        . inlink( '', array(
            'action' => 'deletePhoto', 'class' => 'button drop'
          , 'text' => we('delete photo','Foto löschen')
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
}
  close_fieldset();

  open_fieldset('', we('description (German)', 'Beschreibung (deutsch):') );
    open_fieldset( 'line'
    , label_element( $f['cn_de'], '', 'Name der Gruppe:' )
    , string_element( $f['cn_de'] )
    );
    open_fieldset( 'line'
    , label_element( $f['url_de'], '', 'Internetseite:' )
    , string_element( $f['url_de'] )
    );
    open_fieldset( 'line'
    , label_element( $f['note_de'], '', 'Kurzbeschreibung:' )
    , textarea_element( $f['note_de'] )
    );
  close_fieldset();

  open_fieldset('', we('description (English)', 'Beschreibung (englisch):') );
    open_fieldset( 'line'
    , label_element( $f['cn_en'], '', 'Name of group:' )
    , string_element( $f['cn_en'] )
    );
    open_fieldset( 'line'
    , label_element( $f['url_en'], '', 'Web site:' )
    , string_element( $f['url_en'] )
    );
    open_fieldset( 'line'
    , label_element( $f['note_en'], '', 'Short description:' )
    , textarea_element( $f['note_en'] )
    );
  close_fieldset();

  open_div('right');
    if( $groups_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button qquads'
      , 'action' => 'deleteGroup'
      , 'text' => we('delete group','Gruppe löschen')
      , 'confirm' => we('really delete group?','Gruppe wirklich löschen?')
      , 'inactive' => sql_delete_groups( $groups_id, 'action=dryrun' )
      ) );
      echo inlink( 'group_view', array(
        'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
      , 'groups_id' => $groups_id
      ) );
      if( have_priv('groups','create') ) {
        echo template_button_view();
      }
    }
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view();
  close_div();

close_fieldset();

if( $action === 'deleteGroup' ) {
  need( $groups_id );
  sql_delete_groups( $groups_id, 'action=hard' );
  js_on_exit( "flash_close_message($H_SQ".we('group deleted','Gruppe geloescht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
