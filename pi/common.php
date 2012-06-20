<?php

//
// the following call function we() and thus must be in common.php, not basic.pip:
//

$degree_text = array(
  DEGREE_BACHELOR => 'Bachelor'
, DEGREE_MASTER => 'Master'
, DEGREE_PHD => 'PhD'
, DEGREE_INTERNSHIP => we('research internship','Forschungspraktikum')
, DEGREE_ASSISTANT => we('student assistant','HiWi')
);

$programme_text = array(
  PROGRAMME_BSC => 'BSc'
, PROGRAMME_BED => 'BEd'
, PROGRAMME_MSC => 'MSc'
, PROGRAMME_MED => 'MEd'
, PROGRAMME_SECOND => we('second subject', 'Nebenfach')
, PROGRAMME_OTHER => we('other','sonstige')
);

$people_flag_text = array(
  PEOPLE_FLAG_INSTITUTE => 'institute'
, PEOPLE_FLAG_NOPERSON => 'account'
);

$choices_typeofposition = array(
  'H' => we('budget','Haushalt')
, 'D' => we('third-party','Drittmittel')
, 'W' => 'Werkvertrag'
, 'E' => 'externe Finanzierung'
, 'P' => 'pensioniert'
, 'M' => 'emeritiert'
, 'O' => 'ohne Vertrag/Vergütung'
, 'A' => 'Lehrauftrag unvergütet'
, 'G' => 'Lehrauftrag vergütet'
, 'o' => we('other','sonstige')
);

?>
