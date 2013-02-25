<?php 

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );


$do_edit = ( $teaching_survey_open ? ( $options & OPTION_TEACHING_EDIT ) : 0 );

$actions = array( 'update', 'deleteTeaching', 'download' );
if( $do_edit ) {
  $actions[] = 'save';
}
handle_action( $actions );

if( $action === 'deleteTeaching' ) {
  init_var( 'teaching_id', 'global,type=u,sources=http self,set_scopes=self' );
  need( $teaching_id > 0, we('no entry selected','kein Eintrag ausgewählt') );
  sql_delete_teaching( $teaching_id );
  $do_edit = 0;
  $options = ( $options & ~OPTION_TEACHING_EDIT );
  $teaching_id = 0;
}


$filter_fields = array(
  'term' => array( 'default' => '0', 'initval' => $teaching_survey_term )
, 'year' => array( 'default' => '0', 'initval' => $teaching_survey_year, 'min' => '2011', 'max' => '2020' )
, 'REGEX' => 'size=20,auto=1'
);
if( $do_edit ) {
  $filter_fields['term']['sources'] = 'initval';
  $filter_fields['year']['sources'] = 'initval';
  $filter_fields['year']['min'] = $filter_fields['year']['max'] = $filter_fields['year']['initval'];
}
if( ! $do_edit ) {
  $filter_fields['term']['allow_null'] = $filter_fields['year']['allow_null'] = '0';
}

$f = init_fields( $filter_fields );
$filters = $f['_filters'];
// debug( $f, 'f' );

if( have_priv( 'teaching', 'list' ) ) {
  $f_teacher = filters_person_prepare( true, 'sql_prefix=teacher_,cgi_prefix=F_teacher_,auto_select_unique' );
  // debug( $f_teacher, 'f_teacher' );
  $f_signer = filters_person_prepare( true, 'sql_prefix=signer_,cgi_prefix=F_signer_,auto_select_unique' );
  $f_creator = filters_person_prepare( true, 'sql_prefix=creator_,cgi_prefix=F_creator_,auto_select_unique' );
  $filters = array_merge( $filters, $f_teacher['_filters'], $f_signer['_filters'], $f_creator['_filters'] );
  // unset( $filters['creator_groups_id'] );
}
// debug( $filters, 'filters' );

// debug( $f, 'f' );

if( $do_edit ) {

  init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
  init_var( 'teaching_id', 'global,type=u,sources=http self,set_scopes=self' );

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
        error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'teaching,init' );
    }

    $opts = array(
      'flag_problems' => & $flag_problems
    , 'flag_modified' => 1
    , 'tables' => 'teaching'
    , 'failsafe' => 0   // means: possibly return with NULL value (flagged as error)
    , 'sources' => $sources
    , 'set_scopes' => 'self'
    , 'auto_select_unique' => true
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
    , 'extern' => 'auto=1,text=extern'
    , 'teaching_obligation' => 'min=0,max=8'
    , 'teaching_reduction' => 'min=0,max=8'
    , 'teaching_reduction_reason' => 'size=12'
    , 'course_type'
    , 'course_number' => 'size=2'
    , 'module_number' => 'size=3'
    , 'co_teacher' => 'size=12'
    , 'participants_number'
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
    $opts['merge'] = & $edit;

    if( $edit['extern']['value'] ) {
      $edit = init_fields( array( 'extteacher_cn' => 'size=20,type=H' ), $opts );
    } else {
      $edit = filters_person_prepare( array(
          'teacher_people_id' => 'basename=people_id,type=U'
        , 'teacher_groups_id' => 'basename=groups_id,type=U'
        )
      , $opts
      );
    }
    $opts['merge'] = & $edit;
    $edit = filters_person_prepare( array(
        'signer_people_id' => 'basename=people_id,type=U'
      , 'signer_groups_id' => 'basename=groups_id,type=U'
      )
    , $opts
    );

    $opts['merge'] = & $edit;
    if( $edit['course_type']['value'] === 'X' ) {
      $edit = init_fields( array(
          'course_title' => 'size=20,sources=default,default=X'
        , 'credit_factor' => 'sources=default,default=1.000'
        , 'teaching_factor' => 'min=1,max=3,sources=default,default=1'
        , 'teachers_number' => 'min=1,max=5,sources=default,default=1'
        , 'hours_per_week' => 'sources=default,default=0.0'
        )
      , $opts
      );
    } else if( $edit['course_type']['value'] === 'FP' ) {
      $edit = init_fields( array(
          'course_title' => 'size=20,sources=default,default=FP'
        , 'credit_factor' => 'sources=default,default=1.000'
        , 'teaching_factor' => 'min=1,max=3,sources=default,default=1'
        , 'teachers_number' => 'min=1,max=5,sources=default,default=1'
        , 'hours_per_week' => array( 'format' => '%F.1', 'pattern' => array_keys( $choices_SWS_FP ) )
        )
      , $opts
      );
      // debug( $edit['_problems'], 'with FP: _problems' );
    } else {
      $edit = init_fields( array(
          'course_title' => 'size=20'
        , 'credit_factor'
        , 'teaching_factor' => 'min=1,max=3'
        , 'teachers_number' => 'min=1,max=5'
        , 'hours_per_week' => array( 'format' => '%F.1', 'pattern' => array_keys( $choices_SWS_other ) )
        )
      , $opts
      );
      // debug( $edit['_problems'], 'NON-FP: _problems' );
    }
    $edit['teaching_id']['value'] = $teaching_id;

    $reinit = false;

    if( ( $action === 'save' ) && ! $edit['_problems'] ) {

      $values = array();
      foreach( $edit as $fieldname => $r ) {
        if( $fieldname[ 0 ] !== '_' )
          $values[ $fieldname ] = $r['value'];
      }
      $values['term'] = $f['term']['value'];
      $values['year'] = $f['year']['value'];
      $teaching_id = sql_save_teaching( $teaching_id, $values );
      $options = (int)$options & ~(int)OPTION_TEACHING_EDIT;
      $do_edit = false;
      $edit = false;
      js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
      reinit('reset');
    }

  }
  // debug( $edit, 'edit' );

} else {
  $edit = false;
}

if( $global_format == 'csv' ) {
  // download request
  need_priv( 'teaching', 'list' );
  // echo "[[start: [$global_format] ]]";
  teachinglist_view( $filters, array( 'format' => $global_format ) );
  // echo "[[end]]";
  return;
}



bigskip();

echo html_tag( 'h1', '', we('Teaching','Lehre') );

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', html_span( 'floatright', filter_reset_button( $f ) ) .'Filter' );
  open_tr();
    open_th( '', we('Term / year:','Semester / Jahr:') );
    open_td( 'oneline' );
      if( $do_edit ) {
        echo $f['term']['value'] . ' / ' . $f['year']['value'];
      } else {
        echo filter_term( $f['term'] );
        echo ' / ';
        echo filter_year( $f['year'] );
      }

if( have_priv( 'teaching', 'list' ) ) {
  open_tr();
    open_th( '', we('Teacher:','Lehrender:') );
    open_td();
      open_div( '', filter_group( $f_teacher['groups_id'] ) );

      if( ( $g_id = $f_teacher['groups_id']['value'] ) ) {
        open_div( 'smallskips', filter_person( $f_teacher['people_id'], array( 'filters' => "groups_id=$g_id" ) ) );
      }

  open_tr();
    open_th( '', we('Signer:','Unterzeichner:') );
    open_td();
      open_div( 'smallskips', filter_group( $f_signer['groups_id'] ) );
      if( ( $g_id = $f_signer['groups_id']['value'] ) ) {
        open_div( 'smallskips', filter_person( $f_signer['people_id'], array( 'filters' => "groups_id=$g_id" ) ) );
      }

  open_tr();
    open_th( '', we('Submitter:','Erfasser:') );
    open_td();
      open_div( 'smallskips', filter_group( $f_creator['groups_id'] ) );
      if( ( $g_id = $f_creator['groups_id']['value'] ) ) {
        open_div( 'smallskips', filter_person( $f_creator['people_id'], array( 'filters' => "groups_id=$g_id,privs >= 1" ) ) );
      }
}

open_tr();
  open_th( '', we('search:','suche:') );
  open_td( '', string_element( $f['REGEX'] ) . filter_reset_button( $f['REGEX'] ) );

if( have_priv( 'teaching', 'create' ) ) {
  if( ! $do_edit ) {
    open_tr();
      open_th( 'center,colspan=2', 'Aktionen' );
    open_tr();
      open_td( 'colspan=2', inlink( 'teachinglist', array(
          'class' => 'bigbutton', 'text' => we('add entry','neuer Eintrag' )
        , 'options' => $options | OPTION_TEACHING_EDIT, 'teaching_id' => 0
      ) ) );
    if( have_priv( 'teaching', 'list' ) ) {
      open_tr();
        open_td( 'colspan=2', inlink( 'teachinglist', array(
            'class' => 'bigbutton', 'format' => 'csv', 'window' => 'download'
          , 'text' => we('download CSV','CSV erzeugen' )
        ) ) );
      if( ( "{$f['year']['value']}" !== '0' ) && ( "{$f['term']['value']}" !== '0' ) ) {
        open_tr();
          open_td( 'colspan=2', inlink( 'teachinganon', array(
              'class' => 'bigbutton', 'window' => 'teachinganon'
            , 'text' => we('anonymized List','anonymisierte Liste' )
            , 'year' => $f['year']['value'], 'term' => $f['term']['value']
          ) ) );
      }
    }
  }
}
close_table();

medskip();

if( $debug ) {
  // debug( $filters, 'filters' );
}

if( $do_edit ) {
  open_div( 'medskips center bold', we('edit entry:','Eintrag bearbeiten:') );
  teachingsurvey_form( $edit );
  open_div( 'medskips center bold', ( $teaching_id ? we( 'other ',' andere ' ) : '' ) . we('existing entries:','vorhandene Eintraege:' ) );
}

teachinglist_view( $filters, $do_edit ? "do_edit=1,edit_teaching_id=$teaching_id" : array() );

?>
