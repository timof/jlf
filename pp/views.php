<?php

require_once('code/views.php');

function address_view() {
  open_tag( 'address' );
    echo html_tag( 'p', 'header', we('Contact','Kontakt') );
    echo html_tag( 'p', '', we('University of Potsdam','Universität Potsdam') );
    echo html_tag( 'p', '', we('Institute of Physics and Astronomy','Institut für Physik und Astronomie') );
    echo html_tag( 'p', '', 'Karl-Liebknecht-Straße 24/25' );
    echo html_tag( 'p', '', '14476 Potsdam-Golm' );
  close_tag( 'address' );
}

function peoplelist_view( $filters_in = array(), $opts = array() ) {
  global $global_format;

  $filters = array( '&&', 'flag_institute', 'flag_deleted=0', 'flag_virtual=0', 'groups.flags &= '.GROUPS_FLAG_LIST );
  if( $filters_in ) {
    $filters[] = $filters_in;
  }
  $opts = parameters_explode( $opts );
  $regex_filter = adefault( $opts, 'regex_filter' );

  $list_options = handle_list_options( adefault( $opts, 'list_options', true ), 'people', array(
      'cn' => array( 's' => "CONCAT( sn, ' ', gn )" )
    , 'primary_roomnumber' => 's,t=0,h='.we('room','Raum')
    , 'primary_telephonenumber' => 's,t=1,h='.we('phone','Telefon')
    , 'primary_mail' => 's,t=0,h='.we('mail','Email')
    , 'groups' => 's=primary_groupname,t=0,h='.we('group','Gruppe')
  ) );

  if( ! ( $people = sql_people( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no persons found','Keine Personen gefunden') );
    return;
  }
  $count = count( $people );
  // $limits = handle_list_limits( $opts, $count );
  $list_options['limits'] = false;

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'cn', 'Name' );
      open_list_cell( 'primary_roomnumber', we('room','Raum') );
      open_list_cell( 'primary_telephonenumber', we('phone','Telefon') );
      open_list_cell( 'primary_mail', 'Email' );
      open_list_cell( 'groups', we('groups','Arbeitsgruppen') );

    foreach( $people as $person ) {
      $people_id = $person['people_id'];

      $glinks = '';
      if( ( $ids = $person['affiliations_groups_ids'] ) ) foreach( explode( ',', $ids ) as $g_id ) {
        $glinks .= ' '. alink_group_view( $g_id, 'class=href quads,fullname=1' );
      }

      open_list_row();
        open_list_cell( 'cn',
        inlink( 'visitenkarte', array( 'class' => 'href inlink', 'people_id' => $people_id, 'text' => $person['cn'] ) ) );
        if( $person['primary_roomnumber'] )
          if( $regex_filter ) {
            $r = inlink( '!', array( 'text' => $person['primary_roomnumber'], 'REGEX' => ';'.str_replace( '.', '\\.', $person['primary_roomnumber'] ).';' ) );
          } else {
            $r = $person['primary_roomnumber'];
          }
        else
          $r = ' - ';
        open_list_cell( 'primary_roomnumber', $r );
        if( $person['primary_telephonenumber'] )
          if( $regex_filter ) {
            $r = inlink( '!', array( 'text' => $person['primary_telephonenumber'], 'REGEX' => ';'.str_replace( '+', '\\+', $person['primary_telephonenumber'] ).';' ) );
          } else {
            $r = $person['primary_telephonenumber'];
          }
        else
          $r = ' - ';
        open_list_cell( 'primary_telephonenumber', $r );
        $t = $person['primary_mail'];
        if( $global_format === 'html' ) {
          $t = html_obfuscate_email( $person['primary_mail'] );
        }
        open_list_cell( 'primary_mail', $t, '' );
        open_list_cell( 'groups', $glinks );
    }
  close_list();

}

function groupslist_view( $filters_in = array(), $opts = array() ) {

  $filters = array( '&&', 'flags &= '.GROUPS_FLAG_LIST );
  if( $filters_in ) {
    $filters[] = $filters_in;
  }

  $filters = restrict_view_filters( $filters, 'groups' );

  $list_options = handle_list_options( $opts, 'groups', array(
      'cn' => 's,t=1,h='.we('name','Name')
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

  $selected_groups_id = adefault( $GLOBALS, $opts['select'], 0 );
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

function positionslist_view( $filters_in = array(), $opts = array() ) {
  global $global_format;

  $filters = array( '&&', 'flags &= '.GROUPS_FLAG_LIST );
  if( $filters_in ) {
    $filters[] = $filters_in;
  }
  $opts = parameters_explode( $opts );

  $list_options = handle_list_options( adefault( $opts, 'list_options', true ), 'positions', array(
      'id' => 's=positions_id,t=1'
    , 'nr' => 't=1'
    , 'cn' => 's,t=1,h='.we('title','Titel')
    , 'group' => 's=acronym,t=1,h='.we('group','Gruppe')
    , 'programme' => 's=programme_cn,t=1,h='.we('programme/degree','Studiengang/Abschluss')
    , 'url' => 's,t=1'
  ) );
  if( adefault( $filters_in, 'groups_id' ) ) {
    $list_options['cols']['group']['toggle'] = 'off';
  }

  if( ( $themen = adefault( $opts, 'rows', NULL ) ) === NULL ) {
    $themen = sql_positions( $filters, array( 'orderby' => $list_options['orderby_sql'] ) );
  }
  if( ! $themen ) {
    open_div( '', we('no such posisions/topics', 'Keine Stellen/Themen vorhanden' ) );
    return;
  }
  $count = count( $themen );

  // $limits = handle_list_limits( $list_optionsopts, $count );
  // $list_options['limits'] = & $limits;
  $list_options['limits'] = false;

  // $selected_positions_id = adefault( $GLOBALS, $opts['select'], 0 );
  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
        open_list_cell( 'id' );
      open_list_cell( 'cn', we('topic','Thema') );
      open_list_cell( 'group', we('group','Arbeitsgruppe') );
      open_list_cell( 'programme', we('degree','Abschluss') );
      open_list_cell( 'URL' );
    foreach( $themen as $t ) {
      $positions_id = $t['positions_id'];
      open_list_row();
        open_list_cell( 'nr', $t['nr'], 'right' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
          open_list_cell( 'id', inlink( 'position_view', array( 'class' => 'href', 'text' => $positions_id, 'positions_id' => $positions_id ) ), 'class=number' );
        open_list_cell( 'cn', inlink( 'position_view', array( 'class' => 'href', 'text' => $t['cn'], 'positions_id' => $positions_id ) ) );
        open_list_cell( 'group', ( $t['groups_id'] ? alink_group_view( $t['groups_id'], 'fullname=1' ) : ' - ' ) );
          $s = '';
          $comma = '';
          foreach( $GLOBALS['programme_text'] as $programme_id => $programme_cn ) {
            if( $t['programme_id'] & $programme_id ) {
              $s .= $comma . $programme_cn;
              $comma = ', ';
            }
          }
        open_list_cell( 'programme', $s );
        open_list_cell( 'url', $t['url'], 'url' );
    }
  close_list();
}


function publicationslist_view( $filters = array(), $opts = array() ) {

  $filters = restrict_view_filters( $filters, 'publications' );

  $list_options = handle_list_options( adefault( $opts, 'list_options', true ), 'publications', array(
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
