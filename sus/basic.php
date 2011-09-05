<?php


function kontenkreis_name( $kontenkreis) {
  switch( $kontenkreis ) {
    case 'E':
      return 'Erfolgskonto';
    case 'B':
      return 'Bestandskonto';
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
    default:
      error( 'undefinierte Seite' );
  }
}


?>
