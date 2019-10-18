<?php

function programme_cn_view( $programme_flags, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $text = $GLOBALS[ ( adefault( $opts, 'short' ) ? 'programme_text_short' : 'programme_text' ) ];
  $s = '';
  $comma = '';
  foreach( $text as $id => $cn ) {
    if( $programme_flags & $id ) {
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
        if( $default === NULL ) {
          return NULL;
        }
        if( $ulist ) {
          $class = merge_classes( 'linklist empty', $class );
        }
        return ( $class ? html_span( array( 'class' => $class ), $default ) : $default );
      }
      $s = ( $ulist ? html_tag( 'ul', 'linklist plain' ) : '' );
      foreach( $items as $r ) {
        if( $ulist ) {
          $s .= html_li( $class, $r );
        } else {
          $s .= ( $class ? html_span( array( 'class' => $class ), $r ) : $r );
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
  $s = '';
  if( $pub['jpegphoto'] ) {
    $s = html_span( 'floatright,style=display:inline-block;', photo_view( $pub['jpegphoto'], $pub['jpegphotorights_people_id'] ) );
  }
  $s .= html_div( 'bold center smallskips', $pub['title'] );
  $s .= html_div( 'center smallskips', $pub['authors'] );
  $s .= html_div( 'left smallskips', $pub['abstract'] );

  return html_div( 'highlight', $s );
}

function publication_block_view( $pub, $opts = array() ) {
  global $oUML;

  if( isarray( $pub ) && ! isset( $pub['publications_id'] ) ) {
    $s = html_tag( 'div', 'highlight nopads inline_block' );
    foreach( $pub as $p ) {
      $s .= publication_block_view( $p, $opts );
    }
    $s .= html_tag('div', false );
    return $s;
  }

  $s = '';
  if( $pub['jpegphoto'] ) {
    $s .= html_span( 'floatright,style=display:inline-block;', photo_view( $pub['jpegphoto'], $pub['jpegphotorights_people_id'] ) );
  }

  $s .= html_div( 'cn', inlink( 'publikation', array( 'class' => 'href', 'text' => $pub['cn'], 'publications_id' => $pub['publications_id'] ) ) );
  $s .= html_div( 'summary', $pub['summary'] );
  $s .= html_div( 'smallskips', we('Publication: ',"Ver{$oUML}ffentlichung: ") . publication_reference_view( $pub ) );
  $s .= html_div( 'smallskips', we('Research group: ','Arbeitsgruppe: ') . alink_group_view( $pub['groups_id'], 'fullname=1' ) );
  if( $pub['info_url'] ) {
    $s .= html_div( 'smallskips', we('More information:','Weitere Informationen:')
                      . html_alink( $pub['info_url'], array( 'class' => 'href outlink', 'target' => '_new', 'text' => lenlimit( $pub['info_url'], 60 ) ) )
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
//  $s = $pub['title'];
  $title = $pub['title'];
  $ref = $pub['journal']. ', ' . span_view( 'bold', $pub['volume'] ) . ' ' .$pub['page'];
  $url = $pub['journal_url'];
  if( $url ) {
    $title = html_alink( $url, array( 'class' => 'href outlink', 'text' => $title ) );
    $ref = html_alink( $url, array( 'class' => 'href outlink', 'text' => $ref ) );
  }
  $s .= "$title, $ref, ({$pub['year']})";
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

function groupcontact_view( $group ) {
  $s = html_div('table');
  $s .= html_div( 'tr'
  , html_div( 'td', we('Head:','Leitung:' ) )
    . html_div( 'td', alink_person_view( $group['head_people_id'], 'office' ) )
  );

  $s .= html_div( 'tr'
  , html_div( 'td', we('Secretary:','Sekretariat:' ) )
    . html_div( 'td', alink_person_view( $group['secretary_people_id'], 'office' ) )
  );

  if( $group['url'] ) {
    $s .= html_div( 'tr'
    , html_div( 'td', we('Web page:','Webseite:') )
      . html_div( 'td', html_alink( $group['url'], array( 'text' => $group['url'], 'class' => 'href outlink' ) ) )
    );
  }
  $s .= html_div( false );
  return $s;
}

function group_view( $group, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $hlevel = adefault( $opts, 'hlevel', 2 );
  $class = merge_classes( 'group', adefault( $opts, 'class', '' ) );
  if( isnumber( $group ) ) {
    $group = sql_one_group( $group );
  }
  
  $s = '';
  switch( $group['status'] ) {
    case GROUPS_STATUS_PROFESSOR:
    case GROUPS_STATUS_SPECIAL:
      $t = we('Group:','Arbeitsgruppe:');
      break;
    case GROUPS_STATUS_JOINT:
    case GROUPS_STATUS_EXTERNAL:
    case GROUPS_STATUS_LABCOURSE:
      $t = '';
      break;
    case GROUPS_STATUS_OTHER:
    default:
      $t = we('Group:','Bereich:');
      break;
  }
  $heading = html_tag( "h$hlevel", '', $t . ' ' . $group['cn'] );
  if( $group['h2'] ) {
    $hlevel++;
    $heading .= html_tag( "h$hlevel", '', $group['h2'] );
  }

  $s = groupcontact_view( $group );
  if( $group['note'] ) {
    $s .= html_span( 'description', $group['note'] );
  }

  if( $group['jpegphoto'] ) {
    $p = sql_one_person( $group['jpegphotorights_people_id'], 0 );
    if( $p ) {
      $img = html_img( $group['jpegphoto'], '', credits( $p['cn_notitle'], 'format=jpeg' ) );
      $s = html_div( 'textaroundphoto', html_div( 'illu', $img ) . $s );
    }
  }

  return html_div( 'group', $heading . $s );
}

function person_visitenkarte_view( $person, $opts = array() ) {
  global $oUML, $choices_person_status;
  $opts = parameters_explode( $opts );
  if( isnumber( $person ) ) {
    $person = sql_person( $person );
  }
  $people_id = $person['people_id'];

  $hlevel = adefault( $opts, 'hlevel', 1 );

  $emails = $phones = $faxes = $rooms = $hours = array();
  $affiliations = sql_affiliations( "people_id=$people_id,groups.flag_publish" );
  $n_aff = count( $affiliations );
  foreach( $affiliations as $aff ) {
    if( ( $r = $aff['roomnumber' ] ) ) {
      if( ! in_array( $r, $rooms ) ) {
        $rooms[] = $r;
      }
    }
    if( ( $r = $aff['mail' ] ) ) {
      if( ! in_array( $r, $emails ) ) {
        $emails[] = $r;
      }
    }
    if( ( $r = $aff['telephonenumber' ] ) ) {
      if( ! in_array( $r, $phones ) ) {
        $phones[] = $r;
      }
    }
    if( ( $r = $aff['facsimiletelephonenumber' ] ) ) {
      if( ! in_array( $r, $faxes ) ) {
        $faxes[] = $r;
      }
    }
    if( ( $r = $aff['office_hours' ] ) ) {
      if( ! in_array( $r, $hours ) ) {
        $hours[] = $r;
      }
    }
  }
  // on special request
  $special = '';
  if( ( $person['gn'] == 'Ralf' ) && ( $person['sn'] == 'Menzel' ) && ( $person['people_id'] == 32 ) ) {
    $emails[] = 'photonics@uni-potsdam.de';
    $phones[] = '+49 331 977 1026';
    $faxes[] = '+49 331 977 1577';
    $special = '-';
  }

  $s = '';
  if( $person['jpegphoto'] ) {
    $s .= html_span( 'floatright', photo_view( $person['jpegphoto'], $person['jpegphotorights_people_id'] ) );
  }

  $s .= html_tag( "h$hlevel", '', $person['cn'] );
  switch( $person['status'] ) {
    case PEOPLE_STATUS_FORMER:
    case PEOPLE_STATUS_EMERITUS:
      $s .= html_div( 'smallskipt medskipb', $choices_person_status[ $person['status'] ] );
      break;
    default:
  }

  $th = 'td top smallpads qqpads';
  $td = 'td bottom smallpads qqpads';
  $tr = 'tr';
  $s .= html_div('table') ;

    if( count( $rooms ) === 1 ) {
      $s .= html_div( $tr, html_div( $th, we('Room:','Raum:') ) . html_div( $td, $rooms[ 0 ] ) );
    }
    if( count( $hours ) === 1 ) {
      $s .= html_div( $tr, html_div( $th, we('Office hours:','Sprechzeiten:') ) . html_div( $td, $hours[ 0 ] ) );
    }
    if( count( $phones ) === 1 ) {
      $s .= html_div( $tr, html_div( $th, we('Phone:','Telefon:') ) . html_div( $td, $phones[ 0 ] ) );
    }
    if( count( $faxes ) === 1 ) {
      $s .= html_div( $tr, html_div( $th, 'Fax:' ) . html_div( $td, $faxes[ 0 ] ) );
    }
    if( count( $emails ) === 1 ) {
      $s .= html_div( $tr, html_div( $th, 'Email:' ) . html_div( $td, html_obfuscate_email( $emails[ 0 ] ) ) );
    }
    if( $person['url'] ) {
      $s .= html_div( $tr, html_div( $th, 'Web:' ) . html_div( $td, html_alink( $person['url'], array( 'class' => 'href outlink', 'text' => $person['url'] ) ) ) );
    }
    $s .= $special;

    foreach( $affiliations as $aff ) {
      switch( $person['status'] ) {
        case PEOPLE_STATUS_FORMER:
        case PEOPLE_STATUS_EMERITUS:
        case PEOPLE_STATUS_RIP:
          $t = we('former Group:','ehemaliger Bereich:');
          break;
        default:
          $t = we('Group:','Bereich:');
      }
      $s .= html_div( $tr . ( ( $n_aff > 1 ) ? ' solidtop' : '' ), html_div( $th, $t ) . html_div( $td, alink_group_view( $aff['groups_id'], 'fullname=1' ) ) );

      if( $aff['roomnumber'] && ( count( $rooms ) > 1 ) ) {
        $s .= html_div( $tr, html_div( $th, we('Room:','Raum:') ) . html_div( $td, $aff['roomnumber'] ) );
      }
      if( $aff['office_hours'] && ( count( $hours ) > 1 ) ) {
        $s .= html_div( $tr, html_div( $th, we('Office hours:','Sprechzeiten:') ) . html_div( $td, $aff['office_hours'] ) );
      }
      if( $aff['telephonenumber'] && ( count( $phones ) > 1 ) ) {
        $s .= html_div( $tr, html_div( $th, we('Phone:','Telefon:') ) . html_div( $td, $aff['telephonenumber'] ) );
      }
      if( $aff['facsimiletelephonenumber'] && ( count( $faxes ) > 1 ) ) {
        $s .= html_div( $tr, html_div( $th, 'Fax:' ) . html_div( $td, $aff['facsimiletelephonenumber'] ) );
      }
      if( $aff['mail'] && ( count( $emails ) > 1 ) ) {
        $s .= html_div( $tr, html_div( $th, 'Email:' ) . html_div( $td, html_obfuscate_email( $aff['mail'] ) ) );
      }
    }
    if( $person['affiliation_cn'] ) {
      $t = $person['affiliation_cn'];
      if( $person['affiliation_url'] ) {
        $t = html_alink( $person['affiliation_url'], array( 'class' => 'href outlink', 'text' => $t ) );
      }
      $s .= html_div( $tr . ( ( $n_aff >= 1 ) ? ' solidtop' : '' ), html_div( $th, we('external affiliation:', "externe Zugeh{$oUML}rigkeit:") ) . html_div( $td, $t ) );
    }

  $s .= html_div( false );
  return $s;
}
  

function position_view( $position, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $hlevel = adefault( $opts, 'hlevel', 1 );
  if( isnumber( $position ) ) {
    $position = sql_one_position( $position );
  }
  $positions_id = $position['positions_id'];

  $s = '';
//  if( $position['jpegphoto'] ) {
//    $s .= html_span( 'floatright', photo_view( $position['jpegphoto'], $position['jpegphotorights_people_id'] ) );
//  }
  $s .= html_tag( "h$hlevel", '', we('Suggested topic: ','Themenvorschlag: ' ) . $position['cn'] );

  $s .= html_span( 'description', $position['note'] );

  $s .= html_div( 'table' );

  $s .= html_div( 'tr'
  , html_div( 'td',  we('Programme / final Degree:','Studiengang / Abschluss:') )
    . html_div( 'td', programme_cn_view( $position['programme_flags'] ) )
  );

  $t = '';
  if( ( $url = $position['url'] ) ) {
    $t .= html_div( 'oneline smallskipb', html_alink( $position['url'], array( 'class' => 'href outlink', 'text' => $position['url'] ) ) );
  }
  if( $position['pdf'] ) {
    $filename = preg_replace( '/[^a-zA-Z0-9._-]/', '', $position['cn'] );
    $filename = preg_replace( '/[.][.]+/', '.', $filename );
    $filename = preg_replace( '/[.]+$/', '', $filename );
    $link = inlink( 'position_view', array(
      'text' => "$filename.pdf"
    , 'class' => 'file'
    , 'f' => 'pdf'
    , 'window' => 'download'
    , 'i' => 'attachment'
    , 'n' => hex_encode( $filename )
    , 'positions_id' => $positions_id
    ) );
    $t .= html_div( 'oneline', $link );
  }
  if( $t ) {
    $s .= html_div( 'tr'
   , html_div('td', we('more information:', 'weitere Informationen:' ) )
     . html_div( 'td', $t )
    );
  }
  $s .= html_div( 'tr'
  , html_div( 'td', we('Group:','Gruppe:') )
    . html_div( 'td', alink_group_view( $position['groups_id'], 'fullname=1' ) )
  );
  $s .= html_div( 'tr'
  , html_div( 'td', we('Contact:','Ansprechpartner:') )
    . html_div( 'td', alink_person_view( $position['contact_people_id'] ) )
  );
  $s .= html_div( false );

  // $s .= html_div( 'right', download_button( 'position', 'ldif,pdf', "positions_id=$positions_id" ) );

  return html_div( 'position textaroundphoto', $s );
}

function event_view( $event, $opts = array() ) {
  global $NBSP;

  $opts = parameters_explode( $opts );
  $hlevel = adefault( $opts, 'hlevel', 1 );
  $format = adefault( $opts, 'format', 'detail' );
  $show_year = adefault( $opts, 'show_year', 0 );

  if( isnumber( $event ) ) {
    $event = sql_one_event( $event );
  }
  $events_id = $event['events_id'];
  $g_id = $event['groups_id'];
  $p_id = $event['people_id'];
  if( ( $date = $event['date'] ) ) {
    $date_traditional = substr( $date, 6, 2 ) .'.'. substr( $date, 4, 2 ) .'.'. ( $show_year ? substr( $date, 0, 4 ) : '' );
  } else {
    $date_traditional = '';
  }
  $datetime_traditional = $date_traditional;
  if( ( $time = $event['time'] ) ) {
    $time_traditional = substr( $time, 0, 2 ) .':'. substr( $time, 2, 2 ) . we('',"{$NBSP}Uhr");
    if( $datetime_traditional ) {
      $datetime_traditional .= ",{$NBSP}{$time_traditional}";
    } else {
      $datetime_traditional = $time_traditional;
    }
  } else {
    $time_traditional = '';
  }

  $s = '';

  switch( $format ) {
    case 'detail':

      if( $event['jpegphoto'] ) {
        $s .= html_span(
          'floatright,style=display:inline-block;'
        , photo_view(
          $event['jpegphoto']
          , $event['jpegphotorights_people_id']
          , array( 'url' => inlink( 'event_view', "events_id=$events_id,i=photo,f=jpg,context=url" ) )
          )
        );
      }

      $s .= html_tag( "h$hlevel", '', ( $date_traditional ? "$date_traditional: " : '' ) . $event['cn'] );

      if( $event['note'] ) {
        $s .= html_span( 'description', $event['note'] );
      }
      $s .= html_div( 'table' );
      if( $event['location'] ) {
        $s .= html_div( 'tr' , html_div('td', we('Location:', 'Ort:' ) ) . html_div( 'td', $event['location'] ) );
      }
      if( $time_traditional ) {
        $s .= html_div( 'tr' , html_div('td', we('Time:', 'Zeit:' ) ) . html_div( 'td', $time_traditional ) );
      }

      $t = '';
      if( ( $url = $event['url'] ) ) {
        $t .= html_div( 'oneline smallskipb', html_alink( $url, array( 'text' => $url, 'class' => 'href '.$event['url_class'] ) ) );
      }
      if( $event['pdf'] ) {
        $text = ( $event['pdf_caption'] ? $event['pdf_caption'] : 'download .pdf' );
        $t .= html_div( 'oneline', inlink( 'event_view', array(
          'text' => $text
        , 'class' => 'file'
        , 'f' => 'pdf'
        , 'window' => 'download'
        , 'i' => 'attachment'
        , 'events_id' => $events_id
        ) ) );
      }
      if( $t ) {
        $s .= html_div( 'tr' , html_div('td quads', we('Read more:', 'Details:' ) ) . html_div( 'td quads', $t ) );
      }

      $t = '';
      if( $p_id ) {
        $t = html_div( '', alink_person_view( $p_id, 'default=' ) );
      } else if( $g_id ) {
        $t = html_div( '', alink_group_view( $g_id, 'default=,fullname=1' ) );
      }
      if( $t ) {
        $s .= html_div( 'tr', html_div( 'td',  we('Contact: ','Kontakt: ') ) . html_div( 'td', $t ) );
      }

      $s .= html_div( false );
      // $s .= html_div( 'right', download_button( 'event', 'ldif,pdf', "events_id=$events_id" ) );
      return html_div( 'event', $s );

    case 'ticker':
      if( $datetime_traditional ) {
        $s .= "$datetime_traditional:{$NBSP}";
      }
      $t = $event['cn'];
      if( $event['flag_detailview'] ) {
        $t = inlink( 'event_view', array( 'events_id' => $events_id, 'text' => $t ) );
      } else if( $event['url'] ) {
        $t = html_alink( $event['url'], array( 'class' => 'href '.$event['url_class'], 'text' => $t ) );
      } else if( $event['pdf'] ) {
        $t = inlink( 'event_view', array( 'class' => 'href file', 'text' => $t, 'i' => 'attachment', 'f' => 'pdf', 'window' => 'download', 'events_id' => $events_id ) );
      }
      $s .= $t;
      if( ! $event['flag_detailview'] ) {
        if( $event['location'] ) {
          $s .= ", {$event['location']}";
        }
        $t = '';
        if( $p_id ) {
          $t = alink_person_view( $p_id, 'default=' );
        } else if( $g_id ) {
          $t = alink_group_view( $g_id, 'default=,fullname=1' );
        }
        if( $t ) {
          $s .= ", " . we('Contact: ','Kontakt: ') . $t;
        }
      }
      return html_span( 'tickerline', $s );
      // return html_div( 'tickeritem', "+++$NBSP$s$NBSP+++" );

    case 'highlight':
      if( $event['jpegphoto'] ) {
        $s .= html_span( 'floatright inline_block', photo_view( $event['jpegphoto'], $event['jpegphotorights_people_id'] ) );
      }
      $s .= html_tag( "h$hlevel", '', ( $date_traditional ? "$date_traditional: " : '' ) . $event['cn'] );
      if( $event['note'] ) {
        $s .= html_span( 'description', $event['note'] );
      }
      $t = '';
      if( ( $url = $event['url'] ) ) {
        $t .= html_div( 'oneline smallskipb', html_alink( $url, array( 'text' => $url, 'class' => 'href '.$event['url_class'] ) ) );
      }
      if( $event['pdf'] ) {
        $text = ( $event['pdf_caption'] ? $event['pdf_caption'] : 'download .pdf' );
        $t .= inlink( 'event_view', array(
          'text' => $text
        , 'class' => 'file'
        , 'f' => 'pdf'
        , 'window' => 'download'
        , 'i' => 'attachment'
        , 'events_id' => $events_id
        ) );
      }
      if( $t ) {
        $s .= html_div( 'oneline medskipt', we('Read more: ', 'Details: ' ) . $t );
      }
    
      $s .= html_div( 'clear', '' );
      return html_span( 'block highlight', $s );

    case 'table':

      $s1 = $date_traditional;

      $t = $event['cn'];
      if( $event['flag_detailview'] ) {
        $t = inlink( 'event_view', array( 'events_id' => $events_id, 'text' => $t ) );
      } else if( $event['url'] ) {
        $t = html_alink( $event['url'], array( 'class' => 'href '.$event['url_class'], 'text' => $t ) );
      } else if( $event['pdf'] ) {
        $t = inlink( 'event_view', array( 'class' => 'href file', 'text' => $t, 'i' => 'attachment', 'f' => 'pdf', 'events_id' => $events_id ) );
      }
      $s2 = $t;

      if( ! $event['flag_detailview'] ) {
        if( $event['location'] ) {
          $s2 .= ", {$event['location']}";
        }
        $t = '';
        if( $p_id ) {
          $t = alink_person_view( $p_id, 'default=' );
        } else if( $g_id ) {
          $t = alink_group_view( $g_id, 'default=,fullname=1' );
        }
        if( $t ) {
          $s2 .= ", " . we('Contact: ','Kontakt: ') . $t;
        }
      }

      return html_tag( 'tr', '', html_tag( 'td', '', $s1 ) . html_tag( 'td', '', $s2 ) );
  }
}

function highlight_view( $highlight, $opts = array() ) {
  global $NBSP;

  $opts = parameters_explode( $opts );
  $hlevel = adefault( $opts, 'hlevel', 1 );
  $format = adefault( $opts, 'format', 'highlight' );
  $show_year = adefault( $opts, 'show_year', 0 );

  if( isnumber( $highlight ) ) {
    $highlight = sql_one_highlight( $highlight );
  }
  $highlights_id = $highlight['highlights_id'];
  $g_id = $highlight['groups_id'];
  $p_id = $highlight['people_id'];
  $person = sql_person( $p_id, 0 );
  if( ( $date = $highlight['date'] ) ) {
    $date_traditional = substr( $date, 6, 2 ) .'.'. substr( $date, 4, 2 ) .'.'. ( $show_year ? substr( $date, 0, 4 ) : '' );
  } else {
    $date_traditional = '';
  }
  $datetime_traditional = $date_traditional;
  if( ( $time = $highlight['time'] ) ) {
    $time_traditional = substr( $time, 0, 2 ) .':'. substr( $time, 2, 2 ) . we('',"{$NBSP}Uhr");
    if( $datetime_traditional ) {
      $datetime_traditional .= ",{$NBSP}{$time_traditional}";
    } else {
      $datetime_traditional = $time_traditional;
    }
  } else {
    $time_traditional = '';
  }

  $s = '';

  switch( $format ) {

    case 'highlight':
      if( $person['jpegphoto'] && $highlight['flag_show_person_photo'] ) {
        $url = 'javascript:'.inlink( 'person_view', "people_id=$p_id,context=js" );
        $t = photo_view( $person['jpegphoto'], $person['jpegphotorights_people_id'], array( 'url' => $url ) );
        $s .= html_span( 'floatright inline_block', $t );
      } else if( $highlight['jpegphoto'] ) {
        $s .= html_div('illu', html_img(
          $highlight['jpegphoto']
        , $highlight['jpegphoto_alt']
        , credits( $highlight['jpegphotorights_people_id'] ? $highlight['jpegphotorights_people_id'] : $highlight['jpegphotorights_text'] )
        , array( 'url' => $highlight['url'], 'format' => 'jpeg' )
        ) );
      }
      $s .= html_div( 'cn', ( $date_traditional ? "$date_traditional: " : '' ) . $highlight['cn'] );
      if( $highlight['note'] ) {
        $s .= html_div( 'summary', $highlight['note'] );
      }
      if( ( $url = $highlight['url'] ) ) {
        $t = html_alink( $url, array( 'text' => lenlimit( $url, 60 ), 'class' => 'href '.$highlight['url_class'] ) );
        $s .= html_div( 'inline_block qqpadr smallskips', we('Read more: ', 'Weitere Informationen: ' ) . $t );
      }
      if( $highlight['flag_link_persongroup'] ) {
        $t = '';
        if( $highlight['people_id'] ) {
          $t = alink_person_view( $highlight['people_id'] );
        } else if( $highlight['groups_id'] ) {
          $t = alink_group_view( $highlight['groups_id'], 'fullname=1' );
        }
        if( $t ) {
          $s .= html_div( 'oneline qqpadr smallskips', we('Contact: ', 'Kontakt: ' ) . $t );
        }
      }
      if( $highlight['pdf'] ) {
        $text = ( $highlight['pdf_caption'] ? $highlight['pdf_caption'] : 'download .pdf' );
        $t = inlink( 'highlight_view', array(
          'text' => $text
        , 'class' => 'file qqpadr'
        , 'f' => 'pdf'
        , 'window' => 'download'
        , 'i' => 'attachment'
        , 'highlights_id' => $highlights_id
        ) );
        $s .= html_div( 'oneline smallskips', $t );
      }

      $s .= html_div( 'clear', '' ); 
      return html_div( 'block highlight', $s );
  }
}

function alink_person_view( $filters, $opts = array() ) {
  global $global_format;
  static $cache = array();

  $opts = parameters_explode( $opts );
  $class = adefault( $opts, 'class', '' );
  $filters = restrict_view_filters( $filters, 'people' );
  if( isnumber( $filters ) && isset( $cache[ $filters ] ) ) {
    $people = array( $cache[ $filters ] );
  } else if( adefault( $filters, -1 ) == 'people_record' ) {
    $people = array( $filters );
  } else {
    $people = sql_people( $filters );
  }
  $default = adefault( $opts, 'default', ( adefault( $opts, 'office' ) ? we(' - vacant - ',' - vakant - ') : we('(no person)','(keine Person)') ) );

  $format = adefault( $opts, 'format', '' );
  $items = array();
  foreach( $people as $person ) {
    $cache[ $person['people_id'] ] = $person;
    $text = adefault( $opts, 'text', $person['cn'] );
    $title = adefault( $opts, 'title', $text );
    switch( $global_format ) {
      case 'html':
        $t = inlink( 'person_view', array(
          'people_id' => $person['people_id']
        , 'class' => 'href inlink'
        , 'text' => $text
        , 'title' => $title
        ) );
        if( $showgroup = adefault( $opts, 'showgroup' ) ) {
          $grouplinks = '';
          if( ( $g_id = $person['primary_groups_id'] ) && ( $person['primary_flag_publish'] ) ) { 
            $grouplinks .= html_div( 'qquadl tiny', alink_group_view( $g_id, 'fullname=1' ) );
          }
          if( ( $aff = $person['affiliation_cn'] ) ) {
            if( ( $u = $person['affiliation_url'] ) ) {
              $aff = html_alink( $u, array( 'class' => 'outlink', 'text' => $aff ) );
            }
            $grouplinks .= html_div( 'qquadl tiny', $aff );
          }
          if( ! $grouplinks ) {
            if( $person['url'] ) {
              $grouplinks = html_div( 'qquadl tiny', html_alink( $person['url'], array( 'class' => 'a href outlink', 'text' => $person['url'] ) ) );
            }
          }
          if( $grouplinks ) {
            $t = html_div( 'inline_block', html_div( '', $t ) . $grouplinks );
          }
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
  global $global_format, $jlf_application_name;
  static $cache = array();

  $opts = parameters_explode( $opts );
  $class = adefault( $opts, 'class', '' );
  if( isnumber( $filters ) && isset( $cache[ $filters ] ) ) {
    $groups = array( $cache[ $filters ] );
  } else if( adefault( $filters, -1 ) == 'groups_record' ) {
    $groups = array( $filters );
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
        switch( $group['status'] ) {
          case GROUPS_STATUS_LABCOURSE:
            if( $jlf_application_name == 'pp' ) {
              $t = inlink( 'praktika', array(
                'class' => 'href inlink'
              , 'text' => $text
              , 'title' => $group['cn']
              ) );
              break;
            }
          default:
            $t = inlink( 'group_view', array(
              'groups_id' => $group['groups_id']
            , 'class' => 'href inlink'
            , 'text' => $text
            , 'title' => $group['cn']
            ) );
            break;
        }
        if( adefault( $opts, 'showhead' ) ) {
          $t = html_div( '', $t );
          if( ( $h_id = $group['head_people_id'] ) ) {
            $t .= html_div( 'qquadl tiny', alink_person_view( $h_id ) );
          }
          if( ( $more_id = adefault( $opts, 'showmore' ) ) ) {
            $t .= html_div( 'qquadl tiny', alink_person_view( $more_id ) );
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
  global $aUML, $uUML, $global_format;

  $opts = parameters_explode( $opts );
  $class = adefault( $opts, 'class', '' );
  $filters = restrict_view_filters( $filters, 'documents' );

  $default = adefault( $opts, 'default', we('(no document)','(keine Datei vorhanden)' ) );
  $sql_opts = array();

  $format = adefault( $opts, 'format', 'latest' );
  switch( $format )  {
    case 'latest':
    case 'latest_and_select':
      $sql_opts['orderby'] = adefault( $opts, 'orderby', 'valid_from DESC' );
      $documents = sql_documents( $filters, $sql_opts );
      if( ! $documents ) {
        return $default;
      }
      $document = $documents[ 0 ];
      $text = adefault( $opts, 'text', $document['cn'] );
      switch( $global_format ) {
        case 'html':
          if( $document['url'] ) {
            $s = html_alink( $document['url'], array( 'text' => $text, 'class' => 'href '.$document['url_class'] ) );
          } else if( $document['pdf'] ) {
            $s = inlink( 'download', array(
              'documents_id' => $document['documents_id']
            , 'class' => 'href inlink file'
            , 'text' => $text
            , 'title' => $text
            , 'f' => 'pdf'
            , 'i' => 'document'
            , 'n' => hex_encode( $document['filename'] )
            ) );
          } else {
            $s = $text . ': ' . we('document not available',"Datei nicht verf{$uUML}gbar");
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
      $sql_opts['orderby'] = adefault( $opts, 'orderby', 'programme_flags,valid_from DESC' );
      $documents = sql_documents( $filters, $sql_opts );
      foreach( $documents as $d ) {
        switch( $global_format ) {
          case 'html':
            if( $d['url'] ) {
              $items[] = html_alink( $d['url'], array( 'text' => $d['cn'], 'class' => 'href '.$d['url_class'] ) );
            } else if( $d['pdf'] ) {
              $items[] = inlink( 'download', array(
                'documents_id' => $d['documents_id']
              , 'class' => 'href inlink file'
              , 'text' => $d['cn']
              , 'title' => $d['cn']
              , 'f' => 'pdf'
              , 'i' => 'document'
              , 'n' => hex_encode( $d['filename'] )
              ) );
              if( ! --$max ) {
                break;
              }
            } else {
              $items[] = $d['cn'] . ': ' . we('document not available',"Datei nicht verf{$uUML}gbar");
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

function teaser_view( $pattern, $opts = array() ) {
  global $options, $language_suffix;

  $teaser = sql_teaser( array(
    'tags ~=' => "$pattern"
  , "note_$language_suffix !=" => ''
  , 'jpegphoto !=' => ''
  , 'people.people_id'
  )
  , array( 'orderby' => "RAND()" )
  );
  if( ! $teaser ) {
    return '';
  }
  $t = $teaser[ 0 ];
  
  $opts = parameters_explode( $opts );

  $format = adefault( $opts, 'format', 'teaser' );
  $class = adefault( $opts, 'class', 'teaser' );

  $s = html_div( 'floatright', photo_view( $t['jpegphoto'], $t['jpegphotorights_people_id'], array( 'class' => $class ) ) );
  $s .= html_span( 'large', '"'.$t['note'].'"' );
  $s = html_div('teaser textaroundphoto medskips qquads italic large,style=max-width:600px;', $s );
  switch( $format ) {
    case 'plain':
      return $s;
    case 'teaser':
      $s = inlink( '!', array(
        'class' => 'floatright icon qquadl close'
      , 'options' => ( $options & ~OPTION_SHOW_TEASER )
      , 'title' => we('close teaser','Schliessen' )
      ) ) . $s;
      return html_tag( 'fieldset', $class, $s );
    default:
      return '';
  }
}

?>
