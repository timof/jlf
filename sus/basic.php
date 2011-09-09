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
      error( 'undefinierter Kontenkreis' );
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
      error( 'undefinierte Seite' );
  }
}


?>
