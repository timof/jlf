<?php

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


define( 'PERSON_PRIV_ACCOUNT', 0x01 );
define( 'PERSON_PRIV_COORDINATOR', 0x02 );
define( 'PERSON_PRIV_ADMIN', 0x04 );

function have_priv( $section, $action, $item = 0 ) {
  global $login_privs, $logged_in;

  if( $login_privs & PERSON_PRIV_ADMIN )
    return true;

  if( ! $logged_in )
    return false;

  return true;
  switch( "$section,$action" ) {
    case 'person,create':
      return true;
    case 'person,edit':
    case 'person,delete':
      if( $login_privs & PERSON_PRIV_COORDINATOR )
        return true;
      if( $item ) {
      }
      return false;
    case 'person,auth_methods':
      return false;
    case 'person,password':
      if( $item && ( $item === $GLOBALS['login_people_id'] ) )
        return true;
      return false;
  }
  
  return false;
}

function need_priv( $section, $action, $item = 0 ) {
  if( ! have_priv( $section, $action, $item ) ) {
    error( we('insufficient privileges','keine Berechtigung') );
  }
}

?>
