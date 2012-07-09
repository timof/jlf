<?php

function form_login() {
  open_fieldset( 'class=small_form,style=padding:2em;', we('Login','Anmelden') );
    // if( $GLOBALS['valid_cookie_received'] ) {
      flush_problems();
      hidden_input( 'l', 'login' );
      bigskip();
      open_table('small_form');
        open_tr('medskip');
          open_td( 'label quads', we('user-id: ','Benutzerkennung: ') );
          open_td( 'kbd', string_element( array( 'name' => 'uid', 'size' => 20 ) ) );
        open_tr('medskip');
          open_td( 'label quads', we('password: ','Passwort: ') );
          open_td( 'kbd', html_tag( 'input', 'type=password,size=8,name=password,value=', NULL ) );
        open_tr('medskip');
          open_td();
          open_td('right');
            submission_button( 'login=nop,text='.we('back', 'zurück') );
            quad();
            submission_button( 'action=,text='.we('log in','Anmelden') );
      close_table();
    // } else {
//       open_div( 'warn bigskips qquads' , we(" You seem to have disabled cookies in your browser. Please switch on cookie support for this site!  "
//                                          , " Cookie-Unterst&uuml;tzung ihres Browsers scheint ausgeschaltet zu sein. Bitte erlauben Sie Cookies f&uum;r diese Webseite! " ) );
// 
//       open_div( 'right' );
//         submission_button( 'login=nop,text='.we('back', 'zurück') );
//       close_div();
//     }
      bigskip();
  close_fieldset();
}

function teachingsurvey_form( $edit ) {
  global $login_groups_ids, $choices_typeofposition;

  if( $edit ) {
    need_priv( 'teaching', 'edit', $edit['teaching_id']['value'] /* att: $edit is _not_ always a complete row from 'teaching' (creator_*!) */ );
    $edit_teaching_id = adefault( $edit, array( array( 'teaching_id', 'value' ) ), 0 );
  } else {
    need_priv( 'teaching', 'create' );
  }

  open_table('list hfill');
    open_list_head( 'teacher', we('teacher','Lehrender') );
    open_list_head( 'typeofposition',
      html_tag( 'div', '', we('position','Stelle') )
      . html_tag( 'div', '', we('obligation','Lehrverpflichtung') )
    );
    open_list_head( 'teaching_reduction', we('reduction','Reduktion') );
    open_list_head( 'course', we('course','Veranstaltung') );
    open_list_head( 'hours_per_week',
      html_tag( 'div', '', we('hours per week','SWS') )
    );
    open_list_head( 'teaching_factor',
      html_tag( 'div', '', we('teaching factor','Abhaltefaktor') )
      . html_tag( 'div', '', we('credit factor','Anrechnungsfaktor') )
    );
    open_list_head( 'teachers_number', we('teachers','Lehrende') );
    open_list_head( 'participants_number', we('participants','Teilnehmer') );
    open_list_head( 'note', we('note','Anmerkung') );

    open_tr( 'smallskips' );
      open_list_cell(  'teacher' );
        open_div( 'smallskips left', checkbox_element( $edit['extern'] ) );
        if( $edit['extern']['value'] ) {
          open_div( 'smallskips', string_element( $edit['extteacher_cn'] ) );
        } else {
          open_div( 'smallskips' );
            selector_groups( $edit['teacher_groups_id'] );
          close_div();
          $filters = array();;
          if( $edit['teacher_groups_id']['value'] ) {
            $filters['groups_id'] = $edit['teacher_groups_id']['value'];
            open_div( 'smallskips oneline' );
              selector_people( $edit['teacher_people_id'], array( 'filters' => $filters ) );
            close_div();
          }
        }
      open_list_cell( 'typeofposition smallskips' );
        open_div( 'smallskips' );
          selector_typeofposition( $edit['typeofposition'] );
        close_div();
        open_div( 'smallskips' );
          selector_smallint( $edit['teaching_obligation'] );
        close_div();
      open_list_cell( 'teaching_reduction' );
        open_div( 'center smallskips' );
          echo selector_smallint( $edit['teaching_reduction'] );
          if( $edit['teaching_reduction']['value'] > 0 ) {
            open_span( 'quads', we('reason:','Grund:') );
            close_div();
            open_div( 'center smallskips' );
              echo string_element( $edit['teaching_reduction_reason'] );
        }
        close_div();
if( $edit['course_type']['value'] ) {
      open_list_cell( 'course' );
        open_div( 'smallskips', string_element( $edit['course_title'] ) );
        open_div( 'oneline smallskips' );
          selector_course_type( $edit['course_type'] );
          open_span( '', 'Nr: '.string_element( $edit['course_number'] ) );
          open_span( 'quads', 'Modul: '.string_element( $edit['module_number'] ) );
        close_div();

if( ( $edit['course_type']['value'] == 'FP' ) ) {
      open_list_cell( 'hours_per_week' );
        open_div( 'oneline smallskips' );
          selector_SWS( $edit['hours_per_week'], 'course_type=FP' );
        close_div();
      open_list_cell( 'teaching_factor' );
        open_div( 'oneline center smallskips', $edit['teaching_factor']['value'] );
        open_div( 'oneline center smallskips', $edit['credit_factor']['value'] );
      open_list_cell( 'teachers_number', $edit['teachers_number']['value'], 'smallskips center' );

} else {
      open_list_cell( 'hours_per_week' );
        open_div( 'oneline smallskips' );
          $edit['hours_per_week']['min'] = 1; // start selection from 1, not fractional as with FP
          selector_SWS( $edit['hours_per_week'] );
        close_div();
      open_list_cell( 'smallskips teaching_factor' );
        open_div( 'smallskips' );
          selector_smallint( $edit['teaching_factor'] );
        close_div();
        open_div( 'oneline smallskips' );
          selector_credit_factor( $edit['credit_factor'] );
        close_div();
      open_list_cell( 'teachers_number' );
        open_div( 'smallskips' );
          selector_smallint( $edit['teachers_number'] );
          if( $edit['teachers_number']['value'] > 1 ) {
            open_span( 'qquads', we('co-teachers:','weitere:') );
            close_div();
            open_div();
              echo string_element( $edit['co_teacher'] );
          }
        close_div();
}
} else {
      open_list_cell( 'course', false, 'colspan=4' );
          selector_course_type( $edit['course_type'] );
}
      open_list_cell( 'participants_number' );
        open_div( 'smallskips' );
          echo int_element( $edit['participants_number'] );
        close_div();
      open_list_cell( 'note' );
        open_div( 'smallskips' );
          echo textarea_element( $edit['note'] );
        close_div();

    $GLOBALS['current_table']['row_number'] = 2;
    open_tr();
      open_list_cell( 'teacher', false, 'class=oneline right smallskips,colspan=10' );
        open_div( 'smallskips' );
          open_span( 'qquads' );
            open_span( 'quadr', we( 'entry made by: ', 'Eintrag im Namen von: ' ) );
            if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
              selector_groups( $edit['signer_groups_id'] );
            } else if( count( $login_groups_ids ) != 1 ) {
              selector_groups( $edit['signer_groups_id'] , array( 'filters' => array( 'groups_id' => $login_groups_ids ) ) );
            } else {
              // debug( $edit['signer_groups_id']['value'] , 'signer_groups_id' );
              $signer_group = sql_one_group( $edit['signer_groups_id']['value'] );
              open_span( 'kbd quads bold', $signer_group['acronym'] );
            }
          close_span();
          if( ( $sgi = $edit['signer_groups_id']['value'] ) ) {
            open_span( 'qquads' );
              selector_people( $edit['signer_people_id'] , array( 'filters' => "groups_id=$sgi,HEAD" ) );
            close_span();
          }

          open_span( 'qquads', inlink( 'teachinglist', array(
              'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
            , 'options' => $GLOBALS['options'] & ~OPTION_TEACHING_EDIT
          ) ) );

          open_span( 'qquads' );
            submission_button();
          close_span();
        close_div();

  close_table();
}

?>
