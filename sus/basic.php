<?php


function kontenkreis_name( $kontenkreis) {
  switch( $kontenkreis ) {
    case 'E':
      return 'Erfolgskonto';
    case 'B':
      return 'Bestandskonto';
    case '':
    case '0':
      return '(kein Kontenkreis gew'.H_AMP.'uml;hlt)';
    default:
      error( 'undefinierter Kontenkreis', LOG_FLAG_DATA, 'kontenkreis' );
  }
}

function seite_name( $seite, $plural = false ) {
  $plural = ( $plural ? 'a' : '' );
  switch( $seite ) {
    case 'A':
      return 'Aktiv' . $plural;
    case 'P':
      return 'Passiv' . $plural;
    case '':
    case '0':
      return '(keine Seite gew'.H_AMP.'uml;hlt)';
    default:
      error( 'undefinierte Seite', LOG_FLAG_DATA, 'seite' );
  }
}

function r2( $x ) {
  return (double) sprintf( '%.2lf', $x );
}

// have_priv(), need_priv():
// subproject sus is single-user, for the time being:
//
function have_priv( $section, $action, $item = 0 ) {
  return true;
}
function need_priv( $section, $action, $item = 0 ) {
  // nop
}


?>
