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


define( 'PERSON_PRIV_USER', 0x01 );
define( 'PERSON_PRIV_COORDINATOR', 0x02 );
define( 'PERSON_PRIV_ADMIN', 0x04 );

define( 'OPTION_TEACHING_EDIT', 1 );

define( 'GROUPS_FLAG_INSTITUTE', 0x001 ); // to be considered member of institute
define( 'GROUPS_FLAG_ACTIVE', 0x002 );    // whether it still exists
define( 'GROUPS_FLAG_LIST', 0x004 );      // to be listed on official institute list

define( 'GROUPS_STATUS_PROFESSOR', 1 );
define( 'GROUPS_STATUS_SPECIAL', 2 );
define( 'GROUPS_STATUS_JOINT', 3 );
define( 'GROUPS_STATUS_EXTERNAL', 4 );
define( 'GROUPS_STATUS_OTHER', 5 );



?>
