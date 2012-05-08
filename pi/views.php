<?php

function mainmenu_fullscreen() {
  $mainmenu[] = array( 'script' => 'peoplelist',
       'title' => we('People','Personen'),
       'text' => we('People','Personen') );
  $mainmenu[] = array( 'script' => 'groupslist',
       'title' => we('Groups','Gruppen'),
       'text' => we('Groups','Gruppen') );

  $mainmenu[] = array( 'script' => 'eventslist',
       'title' => we('Events','Veranstaltungen'),
       'text' => we('Events','Veranstaltungen' ) );

  $mainmenu[] = array( 'script' => 'examslist',
       'title' => we('Exam dates','Prüfungstermine'),
       'text' => we('Exam dates','Prüfungstermine') );

  $mainmenu[] = array( 'script' => 'teachinglist',
       'title' => we('Teaching','Lehrerfassung'),
       'text' => we('Teaching','Lehrerfassung') );

//  $mainmenu[] = array( 'script' => 'surveyslist',
//       'title' => we('Surveys','Umfragen'),
//       'text' => we('Surveys','Umfragen') );

  $mainmenu[] = array( 'script' => 'positionslist',
       'title' => we('Thesis Topics','Themen Ba/Ma-Arbeiten'),
       'text' => we('Thesis Topics','Themen Ba/Ma-Arbeiten') );
  
  if( 1 or has_status( PERSON_STATUS_ADMIN ) ) {
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
  if( 1 or has_status( PERSON_STATUS_ADMIN ) ) {
    return $GLOBALS['window'] . '/' . $GLOBALS['thread'] .'/'. $GLOBALS['login_sessions_id'];
  } else {
    return $GLOBALS['window'] . '/' . $GLOBALS['thread'];
  }
}



// people:
//

function peoplelist_view( $filters = array(), $opts = true ) {
  global $script, $login_people_id;

  $opts = handle_list_options( $opts, 'people', array(
      'gn' => 's,t', 'sn' => 's,t', 'title' => 's,t'
    , 'jperson' => 's,t', 'uid' => 's,t', 'roomnumber' => 's,t'
    , 'telephonenumber' => 's,t', 'mail' => 's,t'
    , 'groups' => 's=primary_groupname,t'
    , 'actions' => 't'
  ) );

  if( ! ( $people = sql_people( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', we('no such people','Keine Personen vorhanden') );
    return;
  }
  $count = count( $people );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $selected_people_id = adefault( $GLOBALS, $opts['select'], 0 );
  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_list_head( 'nr' );
    open_list_head( 'title', we('title','Titel') );
    open_list_head( 'gn', we('first names','Vornamen') );
    open_list_head( 'sn', we('last name','Nachname') );
    open_list_head( 'roomnumber', we('room','Raum') );
    open_list_head( 'telephonenumber', we('phone','Telefon') );
    open_list_head( 'mail', 'Email' );
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

      open_tr( 'selectable' );
        open_list_cell( 'nr', $person['nr'] );
        open_list_cell( 'title', $person['title'] );
        open_list_cell( 'gn', $person['gn'] );
        open_list_cell( 'sn', inlink( 'person_view', array( 'class' => 'href', 'people_id' => $people_id, 'text' => $person['sn'] ) ) );
        open_list_cell( 'roomnumber', $person['primary_roomnumber'] );
        open_list_cell( 'telephonenumber', $person['primary_telephonenumber'] );
        open_list_cell( 'mail', $person['primary_mail'] );
        // open_list_cell( 'mail', open_span( 'obfuscated', obfuscate( $person['mail'] ) ) );
        open_list_cell( 'group', $glinks );
        open_list_cell( 'actions' );
          if( have_priv( 'person', 'edit', $people_id ) ) {
            echo inlink( 'person_edit', "class=edit,text=,people_id=$people_id,title=".we('edit data...','bearbeiten...') );
          }
          if( ( $GLOBALS['script'] == 'peoplelist' ) && ( $people_id != $login_people_id ) ) {
            if( have_priv( 'person', 'delete', $people_id ) ) {
              echo inlink( '!submit', "class=drop,confirm=Person loeschen?,action=deletePerson,message=$people_id" );
            }
          }
    }
  close_table();
}


function groupslist_view( $filters = array(), $opts = true ) {
  $opts = handle_list_options( $opts, 'groups', array(
      'nr' => 't=1'
    , 'cn' => 's,t=1'
    , 'acronym' => 's,t=1'
    , 'head' => 's=head_gn,t=1'
    , 'secretary' => 's=secretary_gn,t=1'
    , 'url' => 's,t=1'
    , 'actions' => 't'
  ) );
  if( ! ( $groups = sql_groups( $filters, $opts['orderby_sql'] ) ) ) {
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
    open_list_head( 'nr' );
    open_list_head( 'acronym', we('acronym','Abkürzung'  ));
    open_list_head( 'cn', we('Name of group','Name der Gruppe') );
    open_list_head( 'head', we('group leader','Gruppenleiter') );
    open_list_head( 'secretary', we('secretary','Sekretariat') );
    open_list_head( 'URL' );
    open_list_head( 'actions', we('actions','Aktionen') );
    foreach( $groups as $g ) {
      $groups_id = $g['groups_id'];
      open_tr();
        open_list_cell( 'nr', $g['nr'], 'right' );
        open_list_cell( 'acronym', $g['acronym'] );
        open_list_cell( 'cn', html_alink_group( $groups_id ) );
        open_list_cell( 'head', ( $g['head_people_id'] ? html_alink_person( $g['head_people_id'] ) : '' ) );
        open_list_cell( 'secretary', ( $g['secretary_people_id'] ? html_alink_person( $g['secretary_people_id'] ) : '' ) );
        open_list_cell( 'url', ( $g['url'] ? html_alink( $g['url'], array( 'text' => $g['url'], 'target' => '_new' ) ) : ' - ' ) );
        open_list_cell( 'actions' );
          if( have_priv( 'group', 'edit', $groups_id ) ) {
            echo inlink( 'group_edit', "class=edit,text=,groups_id=$groups_id,title=".we('edit data...','bearbeiten...') );
          }
          if( ( $GLOBALS['script'] == 'groupslist' ) ) {
            if( have_priv( 'group', 'delete', $groups_id ) ) {
              echo inlink( '!submit', "class=drop,confirm=Gruppe loeschen?,action=deleteGroup,message=$groups_id" );
            }
          }

    }
   

  close_table();
}


function positionslist_view( $filters = array(), $opts = true ) {
  $opts = handle_list_options( $opts, 'positions', array(
      'nr' => 't=1'
    , 'cn' => 's,t=1'
    , 'group' => 's=acronym,t=1'
    , 'degree' => 's,t=1'
    , 'url' => 's,t=1'
    , 'actions' => 't'
  ) );
  if( ! ( $themen = sql_positions( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', we('no such posisions/topics', 'Keine Stellen/Themen vorhanden' ) );
    return;
  }
  $count = count( $themen );

  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = false;

  // $selected_positions_id = adefault( $GLOBALS, $opts['select'], 0 );
  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_list_head( 'nr' );
    open_list_head( 'cn', we('topic','Thema') );
    open_list_head( 'group', we('group','Arbeitsgruppe') );
    open_list_head( 'degree', we('degree','Abschluss') );
    open_list_head( 'URL' );
    open_list_head( 'actions', we('actions','Aktionen') );
    foreach( $themen as $t ) {
      $positions_id = $t['positions_id'];
      open_tr();
        open_list_cell( 'nr', $t['nr'], 'right' );
        open_list_cell( 'cn', inlink( 'position_view', array( 'class' => 'href', 'text' => $t['cn'] ) ) );
        open_list_cell( 'group', ( $t['groups_id'] ? html_alink_group( $t['groups_id'] ) : ' - ' ) );
        open_list_cell( 'degree' );
          foreach( $GLOBALS['degree_text'] as $degree_id => $degree_cn ) {
            if( $t['degree'] & $degree_id )
              echo $degree_cn . ' ';
          }
        open_list_cell( 'url', ( $t['url'] ? html_alink( $t['url'], array( 'text' => $t['url'], 'target' => '_top' ) ) : ' - ' ) );
        open_list_cell( 'actions' );
          if( have_priv( 'position', 'edit', $positions_id ) ) {
            echo inlink( 'position_edit', "class=edit,text=,positions_id=$positions_id,title=".we('edit data...','bearbeiten...') );
          }
          if( ( $GLOBALS['script'] == 'positionslist' ) ) {
            if( have_priv( 'position', 'delete', $positions_id ) ) {
              echo inlink( '!submit', "class=drop,confirm=Thema loeschen?,action=deletePosition,message=$positions_id" );
            }
          }
    }

  close_table();
}

function examslist_view( $filters = array(), $opts = true ) {

  debug( $filters, 'filters' );



  $opts = handle_list_options( $opts, 'exams', array(
      'nr' => 't=1'
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
  $opts = handle_list_options( $opts, 'surveys', array(
      'nr' => 't=1'
    , 'cn' => 's,t=1'
    , 'initiator_cn' => 's,t=0'
    , 'ctime' => 's,t=1'
    , 'deadline' => 's,t=1'
    , 'status' => 's=closed,t=1'
    , 'actions' => 't'
  ) );
  if( ! ( $surveys = sql_surveys( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', we('No surveys available', 'Keine Umfragen vorhanden' ) );
    return;
  }
  $count = count( $surveys );

  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = false;

  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_list_head( 'nr' );
    open_list_head( 'cn', we('Subject','Betreff') );
    open_list_head( 'initiator_cn', we('initiated by','Initiator') );
    open_list_head( 'ctime', we('start','Beginn') );
    open_list_head( 'deadline', we('deadline','Einsendeschluss') );
    open_list_head( 'closed', we('status','Status') );
    open_list_head( 'actions', we('actions','Aktionen') );
    foreach( $surveys as $s ) {
      $surveys_id = $s['surveys_id'];
      open_tr();
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
  $opts = handle_list_options( $opts, 'surveysubmissions', array(
      'nr' => 't=1'
    , 'survey' => 's,t=1'
    , 'submitter_cn' => 's,t=0'
    , 'atime' => 's,t=1'
    , 'replies' => 's,t=1'
    , 'actions' => 't'
  ) );
  if( ! ( $submissions = sql_surveysubmissions( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', we('No submissions available', 'Keine Teilnehmer vorhanden' ) );
    return;
  }
  $count = count( $submissions );

  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = false;

  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_list_head( 'nr' );
    open_list_head( 'survey', we('survey','Umfrage') );
    open_list_head( 'submitter_cn', we('submitter','Teilnehmer') );
    open_list_head( 'atime', we('time','Zeit') );
    open_list_head( 'replies', we('replies','Antworten') );
    open_list_head( 'actions', we('actions','Aktionen') );
    foreach( $submissions as $s ) {
      $surveysubmissions_id = $s['surveysubmissions_id'];
      open_tr();
        open_list_cell( 'nr', $s['nr'], 'right' );
        open_list_cell( 'survey', $s['cn'] );
        open_list_cell( 'submitter_cn', $s['submitter_cn'] );
        open_list_cell( 'atime', date_canonical2weird( $s['atime'] ) );
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


function teachinglist_view( $filters = array(), $opts = true ) {
  $opts = handle_list_options( $opts, 'teaching', array(
      'nr' => 't=1'
    , 'yearterm' => 't=0,s'
    , 'teacher' => 't,s=teacher_cn'
    , 'typeofposition' => 's,t'
    , 'actions' => 't'
  ) );
  if( ! ( $teaching = sql_teaching( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', we('No entries available', 'Keine Eintraege vorhanden' ) );
    return;
  }
  $count = count( $teaching );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = false;

  if( ( $edit = adefault( $opts, 'edit', false ) ) ) {
    $edit_id = adefault( $edit, 'teaching_id', 0 );
  }

  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_list_head( 'nr' );
    open_list_head( 'yearterm', we('Term','Semester') );
    open_list_head( 'teacher', we('teacher','Lehrender') );
    open_list_head( 'typeofposition', we('position','Stelle') );

    open_list_head( 'actions', we('actions','Aktionen') );
    foreach( $teaching as $t ) {
      $teaching_id = $t['teaching_id'];
      if( ( $teaching_id === $edit_id ) || ( $edit_id === 0 ) ) {
        open_tr();


        $edit_id = false;
        continue;
      }
      if( $teaching['nr'] < $limits['limit_from'] )
        continue;
      if( $teaching['nr'] > $limits['limit_to'] )
        break;

      open_tr();
        open_list_cell( 'nr', $t['nr'] );
        open_list_cell( 'yearterm', "{$t['term']} {$t['year']}" );


    }
  close_table();
}

?>
