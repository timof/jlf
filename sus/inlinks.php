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
//  - script: true script name
//  - window: name of browser window for target='...' or window.open()
//  - text, title, class: default look and tooltip-help of the link
//
function script_defaults( $target_script, $enforced_target_window = '', $target_thread = 1 ) {
  global $large_window_options, $small_window_options;
  $parameters = array();
  $options = $large_window_options;

  // print_on_exit( "<!-- script_defaults: [$target_script,$enforced_target_window,$target_thread] -->" );
  switch( strtolower( $target_script ) ) {
    //
    // Anzeige im Hauptfenster (aus dem Hauptmenue) oder in "grossem" Fenster moeglich:
    //
    case 'menu':
    case 'index':
      $parameters['script'] = 'menu';
      $parameters['window'] = 'menu';
      $parameters['text'] = 'back';
      $parameters['title'] = 'back to main menu...';
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
      $parameters['text'] = 'personen';
      $parameters['title'] = 'personen...';
      $parameters['class'] = 'people';
      $options = $large_window_options;
      break;
    case 'things':
      $parameters['script'] = 'things';
      $parameters['window'] = 'things';
      $parameters['text'] = 'Gegenst&auml;nde';
      $parameters['title'] = 'Gegenst&auml;nde...';
      $parameters['class'] = 'fant';
      $options = $large_window_options;
      break;
    case 'bilanz':
    case 'bestandskonten':
      $parameters['kontenkreis'] = 'B';
      $parameters['window'] = 'bestandskonten';
      $parameters['text'] = 'Bestandskonten';
      $parameters['title'] = 'Bestandskonten (Bilanz)...';
      $parameters['class'] = 'browse';
    case 'hauptkonten': // only possible with 'self':
      $parameters['script'] = 'hauptkonten';
      $options = $large_window_options;
      break;
    case 'gvrechnung':
    case 'erfolgskonten':
      $parameters['script'] = 'hauptkonten';
      $parameters['kontenkreis'] = 'E';
      $parameters['window'] = 'erfolgskonten';
      $parameters['text'] = 'Erfolgskonten';
      $parameters['title'] = 'Erfolgskonten (GV-Rechnung)...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'buchungen':
    case 'journal':
      $parameters['script'] = 'journal';
      $parameters['window'] = 'journal';
      $parameters['text'] = 'journal';
      $parameters['title'] = 'journal...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'posten':
      $parameters['script'] = 'posten';
      $parameters['window'] = 'posten';
      $parameters['text'] = 'posten';
      $parameters['title'] = 'posten...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'unterkonten':
    case 'unterkontenliste':
      $parameters['script'] = 'unterkontenliste';
      $parameters['window'] = 'unterkonten';
      $parameters['text'] = 'unterkonten';
      $parameters['title'] = 'unterkonten...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'hauptkontenliste':
      $parameters['script'] = 'hauptkontenliste';
      $parameters['window'] = 'hauptkonten';
      $parameters['text'] = 'hauptkonten';
      $parameters['title'] = 'hauptkonten...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'geschaeftsjahre':
      $parameters['script'] = 'geschaeftsjahre';
      $parameters['window'] = 'geschaeftsjahre';
      $parameters['text'] = 'geschaeftsjahre';
      $parameters['title'] = 'geschaeftsjahre...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'darlehenliste':
      $parameters['script'] = 'darlehenliste';
      $parameters['window'] = 'darlehenliste';
      $parameters['text'] = 'darlehen';
      $parameters['title'] = 'darlehen...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'logbook':
      $parameters['script'] = 'logbook';
      $parameters['window'] = 'logbook';
      $parameters['text'] = 'logbuch';
      $parameters['title'] = 'logbuch...';
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
      $parameters['class'] = 'people';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    case 'darlehen':
      $parameters['script'] = 'darlehen';
      $parameters['window'] = 'darlehen';
      $parameters['text'] = 'darlehen';
      $parameters['title'] = 'darlehen...';
      $parameters['class'] = 'browse';
      $options = $small_window_options;
      break;
    case 'hauptkonto':
      $parameters['script'] = 'hauptkonto';
      $parameters['window'] = 'hauptkonto';
      $parameters['text'] = 'hauptkonto';
      $parameters['title'] = 'hauptkonto...';
      $parameters['class'] = 'browse';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '1000';
      $options['height'] = '720';
      break;
    case 'unterkonto':
      $parameters['script'] = 'unterkonto';
      $parameters['window'] = 'unterkonto';
      $parameters['text'] = 'unterkonto';
      $parameters['title'] = 'unterkonto...';
      $parameters['class'] = 'browse';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '1000';
      $options['height'] = '720';
      break;
    case 'buchung':
      $parameters['script'] = 'buchung';
      $parameters['window'] = 'buchung';
      $parameters['text'] = 'buchung';
      $parameters['title'] = 'buchung...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['width'] = '1800';
      $options['height'] = '920';
      break;
    case 'logentry':
      $parameters['script'] = 'logentry';
      $parameters['window'] = 'logentry';
      $parameters['text'] = 'logentry';
      $parameters['title'] = 'logentry...';
      $parameters['class'] = 'card';
      $options = $small_window_options;
      $options['height'] = '1200';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    //
    default:
      error( "undefined target script: [$target_script]" );
  }
  return array( 'parameters' => $parameters, 'options' => $options );
}

$url_vars = array(
  'people_id' => array( 'type' => 'u', 'default' => 0 )
, 'things_id' => array( 'type' => 'u', 'default' => 0 )
, 'darlehen_id' => array( 'type' => 'u', 'default' => 0 )
, 'hauptkonten_id' => array( 'type' => 'u', 'default' => 0 )
, 'unterkonten_id' => array( 'type' => 'u', 'default' => 0 )
, 'buchungen_id' => array( 'type' => 'u', 'default' => 0 )
, 'posten_id' => array( 'type' => 'u', 'default' => 0 )
, 'anschaffungsjahr' => array( 'type' => 'u' )
, 'geschaeftsjahr' => array( 'type' => 'u' )
, 'geschaeftsjahr_thread' => array( 'type' => 'u' )
, 'zahlungsplan_id' =>  array( 'type' => 'u', 'default' => 0 )
, 'kontoklassen_id' =>  array( 'type' => 'u', 'default' => 0 )
, 'geschaeftsbereiche_id' => array( 'type' => 'w', 'default' => 0 )
, 'titel_id' =>  array( 'type' => 'u', 'default' => 0 )
, 'confirmed' =>  array( 'type' => 'w', 'default' => 0 )
, 'jperson' => array( 'type' => '/^[01]$/', 'default' => 0 )
, 'seite' => array( 'type' => '/^[AP0]$/', 'default' => 0 )
, 'kontenkreis' => array( 'type' => '/^[BE0]$/', 'default' => 0 )
, 'buchungsdatum' => array( 'type' => 'u' )
, 'buchungsdatum_von' => array( 'type' => 'u', 'default' => 0 )
, 'buchungsdatum_bis' => array( 'type' => 'u', 'default' => 0 )
, 'stichtag' => array( 'type' => 'u', 'default' => 1231 )
, 'valuta' => array( 'type' => 'u', 'default' => 0 )
, 'valuta_von' => array( 'type' => 'u', 'default' => 100 )
, 'valuta_bis' => array( 'type' => 'u', 'default' => 1231 )
, 'hgb_klasse' => array( 'type' => '/^[a-cA-EIVP0-9.]*$/', 'default' => '' )
);

?>
