<?php

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'groups_id', 'global,type=u,sources=self http,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {
  $problems = array();

  switch( $reinit ) {
    case 'init':
      $sources = 'http self keep default';
      break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'keep default';
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
    , 'note' => 'lines=4,cols=80'
    , 'cn_en' => 'size=40'
    , 'url_en' => 'size=40'
    , 'note_en' => 'rows=4,cols=40'
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
            if( $fieldname['source'] !== 'keep' ) // no need to write existing blob
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
  open_fieldset( 'small_form old', we('permanent data for group','Stammdaten Gruppe') );
} else {
  open_fieldset( 'small_form new', we('new group','neue Gruppe') );
}
  flush_problems();
  open_table('small_form hfill');
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['acronym'] ), we('Short Name:','Kurzname:') );
      open_td( 'oneline' );
        echo string_element( $f['acronym'] );

  if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['acronym'] ), we('Attributes:','Attribute:') );
      open_td();
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
      open_td( array( 'label' => $f['head_people_id'] ), we('Group leader:','Leiter der Gruppe:' ) );
      open_td();
        selector_people( $f['head_people_id']
        , array( 'filters' => "groups_id=$groups_id" , 'more_choices' => array( '0' => we(' - vacant - ',' - vakant - ' ) ) )
        );

    open_tr('medskip');
      open_td( array( 'label' => $f['head_people_id'] ), we('Secretary:','Sekretariat:' ) );
      open_td();
        selector_people( $f['secretary_people_id']
        , array( 'filters' => "groups_id=$groups_id" , 'more_choices' => array( '0' => we(' - vacant - ',' - vakant - ' ) ) )
        );
}

    open_tr( 'medskip' );
      open_td('colspan=2');
    open_tr( 'smallskip' );
      open_th( 'colspan=2', we('permanent data (in German)', 'Daten der Arbeitsgruppe (deutsch):') );
    open_tr( 'smallskip' );
      open_td( array( 'label' => $f['cn'] ), we('Name of group:','Name der Gruppe:') );
      open_td( '', string_element( $f['cn'] ) );
    open_tr( 'smallskip' );
      open_td( array( 'label' => $f['url'] ), we('Internet page:','Internetseite:') );
      open_td( '', string_element( $f['url'] ) );
    open_tr( 'smallskip' );
      open_td( array( 'label' => $f['note'] ), we('Description:','Kurzbeschreibung:') );
      open_td( '', textarea_element( $f['note'] ) );

    open_tr( 'medskip' );
      open_td('colspan=2');
    open_tr( 'smallskip' );
      open_th( 'colspan=2', we('optionally: englisch version of group data:','optional: englische Fassung der Daten:') );
    open_tr( 'smallskip' );
      open_td( array( 'label' => $f['cn_en'] ), we('Name (english):','Name (englisch):') );
      open_td( '', string_element( $f['cn_en'] ) );
    open_tr( 'smallskip' );
      open_td( array( 'label' => $f['url_en'] ), we('Internet page (english):','Internetseite (englisch):') );
      open_td( '', string_element( $f['url_en'] ) );
    open_tr( 'smallskip' );
      open_td( array( 'label' => $f['note_en'] ), we('Description (english):','Kurzbeschreibung (englisch):') );
      open_td( '', textarea_element( $f['note_en'] ) );

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
        }
        if( $groups_id && ! $f['_changes'] )
          template_button();
        reset_button( $f['_changes'] ? '' : 'display=none' );
        // submission_button( $f['_changes'] ? '' : 'display=none' );
        submission_button();
  close_table();

close_fieldset();

if( $action === 'deleteGroup' ) {
  need( $groups_id );
  sql_delete_groups( $groups_id );
  js_on_exit( "flash_close_message($H_SQ".we('group deleted','Gruppe geloescht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}
?>
