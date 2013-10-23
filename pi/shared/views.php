<?php

function programme_cn_view( $programme_id, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $text = $GLOBALS[ ( adefault( $opts, 'short' ) ? 'programme_text_short' : 'programme_text' ) ];
  $s = '';
  $comma = '';
  foreach( $text as $id => $cn ) {
    if( $programme_id & $id ) {
      $s .= "$comma$cn";
      $comma = ', ';
    }
  }
  return $s;
}

function optional_linklist_view( $items, $opts ) {
  global $aUML, $global_format;
  $opts = parameters_explode( $opts );
  $format = adefault( $opts, 'format', '' );
  $default = adefault( $opts, 'default', we('(empty list)',"(keine Eintr{$aUML}ge)") );
  switch( $global_format ) {
    case 'html':
      $class = adefault( $opts, 'class', '' );
      $ulist = ( ( count( $items ) > 1 ) || ( $format == 'list' ) );
      if( ! $items ) {
        if( $ulist ) {
          $class = merge_classes( 'linklist empty', $class );
        }
        return ( $class ? html_span( $class, $default ) : $default );
      }
      $s = ( $ulist ? html_tag( 'ul', 'linklist' ) : '' );
      foreach( $items as $r ) {
        if( $ulist ) {
          $s .= html_li( $class, $r );
        } else {
          $s .= ( $class ? html_span( $class, $r ) : $r );
        }
      }
      $s .= ( $ulist ? html_tag( 'ul', false ) : '' );
      return $s;

    case 'pdf':
    default:
      if( ! $items ) {
       return $default;
      }
      $s = '';
      foreach( $items as $r ) {
        $s .= "$r ";
      }
      return $s;
  }
}

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
      . html_div( 'td', html_alink( $group['url'], array( 'text' => $group['url'], 'class' => 'href outlink' ) ) )
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
  $class = adefault( $opts, 'class', '' );
  $filters = restrict_view_filters( $filters, 'people' );
  if( isnumber( $filters ) && isset( $cache[ $filters ] ) ) {
    $people = array( $cache[ $filters ] );
  } else {
    $people = sql_people( $filters );
  }
  $default = adefault( $opts, 'default', ( adefault( $opts, 'office' ) ? we(' - vacant - ',' - vakant - ') : we('(no person)','(keine Person)') ) );

  $format = adefault( $opts, 'format', '' );
  $items = array();
  foreach( $people as $person ) {
    $cache[ $person['people_id'] ] = $person;
    $text = adefault( $opts, 'text', $person['cn'] );
    switch( $global_format ) {
      case 'html':
        $t = inlink( 'person_view', array(
          'people_id' => $person['people_id']
        , 'class' => 'href inlink'
        , 'text' => $text
        , 'title' => $text
        ) );
        if( adefault( $opts, 'showgroup' ) ) {
          $t = html_div( '', $t );
          if( ( $g_id = $person['primary_groups_id'] ) && ( $person['primary_flag_publish'] ) ) { 
            $t .= html_div( 'qquadl smaller', alink_group_view( $g_id, 'fullname=1' ) );
          } else if( $person['url'] ) {
            $t .= html_div( 'qquadl smaller', html_alink( $person['url'], array( 'class' => 'a href outlink', 'text' => $person['url'] ) ) );
          }
          $t = html_div( 'inline_block', $t );
        }
        $items[] = $t;
        break;
      case 'pdf':
        // return span_view( 'href', $text ); // url_view() makes no sense for deep links (in general)
      default:
        $items[] = $text;
    }
  }
  return optional_linklist_view( $items, array( 'format' => $format, 'default' => $default, 'class' => $class ) );
}

function alink_group_view( $filters, $opts = array() ) {
  global $global_format;
  static $cache = array();

  $opts = parameters_explode( $opts );
  $class = adefault( $opts, 'class', '' );
  if( isnumber( $filters ) && isset( $cache[ $filters ] ) ) {
    $groups = array( $cache[ $filters ] );
  } else {
    $filters = restrict_view_filters( $filters, 'groups' );
    $groups = sql_groups( $filters );
  }
  $default = adefault( $opts, 'default', we('(no group)','(keine Gruppe)') );

  $format = adefault( $opts, 'format', '' );
  $items = array();
  foreach( $groups as $group ) {
    $cache[ $group['groups_id'] ] = $group;
    $text = adefault( $opts, 'text', $group[ adefault( $opts, 'fullname' ) ? 'cn' : 'acronym' ] );
    switch( $global_format ) {
      case 'html':
        $t = inlink( 'group_view', array(
          'groups_id' => $group['groups_id']
        , 'class' => 'href inlink'
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
        $items[] = $t;
        break;
      case 'pdf':
        // return span_view( 'href', $text );
      default:
        $items[] = $text;
        break;
    }
  }
  return optional_linklist_view( $items, array( 'format' => $format, 'default' => $default, 'class' => $class ) );
}

function alink_document_view( $filters, $opts = array() ) {
  global $aUML, $global_format;

  $opts = parameters_explode( $opts );
  $class = adefault( $opts, 'class', '' );
  $filters = restrict_view_filters( $filters, 'documents' );
  $documents = sql_documents( $filters );

  $default = adefault( $opts, 'default', we('(no document)','(keine Datei vorhanden)' ) );

  $format = adefault( $opts, 'format', 'latest' );
  switch( $format )  {
    case 'latest':
    case 'latest_and_select':
      if( ! $documents ) {
        return $default;
      }
      $document = $documents[ 0 ];
      $text = adefault( $opts, 'text', $document['cn'] );
      switch( $global_format ) {
        case 'html':
          if( $document['url'] ) {
            $s = html_alink( $document['url'], array( 'text' => $text, 'class' => 'href file' ) );
          } else {
            $s = inlink( 'download', array(
              'documents_id' => $document['documents_id']
            , 'class' => 'href inlink file'
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
      $max = adefault( $opts, 'max', 0 );
      $items = array();
      foreach( $documents as $d ) {
        switch( $global_format ) {
          case 'html':
            if( $d['url'] ) {
              $items[] = html_alink( $d['url'], array( 'text' => $d['cn'], 'class' => 'href file' ) );
            } else {
              $items[] = inlink( 'download', array(
                'documents_id' => $d['documents_id']
              , 'class' => 'href inlink file'
              , 'text' => $d['cn']
              , 'title' => $d['cn']
              , 'f' => 'pdf'
              , 'i' => 'document'
              ) );
              if( ! --$max ) {
                break;
              }
            }
            break;

          case 'pdf':
          default:
            $items[] = $d['cn'];
            break;
        }
      }
      return optional_linklist_view( $items, array( 'format' => $format, 'default' => $default, 'class' => $class ) );
    }
}

?>
