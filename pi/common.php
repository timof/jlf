<?php

//
// the following call function we() and thus must be in common.php, not basic.pip:
//

define( 'DEGREE_BACHELOR', 0x1 );
define( 'DEGREE_MASTER', 0x2 );
define( 'DEGREE_PHD', 0x4 );
define( 'DEGREE_INTERNSHIP', 0x8 );
define( 'DEGREE_ASSISTANT', 0x10 );
$degree_text = array(
  DEGREE_BACHELOR => 'Bachelor'
, DEGREE_MASTER => 'Master'
, DEGREE_PHD => 'PhD'
, DEGREE_INTERNSHIP => we('research internship','Forschungspraktikum')
, DEGREE_ASSISTANT => we('student assistant','HiWi')
);

define( 'PROGRAMME_BSC',  0x100 );
define( 'PROGRAMME_BED',  0x200 );
define( 'PROGRAMME_MSC',  0x400 );
define( 'PROGRAMME_MED' , 0x800 );
define( 'PROGRAMME_SECOND',  0x1000 );
define( 'PROGRAMME_OTHER',  0x2000 );
$programme_text = array(
  PROGRAMME_BSC => 'BSc'
, PROGRAMME_BED => 'BEd'
, PROGRAMME_MSC => 'MSc'
, PROGRAMME_MED => 'MEd'
, PROGRAMME_SECOND => we('second subject', 'Nebenfach')
, PROGRAMME_OTHER => we('other','sonstige')
);

define( 'OPTION_TEACHING_EDIT', 1 );

?>
