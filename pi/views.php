<?php // pi/views.php

require_once('code/views.php');

function mainmenu_fullscreen() {
  global $logged_in;

  $mainmenu[] = array( 'script' => 'peoplelist'
  , 'title' => we('People','Personen')
  , 'text' => we('People','Personen')
  );

  $mainmenu[] = array( 'script' => 'groupslist'
  , 'title' => we('Groups','Gruppen')
  , 'text' => we('Groups','Gruppen')
  );

  if( 0 ) {
    $mainmenu[] = array( 'script' => 'eventslist'
    , 'title' => we('Events','Veranstaltungen')
    , 'text' => we('Events','Veranstaltungen' )
    );
  }
  
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    $mainmenu[] = array( 'script' => 'examslist'
    , 'title' => we('Exam dates','Prüfungstermine')
    , 'text' => we('Exam dates','Prüfungstermine')
    , 'inactive' => true
    );
  }
  
  $mainmenu[] = array( 'script' => 'teachinglist'
  , 'title' => we('Teaching','Lehrerfassung')
  , 'text' => we('Teaching','Lehrerfassung')
  , 'inactive' => ( $logged_in ? false : we('please login first','bitte erst Anmelden') )
  );
  
  if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    $mainmenu[] = array( 'script' => 'configuration'
    , 'title' => we('Configuration','Konfiguration')
    , 'text' => we('Configuration','Konfiguration')
    );
  }

  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    $mainmenu[] = array( 'script' => 'surveyslist'
    , 'title' => we('Surveys','Umfragen')
    , 'text' => we('Surveys','Umfragen')
    , 'inactive' => true
    );
  }
  
  $mainmenu[] = array( 'script' => 'positionslist'
  , 'title' => we('Thesis Topics','Themen Ba/Ma-Arbeiten')
  , 'text' => we('Thesis Topics','Themen Ba/Ma-Arbeiten')
  , 'inactive' => true
  );

  $mainmenu[] = array( 'script' => 'publicationslist'
  , 'title' => we('Publications','Publikationen')
  , 'text' => we('Publications','Publikationen')
  , 'inactive' => false
  );
  
  $mainmenu[] = array( 'script' => 'roomslist'
  , 'title' => we('Labs','Labore')
  , 'text' => we('Labs','Labore')
  , 'inactive' => false
  );

  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
      $mainmenu[] = array( 'script' => 'admin'
      , 'title' => 'Admin'
      , 'text' => 'Admin'
      );
      $mainmenu[] = array( 'script' => 'sessions'
      , 'title' => 'Sessions'
      , 'text' => 'Sessions'
      );
      $mainmenu[] = array( 'script' => 'logbook'
      , 'title' => we('Logbook','Logbuch')
      , 'text' => we('Logbook','Logbuch')
      );
      $mainmenu[] = array( 'script' => 'anylist'
      , 'title' => we('Tables','Tabellen')
      , 'text' => we('Tables','Tabellen')
      );
  }

  foreach( $mainmenu as $h ) {
    // open_tr();
      open_li( '', inlink( $h['script'], array(
        'text' => $h['text'], 'title' => $h['title'] , 'class' => 'bigbutton', 'inactive' => adefault( $h, 'inactive' )
      ) ) );
  }
  // open_tr('medskip');
    if( $logged_in ) {
      open_li( '', inlink( '', array(
        'text' => we('Logout', 'Abmelden')
      , 'title' => we('Logout', 'Abmelden')
      , 'class' => 'bigbutton'
      , 'login' => 'logout'
      ) ) );
    } else {
      open_li( '', inlink( '', array(
        'text' => we('Login', 'Anmelden')
      , 'title' => we('Login', 'Anmelden')
      , 'class' => 'bigbutton'
      , 'login' => 'login'
      ) ) );
    }
}

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
    return $GLOBALS['window'] . '/' . $GLOBALS['thread'] .'/'. $GLOBALS['login_sessions_id'];
  } else {
    return $GLOBALS['window'] . '/' . $GLOBALS['thread'];
  }
}


// people:
//

function peoplelist_view( $filters = array(), $opts = array() ) {

  $filters = restrict_view_filters( $filters, 'people' );
  $opts = parameters_explode( $opts );
  $regex_filter = adefault( $opts, 'regex_filter' );

  $list_options = handle_list_options( adefault( $opts, 'list_options', true ), 'people', array(
      'id' => 's=people_id,h=id,t='.( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ? 1 : 'off' )
    , 'nr' => 't=1'
    , 'gn' => 's,t,h='.we('first names','Vornamen')
    , 'sn' => 's,t,h='.we('last name','Nachname')
    , 'title' => 's,t,h='.we('title','Titel')
    , 'jperson' => 's,t'
    , 'flags' => 't'
    , 'typeofposition' => 't=0,s'
    , 'teaching_obligation' => 't=0,s'
    , 'teaching_reduction' => 't=0,s'
    , 'uid' => 's,t'
    , 'url' => 's,t=0'
    , 'primary_roomnumber' => 's,t,h='.we('room','Raum')
    , 'primary_telephonenumber' => 's,t,h='.we('phone','Telefon')
    , 'primary_mail' => 's,t,h='.we('mail','Email')
    , 'groups' => 's=primary_groupname,t,h='.we('group','Gruppe')
  ) );

  if( ! ( $people = sql_people( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
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
        open_list_cell( 'flags' );
        open_list_cell( 'uid' );
      }
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

      $aff = sql_affiliations( "people_id=$people_id" );
      $glinks = '';
      foreach( $aff as $a ) {
        if( $a['groups_id'] )
          $glinks .= ' '. alink_group_view( $a['groups_id'], 'href inlink quadr' );
      }

      open_list_row();
        open_list_cell( 'nr', inlink( 'person_view', array( 'class' => 'href inlink', 'people_id' => $people_id, 'text' => $person['nr'] ) ), 'number' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', any_link( 'people', $people_id, "text=$people_id" ), 'number' );
          $t = '';
          $t = ( $person['flag_institute'] ? ' I ' : '' ); 
          if( $person['flag_virtual'] )
            $t .= ' V ';
          if( $person['flag_deleted'] )
            $t .= ' D ';
          open_list_cell( 'flags', $t );
          open_list_cell( 'uid', $person['uid'] );
        }
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
            $r = inlink( '', array( 'text' => $person['primary_roomnumber'], 'REGEX' => 'ROOM:'.str_replace( '.', '\\.', $person['primary_roomnumber'] ).';' ) );
          } else {
            $r = $person['primary_roomnumber'];
          }
        } else {
          $r = ' - ';
        }
        open_list_cell( 'primary_roomnumber', $r, 'oneline' );
        if( $person['primary_telephonenumber'] ) {
          if( $regex_filter ) {
            $r = inlink( '', array( 'text' => $person['primary_telephonenumber'], 'REGEX' => 'PHONE:'.str_replace( '+', '\\+', $person['primary_telephonenumber'] ).';' ) );
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

  $filters = restrict_view_filters( $filters, 'groups' );

  $list_options = handle_list_options( $opts, 'groups', array(
      'id' => 's=groups_id,t='.( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ? 1 : 'off' )
    , 'nr' => 't=1'
    , 'cn' => 's,t=1,h='.we('name','Name')
    , 'acronym' => 's,t=1,h='.we('acronym','Kurzname')
    , 'status' => array( 's' => 'groups.flags DESC'
                       , 'h' => we('status','Status')
                       , 't' => have_minimum_person_priv( PERSON_PRIV_COORDINATOR )
      )
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
      open_list_cell( 'cn', we('Name of group','Name der Gruppe') );
      open_list_cell( 'status' );
      open_list_cell( 'head', we('group leader','Gruppenleiter') );
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
        open_list_cell( 'cn', $g['cn_we'] );
        open_list_cell( 'status', ( $g['flags'] & GROUPS_FLAG_INSTITUTE ? 'institut' : 'extern' ) );
        open_list_cell( 'head', ( $g['head_people_id'] ? alink_person_view( $g['head_people_id'] ) : '' ) );
        open_list_cell( 'secretary', ( $g['secretary_people_id'] ? alink_person_view( $g['secretary_people_id'] ) : '' ) );
        open_list_cell( 'url', url_view( $g['url'] ) );
    }
  close_list();
}


function positionslist_view( $filters = array(), $opts = array() ) {
  $filters = restrict_view_filters( $filters, 'positions' );

  $list_options = handle_list_options( adefault( $opts, 'list_options', true ), 'positions', array(
      'id' => 's=positions_id,t=1'
    , 'nr' => 't=1'
    , 'cn' => 's,t=1,h='.we('title','Titel')
    , 'group' => 's=acronym,t=1,h='.we('group','Gruppe')
    , 'degree' => 's,t=1,h='.we('degree','Abschluss')
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
    open_list_cell( 'degree', we('degree','Abschluss') );
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
          $s = '';
          foreach( $GLOBALS['degree_text'] as $degree_id => $degree_cn ) {
            if( $t['degree'] & $degree_id )
              $s .= $degree_cn . ' ';
          }
        open_list_cell( 'degree', $s );
        open_list_cell( 'url', url_view( $t['url'] ) );
    }

  close_list();
}


function publicationslist_view( $filters = array(), $opts = array() ) {

  $filters = restrict_view_filters( $filters, 'publications' );

  $list_options = handle_list_options( adefault( $opts, 'list_options', true ), 'publications', array(
      'id' => 's=publications_id,t=1'
    , 'nr' => 't=1'
    , 'title' => 's,t=1,h='.we('title','Titel')
    , 'authors' => 's,t=1,h='.we('authors','Autoren')
    , 'journal' => 's,t=1,h='.we('journal','Journal')
    , 'volume' => 's,t=1,h='.we('volume','Band')
    , 'page' => 's,t=1,h='.we('page','Seite')
    , 'year' => 's,t=1,h='.we('year of publication','Erscheinungsjahr')
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



function examslist_view( $filters = array(), $opts = array() ) {

  $filters = restrict_view_filters( $filters, 'exams' );

  $list_options = handle_list_options( $opts, 'exams', array(
      'nr' => 't=1'
    , 'id' => 's=exams_id,t=1'
    , 'cn' => 't=1'
    , 'teacher' => 's=teacher_cn,t=1'
    , 'degree' => 's,t=1'
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

  $groups = sql_groups( 'INSTITUTE' );
  $groups[] = 'extern'; // dummy entry for all extern teachers
  foreach( $groups as $group ) {
    if( $group === 'extern' ) {
      $section_title = 'Externe Dozenten';

      $teachers = sql_teaching( array( '&&', $filters, "INSTITUTE=0" ), 'groupby=teacher_people_id' )  // merge: members of non-institute groups...
                + sql_teaching( array( '&&', $filters, "extern" ), 'groupby=extteacher_cn' );      // ...plus unknown aliens (kludge on special request by diph)

      $teachings = sql_teaching( array( '&&', $filters, "INSTITUTE=0,course_type!=X,course_type!=N" ) )  // merge: members of non-institute groups...
                + sql_teaching( array( '&&', $filters, "extern,course_type!=X,course_type!=N" ) );       // ...plus unknown aliens (kludge on special request by diph)

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
        array( '&&', $filters, "teacher_groups_id=$groups_id,course_type!=X,course_type!=N" )
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
        switch( $t['course_type'] ) {
          case 'SE':
          case 'VL':
            $sws /= ( $t['teachers_number'] ? $t['teachers_number'] : 1.0 );
            break;
        }

        $teaching_sum += $sws;
        open_list_cell( '', $t['course_type'] );
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

  $opts = parameters_explode( $opts );
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
  $list_options = handle_list_options( adefault( $opts, 'list_options', true ), 'teaching', $cols );

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
          echo $sep . $t['course_type'];
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

        if( $t['course_type'] === 'X' ) { // sabbatical
          open_list_cell( 'course', we(' - sabbatical -','- freigestellt -'), 'colspan=5' );
        } else if( $t['course_type'] === 'N' ) { // none
          open_list_cell( 'course', we(' - none -','- keine Lehre -'), 'colspan=5' );
        } else {
          open_list_cell( 'course'
          , html_div( 'quads bold left', $t['course_title'] )
            . html_div( '',
                html_span( 'quadl oneline', we('type: ','Art: ').$t['course_type'] )
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

function roomslist_view( $filters = array(), $opts = array() ) {
  $list_options = handle_list_options( adefault( $opts, 'list_options', true ), 'positions', array(
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
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
        open_list_cell( 'id' );
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
            $list[] = we('Group leader: ','Leiter der Gruppe: ' ) . alink_group_view( $id );
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


function alink_person_view( $filters, $opts = array() ) {
  global $global_format;
  $opts = parameters_explode( $opts );
  $person = sql_person( $filters, NULL );
  if( $person ) {
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
  $opts = parameters_explode( $opts, 'default_key=class' );
  $class = adefault( $opts, 'class', 'href inlink' );
  $group = sql_one_group( $filters, NULL );
  if( $group ) {
    switch( $global_format ) {
      case 'html':
        return inlink( 'group_view', array(
          'groups_id' => $group['groups_id']
        , 'class' => $class
        , 'text' => adefault( $opts, 'text', $group['acronym'] )
        , 'title' => $group['cn_we']
        ) );
      case 'pdf':
        // return span_view( 'href', $text );
      default:
        return $text;
    }
  } else {
    return we('(no group)','(keine Gruppe)');
  }
}

require_once('pp_shared/views.php');
  
?>
