<?php

echo html_tag( 'h1', '', we('Teaching','Lehre') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );
define( 'TEACHING_OPTION_EDIT', 1 );

$f = init_fields( array(
  'term'
, 'year'
, 'teacher_people_id' => 'type=Tpeople_id'
, 'teacher_groups_id' => 'type=Tgroups_id'
, 'submitter_people_id' => 'type=u'
) );

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
  $filters = $f['_filters'];
}
close_table();

$filters = $f['_filters'];
if( ! have_priv( 'teaching', 'list' ) ) {
  $filters += array( 'submitter_people_id' => $login_people_id );
}


if( $options & TEACHING_OPTION_EDIT ) {
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
    , 'term', 'year'
    , 'teacher_groups_id', 'teacher_people_id'
    , 'teaching_obligation' => 'min=0,max=8'
    , 'teaching_reduction' => 'min=0,max=8'
    , 'teaching_reduction_reason'
    );
    $edit = init_fields( $fields, $opts );


    $reinit = false;
  }




} else {
  $edit = false;
}

// debug( $edit, 'edit' );



bigskip();

handle_action( array( 'update', 'deleteTeaching' ) );
switch( $action ) {
  case 'deleteTeaching':
    need( $message > 0, we('no entry selected','kein Eintrag ausgewaehlt') );
    need_priv( 'teaching', 'delete', $message );
    sql_delete_teaching( $message );
    break;
}

medskip();


teachinglist_view( $filters, $edit ? array( 'edit' => $edit ) : '' );

open_div( 'right' );
  if( $edit ) {
      open_span( 'quads', inlink( 'teachinglist', array(
          'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
        , 'options' => $options & ~TEACHING_OPTION_EDIT
      ) ) );
  } else {
    if( have_priv( 'teaching', 'edit' ) ) {
      open_span( 'quads', inlink( 'teachinglist', array(
          'class' => 'edit', 'text' => we('add entry','Eintrag hinzufügen' )
        , 'options' => $options | TEACHING_OPTION_EDIT, 'teaching_id' => 0
      ) ) );
    }
  }
close_div();

?>
