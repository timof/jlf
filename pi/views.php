<?php

// main menu
//

$mainmenu = array();



$mainmenu[] = array( 'script' => "personen",
     "title" => "Personen",
     "text" => "Personen" );


$mainmenu[] = array( 'script' => "logbook",
     "title" => "Logbuch",
     "text" => "Logbuch" );



function mainmenu_fullscreen() {
  global $mainmenu;
  foreach( $mainmenu as $h ) {
    open_tr();
      open_td( '', "colspan='2'", inlink( $h['script'], array(
        'text' => $h['text'], 'title' => $h['title'] , 'class' => 'bigbutton'
      ) ) );
  }
}

function mainmenu_header() {
  global $mainmenu;
  foreach( $mainmenu as $h ) {
    open_li( '', '', inlink( $h['script'], array(
      'text' => $h['text'], 'title' => $h['title'] , 'class' => 'href'
    ) ) );
  }
}


function window_title() {
  return $GLOBALS['window'] . '/' . $GLOBALS['thread'] .'/'. $GLOBALS['login_session_id'];
}



// people:
//

function people_view( $filters = array(), $opts = true ) {
  global $script, $login_people_id;

  $opts = handle_list_options( $opts, 'people', array(
      'id' => 's,t=0'
    , 'cn' => 's,t=0', 'gn' => 's,t', 'sn' => 's,t', 'title' => 's,t'
    , 'phone' => 's=telephonenumber,t', 'mail' => 's,t'
    , 'jperson' => 's,t', 'uid' => 's,t', 'room' => 's,t'
    , 'group' => 's,t'
    , 'aktionen' => 't'
  ) );

  if( ! ( $people = sql_people( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'Keine Personen vorhanden' );
    return;
  }
  $count = count( $people );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $selected_people_id = adefault( $GLOBALS, $opts['select'], 0 );
  open_table( 'list hfill oddeven', '', $opts );
    open_list_head( 'nr' );
    open_list_head( 'id' );
    open_list_head( 'cn' );
    open_list_head( 'title', 'Titel' );
    open_list_head( 'gn', 'Vorname' );
    open_list_head( 'sn', 'Nachname' );
    open_list_head( 'phone', 'Telefon' );
    open_list_head( 'mail', 'Email' );
    open_list_head( 'group', 'Arbeitsgruppe' );
    open_list_head( 'room', 'Raum' );
    open_list_head( 'Aktionen' );

    foreach( $people as $person ) {
      if( $person['nr'] < $limits['limit_from'] )
        continue;
      if( $person['nr'] > $limits['limit_to'] )
        break;
      $people_id = $person['people_id'];
      if( $opts['select'] ) {
        open_tr( 'selectable' );
          open_td(
            'left ' .( $people_id == $selected_people_id ? 'selected' : 'unselected' )
          , "onclick=\"".inlink( '', array( 'context' => 'js', $opts['select'] => $people_id ) ) ."\";"
          , $person['nr']
          );
      } else {
        open_tr();
          open_td( 'right', '', $person['nr'] );
      }
        open_list_cell( 'id', $people_id );
        open_list_cell( 'cn', $person['cn'] );
        open_list_cell( 'title', $person['title'] );
        open_list_cell( 'gn', $person['gn'] );
        open_list_cell( 'sn', $person['sn'] );
        open_list_cell( 'phone', $person['telephonenumber'] );
        open_list_cell( 'mail', $person['mail'] );
        open_list_cell( 'group', $person['group'] );
        open_list_cell( 'aktionen' );
          echo inlink( 'person', "class=edit,text=,people_id=$people_id" );
          if( ( $script == 'personen' ) && ( $people_id != $login_people_id ) ) {
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



// logbook:
//

function logbook_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, 'log', array( 
    'nr' => 't', 'id' => 't,s=logbook_id'
  , 'session' => 't,s=sessions_id', 'timestamp' => 't,s'
  , 'thread' => 't,s', 'window' => 't,s', 'script' => 't,s'
  , 'event' => 't,s', 'note' => 't,s', 'aktionen' => 't'
  ) );

  if( ! ( $logbook = sql_logbook( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'Keine Eintraege vorhanden' );
    return;
  }
  $count = count( $logbook );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list hfill oddeven', '', $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'session' );
      open_list_head( 'timestamp' );
      open_list_head( 'thread', "<div>thread</div><div class='small'>parent</div>" );
      open_list_head( 'window', "<div>window</div><div class='small'>parent</div>" );
      open_list_head( 'script', "<div>script</div><div class='small'>parent</div>" );
      open_list_head( 'event' );
      open_list_head( 'note');
      // open_list_head( 'left',"rowspan='2'", 'details' );
      open_list_head( 'Aktionen' );

    foreach( $logbook as $l ) {
      if( $l['nr'] < $limits['limit_from'] )
        continue;
      if( $l['nr'] > $limits['limit_to'] )
        break;
      open_tr();
        open_list_cell( 'nr', $l['nr'], 'class=number' );
        open_list_cell( 'id', $l['logbook_id'], 'class=number' );
        open_list_cell( 'session', $l['sessions_id'], 'class=number' );
        open_list_cell( 'timestamp', $l['timestamp'], 'class=right' );
        open_list_cell( 'thread', false, 'class=center' );
          open_div( 'center', '', $l['thread'] );
          open_div( 'center small', '', $l['parent_thread'] );
        open_list_cell( 'window', false, 'class=center' );
          open_div( 'center', '', $l['window'] );
          open_div( 'center small', '', $l['parent_window'] );
        open_list_cell( 'script', false, 'class=center' );
          open_div( 'center', '', $l['script'] );
          open_div( 'center small', '', $l['parent_script'] );
        open_list_cell( 'event', $l['event'] );
        open_list_cell( 'note' );
          if( strlen( $l['note'] ) > 100 )
            $s = substr( $l['note'], 0, 100 ).'...';
          else
            $s = $l['note'];
          if( $l['stack'] )
            $s .= ' [stack]';
          echo inlink( 'logentry', array( 'class' => 'card', 'text' => $s, 'logbook_id' => $l['logbook_id'] ) );
//         open_td();
//           if( $l['stack'] ) {
//             echo inlink( 'logentry', array( 'class' => 'card', 'text' => '', 'logbook_id' => $l['logbook_id'] ) );
//           } else {
//             echo '-';
//           }
        open_list_cell( 'aktionen' );
          echo inlink( '!submit', 'class=button,text=prune,action=prune,message='. $l['logbook_id'] );
    }
  close_table();
}



?>
