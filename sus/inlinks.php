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

// window_defaults: define default parameters and default options for views:
//  - window (historical name...): name of the script
//  - window_id: window name for target='...' or window.open()
//  - text, title, class: default look and tooltip-help of the link
//
function window_defaults( $name, $target_window_id = '' ) {
  global $readonly, $login_dienst, $large_window_options, $small_window_options;
  $parameters = array();
  $options = $large_window_options;
  $window = ( $name == 'self' ? $GLOBALS['window'] : $name );
  switch( strtolower( $window ) ) {
    //
    // Anzeige im Hauptfenster (aus dem Hauptmenue) oder in "grossem" Fenster moeglich:
    //
    case 'menu':
    case 'index':
      $parameters['window'] = 'menu';
      $parameters['text'] = 'back';
      $parameters['window_id'] = 'main';
      $parameters['title'] = 'back to main menu...';
      if( ! $target_window_id || ( $target_window_id == 'main' ) ) {
        $options = $large_window_options;
      } else {
        $options = array_merge( $small_window_options, array( 'width' => '320' ) );
      }
      break;
    case 'personen':
      $parameters['window'] = 'personen';
      $parameters['window_id'] = 'personen';
      $parameters['text'] = 'personen';
      $parameters['title'] = 'personen...';
      $parameters['class'] = 'people';
      $options = $large_window_options;
      break;
    case 'things':
      $parameters['window'] = 'things';
      $parameters['window_id'] = 'things';
      $parameters['text'] = 'Gegenst&auml;nde';
      $parameters['title'] = 'Gegenst&auml;nde...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'bilanz':
    case 'bestandskonten':
      $parameters['window'] = 'hauptkonten';
      $parameters['kontoart'] = 'B';
      $parameters['window_id'] = 'bestandskonten';
      $parameters['text'] = 'Bestandskonten';
      $parameters['title'] = 'Bestandskonten (Bilanz)...';
      $parameters['class'] = 'browse';
    case 'hauptkonten': // only possible with 'self':
      $options = $large_window_options;
      break;
    case 'gvrechnung':
    case 'erfolgskonten':
      $parameters['window'] = 'hauptkonten';
      $parameters['kontoart'] = 'E';
      $parameters['window_id'] = 'erfolgskonten';
      $parameters['text'] = 'Erfolgskonten';
      $parameters['title'] = 'Erfolgskonten (GV-Rechnung)...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'buchungen':
    case 'journal':
      $parameters['window'] = 'journal';
      $parameters['window_id'] = 'journal';
      $parameters['text'] = 'journal';
      $parameters['title'] = 'journal...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'unterkonten':
      $parameters['window'] = 'unterkonten';
      $parameters['window_id'] = 'unterkonten';
      $parameters['text'] = 'unterkonten';
      $parameters['title'] = 'unterkonten...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'bankkonten':
      $parameters['window'] = 'bankkonten';
      $parameters['window_id'] = 'bankkonten';
      $parameters['text'] = 'bankkonten';
      $parameters['title'] = 'bankkonten...';
      $parameters['class'] = 'cash';
      $options = $large_window_options;
      break;
    case 'darlehen':
      $parameters['window'] = 'darlehen';
      $parameters['window_id'] = 'darlehen';
      $parameters['text'] = 'darlehen';
      $parameters['title'] = 'darlehen...';
      $parameters['class'] = 'cash';
      $options = $large_window_options;
      break;
    //
    // "kleine" Fenster:
    //
    case 'person':
      $parameters['window'] = 'person';
      $parameters['window_id'] = 'person';
      $parameters['text'] = 'person';
      $parameters['title'] = 'person...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    case 'thing':
      $parameters['window'] = 'thing';
      $parameters['window_id'] = 'thing';
      $parameters['text'] = 'Gegenstand';
      $parameters['title'] = 'Gegenstand...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['scrollbars'] = 'yes';
      break;
    case 'darlehen':
      $parameters['window'] = 'darlehen';
      $parameters['window_id'] = 'darlehen';
      $parameters['text'] = 'darlehen';
      $parameters['title'] = 'darlehen...';
      $parameters['class'] = 'browse';
      $options = $small_window_options;
      break;
    case 'hauptkonto':
      $parameters['window'] = 'hauptkonto';
      $parameters['window_id'] = 'hauptkonto';
      $parameters['text'] = 'hauptkonto';
      $parameters['title'] = 'hauptkonto...';
      $parameters['class'] = 'browse';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '860';
      $options['height'] = '720';
      break;
    case 'unterkonto':
      $parameters['window'] = 'unterkonto';
      $parameters['window_id'] = 'unterkonto';
      $parameters['text'] = 'unterkonto';
      $parameters['title'] = 'unterkonto...';
      $parameters['class'] = 'browse';
      $options = $small_window_options;
      $options['scrollbars'] = 'yes';
      $options['width'] = '880';
      $options['height'] = '720';
      break;
    case 'buchung':
      $parameters['window'] = 'buchung';
      $parameters['window_id'] = 'buchung';
      $parameters['text'] = 'buchung';
      $parameters['title'] = 'buchung...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      $options['width'] = '1200';
      $options['height'] = '720';
      break;
    //
    default:
      error( "undefined window: [$window]" );
  }
  if( $name == 'self' ) {
    // pass all options (in case we fork), but only limited set of parameters:
    $parameters = array( 'window' => $window, 'window_id' => $GLOBALS['window_id'] );
  }
  if( $parameters )
    return array( 'parameters' => $parameters, 'options' => $options );
  else
    return NULL;
}

$jlf_url_vars += array(
  'people_id' => 'u'
, 'things_id' => 'u'
, 'darlehen_id' => 'u'
, 'hauptkonten_id' => 'u'
, 'unterkonten_id' => 'u'
, 'buchungen_id' => 'u'
, 'posten_id' => 'u'
, 'anschaffungsjahr' => 'u'
, 'zahlungsplan_id' => 'u'
, 'kontoklassen_id' => 'u'
, 'geschaeftsbereiche_id' => 'w'
, 'titel_id' => 'u'
, 'confirmed' => 'w'
, 'detail' => 'w'
, 'jperson' => '/^[JN01]?$/'
, 'seite' => '/^[AP0]?$/'
, 'kontoart' => '/^[EB0]?$/'
, 'buchungsdatum' => '/^\d\d\d\d\d\d\d\d$/'
, 'buchungsdatum_von' => '/^\d\d\d\d\d\d\d\d$/'
, 'buchungsdatum_bis' => '/^\d\d\d\d\d\d\d\d$/'
, 'buchungsdatum_day' => 'U'
, 'buchungsdatum_month' => 'U'
, 'buchungsdatum_year' => 'U'
, 'valuta' => '/^\d\d\d\d\d\d\d\d$/'
, 'valuta_von' => '/^\d\d\d\d\d\d\d\d$/'
, 'valuta_bis' => '/^\d\d\d\d\d\d\d\d$/'
, 'valuta_day' => 'U'
, 'valuta_month' => 'U'
, 'valuta_year' => 'U'
);

?>
