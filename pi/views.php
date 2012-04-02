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
      'id' => 's,t=0'
    , 'gn' => 's,t', 'sn' => 's,t', 'title' => 's,t'
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
        open_list_cell( 'sn', $person['sn'] );
        open_list_cell( 'roomnumber', $person['primary_roomnumber'] );
        open_list_cell( 'telephonenumber', $person['primary_telephonenumber'] );
        open_list_cell( 'mail', $person['primary_mail'] );
        // open_list_cell( 'mail', open_span( 'obfuscated', obfuscate( $person['mail'] ) ) );
        open_list_cell( 'group', $glinks );
        open_list_cell( 'aktionen' );
          echo inlink( 'person', "class=edit,text=,people_id=$people_id,title=".we('edit data...','bearbeiten...') );
          if( ( $GLOBALS['script'] == 'personen' ) && ( $people_id != $login_people_id ) ) {
            echo inlink( '!submit', "class=drop,confirm=Person loeschen?,action=deletePerson,message=$people_id" );
          }
    }
  close_table();
}

function person_view( $people_id ) {
  $person = sql_people( $people_id );
  open_table( 'list oddeven' );
    if( $person['jperson'] ) {
      open_tr();
        open_td( '', '', 'Firma:' );
        open_td( '', '', $person['cn'] );
      open_tr();
        open_td( '', '', 'Ansprechpartner:' );
        open_td( '', '', "{$person['title']} {$person['vorname']} {$person['nachname']}" );
    } else {
      open_tr();
        open_td( '', '', 'Anrede:' );
        open_td( '', '', $person['title'] );
      open_tr();
        open_td( '', '', 'Vorname:' );
        open_td( '', '', $person['vorname'] );
      open_tr();
        open_td( '', '', 'Nachname:' );
        open_td( '', '', $person['nachname'] );
    }
    open_tr();
      open_td( '', '', 'Email:' );
      open_td( '', '', $person['email'] );
    open_tr();
      open_td( '', '', 'Telefon:' );
      open_td( '', '', $person['telephonenumber'] );
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
        open_list_cell( 'cn', $g['cn'] );
        open_list_cell( 'head', ( $g['head_people_id'] ? html_alink_person( $g['head_people_id'] ) : '' ) );
        open_list_cell( 'secretary', ( $g['secretary_people_id'] ? html_alink_person( $g['secretary_people_id'] ) : '' ) );
        open_list_cell( 'url', ( $g['url'] ? html_alink( $g['url'], array( 'text' => $g['url'], 'target' => '_new' ) ) : ' - ' ) );
        open_list_cell( 'aktionen' );
          echo inlink( 'gruppe', "class=edit,text=,groups_id=$groups_id,title=".we('edit data...','bearbeiten...') );
          if( ( $GLOBALS['script'] == 'gruppen' ) ) {
            echo inlink( '!submit', "class=drop,confirm=Gruppe loeschen?,action=deleteGroup,message=$groups_id" );
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
        open_list_cell( 'cn', $t['cn'] );
        open_list_cell( 'gruppe', ( $t['groups_id'] ? html_alink_gruppe( $t['groups_id'] ) : ' - ' ) );
        open_list_cell( 'abschluss' );
          foreach( $GLOBALS['abschluss_text'] as $abschluss_id => $abschluss_cn ) {
            if( $t['abschluss'] & $abschluss_id )
              echo $abschluss_cn . ' ';
          }
        open_list_cell( 'url', ( $t['url'] ? html_alink( $t['url'], array( 'text' => $t['url'], 'target' => '_top' ) ) : ' - ' ) );
        open_list_cell( 'aktionen' );
          echo inlink( 'bamathema', "class=edit,text=,bamathemen_id=$bamathemen_id,title=".we('edit data...','bearbeiten...') );
          if( ( $GLOBALS['script'] == 'bamathemen' ) ) {
            echo inlink( '!submit', "class=drop,confirm=Thema loeschen?,action=deleteBamathema,message=$bamathemen_id" );
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


?>
