<?php

function mainmenu_fullscreen() {
  $mainmenu[] = array( 'script' => 'peoplelist',
       'title' => we('People','Personen'),
       'text' => we('People','Personen') );
  $mainmenu[] = array( 'script' => 'groupslist',
       'title' => we('Groups','Gruppen'),
       'text' => we('Groups','Gruppen') );

  if( 0 ) {
    $mainmenu[] = array( 'script' => 'eventslist',
         'title' => we('Events','Veranstaltungen'),
         'text' => we('Events','Veranstaltungen' ) );
  }
  
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    $mainmenu[] = array( 'script' => 'examslist',
         'title' => we('Exam dates','Prüfungstermine'),
         'text' => we('Exam dates','Prüfungstermine') );
  }
  
  if( $GLOBALS['logged_in'] ) {
    $mainmenu[] = array( 'script' => 'teachinglist',
         'title' => we('Teaching','Lehrerfassung'),
         'text' => we('Teaching','Lehrerfassung') );
  }
  
  if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    $mainmenu[] = array( 'script' => 'configuration',
         'title' => we('Configuration','Konfiguration'),
         'text' => we('Configuration','Konfiguration') );
  }

  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    $mainmenu[] = array( 'script' => 'surveyslist',
         'title' => we('Surveys','Umfragen'),
         'text' => we('Surveys','Umfragen') );
  }
  
    $mainmenu[] = array( 'script' => 'positionslist',
         'title' => we('Thesis Topics','Themen Ba/Ma-Arbeiten'),
         'text' => we('Thesis Topics','Themen Ba/Ma-Arbeiten') );
    
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
      $mainmenu[] = array( 'script' => 'admin',
           'title' => 'Admin',
           'text' => 'Admin' );
      $mainmenu[] = array( 'script' => 'logbook',
           'title' => we('Logbook','Logbuch'),
           'text' => we('Logbook','Logbuch') );
  }

  foreach( $mainmenu as $h ) {
    open_tr();
      open_td( '', inlink( $h['script'], array(
        'text' => $h['text'], 'title' => $h['title'] , 'class' => 'bigbutton'
      ) ) );
  }
  open_tr('medskip');
    if( $GLOBALS['logged_in'] ) {
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

function peoplelist_view( $filters = array(), $opts = true ) {
  global $script, $login_people_id, $people_flag_text;
  
  $filters = restrict_view_filters( $filters, 'people' );

  $opts = handle_list_options( $opts, 'people', array(
      'id' => 's=people_id,t,h=id'
    , 'nr' => 't=1'
    , 'gn' => 's,t,h='.we('first names','Vornamen')
    , 'sn' => 's,t,h='.we('last name','Nachmane')
    , 'title' => 's,t,h='.we('title','Titel')
    , 'jperson' => 's,t'
    , 'flags' => 't'
    , 'uid' => 's,t'
    , 'primary_roomnumber' => 's,t,h='.we('room','Raum')
    , 'primary_telephonenumber' => 's,t,h='.we('phone','Telefon')
    , 'primary_mail' => 's,t,h='.we('mail','Email')
    , 'groups' => 's=primary_groupname,t,h='.we('group','Gruppe')
    , 'actions' => 't'
  ) );

  if( ! ( $people = sql_people( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', we('no such people','Keine Personen vorhanden') );
    return;
  }
  $count = count( $people );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $selected_people_id = adefault( $GLOBALS, $opts['select'], 0 );
  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_tr('listhead selectable');
    open_list_head( 'nr' );
    if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
      open_list_head( 'id' );
      open_list_head( 'flags' );
      open_list_head( 'uid' );
    }
    open_list_head( 'title', we('title','Titel') );
    open_list_head( 'gn', we('first names','Vornamen') );
    open_list_head( 'sn', we('last name','Nachname') );
    open_list_head( 'primary_roomnumber', we('room','Raum') );
    open_list_head( 'primary_telephonenumber', we('phone','Telefon') );
    open_list_head( 'primary_mail', 'Email' );
    open_list_head( 'groups', we('groups','Arbeitsgruppen') );
    open_list_head( 'actions', we('actions','Aktionen') );

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
          $glinks .= html_alink_group( $a['groups_id'], 'href quads' );
      }

      open_tr('listrow selectable');
        open_list_cell( 'nr', $person['nr'] );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_list_cell( 'id', $people_id );
          open_list_cell( 'flags' );
            for( $i = 1; isset( $people_flag_text[ $i ] ); $i <<= 1 ) {
              if( $person['flags'] & $i )
                open_div( 'center', $people_flag_text[ $i ] );
            }
          open_list_cell( 'uid', $person['uid'] );
        }
        open_list_cell( 'title', $person['title'] );
        open_list_cell( 'gn', $person['gn'] );
        open_list_cell( 'sn', inlink( 'person_view', array( 'class' => 'href', 'people_id' => $people_id, 'text' => $person['sn'] ) ) );
        open_list_cell( 'primary_roomnumber', $person['primary_roomnumber'] );
        open_list_cell( 'primary_telephonenumber', $person['primary_telephonenumber'] );
        open_list_cell( 'primary_mail', $person['primary_mail'] );
        // open_list_cell( 'primary_mail', open_span( 'obfuscated', obfuscate( $person['mail'] ) ) );
        open_list_cell( 'groups', $glinks );
        open_list_cell( 'actions' );
          if( have_priv( 'person', 'edit', $people_id ) ) {
            open_span( 'oneline', inlink( 'person_edit', "class=edit,text=,people_id=$people_id,title=".we('edit data...','bearbeiten...') ) );
          }
          // if( ( $GLOBALS['script'] == 'peoplelist' ) && ( $people_id != $login_people_id ) ) {
          //   if( have_priv( 'person', 'delete', $people_id ) ) {
          //     open_span( 'oneline', H_AMP.'nbsp;'.inlink( '!submit', "class=drop,confirm=Person loeschen?,action=deletePerson,message=$people_id" ).H_AMP.'nbsp;');
          //   }
          // }
    }
  close_table();
}


function groupslist_view( $filters = array(), $opts = true ) {

  $filters = restrict_view_filters( $filters, 'groups' );

  $opts = handle_list_options( $opts, 'groups', array(
      'id' => 's=groups_id,t=1'
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
    , 'actions' => 't'
  ) );
  if( ! ( $groups = sql_groups( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', we('no such groups','Keine Gruppen vorhanden') );
    return;
  }
  $count = count( $groups );

  // probably don't need limits here for the time being:
  //
  // $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = false;

  $selected_groups_id = adefault( $GLOBALS, $opts['select'], 0 );
  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_tr('listhead');
    open_list_head( 'nr' );
    if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
      open_list_head( 'id' );
    open_list_head( 'acronym' );
    open_list_head( 'cn', we('Name of group','Name der Gruppe') );
    open_list_head( 'status' );
    open_list_head( 'head', we('group leader','Gruppenleiter') );
    open_list_head( 'secretary', we('secretary','Sekretariat') );
    open_list_head( 'URL' );
    open_list_head( 'actions', we('actions','Aktionen') );
    foreach( $groups as $g ) {
      $groups_id = $g['groups_id'];
      open_tr('listrow');
        open_list_cell( 'nr', $g['nr'], 'right' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
          open_list_cell( 'id', $groups_id, 'right' );
        open_list_cell( 'acronym', html_alink_group( $groups_id ) );
        open_list_cell( 'cn', $g['cn_we'] );
        open_list_cell( 'status', ( $g['flags'] & GROUPS_FLAG_INSTITUTE ? 'institut' : 'extern' ) );
        open_list_cell( 'head', ( $g['head_people_id'] ? html_alink_person( $g['head_people_id'] ) : '' ) );
        open_list_cell( 'secretary', ( $g['secretary_people_id'] ? html_alink_person( $g['secretary_people_id'] ) : '' ) );
        open_list_cell( 'url', ( $g['url'] ? html_alink( $g['url'], array( 'text' => $g['url'], 'target' => '_new' ) ) : ' - ' ) );
        open_list_cell( 'actions' );
          if( have_priv( 'groups', 'edit', $groups_id ) ) {
            echo inlink( 'group_edit', "class=edit,text=,groups_id=$groups_id,title=".we('edit data...','bearbeiten...') );
          }
//           if( ( $GLOBALS['script'] == 'groupslist' ) ) {
//             if( have_priv( 'groups', 'delete', $groups_id ) ) {
//               echo inlink( '!submit', "class=drop,confirm=Gruppe loeschen?,action=deleteGroup,message=$groups_id" );
//             }
//           }

    }

  close_table();
}


function positionslist_view( $filters = array(), $opts = true ) {

  $filters = restrict_view_filters( $filters, 'positions' );

  $opts = handle_list_options( $opts, 'positions', array(
      'id' => 's=positions_id,t=1'
    , 'nr' => 't=1'
    , 'cn' => 's,t=1,h='.we('title','Titel')
    , 'group' => 's=acronym,t=1,h='.we('group','Gruppe')
    , 'degree' => 's,t=1,h='.we('degree','Abschluss')
    , 'url' => 's,t=1'
    , 'actions' => 't'
  ) );
  if( ! ( $themen = sql_positions( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', we('no such posisions/topics', 'Keine Stellen/Themen vorhanden' ) );
    return;
  }
  $count = count( $themen );

  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  // $selected_positions_id = adefault( $GLOBALS, $opts['select'], 0 );
  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_tr('listhead');
    open_list_head( 'nr' );
    if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
      open_list_head( 'id' );
    open_list_head( 'cn', we('topic','Thema') );
    open_list_head( 'group', we('group','Arbeitsgruppe') );
    open_list_head( 'degree', we('degree','Abschluss') );
    open_list_head( 'URL' );
    open_list_head( 'actions', we('actions','Aktionen') );
    foreach( $themen as $t ) {
      $positions_id = $t['positions_id'];
      open_tr('listrow');
        open_list_cell( 'nr', $t['nr'], 'right' );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
          open_list_cell( 'id', $positions_id, 'right' );
        open_list_cell( 'cn', inlink( 'position_view', array( 'class' => 'href', 'text' => $t['cn'] ) ) );
        open_list_cell( 'group', ( $t['groups_id'] ? html_alink_group( $t['groups_id'] ) : ' - ' ) );
        open_list_cell( 'degree' );
          foreach( $GLOBALS['degree_text'] as $degree_id => $degree_cn ) {
            if( $t['degree'] & $degree_id )
              echo $degree_cn . ' ';
          }
        open_list_cell( 'url', ( $t['url'] ? html_alink( $t['url'], array( 'text' => $t['url'], 'target' => '_top' ) ) : ' - ' ) );
        open_list_cell( 'actions' );
          if( have_priv( 'positions', 'edit', $positions_id ) ) {
            echo inlink( 'position_edit', "class=edit,text=,positions_id=$positions_id,title=".we('edit data...','bearbeiten...') );
          }
          if( ( $GLOBALS['script'] == 'positionslist' ) ) {
            if( have_priv( 'positions', 'delete', $positions_id ) ) {
              echo inlink( '!submit', "class=drop,confirm=Thema loeschen?,action=deletePosition,message=$positions_id" );
            }
          }
    }

  close_table();
}

function examslist_view( $filters = array(), $opts = true ) {

  $filters = restrict_view_filters( $filters, 'exams' );

  $opts = handle_list_options( $opts, 'exams', array(
      'nr' => 't=1'
    , 'id' => 's=exams_id,t=1'
    , 'cn' => 't=1'
    , 'teacher' => 's=teacher_cn,t=1'
    , 'degree' => 's,t=1'
    , 'url' => 's,t=1'
    , 'actions' => 't'
  ) );
  if( ! ( $pruefungen = sql_pruefungen( $filters, 'utc,semester' ) ) ) {
    open_div( '', we('No exams', 'Keine Pruefungen vorhanden' ) );
    return;
  }
  $count = count( $pruefungen );

  // $date_start = 
}




function surveyslist_view( $filters = array(), $opts = true ) {
  $filters = restrict_view_filters( $filters, 'surveys' );
  $opts = handle_list_options( $opts, 'surveys', array(
      'nr' => 't=1'
    , 'cn' => 's,t=1,h='.we('designation','Bezeichnung')
    , 'initiator_cn' => 's,t=0,h='.we('initiator','Initiator')
    , 'ctime' => 's,t=1,h='.we('creation time','Erstellzeit')
    , 'deadline' => 's,t=1'
    , 'status' => 's=closed,t=1'
    , 'actions' => 't'
  ) );
  if( ! ( $surveys = sql_surveys( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', we('No surveys available', 'Keine Umfragen vorhanden' ) );
    return;
  }
  $count = count( $surveys );

  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_tr('listhead');
    open_list_head( 'nr' );
    open_list_head( 'cn', we('Subject','Betreff') );
    open_list_head( 'initiator_cn', we('initiated by','Initiator') );
    open_list_head( 'ctime', we('start','Beginn') );
    open_list_head( 'deadline', we('deadline','Einsendeschluss') );
    open_list_head( 'closed', we('status','Status') );
    open_list_head( 'actions', we('actions','Aktionen') );
    foreach( $surveys as $s ) {
      $surveys_id = $s['surveys_id'];
      open_tr('listrow');
        open_list_cell( 'nr', $s['nr'], 'right' );
        open_list_cell( 'cn', $s['cn'] );
        open_list_cell( 'initiator_cn', $s['initiator_cn'] );
        open_list_cell( 'ctime', date_canonical2weird( $s['ctime'] ) );
        open_list_cell( 'deadline', date_canonical2weird( $s['deadline'] ) );
        open_list_cell( 'status', $u['closed'] ? we('open','offen') : we('closed','abgeschlossen') );
        open_list_cell( 'actions' );
          if( have_priv( 'surveys', 'edit', $surveys_id ) ) {
            echo inlink( 'survey_edit', "class=edit,text=,surveys_id=$surveys_id,title=".we('edit survey...','Umfrage bearbeiten...') );
          }
          if( ( $GLOBALS['script'] == 'surveys' ) ) {
            if( have_priv( 'surveys', 'delete', $surveys_id ) ) {
              echo inlink( '!submit', "class=drop,confirm=".we('delete survey?','Umfrage loeschen?').",action=deleteSurvey,message=$surveys_id" );
            }
          }
    }
  close_table();
}

function surveysubmissions_view( $filters = array(), $opts = true ) {
  $filters = restrict_view_filters( $filters, 'surveysubmissions' );
  $opts = handle_list_options( $opts, 'surveysubmissions', array(
      'nr' => 't=1'
    , 'survey' => 's,t=1,h='.we('survey','Umfrage')
    , 'creator_cn' => 's,t=0,h='.we('submitter','Einsender')
    , 'mtime' => 's,t=1'.we('last modification','letzte Änderung')
    , 'replies' => 's,t=1,h='.we('replies','Antworten')
    , 'actions' => 't'
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
    open_list_head( 'nr' );
    open_list_head( 'survey', we('survey','Umfrage') );
    open_list_head( 'creator_cn', we('submitter','Teilnehmer') );
    open_list_head( 'mtime', we('time','Zeit') );
    open_list_head( 'replies', we('replies','Antworten') );
    open_list_head( 'actions', we('actions','Aktionen') );
    foreach( $submissions as $s ) {
      $surveysubmissions_id = $s['surveysubmissions_id'];
      open_tr('listrow');
        open_list_cell( 'nr', $s['nr'], 'right' );
        open_list_cell( 'survey', $s['cn'] );
        open_list_cell( 'creator_cn', $s['creator_cn'] );
        open_list_cell( 'mtime', date_canonical2weird( $s['mtime'] ) );
        open_list_cell( 'replies', $t['replies_count'] );
        open_list_cell( 'actions' );
          if( have_priv( 'surveysubmissions', 'edit',  $s ) ) {
            echo inlink( 'surveysubmission_edit', "class=edit,text=,surveysubmissions_id=$surveysubmissions_id,title=".we('edit data...','bearbeiten...') );
          }
          if( ( $GLOBALS['script'] == 'surveysubmissions' ) ) {
            if( have_priv( 'surveysubmissions', 'delete',  $s ) ) {
              echo inlink( '!submit', "class=drop,confirm=Teilnahme loeschen?,action=deleteSurveysubmission,message=$surveysubmissions_id" );
            }
          }
    }
  close_table();
}

function teachinganon_view( $filters ) {
  need_priv( 'teaching', 'list' );

  open_table('list oddeven');

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

    open_tr();
      open_th( 'colspan=17,style=padding:2em 0em 0em 1em;background-color:white;', $section_title );

    open_tr();
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
  $do_edit = adefault( $opts, 'do_edit', 0 );
  $edit_teaching_id = adefault( $opts, 'edit_teaching_id', 0 );
  $format = adefault( $opts, 'format', 'html' );

  $cols = array(
    'nr' => 't=1'
  , 'id' => 's=teaching_id,t=1'
  , 'yearterm' => array( 'sort' => 'CONCAT(teaching.year,teaching.term)', 'toggle' => ( isset( $filters['year'] ) && isset( $filters['term'] ) ? '0' : '1' ), 'h' => we('term','Semester') )
  , 'teacher' => 't,s=teacher_cn,h='.we('teacher','Lehrender')
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
  , 'actions' => 't'
  );
  if( have_priv( 'teaching', 'list' ) ) {
    // need this even with $edit so the sorting doesn't fail:
    $cols['creator'] = 's=creator_cn,t,h='.we('submitted by','Eintrag von');
  }
  $opts = handle_list_options( $opts, 'teaching', $cols );
  if( $do_edit ) {
    foreach( $opts['cols'] as $key => $val ) {
      $opts['cols'][ $key ]['toggle'] = 'on';
    }
    $opts['cols']['nr']['toggle'] = 'off';
    $opts['cols']['yearterm']['toggle'] = 'off';
    $opts['cols']['signer']['toggle'] = 'off';
    $opts['cols']['actions']['toggle'] = 'off';
    $opts['columns_toggled_off'] = 4;
    $cols = 9;
    if( have_priv( 'teaching', 'list' ) ) {
      $opts['cols']['creator']['toggle'] = 'off';
      $opts['columns_toggled_off'] = 5;
      $cols = 10;
    }
  }

  $teaching = sql_teaching( $filters, array( 'orderby' => $opts['orderby_sql'] ) );
  $sep = ' ## ';
  switch( $format ) {
    case 'csv':
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
      return;
    default:
      break;
  }

  if( ! $teaching && ! $do_edit ) {
    open_div( '', we('No entries available', 'Keine Eintraege vorhanden' ) );
    return;
  }
  $count = count( $teaching );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_tr('listhead');
    open_list_head( 'nr' );
    if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
      open_list_head( 'id' );
    open_list_head( 'yearterm', we('Term','Semester') );
    open_list_head( 'teacher', we('teacher','Lehrender') );
    open_list_head( 'typeofposition',
      html_tag( 'div', '', we('position','Stelle') )
      . html_tag( 'div', '', we('obligation','Lehrverpflichtung') )
    );
    open_list_head( 'teaching_reduction', we('reduction','Reduktion') );
    open_list_head( 'course', we('course','Veranstaltung') );
    open_list_head( 'hours_per_week',
      html_tag( 'div', '', we('hours per week','SWS') )
    );
    open_list_head( 'teaching_factor',
      html_tag( 'div', '', we('teaching factor','Abhaltefaktor') )
      . html_tag( 'div', '', we('credit factor','Anrechnungsfaktor') )
    );
    open_list_head( 'teachers_number', we('teachers','Lehrende') );
    open_list_head( 'participants_number', we('participants','Teilnehmer') );
    open_list_head( 'signer', we('signed by','im Namen von') );
    if( isset( $cols['creator'] ) ) {
      open_list_head( 'creator', we('submitted by','Eintrag von') );
    }
    open_list_head( 'note', we('note','Anmerkung') );
    open_list_head( 'actions', we('actions','Aktionen') );

    foreach( $teaching  as $t ) {
      $teaching_id = $t['teaching_id'];
      if( $teaching_id === $edit_teaching_id )
        continue;
      if( $t['nr'] < $limits['limit_from'] )
        continue;
      if( $t['nr'] > $limits['limit_to'] )
        break;

      open_tr('listrow');
        open_list_cell( 'nr', $t['nr'] );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) )
          open_list_cell( 'id', $teaching_id );
        open_list_cell( 'yearterm', "{$t['term']} {$t['year']}" );
        open_list_cell( 'teacher' );
          if( $t['extern'] ) {
            open_div( '', 'extern:' );
            open_div( 'bold', $t['extteacher_cn'] );
          } else {
            open_div( '', html_alink_group( $t['teacher_groups_id'] ) );
            open_div( '', html_alink_person( $t['teacher_people_id'] ) );
          }
        open_list_cell( 'typeofposition' );
          // open_div( 'center', adefault( $choices_typeofposition, $t['typeofposition'], we('unknown','unbekannt') ) );
          open_div( 'center', $t['typeofposition'] );
          open_div( 'center', $t['teaching_obligation'] );
        // open_list_cell( 'teaching_obligation', $t['teaching_obligation'] );
        open_list_cell( 'teaching_reduction' );
          open_div( 'center', $t['teaching_reduction'] );
          // open_div( 'left', $t['teaching_reduction_reason'] );
        if( $t['course_type'] === 'X' ) { // sabbatical
          open_list_cell( 'course', we(' - sabbatical -','- freigestellt -'), 'colspan=5' );
        } else {
          open_list_cell( 'course' );
            open_div( 'quads bold left', $t['course_title'] );
            open_div();
              open_span( 'quad oneline', we('type: ','Art: ').$t['course_type'] );
              open_span( 'quad oneline', we('number: ','Nummer: ').$t['course_number'] );
              open_span( 'qquads oneline', we('module: ','Modul: ').$t['module_number'] );
            close_div();
          open_list_cell( 'hours_per_week' );
            open_div( 'center', $t['hours_per_week'] );
          open_list_cell( 'teaching_factor' );
            open_div( 'center', $t['teaching_factor'] );
            open_div( 'center', $t['credit_factor'] );
          open_list_cell( 'teachers_number' );
            open_div( 'center', $t['teachers_number'] );
          open_list_cell( 'participants_number', $t['participants_number'] ? $t['participants_number'] : we('unknown','unbekannt') );
        }
        open_list_cell( 'signer' );
          open_div( '', html_alink_group( $t['signer_groups_id'] ) );
          open_div( '', html_alink_person( $t['signer_people_id'] ) );
        if( isset( $cols['creator'] ) ) {
          open_list_cell( 'creator', html_alink_person( $t['creator_people_id'] ) );
        }
        open_list_cell( 'note' );
          open_div( '', $t['note'] );
          if( $t['teaching_reduction'] > 0 ) {
            open_div( 'left', we('reduction: ','Reduktion: ') . $t['teaching_reduction_reason'] );
          }
          if( $t['teachers_number'] > 1 ) {
            open_div( 'left', we('co-teachers: ','Mit-Lehrende: ') . $t['co_teacher'] );
          }
        open_list_cell( 'actions' );
          if( ! $do_edit ) {
            if( ( $GLOBALS['script'] == 'teachinglist' ) ) {
              if( have_priv( 'teaching', 'edit', $t ) ) {
                echo inlink( 'teachinglist', array(
                  'class' => 'edit', 'text' => '', 'teaching_id' => $teaching_id
                , 'title' => we('edit data...','bearbeiten...')
                , 'options' => $GLOBALS['options'] | OPTION_TEACHING_EDIT
                ) );
              }
              // if( have_priv( 'teaching', 'delete',  $t ) ) {
              //   echo inlink( '!submit', "class=drop,action=deleteTeaching,message=$teaching_id,confirm=".we('delete entry?','Eintrag löschen?') );
              // }
            }
          }

    }
  close_table();
}

?>
