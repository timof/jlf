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
    case 'veranstaltungen':
      $parameters['script'] = 'veranstaltungen';
      $parameters['window'] = 'veranstaltungen';
      $parameters['text'] = 'Veranstaltungen';
      $parameters['title'] = 'Veranstaltungen...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'pruefungstermine':
      $parameters['script'] = 'pruefungstermine';
      $parameters['window'] = 'pruefungstermine';
      $parameters['text'] = 'Pr&uuml;fungstermine';
      $parameters['title'] = 'Pr&uuml;fungstermine...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'themen':
      $parameters['script'] = 'themen';
      $parameters['window'] = 'themen';
      $parameters['text'] = 'Themen';
      $parameters['title'] = 'Themen f&uuml;r Bachelor- und Masterarbeiten...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    //
    // "kleine" Fenster:
    //
    case 'person':
      $parameters['script'] = 'person';
      $parameters['window'] = 'person';
      $parameters['text'] = 'person';
      $parameters['title'] = 'person...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    case 'unterkonto':
      $parameters['script'] = 'thema';
      $parameters['window'] = 'thema';
      $parameters['text'] = 'Thema';
      $parameters['title'] = 'Thema...';
      $parameters['class'] = 'browse';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '880';
      $options['height'] = '720';
      break;
    //
    default:
      error( "undefined target script: [$target_script]" );
  }
  return array( 'parameters' => $parameters, 'options' => $options );
}

$jlf_url_vars += array(
  'people_id' => 'u'
, 'pruefungen_id' => 'u'
, 'themen_id' => 'u'
);

?>
