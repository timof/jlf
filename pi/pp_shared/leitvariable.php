<?php // pi/leitvariable.php

$leitvariable = array(
//   'current_year' => array(
//     'meaning' => 'current year'
//   , 'default' => '2012'
//   , 'comment' => 'default for year in many places'
//   , 'local' => false
//   , 'runtime_editable' => 1
//   , 'readonly' => 0
//   , 'cols' => '4'
//   )
// , 'current_term' => array(
//     'meaning' => 'current term (S or W)'
//   , 'default' => ''
//   , 'comment' => 'default term in many places'
//   , 'local' => false
//   , 'runtime_editable' => 1
//   , 'readonly' => 0
//   , 'cols' => '1'
//   )
  'teaching_survey_year' => array(
    'meaning' => 'teaching survey input is for this year'
  , 'default' => '2013'
  , 'comment' => ''
  , 'runtime_editable' => 1
  , 'cols' => '4'
  )
, 'teaching_survey_term' => array(
    'meaning' => 'teaching survey input is for this term (S or W)'
  , 'default' => ''
  , 'comment' => ''
  , 'runtime_editable' => 1
  , 'cols' => '1'
  )
, 'teaching_survey_open' => array(
    'meaning' => 'flag: whether teaching survey is open for input'
  , 'default' => ''
  , 'comment' => ''
  , 'runtime_editable' => 1
  , 'cols' => '1'
  )
);

?>
