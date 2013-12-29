<?php // pi/windows/exam_edit.php

need( false );

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'exams_id', 'global,type=u,sources=self http,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {

  switch( $reinit ) {
    case 'init':
      $sources = 'http self initval default';
      break;
    case 'self':
      $sources = 'self initval default';
      break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'initval default';
      break;
    default:
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'exams,init' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'exams'
  , 'failsafe' => false   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $exams_id ) {
    $exam = sql_one_exam( $exams_id );
    $opts['rows'] = array( 'exams' => $exam );
  }

  $f = init_fields( array(
      'cn' => 'size=40'
    , 'course' => 'size=40'
    , 'module' => 'size=40'
    , 'semester' => 'min=1,max=12'
    , 'programme' => 'auto=1'
    , 'note' => 'lines=2,cols=80'
    , 'teacher_groups_id'
    , 'teacher_people_id'
    , 'url' => 'size=60'
    , 'year' => 'min=2012,max=2020,default='.substr( $utc, 0, 4 )
    , 'month' => 'size=2,min=1,max=12,default='.substr( $utc, 4, 2 )
    , 'day' => 'size=2,min=1,max=31,default='.substr( $utc, 6, 2 )
    , 'hour' => 'size=2,min=0,max=23,default=10'
    , 'minute' => 'size=2,min=0,max=59,default=15'
    )
  , $opts
  );
  $reinit = false;

  handle_actions( array( 'reset', 'save', 'init', 'template' ) ); 
  if( $action ) switch( $action ) {
    case 'template':
      $exams_id = 0;
      break;

    case 'save':
      if( ! $f['_problems'] ) {

        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            $values[ $fieldname ] = $r['value'];
        }
        $values['utc'] = sprintf( '%04u%02u%02u.%02u%02u00', $values['year'], $values['month'], $values['day'], $values['hour'], $values['minute'] );
        unset( $values['year'] );
        unset( $values['month'] );
        unset( $values['day'] );
        unset( $values['hour'] );
        unset( $values['minute'] );
        if( $exams_id ) {
          sql_update( 'exams', $exams_id, $values );
        } else {
          $exams_id = sql_insert( 'exams', $values );
        }
        reinit('reset');
      }
      break;
  }

} // while $reinit


if( $exams_id ) {
  open_fieldset( 'small_form old', we('Exam data','Stammdaten Prüfungstermin') );
} else {
  open_fieldset( 'small_form new', we('New exam','neuer Prüfungstermin' ) );
}
  open_table('small_form hfill');

    open_tr( 'medskip' );
      open_td( array( 'label' => $f['cn'] ), we('Exam:','Prüfung:') );
      open_td( '', string_element( $f['cn'] ) );

    open_tr( 'medskip' );
      open_td( array( 'label' => $f['course'] ), we('Course:','Lehrveranstaltung:') );
      open_td( '', string_element( $f['course'] ) );

    open_tr( 'medskip' );
      open_td( array( 'label' => $f['module'] ), we('Module:','Modul:') );
      open_td( '', string_element( $f['module'] ) );

    open_tr( 'medskip' );
      open_td( array( 'label' => $f['programme'] ), we('Degree programme:','Studiengang:') );
      open_td('oneline');
        $s = $f['programme'];
        foreach( $programme_text as $programme_id => $programme_cn ) {
          $s['mask'] = $programme_id;
          open_span( 'quadr', checkbox_element( $s ). "$programme_cn " );
        }
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['semester'] ), we('Semester:','(Regel-)Fachsemester:') );
      open_td( '', selector_int( $f['semester'] ) );

    open_tr( 'bigskip' );
      open_td( '', we('Date:','Datum:' ) );
      open_td('oneline');
        echo selector_int( $f['year'] );
        echo selector_int( $f['month'] );
        echo selector_int( $f['day'] );

    open_tr( 'bigskip' );
      open_td( '', we('Time:','Zeit:' ) );
      open_td('oneline');
        echo selector_int( $f['hour'] );
        echo selector_int( $f['minute'] );

    open_tr( 'bigskip' );
      open_td( array( 'label' => $f['note'] ), we('Notes:','Hinweise:') );
      open_td( '', textarea_element( $f['note'] ) );

    open_tr( 'bigskip' );
      open_td( array( 'label' => $f['url'] ), we('web page:','Internetseite:') );
      open_td( '', string_element( $f['url'] ) );

    open_tr( 'bigskip' );
      open_td( array( 'label' => $f['teacher_groups_id'] ), $f['teacher_groups_id']['value'] ? ' ' : we('Teacher:','Dozent:') );
      open_td( '', selector_groups( $f['teacher_groups_id'] ) );

if( $f['teacher_groups_id']['value'] ) {
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['teacher_people_id'] ), we('Teacher:','Dozent:') );
      open_td( '', selector_people( $f['teacher_people_id'], array( 'filters' => array( 'groups_id' => $f['teacher_groups_id']['value'] ) ) ) );
}

    open_tr( 'bigskip' );
      open_td( 'right,colspan=2' );
        if( $exams_id ) {
          echo template_button_view();
        }
        echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
        echo save_button_view();
  close_table();

close_fieldset();

?>
