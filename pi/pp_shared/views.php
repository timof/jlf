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

function publicationsreferences_view( $filters = array(), $opts = array() ) {
  $filters = restrict_view_filters( $filters, 'publications' );
  if( ! ( $publications = sql_publications( $filters ) ) ) {
    open_div( '', we('no publications found', 'Keine Veröffentlichungen gefunden' ) );
    return;
  }
  open_ul('references');
    foreach( $publications as $p ) {
      open_li();
        echo $p['authors']. ', ';
        echo inlink( 'publikation', array(
          'class' => 'href italic'
        , 'text' => $p['title']
        , 'publications_id' => $p['publications_id']
        ) ) .', ';
        echo $p['journal']. ', ';
        if( $p['url'] ) {
          echo html_alink( $p['url'], array(
            'class' => 'href outlink'
          , 'text' => $p['url']
          ) ) .', ';
        }
        echo $p['year'];
      close_li();
    }
  close_ul();
}

?>
