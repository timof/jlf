<?php

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'pruefungen_id', 'global,type=u,sources=self http,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {

  switch( $reinit ) {
    case 'init':
      $sources = 'http self keep default';
      break;
    case 'self':
      $sources = 'self keep default';
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
  , 'tables' => 'pruefungen'
  , 'failsafe' => false   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $pruefungen_id ) {
    $pruefung = sql_one_pruefung( $pruefungen_id );
    $opts['rows'] = array( 'pruefungen' => $pruefung );
  }

  $f = init_fields( array(
      'cn' => 'size=40'
    , 'semester' => 'min=1,max=12'
    , 'studiengang' => 'auto=1'
    , 'note' => 'lines=2,cols=80'
    , 'dozent_groups_id'
    , 'dozent_people_id'
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

  handle_action( array( 'reset', 'save', 'update', 'init', 'template' ) ); 
  switch( $action ) {
    case 'template':
      $pruefungen_id = 0;
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
        if( $pruefungen_id ) {
          sql_update( 'pruefungen', $pruefungen_id, $values );
        } else {
          $pruefungen_id = sql_insert( 'pruefungen', $values );
        }
        reinit('reset');
      }
      break;
  }

} // while $reinit


if( $pruefungen_id ) {
  open_fieldset( 'small_form old', we('Exam data','Stammdaten Pruefungstermin') );
} else {
  open_fieldset( 'small_form new', we('New exam','neuer Pruefungstermin' ) );
}
  open_table('small_form hfill');
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['cn'] ), we('Lecture:','Veranstaltung:') );
      open_td( '', string_element( $f['cn'] ) );

    open_tr( 'medskip' );
      open_td( array( 'label' => $f['studiengang'] ), we('Degree programme:','Studiengang:') );
      open_td('oneline');
        $s = $f['studiengang'];
        foreach( $studiengang_text as $studiengang_id => $studiengang_cn ) {
          $s['mask'] = $studiengang_id;
          open_span( 'quadr', checkbox_element( $s ). "$studiengang_cn " );
        }
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['semester'] ), '(Regel-)Fachsemester:' );
      open_td();
        echo selector_int( $f['semester'] );

    open_tr( 'bigskip' );
      open_td( '', we('Date:','Datum:' ) );
      open_td('oneline');
        selector_int( $f['year'] );
        selector_int( $f['month'] );
        selector_int( $f['day'] );

    open_tr( 'bigskip' );
      open_td( '', we('Time:','Zeit:' ) );
      open_td('oneline');
        selector_int( $f['hour'] );
        selector_int( $f['minute'] );

    open_tr( 'bigskip' );
      open_td( array( 'label' => $f['note'] ), we('Description','Beschreibung:') );
      open_td( '', textarea_element( $f['note'] ) );

    open_tr( 'bigskip' );
      open_td( array( 'label' => $f['url'] ), 'Internetseite:' );
      open_td( '', string_element( $f['url'] ) );

    open_tr( 'bigskip' );
      open_td( array( 'label' => $f['dozent_groups_id'] ), $f['dozent_groups_id']['value'] ? ' ' : we('Teacher:','Dozent:') );
      open_td();
        selector_groups( $f['dozent_groups_id'] );

if( $f['dozent_groups_id']['value'] ) {
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['dozent_people_id'] ), we('Teacher:','Dozent:') );
      open_td();
        selector_people( $f['dozent_people_id'], array( 'filters' => array( 'groups_id' => $f['dozent_groups_id']['value'] ) ) );
}


    open_tr( 'bigskip' );
      open_td( 'right,colspan=2' );
        if( $pruefungen_id && ! $f['_changes'] )
          template_button();
        reset_button( $f['_changes'] ? '' : 'display=none' );
        // submission_button( $f['_changes'] ? '' : 'display=none' );
        submission_button();
  close_table();

close_fieldset();

?>
