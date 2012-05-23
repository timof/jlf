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
  global $script, $login_people_id;
  
  $filters = restrict_view_filters( $filters, 'people' );

  $opts = handle_list_options( $opts, 'people', array(
      'gn' => 's,t', 'sn' => 's,t', 'title' => 's,t'
    , 'jperson' => 's,t', 'uid' => 's,t'
    , 'primary_roomnumber' => 's,t,h='.we('room','Raum')
    , 'primary_telephonenumber' => 's,t,h='.we('phone','Telefon')
    , 'primary_mail' => 's,t'
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

      open_tr( 'selectable' );
        open_list_cell( 'nr', $person['nr'] );
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

  $filters = restrict_view_filters( $filters, 'groups' );

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
        open_list_cell( 'acronym', html_alink_group( $groups_id ) );
        open_list_cell( 'cn', $g['cn_we'] );
        open_list_cell( 'head', ( $g['head_people_id'] ? html_alink_person( $g['head_people_id'] ) : '' ) );
        open_list_cell( 'secretary', ( $g['secretary_people_id'] ? html_alink_person( $g['secretary_people_id'] ) : '' ) );
        open_list_cell( 'url', ( $g['url'] ? html_alink( $g['url'], array( 'text' => $g['url'], 'target' => '_new' ) ) : ' - ' ) );
        open_list_cell( 'actions' );
          if( have_priv( 'groups', 'edit', $groups_id ) ) {
            echo inlink( 'group_edit', "class=edit,text=,groups_id=$groups_id,title=".we('edit data...','bearbeiten...') );
          }
          if( ( $GLOBALS['script'] == 'groupslist' ) ) {
            if( have_priv( 'groups', 'delete', $groups_id ) ) {
              echo inlink( '!submit', "class=drop,confirm=Gruppe loeschen?,action=deleteGroup,message=$groups_id" );
            }
          }

    }

  close_table();
}


function positionslist_view( $filters = array(), $opts = true ) {

  $filters = restrict_view_filters( $filters, 'positions' );

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
  $filters = restrict_view_filters( $filters, 'surveysubmissions' );
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
  global $login_groups_ids;

  $filters = restrict_view_filters( $filters, 'teaching' );

  if( ( $edit = adefault( $opts, 'edit', false ) ) ) {
    $edit_teaching_id = adefault( $edit, 'teaching_id', 0 );
    // debug( $edit['course_title'], 'course_title' );
    // debug( $GLOBALS['login_groups_ids'], 'login_groups_ids' );
  }

  $cols = array(
    'nr' => 't=1'
  , 'yearterm' => array( 'sort', 'toggle' => ( isset( $filters['year'] ) && isset( $filters['term'] ) ? '0' : '1' ) )
  , 'teacher' => 't,s=teacher_cn'
  , 'typeofposition' => 's,t'
//    , 'teaching_obligation' => 's,t'
  , 'teaching_reduction' => 's,t'
  , 'course' => 's=course_number,t'
  , 'hours_per_week' => 's,t'
  , 'teaching_factor' => 's,t'
//    , 'credit_factor' => 's,t'
  , 'teachers_number' => 's,t'
  , 'participants_number' => 's,t'
  , 'note' => 't'
  , 'signer' => 't=0,s=signer_cn'
  , 'actions' => 't'
  );
  if( have_priv( 'teaching', 'list' ) ) {
    // need this even with $edit so the sorting doesn't fail:
    $cols['submitter'] = 's=submitter_cn,t';
  }
  $opts = handle_list_options( $opts, 'teaching', $cols );
  if( $edit ) {
    foreach( $opts['cols'] as $key => $val ) {
      $opts['cols'][ $key ]['toggle'] = 'on';
    }
    $opts['cols']['nr']['toggle'] = 'off';
    $opts['cols']['yearterm']['toggle'] = 'off';
    $opts['cols']['signer']['toggle'] = 'off';
    $opts['cols']['actions']['toggle'] = 'off';
    $opts['columns_toggled_off'] = 4;
    if( have_priv( 'teaching', 'list' ) ) {
      $opts['cols']['submitter']['toggle'] = 'off';
      $opts['columns_toggled_off'] = 5;
    }
  }

  $teaching = sql_teaching( $filters, $opts['orderby_sql'] );
  if( ! $teaching && ! $edit ) {
    open_div( '', we('No entries available', 'Keine Eintraege vorhanden' ) );
    return;
  }
  $count = count( $teaching );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = false;

  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_list_head( 'nr' );
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
    open_list_head( 'note', we('note','Anmerkung') );
    open_list_head( 'signer', we('signed by','im Namen von') );
    if( isset( $cols['submitter'] ) ) {
      open_list_head( 'submitter', we('submitted by','Eintrag von') );
    }
    open_list_head( 'actions', we('actions','Aktionen') );

    if( $edit ) {
      open_tr( 'smallskips' );
        open_list_cell( 'nr' );
//         open_list_cell( 'yearterm' );
//           open_div( 'smallskips' );
//             selector_term( $edit['term'] );
//           close_div();
//           open_div( 'smallskips' );
//             selector_year( $edit['year'] );
//           close_div();
        open_list_cell(  'teacher' );
          open_div( 'smallskips' );
            selector_groups( $edit['teacher_groups_id'] );
          close_div();
          $filters = array();;
          if( $edit['teacher_groups_id']['value'] ) {
            $filters['groups_id'] = $edit['teacher_groups_id']['value'];
            open_div( 'smallskips oneline' );
              selector_people( $edit['teacher_people_id'], array( 'filters' => $filters ) );
            close_div();
          }
        open_list_cell( 'typeofposition smallskips' );
          open_div( 'smallskips' );
            selector_typeofposition( $edit['typeofposition'] );
          close_div();
          open_div( 'smallskips' );
            selector_smallint( $edit['teaching_obligation'] );
          close_div();
        open_list_cell( 'teaching_reduction' );
          open_div( 'center smallskips' );
            echo selector_smallint( $edit['teaching_reduction'] );
            if( $edit['teaching_reduction']['value'] > 0 ) {
              open_span( 'quads', we('reason:','Grund:') );
              close_div();
              open_div( 'center smallskips' );
                echo string_element( $edit['teaching_reduction_reason'] );
          }
          close_div();
if( $edit['course_type']['value'] ) {
        open_list_cell( 'course' );
          open_div( 'oneline smallskips' );
            selector_course_type( $edit['course_type'] );
            open_span( '', 'Nr: '.string_element( $edit['course_number'] ) );
            open_span( 'quads', 'Modul: '.string_element( $edit['module_number'] ) );
          close_div();
          open_div( 'smallskips', string_element( $edit['course_title'] ) );

if( ( $edit['course_type']['value'] == 'FP' ) ) {
        open_list_cell( 'hours_per_week' );
          open_div( 'oneline smallskips' );
            selector_SWS_FP( $edit['hours_per_week'] );
          close_div();
        open_list_cell( 'teaching_factor' );
          open_div( 'oneline center smallskips', $edit['teaching_factor']['value'] );
          open_div( 'oneline center smallskips', $edit['credit_factor']['value'] );
        open_list_cell( 'teachers_number', $edit['teachers_number']['value'], 'smallskips center' );

} else {
        open_list_cell( 'hours_per_week' );
          open_div( 'oneline smallskips' );
            $edit['hours_per_week']['min'] = 1; // start selection from 1, not fractional as with FP
            if( $edit['hours_per_week']['value'] )
              $edit['hours_per_week']['value'] = (int)$edit['hours_per_week']['value'];
            selector_smallint( $edit['hours_per_week'] );
          close_div();
        open_list_cell( 'smallskips teaching_factor' );
          open_div( 'smallskips' );
            selector_smallint( $edit['teaching_factor'] );
          close_div();
          open_div( 'oneline smallskips' );
            selector_credit_factor( $edit['credit_factor'] );
          close_div();
        open_list_cell( 'teachers_number' );
          open_div( 'smallskips' );
            selector_smallint( $edit['teachers_number'] );
          close_div();
          if( $edit['teachers_number']['value'] > 1 ) {
            open_div( '', string_element( $edit['co_teacher'] ) );
          }
}
} else {
        open_list_cell( 'course', false, 'colspan=4' );
            selector_course_type( $edit['course_type'] );
}
        open_list_cell( 'participants_number' );
          open_div( 'smallskips' );
            echo int_element( $edit['participants_number'] );
          close_div();
        open_list_cell( 'note' );
          open_div( 'smallskips' );
            echo textarea_element( $edit['note'] );
          close_div();
        open_list_cell( 'actions' );

      $GLOBALS['current_table']['row_number'] = 2;
      open_tr();
        open_list_cell( 'teacher', false, 'class=oneline right smallskips,colspan=9' );
          open_div( 'smallskips' );
            open_span( 'qquads' );
              open_span( 'quadr', we( 'entry made by: ', 'Eintrag im Namen von: ' ) );
              if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
                selector_groups( $edit['signer_groups_id'] );
              } else if( count( $login_groups_ids ) != 1 ) {
                selector_groups( $edit['signer_groups_id'] , array( 'filters' => array( 'groups_id' => $login_groups_ids ) ) );
              } else {
                // debug( $edit['signer_groups_id']['value'] , 'signer_groups_id' );
                $signer_group = sql_one_group( $edit['signer_groups_id']['value'] );
                open_span( 'kbd quads bold', $signer_group['acronym'] );
              }
            close_span();
            if( ( $sgi = $edit['signer_groups_id']['value'] ) ) {
              open_span( 'qquads' );
                selector_people( $edit['signer_people_id'] , array( 'filters' => "groups_id=$sgi" ) );
              close_span();
            }

            open_span( 'qquads', inlink( 'teachinglist', array(
                'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
              , 'options' => $GLOBALS['options'] & ~OPTION_TEACHING_EDIT
            ) ) );

            open_span( 'qquads' );
              submission_button();
            close_span();
          close_div();

      open_tr( 'medskips' );
        open_list_cell( 'teacher', false, 'class=oneline center,colspan=9' );
          open_div( 'medskips', ( $edit_teaching_id ? we( 'other ',' andere ' ) : '' ) . we('existing entries:','vorhandene Eintraege:' ) );
    }

    foreach( $teaching  as $t ) {
      $teaching_id = $t['teaching_id'];
      if( $teaching_id === $edit['teaching_id']['value'] )
        continue;
      if( $t['nr'] < $limits['limit_from'] )
        continue;
      if( $t['nr'] > $limits['limit_to'] )
        break;

      open_tr();
        open_list_cell( 'nr', $t['nr'] );
        open_list_cell( 'yearterm', "{$t['term']} {$t['year']}" );
        open_list_cell( 'teacher' );
          open_div( '', $t['teacher_group_acronym'] );
          open_div( '', $t['teacher_cn'] );
        open_list_cell( 'typeofposition' );
          open_div( 'center', $t['typeofposition'] );
          open_div( 'center', $t['teaching_obligation'] );
        // open_list_cell( 'teaching_obligation', $t['teaching_obligation'] );
        open_list_cell( 'teaching_reduction' );
          open_div( 'center', $t['teaching_reduction'] );
          open_div( 'left', $t['teaching_reduction_reason'] );
        open_list_cell( 'course' );
          open_div();
            open_span( 'quad', we('number: ','Nummer: ').$t['course_number'] );
            open_span( 'qquads', we('module: ','Modul: ').$t['module_number'] );
            open_span( 'quad', we('type: ','Art: ').$t['course_type'] );
          close_div();
          open_div( 'quads bold left', $t['course_title'] );
        open_list_cell( 'hours_per_week' );
          open_div( 'center', $t['hours_per_week'] );
        open_list_cell( 'teaching_factor' );
          open_div( 'center', $t['teaching_factor'] );
          open_div( 'center', $t['credit_factor'] );
        open_list_cell( 'teachers_number' );
          open_div( 'center', $t['teachers_number'] );
          open_div( 'left', $t['co_teacher'] );
        open_list_cell( 'participants_number', $t['participants_number'] );
        open_list_cell( 'note', $t['note'] );
        open_list_cell( 'signer', $t['signer_cn'] );
        if( isset( $cols['submitter'] ) ) {
          open_list_cell( 'submitter', $t['submitter_cn'] );
        }
        open_list_cell( 'actions' );
          if( ( $GLOBALS['script'] == 'teachinglist' ) ) {
            if( have_priv( 'teaching', 'edit',  $t ) ) {
              echo inlink( 'teachinglist', array(
                'class' => 'edit', 'text' => '', 'teaching_id' => $teaching_id
              , 'title' => we('edit data...','bearbeiten...')
              , 'options' => $GLOBALS['options'] | OPTION_TEACHING_EDIT
              ) );
            }
            if( have_priv( 'teaching', 'delete',  $t ) ) {
              echo inlink( '!submit', "class=drop,action=deleteTeaching,message=$teaching_id,confirm=".we('delete entry?','Eintrag loeschen?') );
            }
          }

    }
  close_table();
}

?>
