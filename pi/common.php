<?php

define( 'BAMATHEMA_ABSCHLUSS_BACHELOR', 0x1 );
define( 'BAMATHEMA_ABSCHLUSS_MASTER', 0x2 );
define( 'BAMATHEMA_ABSCHLUSS_PHD', 0x4 );
define( 'BAMATHEMA_ABSCHLUSS_PRAKTIKUM', 0x8 );
$abschluss_text = array(
  BAMATHEMA_ABSCHLUSS_BACHELOR => 'Bachelor'
, BAMATHEMA_ABSCHLUSS_MASTER => 'Master'
, BAMATHEMA_ABSCHLUSS_PHD => 'PhD'
, BAMATHEMA_ABSCHLUSS_PRAKTIKUM => 'Forschungspraktikum'
);

define( 'STUDIENGANG_BSC',  0x100 );
define( 'STUDIENGANG_BED',  0x200 );
define( 'STUDIENGANG_MSC',  0x400 );
define( 'STUDIENGANG_MED' , 0x800 );
define( 'STUDIENGANG_NF',  0x1000 );
define( 'STUDIENGANG_SONSTIGE',  0x2000 );
$studiengang_text = array(
  STUDIENGANG_BSC => 'BSc'
, STUDIENGANG_BED => 'BEd'
, STUDIENGANG_MSC => 'MSc'
, STUDIENGANG_MED => 'MEd'
, STUDIENGANG_NF => we('second subject', 'Nebenfach')
, STUDIENGANG_SONSTIGE => we('other','sonstige')
);


define( 'PERSON_ROLE_ACCOUNT', 0x01 );
define( 'PERSON_ROLE_COORDINATOR', 0x02 );
define( 'PERSON_ROLE_ADMIN', 0x04 );

function have_priv( $section, $action, $item = NULL ) {
  global $login_role;

  if( $login_role == PERSON_ROLE_ADMIN )
    return true;

  return true;
  menatwork();
  switch( $section ) {
    case 'person':
      switch( $action ) {
        case 'create':
          return $login_role >= PERSON_ROLE_ACCOUNT;
        case 'delete':

    }
          
      
  }
  
  return false;
}

function need_priv( $section, $action, $item = NULL ) {
  if( ! have_priv( $section, $action, $item ) ) {
    error( we('insufficient privileges','keine Berechtigung') );
  }
}

?>
