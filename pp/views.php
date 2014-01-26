<?php

require_once('code/views.php');

function window_title() {
  return 'Universität Potsdam - Institut für Physik und Astronomie';
}

function address_view( $opts = array() ) {
  $opts = parameters_explode( $opts );
  $maplink = adefault( $opts, 'maplink', true );
  open_tag( 'address' );
    open_tag( 'a', array( 'href' => 'http://www.openstreetmap.org/#map=19/52.40935/12.97329&layers=N', 'class' => 'maplink inline_block', 'style' => 'display:inline-block;' ) );
      if( ( $header = adefault( $opts, 'header', true ) ) ) {
        open_span( 'header', ( $header === true ) ? we('Contact','Kontakt') : $header );
      }
      open_span( '', we('University of Potsdam','Universität Potsdam') );
      open_span( '', we('Institute of Physics and Astronomy','Institut für Physik und Astronomie') );
      open_span( '', we('Campus Golm, building 28', 'Campus Golm, Haus 28' ) );
      open_span( '', 'Karl-Liebknecht-Straße 24/25' );
      open_span( '', '14476 Potsdam-Golm' );
      if( $maplink ) {
        open_span( 'smallskipt left smaller', we('link to map:','Link zur Karte:') );
        open_span( 'center', photo_view( '/pp/fotos/osm.haus28.tiny.gif', 'OpenStreetMap project', 'format=url' ) );
      }
    close_tag( 'a' );
  close_tag( 'address' );
}

function peoplelist_view( $filters_in = array(), $opts = array() ) {
  global $global_format, $client_is_intranet;

  $filters = array( '&&', 'people.flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'groups.flag_publish' );
  if( $filters_in ) {
    $filters[] = $filters_in;
  }
  $opts = parameters_explode( $opts, array( 'set' => array(
    'filename' => we('people','personen')
  , 'orderby' => 'cn'
  ) ) );
  if( ( $search_filter = adefault( $opts, 'search_filter' ) ) ) {
    if( isnumber( $search_filter ) ) {
      $search_filter = 'P2_SEARCH';
    }
  }

  $list_options = handle_list_options( $opts, 'people', array(
      'cn' => array( 's' => "CONCAT( sn, ' ', gn )" )
    , 'primary_roomnumber' => 's,t=1,h='.we('room','Raum')
    , 'primary_telephonenumber' => 's,t=1,h='.we('phone','Telefon')
    , 'primary_mail' => 's,t=1,h='.we('mail','Email')
    , 'groups' => 's=primary_groupname,t=0,h='.we('group','Gruppe')
  ) );

  if( ! ( $people = sql_people( $filters, array( 'more_selects' => 'affiliations_groups_ids', 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no persons found','Keine Personen gefunden') );
    return;
  }
  $count = count( $people );
  // $limits = handle_list_limits( $opts, $count );
  $list_options['limits'] = false;

  $select = adefault( $opts, 'select' );
  if( $select ) {
    if( isnumber( $select ) ) {
      $select = $list_options['list_id'].'_selected_id';
    }
    if( ! isset( $GLOBALS[ $select ] ) ) {
      init_var( $select, 'global=1,sources=http persistent,type=u,set_scopes=self' );
    }
  }
  $insert = ( $global_format == 'html' ? adefault( $opts, 'insert' ) : false );
  $selected_people_id = ( $select ? $GLOBALS[ $select ] : 0 );

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'cn', 'Name' );
      open_list_cell( 'primary_roomnumber', we('room','Raum') );
      open_list_cell( 'primary_telephonenumber', we('phone','Telefon') );
      open_list_cell( 'primary_mail', 'Email' );
      open_list_cell( 'groups', we('groups','Arbeitsgruppen') );
      $colspan = $GLOBALS['current_list']['col_number'];
    foreach( $people as $person ) {
      $people_id = $person['people_id'];
      if( $selected_people_id == $people_id ) {
        open_list_row( array( 'class' => 'selected', 'onclick' => inlink( '!', "context=js,$select=0" ) ) );
        if( $insert ) {
          open_list_cell( 'cn', person_visitenkarte_view( $person ), "colspan=$colspan" );
          continue;
        }
      } else {
        open_list_row( $select ? array( 'onclick' => inlink( '!', "context=js,$select=$people_id" ) ) : '' );
      }

        $glinks = '';
        if( $list_options['cols']['groups']['toggle'] ) {
          // foreach( sql_affiliations( "people_id=$people_id,groups.flag_publish" ) as $a ) {
          $ids = explode( ',', $person['affiliations_groups_ids'] );
          foreach( $ids as $g_id ) {
            $glinks .= ' '. alink_group_view( $g_id, array( 'class' => 'href inlink quadr', 'default' => NULL, 'fullname' => 1 ) );
          }
        }
        if( $insert ) {
          open_list_cell( 'cn', inlink( '!', array( 'class' => 'href inlink', $select => $people_id, 'text' => $person['cn'] ) ) );
        } else {
          open_list_cell( 'cn', inlink( 'visitenkarte', array( 'class' => 'href inlink', 'people_id' => $people_id, 'text' => $person['cn'] ) ) );
        }
        if( $person['primary_roomnumber'] ) {
          if( $search_filter ) {
            $r = inlink( '!', array( 'text' => $person['primary_roomnumber'], $search_filter => ';'.str_replace( '.', '\\.', $person['primary_roomnumber'] ).';' ) );
          } else {
            $r = $person['primary_roomnumber'];
          }
        } else {
          $r = ' - ';
        }
        open_list_cell( 'primary_roomnumber', $r );
        if( $person['primary_telephonenumber'] ) {
          if( $search_filter ) {
            $r = inlink( '!', array( 'text' => $person['primary_telephonenumber'], $search_filter => ';'.str_replace( '+', '\\+', $person['primary_telephonenumber'] ).';' ) );
          } else {
            $r = $person['primary_telephonenumber'];
          }
        } else {
          $r = ' - ';
        }
        open_list_cell( 'primary_telephonenumber', $r );
        $t = $person['primary_mail'];
        if( $global_format === 'html' ) {
          $t = html_obfuscate_email( $person['primary_mail'] );
        } else if( ! $client_is_intranet ) {
          $t = '(suppressed - intranet only)';
        }
        open_list_cell( 'primary_mail', $t, '' );
        open_list_cell( 'groups', $glinks );
    }
  close_list();

}

function groupslist_view( $filters_in = array(), $opts = array() ) {

  $filters = array( '&&', 'flag_publish' );
  if( $filters_in ) {
    $filters[] = $filters_in;
  }

  $filters = restrict_view_filters( $filters_in, 'groups' );

  $opts = parameters_explode( $opts, 'set=filename='.we('groups','gruppen') );
  $list_options = handle_list_options( $opts, 'groups', array(
      'cn' => 's,h='.we('name','Name')
    , 'head' => 's=head_sn,t=1,h='.we('head','Leiter')
    , 'secretary' => 's=secretary_sn,t=1,h='.we('secretary','Sekretatiat')
  ) );

  if( ! ( $groups = sql_groups( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no such groups','Keine Gruppen vorhanden') );
    return;
  }
  $count = count( $groups );

  // probably don't need limits here for the time being:
  //
  // $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = false;

  // $selected_groups_id = adefault( $GLOBALS, $opts['select'], 0 );
  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'cn', we('Name of group','Name der Gruppe') );
      open_list_cell( 'head', we('group leader','Gruppenleiter') );
      open_list_cell( 'secretary', we('secretary','Sekretariat') );
    foreach( $groups as $g ) {
      $groups_id = $g['groups_id'];
      open_list_row();
        open_list_cell( 'cn', alink_group_view( $groups_id, 'fullname=1' ) );
        open_list_cell( 'head', ( $g['head_people_id'] ? alink_person_view( $g['head_people_id'] ) : '' ) );
        open_list_cell( 'secretary', ( $g['secretary_people_id'] ? alink_person_view( $g['secretary_people_id'] ) : '' ) );

    }
  close_list();
}

function moduleslist_view( $filters_in = array(), $opts = array() ) {

  if( $filters_in ) {
    $filters[] = $filters_in;
  }

  $opts = parameters_explode( $opts, 'set=filename='.we('modules','module') );
  $list_options = handle_list_options( $opts, 'groups', array(
      'tag' => 's,h='.we('module','Modul')
    , 'cn' => 's,t=1,h='.we('title','Titel')
    , 'programme' => 's,t=1,h='.we('programme','Studiengang')
    , 'contact' => 's=contact_people_cn,t=1,h='.we('contact','verantwortliche Person')
  ) );

  if( ! ( $modules = sql_modules( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no modules','Keine Module vorhanden') );
    return;
  }
  $count = count( $modules );

  // probably don't need limits here for the time being:
  //
  // $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = false;

  // $selected_groups_id = adefault( $GLOBALS, $opts['select'], 0 );
  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'tag' );
      open_list_cell( 'cn' );
      open_list_cell( 'programme' );
      open_list_cell( 'contact' );
    foreach( $modules as $m ) {
      $modules_id = $g['modules_id'];
      open_list_row();
        open_list_cell( 'tag', $m['tag'] );
        open_list_cell( 'cn', $m['cn'] );
        open_list_cell( 'programme', programme_cn_view( $m['programme_flags'] ) );
        open_list_cell( 'contact', alink_person_view( $m['contact_people_id'], 'office' ) );
    }
  close_list();
}

function positionslist_view( $filters_in = array(), $opts = array() ) {
  global $global_format;

  $filters = restrict_view_filters( $filters_in, 'positions' );
  $opts = parameters_explode( $opts, 'set=filename='.we('topics','themen') );
  $list_options = handle_list_options( $opts, 'positions', array(
      'id' => 's=positions_id,t=1'
//    , 'nr' => 't=1'
    , 'cn' => 's,h='.we('title','Titel')
    , 'group' => 's=acronym,t=1,h='.we('group','Gruppe')
    , 'programme' => 's=programme_flags,t=1,h='.we('degree course','Studiengang/Abschluss')
    , 'url' => 's,t=1'
  ) );
  if( adefault( $filters_in, 'groups_id' ) ) {
    $list_options['cols']['group']['toggle'] = 'off';
  }

  if( ( $themen = adefault( $opts, 'rows', NULL ) ) === NULL ) {
    $themen = sql_positions( $filters, array( 'orderby' => $list_options['orderby_sql'] ) );
  }
  if( ! $themen ) {
    open_div( '', we('no topics found', 'Keine Themen gefunden' ) );
    return;
  }
  $count = count( $themen );

  $list_options['limits'] = false;

  $select = adefault( $opts, 'select' );
  if( $select ) {
    if( isnumber( $select ) ) {
      $select = $list_options['list_id'].'_selected_id';
    }
    if( ! isset( $GLOBALS[ $select ] ) ) {
      init_var( $select, 'global=1,sources=http persistent,type=u,set_scopes=self' );
    }
  }
  $insert = ( $global_format == 'html' ? adefault( $opts, 'insert' ) : false );
  $selected_positions_id = ( $select ? $GLOBALS[ $select ] : 0 );

  open_list( $list_options );
    open_list_row('header');
//      open_list_cell( 'nr' );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
        open_list_cell( 'id' );
      }
      open_list_cell( 'cn', we('topic','Thema') );
      open_list_cell( 'group', we('group','Arbeitsgruppe') );
      open_list_cell( 'programme' );
      open_list_cell( 'URL' );
      $colspan = $GLOBALS['current_list']['col_number'];
    foreach( $themen as $t ) {
      $positions_id = $t['positions_id'];
      if( $selected_positions_id == $positions_id ) {
        open_list_row( array( 'class' => 'selected', 'onclick' => inlink( '', "context=js,$select=0" ) ) );
        if( $insert ) {
          open_list_cell( 'cn', position_view( $t ), "colspan=$colspan" );
          continue;
        }
      } else {
        open_list_row( $select ? array( 'onclick' => inlink( '', "context=js,$select=$positions_id" ) ) : '' );
      }
        // open_list_cell( 'nr', $t['nr'], 'right' );
        if( $insert ) {
          open_list_cell( 'cn', inlink( '!', array( 'class' => 'href', 'text' => $t['cn'], $select => $positions_id ) ) );
        } else {
          open_list_cell( 'cn', inlink( 'position_view', array( 'class' => 'href', 'text' => $t['cn'], 'positions_id' => $positions_id ) ) );
        }
        open_list_cell( 'group', ( $t['groups_id'] ? alink_group_view( $t['groups_id'], 'fullname=1' ) : ' - ' ) );
        open_list_cell( 'programme', programme_cn_view( $t['programme_flags'], 'short=1' ) );
        open_list_cell( 'url', $t['url'], 'url' );
    }
  close_list();
}


function publicationslist_view( $filters = array(), $opts = array() ) {

  $filters = restrict_view_filters( $filters, 'publications' );

  $opts = parameters_explode( $opts, 'set=filename='.we('publications','publikationen') );
  $list_options = handle_list_options( $opts, 'publications', array(
      'id' => 's=publications_id,t=1'
    , 'nr' => 't=1'
    , 'title' => 's,t=1,h='.we('title','Titel')
    , 'year' => 's,t=1,h='.we('year of publication','Erscheinungsjahr')
    , 'group' => 's=acronym,t=1,h='.we('group','Gruppe')
    , 'authors' => 's,t=1,h='.we('authors','Autoren')
    , 'journal' => 's,t=1,h='.we('journal','Journal')
    , 'journal_url' => 's,t=1'
  ) );

  if( ! ( $publications = sql_publications( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no publications found', 'Keine Veröffentlichungen gefunden' ) );
    return;
  }
  $count = count( $publications );
  // $limits = handle_list_limits( $list_options, $count );
  // $list_options['limits'] = & $limits;
  $list_options['limits'] = false;

  // $selected_publications_id = adefault( $GLOBALS, $opts['select'], 0 );

  open_list( $list_options );
    open_list_row('header');
    open_list_cell( 'nr' );
    if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
      open_list_cell( 'id' );
    open_list_cell( 'title', we('title','Titel') );
    open_list_cell( 'authors' );
    open_list_cell( 'journal' );
    open_list_cell( 'group' );
    open_list_cell( 'URL' );
    foreach( $publications as $p ) {
      $publications_id = $p['publications_id'];
      open_list_row();
        open_list_cell( 'nr', $p['nr'], 'right' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', inlink( 'publikation', array( 'text' => $publications_id, 'publications_id' => $publications_id ) ), 'class=number' );
        }
        open_list_cell( 'title', inlink( 'publikation', array( 'text' => $p['title'], 'publications_id' => $publications_id ) ) );
        open_list_cell( 'authors', $p['authors'] );
        open_list_cell( 'journal', $p['journal'] );
        open_list_cell( 'group', ( $p['groups_id'] ? alink_group_view( $p['groups_id'], 'fullname=1' ) : ' - ' ) );
        open_list_cell( 'journal_url', $p['journal_url'], 'url' );
    }
  close_list();
}

require_once('shared/views.php');

?>
