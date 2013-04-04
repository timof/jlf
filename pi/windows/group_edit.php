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
    )
  , $opts
  );
  $reinit = false;

  handle_action( array( 'reset', 'save', 'update', 'init', 'template', 'deleteGroup' ) ); 
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
  }

} // while $reinit


if( $groups_id ) {
  open_fieldset( 'old', we('edit group','Gruppe bearbeiten') );
} else {
  open_fieldset( 'new', we('new group','neue Gruppe') );
}

  open_fieldset( 'table', we('Properties','Stammdaten') );
  // flush_problems();
  // open_table('small_form hfill');
    open_tr( 'medskip' );
      open_label( $f['acronym'], 'td', we('Short Name:','Kurzname:') );
      echo string_element( $f['acronym'], 'td' );

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
      open_label( $f['head_people_id'], 'td', we('Group leader:','Leiter der Gruppe:' ) );
      open_td( '', selector_people( $f['head_people_id'], array(
        'filters' => "groups_id=$groups_id" , 'choices' => array( '0' => we(' - vacant - ',' - vakant - ' ) ) )
      ) );

    open_tr('medskip');
      open_label( $f['secretary_people_id'], 'td', we('Secretary:','Sekretariat:' ) );
      open_td( '', selector_people( $f['secretary_people_id'], array(
        'filters' => "groups_id=$groups_id" , 'choices' => array( '0' => we(' - vacant - ',' - vakant - ' ) ) )
      ) );
}
  close_fieldset();

  open_fieldset('table', we('description (German)', 'Beschreibung (deutsch):') );
    open_tr( 'smallskip' );
      open_label( $f['cn'], 'td', 'Name der Gruppe:' );
      echo string_element( $f['cn'], 'td' );
    open_tr( 'smallskip' );
      open_label( $f['url'], 'td', 'Internetseite:' );
      echo string_element( $f['url'], 'td' );
    open_tr( 'smallskip' );
      open_label( $f['note'], 'td', 'Kurzbeschreibung:' );
      open_td( '', textarea_element( $f['note'] ) );
  close_fieldset();

  open_fieldset('table', we('optional: description (English)', 'optional: Beschreibung (englisch):') );
    open_tr( 'smallskip' );
      open_label( $f['cn_en'], 'td', 'Name (english):' );
      echo string_element( $f['cn_en'], 'td' );
    open_tr( 'smallskip' );
      open_label( $f['url_en'], 'td', 'Web site (english):' );
      echo string_element( $f['url_en'], 'td' );
    open_tr( 'smallskip' );
      open_label( $f['note_en'], 'td', 'Description (englisch):' );
      open_td( '', textarea_element( $f['note_en'] ) );
  close_fieldset();

  open_tr( 'bigskip' );
    open_td( 'right,colspan=2' );
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

close_fieldset();

if( $action === 'deleteGroup' ) {
  need( $groups_id );
  sql_delete_groups( $groups_id );
  js_on_exit( "flash_close_message($H_SQ".we('group deleted','Gruppe geloescht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}
?>
