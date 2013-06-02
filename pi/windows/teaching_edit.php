<?php 


init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'teaching_id', 'global,type=u,sources=http self,set_scopes=self' );
need_priv( 'teaching', $teaching_id ? 'edit' : 'create', $teaching_id );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {
  $problems = array();

  switch( $reinit ) {
    case 'init':
      $sources = 'http self initval';
      break;
 //   case 'self':
 //     $sources = 'self';
 //     break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'initval';
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

  $fields = array(
    'extern' => 'auto=1,text=extern'
  , 'course_type'
  , 'course_number' => 'size=2'
  , 'module_number' => 'size=3'
  , 'co_teacher' => 'size=12'
  , 'participants_number'
  , 'note' => 'lines=3,cols=40'
  );
  if( $teaching_id ) {
    $fields['term'] = 'sources=initval';
    $fields['year'] = 'sources=initval';
  } else if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    $fields['term'] = "sources=http initval,initval=$teaching_survey_term";
    $fields['year'] = "sources=http initval,initval=$teaching_survey_year";
  } else {
    $fields['term'] = "sources=initval,initval=$teaching_survey_term";
    $fields['year'] = "sources=initval,initval=$teaching_survey_year";
  }
  $f = init_fields( $fields, $opts );

  //
  // handle signer:
  //

  $fields = array(
    'signer_groups_id' => array( 'type' => 'U', 'basename' => 'groups_id' )
  , 'signer_people_id' => array( 'type' => 'U' ,'basename' => 'people_id' )
  );
  if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    $fields['signer_groups_id']['pattern'] = $login_groups_ids;
    if( count( $login_groups_ids ) == 1 ) {
      $fields['signer_groups_id']['sources'] = 'initval';
      $fields['signer_groups_id']['initval'] = $login_groups_ids[ 0 ];
    }
  }
  $opts['merge'] = & $f;
  $f = filters_person_prepare( $fields, $opts );

  //
  // handle teacher and related: obligation, reduction, ...
  //

  if( $f['extern']['value'] ) {

    $opts['merge'] = & $f;
    $f = init_fields( array(
        'extteacher_cn' => 'size=20,type=H'
      , 'typeofposition' => 'sources=initval,initval=o'
      , 'teaching_obligation' => 'sources=initval,initval=0'
      , 'teaching_reduction' => 'sources=initval,initval=0'
      , 'teaching_reduction_reason' => 'type=a,sources=initval,initval='
      , 'teacher_people_id' => 'type=u,sources=initval,initval=0'
      , 'teacher_groups_id' => 'type=u,sources=initval,initval=0'
      )
    , $opts
    );

  } else {

    $p_prev = adefault( $jlf_persistent_vars['self'], 'teacher_people_id', 0 );
    $g_prev = adefault( $jlf_persistent_vars['self'], 'teacher_groups_id', 0 );
    $opts['merge'] = & $f;
    $f = filters_person_prepare( array(
        'teacher_people_id' => 'basename=people_id,type=U'
      , 'teacher_groups_id' => 'basename=groups_id,type=U'
      )
    , $opts
    );

    $opts['merge'] = & $f;
    $p_new = $f['teacher_people_id']['value'];
    $g_new = $f['teacher_groups_id']['value'];
    $new_aff = false;
    if( $p_new && $g_new && ( ( $p_new != $p_prev ) || ( $g_new != $g_prev ) ) ) {
      $new_aff = sql_affiliations( "people_id=$p_new,groups_id=$g_new", 'single_row=1,default=0' );
    }
    if( $new_aff ) {
      $f = init_fields( array(
          'extteacher_cn' => 'initval=,sources=initval'
        , 'typeofposition' => 'sources=initval,initval='.$new_aff['typeofposition']
        , 'teaching_obligation' => 'min=0,max=8,sources=initval,initval='.$new_aff['teaching_obligation']
        , 'teaching_reduction' => 'min=0,max=8,sources=initval,initval='.$new_aff['teaching_reduction']
        , 'teaching_reduction_reason' => 'sources=initval,initval='.$new_aff['teaching_reduction_reason']
        )
      , $opts
      );
    } else if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
      $f = init_fields( array(
          'extteacher_cn' => 'initval=,sources=initval'
        , 'typeofposition'
        , 'teaching_obligation' => 'min=0,max=8'
        , 'teaching_reduction' => 'min=0,max=8'
        , 'teaching_reduction_reason'
        )
      , $opts
      );
    } else {
      $f = init_fields( array(
          'extteacher_cn' => 'initval=,sources=initval'
        , 'typeofposition' => 'sources=self'
        , 'teaching_obligation' => 'sources=self'
        , 'teaching_reduction' => 'sources=self'
        , 'teaching_reduction_reason' => 'sources=self'
        )
      , $opts
      );
    }
  }

  //
  // handle course_type and related
  //

  $opts['merge'] = & $f;
  $t = $f['course_type']['value'];
  if( $t === 'X' ) { // sabbatical
    $f = init_fields( array(
      'course_title' => 'sources=initval,initval=X'
      , 'credit_factor' => 'sources=initval,initval=1.000'
      , 'teaching_factor' => 'sources=initval,initval=1'
      , 'teachers_number' => 'sources=initval,initval=1'
      , 'hours_per_week' => 'sources=initval,initval=0.0'
      )
    , $opts
    );
  } else if( ( $t === 'GP' ) || ( $t === 'FP' ) ) {
    $f = init_fields( array(
        'course_title' => 'sources=initval,initval='.$t
      , 'credit_factor' => 'sources=initval,initval=1.000'
      , 'teaching_factor' => 'sources=initval,initval=1'
      , 'teachers_number' => 'sources=initval,initval=1'
      , 'hours_per_week' => array( 'format' => '%F.1', 'pattern' => array_keys( $choices_SWS_FP ) )
      )
    , $opts
    );
  } else {
    $f = init_fields( array(
        'course_title' => 'size=20'
      , 'credit_factor'
      , 'teaching_factor' => 'min=1,max=3'
      , 'teachers_number' => 'min=1,max=5'
      , 'hours_per_week' => array( 'format' => '%F.1', 'pattern' => array_keys( $choices_SWS_other ) )
      )
    , $opts
    );
  }

  $reinit = false;

  if( ( $action === 'save' ) ) {
    if( $f['_problems'] ) {
      $error_messages[] = we('saving failed','Speichern fehlgeschlagen' );

    } else {

      $values = array();
      foreach( $f as $fieldname => $r ) {
        if( $fieldname[ 0 ] !== '_' )
          $values[ $fieldname ] = $r['value'];
      }
      if( ! $teaching_id ) {
        // $values['term'] = $teaching_survey_term;
        // $values['year'] = $teaching_survey_year;
      }
      $teaching_id = sql_save_teaching( $teaching_id, $values );
      $info_messages[] = we('entry was saved','Eintrag wurde gespeichert');
      unset( $f );
      js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
      reinit('reset');
    }
  }

}

bigskip();

if( $teaching_id ) {
  open_fieldset( 'old', we('teaching survey entry','Eintrag Lehrerfassung') );
} else {
  open_fieldset( 'new', we('new teaching survey entry','neuer Eintrag Lehrerfassung') );
}
  flush_all_messages();

  open_fieldset('table', we('term:','Semester:') );

    if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
      
    } else {
  
  
    }

  close_fieldset();

  open_fieldset('table', we('teacher:','Lehrender:') );

    open_tr('td:smallskipt');
      open_td( '', 'Person:' );
      open_td();
        if( $f['extern']['value'] ) {
          open_div( 'left', checkbox_element( $f['extern'] ) );
          open_div( 'smallskipt', label_element( $f['extteacher_cn']['class'], 'qquad', 'Name: '. string_element( $f['extteacher_cn'] ) ) );
        } else {
          $filters = array();
          if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
            $filters['groups_id'] = $login_groups_ids;
          }
          open_div( '',
            selector_groups( $f['teacher_groups_id'], array( 'filters' => $filters ) )
            . hskip('2ex')
            . checkbox_element( $f['extern'] )
          );
          if( $f['teacher_groups_id']['value'] ) {
            $filters = array( 'groups_id' => $f['teacher_groups_id']['value'] );
            open_div( 'smallskipt oneline', selector_people( $f['teacher_people_id'], array( 'filters' => $filters ) ) );
          }
        }

$teacher_id = $f['teacher_people_id']['value'];
$extern = $f['extern']['value'];
if( $teacher_id || $extern || $teaching_id ) {

  close_fieldset();
  open_fieldset('table', we('position:','Stelle:') );

  if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) && ( ! $extern ) ) {
    open_tr();
      open_td( '', label_element( $f['typeofposition'], '', we('position:','Stelle:') ) );
      open_td( '', selector_typeofposition( $f['typeofposition'] ) );
    open_tr();
      open_td( '', label_element( $f['teaching_obligation'], '', we('teaching obligation: ','Lehrverpflichtung: ') ) );
      open_td( '', selector_smallint( $f['teaching_obligation'] ) );
    open_tr();
      open_td( '', label_element( $f['teaching_reduction'], '', we('reduction: ','Reduktion: ') ) );
      open_td( '', selector_smallint( $f['teaching_reduction'] ) );
    if( $f['teaching_reduction']['value'] ) {
      open_tr();
        open_td( '', label_element( $f['teaching_reduction_reason'], '', we('reason: ','Grund: ') ) );
        open_td( '', string_element( $f['teaching_reduction_reason'] ) );
    }

  } else {

      $t = $f['typeofposition']['value'];
      $tt = adefault( $choices_typeofposition, $t, we('unknown','unbekannt') );
      open_tr();
        open_td( '', "$t ($tt)" );
        open_td( '', we('teaching obligation: ','Lehrverpflichtung: ') . $f['teaching_obligation']['value'] );
    if( ( $t = $f['teaching_reduction']['value'] ) ) {
      open_tr('medskipb');
        open_td('', we('reduction: ','Reduktion: ') . $t );
        open_td('', we('reason: ','Grund: ') . $f['teaching_reduction_reason']['value'] );
    }
  }

  close_fieldset();

  open_fieldset('table', we('course:','Veranstaltung:') );
    $t = $f['course_type']['value'];
    open_tr();
      open_td( '', label_element( $f['course_type'], '', we('type:','Art:') ) );
      open_td( '', selector_course_type( $f['course_type'] ) );

  if( $t && ( $t !== 'X' ) ) {

    if( ( $t !== 'FP' ) && ( $t !== 'GP' )  ) {
      open_tr();
        open_td( '', label_element( $f['course_title'], '', we('title:','Titel:') ) );
        open_td( '', string_element( $f['course_title'] ) );
    }

    open_tr();
      open_td( '', label_element( $f['module_number'], '', 'Modul: ' ) );
      open_td( '', string_element( $f['module_number'] ) );

    open_tr();
      open_td( '', label_element( $f['hours_per_week'], '', 'SWS: ' ) );
      open_td( '', selector_SWS( $f['hours_per_week'], "course_type=$t" ) );

    $vv_name = "KomVV_".$teaching_survey_term."S".$teaching_survey_year.".pdf";
    $link = html_alink( "http://theosolid.qipc.org/$vv_name", array( 'class' => 'file', 'text' => $vv_name, 'target' => '_blank' ) );
    open_tr();
      open_td( '', label_element( $f['course_number'], '', "Nr. $link" ) );
      open_td( '', string_element( $f['course_number'] ) );

    if( ( $t !== 'FP' ) && ( $t !== 'GP' )  ) {

      open_tr();
        open_td( '', label_element( $f['teaching_factor'], '', 'Abhaltefaktor: ' ) );
        open_td( '', selector_smallint( $f['teaching_factor'] ) );

      open_tr();
        open_td( '', label_element( $f['credit_factor'], '', 'Anrechnungsfaktor: ' ) );
        open_td( '', selector_credit_factor( $f['credit_factor'] ) );

      open_tr();
        open_td( '', label_element( $f['teachers_number'], '', 'Anzahl Lehrende: ' ) );
        open_td( '', selector_smallint( $f['teachers_number'] ) );
        if( $f['teachers_number']['value'] > 1 ) {
          open_tr();
            open_td( '', label_element( $f['co_teacher'], '', we('co-teachers:',' Mit-Lehrende: ') ) );
            open_td( '', string_element( $f['co_teacher'] ) );
        }

    }

    open_tr('smallskip');
      open_td( '', label_element( $f['participants_number'], '', we('participants:','Teilnehmer: ') ) );
      open_td( '', int_element( $f['participants_number'] ) );
  }

  if( $t ) {

    open_tr('medskips');
      open_td( '', label_element( $f['note'], '', we('note:','Anmerkung:' ) ) );
      open_td( '', textarea_element( $f['note'] ) );

    open_tr('solidtop td:smallskipt');
      open_td( '', label_element( $f['signer_people_id'], '', we('entry made for:','Eintrag im Namen von:' ) ) );
      open_td();
        open_div();
          if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
            echo selector_groups( $f['signer_groups_id'] );
          } else if( count( $login_groups_ids ) != 1 ) {
            echo selector_groups( $f['signer_groups_id'] , array( 'filters' => array( 'groups_id' => $login_groups_ids ) ) );
          } else {
            $signer_group = sql_one_group( $f['signer_groups_id']['value'] );
            open_span( 'kbd quads bold', $signer_group['acronym'] );
          }
        close_div();
        if( ( $sgi = $f['signer_groups_id']['value'] ) ) {
          open_div( 'smallskipt', selector_people( $f['signer_people_id'] , array( 'filters' => "groups_id=$sgi,HEAD" ) ) );
        }

  }

} // have teacher_people_id

  close_fieldset();

  open_div('right');
    if( $teaching_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button'
      , 'action' => 'deleteTeaching'
      , 'text' => we('delete entry','Eintrag löschen')
      , 'confirm' => we('really delete entry?','Eintrag wirklich löschen?')
      , 'inactive' => sql_delete_teaching( $teaching_id, 'check' )
      ) );
    }
    echo reset_button_view();
    echo save_button_view();
  close_div();

close_fieldset();


if( $action === 'deleteTeaching' ) {
  need( $teaching_id > 0, we('no entry selected','kein Eintrag ausgewählt') );
  sql_delete_teaching( $teaching_id );
  js_on_exit( "flash_close_message($H_SQ".we('entry deleted','Eintrag gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}


?>
