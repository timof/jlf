<?php // pi/views.php

require_once('code/views.php');

function submenu_lehre_view( $opts = array() ) {
  global $logged_in;

  $menu = array();
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    $menu[] = array( 'script' => 'examslist'
    , 'title' => we('Exam dates','Prüfungstermine')
    , 'text' => we('Exam dates','Prüfungstermine')
    , 'inactive' => true
    );
  }

  $menu[] = array( 'script' => 'teachinglist'
  , 'title' => we('Teaching','Lehrerfassung')
  , 'text' => we('Teaching','Lehrerfassung')
  , 'inactive' => ( $logged_in ? false : we('please login first','bitte erst Anmelden') )
  );

  $menu[] = array( 'script' => 'positionslist'
  , 'title' => we('Thesis Topics','Themen Ba/Ma-Arbeiten')
  , 'text' => we('Thesis Topics','Themen Ba/Ma-Arbeiten')
  );

  $menu[] = array( 'script' => 'moduleslist'
  , 'title' => we('Modules','Module')
  , 'text' => we('Modules and contact persons','Module und Modulverantwortliche')
  );


  return menu_view( $menu, $opts );
}

function mainmenu_view( $opts = array() ) {
  global $logged_in;

  $menu = array();
  $menu[] = array( 'script' => 'peoplelist'
  , 'title' => we('People','Personen')
  , 'text' => we('People','Personen')
  );

  $menu[] = array( 'script' => 'groupslist'
  , 'title' => we('Groups','Gruppen')
  , 'text' => we('Groups','Gruppen')
  );

  if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    $menu[] = array( 'script' => 'configuration'
    , 'title' => we('Configuration','Konfiguration')
    , 'text' => we('Configuration','Konfiguration')
    );
  }

  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    $menu[] = array( 'script' => 'eventslist'
    , 'title' => we('Events','Veranstaltungen')
    , 'text' => we('Events','Veranstaltungen' )
    );
    $menu[] = array( 'script' => 'surveyslist'
    , 'title' => we('Surveys','Umfragen')
    , 'text' => we('Surveys','Umfragen')
    , 'inactive' => true
    );

    $menu[] = array( 'script' => 'menu'
    , 'title' => we('Teaching...','Lehre...')
    , 'text' => we('Teaching...','Lehre...')
    , 'window' => 'submenu_lehre'
    );

    if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
      $menu[] = array( 'script' => 'menu'
      , 'title' => we('Admin...','Admin...')
      , 'text' => we('Admin...','Admin...')
      , 'window' => 'submenu_root'
      );
    }

  } else {
    $menu[] = array( 'script' => 'teachinglist'
    , 'title' => we('Teaching','Lehrerfassung')
    , 'text' => we('Teaching','Lehrerfassung')
    , 'inactive' => ( $logged_in ? false : we('please login first','bitte erst Anmelden') )
    );

    $menu[] = array( 'script' => 'positionslist'
    , 'title' => we('Thesis Topics','Themen Ba/Ma-Arbeiten')
    , 'text' => we('Thesis Topics','Themen Ba/Ma-Arbeiten')
    );

  }

  $menu[] = array( 'script' => 'publicationslist'
  , 'title' => we('Publications','Publikationen')
  , 'text' => we('Publications','Publikationen')
  );

  $menu[] = array( 'script' => 'documentslist'
  , 'title' => we('Documents','Dateien')
  , 'text' => we('Documents','Dateien')
  );

  $menu[] = array( 'script' => 'roomslist'
  , 'title' => we('Labs','Labore')
  , 'text' => we('Labs','Labore')
  );



  if( $logged_in ) {
    $menu[] = array( 'script' => ''
    , 'title' => we('Logout', 'Abmelden')
    , 'text' => we('Logout', 'Abmelden')
    , 'login' => 'logout'
    );
  } else {
    $menu[] = array( 'script' => ''
    , 'text' => we('Login', 'Anmelden')
    , 'title' => we('Login', 'Anmelden')
    , 'class' => 'big button'
    , 'login' => 'login'
    );
  }
  return menu_view( $menu, $opts );
}

//   if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
//       $mainmenu[] = array( 'script' => 'maintenance'
//       , 'title' => 'Maintenance'
//       , 'text' => 'Maintenance'
//       );
//       $mainmenu[] = array( 'script' => 'sessions'
//       , 'title' => 'Sessions'
//       , 'text' => 'Sessions'
//       );
//       $mainmenu[] = array( 'script' => 'logbook'
//       , 'title' => we('Logbook','Logbuch')
//       , 'text' => we('Logbook','Logbuch')
//       );
//       $mainmenu[] = array( 'script' => 'anylist'
//       , 'title' => we('Tables','Tabellen')
//       , 'text' => we('Tables','Tabellen')
//       );
//       $mainmenu[] = array( 'script' => 'profile'
//       , 'title' => we('Profiler','Profiler')
//       , 'text' => we('Profiler','Profiler')
//       );
//   }

  // open_tr('medskip');

// function mainmenu_header() {
//   global $mainmenu;
//   foreach( $mainmenu as $h ) {
//     open_li( '', '', inlink( $h['script'], array(
//       'text' => $h['text'], 'title' => $h['title'] , 'class' => 'href'
//     ) ) );
//   }
// }


function window_title() {
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    return $GLOBALS['jlf_application_name'].' '.$GLOBALS['jlf_application_instance'].' '.$GLOBALS['window'].' / '.$GLOBALS['thread'].' / '.$GLOBALS['login_sessions_id'];
  } else {
    return $GLOBALS['window'] . ' / ' . $GLOBALS['thread'];
  }
}


// people:
//

function peoplelist_view( $filters = array(), $opts = array() ) {
  global $choices_person_status;

  $filters = restrict_view_filters( $filters, 'people' );
  $opts = parameters_explode( $opts, 'set=filename='.we('people','personen') );
  $regex_filter = adefault( $opts, 'regex_filter' );

  $list_options = handle_list_options( $opts, 'people', array(
      'id' => 's=people_id,h=id,t='.( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ? 1 : 'off' )
    , 'nr' => 't=1'
    , 'gn' => 's,t,h='.we('first names','Vornamen')
    , 'sn' => 's,t,h='.we('last name','Nachname')
    , 'title' => 's,t,h='.we('title','Titel')
    , 'jperson' => 's,t'
    , 'flag_deleted' => 't,s'
    , 'flag_virtual' => 't,s'
    , 'flag_publish' => 't,s'
    , 'status' => 's,t=1,h='.we('status','Status')
    , 'typeofposition' => 't=0,s'
    , 'teaching_obligation' => 't=0,s'
    , 'teaching_reduction' => 't=0,s'
    , 'uid' => 's,t'
    , 'url' => 's,t=0'
    , 'auth' => 's=authentication_methods,t=0'
    , 'primary_roomnumber' => 's,t,h='.we('room','Raum')
    , 'primary_telephonenumber' => 's,t,h='.we('phone','Telefon')
    , 'primary_mail' => 's,t,h='.we('mail','Email')
    , 'groups' => 's=primary_groupname,t,h='.we('group','Gruppe')
  ) );

  if( ! ( $people = sql_people( $filters, array( 'orderby' => $list_options['orderby_sql'], 'more_selects' => 'teaching_obligation,teaching_reduction' ) ) ) ) {
    open_div( '', we('no such people','Keine Personen vorhanden') );
    return;
  }
  $count = count( $people );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  // $selected_people_id = adefault( $GLOBALS, $opts['select'], 0 );

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
        open_list_cell( 'id' );
        open_list_cell( 'uid' );
        open_list_cell( 'auth' );
        open_list_cell( 'flag_virtual' );
      }
      open_list_cell( 'flag_deleted' );
      open_list_cell( 'flag_publish' );
      open_list_cell( 'status' );
      open_list_cell( 'typeofposition', we('position','Stelle') );
//      if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
        open_list_cell( 'teaching_obligation', we('teaching','Lehrverpflichtung') );
        open_list_cell( 'teaching_reduction', we('reduction','Reduktion') );
//      }
      open_list_cell( 'title', we('title','Titel') );
      open_list_cell( 'gn', we('first names','Vornamen') );
      open_list_cell( 'sn', we('last name','Nachname') );
      open_list_cell( 'url', we('web page','Webseite') );
      open_list_cell( 'primary_roomnumber', we('room','Raum') );
      open_list_cell( 'primary_telephonenumber', we('phone','Telefon') );
      open_list_cell( 'primary_mail', 'Email' );
      open_list_cell( 'groups', we('groups','Arbeitsgruppen') );

    foreach( $people as $person ) {
      if( $person['nr'] < $limits['limit_from'] )
        continue;
      if( $person['nr'] > $limits['limit_to'] )
        break;
      $people_id = $person['people_id'];

      $glinks = '';
      foreach( sql_affiliations( "people_id=$people_id" ) as $a ) {
        if( ! ( $g_id = $a['groups_id'] ) ) {
          continue;
        }
        $glinks .= ' '. alink_group_view( $g_id, 'href inlink quadr' );
      }

      open_list_row();
        open_list_cell( 'nr', inlink( 'person_view', array( 'class' => 'href inlink', 'people_id' => $people_id, 'text' => $person['nr'] ) ), 'number' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', any_link( 'people', $people_id, "text=$people_id" ), 'number' );
          open_list_cell( 'uid', $person['uid'] );
          open_list_cell( 'auth', $person['authentication_methods'] );
          open_list_cell( 'flag_virtual', $person['flag_virtual'] );
        }
        open_list_cell( 'flag_deleted', $person['flag_deleted'] );
        open_list_cell( 'flag_publish', $person['flag_publish'] );
        open_list_cell( 'status', adefault( $choices_person_status, $person['status'], we('(not set)','(nicht gesetzt)' ) ) );
        open_list_cell( 'typeofposition', $person['typeofposition'] );
//        if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
          open_list_cell( 'teaching_obligation', $person['teaching_obligation'] );
          open_list_cell( 'teaching_reduction', $person['teaching_reduction'] );
//        }
        open_list_cell( 'title', $person['title'] );
        open_list_cell( 'gn', $person['gn'] );
        open_list_cell( 'sn', inlink( 'person_view', array( 'class' => 'href inlink', 'people_id' => $people_id, 'text' => $person['sn'] ) ) );
        open_list_cell( 'url', url_view( $person['url'] ) );
        if( $person['primary_roomnumber'] ) {
          if( $regex_filter ) {
            $r = inlink( '', array( 'text' => $person['primary_roomnumber'], 'REGEX' => ';'.str_replace( '.', '\\.', $person['primary_roomnumber'] ).';' ) );
          } else {
            $r = $person['primary_roomnumber'];
          }
        } else {
          $r = ' - ';
        }
        open_list_cell( 'primary_roomnumber', $r, 'oneline' );
        if( $person['primary_telephonenumber'] ) {
          if( $regex_filter ) {
            $r = inlink( '', array( 'text' => $person['primary_telephonenumber'], 'REGEX' => ';'.str_replace( '+', '\\+', $person['primary_telephonenumber'] ).';' ) );
          } else {
            $r = $person['primary_telephonenumber'];
          }
        } else {
          $r = ' - ';
        }
        open_list_cell( 'primary_telephonenumber', $r, 'oneline' );
        open_list_cell( 'primary_mail', $person['primary_mail'], 'oneline' );
        open_list_cell( 'groups', $glinks );
    }
  close_list();
}


function groupslist_view( $filters = array(), $opts = array() ) {
  global $choices_group_status, $oUML;

  $filters = restrict_view_filters( $filters, 'groups' );

  $opts = parameters_explode( $opts, 'set=filename='.we('groups','gruppen') );
  $list_options = handle_list_options( $opts, 'groups', array(
      'id' => 's=groups_id,t='.( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ? 1 : 'off' )
    , 'nr' => 't=1'
    , 'cn' => 's,t=1,h='.we('name','Name')
    , 'acronym' => 's,t=1,h='.we('acronym','Kurzname')
    , 'keyarea' => 's,t,h=key area'
    , 'status' => 's,t=1,h='.we('status','Status')
    , 'research' => 's=flag_research,t,h='.we('research',"Forschung")
    , 'publish' => 's=flag_publish,t,h='.we('publish',"{$oUML}ffentlich")
    , 'institute' => 's=flag_institute,t,h='.we('institute',"Institut")
    , 'head' => 's=head_sn,t=1,h='.we('head','Leiter')
    , 'secretary' => 's=secretary_sn,t=1,h='.we('secretary','Sekretatiat')
    , 'url' => 's,t=1'
  ) );

  if( ! ( $groups = sql_groups( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no such groups','Keine Gruppen vorhanden') );
    return;
  }
  $count = count( $groups );

  // probably don't need limits here for the time being:
  //
  // $limits = handle_list_limits( $opts, $count );
  $list_options['limits'] = false;

  // $selected_groups_id = adefault( $GLOBALS, $opts['select'], 0 );
  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
        open_list_cell( 'id' );
      }
      open_list_cell( 'acronym' );
      open_list_cell( 'keyarea' );
      open_list_cell( 'cn', we('Name of group','Name der Gruppe') );
      open_list_cell( 'status' );
      open_list_cell( 'research' );
      open_list_cell( 'publish' );
      open_list_cell( 'institute' );
      open_list_cell( 'head', we('head','Gruppenleiter') );
      open_list_cell( 'secretary', we('secretary','Sekretariat') );
      open_list_cell( 'URL' );
    foreach( $groups as $g ) {
      $groups_id = $g['groups_id'];
      open_list_row();
        open_list_cell( 'nr', alink_group_view( $groups_id, array( 'text' => $g['nr'], 'class' => 'href inlink' ) ), 'number' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', any_link( 'groups', $groups_id, "text=$groups_id" ), 'number' );
        }
        open_list_cell( 'acronym', alink_group_view( $groups_id ) );
        open_list_cell( 'keyarea', $g['keyarea'] );
        open_list_cell( 'cn', $g['cn'], 'oneline' );
        open_list_cell( 'status', adefault( $choices_group_status, $g['status'], we('(not set)','(nicht gesetzt)' ) ) );
        open_list_cell( 'research', $g['flag_research'] );
        open_list_cell( 'publish', $g['flag_publish'] );
        open_list_cell( 'institute', $g['flag_institute'] );
        open_list_cell( 'head', ( $g['head_people_id'] ? alink_person_view( $g['head_people_id'] ) : '' ) );
        open_list_cell( 'secretary', ( $g['secretary_people_id'] ? alink_person_view( $g['secretary_people_id'] ) : '' ) );
        open_list_cell( 'url', url_view( $g['url'] ) );
    }
  close_list();
}


function positionslist_view( $filters = array(), $opts = array() ) {
  $filters = restrict_view_filters( $filters, 'positions' );

  $opts = parameters_explode( $opts, 'set=filename='.we('topics','themen') );
  $list_options = handle_list_options( $opts, 'positions', array(
      'id' => 's=positions_id,t=1'
    , 'nr' => 't=1'
    , 'cn' => 's,t=1,h='.we('title','Titel')
    , 'group' => 's=acronym,t=1,h='.we('group','Gruppe')
    , 'programme_flags' => 's,t=1,h='.we('programme','Studiengang')
    , 'url' => 's,t=1'
  ) );

  if( ! ( $themen = sql_positions( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no such posisions/topics', 'Keine Stellen/Themen vorhanden' ) );
    return;
  }
  $count = count( $themen );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  // $selected_positions_id = adefault( $GLOBALS, $opts['select'], 0 );

  open_list( $list_options );
    open_list_row('header');
    open_list_cell( 'nr' );
    if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
      open_list_cell( 'id' );
    open_list_cell( 'cn', we('topic','Thema') );
    open_list_cell( 'group', we('group','Arbeitsgruppe') );
    open_list_cell( 'programme_flags' );
    open_list_cell( 'URL' );
    foreach( $themen as $t ) {
      $positions_id = $t['positions_id'];
      open_list_row();
        open_list_cell( 'nr', $t['nr'], 'right' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', any_link( 'positions', $positions_id, "text=$positions_id" ), 'number' );
        }
        open_list_cell( 'cn', inlink( 'position_view', array( 'text' => $t['cn'], 'positions_id' => $positions_id ) ) );
        open_list_cell( 'group', ( $t['groups_id'] ? alink_group_view( $t['groups_id'] ) : ' - ' ) );
        open_list_cell( 'programme_flags', programme_cn_view( $t['programme_flags'], 'short=1' ) );
        open_list_cell( 'url', url_view( $t['url'] ) );
    }

  close_list();
}


function publicationslist_view( $filters = array(), $opts = array() ) {

  $filters = restrict_view_filters( $filters, 'publications' );

  $opts = parameters_explode( $opts, 'set=filename='.we('publications','publikationen') );
  $list_options = handle_list_options( $opts, 'publications', array(
      'id' => 's=publications_id,t=1'
    , 'nr' => 't=1'
    , 'cn' => 's,t=1,h='.we('short title','Kurztitel')
    , 'title' => 's,t=0,h='.we('full title','Langtitel')
    , 'authors' => 's,t=1,h='.we('authors','Autoren')
    , 'journal' => 's,t=1,h='.we('journal','Journal')
    , 'volume' => 's,t=1,h='.we('volume','Band')
    , 'page' => 's,t=1,h='.we('page','Seite')
    , 'year' => 's,t=1,h='.we('year','Jahr')
    , 'group' => 's=acronym,t=1,h='.we('group','Gruppe')
    , 'info_url' => 's,t=1'
    , 'journal_url' => 's,t=1'
  ) );

  if( ! ( $publications = sql_publications( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no publications found', 'Keine Veröffentlichungen gefunden' ) );
    return;
  }
  $count = count( $publications );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  // $selected_publications_id = adefault( $GLOBALS, $opts['select'], 0 );

  open_list( $list_options );
    open_list_row('header');
    open_list_cell( 'nr' );
    if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
      open_list_cell( 'id' );
    }
    open_list_cell( 'cn' );
    open_list_cell( 'title', we('title','Titel') );
    open_list_cell( 'authors' );
    open_list_cell( 'journal' );
    open_list_cell( 'volume' );
    open_list_cell( 'page' );
    open_list_cell( 'year' );
    open_list_cell( 'journal_url', 'acticle link' );
    open_list_cell( 'group' );
    open_list_cell( 'info_url', 'information link' );
    foreach( $publications as $p ) {
      $publications_id = $p['publications_id'];
      open_list_row();
        open_list_cell( 'nr', $p['nr'], 'right' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', any_link( 'publications', $publications_id, "text=$publications_id" ), 'number' );
        }
        open_list_cell( 'cn', inlink( 'publication_view', array( 'text' => $p['cn'], 'publications_id' => $publications_id ) ) );
        open_list_cell( 'title', inlink( 'publication_view', array( 'text' => $p['title'], 'publications_id' => $publications_id ) ) );
        open_list_cell( 'authors', $p['authors'] );
        open_list_cell( 'journal', $p['journal'] );
        open_list_cell( 'volume', $p['volume'] );
        open_list_cell( 'page', $p['page'] );
        open_list_cell( 'year', $p['year'] );
        open_list_cell( 'journal_url', url_view( $p['journal_url'] ) );
        open_list_cell( 'group', ( $p['groups_id'] ? alink_group_view( $p['groups_id'] ) : ' - ' ) );
        open_list_cell( 'info_url', url_view( $p['info_url'] ) );
    }
  close_list();
}

function eventslist_view( $filters = array(), $opts = array() ) {

  $filters = restrict_view_filters( $filters, 'events' );

  $opts = parameters_explode( $opts, 'set=filename='.we('events','veranstaltungen') );
  $list_options = handle_list_options( $opts, 'publications', array(
      'id' => 's=events_id,t=1'
    , 'nr' => 't=1'
    , 'cn' => 's,t=1,h='.we('title','Titel')
    , 'flags' => array( 's' => 'flag_publish, flag_ticker, flag_detailview', 't' )
    , 'date' => 's,t=1,h='.we('date','Datum')
    , 'time' => 's,t=1,h='.we('time','Zeit')
    , 'location' => 's,t=1,h='.we('location','Ort')
    , 'groups_cn' => 's,t=1,h='.we('group','Gruppe')
    , 'people_cn' => 's,t=1,h='.we('contact','Kontakt')
    , 'url' => 's,t=1'
  ) );

  if( ! ( $events = sql_events( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no events found', 'Keine Veranstaltungen gefunden' ) );
    return;
  }
  $count = count( $events );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  open_list( $list_options );
    open_list_row('header');
    open_list_cell( 'nr' );
    if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
      open_list_cell( 'id' );
    }
    open_list_cell( 'cn' );
    open_list_cell( 'flags' );
    open_list_cell( 'date' );
    open_list_cell( 'time' );
    open_list_cell( 'location' );
    open_list_cell( 'groups_cn' );
    open_list_cell( 'people_cn' );
    open_list_cell( 'url' );
    foreach( $events as $r ) {
      $events_id = $r['events_id'];
      open_list_row();
        open_list_cell( 'nr', inlink( 'event_view', "events_id=$events_id,text={$r['nr']}" ), 'right' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', any_link( 'events', $events_id, "text=$events_id" ), 'number' );
        }
        open_list_cell( 'cn', inlink( 'event_view', array( 'text' => $r['cn'], 'events_id' => $events_id ) ) );
        $t = '';
        foreach( array( 'T' => 'flag_ticker', 'P' => 'flag_publish', 'D' => 'flag_detailview' ) as $flag => $name ) {
          if( $r[ $name ] ) {
            $t .= "$flag ";
          }
        }
        open_list_cell( 'flags', $t, 'oneline' );
        open_list_cell( 'date', $r['date'] );
        open_list_cell( 'time', $r['time'] );
        open_list_cell( 'location', $r['location'] );
        open_list_cell( 'groups_cn', $r['groups_id'] ? alink_group_view( $r['groups_id'], 'fullname=1' ) : ' - ' );
        open_list_cell( 'people_cn', $r['people_id'] ? alink_person_view( $r['people_id'] ) : ' - ' );
        open_list_cell( 'url', url_view( $r['url'], 'class='.$r['url_class'] ) );
    }
  close_list();
}


function examslist_view( $filters = array(), $opts = array() ) {

  $filters = restrict_view_filters( $filters, 'exams' );

  $opts = parameters_explode( $opts, 'set=filename='.we('exams','pruefungen') );
  $list_options = handle_list_options( $opts, 'exams', array(
      'nr' => 't=1'
    , 'id' => 's=exams_id,t=1'
    , 'cn' => 't=1'
    , 'teacher' => 's=teacher_cn,t=1'
    , 'programme' => 't,s=programme_flags,h='.we('programme','Studiengang')
    , 'url' => 's,t=1'
    , 'actions' => 't'
  ) );
  if( ! ( $exams = sql_exams( $filters, 'utc,semester' ) ) ) {
    open_div( '', we('No exams', 'Keine Prüfungen vorhanden' ) );
    return;
  }
  $count = count( $exams );

  // $date_start = 
}


function surveyslist_view( $filters = array(), $opts = array() ) {
  $filters = restrict_view_filters( $filters, 'surveys' );
  $list_options = handle_list_options( $opts, 'surveys', array(
      'nr' => 't=1'
    , 'cn' => 's,t=1,h='.we('designation','Bezeichnung')
    , 'initiator_cn' => 's,t=0,h='.we('initiator','Initiator')
    , 'ctime' => 's,t=1,h='.we('creation time','Erstellzeit')
    , 'deadline' => 's,t=1'
    , 'status' => 's=closed,t=1'
//    , 'actions' => 't'
  ) );
  if( ! ( $surveys = sql_surveys( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', we( 'No surveys available', 'Keine Umfragen vorhanden' ) );
    return;
  }
  $count = count( $surveys );

  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
        open_list_cell( 'id' );
      }
      open_list_cell( 'cn', we('Subject','Betreff') );
      open_list_cell( 'initiator_cn', we('initiated by','Initiator') );
      open_list_cell( 'ctime', we('start','Beginn') );
      open_list_cell( 'deadline', we('deadline','Einsendeschluss') );
      open_list_cell( 'closed', we('status','Status') );
    foreach( $surveys as $s ) {
      $surveys_id = $s['surveys_id'];
      open_list_row('listrow');
        open_list_cell( 'nr', $s['nr'], 'right' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', any_link( 'surveys', $surveys_id, "text=$surveys_id" ), 'number' );
        }
        open_list_cell( 'cn', $s['cn'] );
        open_list_cell( 'initiator_cn', $s['initiator_cn'] );
        open_list_cell( 'ctime', date_canonical2weird( $s['ctime'] ) );
        open_list_cell( 'deadline', date_canonical2weird( $s['deadline'] ) );
        open_list_cell( 'status', $u['closed'] ? we('open','offen') : we('closed','abgeschlossen') );
//         open_list_cell( 'actions' );
//           if( have_priv( 'surveys', 'edit', $surveys_id ) ) {
//             echo inlink( 'survey_edit', "class=edit,text=,surveys_id=$surveys_id,title=".we('edit survey...','Umfrage bearbeiten...') );
//           }
//           if( ( $GLOBALS['script'] == 'surveys' ) ) {
//             if( have_priv( 'surveys', 'delete', $surveys_id ) ) {
//               echo inlink( '!submit', "class=drop,confirm=".we('delete survey?','Umfrage loeschen?').",action=deleteSurvey,message=$surveys_id" );
//             }
//           }
    }
  close_list();
}

function surveysubmissions_view( $filters = array(), $opts = array() ) {
  $filters = restrict_view_filters( $filters, 'surveysubmissions' );
  $opts = handle_list_options( $opts, 'surveysubmissions', array(
      'nr' => 't=1'
    , 'survey' => 's,t=1,h='.we('survey','Umfrage')
    , 'creator_cn' => 's,t=0,h='.we('submitter','Einsender')
    , 'mtime' => 's,t=1'.we('last modification','letzte Änderung')
    , 'replies' => 's,t=1,h='.we('replies','Antworten')
//    , 'actions' => 't'
  ) );
  if( ! ( $submissions = sql_surveysubmissions( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', we('No submissions available', 'Keine Teilnehmer vorhanden' ) );
    return;
  }
  $count = count( $submissions );

  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( $opts );
    open_tr('listhead');
    open_list_cell( 'nr' );
    open_list_cell( 'survey', we('survey','Umfrage') );
    open_list_cell( 'creator_cn', we('submitter','Teilnehmer') );
    open_list_cell( 'mtime', we('time','Zeit') );
    open_list_cell( 'replies', we('replies','Antworten') );
//    open_list_cell( 'actions', we('actions','Aktionen') );
    foreach( $submissions as $s ) {
      $surveysubmissions_id = $s['surveysubmissions_id'];
      open_tr('listrow');
        open_list_cell( 'nr', $s['nr'], 'right' );
        open_list_cell( 'survey', $s['cn'] );
        open_list_cell( 'creator_cn', $s['creator_cn'] );
        open_list_cell( 'mtime', date_canonical2weird( $s['mtime'] ) );
        open_list_cell( 'replies', $t['replies_count'] );
//        open_list_cell( 'actions' );
//           if( have_priv( 'surveysubmissions', 'edit',  $s ) ) {
//             echo inlink( 'surveysubmission_edit', "class=edit,text=,surveysubmissions_id=$surveysubmissions_id,title=".we('edit data...','bearbeiten...') );
//           }
//           if( ( $GLOBALS['script'] == 'surveysubmissions' ) ) {
//             if( have_priv( 'surveysubmissions', 'delete',  $s ) ) {
//               echo inlink( '!submit', "class=drop,confirm=Teilnahme loeschen?,action=deleteSurveysubmission,message=$surveysubmissions_id" );
//             }
//           }
    }
  close_table();
}

function teachinganon_view( $filters ) {
  need_priv( 'teaching', 'list' );

  $list_options = handle_list_options( true, 'teachinganon' );
  $list_options['limits'] = false;
  $list_options['toggle_prefix'] = false;
  $list_options['sort_prefix'] = false;

  open_list( $list_options );

  $groups = sql_groups( 'flag_institute' );
  $groups[] = 'extern'; // dummy entry for all extern teachers
  foreach( $groups as $group ) {
    if( $group === 'extern' ) {
      $section_title = 'Externe Dozenten';

      $teachers = sql_teaching( array( '&&', $filters, "flag_institute=0" ), 'groupby=teacher_people_id' )  // merge: members of non-institute groups...
                + sql_teaching( array( '&&', $filters, "extern" ), 'groupby=extteacher_cn' );      // ...plus unknown aliens (kludge on special request by diph)

      $teachings = sql_teaching( array( '&&', $filters, "flag_institute=0,lesson_type!=X,lesson_type!=N" ) )  // merge: members of non-institute groups...
                + sql_teaching( array( '&&', $filters, "extern,lesson_type!=X,lesson_type!=N" ) );       // ...plus unknown aliens (kludge on special request by diph)

    } else {
      $groups_id = $group['groups_id'];
      $section_title = 'Bereich: '. $group['cn'];
      $head_people_id = $group['head_people_id'];

      $teachers = sql_teaching(
        array( '&&', $filters, "teacher_groups_id=$groups_id" )
      , array(
          'groupby' => 'teacher_people_id'
        , 'orderby' => "IF( teaching.teacher_people_id = $head_people_id, 0, 1 ), teacher.privs DESC,teacher_cn"
        )
      );
      $teachings = sql_teaching(
        array( '&&', $filters, "teacher_groups_id=$groups_id,lesson_type!=X,lesson_type!=N" )
      , array(
          'orderby' => "IF( teaching.teacher_people_id = $head_people_id, 0, 1 ), CAST( course_number AS UNSIGNED )"
        )
      );

    }

    if( ( ! $teachers ) && ( ! $teachings ) ) {
      continue;
    }

    open_list_row('header');
      open_list_cell( 'style=padding:2em 0em 0em 1em;background-color:white;', $section_title, 'colspan=17' );

    open_list_row('header');
      open_list_cell( '', 'Dozent' );
      open_list_cell( '', 'Stelle' );
      open_list_cell( '', 'Pf' );
      open_list_cell( '', 'Red' );
      open_list_cell( 'oneline', 'Pf eff' );
      open_list_cell( 'solidleft solidright,style=padding-left:1em;', ' ' );

      open_list_cell( '', 'Art' );
      open_list_cell( '', 'Titel' );
      open_list_cell( '', 'Nr.' );
      open_list_cell( '', 'Mod.' );
      open_list_cell( '', 'SWS' );
      open_list_cell( '', 'AnrF.' );
      open_list_cell( '', 'AbhF' );
      // open_list_cell( '', '#/Co-Veranstalter' );
      open_list_cell( '', '#Veranst.' );
      open_list_cell( 'oneline', 'SWS eff' );
      open_list_cell( '', 'Teiln.' );
      open_list_cell( '', 'Kommentar' );

    $obligation_sum = 0.0;
    $teaching_sum = 0.0;
    $GLOBALS['current_table']['row_number'] = 2;
    $j = 0;
    while( isset( $teachers[ $j ] ) || isset( $teachings[ $j ] ) ) {
      open_list_row();

      if( isset( $teachers[ $j ] ) ) {
        $t = $teachers[ $j ];
        $r = $t['teaching_reduction'];
        $ob_eff = $t['teaching_obligation'] - $r;
        $obligation_sum += $ob_eff;

        open_list_cell( '', $t['teacher_cn'] );
        open_list_cell( '', $t['typeofposition'] );
        open_list_cell( 'number', price_view( $t['teaching_obligation'] ) );
        open_list_cell( 'number', ( $r > 0 ) ? html_tag( 'abbr', array( 'class' => 'specialcase', 'title' => "Reduktionsgrund: ".$t['teaching_reduction_reason'] ), " $r " ) : ' 0 ' );
        open_list_cell( 'number', price_view( $ob_eff ) );

      } else {
        open_list_cell( '', ' ', 'colspan=5' );
      }

      open_list_cell( 'solidleft solidright', ' ' );

      if( isset( $teachings[ $j ] ) ) {
        $t = $teachings[ $j ];
        $n = $t['teachers_number'];
        $sws = ( $t['hours_per_week'] * $t['credit_factor'] * $t['teaching_factor'] );
        switch( $t['lesson_type'] ) {
          case 'SE':
          case 'VL':
            $sws /= ( $t['teachers_number'] ? $t['teachers_number'] : 1.0 );
            break;
        }

        $teaching_sum += $sws;
        open_list_cell( '', $t['lesson_type'] );
        open_list_cell( '', $t['course_title'] );
        open_list_cell( '', $t['course_number'] );
        open_list_cell( '', $t['module_number'] );
        open_list_cell( 'number', price_view( $t['hours_per_week'] ) );
        open_list_cell( '', $t['credit_factor'] );
        open_list_cell( '', $t['teaching_factor'] );
        open_list_cell( '', ( $n > 1 ) ? html_tag( 'abbr', array( 'class' => 'specialcase', 'title' => "Mit-Veranstalter: ".$t['co_teacher'] ), " $n " ) : ' 1 ' );
        open_list_cell( 'number', price_view( $sws ) );
        open_list_cell( 'number', ( $t['participants_number'] ? $t['participants_number'] : '(unbekannt)' ) );
        open_list_cell( '', $t['note'] );


      } else {
        open_td( '', ' ', 'colspan=11' );
      }

      $j++;
    }
    open_list_row('sum');
      open_list_cell( '', we('sum:','Summe:'), 'colspan=4' );
      open_list_cell( 'number', price_view( $obligation_sum ) );
      open_list_cell( '', ' ' );
      open_list_cell( '', ' ', 'colspan=8' );
      open_list_cell( 'number', price_view( $teaching_sum ) );
      open_list_cell( '', ' ', 'colspan=8' );

  }
  close_list();
}


function teachinglist_view( $filters = array(), $opts = array() ) {
  global $login_groups_ids, $choices_typeofposition, $global_format;

  $filters = restrict_view_filters( $filters, 'teaching' );

  $opts = parameters_explode( $opts, 'set=filename='.we('teaching','lehrerfassung') );
  // $do_edit = adefault( $opts, 'do_edit', 0 );
  // $edit_teaching_id = adefault( $opts, 'edit_teaching_id', 0 );
  $format = adefault( $opts, 'format', $global_format );

  $cols = array(
    'nr' => 't=on'
  , 'id' => 's=teaching_id,t=1'
  , 'yearterm' => array( 's' => 'CONCAT(teaching.year,teaching.term)', 't' => ( isset( $filters['year'] ) && isset( $filters['term'] ) ? '0' : '1' ), 'h' => we('term','Semester') )
  , 'teacher' => array( 't' => 1, 's' => 'teacher_sn', 'h' => we('teacher','Lehrender') )
  , 'typeofposition' => 's,t,h='.we('type of position','Stelle')
//    , 'teaching_obligation' => 's,t'
  , 'teaching_reduction' => 's,t,h='.we('reduction','Reduktion')
  , 'course' => 's=course_number,t,h='.we('course','Veranstaltung')
  , 'hours_per_week' => 's,t,h='.we('hours per week','Wochenstunden')
  , 'teaching_factor' => 's,t,h='.we('teaching factor','Abhaltefaktor')
//    , 'credit_factor' => 's,t'
  , 'teachers_number' => 's,t,h='.we('number of teachers','Anzahl Lehrende')
  , 'participants_number' => 's,t,h='.we('number of participants','Anzahl Teilnehmer')
  , 'note' => 't,h='.we('note','Anmerkung')
  , 'signer' => 't=0,s=signer_cn,h='.we('signed by','im Namen von')
//  , 'actions' => 't'
  );
  if( have_priv( 'teaching', 'list' ) ) {
    // need this even with $edit so the sorting doesn't fail:
    $cols['creator'] = 's=creator_cn,t,h='.we('submitted by','Eintrag von');
  }
  $list_options = handle_list_options( $opts, 'teaching', $cols );

  $teaching = sql_teaching( $filters, array( 'orderby' => $list_options['orderby_sql'] ) );

  $sep = ' ## ';
  switch( $format ) {
    case 'csv':
      begin_deliverable( 'teachinglist', 'csv' );
        echo "nr $sep Semester $sep Lehrender $sep Stelle $sep Lehrverpflichtung $sep Reduktion $sep Art $sep Veranstaltung "
           . "$sep VVZ-Nr $sep Modul-Nr $sep SWS $sep Abhaltefaktor $sep Anrechnungsfaktor $sep Lehrende "
           . "$sep Teilnehmer $sep im Namen von $sep Anmerkung"
           . "\n";
        foreach( $teaching as $t ) {
          echo $t['nr'];
          echo $sep . $t['term'] . ' ' . $t['year'];
          echo $sep . $t['teacher_cn'];
          echo $sep . $t['typeofposition'];
          echo $sep . $t['teaching_obligation'];
          echo $sep . $t['teaching_reduction'];
          echo $sep . $t['lesson_type'];
          echo $sep . $t['course_title'];
          echo $sep . $t['course_number'];
          echo $sep . $t['module_number'];
          echo $sep . $t['hours_per_week'];
          echo $sep . $t['teaching_factor'];
          echo $sep . $t['credit_factor'];
          echo $sep . $t['teachers_number'];
          echo $sep . ( $t['participants_number'] ? $t['participants_number'] : we('unknown','unbekannt') );
          echo $sep . $t['signer_cn'];
          echo $sep;
            echo $t['note'] . ' ';
            if( $t['teaching_reduction'] ) {
              echo "Reduktionsgrund: ".$t['teaching_reduction_reason'];
            }
            if( $t['teachers_number'] > 1 ) {
              echo "Mit-Lehrende: ".$t['co_teacher'];
            }
          echo "\n";
        }
      end_deliverable( 'teachinglist' );
      return;
    default:
      break;
  }

  if( ! $teaching ) {
    open_div( '', we('No entries available', 'Keine Eintraege vorhanden' ) );
    return;
  }
  $count = count( $teaching );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
        open_list_cell( 'id' );
      }
      open_list_cell( 'yearterm', we('Term','Semester') );
      open_list_cell( 'teacher', we('teacher','Lehrender') );
      open_list_cell( 'typeofposition',
        html_tag( 'div', '', we('position','Stelle') )
        . html_tag( 'div', '', we('obligation','Lehrverpflichtung') )
      );
      open_list_cell( 'teaching_reduction', we('reduction','Reduktion') );
      open_list_cell( 'course', we('course','Veranstaltung') );
      open_list_cell( 'hours_per_week',
        html_tag( 'div', '', we('hours per week','SWS') )
      );
      open_list_cell( 'teaching_factor',
        html_tag( 'div', '', we('teaching factor','Abhaltefaktor') )
        . html_tag( 'div', '', we('credit factor','Anrechnungsfaktor') )
      );
      open_list_cell( 'teachers_number', we('teachers','Lehrende') );
      open_list_cell( 'participants_number', we('participants','Teilnehmer') );
      open_list_cell( 'signer', we('signed by','im Namen von') );
      if( isset( $cols['creator'] ) ) {
        open_list_cell( 'creator', we('submitted by','Eintrag von') );
      }
      open_list_cell( 'note', we('note','Anmerkung') );

    foreach( $teaching  as $t ) {
      $teaching_id = $t['teaching_id'];
      if( $t['nr'] < $limits['limit_from'] )
        continue;
      if( $t['nr'] > $limits['limit_to'] )
        break;

      open_list_row();
        $s = $t['nr'];
        if( have_priv( 'teaching', 'edit', $t ) ) {
          $s = inlink( 'teaching_edit', array(
            'class' => 'edit', 'text' => $s, 'teaching_id' => $teaching_id
          , 'title' => we('edit data...','bearbeiten...')
          ) );
        }
        open_list_cell( 'nr', $s, 'class=number' );

        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', any_link( 'teaching', $teaching_id, "text=$teaching_id" ), 'class=number' );
        }
        open_list_cell( 'yearterm', "{$t['term']} {$t['year']}" );
          if( $t['extern'] ) {
            $s = html_div( '', 'extern:' )
                 . html_div( 'bold', $t['extteacher_cn'] );
          } else {
            $s = html_div( '', alink_group_view( $t['teacher_groups_id'] ) )
                 . html_div( '', alink_person_view( $t['teacher_people_id'] ) );
          }
        open_list_cell( 'teacher', $s );
          // open_div( 'center', adefault( $choices_typeofposition, $t['typeofposition'], we('unknown','unbekannt') ) );
        open_list_cell( 'typeofposition'
        , html_div( 'center', $t['typeofposition'] )
          . html_div( 'center', $t['teaching_obligation'] )
        );
        // open_list_cell( 'teaching_obligation', $t['teaching_obligation'] );
        open_list_cell( 'teaching_reduction', $t['teaching_reduction'], 'center' );
          // open_div( 'left', $t['teaching_reduction_reason'] );

        if( $t['lesson_type'] === 'X' ) { // sabbatical
          open_list_cell( 'course', we(' - sabbatical -','- freigestellt -'), 'colspan=5' );
        } else if( $t['lesson_type'] === 'N' ) { // none
          open_list_cell( 'course', we(' - none -','- keine Lehre -'), 'colspan=5' );
        } else {
          open_list_cell( 'course'
          , html_div( 'quads bold left', $t['course_title'] )
            . html_div( '',
                html_span( 'quadl oneline', we('type: ','Art: ').$t['lesson_type'] )
                . html_span( 'quadl oneline', we('number: ','Nummer: ').$t['course_number'] )
                . html_span( 'qquads oneline', we('module: ','Modul: ').$t['module_number'] )
              )
          );
          open_list_cell( 'hours_per_week', html_div( 'center', $t['hours_per_week'] ) );
          open_list_cell( 'teaching_factor'
          , html_div( 'center', $t['teaching_factor'] )
            . html_div( 'center', $t['credit_factor'] )
          );
          open_list_cell( 'teachers_number', $t['teachers_number'], 'center' );
          open_list_cell( 'participants_number', $t['participants_number'] ? $t['participants_number'] : we('unknown','unbekannt') );
        }
        open_list_cell( 'signer'
        , html_div( '', alink_group_view( $t['signer_groups_id'] ) )
          . html_div( '', alink_person_view( $t['signer_people_id'] ) )
        );
        if( isset( $cols['creator'] ) ) {
          open_list_cell( 'creator', alink_person_view( $t['creator_people_id'] ) );
        }
        $s = html_div( '', $t['note'] );
        if( $t['teaching_reduction'] > 0 ) {
          $s .= html_div( 'left', we('reduction: ','Reduktion: ') . $t['teaching_reduction_reason'] );
        }
        if( $t['teachers_number'] > 1 ) {
          $s .= html_div( 'left', we('co-teachers: ','Mit-Lehrende: ') . $t['co_teacher'] );
        }
        open_list_cell( 'note', $s );

    }
  close_list();
}

function moduleslist_view( $filters = array(), $opts = array() ) {
  global $uUML;
  $opts = parameters_explode( $opts, 'set=filename='.we('modules','module') );
  $list_options = handle_list_options( $opts, 'modules', array(
      'id' => 's=modules_id,t=' . ( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ? '1' : 'off' )
    , 'nr' => 't=1'
    , 'tag' => 's,t=1,h='.we('short name','Kurzbezeichnung')
    , 'cn' => 's,t=1,h='.we('title','Titel')
    , 'year_valid_from' => 's,t=1,h='.we('valid from',"g{$uUML}ltig ab")
    , 'programme' => 's=programme_flags,t=1,h='.we('programme','Studiengang')
    , 'contact' => 's=contact_people_id,t=1,h='.we('responsible person','Verantwortliche Person')
  ) ); 

  if( ! ( $modules = sql_modules( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no modues', 'Keine Module vorhanden' ) );
    return;
  }
  $count = count( $modules );
  $list_options['limits'] = false;

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
        open_list_cell( 'id' );
      }
      open_list_cell( 'tag' );
      open_list_cell( 'cn' );
      open_list_cell( 'year_valid_from' );
      open_list_cell( 'programme' );
      open_list_cell( 'contact' );
    foreach( $modules as $r ) {
      $modules_id = $r['modules_id'];
      open_list_row();
        $t = inlink( 'module_view', array( 'modules_id' => $modules_id, 'text' => $r['nr'], 'class' => 'href inlink' ) );
        open_list_cell( 'nr', $t, 'number' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', any_link( 'modules', $modules_id, "text=$modules_id" ), 'number' );
        }
        $t = inlink( 'module_view', array( 'modules_id' => $modules_id, 'text' => $r['tag'], 'class' => 'href inlink' ) );
        open_list_cell( 'tag', $t );
        $t = inlink( 'module_view', array( 'modules_id' => $modules_id, 'text' => $r['cn'], 'class' => 'href inlink' ) );
        open_list_cell( 'cn', $t );
        open_list_cell( 'year_valid_from', $r['year_valid_from'] );
        open_list_cell( 'programme', programme_cn_view( $r['programme_flags'] ) ); 
        open_list_cell( 'contact', alink_person_view( $r['contact_people_id'], 'office' ) ); 
    }
  close_list();
}

function roomslist_view( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts, 'set=filename='.we('labs','labore') );
  $list_options = handle_list_options( $opts, 'rooms', array(
      'id' => 's=rooms_id,t=' . ( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ? '1' : 'off' )
    , 'nr' => 't=1'
    , 'roomnumber' => 's,t=1,h='.we('roomnumber','Raumnummer')
    , 'groups_id' => 's=owning_group_cn,t=1,h='.we('group','Gruppe')
    , 'contact_cn' => 's,t=1,h='.we('responsible person','Verantwortliche Person')
    , 'contact2_cn' => 's,t=1,h='.we('deputy','Vertretung')
  ) ); 

  if( ! ( $rooms = sql_rooms( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no rooms', 'Keine Räume vorhanden' ) );
    return;
  }
  $count = count( $rooms );
  // $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = false;

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
        open_list_cell( 'id' );
      }
      open_list_cell( 'roomnumber' );
      open_list_cell( 'groups_id' );
      open_list_cell( 'contact_cn' );
      open_list_cell( 'contact2_cn' );
    foreach( $rooms as $r ) {
      $rooms_id = $r['rooms_id'];
      open_list_row();
        $t = inlink( 'room_view', array( 'rooms_id' => $rooms_id, 'text' => $r['nr'], 'class' => 'href inlink' ) );
        open_list_cell( 'nr', $t, 'number' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', any_link( 'rooms', $rooms_id, "text=$rooms_id" ), 'number' );
        }
        $t = inlink( 'room_view', array( 'rooms_id' => $rooms_id, 'text' => $r['roomnumber'], 'class' => 'href inlink' ) );
        open_list_cell( 'roomnumber', $t );
        open_list_cell( 'groups_id', alink_group_view( $r['groups_id'] ) ); 
        open_list_cell( 'contact_cn', alink_person_view( $r['contact_people_id'], 'office' ) ); 
        open_list_cell( 'contact2_cn', alink_person_view( $r['contact2_people_id'], 'office' ) ); 
    }
  close_list();
}

function documentslist_view( $filters = array(), $opts = array() ) {
  global $choices_documenttype, $oUML, $uUML;
  $opts = parameters_explode( $opts, 'set=filename='.we('documents','dokumente') );
  $list_options = handle_list_options( $opts, 'documents', array(
      'id' => 's=documents_id,t=' . ( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ? '1' : 'off' )
    , 'nr' => 't=1'
    , 'type' => 's,t'
    , 'tag' => 's,t'
    , 'programme' => 't,s=programme_flags,h='.we('programme','Studiengang')
    , 'cn' => 's,t,h='.we('name','Bezeichnung')
    , 'filename' => 's,t,h='.we('file name','Dateiname')
    , 'current' => 's=flag_current,t,h='.we('current','aktuell')
    , 'publish' => 's=flag_publish,t,h='.we('publish',"{$oUML}ffentlich")
    , 'url' => 's,t,h='.we('file or link','Datei oder Link')
    , 'valid_from' => 's,t'
  ) ); 

  if( ! ( $documents = sql_documents( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no documents', 'Keine Dateien vorhanden' ) );
    return;
  }
  $count = count( $documents );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
        open_list_cell( 'id' );
      }
      open_list_cell( 'type' );
      open_list_cell( 'tag' );
      open_list_cell( 'programme' );
      open_list_cell( 'cn' );
      open_list_cell( 'filename' );
      open_list_cell( 'current' );
      open_list_cell( 'publish' );
      open_list_cell( 'url' );
      open_list_cell( 'valid_from' );
    foreach( $documents as $r ) {
      $documents_id = $r['documents_id'];
      open_list_row();
        $t = inlink( 'document_view', array( 'documents_id' => $documents_id, 'text' => $r['nr'], 'class' => 'href inlink' ) );
        open_list_cell( 'nr', $t, 'number' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', any_link( 'documents', $documents_id, "text=$documents_id" ), 'number' );
        }
        open_list_cell( 'type', adefault( $choices_documenttype, $r['type'], we('(not valid)',"(ung{$uUML}ltig)") ) );
        open_list_cell( 'tag', $r['tag'] );
        open_list_cell( 'programme', programme_cn_view( $r['programme_flags'], 'short=1' ) );
        $t = inlink( 'document_view', array( 'documents_id' => $documents_id, 'text' => $r['cn'], 'class' => 'href inlink' ) );
        open_list_cell( 'cn', $t );
        open_list_cell( 'filename', $r['filename'] );
        open_list_cell( 'current', ( $r['flag_current'] ? we('yes','ja') : we('no','nein') ) );
        open_list_cell( 'publish', ( $r['flag_publish'] ? we('yes','ja') : we('no','nein') ) );
        if( ( $url = $r['url'] ) ) {
          $t = url_view( $url, 'class=href '.$r['url_class'] );
        } else if( $r['pdf'] ) {
          $t = inlink( 'document_view', array(
            'documents_id' => $documents_id
          , 'f' => 'pdf'
          , 'i' => 'document'
          , 'class' => 'file'
          , 'window' => 'download'
          , 'n' => hex_encode( $r['filename'] )
          , 'text' => $r['filename']
          ) );
        } else {
          $t = span_view( 'warn', we('no document available',"keine Datei verf{$uUML}gbar") );
        }
        open_list_cell( 'url', $t );
        open_list_cell( 'valid_from', $r['valid_from'] );
    }
  close_list();
}


function people_references_view( $people_id ) {

  // if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) 

  $references = sql_references( 'people', $people_id, "ignore=changelog logbook sessions affiliations" );
  $list = array();
  $list['other'] = array();
  foreach( $references as $table => $trefs ) {
    foreach( $trefs as $col => $r ) {
      foreach( $r as $id ) {
        if( ! have_priv( 'read', $table, $id ) ) {
          $list['other'][ $table ] = $list['other'];
        }
        switch( $col ) {
          case 'jpegphotorights_people_id':
            $list['rights'] = we('photo rights: ','Bildrechte: ' ) . entry_link( $table, $id, "col=$col" );
            continue 2;
          case 'creator_people_id':
            
        }

        switch( "$table,$col" ) {
          case 'teaching,teacher_people_id':
            $list[] = we('teaching survey: teacher: ','Lehrerfassung: Dozent: ' ) . entry_link( $table, $id, "col=$col" );
            continue 2;
          case 'teaching,signer_people_id':
            $list[] = we('teaching survey: signer: ','Lehrerfassung: Unterschrift: ' ) . entry_link( $table, $id, "col=$col" );
            continue 2;
          case 'teaching,creator_people_id':
            $list[] = we('teaching survey: submitter: ','Lehrerfassung: Erfasser: ' ) . entry_link( $table, $id, "col=$col" );
            continue 2;
          case 'people,people_id':
            continue 2;
          case 'groups,head_people_id':
            $list[] = we('Head of group: ','Leiter der Gruppe: ' ) . alink_group_view( $id );
            continue 2;
          case 'groups,secretary_people_id':
            $list[] = we('Secretary: ','Sekretariat: ' ) . alink_group_view( $id );
            continue 2;
        }
        $list[] = we('other: ','weitere: ') . entry_link( $table, $id, "col=$col" );
      }
      if( $list ) {
        open_fieldset( '', we('links to this person','Verweise auf diese Person') );
          open_ul();
            foreach( $list as $item ) {
              open_li( '', $item );
            }
          close_ul();
        close_fieldset(); 
      }
    }
  }
}



require_once('shared/views.php');
  
?>
