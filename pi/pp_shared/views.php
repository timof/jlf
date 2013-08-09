<?php

function publication_highlight_view( $pub, $opts = array() ) {
  $opts = parameters_explode( $opts );
  if( isnumber( $pub ) ) {
    $pub = sql_one_publication( $pub );
  }
  if( $pub['jpegphoto'] ) {
    $s = html_span( 'floatright', photo_view( $pub['jpegphoto'], $pub['jpegphotorights_people_id'] ) );
  }
  $s .= html_div( 'bold center smallskips', $pub['title'] );
  $s .= html_div( 'center smallskips', $pub['authors'] );
  $s .= html_div( 'left smallskips', $pub['abstract'] );

  $s .= html_div( 'left smallskips' );

  return html_div( 'highlight', $s );
  break;
}

function publication_reference_view( $pub, $opts = array() ) {
  $opts = parameters_explode( $opts );
  if( isnumber( $pub ) ) {
    $pub = sql_one_publication( $pub );
  }

  switch( $format ) {
    case 'highlight':
    case 'reference':

      break;
    default:
      error( 'undefined format: ' . $format, LOG_FLAG_CODE,  'publications' );
  }
}

?>
