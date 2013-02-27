<?php

echo html_tag( 'h1', '', 'Konfiguration' );

need_priv( 'config', 'read' );

$fields = array(
//   'current_year' => array(
//     'type' => 'U4'
//   , 'sources' => 'http initval'
//   , 'initval' => $current_year
//   , 'default' => $current_year
//   , 'min' => 2012, 'max' => 2100
//   , 'global' => 1
//   )
// , 'current_term' => array(
//     'type' => 'W1'
//   , 'sources' => 'http initval'
//   , 'initval' => $current_term
//   , 'pattern' => '/^[WS]$/'
//   , 'global' => 1
//   )
  'teaching_survey_open' => array(
    'type' => 'u1'
  , 'sources' => 'http initval'
  , 'initval' => $teaching_survey_open
  , 'pattern' => '/^[01]$/'
  , 'global' => 1
  , 'auto' => 1
  )
, 'teaching_survey_year' => array(
    'type' => 'U4'
  , 'sources' => 'http initval'
  , 'initval' => $teaching_survey_year
  , 'default' => $current_year
  , 'min' => 2012, 'max' => 2100
  , 'global' => 1
  )
, 'teaching_survey_term' => array(
    'type' => 'W1'
  , 'sources' => 'http initval'
  , 'initval' => $teaching_survey_term
  , 'pattern' => '/^[WS]$/'
  , 'global' => 1
  )
);

$f = init_fields( $fields, 'failsafe=0' );
// debug( $f, 'f' );

if( isset( $f['_changes'] ) ) {
  foreach( $f['_changes'] as $fieldname => $raw ) {
    sql_update( 'leitvariable', "name=$fieldname", array( 'value' => $f[ $fieldname ]['value'] ) );
  }
}

open_table( 'menu' );

  open_tr( 'medskip left' );
    open_th( 'left,colspan=4', we( 'teaching survey', 'Lehrerfassung' ) );
  open_tr();
    open_th( 'right', we( 'for term:', "f{$uUML}r Semester:" ) );
    open_td( '', selector_term( $f['teaching_survey_term'] ) );
    open_td( '', selector_int( $f['teaching_survey_year'] ) );

    open_td( 'qquad right' );
      echo we( 'activate:', 'freischalten:' );
      open_span( 'qquadr', radiobutton_element(
        $f['teaching_survey_open']
      , array( 'value' => '0', 'text' => we( 'closed', 'geschlossen' ) )
      ) );
      open_span( 'qquadr', radiobutton_element(
        $f['teaching_survey_open']
      , array( 'value' => '1', 'text' => we( 'open', 'offen' ) )
      ) );

close_table();


$filters = array();


if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
  medskip();
// $sessions = sql_sessions( "atime<$then" );
//  open_fieldset( 'small_form', 'maintenance', 'on' );
//    persistent_vars_view( "name=thread_atime,value<$then" );
//  close_fieldset();
 
  open_fieldset( 'small_form medskip', 'persistent variables', 'off' );
    persistent_vars_view( $filters );
  close_fieldset();
}

?>
