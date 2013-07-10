<?php

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'groups_id', 'global,type=u,sources=self http,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {
  $problems = array();

  switch( $reinit ) {
    case 'init':
      $sources = 'http self initval default';
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
    , 'cn' => 'size=60'
    , 'url' => 'size=60'
    , 'note' => 'lines=4,cols=60'
    , 'cn_en' => 'size=60'
    , 'url_en' => 'size=60'
    , 'note_en' => 'rows=4,cols=60'
    , 'flags' => 'type=u,auto=1,default='. ( GROUPS_FLAG_INSTITUTE | GROUPS_FLAG_ACTIVE | GROUPS_FLAG_LIST )
    , 'head_people_id'
    , 'secretary_people_id'
    , 'jpegphoto' => 'set_scopes='
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
          if( $fieldname[ 0 ] !== '_' )
            if( $fieldname['source'] !== 'initval' ) // no need to write existing blob
              $values[ $fieldname ] = $r['value'];
        }
        if( ! ( $problems = sql_save_group( $groups_id, $values, 'check' ) ) ) {
          $groups_id = sql_save_group( $groups_id, $values );
          need( isnumber( $groups_id ) && ( $groups_id > 0 ) );
          reinit('reset');
        }
        js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
      }
      break;

    case 'deleteGroup':
      // handled at end of script
      break;

    case 'deletePhoto':
      need( $groups_id );
      need_priv( 'groups', 'edit', $groups_id );
      sql_update( 'groups', $groups_id, array( 'jpegphoto' => '' ) );
      reinit('init');
      break;
  }

} // while $reinit


if( $groups_id ) {
  open_fieldset( 'old', we('edit group','Gruppe bearbeiten') );
} else {
  open_fieldset( 'new', we('new group','neue Gruppe') );
}
  flush_all_messages();

  open_fieldset( 'table', we('Properties','Stammdaten') );
    open_tr( 'medskip' );
      open_td( '', label_element( $f['acronym'], '', we('Short Name:','Kurzname:') ) );
      open_td( '', string_element( $f['acronym'] ) );

  if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    open_tr( 'medskip' );
      open_td( '', we('Attributes:','Attribute:') );
      open_td( 'oneline' );
        $f['flags']['mask'] = GROUPS_FLAG_INSTITUTE;
        $f['flags']['text'] = we('member of institute','Institutsmitglied');
        open_span( 'qquad',  checkbox_element( $f['flags'] ) );

        $f['flags']['mask'] = GROUPS_FLAG_LIST;
        $f['flags']['text'] = we('list on public site','öffentlich anzeigen');
        open_span( 'qquad',  checkbox_element( $f['flags'] ) );

        $f['flags']['mask'] = GROUPS_FLAG_ACTIVE;
        $f['flags']['text'] = we('group still active','Gruppe noch aktiv');
        open_span( 'qquad',  checkbox_element( $f['flags'] ) );
  }

if( $groups_id ) {
    open_tr('medskip');
      open_td( '', label_element( $f['head_people_id'], '', we('Group leader:','Leiter der Gruppe:' ) ) );
      open_td( '', selector_people( $f['head_people_id'], array(
        'filters' => "groups_id=$groups_id" , 'choices' => array( '0' => we(' - vacant - ',' - vakant - ' ) ) )
      ) );

    open_tr('medskip');
      open_td( '', label_element( $f['secretary_people_id'], '', we('Secretary:','Sekretariat:' ) ) );
      open_td( '', selector_people( $f['secretary_people_id'], array(
        'filters' => "groups_id=$groups_id" , 'choices' => array( '0' => we(' - vacant - ',' - vakant - ' ) ) )
      ) );

    if( $f['jpegphoto']['value'] ) {
      open_tr('medskp');
        open_td( '', we('existing photo:','vorhandenes Foto:' ) );
        open_td( 'oneline',
          html_tag( 'img', array(
              'height' => '100'
            , 'src' => 'data:image/jpeg;base64,' . $f['jpegphoto']['value']
            ), NULL
          )
        . inlink( '', array(
            'action' => 'deletePhoto', 'class' => 'drop'
          , 'title' => we('delete photo','Foto löschen')
          , 'confirm' => we('really delete photo?','Foto wirklich löschen?')
          ) )
        );
    }
    open_tr();
      open_td( 'medskipb', label_element( $f['jpegphoto'], '', we('upload photo:','Foto hochladen:') ) );
      open_td( 'oneline bigskip', file_element( $f['jpegphoto'] ) . ' (jpeg, max. 200kB)' );
}
  close_fieldset();

  open_fieldset('table', we('description (German)', 'Beschreibung (deutsch):') );
    open_tr( 'smallskip' );
      open_td( '', label_element( $f['cn'], '', 'Name der Gruppe:' ) );
      open_td( '', string_element( $f['cn'] ) );
    open_tr( 'smallskip' );
      open_td( '', label_element( $f['url'], '', 'Internetseite:' ) );
      open_td( '', string_element( $f['url'] ) );
    open_tr( 'smallskip' );
      open_td( '', label_element( $f['note'], '', 'Kurzbeschreibung:' ) );
      open_td( '', textarea_element( $f['note'] ) );
  close_fieldset();

  open_fieldset('table', we('optional: description (English)', 'optional: Beschreibung (englisch):') );
    open_tr( 'smallskip' );
      open_td( '', label_element( $f['cn_en'], '', 'Name (english):' ) );
      open_td( '', string_element( $f['cn_en'] ) );
    open_tr( 'smallskip' );
      open_td( '', label_element( $f['url_en'], '', 'Web site (english):' ) );
      open_td( '', string_element( $f['url_en'] ) );
    open_tr( 'smallskip' );
      open_td( '', label_element( $f['note_en'], '', 'Description (englisch):' ) );
      open_td( '', textarea_element( $f['note_en'] ) );
  close_fieldset();

  open_div('right');
    if( $groups_id ) {
      if( ! sql_delete_groups( $groups_id, 'check' ) ) {
        echo inlink( 'self', array(
          'class' => 'drop button qquads'
        , 'action' => 'deleteGroup'
        , 'text' => we('delete group','Gruppe löschen')
        , 'confirm' => we('really delete group?','Gruppe wirklich löschen?')
        ) );
      }
      echo inlink( 'group_view', array(
        'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
      , 'groups_id' => $groups_id
      ) );
      echo template_button_view();
    }
    echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
    echo save_button_view();
  close_div();

close_fieldset();

if( $action === 'deleteGroup' ) {
  need( $groups_id );
  sql_delete_groups( $groups_id );
  js_on_exit( "flash_close_message($H_SQ".we('group deleted','Gruppe geloescht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}
?>
