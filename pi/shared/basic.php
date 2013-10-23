<?php // pp/shared/basic.php

// we need to define constants early (so they are available when functions are parsed);
// the textual representation goes to common.php (as we() may need to be called)

define( 'PROGRAMME_BSC', 0x1 );
define( 'PROGRAMME_BED', 0x2 );
define( 'PROGRAMME_MSC', 0x4 );
define( 'PROGRAMME_MED', 0x8 );
define( 'PROGRAMME_PHD', 0x10 );
define( 'PROGRAMME_SECOND',  0x20 );
define( 'PROGRAMME_INTERNSHIP', 0x40 );
define( 'PROGRAMME_ASSISTANT', 0x80 );
define( 'PROGRAMME_DIPLOM', 0x100 );


define( 'PERSON_PRIV_USER', 0x01 );
define( 'PERSON_PRIV_COORDINATOR', 0x02 );
define( 'PERSON_PRIV_ADMIN', 0x04 );

define( 'OPTION_TEACHING_EDIT', 1 );

define( 'GROUPS_STATUS_PROFESSOR', 1 );
define( 'GROUPS_STATUS_SPECIAL', 2 );
define( 'GROUPS_STATUS_JOINT', 3 );
define( 'GROUPS_STATUS_EXTERNAL', 4 );
define( 'GROUPS_STATUS_LABCOURSE', 5 );
define( 'GROUPS_STATUS_OTHER', 9 );

define( 'PEOPLE_STATUS_OTHER', 1 );
define( 'PEOPLE_STATUS_PROFESSOR', 2 );
define( 'PEOPLE_STATUS_JOINT', 3 );
define( 'PEOPLE_STATUS_SPECIAL', 4 );
define( 'PEOPLE_STATUS_EXTERNAL', 5 );
define( 'PEOPLE_STATUS_STUDENT', 6 );



?>
