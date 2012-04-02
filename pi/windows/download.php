<?php

// need( $global_context == CONTEXT_DOWNLOAD );

init_var( 'item', 'type=w,sources=http,global' );
init_var( 'id', 'type=u,sources=http,global' );

switch( $item ) {

  case 'people_jpegphoto':
    $person = sql_person( $id );
    if( $person['jpegphoto'] ) {
      header( 'Content-Type: image/jpeg' );
      echo base64_decode( $person['jpegphoto'] );
    }  
    break;

  case 'bamathemen_pdf':
    $thema = sql_one_bamathema( $id );
    if( $thema['pdf'] ) {
      header( 'Content-Type: application/pdf' );
      echo base64_decode( $thema['pdf'] );
    }  
    break;

  default:
    header( 'Content-Type: text/plain' );
    echo 'Hello, World!';
    break;
    
}

?>
