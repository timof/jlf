<?php

function publication_highlight_view( $pub, $opts = array() ) {
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
}

function publication_columns_view( $pub, $opts = array() ) {
  if( isnumber( $pub ) ) {
    $pub = sql_one_publication( $pub );
  }
  $col1 = '';
  if( $pub['jpegphoto'] ) {
    $ccol1 .= html_span( 'floatright', photo_view( $pub['jpegphoto'], $pub['jpegphotorights_people_id'] ) );
  }
  $col1 .= html_div( 'cn', $pub['cn'] );
  $col1 .= html_div( 'summary', $pub['summary'] );

  $col2 = html_tag('ul');

  $col2 .= html_li( ''
  , html_div( 'bold', we('Publication:','Veröffentlichung:') )
  , html_div( '', publicaton_reference_view( $pub ) )
  );
  $col2 .= html_li( ''
  , html_div( 'bold', we('Research group:','Arbeitsgruppe:') )
  , html_alink_group( $pub['groups_id'], 'fullname=1' )
  );
  if( $pub['info_url'] ) {
    $col2 .= html_li( ''
    , html_div( 'bold', we('More information:','Weitere Informationen:') )
    , html_alink( $pub['info_url'], array( 'class' => 'href outlink', 'text' => $pub['info_url'] ) )
    );
  }

  return html_div( 'highlight tr'
  , html_div( 'highlight td', $col1 )
  , html_div( 'highlight td', $col2 )
  );
}

function publication_reference_view( $pub, $opts = array() ) {
  if( isnumber( $pub ) ) {
    $pub = sql_one_publication( $pub );
  }
  $s = $pub['authors']. ', ';
  $s .= inlink( 'publikation', array(
    'class' => 'href italic'
  , 'text' => $pub['title']
  , 'publications_id' => $pub['publications_id']
  ) );
  $s .= ', ';
  $ref = $pub['journal']. ', ' . span_view( 'bold', $pub['volume'] ) . ' ' .$pub['page'];
  if( $pub['journal_url'] ) {
    $ref = html_alink( $pub['journal_url'], array(
      'class' => 'href outlink'
    , 'text' => $ref
    ) );
  }
  $s .= $ref . ', ';
  $s .= '('.$pub['year'].')';
  return $s;
}

function publicationsreferenceslist_view( $filters = array(), $opts = array() ) {
  $filters = restrict_view_filters( $filters, 'publications' );
  if( ! ( $publications = sql_publications( $filters ) ) ) {
    return html_div( '', we('no publications found', 'Keine Veröffentlichungen gefunden' ) );
  }
  $s = '';
  foreach( $publications as $p ) {
    $s .= publication_reference_view( $p );
  }
  return html_tag( 'ul', 'references', $s );
}

?>
