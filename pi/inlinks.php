<?php
// default options for windows (for javascript window.open()-call)
// - (these are really constants, but php doesn't not support array-valued constants)
// - this file may be included from inside a function (from doku-wiki!), so we need `global':
//
global $large_window_options, $small_window_options;
$large_window_options = array(
    'dependent' => 'yes'
  , 'toolbar' => 'yes'
  , 'menubar' => 'yes'
  , 'location' => 'yes'
  , 'scrollbars' => 'yes'
  , 'resizable' => 'yes'
  , 'width' => '1024'
  , 'height' => '768'
);
$small_window_options = array(
    'dependent' => 'yes'
  , 'toolbar' => 'no'
  , 'menubar' => 'no'
  , 'location' => 'no'
  , 'scrollbars' => 'yes'
  , 'resizable' => 'yes'
  , 'width' => '680'
  , 'height' => '560'
  , 'left' => '80'
  , 'top' => '180'
);

// script_defaults: define default parameters and default options for views:
//
function script_defaults( $target_script, $enforced_target_window = '', $target_thread = 1 ) {
  global $large_window_options, $small_window_options;
  $parameters = array();
  $options = $large_window_options;

  switch( strtolower( $target_script ) ) {
    //
    // Anzeige im Hauptfenster (aus dem Hauptmenue) oder in "grossem" Fenster moeglich:
    //
    case 'menu':
    case 'index':
      $parameters['script'] = 'menu';
      $parameters['window'] = 'menu';
      $parameters['text'] = 'zur&uuml;ck';
      $parameters['title'] = 'Hauptmenue...';
      $target_window = ( $enforced_target_window ? $enforced_target_window : 'menu' );
      if( ( $target_window == 'menu' ) && ( $target_thread == 1 ) ) {
        // menu in main browser window:
        $options = $large_window_options;
      } else {
        // detached menu in small window:
        $options = array_merge( $small_window_options, array( 'width' => '320' ) );
      }
      break;
    case 'peoplelist':
      $parameters['script'] = 'peoplelist';
      $parameters['window'] = 'peoplelist';
      $parameters['text'] = we('People','Personen');
      $parameters['title'] = we('People...','Personen...');
      $parameters['class'] = 'people';
      $options = $large_window_options;
      break;
    case 'groupslist':
      $parameters['script'] = 'groupslist';
      $parameters['window'] = 'groupslist';
      $parameters['text'] = we('Groups','Gruppen');
      $parameters['title'] = we('Groups...','Gruppen...');
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'eventslist':
      $parameters['script'] = 'eventslist';
      $parameters['window'] = 'eventslist';
      $parameters['text'] = we('Events','Veranstaltungen');
      $parameters['title'] = we('Events...','Veranstaltungen...');
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'examslist':
      $parameters['script'] = 'examslist';
      $parameters['window'] = 'examslist';
      $parameters['text'] = we('Examination dates','Pr&uuml;fungstermine');
      $parameters['title'] = we('Examination dates','Pr&uuml;fungstermine...');
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'positionslist':
      $parameters['script'] = 'positionslist';
      $parameters['window'] = 'positionslist';
      $parameters['text'] = we('Positions & Topics','Stellen & Themen');
      $parameters['title'] = we('open positions & topics for theses','offene Stellen und Themen f&uuml;r Ba/Ma/PhD-Arbeiten...');
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'publicationslist':
      $parameters['script'] = 'publicationslist';
      $parameters['window'] = 'publicationslist';
      $parameters['text'] = we('Publications','Veröffntlichungen');
      $parameters['title'] = we('Publications','Veröffentlichungen...');
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'surveyslist':
      $parameters['script'] = 'surveyslist';
      $parameters['window'] = 'surveyslist';
      $parameters['text'] = we('Surveys','Umfragen');
      $parameters['title'] = we('Surveys','Umfragen...');
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'teachinglist':
      $parameters['script'] = 'teachinglist';
      $parameters['window'] = 'teachinglist';
      $parameters['text'] = we('Teaching','Lehrerfassung');
      $parameters['title'] = we('Teaching','Lehrerfassung...');
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'teaching_edit':
      $parameters['script'] = 'teaching_edit';
      $parameters['window'] = 'teaching_edit';
      $parameters['text'] = we('Edit Teaching','Lehrerfassung edieren');
      $parameters['title'] = we('Edit Teaching','Lehrerfassung edieren...');
      $parameters['class'] = 'edit';
      $options = $large_window_options;
      break;
    case 'teachinganon':
      $parameters['script'] = 'teachinganon';
      $parameters['window'] = 'teachinganon';
      $parameters['text'] = we('Teaching anonymized','Lehrerfassung anonymisiert');
      $parameters['title'] = we('Teaching anonymized','Lehrerfassung anonymisiert...');
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'config':
    case 'configuration':
      $parameters['script'] = 'configuration';
      $parameters['window'] = 'configuration';
      $parameters['text'] = 'Konfiguration';
      $parameters['title'] = 'Konfiguration...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'admin':
    case 'maintenance':
      $parameters['script'] = 'maintenance';
      $parameters['window'] = 'maintenance';
      $parameters['text'] = 'Admin';
      $parameters['title'] = 'Admin-Funktionen...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'references':
      $parameters['script'] = 'references';
      $parameters['window'] = 'references';
      $parameters['text'] = we('references','Verweise');
      $parameters['title'] = we('references...','Verweise...');
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'logbook':
      $parameters['script'] = 'logbook';
      $parameters['window'] = 'logbook';
      $parameters['text'] = we('logbook','Logbuch');
      $parameters['title'] = we('server log...','Server Logbuch...');
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'login':
      $parameters['script'] = 'login';
      $parameters['window'] = 'menu';
      $parameters['text'] = 'Login';
      $parameters['title'] = 'Login...';
      $parameters['class'] = 'record';
      $options = $large_window_options;
      break;
    //
    // "kleine" Fenster:
    //
    case 'person_view':
      $parameters['script'] = 'person_view';
      $parameters['window'] = 'person';
      $parameters['text'] = we('person','Person');
      $parameters['title'] = we('person...','Person...');
      $parameters['class'] = 'href inlink';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    case 'person_edit':
      $parameters['script'] = 'person_edit';
      $parameters['window'] = 'person';
      $parameters['text'] = we('edit person','Person bearbeiten');
      $parameters['title'] = we('edit person data...','Personendaten bearbeiten...' );
      $parameters['class'] = 'edit';
      $options = $large_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    case 'group_view':
      $parameters['script'] = 'group_view';
      $parameters['window'] = 'group';
      $parameters['text'] = we('group','gruppe');
      $parameters['title'] = we('Group...','Gruppe...');
      $parameters['class'] = 'href inlink';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '960';
      $options['height'] = '800';
      break;
    case 'group_edit':
      $parameters['script'] = 'group_edit';
      $parameters['window'] = 'group';
      $parameters['text'] = we('edit group','Gruppe bearbeiten');
      $parameters['title'] = we('edit group data...','Gruppendaten bearbeiten...');
      $parameters['class'] = 'edit';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '960';
      $options['height'] = '800';
      break;
    case 'position_view':
      $parameters['script'] = 'position_view';
      $parameters['window'] = 'position';
      $parameters['text'] = we('position/topic','Stelle/Thema');
      $parameters['title'] = we('Posittion/Topic...','Stelle/Thema...');
      $parameters['class'] = 'href inlink';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '960';
      $options['height'] = '720';
      break;
    case 'position_edit':
      $parameters['script'] = 'position_edit';
      $parameters['window'] = 'position';
      $parameters['text'] = we('edit position/thesis','Stelle/Thema bearbeiten');
      $parameters['title'] = we('edit position/topic...','Stelle/Thema bearbeiten...');
      $parameters['class'] = 'edit';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '960';
      $options['height'] = '720';
      break;
    case 'room_view':
      $parameters['script'] = 'room_view';
      $parameters['window'] = 'roon';
      $parameters['text'] = we('room','Raum');
      $parameters['title'] = we('room...','Raum...');
      $parameters['class'] = 'hread inlink';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '960';
      $options['height'] = '720';
      break;
    case 'room_edit':
      $parameters['script'] = 'room_edit';
      $parameters['window'] = 'roon';
      $parameters['text'] = we('edit room','Raum bearbeiten');
      $parameters['title'] = we('edit room...','Raum bearbeiten...');
      $parameters['class'] = 'edit';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '960';
      $options['height'] = '720';
      break;
    case 'exam_view':
      $parameters['script'] = 'exam_view';
      $parameters['window'] = 'exam';
      $parameters['text'] = we('exam date','Prüfungstermin');
      $parameters['title'] = we('exam date...','Prüfungstermin ...');
      $parameters['class'] = 'href inlink';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '800';
      $options['height'] = '720';
      break;
    case 'exam_edit':
      $parameters['script'] = 'exam_edit';
      $parameters['window'] = 'exam';
      $parameters['text'] = we('edit exam date','Prüfungstermin bearbeiten');
      $parameters['title'] = we('edit exam date...','Prüfungstermin bearbeiten...');
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '800';
      $options['height'] = '720';
      break;
    case 'survey_view':
      $parameters['script'] = 'survey_view';
      $parameters['window'] = 'survey';
      $parameters['text'] = we('edit survey','Umfrage bearbeiten...');
      $parameters['title'] = we('edit survey...','Umfrage bearbeiten...');
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '800';
      $options['height'] = '720';
      break;
    case 'survey_edit':
      $parameters['script'] = 'survey_edit';
      $parameters['window'] = 'survey';
      $parameters['text'] = we('edit survey','Umfrage bearbeiten...');
      $parameters['title'] = we('edit survey...','Umfrage bearbeiten...');
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '800';
      $options['height'] = '720';
      break;
    case 'event_view':
      $parameters['script'] = 'event_view';
      $parameters['window'] = 'event';
      $parameters['text'] = we('event','Veranstaltung');
      $parameters['title'] = we('event...','Veranstaltung...');
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '800';
      $options['height'] = '720';
      break;
    case 'event_edit':
      $parameters['script'] = 'event_edit';
      $parameters['window'] = 'event';
      $parameters['text'] = we('edit event','Veranstaltung bearbeiten');
      $parameters['title'] = we('edit event data...','Veranstaltungsdaten bearbeiten...');
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '800';
      $options['height'] = '720';
      break;
    case 'publication_view':
      $parameters['script'] = 'publication_view';
      $parameters['window'] = 'publication';
      $parameters['text'] = we('publication','Veröffentlichung');
      $parameters['title'] = we('publication...','Veröffentlichung...');
      $parameters['class'] = 'href inlink';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '800';
      $options['height'] = '720';
      break;
    case 'publication_edit':
      $parameters['script'] = 'publication_edit';
      $parameters['window'] = 'publication';
      $parameters['text'] = we('edit publication','Veröffentlichung bearbeiten');
      $parameters['title'] = we('edit publication data...','Veröffentlichung bearbeiten...');
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '800';
      $options['height'] = '720';
      break;
    case 'logentry':
      $parameters['script'] = 'logentry';
      $parameters['window'] = 'logentry';
      $parameters['text'] = we('log entry','Logbuch Eintrag');
      $parameters['title'] = we('log entry...','Logbuch Eintrag...');
      $parameters['class'] = 'href inlink';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    //
    case 'download':
      $parameters['script'] = 'download';
      $parameters['window'] = 'download';
      $parameters['text'] = 'download';
      $parameters['title'] = 'download...';
      $parameters['class'] = 'file';
      break;
    default:
      logger( "unexpected target script: [$target_script]", LOG_LEVEL_ERROR, LOG_FLAG_CODE | LOG_FLAG_INPUT, 'links' );
      return NULL;
  }
  return array( 'parameters' => $parameters, 'options' => $options );
}

$cgi_get_vars = array(
  'people_id' => array( 'type' => 'u' )
, 'affiliations_id' => array( 'type' => 'u' )
, 'groups_id' => array( 'type' => 'u' )
, 'exams_id' => array( 'type' => 'u' )
, 'teaching_id' => array( 'type' => 'u' )
, 'offices_id' => array( 'type' => 'u' )
, 'positions_id' => array( 'type' => 'u' )
, 'publications_id' => array( 'type' => 'u' )
, 'rooms_id' => array( 'type' => 'u' )
, 'degree_id' => array( 'type' => 'u' )
, 'programme_id' => array( 'type' => 'u' )
, 'item' => array( 'type' => 'w' )
, 'term' => array( 'type' => 'W1', 'pattern' => '/^[WS0]$/', 'default' => '0' )
, 'id' => array( 'type' => 'u' )
, 'year' => array( 'type' => 'u4', 'format' => '%04u' )
, 'month' => array( 'type' => 'u2', 'format' => '%02u' )
, 'day' => array( 'type' => 'u2', 'format' => '%02u' )
);

$cgi_vars = array(
  'hour' => array( 'type' => 'u2', 'format' => '%02u' )
, 'minute' => array( 'type' => 'u2', 'format' => '%02u' )
);

?>
