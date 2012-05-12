<?php

echo html_tag( 'h1', '', we('Teaching','Lehre') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );
define( 'OPTION_TEACHING_EDIT', 1 );
$do_edit = ( $options & OPTION_TEACHING_EDIT );

$f = init_fields( array(
  'term' => 'default=W'    // kludge alert
, 'year' => 'default=2011'
, 'teacher_people_id' => 'type=Tpeople_id'
, 'teacher_groups_id' => 'type=Tgroups_id'
, 'submitter_people_id' => 'type=u'
) );

if( $do_edit ) {
  if( ! $f['term']['value'] )
    $f['term']['value'] = $f['term']['default'];
  if( ! $f['year']['value'] )
    $f['year']['value'] = $f['year']['default'];
}

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', we('Term:','Semester:') );
    open_td( 'oneline' );
      filter_term( $f['term'] );
      filter_year( $f['year'] );
if( have_priv( 'teaching', 'list' ) ) {
  open_tr();
    open_th( '', we('Group:','Gruppe:') );
    open_td();
      filter_group( $f['teacher_groups_id'] );
  open_tr();
    open_th( '', we('Submitter:','Erfasser:') );
    open_td();
      filter_person( $f['submitter_people_id'], array( 'filters' => 'privs >= 1' ) );
}
if( have_priv( 'teaching', 'create' ) ) {
  open_tr();
    open_th( 'center,colspan=2', 'Aktionen' );
  open_tr();
    open_td( 'colspan=2', inlink( 'teachinglist', array(
        'class' => 'bigbutton', 'text' => we('add entry','neuer Eintrag' )
      , 'options' => $options | OPTION_TEACHING_EDIT, 'teaching_id' => 0
    ) ) );
}
close_table();

$filters = $f['_filters'];


if( $do_edit ) {
  init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
  init_var( 'teaching_id', 'global,type=u,sources=http self,set_scopes=self' );

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
        error( 'cannot initialize - invalid $reinit' );
    }
  
    $opts = array(
      'flag_problems' => & $flag_problems
    , 'flag_modified' => 1
    , 'tables' => 'teaching'
    , 'failsafe' => 0   // means: possibly return with NULL value (flagged as error)
    , 'sources' => $sources
    , 'set_scopes' => 'self'
    );
    if( $action === 'save' ) {
      $flag_problems = 1;
    }
  
    if( $teaching_id ) {
      $teaching = sql_teaching( $teaching_id );
      $opts['rows'] = array( 'people' => $person );
    }
  
    $fields = array(
      'typeofposition'
    , 'teacher_groups_id'
    , 'teacher_people_id'
    , 'teaching_obligation' => 'min=0,max=8'
    , 'teaching_reduction' => 'min=0,max=8'
    , 'teaching_reduction_reason' => 'size=12'
    , 'course_type'
    , 'course_number' => 'size=2'
    , 'module_number' => 'size=3'
    , 'hours_per_week' => 'min=1,max=8'
    , 'co_teacher' => 'size=12'
    , 'participants_number'
    , 'signer_people_id'
    , 'note' => 'lines=3,cols=20'
    , 'signer_groups_id' => array( 'type' => 'U' )
    , 'signer_people_id' => 'type=U'
    );
    if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
      // debug( $login_groups_ids );
      $fields['signer_groups_id']['pattern'] = $login_groups_ids;
      if( count( $login_groups_ids ) == 1 ) {
        $fields['signer_groups_id']['sources'] = 'default';
        $fields['signer_groups_id']['default'] = $login_groups_ids[ 0 ];
      }
      // debug( $fields['signer_groups_id'], 'signer_groups_id' );
    }
    $edit = init_fields( $fields, $opts );

    if( $edit['course_type']['value'] == 'FP' ) {
      $edit = init_fields( array(
          'course_title' => 'size=20,sources=default,default=FP'
        , 'credit_factor' => 'sources=default,default=1.0'
        , 'teaching_factor' => 'min=1,max=3,sources=default,default=1'
        , 'teachers_number' => 'min=1,max=5,sources=default,default=1'
        )
      , $opts + array( 'merge' => $edit )
      );
      // debug( $edit['_problems'], 'with FP: _problems' );
    } else {
      $edit = init_fields( array(
          'course_title' => 'size=20'
        , 'credit_factor'
        , 'teaching_factor' => 'min=1,max=3'
        , 'teachers_number' => 'min=1,max=5'
        )
      , $opts + array( 'merge' => $edit )
      );
      // debug( $edit['_problems'], 'NON-FP: _problems' );
    }

    $reinit = false;
  }


} else {
  $edit = false;
}

// debug( $edit, 'edit' );



bigskip();

$actions = array( 'update', 'deleteTeaching' );
if( $do_edit ) {
  $actions[] = 'save';
}

handle_action( $actions );
switch( $action ) {
  case 'deleteTeaching':
    need( $message > 0, we('no entry selected','kein Eintrag ausgewaehlt') );
    need_priv( 'teaching', 'delete', $message );
    sql_delete_teaching( $message );
    break;

  case 'save':
    if( ! $edit['_problems'] ) {

      $values = array();
      foreach( $edit as $fieldname => $r ) {
        if( $fieldname[ 0 ] !== '_' )
          if( $fieldname['source'] !== 'keep' ) // no need to write existing blob
            $values[ $fieldname ] = $r['value'];
      }
      // debug( strlen( $values['pdf'] ), 'size of pdf' );
      debug( $values, 'save: values' );
      if( $teaching_id ) {
        // sql_update( 'teaching', $teaching_id, $values );
      } else {
        // $teaching_id = sql_insert( 'teaching', $values );
      }
      // reinit('reset');
      $options &= ~OPTION_TEACHING_EDIT;
      $edit = false;
      js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
    }
    break;
}

medskip();


teachinglist_view( $filters, $edit ? array( 'edit' => $edit ) : '' );

open_div( 'right medskip' );
  if( $edit ) {
      open_span( 'quads', inlink( 'teachinglist', array(
          'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
        , 'options' => $options & ~OPTION_TEACHING_EDIT
      ) ) );
  } else {
  }
close_div();

?>
