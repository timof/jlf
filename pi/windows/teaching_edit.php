<?php  // /pi/windows/teaching_edit.php

sql_transaction_boundary('*');

define( 'OPTION_ALLOW_ALL_TEACHERS', 0x01 );
define( 'OPTION_ALLOW_ALL_SIGNERS', 0x02 );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );
if( ! have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
  $options = 0;
}

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'teaching_id', 'global,type=u,sources=http self,set_scopes=self' );
init_var( 'year', "global,type=U4,sources=http self initval,set_scopes=self,initval=$teaching_survey_year" );
init_var( 'term', "global,sources=http self initval,set_scopes=self,initval=$teaching_survey_term" );

if( $teaching_id ) {
  need_priv( 'teaching', 'edit', $teaching_id );
} else {
  need_priv( 'teaching', 'create', "year=$year,term=$term" );
}

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {
  $problems = array();

  switch( $reinit ) {
    case 'init':
      $sources = 'http self initval';
      break;
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
    $year = $teaching['year'];
    $term = $teaching['term'];
  }

  $fields = array(
    'extern' => 'auto=1,text=extern'
  , 'lesson_type'
  , 'course_number' => 'size=2'
  , 'module_number' => 'size=3'
  , 'co_teacher' => 'size=12'
  , 'participants_number'
  , 'note' => 'lines=3,cols=40'
  );
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
  if( $options && OPTION_ALLOW_ALL_SIGNERS ) {
    $f = init_fields( $fields, $opts );
  } else {
    $f = filters_person_prepare( $fields, $opts );
  }

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

    $opts['merge'] = & $f;
    if( $options && OPTION_ALLOW_ALL_TEACHERS ) {
      $f = init_fields( array(
          'teacher_people_id' => 'basename=people_id,type=U'
        , 'teacher_groups_id' => array( 'basename' => 'groups_id', 'type' => 'U' )
        )
      , $opts
      );
    } else if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
      $f = filters_person_prepare( array(
          'teacher_people_id' => 'basename=people_id,type=U'
        , 'teacher_groups_id' => array( 'basename' => 'groups_id', 'type' => 'U' )
        )
      , $opts
      );
    } else {
      $g_teacher_pattern = $login_groups_ids;
      $f = filters_person_prepare( array(
          'teacher_people_id' => 'basename=people_id,type=U'
        , 'teacher_groups_id' => array( 'basename' => 'groups_id', 'type' => 'U', 'pattern' => $g_teacher_pattern )
        )
      , $opts
      );
    }

    $opts['merge'] = & $f;
    $p_new = $f['teacher_people_id']['value'];
    $g_new = $f['teacher_groups_id']['value'];

    $reset_position_data = false;
    $new_aff = 0;
    if( $p_new && $g_new ) { 
      $filters_aff = array( 'groups_id' => $g_new , 'people_id' => $p_new );
      if( ! ( $options & OPTION_ALLOW_ALL_TEACHERS ) ) {
        $filters_aff[ 'flag_deleted'] = 0;
      }
      $new_aff = sql_affiliations( $filters_aff, 'single_row=1,default=0' );
      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        $reset_position_data = ( $new_aff && ( $action === 'initPositionData' ) );
       } else {
        $reset_position_data = $new_aff;
       }
    }
    if( $reset_position_data ) {
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
  // handle lesson_type and related
  //

  $opts['merge'] = & $f;
  $t = $f['lesson_type']['value'];
  if( ( $t === 'X' /* (sabbatical) */ ) || ( $t === 'N' /* (none) */ ) ) {
    $f = init_fields( array(
        'course_title' => 'sources=initval,initval=X'
      , 'credit_factor' => 'sources=initval,initval=1.000'
      , 'teaching_factor' => 'sources=initval,initval=1'
      , 'teachers_number' => 'sources=initval,initval=1'
      , 'hours_per_week' => 'sources=initval,initval=0.0'
      )
    , $opts
    );
  } else if( $t === 'GP' ) {
    $f = init_fields( array(
        'course_title' => 'sources=initval,initval='.$t
      , 'credit_factor' => 'sources=initval,initval=0.500'
      , 'teaching_factor' => 'sources=initval,initval=1'
      , 'teachers_number' => 'sources=initval,initval=1'
      , 'hours_per_week' => array( 'format' => '%F.1', 'pattern' => array_keys( $choices_SWS_other ) )
      )
    , $opts
    );
  } else if( $t === 'P' ) {
    $f = init_fields( array(
        'course_title' => 'size=40'
      , 'credit_factor' => 'sources=initval,initval=0.500'
      , 'teaching_factor' => 'sources=initval,initval=1'
      , 'teachers_number' => 'sources=initval,initval=1'
      , 'hours_per_week' => array( 'format' => '%F.1', 'pattern' => array_keys( $choices_SWS_other ) )
      )
    , $opts
    );
  } else if( $t === 'FP' ) {
    $f = init_fields( array(
        'course_title' => 'sources=initval,initval='.$t
      , 'credit_factor' => 'sources=initval,initval=1.000'
      , 'teaching_factor' => 'sources=initval,initval=1'
      , 'teachers_number' => 'sources=initval,initval=1'
      , 'hours_per_week' => array( 'format' => '%F.1', 'pattern' => array_keys( $choices_SWS_FP ) )
      )
    , $opts
    );
  } else if( $t === 'FO' ) {
    $f = init_fields( array(
        'course_title' => 'size=40'
      , 'credit_factor' => 'sources=initval,initval=1.000'
      , 'teaching_factor' => 'sources=initval,initval=1'
      , 'teachers_number' => 'min=1,max=9'
      , 'hours_per_week' => array( 'format' => '%F.1', 'pattern' => array_keys( $choices_SWS_other ) )
      )
    , $opts
    );
  } else if( $t === 'EP' ) {
    $f = init_fields( array(
        'course_title' => 'size=40'
      , 'credit_factor' => 'sources=initval,initval=1.000'
      , 'teaching_factor' => 'sources=initval,initval=1'
      , 'teachers_number' => 'min=1,max=9'
      , 'hours_per_week' => array( 'format' => '%F.1', 'pattern' => array_keys( $choices_SWS_other ) )
      )
    , $opts
    );
  } else {
    $f = init_fields( array(
        'course_title' => 'size=40'
      , 'credit_factor' => 'sources=initval,initval=1.000'
      , 'teaching_factor' => 'min=1,max=4'
      , 'teachers_number' => 'min=1,max=9'
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
      $values['term'] = $term;
      $values['year'] = $year;

      $error_messages = sql_save_teaching( $teaching_id, $values, 'action=dryrun' );
      if( ! $error_messages ) {
        $teaching_id = sql_save_teaching( $teaching_id, $values, 'action=hard' );
        $info_messages[] = we('entry was saved','Eintrag wurde gespeichert');
        js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
        reinit('reset');
      }
    }
  }

}

bigskip();

if( $teaching_id ) {
  $v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'teaching', $teaching_id ) ) : '' );
  open_fieldset( 'old', we('teaching survey entry','Eintrag Lehrerfassung') . $v );
} else {
  open_fieldset( 'new', we('new teaching survey entry','neuer Eintrag Lehrerfassung') );
}
  open_div( 'bold center', we('term:','Semester:') . " $term $year" );

  open_fieldset('', we('teacher:','Lehrender:') );

    if( $f['extern']['value'] ) {
      open_fieldset( 'line'
      , label_element( $f['extteacher_cn'], '', 'Person: ' )
      , checkbox_element( $f['extern'] )
       . label_element( $f['extteacher_cn'], 'qquad', 'Name: '. string_element( $f['extteacher_cn'] ) )
      );
    } else {

      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {

        open_fieldset('line'
        , label_element( $f['teacher_groups_id'], '', 'Gruppe: ' )
        , selector_groups( $f['teacher_groups_id'] ) . hskip('2ex') . checkbox_element( $f['extern'] )
        );
        if( $f['teacher_groups_id']['value'] ) {
          if( $teaching_id && $g_new && $p_new && ! $new_aff ) {
            $options |= OPTION_ALLOW_ALL_TEACHERS;
          }
          if( $options & OPTION_ALLOW_ALL_TEACHERS ) {
            $filters = array();
          } else {
            $filters = array( 'groups_id' => $f['teacher_groups_id']['value'], 'flag_deleted' => 0 );
          }
          open_fieldset('line'
          , label_element( $f['teacher_people_id'], '', 'Person: ' )
          , selector_people( $f['teacher_people_id'], array( 'filters' => $filters ) )
            . hskip('2ex')
            . checkbox_element( array(
              'name' => 'options'
              , 'normalized' => $options
              , 'mask' => OPTION_ALLOW_ALL_TEACHERS
              , 'text' => we('show all','alle anzeigen' )
              , 'auto' => 1
              , 'title' => 'allow to select a person who is not (or no longer) a group member'
              ) )
          );
        }

      } else {

        if( $teaching_id && $p_new && $g_new && ! $new_aff ) {
          open_fieldset('line'
          , label_element( $f['teacher_groups_id'], '', we('group:','Gruppe:') )
          , alink_group( $g_new )
          );
          open_fieldset('line'
          , label_element( $f['teacher_people_id'], '', we('person:','Person:') )
          , alink_group( $g_new ) . we(' (not or no longer a group member)',' (kein Gruppenmitglied (mehr))' )
          );
        } else {
          $filters = array( 'groups_id' => $login_groups_ids );
          open_fieldset('line'
          , label_element( $f['teacher_groups_id'], '', 'Gruppe: ' )
          , selector_groups( $f['teacher_groups_id'], array( 'filters' => $filters ) )
          );
          if( $f['teacher_groups_id']['value'] ) {
            $filters = array( 'groups_id' => $g_new, 'flag_deleted' => 0 );
            open_fieldset('line'
            , label_element( $f['teacher_people_id'], '', 'Person: ' )
            , selector_people( $f['teacher_people_id'], array( 'filters' => $filters ) )
            );
          }
        }
      } // coordinator?

    } // extern?

  close_fieldset( /* teacher */ );

$teacher_id = $f['teacher_people_id']['value'];
$extern = $f['extern']['value'];
if( $teacher_id || $extern || $teaching_id ) {

  open_fieldset('table td:smallskipt;qquadr', we('position:','Stelle:') );
  // open_table( 'css td:smallskips;qquadr' );

    if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) && ( ! $extern ) ) {
  
      open_tr();
        open_td( '', we('teaching survey entry:','Datensatz Lehrerfassung:') );
        if( $new_aff ) {
          open_td( 'grey dottedtop dottedleft', inlink( 'person_view', array( 'people_id' => $new_aff['people_id'], 'text' => we('personal record: ','Personendatensatz: ') ) ) );
        }
      open_tr();
        open_fieldset( 'td line'
        , label_element( $f['typeofposition'], '', we('position:','Stelle:') )
        , selector_typeofposition( $f['typeofposition'], 'positionBudget=1' )
        );
        if( $new_aff ) {
          open_td( 'italic grey dottedleft', adefault( $choices_typeofposition, $new_aff['typeofposition'], we('unknown','unbekannt') ) );
        }
      open_tr();
        open_fieldset( 'td line'
        , label_element( $f['teaching_obligation'], '', we('teaching obligation: ','Lehrverpflichtung: ') )
        , selector_smallint( $f['teaching_obligation'] )
        );
        if( $new_aff ) {
          open_td( 'italic grey dottedleft', $new_aff['teaching_obligation'] );
        }
      open_tr();
        open_fieldset( 'td line'
        , label_element( $f['teaching_reduction'], '', we('reduction: ','Reduktion: ') )
        , selector_smallint( $f['teaching_reduction'] )
        );
        if( $new_aff ) {
          open_td( 'italic grey dottedleft', $new_aff['teaching_reduction'] );
        }
  
      if( $f['teaching_reduction']['value'] ) {
        open_tr();
          open_fieldset( 'td line'
          , label_element( $f['teaching_reduction_reason'], '', we('reason: ','Grund: ') )
          , string_element( $f['teaching_reduction_reason'] )
          );
          if( $new_aff ) {
            open_td( 'italic grey dottedleft', $new_aff['teaching_reduction_reason'] );
          }
      }
      open_tr();
        if( $new_aff ) {
          open_td();
          open_td( 'left dottedleft dottedbottom', inlink( 'self', array(
            'class' => 'button'
          , 'action' => 'initPositionData'
          , 'text' => we('<<< copy values from personal data','<<< Werte aus Personendaten übernehmen')
          ) ) );
        } else {
          open_td( 'comment', we('no group affiliation found - please enter position data manually!','keine Gruppenzuordnung gefunden - bitte Stellendaten manuell erfassen!' ) );
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
  
      open_tr();
        open_td();
        open_td( 'smallskips' );
         open_div( '', 'Falls diese Angaben nicht stimmen: ' );
         open_div( '', 'Bitte in den '
            . inlink( 'person_edit', "people_id=$teacher_id,text=Personendaten" ) 
            . ' korrigieren: die Daten werden von dort übernommen!'
         );
    }

    // close_table();
    close_fieldset( /* stelle */ );

}


if( $teacher_id || $extern || $teaching_id ) {
  $t = $f['lesson_type']['value'];

  open_fieldset('', we('course:','Veranstaltung:') );

    open_fieldset('line'
    , label_element( $f['lesson_type'], '', we('type:','Art:') )
    , selector_lesson_type( $f['lesson_type'] )
     . ( ( $t && ( $t !== 'X' ) && ( $t !== 'N' ) )
         ? html_span( 'qquadl italic small', we('credit factor: ','Anrechnungsfaktor: ').$f['credit_factor']['value'] )
         : ''
       )
    );

  if( $t && ( $t !== 'X' ) && ( $t !== 'N' ) ) {

    if( ( $t !== 'FP' ) && ( $t !== 'GP' )  ) {
      open_fieldset( 'line'
      , label_element( $f['course_title'], '', we('title:','Titel:') )
      , string_element( $f['course_title'] )
      );
    }

    open_fieldset( 'line'
    , label_element( $f['module_number'], '', 'Modul: ' )
    , string_element( $f['module_number'] )
    );

    open_fieldset( 'line'
    , label_element( $f['hours_per_week'], '', 'SWS: ' )
    , selector_SWS( $f['hours_per_week'], "lesson_type=$t" )
    );

    $vv_name = "KomVV_".$teaching_survey_term.'S'.$year.".pdf";
    $link = html_alink( "http://theosolid.qipc.org/$vv_name", array( 'class' => 'file', 'text' => $vv_name, 'target' => '_blank' ) );
    open_fieldset( 'line'
    , label_element( $f['course_number'], '', "Nr. $link" )
    , string_element( $f['course_number'] )
    );

    if( ( $t !== 'FP' ) && ( $t !== 'GP' ) && ( $t !== 'P' ) ) {

      if( ( $t != 'EP' ) && ( $t != 'FO' ) ) {
        open_fieldset( 'line'
        , label_element( $f['teaching_factor'], '', 'Abhaltefaktor: ' )
        , selector_smallint( $f['teaching_factor'] )
        );
  
        open_fieldset( 'line'
        , label_element( $f['credit_factor'], '', 'Anrechnungsfaktor: ' )
        , selector_credit_factor( $f['credit_factor'] )
        );
      }

      open_fieldset( 'line'
      , label_element( $f['teachers_number'], '', 'Anzahl Lehrende: ' )
      , selector_smallint( $f['teachers_number'] )
      );
        if( $f['teachers_number']['value'] > 1 ) {
          open_fieldset( 'line'
          , label_element( $f['co_teacher'], '', we('co-teachers:',' Mit-Lehrende: ') )
          , string_element( $f['co_teacher'] )
          );
        }

    }

    open_fieldset( 'line'
    , label_element( $f['participants_number'], '', we('participants:','Teilnehmer: ') )
    , int_element( $f['participants_number'] )
    );
  }

  if( $t ) {

    open_fieldset( 'line'
    , label_element( $f['note'], '', we('note:','Anmerkung:' ) )
    , textarea_element( $f['note'] )
    );

  }

  close_fieldset( /* veranstaltung */ );
}

if( ( $teacher_id || $extern || $teaching_id ) && $f['lesson_type']['value'] ) {

  open_fieldset( '', we('entry in the name of:','Eintrag im Namen von:') );

    if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
      open_fieldset('line'
      , label_element( $f['signer_groups_id'], '', we('Group:','Gruppe:' ) )
      , selector_groups( $f['signer_groups_id'] )
      );
      if( ( $sgi = $f['signer_groups_id']['value'] ) ) {
        if( ( $spi = $f['signer_people_id']['value'] ) ) {
          if( ! sql_affiliations( "groups_id=$sgi,people_id=$spi,flag_deleted=0,HEAD" ) ) {
            $options |= OPTION_ALLOW_ALL_SIGNERS;
          }
        }
        if( $options & OPTION_ALLOW_ALL_SIGNERS ) {
          $filters = array();
        } else {
          $filters = array( 'groups_id' => $f['signer_groups_id']['value'], 'flag_deleted' => 0, 'HEAD' );
        }
        open_fieldset('line'
        , label_element( $f['signer_people_id'], '', 'Person:' )
        , selector_people( $f['signer_people_id'] , array( 'filters' => $filters ) )
          . hskip('2ex')
          . checkbox_element( array(
            'name' => 'options'
            , 'normalized' => $options
            , 'mask' => OPTION_ALLOW_ALL_SIGNERS
            , 'text' => we('show all','alle anzeigen' )
            , 'auto' => 1
            , 'title' => 'allow to select a person who is not (or no longer) head of group'
            ) )
        );
      }
    } else { // not coordinator:
      if( count( $login_groups_ids ) != 1 ) {
        $t = selector_groups( $f['signer_groups_id'] , array( 'filters' => array( 'groups_id' => $login_groups_ids ) ) );
      } else {
        $signer_group = sql_one_group( $f['signer_groups_id']['value'] );
        $t = span_view( 'kbd quads bold', $signer_group['acronym'] );
      }
      open_fieldset('line' , label_element( $f['signer_groups_id'], '', we('Group:','Gruppe:' ) ) , $t );

      if( ( $sgi = $f['signer_groups_id']['value'] ) ) {
        open_fieldset('line'
        , label_element( $f['signer_people_id'], '', 'Person:' )
        , selector_people( $f['signer_people_id'] , array( 'filters' => "groups_id=$sgi,HEAD,flag_deleted=0" ) )
        );
      }
    }

  close_fieldset( /* unterschrift */ );
}

  open_div('right');
    if( $teaching_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button'
      , 'action' => 'deleteTeaching'
      , 'text' => we('delete entry','Eintrag löschen')
      , 'confirm' => we('really delete entry?','Eintrag wirklich löschen?')
      , 'inactive' => sql_delete_teaching( $teaching_id, 'action=dryrun' )
      ) );
    }
    echo reset_button_view();
    echo save_button_view();
  close_div();

close_fieldset( /* complete form */ );


if( $action === 'deleteTeaching' ) {
  need( $teaching_id > 0, we('no entry selected','kein Eintrag ausgewählt') );
  sql_delete_teaching( $teaching_id, 'action=hard' );
  js_on_exit( "flash_close_message($H_SQ".we('entry deleted','Eintrag gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}


?>
