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
    case 'personen':
      $parameters['script'] = 'personen';
      $parameters['window'] = 'personen';
      $parameters['text'] = 'Mitarbeirer';
      $parameters['title'] = 'Mitarbeiter...';
      $parameters['class'] = 'people';
      $options = $large_window_options;
      break;
    case 'gruppen':
      $parameters['script'] = 'gruppen';
      $parameters['window'] = 'gruppen';
      $parameters['text'] = 'Gruppen';
      $parameters['title'] = 'Gruppen...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'veranstaltungen':
      $parameters['script'] = 'veranstaltungen';
      $parameters['window'] = 'veranstaltungen';
      $parameters['text'] = 'Veranstaltungen';
      $parameters['title'] = 'Veranstaltungen...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'pruefungen':
      $parameters['script'] = 'pruefungen';
      $parameters['window'] = 'pruefungen';
      $parameters['text'] = 'Pr&uuml;fungstermine';
      $parameters['title'] = 'Pr&uuml;fungstermine...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'bamathemen':
      $parameters['script'] = 'bamathemen';
      $parameters['window'] = 'bamathemen';
      $parameters['text'] = 'Themen';
      $parameters['title'] = 'Themen f&uuml;r Bachelor- und Masterarbeiten...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'umfragen':
      $parameters['script'] = 'umfragen';
      $parameters['window'] = 'umfragen';
      $parameters['text'] = 'Umfragen';
      $parameters['title'] = 'Umfragen...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'admin':
      $parameters['script'] = 'admin';
      $parameters['window'] = 'admin';
      $parameters['text'] = 'Admin';
      $parameters['title'] = 'Admin-Funktionen...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'logbook':
      $parameters['script'] = 'logbook';
      $parameters['window'] = 'logbook';
      $parameters['text'] = 'Logbuch';
      $parameters['title'] = 'Server Logbuch...';
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
      $parameters['text'] = 'Person';
      $parameters['title'] = 'Person...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    case 'person_edit':
      $parameters['script'] = 'person_edit';
      $parameters['window'] = 'person';
      $parameters['text'] = 'person';
      $parameters['title'] = we('edit person data...','Personendaten bearbeiten...' );
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    case 'gruppe_view':
      $parameters['script'] = 'gruppe_view';
      $parameters['window'] = 'gruppe';
      $parameters['text'] = 'Gruppe';
      $parameters['title'] = 'Gruppe...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '960';
      $options['height'] = '800';
      break;
    case 'gruppe_edit':
      $parameters['script'] = 'gruppe_edit';
      $parameters['window'] = 'gruppe';
      $parameters['text'] = 'Gruppe';
      $parameters['title'] = we('edit group data...','Gruppendaten bearbeiten...');
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '960';
      $options['height'] = '800';
      break;
    case 'bamathema_edit':
      $parameters['script'] = 'bamathema_edit';
      $parameters['window'] = 'bamathema';
      $parameters['text'] = 'Thema';
      $parameters['title'] = we('edit topic data...','Daten bearbeiten...');
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '960';
      $options['height'] = '720';
      break;
    case 'pruefung_edit':
      $parameters['script'] = 'pruefung_edit';
      $parameters['window'] = 'pruefung';
      $parameters['text'] = 'Veranstaltung';
      $parameters['title'] = we('edit exam data...','Prüfungsdaten bearbeiten...');
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '800';
      $options['height'] = '720';
      break;
    case 'umfrage_edit':
      $parameters['script'] = 'umfrage_edit';
      $parameters['window'] = 'umfrage_edit';
      $parameters['text'] = 'Umfrage...';
      $parameters['title'] = we('edit survey...','Umfrage bearbeiten...');
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '800';
      $options['height'] = '720';
      break;
    case 'veranstaltung_edit':
      $parameters['script'] = 'veranstaltung_edit';
      $parameters['window'] = 'veranstaltung';
      $parameters['text'] = 'Veranstaltung';
      $parameters['title'] = we('edit event data...','Veranstaltungsdaten bearbeiten...');
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '800';
      $options['height'] = '720';
      break;
    case 'logentry':
      $parameters['script'] = 'logentry';
      $parameters['window'] = 'logentry';
      $parameters['text'] = 'Logbuch Eintrag';
      $parameters['title'] = 'Logbuch Eintrag...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    //
    default:
      error( "undefined target script: [$target_script]" );
  }
  return array( 'parameters' => $parameters, 'options' => $options );
}

$cgi_get_vars = array(
  'people_id' => array( 'type' => 'u' )
, 'groups_id' => array( 'type' => 'u' )
, 'pruefungen_id' => array( 'type' => 'u' )
, 'bamathemen_id' => array( 'type' => 'u' )
, 'abschluss_id' => array( 'type' => 'u' )
, 'studiengang_id' => array( 'type' => 'u' )
, 'item' => array( 'type' => 'w' )
, 'id' => array( 'type' => 'u' )
);

$cgi_vars = array(
  'year' => array( 'type' => 'u4', 'format' => '%04u' )
, 'month' => array( 'type' => 'u2', 'format' => '%02u' )
, 'day' => array( 'type' => 'u2', 'format' => '%02u' )
, 'hour' => array( 'type' => 'u2', 'format' => '%02u' )
, 'minute' => array( 'type' => 'u2', 'format' => '%02u' )
);

?>
