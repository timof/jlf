<?php // /pi/windows/exam.php

need( false );

init_var( 'exams_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $exams_id ) {
  open_div( 'warn', we('no exam selected','keine Prüfung gewaehlt') );
  return;
}

$exam = sql_one_exam( $exams_id );
open_fieldset( 'small_form old', we('Exam data','Stammdaten Prüfungstermin') );

  open_table('small_form hfill');

    open_tr( 'medskip' );
      open_td( array( 'label' => $f['cn'] ), we('Exam:','Prüfung:') );
      open_td( '', string_element( $f['cn'] ) );

    if( $exam['course'] ) {
      open_tr( 'medskip' );
        open_td( 'label', we('Course:','Lehrveranstaltung:') );
        open_td( 'kbd', $exam['course'] );
    }

    if( $exam['module'] ) {
      open_tr( 'medskip' );
        open_td( 'label', we('Module:','Modul:') );
        open_td( 'kbd', $exam['module'] );
    }

    open_tr( 'medskip' );
      open_td( 'label', we('Degree programme:','Studiengang:') );
      open_td('oneline kbd');
        $s = $exam['programme_flags'];
        foreach( $programme_text as $programme_flags => $programme_cn ) {
          if( $s & $programme_flags ) {
            open_span( 'quadr', $programme_cn );
          }
        }

    open_tr( 'medskip' );
      open_td( 'label', we('Semester:','(Regel-)Fachsemester:') );
      open_td( 'kbd', $exam['semester'] );

    open_tr( 'bigskip' );
      open_td( 'label', we('Date:','Datum:' ) );
      open_td('oneline kbd', $exam['year'] .'-'. $exam['month'] .'-'. $exam['day'] );

    open_tr( 'bigskip' );
      open_td( 'label', we('Time:','Zeit:' ) );
      open_td('oneline kbd', $exam['hour'] .'.'. $exam['minute'] );

    if( $exam['note'] ) {
      open_tr( 'bigskip' );
        open_td( 'label top', we('Notes:','Hinweise:') );
        open_td( 'kbd top', $exam['note'] );
    }

    if( $exam['url'] ) {
      open_tr( 'bigskip' );
        open_td( 'label', we('web page:','Internetseite:') );
        open_td( 'kbd', url_view( $exam['url'], array( 'text' => $exam['url'] ) ) );
    }

    if( $exam['teacher_people_id']['value'] ) {
      open_tr( 'medskip' );
        open_td( 'label', we('Teacher:','Dozent:') );
        open_td( 'kbd', alink_person_view( $exam['teacher_people_id'] ) );
    }

    if( have_priv( 'exam', 'edit', $exams_id ) ) {
      open_tr();
        open_td( 'colspan=2', inlink( 'exam_edit', array(
            'class' => 'edit', 'text' => we('edit...','bearbeiten...' )
          , 'exams_id' => $exams_id
          ) ) );
    }
  close_table();

close_fieldset();

?>
