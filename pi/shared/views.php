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

  return html_div( 'highlight', $s );
}

function publication_block_view( $pub, $opts = array() ) {
  global $oUML;

  if( isarray( $pub ) && ! isset( $pub['publications_id'] ) ) {
    $s = html_tag( 'div', 'highlight inline_block' );
    foreach( $pub as $p ) {
      $s .= publication_block_view( $p, $opts );
    }
    $s .= html_tag('div', false );
   return $s;
 }

  $s = '';
  if( $pub['jpegphoto'] ) {
    $s .= html_span( 'floatright', photo_view( $pub['jpegphoto'], $pub['jpegphotorights_people_id'] ) );
  }

  $s .= html_div( 'cn', $pub['cn'] );
  $s .= html_div( 'summary', $pub['summary'] );
  $s .= html_div( 'smallskips', we('Publication: ',"Ver{$oUML}ffentlichung: ") . publication_reference_view( $pub ) );
  $s .= html_div( 'smallskips', we('Research group: ','Arbeitsgruppe: ') . alink_group_view( $pub['groups_id'], 'fullname=1' ) );
  if( $pub['info_url'] ) {
    $s .= html_div( 'smallskips', we('More information:','Weitere Informationen:')
                      . html_alink( $pub['info_url'], array( 'class' => 'href outlink', 'target' => '_new', 'text' => $pub['info_url'] ) )
    );
  }

  return html_div( 'highlight clear', $s );
}


function publication_columns_view( $pub, $opts = array() ) {

  if( isarray( $pub ) && ! isset( $pub['publications_id'] ) ) {
    $s = html_tag( 'div', 'table highlight qquadr' );
    $s .= html_div('style=display:table-column-group;' , html_div('style=display:table-column;width:38%;','') . html_div('style=display:table-column;width:62%;','') );
    foreach( $pub as $p ) {
      $s .= publication_columns_view( $p, $opts );
    }
    $s .= html_tag( 'div', false );
    return $s;
  }
    
  if( isnumber( $pub ) ) {
    $pub = sql_one_publication( $pub );
  }
  $col1 = '';
  if( $pub['jpegphoto'] ) {
    $col1 .= html_span( 'floatright', photo_view( $pub['jpegphoto'], $pub['jpegphotorights_people_id'] ) );
  }
  $col1 .= html_div( 'cn', $pub['cn'] );
  $col1 .= html_div( 'summary', $pub['summary'] );

  $col2 = html_tag('ul');

  $col2 .= html_li( ''
  , html_span( '', we('Research group:','Arbeitsgruppe:') )
    . alink_group_view( $pub['groups_id'], 'fullname=1' )
  );
  $col2 .= html_li( ''
  , html_span( '', we('Publication:','Veröffentlichung:') )
    . html_span( '', publication_reference_view( $pub ) )
  );
  if( $pub['info_url'] ) {
    $col2 .= html_li( ''
    , html_span( 'bold', we('More information:','Weitere Informationen:') )
      . html_alink( $pub['info_url'], array( 'class' => 'href outlink', 'target' => '_new', 'text' => $pub['info_url'] ) )
    );
  }
  $col2 .= html_tag('ul', false );

  return html_div( 'highlight tr'
  , html_div( 'highlight td', $col1 )
    . html_div( 'highlight td', $col2 )
  );
}

function publication_reference_view( $pub, $opts = array() ) {
  if( isnumber( $pub ) ) {
    $pub = sql_one_publication( $pub );
  }
  $s = $pub['authors']. ', ';
  $s .= inlink( 'publication_view', array(
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
  return span_view( 'reference', $s );
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


function group_view( $group, $opts = array() ) {
  if( isnumber( $group ) ) {
    $group = sql_one_group( $group );
  }
  
  $s = '';
  if( $group['jpegphoto'] ) {
    $s .= html_span( 'floatright', photo_view( $group['jpegphoto'], $group['jpegphotorights_people_id'], 'style=max-width:320px;max-height:240px;' ) );
  }

  $s .= html_tag( 'h1', '', we('Group: ','Gruppe/Bereich: ') . html_span( 'oneline', $group['cn'] ) );

  $s .= html_div('table');

  $s .= html_div( 'tr'
  , html_div( 'td', we('Head of group:','Leiter der Gruppe:' ) )
    . html_div( 'td', alink_person_view( $group['head_people_id'], 'office' ) )
  );

  $s .= html_div( 'tr'
  , html_div( 'td', we('Secretary:','Sekretariat:' ) )
    . html_div( 'td', alink_person_view( $group['secretary_people_id'], 'office' ) )
  );

  if( $group['url'] ) {
    $s .= html_div( 'tr'
    , html_div( 'td', we('Web page:','Internetseite:') )
      . html_div( 'td', html_alink( $group['url'], array( 'text' => $group['url'] ) ) )
    );
  }
  $s .= html_div( false );

  if( $group['note'] ) {
    $s .= html_span( 'description', $group['note'] );
  }

  return html_div( 'group textaroundphoto', $s );
}





function alink_person_view( $filters, $opts = array() ) {
  global $global_format;
  static $cache = array();
  $opts = parameters_explode( $opts );
  if( isnumber( $filters ) && isset( $cache[ $filters ] ) ) {
    $person = $cache[ $filters ];
  } else {
    $person = sql_person( $filters, NULL );
  }
  if( $person ) {
    $cache[ $person['people_id'] ] = $person;
    $text = adefault( $opts, 'text', $person['cn'] );
    switch( $global_format ) {
      case 'html':
        return inlink( 'person_view', array(
          'people_id' => $person['people_id']
        , 'class' => adefault( $opts, 'class', 'href inlink' )
        , 'text' => $text
        , 'title' => $text
        ) );
      case 'pdf':
        // return span_view( 'href', $text ); // url_view() makes no sense for deep links (in general)
      default:
        return $text;
    }
  } else {
    $default = ( adefault( $opts, 'office' ) ? we(' - vacant - ',' - vakant - ') : we('(no person)','(keine Person)') );
    return adefault( $opts, 'default', $default );
  }
}

function alink_group_view( $filters, $opts = array() ) {
  global $global_format;
  static $cache = array();
  $opts = parameters_explode( $opts, 'default_key=class' );
  $class = adefault( $opts, 'class', 'href inlink' );
  if( isnumber( $filters ) && isset( $cache[ $filters ] ) ) {
    $group = $cache[ $filters ];
  } else {
    $group = sql_one_group( $filters, NULL );
  }
  if( $group ) {
    $cache[ $group['groups_id'] ] = $group;
    $text = adefault( $opts, 'text', $group[ adefault( $opts, 'fullname' ) ? 'cn' : 'acronym' ] );
    switch( $global_format ) {
      case 'html':
        $t = inlink( 'group_view', array(
          'groups_id' => $group['groups_id']
        , 'class' => $class
        , 'text' => $text
        , 'title' => $group['cn']
        ) );
        if( adefault( $opts, 'showhead' ) ) {
          $t = html_div( '', $t );
          if( ( $h_id = $group['head_people_id'] ) ) {
            $t .= html_div( 'qquadl smaller', alink_person_view( $h_id ) );
          }
          $t = html_div( 'inline_block', $t );
        }
        return $t;
      case 'pdf':
        // return span_view( 'href', $text );
      default:
        return $text;
    }
  } else {
    return we('(no group)','(keine Gruppe)');
  }
}

function alink_document_view( $filters, $opts = array() ) {
  global $aUML, $global_format;

  $opts = parameters_explode( $opts );
  $documents = sql_documents( $filters, array( 'orderby' => 'valid_from DESC' ) );
  if( count( $documents ) < 1 ) {
    return adefault( $opts, 'default', we('(no document)','(keine Datei vorhanden)' ) );
  }
  $format = adefault( $opts, 'format', 'latest' );
  switch( $format )  {
    case 'latest':
    case 'latest_and_select':
      $document = $documents[ 0 ];
      $text = adefault( $opts, 'text', $document['cn'] );
      switch( $global_format ) {
        case 'html':
          if( $document['url'] ) {
            $s = html_alink( $document['url'], array( 'text' => $text, 'class' => 'href outlink file' ) );
          } else {
            $s = inlink( 'download', array(
              'documents_id' => $document['documents_id']
            , 'class' => adefault( $opts, 'class', 'href inlink file' )
            , 'text' => $text
            , 'title' => $text
            , 'f' => 'pdf'
            , 'i' => 'document'
            ) );
          }
          if( ( count( $documents ) > 1 ) && ( $format == 'latest_and_select' ) ) {
            $field = array(
              'keyformat' => 'form_id'
            , 'default_display' => we('older versions...',"{$aUML}ltere Fassungen...")
            , 'choices' => array()
            , 'class' => 'qpadl'
            );
            for( $j = 1; $j < count( $documents ); $j++ ) {
              $d = $documents[ $j ];
              $f = ( $d['pdf'] ? 'pdf' : 'html' );
              $form_id = open_form( "script=download,f=$f,i=document,documents_id=".$d['documents_id'], '', 'hidden' );
              $field['choices'][ $form_id ] = $d['cn'];
             }
            $s .= html_span( 'qquadl', select_element( $field ) );
          }
          return $s;
        case 'pdf':
        default:
          return $text;
      }
      break;
    case 'list':
      switch( $global_format ) {
        case 'html':
          $s = html_tag('ul');
          if( $document['url'] ) {
            $s .= html_li( '', html_alink( $document['url'], array( 'text' => $text, 'class' => 'href outlink file' ) ) );
          } else {
            $s .= html_li( '', inlink( 'download', array(
              'documents_id' => $document['documents_id']
            , 'class' => adefault( $opts, 'class', 'href inlink' )
            , 'text' => $text
            , 'title' => $text
            , 'f' => 'pdf'
            , 'i' => 'document'
            ) ) );
          }
          $s .= html_tag( 'ul', false );
          return $s;
        case 'pdf':
        default:
          menatwork();
      }
  }
}

?>
