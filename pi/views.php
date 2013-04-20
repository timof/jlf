<?php

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
    );
  }
  
    $mainmenu[] = array( 'script' => 'positionslist',
      'title' => we('Thesis Topics','Themen Ba/Ma-Arbeiten')
    , 'text' => we('Thesis Topics','Themen Ba/Ma-Arbeiten')
    , 'inactive' => true
    );

  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
      $mainmenu[] = array( 'script' => 'admin'
      , 'title' => 'Admin'
      , 'text' => 'Admin'
      );
      $mainmenu[] = array( 'script' => 'logbook'
      , 'title' => we('Logbook','Logbuch')
      , 'text' => we('Logbook','Logbuch')
      );
  }

  foreach( $mainmenu as $h ) {
    open_tr();
      open_td( '', inlink( $h['script'], array(
        'text' => $h['text'], 'title' => $h['title'] , 'class' => 'bigbutton', 'inactive' => adefault( $h, 'inactive' )
      ) ) );
  }
  open_tr('medskip');
    if( $logged_in ) {
      open_td( '', inlink( '', array(
        'text' => we('Logout', 'Abmelden')
      , 'title' => we('Logout', 'Abmelden')
      , 'class' => 'bigbutton'
      , 'login' => 'logout'
      ) ) );
    } else {
      open_td( '', inlink( '', array(
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
    , 'uid' => 's,t'
    , 'primary_roomnumber' => 's,t,h='.we('room','Raum')
    , 'primary_telephonenumber' => 's,t,h='.we('phone','Telefon')
    , 'primary_mail' => 's,t,h='.we('mail','Email')
    , 'groups' => 's=primary_groupname,t,h='.we('group','Gruppe')
//    , 'actions' => 't'
  ) );

  if( ! begin_deliverable( ( $list_id = $list_options['list_id'] ), $list_options['allow_download'] ) ) {
    return;
  }

  if( ! ( $people = sql_people( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no such people','Keine Personen vorhanden') );
    return;
  }
  $count = count( $people );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  // $selected_people_id = adefault( $GLOBALS, $opts['select'], 0 );
  $list_options['class'] = 'list';

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      open_list_cell( 'id' );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
        open_list_cell( 'flags' );
        open_list_cell( 'uid' );
      }
      open_list_cell( 'title', we('title','Titel') );
      open_list_cell( 'gn', we('first names','Vornamen') );
      open_list_cell( 'sn', we('last name','Nachname') );
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
          $glinks .= ' '.html_alink_group( $a['groups_id'], 'href inlink quadr' );
      }

      open_list_row();
        open_list_cell( 'nr', $person['nr'], 'number' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', inlink( 'person_view', array( 'people_id' => $people_id, 'text' => $people_id ) ), 'number' );
          $t = '';
          $t = ( $person['flag_institute'] ? ' I ' : '' ); 
          if( $person['flag_virtual'] )
            $t .= ' V ';
          if( $person['flag_deleted'] )
            $t .= ' D ';
          open_list_cell( 'flags', $t );
          open_list_cell( 'uid', $person['uid'] );
        }
        open_list_cell( 'title', $person['title'] );
        open_list_cell( 'gn', $person['gn'] );
        open_list_cell( 'sn', inlink( 'person_view', array( 'class' => 'href inlink', 'people_id' => $people_id, 'text' => $person['sn'] ) ) );
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

  end_deliverable( $list_id );
}


function groupslist_view( $filters = array(), $opts = true ) {

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

  if( ! begin_deliverable( ( $list_id = $list_options['list_id'] ), $list_options['allow_download'] ) ) {
    return;
  }

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
  $list_options['class'] = 'list';
  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
        open_list_cell( 'id' );
      open_list_cell( 'acronym' );
      open_list_cell( 'cn', we('Name of group','Name der Gruppe') );
      open_list_cell( 'status' );
      open_list_cell( 'head', we('group leader','Gruppenleiter') );
      open_list_cell( 'secretary', we('secretary','Sekretariat') );
      open_list_cell( 'URL' );
    foreach( $groups as $g ) {
      $groups_id = $g['groups_id'];
      open_list_row();
        open_list_cell( 'nr', $g['nr'], 'number' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', html_alink_group( $groups_id, array( 'text' => $groups_id ) ), 'number' ); 
        }
        open_list_cell( 'acronym', html_alink_group( $groups_id ) );
        open_list_cell( 'cn', $g['cn_we'] );
        open_list_cell( 'status', ( $g['flags'] & GROUPS_FLAG_INSTITUTE ? 'institut' : 'extern' ) );
        open_list_cell( 'head', ( $g['head_people_id'] ? html_alink_person( $g['head_people_id'] ) : '' ) );
        open_list_cell( 'secretary', ( $g['secretary_people_id'] ? html_alink_person( $g['secretary_people_id'] ) : '' ) );
        open_list_cell( 'url', ( $g['url'] ? html_alink( $g['url'], array( 'class' => 'href outlink', 'text' => $g['url'], 'target' => '_new' ) ) : ' - ' ) );

    }
  close_list();

  end_deliverable( $list_id );
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

  if( ! begin_deliverable( ( $list_id = $list_options['list_id'] ), $list_options['allow_download'] ) ) {
    return;
  }

  if( ! ( $themen = sql_positions( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
    open_div( '', we('no such posisions/topics', 'Keine Stellen/Themen vorhanden' ) );
    return;
  }
  $count = count( $themen );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  // $selected_positions_id = adefault( $GLOBALS, $opts['select'], 0 );
  $list_options['class'] = 'list';

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
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
          open_list_cell( 'id', inlink( 'position_view', array( 'text' => $positions_id, 'positions_id' => $positions_id ) ), 'class=number' );
        open_list_cell( 'cn', inlink( 'position_view', array( 'text' => $t['cn'], 'positions_id' => $positions_id ) ) );
        open_list_cell( 'group', ( $t['groups_id'] ? html_alink_group( $t['groups_id'] ) : ' - ' ) );
          $s = '';
          foreach( $GLOBALS['degree_text'] as $degree_id => $degree_cn ) {
            if( $t['degree'] & $degree_id )
              $s .= $degree_cn . ' ';
          }
        open_list_cell( 'degree', $s );
        open_list_cell( 'url', ( $t['url'] ? html_alink( $t['url'], array( 'text' => $t['url'], 'target' => '_top', 'class' => 'href outlink' ) ) : ' - ' ) );
    }

  close_list();

  end_deliverable( $list_id );
}

function examslist_view( $filters = array(), $opts = true ) {

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


function surveyslist_view( $filters = array(), $opts = true ) {
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

  $list_options['class'] = 'list';
  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      open_list_cell( 'cn', we('Subject','Betreff') );
      open_list_cell( 'initiator_cn', we('initiated by','Initiator') );
      open_list_cell( 'ctime', we('start','Beginn') );
      open_list_cell( 'deadline', we('deadline','Einsendeschluss') );
      open_list_cell( 'closed', we('status','Status') );
    foreach( $surveys as $s ) {
      $surveys_id = $s['surveys_id'];
      open_list_row('listrow');
        open_list_cell( 'nr', $s['nr'], 'right' );
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

function surveysubmissions_view( $filters = array(), $opts = true ) {
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

  $opts['class'] = 'list hfill oddeven';
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

  if( ! begin_deliverable( ( $list_id = $list_options['list_id'] ), $list_options['allow_download'] ) ) {
    return;
  }
  open_list('list');

  $groups = sql_groups( 'INSTITUTE' );
  $groups[] = 'extern'; // dummy entry for all extern teachers
  foreach( $groups as $group ) {
    if( $group === 'extern' ) {
      $section_title = 'Externe Dozenten';

      $teachers = sql_teaching( array( '&&', $filters, "INSTITUTE=0" ), 'groupby=teacher_people_id' )  // merge: members of non-institute groups...
                + sql_teaching( array( '&&', $filters, "extern" ), 'groupby=extteacher_cn' );      // ...plus unknown aliens (kludge on special request by diph)

      $teachings = sql_teaching( array( '&&', $filters, "INSTITUTE=0,course_type!=X" ) )  // merge: members of non-institute groups...
                + sql_teaching( array( '&&', $filters, "extern,course_type!=X" ) );       // ...plus unknown aliens (kludge on special request by diph)

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
        array( '&&', $filters, "teacher_groups_id=$groups_id,course_type!=X" )
      , array(
          'orderby' => "IF( teaching.teacher_people_id = $head_people_id, 0, 1 ), CAST( course_number AS UNSIGNED )"
        )
      );

    }

    if( ( ! $teachers ) && ( ! $teachings ) ) {
      continue;
    }

    open_list_row();
      open_th( 'colspan=17,style=padding:2em 0em 0em 1em;background-color:white;', $section_title );

    open_list_row('header');
      open_th( '', 'Dozent' );
      open_th( '', 'Stelle' );
      open_th( '', 'Pf' );
      open_th( '', 'Red' );
      open_th( 'oneline', 'Pf eff' );
      open_th( 'solidleft solidright,style=padding-left:1em;', ' ' );

      open_th( '', 'Art' );
      open_th( '', 'Titel' );
      open_th( '', 'Nr.' );
      open_th( '', 'Mod.' );
      open_th( '', 'SWS' );
      open_th( '', 'AnrF.' );
      open_th( '', 'AbhF' );
      // open_th( '', '#/Co-Veranstalter' );
      open_th( '', '#Veranst.' );
      open_th( 'oneline', 'SWS eff' );
      open_th( '', 'Teiln.' );
      open_th( '', 'Kommentar' );

    $obligation_sum = 0.0;
    $teaching_sum = 0.0;
    $GLOBALS['current_table']['row_number'] = 2;
    $j = 0;
    while( isset( $teachers[ $j ] ) || isset( $teachings[ $j ] ) ) {
      open_tr();

      if( isset( $teachers[ $j ] ) ) {
        $t = $teachers[ $j ];
        $r = $t['teaching_reduction'];
        $ob_eff = $t['teaching_obligation'] - $r;
        $obligation_sum += $ob_eff;

        open_td( '', $t['teacher_cn'] );
        open_td( '', $t['typeofposition'] );
        open_td( 'number', price_view( $t['teaching_obligation'] ) );
        open_td( 'number', ( $r > 0 ) ? html_tag( 'abbr', array( 'class' => 'specialcase', 'title' => "Reduktionsgrund: ".$t['teaching_reduction_reason'] ), " $r " ) : ' 0 ' );
        open_td( 'number', price_view( $ob_eff ) );

      } else {
        open_td( 'colspan=5', ' ' );
      }

      open_td( 'solidleft solidright', ' ' );

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
        open_td( '', $t['course_type'] );
        open_td( '', $t['course_title'] );
        open_td( '', $t['course_number'] );
        open_td( '', $t['module_number'] );
        open_td( 'number', price_view( $t['hours_per_week'] ) );
        open_td( '', $t['credit_factor'] );
        open_td( '', $t['teaching_factor'] );
        open_td( '', ( $n > 1 ) ? html_tag( 'abbr', array( 'class' => 'specialcase', 'title' => "Mit-Veranstalter: ".$t['co_teacher'] ), " $n " ) : ' 1 ' );
        open_td( 'number', price_view( $sws ) );
        open_td( 'number', ( $t['participants_number'] ? $t['participants_number'] : '(unbekannt)' ) );
        open_td( '', $t['note'] );


      } else {
        open_td( 'colspan=11', ' ' );
      }

      $j++;
    }
    open_tr('sum');
      open_td( 'colspan=4', we('sum:','Summe:') );
      open_td( 'number', price_view( $obligation_sum ) );
      open_td( '', ' ' );
      open_td( 'colspan=8', ' ' );
      open_td( 'number', price_view( $teaching_sum ) );
      open_td( 'colspan=2', ' ' );

  }
  close_table();
}


function teachinglist_view( $filters = array(), $opts = array() ) {
  global $login_groups_ids, $choices_typeofposition;

  $filters = restrict_view_filters( $filters, 'teaching' );

  $opts = parameters_explode( $opts );
  // $do_edit = adefault( $opts, 'do_edit', 0 );
  // $edit_teaching_id = adefault( $opts, 'edit_teaching_id', 0 );
  $format = adefault( $opts, 'format', 'html' );

  $cols = array(
    'nr' => 't=on'
  , 'id' => 's=teaching_id,t=1'
  , 'yearterm' => array( 'sort' => 'CONCAT(teaching.year,teaching.term)', 'toggle' => ( isset( $filters['year'] ) && isset( $filters['term'] ) ? '0' : '1' ), 'h' => we('term','Semester') )
  , 'teacher' => array( 'toggle' => 1, 'sort' => 'CONCAT(teacher_sn,teacher_gn)', 'h' => we('teacher','Lehrender') )
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

  if( ! begin_deliverable( ( $list_id = $list_options['list_id'] ), $list_options['allow_download'] ) ) {
    return;
  }
  $teaching = sql_teaching( $filters, array( 'orderby' => $list_options['orderby_sql'] ) );

//   $sep = ' ## ';
//   switch( $format ) {
//     case 'csv':
//       echo "nr $sep Semester $sep Lehrender $sep Stelle $sep Lehrverpflichtung $sep Reduktion $sep Art $sep Veranstaltung "
//          . "$sep VVZ-Nr $sep Modul-Nr $sep SWS $sep Abhaltefaktor $sep Anrechnungsfaktor $sep Lehrende "
//          . "$sep Teilnehmer $sep im Namen von $sep Anmerkung"
//          . "\n";
//       foreach( $teaching as $t ) {
//         echo $t['nr'];
//         echo $sep . $t['term'] . ' ' . $t['year'];
//         echo $sep . $t['teacher_cn'];
//         echo $sep . $t['typeofposition'];
//         echo $sep . $t['teaching_obligation'];
//         echo $sep . $t['teaching_reduction'];
//         echo $sep . $t['course_type'];
//         echo $sep . $t['course_title'];
//         echo $sep . $t['course_number'];
//         echo $sep . $t['module_number'];
//         echo $sep . $t['hours_per_week'];
//         echo $sep . $t['teaching_factor'];
//         echo $sep . $t['credit_factor'];
//         echo $sep . $t['teachers_number'];
//         echo $sep . ( $t['participants_number'] ? $t['participants_number'] : we('unknown','unbekannt') );
//         echo $sep . $t['signer_cn'];
//         echo $sep;
//           echo $t['note'] . ' ';
//           if( $t['teaching_reduction'] ) {
//             echo "Reduktionsgrund: ".$t['teaching_reduction_reason'];
//           }
//           if( $t['teachers_number'] > 1 ) {
//             echo "Mit-Lehrende: ".$t['co_teacher'];
//           }
//         echo "\n";
//       }
//       return;
//     default:
//       break;
//   }

  if( ! $teaching ) {
    open_div( '', we('No entries available', 'Keine Eintraege vorhanden' ) );
    return;
  }
  $count = count( $teaching );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  $list_options['class'] = 'list';
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
          open_list_cell( 'id', $teaching_id, 'class=number' );
        }
        open_list_cell( 'yearterm', "{$t['term']} {$t['year']}" );
          if( $t['extern'] ) {
            $s = html_div( '', 'extern:' )
                 . html_div( 'bold', $t['extteacher_cn'] );
          } else {
            $s = html_div( '', html_alink_group( $t['teacher_groups_id'] ) )
                 . html_div( '', html_alink_person( $t['teacher_people_id'] ) );
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
        } else {
          open_list_cell( 'course'
          , html_div( 'quads bold left', $t['course_title'] )
            . html_div( '',
                html_span( 'quad oneline', we('type: ','Art: ').$t['course_type'] )
                . html_span( 'quad oneline', we('number: ','Nummer: ').$t['course_number'] )
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
        , html_div( '', html_alink_group( $t['signer_groups_id'] ) )
          . html_div( '', html_alink_person( $t['signer_people_id'] ) )
        );
        if( isset( $cols['creator'] ) ) {
          open_list_cell( 'creator', html_alink_person( $t['creator_people_id'] ) );
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

  end_deliverable( $list_id );
}

?>
