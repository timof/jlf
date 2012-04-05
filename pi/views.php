<?php

function mainmenu_fullscreen() {
  $mainmenu[] = array( 'script' => 'personen',
       'title' => we('People','Personen'),
       'text' => we('People','Personen') );
  $mainmenu[] = array( 'script' => 'gruppen',
       'title' => we('Groups','Gruppen'),
       'text' => we('Groups','Gruppen') );
  // $mainmenu[] = array( 'script' => 'veranstaltungen',
  //      'title' => 'Veranstaltungen',
  //      'text' => 'Veranstaltungen' );
  $mainmenu[] = array( 'script' => 'pruefungen',
       'title' => we('Exams','Prüfungstermine'),
       'text' => we('Exams','Prüfungstermine') );

  $mainmenu[] = array( 'script' => 'umfragen',
       'title' => we('Surveys','Umfragen'),
       'text' => we('Surveys','Umfragen') );

  $mainmenu[] = array( 'script' => 'bamathemen',
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
        'text' => we('log out', 'Abmelden')
      , 'title' => we('log out', 'Abmelden')
      , 'class' => 'bigbutton'
      , 'login' => 'logout'
      ) ) );
    } else {
      open_td( '', inlink( '', array(
        'text' => we('log in', 'Anmelden')
      , 'title' => we('log in', 'Anmelden')
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
    , 'aktionen' => 't'
  ) );

  if( ! ( $people = sql_people( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', 'Keine Personen vorhanden' );
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
          $glinks .= html_alink_gruppe( $a['groups_id'], 'href quads' );
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
        open_list_cell( 'aktionen' );
          if( have_priv( 'person', 'edit', $people_id ) ) {
            echo inlink( 'person_edit', "class=edit,text=,people_id=$people_id,title=".we('edit data...','bearbeiten...') );
          }
          if( ( $GLOBALS['script'] == 'personen' ) && ( $people_id != $login_people_id ) ) {
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
    , 'kurzname' => 's,t=1'
    , 'head' => 's=head_gn,t=1'
    , 'secretary' => 's=secretary_gn,t=1'
    , 'url' => 's,t=1'
    , 'aktionen' => 't'
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
    open_list_head( 'kurzname', 'Kuerzel' );
    open_list_head( 'cn', we('Name of group','Name der Gruppe') );
    open_list_head( 'head', we('group leader','Gruppenleiter') );
    open_list_head( 'secretary', we('secretary','Sekretariat') );
    open_list_head( 'URL' );
    open_list_head( 'aktionen', we('actions','Aktionen') );
    foreach( $groups as $g ) {
      $groups_id = $g['groups_id'];
      open_tr();
        open_list_cell( 'nr', $g['nr'], 'right' );
        open_list_cell( 'kurzname', $g['kurzname'] );
        open_list_cell( 'cn', html_alink_gruppe( $groups_id ) );
        open_list_cell( 'head', ( $g['head_people_id'] ? html_alink_person( $g['head_people_id'] ) : '' ) );
        open_list_cell( 'secretary', ( $g['secretary_people_id'] ? html_alink_person( $g['secretary_people_id'] ) : '' ) );
        open_list_cell( 'url', ( $g['url'] ? html_alink( $g['url'], array( 'text' => $g['url'], 'target' => '_new' ) ) : ' - ' ) );
        open_list_cell( 'aktionen' );
          if( have_priv( 'gruppe', 'edit', $groups_id ) ) {
            echo inlink( 'gruppe_edit', "class=edit,text=,groups_id=$groups_id,title=".we('edit data...','bearbeiten...') );
          }
          if( ( $GLOBALS['script'] == 'gruppen' ) ) {
            if( have_priv( 'gruppe', 'delete', $groups_id ) ) {
              echo inlink( '!submit', "class=drop,confirm=Gruppe loeschen?,action=deleteGroup,message=$groups_id" );
            }
          }

    }
   

  close_table();
}


function bamathemenlist_view( $filters = array(), $opts = true ) {
  $opts = handle_list_options( $opts, 'bamathemen', array(
      'nr' => 't=1'
    , 'cn' => 's,t=1'
    , 'gruppe' => 's=kurzname,t=1'
    , 'abschluss' => 's,t=1'
    , 'url' => 's,t=1'
    , 'aktionen' => 't'
  ) );
  if( ! ( $themen = sql_bamathemen( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', we('No topics available', 'Keine Themen vorhanden' ) );
    return;
  }
  $count = count( $themen );

  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = false;

  // $selected_bamathemen_id = adefault( $GLOBALS, $opts['select'], 0 );
  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_list_head( 'nr' );
    open_list_head( 'cn', we('topic','Thema') );
    open_list_head( 'gruppe', we('group','Arbeitsgruppe') );
    open_list_head( 'abschluss', we('degree','Abschluss') );
    open_list_head( 'URL' );
    open_list_head( 'aktionen', we('actions','Aktionen') );
    foreach( $themen as $t ) {
      $bamathemen_id = $t['bamathemen_id'];
      open_tr();
        open_list_cell( 'nr', $t['nr'], 'right' );
        open_list_cell( 'cn', inlink( 'bamathema_view', array( 'class' => 'href', 'text' => $t['cn'] ) ) );
        open_list_cell( 'gruppe', ( $t['groups_id'] ? html_alink_gruppe( $t['groups_id'] ) : ' - ' ) );
        open_list_cell( 'abschluss' );
          foreach( $GLOBALS['abschluss_text'] as $abschluss_id => $abschluss_cn ) {
            if( $t['abschluss'] & $abschluss_id )
              echo $abschluss_cn . ' ';
          }
        open_list_cell( 'url', ( $t['url'] ? html_alink( $t['url'], array( 'text' => $t['url'], 'target' => '_top' ) ) : ' - ' ) );
        open_list_cell( 'aktionen' );
          if( have_priv( 'bamathema', 'edit', $bamathemen_id ) ) {
            echo inlink( 'bamathema_edit', "class=edit,text=,bamathemen_id=$bamathemen_id,title=".we('edit data...','bearbeiten...') );
          }
          if( ( $GLOBALS['script'] == 'bamathemen' ) ) {
            if( have_priv( 'bamathema', 'delete', $bamathemen_id ) ) {
              echo inlink( '!submit', "class=drop,confirm=Thema loeschen?,action=deleteBamathema,message=$bamathemen_id" );
            }
          }
    }

  close_table();
}

function pruefungenlist_view( $filters = array(), $opts = true ) {

  debug( $filters, 'filters' );



  $opts = handle_list_options( $opts, 'pruefungen', array(
      'nr' => 't=1'
    , 'cn' => 't=1'
    , 'dozent' => 's=dozent_cn,t=1'
    , 'abschluss' => 's,t=1'
    , 'url' => 's,t=1'
    , 'aktionen' => 't'
  ) );
  if( ! ( $pruefungen = sql_pruefungen( $filters, 'utc,semester' ) ) ) {
    open_div( '', we('No exams', 'Keine Pruefungen vorhanden' ) );
    return;
  }
  $count = count( $pruefungen );

  // $date_start = 
}




function umfragenlist_view( $filters = array(), $opts = true ) {
  $opts = handle_list_options( $opts, 'bamathemen', array(
      'nr' => 't=1'
    , 'cn' => 's,t=1'
    , 'initiator_cn' => 's,t=0'
    , 'ctime' => 's,t=1'
    , 'deadline' => 's,t=1'
    , 'status' => 's=closed,t=1'
    , 'aktionen' => 't'
  ) );
  if( ! ( $umfragen = sql_umfragen( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', we('No surveys available', 'Keine Umfragen vorhanden' ) );
    return;
  }
  $count = count( $umfragen );

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
    open_list_head( 'aktionen', we('actions','Aktionen') );
    foreach( $umfragen as $u ) {
      $umfragen_id = $u['umfragen_id'];
      open_tr();
        open_list_cell( 'nr', $u['nr'], 'right' );
        open_list_cell( 'cn', $u['cn'] );
        open_list_cell( 'initiator_cn', $u['initiator_cn'] );
        open_list_cell( 'ctime', date_canonical2weird( $u['ctime'] ) );
        open_list_cell( 'deadline', date_canonical2weird( $u['deadline'] ) );
        open_list_cell( 'status', $u['closed'] ? we('open','offen') : we('closed','abgeschlossen') );
        open_list_cell( 'aktionen' );
          if( have_priv( 'umfragen', 'edit', $umfragen_id ) ) {
            echo inlink( 'umfrage_edit', "class=edit,text=,umfragen_id=$umfragen_id,title=".we('edit data...','bearbeiten...') );
          }
          if( ( $GLOBALS['script'] == 'umfragen' ) ) {
            if( have_priv( 'umfragen', 'delete', $umfragen_id ) ) {
              echo inlink( '!submit', "class=drop,confirm=Umfrage loeschen?,action=deleteUmfrage,message=$umfragen_id" );
            }
          }
    }
  close_table();
}

function umfrageteilnehmer_view( $filters = array(), $opts = true ) {
  $opts = handle_list_options( $opts, 'bamathemen', array(
      'nr' => 't=1'
    , 'umfrage' => 's,t=1'
    , 'umfrageteilnehmer_cn' => 's,t=0'
    , 'atime' => 's,t=1'
    , 'antworten' => 's,t=1'
    , 'aktionen' => 't'
  ) );
  if( ! ( $teilnehmer = sql_umfrageteilnehmer( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', we('No submissions available', 'Keine Teilnehmer vorhanden' ) );
    return;
  }
  $count = count( $teilnehmer );

  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = false;

  $opts['class'] = 'list hfill oddeven';
  open_table( $opts );
    open_list_head( 'nr' );
    open_list_head( 'umfrage', we('survey','Umfrage') );
    open_list_head( 'umfrageteilnehmer_cn', we('submitter','Teilnehmer') );
    open_list_head( 'atime', we('time','Zeit') );
    open_list_head( 'antworten', we('replies','Antworten') );
    open_list_head( 'aktionen', we('actions','Aktionen') );
    foreach( $teilnehmer as $t ) {
      $umfrageteilnehmer_id = $t['umfrageteilnehmer_id'];
      open_tr();
        open_list_cell( 'nr', $t['nr'], 'right' );
        open_list_cell( 'umfrage', $t['cn'] );
        open_list_cell( 'umfrageteilnehmer_cn', $t['umfrageteilnehmer_cn'] );
        open_list_cell( 'atime', date_canonical2weird( $t['atime'] ) );
        open_list_cell( 'antworten', $t['antworten_count'] );
        open_list_cell( 'aktionen' );
          echo inlink( 'umfrageteilnehmer_edit', "class=edit,text=,umfrageteilnehmer_id=$umfrageteilnehmer_id,title=".we('edit data...','bearbeiten...') );
          if( ( $GLOBALS['script'] == 'umfrageteilnehmer' ) ) {
            echo inlink( '!submit', "class=drop,confirm=Teilnahme loeschen?,action=deleteUmfrageteilnehmer,message=$umfrageteilnehmer_id" );
          }
    }
  close_table();
}



?>
