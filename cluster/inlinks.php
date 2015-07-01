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
  , 'toolbar' => 'yes' /* required to edit url! */
  , 'menubar' => 'no'
  , 'location' => 'yes'
  , 'scrollbars' => 'yes'
  , 'resizable' => 'yes'
  , 'width' => '640'
  , 'height' => '460'
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
    case 'hostslist':
      $parameters['script'] = 'hostslist';
      $parameters['window'] = 'hostslist';
      $parameters['text'] = 'hostslist';
      $parameters['title'] = 'list of hosts...';
      $options = $large_window_options;
      break;
    case 'diskslist':
      $parameters['script'] = 'diskslist';
      $parameters['window'] = 'diskslist';
      $parameters['text'] = 'diskslist';
      $parameters['title'] = 'list of disks...';
      $options = $large_window_options;
      break;
    case 'serviceslist':
      $parameters['script'] = 'serviceslist';
      $parameters['window'] = 'serviceslist';
      $parameters['text'] = 'serviceslist';
      $parameters['title'] = 'list of services...';
      $options = $large_window_options;
      break;
    case 'tapeslist':
      $parameters['script'] = 'tapeslist';
      $parameters['window'] = 'tapeslist';
      $parameters['text'] = 'tapeslist';
      $parameters['title'] = 'list of tapes...';
      $options = $large_window_options;
      break;
    case 'backupjobslist':
    case 'backupprofileslist':
      $parameters['script'] = 'backupprofileslist';
      $parameters['window'] = 'backupprofileslist';
      $parameters['text'] = 'backupprofileslist';
      $parameters['title'] = 'list of backupprofiles...';
      $options = $large_window_options;
      break;
    case 'tapechunkslist':
      $parameters['script'] = 'tapechunkslist';
      $parameters['window'] = 'tapechunkslist';
      $parameters['text'] = 'tapechunkslist';
      $parameters['title'] = 'list of tapechunks...';
      $options = $large_window_options;
      break;
    case 'backupchunkslist':
      $parameters['script'] = 'backupchunkslist';
      $parameters['window'] = 'backupchunkslist';
      $parameters['text'] = 'backupchunkslist';
      $parameters['title'] = 'list of backupchunks...';
      $options = $large_window_options;
      break;
    case 'accountslist':
      $parameters['script'] = 'accountslist';
      $parameters['window'] = 'accountslist';
      $parameters['text'] = 'accountslist';
      $parameters['title'] = 'list of accounts...';
      $options = $large_window_options;
      break;
    case 'systemslist':
      $parameters['script'] = 'systemslist';
      $parameters['window'] = 'systemslist';
      $parameters['text'] = 'systemslist';
      $parameters['title'] = 'list of systems...';
      $options = $large_window_options;
      break;
    case 'tests':
      $parameters['script'] = 'tests';
      $parameters['window'] = 'tests';
      $parameters['text'] = 'tests';
      $parameters['title'] = 'tests...';
      $options = $large_window_options;
      break;
    case 'persistentvars':
      $parameters['script'] = 'persistentvars';
      $parameters['window'] = 'persistentvars';
      $parameters['text'] = 'persistentvars';
      $parameters['title'] = 'persistentvars...';
      $options = $large_window_options;
      break;
    case 'maintenance':
      $parameters['script'] = 'maintenance';
      $parameters['window'] = 'maintenance';
      $parameters['text'] = 'maintenance';
      $parameters['title'] = 'maintenance...';
      $options = $large_window_options;
      break;
    case 'profile':
      $parameters['script'] = 'profile';
      $parameters['window'] = 'profile';
      $parameters['text'] = 'profile';
      $parameters['title'] = 'profile...';
      $options = $large_window_options;
      break;
    case 'sessions':
      $parameters['script'] = 'sessions';
      $parameters['window'] = 'sessions';
      $parameters['text'] = 'sessions';
      $parameters['title'] = 'sessions...';
      $options = $large_window_options;
      break;
    case 'debuglist':
      $parameters['script'] = 'debuglist';
      $parameters['window'] = 'debuglist';
      $parameters['text'] = 'debug';
      $parameters['title'] = 'debug...';
      $options = $large_window_options;
      break;
    case 'logbook':
      $parameters['script'] = 'logbook';
      $parameters['window'] = 'logbook';
      $parameters['text'] = 'logbook';
      $parameters['title'] = 'logbook...';
      $options = $large_window_options;
      break;
    case 'anylist':
      $parameters['script'] = 'anylist';
      $parameters['window'] = 'anylist';
      $parameters['text'] = 'tables';
      $parameters['title'] = 'tables...';
      $options = $large_window_options;
      break;
    //
    // "kleine" Fenster:
    //
    case 'person_view':
      $parameters['script'] = 'person_view';
      $parameters['window'] = 'person_view';
      $parameters['text'] = 'person';
      $parameters['title'] = 'details on person...';
      $parameters['class'] = 'href';
      $options = $small_window_options;
      $options['height'] = 700;
      break;
    case 'accountdomainslist':
      $parameters['script'] = 'accountdomainslist';
      $parameters['window'] = 'accountdomainslist';
      $parameters['text'] = 'accountdomainslist';
      $parameters['title'] = 'list of accountdomains...';
      $options = $small_window_options;
      break;
    case 'host':
      $parameters['script'] = 'host';
      $parameters['window'] = 'host';
      $parameters['text'] = 'host';
      $parameters['title'] = 'details on host...';
      $options = $small_window_options;
      $options['height'] = 700;
      break;
    case 'disk':
      $parameters['script'] = 'disk';
      $parameters['window'] = 'disk';
      $parameters['text'] = 'disk';
      $parameters['title'] = 'details on disk...';
      $options = $small_window_options;
      break;
    case 'tape':
      $parameters['script'] = 'tape';
      $parameters['window'] = 'tape';
      $parameters['text'] = 'tape';
      $parameters['title'] = 'details on tape...';
      $options = $small_window_options;
      break;
    case 'backupchunk':
      $parameters['script'] = 'backupchunk';
      $parameters['window'] = 'backupchunk';
      $parameters['text'] = 'backupchunk';
      $parameters['title'] = 'details on backupchunk...';
      $options = $small_window_options;
      break;
    case 'service':
      $parameters['script'] = 'service';
      $parameters['window'] = 'service';
      $parameters['text'] = 'service';
      $parameters['title'] = 'details on service...';
      $options = $small_window_options;
      break;
    case 'account':
      $parameters['script'] = 'account';
      $parameters['window'] = 'account';
      $parameters['text'] = 'account';
      $parameters['title'] = 'details on account...';
      $options = $small_window_options;
      break;
    case 'system':
      $parameters['script'] = 'system';
      $parameters['window'] = 'system';
      $parameters['text'] = 'system';
      $parameters['title'] = 'details on system...';
      $options = $small_window_options;
      break;
    case 'profileentry':
      $parameters['script'] = 'profileentry';
      $parameters['window'] = 'profileentry';
      $parameters['text'] = we('profile entry','Profile Eintrag');
      $parameters['title'] = we('profile entry...','Profile Eintrag...');
      $parameters['class'] = 'href';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    case 'session':
      $parameters['script'] = 'session';
      $parameters['window'] = 'session';
      $parameters['text'] = 'session';
      $parameters['title'] = 'session...';
      $parameters['class'] = 'href';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    case 'debugentry':
      $parameters['script'] = 'debugentry';
      $parameters['window'] = 'debugentry';
      $parameters['text'] = 'debug entry';
      $parameters['title'] = 'debug entry...';
      $parameters['class'] = 'href';
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    case 'logentry':
      $parameters['script'] = 'logentry';
      $parameters['window'] = 'logentry';
      $parameters['text'] = 'logentry';
      $parameters['title'] = 'logentry...';
      $options = $small_window_options;
      $options['height'] = '1200';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    case 'any_view':
      $parameters['script'] = 'any_view';
      $parameters['window'] = 'any_view';
      $parameters['text'] = we('record','Datensatz');
      $parameters['title'] = we('record...','Datensatz...');
      $options = $small_window_options;
      $options['height'] = '800';
      $options['width'] = '720';
      $options['scrollbars'] = 'yes';
      break;
    //
    default:
      // error( "undefined target script: [$target_script]", LOG_FLAG_INPUT | LOG_FLAG_CODE | 'links' );
      return NULL;
  }
  return array( 'parameters' => $parameters, 'options' => $options );
}

$cgi_get_vars = array(
  'detail' => array( 'type' => 'w', 'default' => '0' )
);

$cgi_vars = array(
  // for type_disk, here (unlike in $tables['disks']) we allow empty string too (for filters):
  'type_disk' => array( 'type' => 'E;;' . implode( ';', $disk_types ), 'default' => '' )
, 'interface_disk' => array( 'type' => 'E;;' . implode( ';', $disk_interfaces ), 'default' => '' )
, 'type_tape' => array( 'type' => 'E;;' . implode( ';', $tape_types ), 'default' => '' )
, 'oid' => array( 'type' => 'a240', 'pattern' => '/^[0-9.]*$/', 'default' => '' )
, 'REGEX' => array( 'type' => 'h' )
);

?>
