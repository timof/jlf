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
      $parameters['class'] = 'browse';
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
      $parameters['class'] = 'browse';
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
    case 'maintenance':
      $parameters['script'] = 'maintenance';
      $parameters['window'] = 'maintenance';
      $parameters['text'] = 'maintenance';
      $parameters['title'] = 'maintenance...';
      $options = $large_window_options;
      break;
    case 'sessions':
      $parameters['script'] = 'sessions';
      $parameters['window'] = 'sessions';
      $parameters['text'] = 'sessions';
      $parameters['title'] = 'sessions...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'logbook':
      $parameters['script'] = 'logbook';
      $parameters['window'] = 'logbook';
      $parameters['text'] = 'logbook';
      $parameters['title'] = 'logbook...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'anylist':
      $parameters['script'] = 'anylist';
      $parameters['window'] = 'anylist';
      $parameters['text'] = 'tables';
      $parameters['title'] = 'tables...';
      $parameters['class'] = 'browse';
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
      $parameters['class'] = 'browse';
      $options = $small_window_options;
      break;
    case 'host':
      $parameters['script'] = 'host';
      $parameters['window'] = 'host';
      $parameters['text'] = 'host';
      $parameters['title'] = 'details on host...';
      $parameters['class'] = 'href';
      $options = $small_window_options;
      $options['height'] = 700;
      break;
    case 'disk':
      $parameters['script'] = 'disk';
      $parameters['window'] = 'disk';
      $parameters['text'] = 'disk';
      $parameters['title'] = 'details on disk...';
      $parameters['class'] = 'href';
      $options = $small_window_options;
      break;
    case 'tape':
      $parameters['script'] = 'tape';
      $parameters['window'] = 'tape';
      $parameters['text'] = 'tape';
      $parameters['title'] = 'details on tape...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      break;
    case 'backupchunk':
      $parameters['script'] = 'backupchunk';
      $parameters['window'] = 'backupchunk';
      $parameters['text'] = 'backupchunk';
      $parameters['title'] = 'details on backupchunk...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      break;
    case 'service':
      $parameters['script'] = 'service';
      $parameters['window'] = 'service';
      $parameters['text'] = 'service';
      $parameters['title'] = 'details on service...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      break;
    case 'account':
      $parameters['script'] = 'account';
      $parameters['window'] = 'account';
      $parameters['text'] = 'account';
      $parameters['title'] = 'details on account...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      break;
    case 'system':
      $parameters['script'] = 'system';
      $parameters['window'] = 'system';
      $parameters['text'] = 'system';
      $parameters['title'] = 'details on system...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      break;
    case 'debugentry':
      $parameters['script'] = 'debugentry';
      $parameters['window'] = 'debugentry';
      $parameters['text'] = 'debug entry';
      $parameters['title'] = 'debug entry...';
      $parameters['class'] = 'href inlink';
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
      $parameters['class'] = 'card';
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
      $parameters['class'] = 'href inlink';
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
);

?>
