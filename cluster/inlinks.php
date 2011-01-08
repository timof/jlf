<?
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
  , 'scrollbars' => 'no'
  , 'resizable' => 'yes'
  , 'width' => '640'
  , 'height' => '460'
  , 'left' => '80'
  , 'top' => '180'
);

// window_defaults: define default parameters and default options for views:
//  - window (historical name...): name of the script
//  - window_id: window name for target='...' or window.open()
//  - text, title, class: default look and tooltip-help of the link
//
function window_defaults( $name ) {
  global $readonly, $login_dienst, $large_window_options, $small_window_options;
  $parameters = array();
  $options = $large_window_options;
  switch( strtolower( $name ) ) {
    //
    // self: display in same window:
    //
    case 'self':
      $parameters['window'] = $GLOBALS['window'];
      $parameters['window_id'] = $GLOBALS['window_id'];
      break;
    //
    // Anzeige im Hauptfenster (aus dem Hauptmenue) oder in "grossem" Fenster moeglich:
    //
    case 'menu':
    case 'index':
      $parameters['window'] = 'menu';
      $parameters['window_id'] = 'main';
      $parameters['text'] = 'back';
      $parameters['title'] = 'back to main menu...';
      $options = $large_window_options;
      break;
    case 'hostlist':
      $parameters['window'] = 'hostlist';
      $parameters['window_id'] = 'hostlist';
      $parameters['text'] = 'hostlist';
      $parameters['title'] = 'list of hosts...';
      $parameters['class'] = 'browse';
      $options = $large_window_options;
      break;
    case 'disklist':
      $parameters['window'] = 'disklist';
      $parameters['window_id'] = 'disklist';
      $parameters['text'] = 'disklist';
      $parameters['title'] = 'list of disks...';
      $options = $large_window_options;
      break;
    case 'servicelist':
      $parameters['window'] = 'servicelist';
      $parameters['window_id'] = 'servicelist';
      $parameters['text'] = 'servicelist';
      $parameters['title'] = 'list of services...';
      $options = $large_window_options;
      break;
    case 'tapelist':
      $parameters['window'] = 'tapelist';
      $parameters['window_id'] = 'tapelist';
      $parameters['text'] = 'tapelist';
      $parameters['title'] = 'list of tapes...';
      $options = $large_window_options;
      break;
    case 'accountlist':
      $parameters['window'] = 'accountlist';
      $parameters['window_id'] = 'accountlist';
      $parameters['text'] = 'accountlist';
      $parameters['title'] = 'list of accounts...';
      $options = $large_window_options;
      break;
    case 'systemlist':
      $parameters['window'] = 'systemlist';
      $parameters['window_id'] = 'systemlist';
      $parameters['text'] = 'systemlist';
      $parameters['title'] = 'list of systems...';
      $options = $large_window_options;
      break;
    case 'sync':
      $parameters['window'] = 'sync';
      $parameters['window_id'] = 'main';
      $parameters['text'] = 'sync';
      $parameters['title'] = 'synchronize with ldap...';
      $options = $large_window_options;
      break;
    //
    // "kleine" Fenster:
    //
    case 'accountdomainlist':
      $parameters['window'] = 'accountdomainlist';
      $parameters['window_id'] = 'accountdomainlist';
      $parameters['text'] = 'accountdomainlist';
      $parameters['title'] = 'list of accountdomains...';
      $parameters['class'] = 'browse';
      $options = $small_window_options;
      break;
    case 'host':
      $parameters['window'] = 'host';
      $parameters['window_id'] = 'host';
      $parameters['text'] = 'host';
      $parameters['title'] = 'details on host...';
      $parameters['class'] = 'href';
      $options = $small_window_options;
      $options['height'] = 700;
      break;
    case 'disk':
      $parameters['window'] = 'disk';
      $parameters['window_id'] = 'disk';
      $parameters['text'] = 'disk';
      $parameters['title'] = 'details on disk...';
      $parameters['class'] = 'href';
      $options = $small_window_options;
      break;
    case 'tape':
      $parameters['window'] = 'tape';
      $parameters['window_id'] = 'tape';
      $parameters['text'] = 'tape';
      $parameters['title'] = 'details on tape...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      break;
    case 'service':
      $parameters['window'] = 'service';
      $parameters['window_id'] = 'service';
      $parameters['text'] = 'service';
      $parameters['title'] = 'details on service...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      break;
    case 'account':
      $parameters['window'] = 'account';
      $parameters['window_id'] = 'account';
      $parameters['text'] = 'account';
      $parameters['title'] = 'details on account...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      break;
    case 'system':
      $parameters['window'] = 'system';
      $parameters['window_id'] = 'system';
      $parameters['text'] = 'system';
      $parameters['title'] = 'details on system...';
      $parameters['class'] = 'record';
      $options = $small_window_options;
      break;
    //
    default:
      error( "undefined window: $name " );
  }
  if( $parameters )
    return array( 'parameters' => $parameters, 'options' => $options );
  else
    return NULL;
}

$jlf_get_vars += array(
  'accountdomains_id' => 'u'
, 'hosts_id' => 'u'
, 'disks_id' => 'u'
, 'tapes_id' => 'u'
, 'services_id' => 'u'
, 'confirmed' => 'w'
, 'detail' => 'w'
, 'locations_id' => 'w'
, 'type_disk' => '/^[a-zA-Z0-9.-]*$/'
, 'type_tape' => '/^[a-zA-Z0-9.-]*$/'
);

?>
