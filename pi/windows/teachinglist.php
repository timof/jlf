<?php 
echo html_tag( 'h1', '', we('Teaching','Lehre') );

// kludge alert - make this configurable!
$allow_edit = 1;
$term_edit = 'S';
$year_edit = 2012;

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );
define( 'OPTION_TEACHING_EDIT', 1 );
$do_edit = ( $allow_edit ? ( $options & OPTION_TEACHING_EDIT ) : 0 );

$actions = array( 'update', 'deleteTeaching' );
if( $do_edit ) {
  $actions[] = 'save';
}
handle_action( $actions );

$filter_fields = array(
  'term' => array( 'default' => $term_edit )
, 'year' => array( 'default' => $year_edit, 'min' => '2011', 'max' => '2020' )
, 'REGEX' => 'size=20,auto=1'
);
if( ( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) && ( $do_edit ) ) {
  $filter_fields['term']['sources'] = 'default';
  // $filter_fields['term']['min'] = $filter_fields['term']['max'] = $filter_fields['term']['default'];
  $filter_fields['year']['sources'] = 'default';
  $filter_fields['year']['min'] = $filter_fields['year']['max'] = $filter_fields['year']['default'];
}
if( ! $do_edit ) {
  $filter_fields['term']['allow_null'] = $filter_fields['year']['allow_null'] = '0';
}

$f = init_fields( $filter_fields );

if( have_priv( 'teaching', 'list' ) ) {
  $f_teacher = filters_person_prepare( true, array( 'prefix' => 'F_teacher_' ) );
  $f_signer = filters_person_prepare( true, array( 'prefix' => 'F_signer_' ) );
  $f_submitter = filters_person_prepare( true, array( 'prefix' => 'F_submitter_' ) );
}

// debug( $f, 'f' );

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
      $teaching = sql_one_teaching( $teaching_id );
      $opts['rows'] = array( 'teaching' => $teaching );
    }
    // debug( $teaching_id, 'teaching_id' );
    // debug( $opts['rows'], 'rows' );

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
    , 'hours_per_week' => 'min=0.1,max=8'
    , 'co_teacher' => 'size=12'
    , 'participants_number'
    , 'signer_people_id'
    , 'note' => 'lines=3,cols=20'
    );
    if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
      // debug( $login_groups_ids );
      $fields['signer_groups_id']['pattern'] = $login_groups_ids;
      if( count( $login_groups_ids ) == 1 ) {
        $fields['signer_groups_id']['sources'] = 'default';
        $fields['signer_groups_id']['default'] = $login_groups_ids[ 0 ];
      }
    }
    $edit = init_fields( $fields, $opts );
    $edit = filters_person_prepare(
      array( 'teacher_people_id' => 'type=Tpeople_id', 'teacher_groups_id' => 'type=Tgroups_id' )
    , $opts + array( 'merge' => $edit )
    );
    $edit = filters_person_prepare(
      array( 'signer_people_id' => 'type=Tpeople_id', 'signer_groups_id' => 'type=Tgroups_id' )
    , $opts + array( 'merge' => $edit )
    );

    if( $edit['course_type']['value'] == 'FP' ) {
      $edit = init_fields( array(
          'course_title' => 'size=20,sources=default,default=FP'
        , 'credit_factor' => 'sources=default,default=1.000'
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
    $edit['teaching_id']['value'] = $teaching_id;

    $reinit = false;

    if( ( $action === 'save' ) && ! $edit['_problems'] ) {

      $values = array();
      foreach( $edit as $fieldname => $r ) {
        if( $fieldname[ 0 ] !== '_' )
          if( $fieldname['source'] !== 'keep' ) // no need to write existing blob
            $values[ $fieldname ] = $r['value'];
      }
      $values['term'] = $f['term']['value'];
      $values['year'] = $f['year']['value'];
      $values['submitter_people_id'] = $login_people_id;
      // debug( strlen( $values['pdf'] ), 'size of pdf' );
      // debug( $values, 'save: values' );
      if( $teaching_id ) {
        logger( "update teaching $teaching_id", 'update' );
        sql_update( 'teaching', $teaching_id, $values );
      } else {
        logger( "insert teaching", 'insert' );
        $teaching_id = sql_insert( 'teaching', $values );
      }
      $options &= ~OPTION_TEACHING_EDIT;
      $do_edit = false;
      $edit = false;
      js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
      reinit('reset');
    }

  }

} else {
  $edit = false;
}

// debug( $edit, 'edit' );

bigskip();

switch( $action ) {
  case 'deleteTeaching':
    need( $message > 0, we('no entry selected','kein Eintrag ausgewaehlt') );
    need_priv( 'teaching', 'delete', $message );
    sql_delete_teaching( $message );
    break;

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
    open_th( '', we('Teacher:','Lehrender:') );
    open_td();
      open_div();
        filter_group( $f['F_teacher_groups_id'] );
      close_div();

      if( ( $g_id = $f['F_teacher_groups_id']['value'] ) ) {
        open_div( 'smallskips' );
          filter_person( $f['F_teacher_people_id'], array( 'filters' => "groups_id=$g_id" ) );
        close_div();
      }

  open_tr();
    open_th( '', we('Submitter:','Erfasser:') );
    open_td();
      filter_person( $f['submitter_people_id'], array( 'filters' => 'privs >= 1' ) );
  open_tr();
    open_th( '', we('Signer:','Unterzeichner:') );
    open_td();
      open_div( 'smallskips' );
        filter_group( $f['F_signer_groups_id'] );
      close_div();
      if( ( $g_id = $f['F_signer_groups_id']['value'] ) ) {
        open_div( 'smallskips' );
          filter_person( $f['F_signer_people_id'], array( 'filters' => "groups_id=$g_id" ) );
        close_div();
      }
}

open_tr();
  open_th( '', we('search:','suche:') );
  open_td( '', string_element( $f['REGEX'] ) );

if( have_priv( 'teaching', 'create' ) ) {
  if( ! $do_edit ) {
    open_tr();
      open_th( 'center,colspan=2', 'Aktionen' );
    open_tr();
      open_td( 'colspan=2', inlink( 'teachinglist', array(
          'class' => 'bigbutton', 'text' => we('add entry','neuer Eintrag' )
        , 'options' => $options | OPTION_TEACHING_EDIT, 'teaching_id' => 0
      ) ) );
  }
}
close_table();

medskip();

if( $debug ) {
  debug( $f['_filters'], '_filters' );
}

teachinglist_view( $f['_filters'], $do_edit ? array( 'edit' => $edit ) : '' );


?>
