<?php

// inlinks.php (Timo Felbinger, 2008, 2009)
//
// functions and definitions for internal hyperlinks, in particular: window properties

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

// pseudo-parameters: when generating links and forms with the functions below,
// these parameters will never be transmitted via GET or POST; rather, they determine
// how the link itself will look and behave:
//
$pseudo_parameters = array( 'img', 'attr', 'title', 'text', 'class', 'confirm', 'anchor', 'url', 'context', 'enctype' );

//
// internal functions (not supposed to be called by consumers):
//

// fc_window_defaults: define default parameters and default options for views:
//  - window (historical name...): name of the script
//  - window_id: window name for target='...' or window.open()
//  - text, title, class: default look and tooltip-help of the link
//
function fc_window_defaults( $name ) {
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
      $parameters['text'] = 'Beenden';
      $parameters['title'] = 'zur&uuml;ck zum Hauptmen&uuml;';
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
    case 'userlist':
      $parameters['window'] = 'userlist';
      $parameters['window_id'] = 'userlist';
      $parameters['text'] = 'userlist';
      $parameters['title'] = 'list of users...';
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
    case 'user':
      $parameters['window'] = 'user';
      $parameters['window_id'] = 'user';
      $parameters['text'] = 'user';
      $parameters['title'] = 'details on user...';
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
      error( "undefiniertes Fenster: $name " );
  }
  if( $parameters )
    return array( 'parameters' => $parameters, 'options' => $options );
  else
    return NULL;
}


// parameters_explode():
// convert string "k1=v1,k2=k2,..." into array( k1 => v1, k2 => v2, ...)
//
function parameters_explode( $s ) {
  $r = array();
  $pairs = explode( ',', $s );
  foreach( $pairs as $pair ) {
    $v = explode( '=', $pair );
    if( $v[0] == '' )
      continue;
    $r[$v[0]] = ( isset($v[1]) ? $v[1] : '' );
  }
  return $r;
}

// fc_url(): create an internal URL, passing $parameters in the query string.
// - parameters with value NULL will be skipped
// - pseudo-parameters (see open) will always be skipped except for two special cases:
//   - anchor: append an #anchor to the url
//   - url: return the value of this parameter immediately (overriding all others)
//
function fc_url( $parameters ) {
  global $pseudo_parameters, $form_id;

  $url = 'index.php?';
  $anchor = '';
  foreach( $parameters as $key => $value ) {
    switch( $key ) {
      case 'anchor':
        $anchor = "#$value";
        continue 2;
      case 'url':
        return $value;
      default:
        if( in_array( $key, $pseudo_parameters ) )
          continue 2;
    }
    if( $value !== NULL )
      $url .= "&amp;$key=$value";
  }
  $url .= $anchor;
  return $url;
}


// alink: compose from parts and return an <a href=...> hyperlink
// $url may also contain javascript; if so, '-quotes but no "-quotes must be used in the js code
//
function alink( $url, $class = '', $text = '', $title = '', $img = false ) {
  global $activate_safari_kludges, $activate_konqueror_kludges;
  $alt = '';
  if( $title ) {
    $alt = "alt='$title'";
    $title = "title='$title'";
  }
  $l = "<a class='$class' $title href=\"$url\">";
  if( $img ) {
    $l .= "<img src='$img' class='icon' $alt $title />";
    if( $text )
      $l .= ' ';
  }
  if( $text !== '' ) {
    $l .= "$text";
  } else if( ! $img ) {
    if( $activate_safari_kludges )
      $l .= "&#8203;"; // safari can't handle completely empty links...
    if( $activate_konqueror_kludges )
      $l .= "&nbsp;"; // ...dito konqueror (and it can't even handle unicode)
  }
  return $l . '</a>';
}


//////////////////////////////////////////////
//
// consumer-callable functions follow below:
//

// fc_link: create internal link:
//   $window: name of the view; determines script, target window, and defaults for parameters and options. default: 'self'
//            if $window == 'self', global $self_fields will be merged with $parameters
//   $parameters: GET parameters to be passed in url: either "k1=v1&k2=v2" string, or array of 'name' => 'value' pairs
//                this will override defaults and (if applicable) $self_fields.
//                use 'name' => NULL to explicitely _not_ pass $name even if it is in defaults or $self_fields.
//   $options:    window options to be passed in javascript:window_open() (optional, to override defaults)
// $parameters may also contain some pseudo-parameters:
//   text, title, class, img: to specify the look of the link (see alink above)
//   window_id: name of browser target window (will also be passed in the query string)
//   confirm: if set, a javascript confirm() call will pop up with text $confirm when the link is clicked
//   context: where the link is to be used:
//    'a' (default): return a complete <a href=...>...</a> link. the link will contain javascript if the target window
//                   is differerent from the current window or if $confirm is specified.
//    'js': always return javascript code that can be used in event handlers like onclick=...
//    'action': always return the plain url, never javascript (most pseudo parameters will have no effect)
//    'form': return string of attributes suitable to insert into a <form>-tag. the result always contains action='...'
//            and may also contain target='...' and onsubmit='...' attributes if needed.
// as a special case, $parameters === NULL can be used to just open a browser window with no document
// (this can be used in <form onsubmit='...', in combination with target=..., to submit a form into a new window)
//
function fc_link( $window = '', $parameters = array(), $options = array() ) {
  global $self_fields;

  // allow string or array form:
  if( is_string( $parameters ) )
    $parameters = parameters_explode( $parameters );
  if( is_string( $options ) )
    $options = parameters_explode( $options );
  $window or $window = 'self';

  $window_defaults = fc_window_defaults( $window );
  if( ! $window_defaults )  // probably: no access to this item; don't generate a link, just return plain text, if any:
    return adefault( $parameters, 'text', '' );

  if( $parameters === NULL ) {  // open empty window
    $parameters = $window_defaults['parameters'];
    $url = '';
    $context = 'js';  // window.open() _needs_ js (and opening empty windows is only useful in onsubmit() anyway)
  } else {
    if( $window == 'self' )
      $parameters = array_merge( $self_fields, $parameters );
    $parameters = array_merge( $window_defaults['parameters'], $parameters );
    $window = $window_defaults['parameters']['window'];  // force canonical script name
    $parameters['window'] = $window;
    $url = fc_url( $parameters );
    $context = adefault( $parameters, 'context', 'a' );
  }

  $options = array_merge( $window_defaults['options'], $options );
  $option_string = '';
  $komma = '';
  foreach( $options as $key => $value ) {
    $option_string .= "$komma$key=$value";
    $komma = ',';
  }

  $confirm = '';
  if( isset( $parameters['confirm'] ) )
    $confirm = "if( confirm( '{$parameters['confirm']}' ) ) ";

  $window_id = adefault( $parameters, 'window_id', '' );
  $js_window_name = $window_id;
  if( ( $window_id == 'main' ) or ( $window_id == 'top' ) )
    $js_window_name = '_top';

  switch( $context ) {
    case 'a':
      if( $window_id != $GLOBALS['window_id'] ) {
        $url = "javascript: $confirm window.open( '$url', '$js_window_name', '$option_string' ).focus();";
      } else if( $confirm ) {
        $url = "javascript: $confirm self.location.href='$url';";
      }
      $title = adefault( $parameters, 'title', '' );
      $text = adefault( $parameters, 'text', '' );
      $img = adefault( $parameters, 'img', '' );
      $class = adefault( $parameters, 'class', 'href' );
      return alink( $url, $class, $text, $title, $img );
    case 'action':
      return $url;
    case 'js':
      if( $window_id != $GLOBALS['window_id'] ) {
        return "$confirm window.open( '$url', '$js_window_name', '$option_string' ).focus();";
      } else {
        return "$confirm self.location.href='$url';";
      }
    case 'form':
      $enctype = adefault( $parameters, 'enctype', '' );
      if( $enctype )
        $enctype = "enctype='$enctype'";
      if( $window_id == $GLOBALS['window_id'] ) {
        $target = '';
        $onsubmit = '';
      } else {
        $target = "target='$js_window_name'";
        // $onsubmit: 
        //  - make sure the target window exists (open empty window unless already open), then
        //  - force reload of document in current window (to issue fresh iTAN for this form):
        $onsubmit = 'onsubmit="'. fc_link( $window, NULL ) . ' document.forms.update_form.submit(); "';
      }
      return "action='$url' $target $onsubmit $enctype";
    default:
      error( 'undefinierter $context' );
  }
}

// fc_action(): generates simple form and one submit button
// $get_parameters: determine the url as in fc_link. In particular, 'window' allows to submit this form to
//                  an arbitrary script in a different window (default: submit to same script), and the
//                  style of the <a> can be specified.
// $post_parameter: additional parameters to be POSTed in hidden input fields.
// forms can't be nested; thus, to allow fc_action() to be called inside other forms, we
//   - use an <a>-element for the submit button and
//   - insert the actual form at the end of the document
//
// if 'update' is one of the $get_parameters, the update_form (inserted at bottom of every page) will
// be used; from $get_parameters, only pseudo-parameters will take effect, and the only $post_parameters
// which can be passed are 'action' and 'message'.
//
function fc_action( $get_parameters = array(), $post_parameters = array(), $options = array() ) {
  global $print_on_exit, $self_post_fields, $pseudo_parameters;

  if( is_string( $get_parameters ) )
    $get_parameters = parameters_explode( $get_parameters );
  if( is_string( $post_parameters ) )
    $post_parameters = parameters_explode( $post_parameters );

  $window = adefault( $get_parameters, 'window', 'self' );
  unset( $get_parameters['window'] );
  $window_defaults = fc_window_defaults( $window );
  $get_parameters = array_merge( $window_defaults['parameters'], $get_parameters );

  $title = adefault( $get_parameters, 'title', '' );
  $text = adefault( $get_parameters, 'text', '' );
  $class = adefault( $get_parameters, 'class', 'button' );
  $img = adefault( $get_parameters, 'img', '' );
  $context = adefault( $get_parameters, 'context', 'a' );

  if( $confirm = adefault( $get_parameters, 'confirm', '' ) )
    $confirm = " if( confirm( '$confirm' ) ) ";

  if( isset( $get_parameters['update'] ) ) {
    $action = adefault( $post_parameters, 'action', '' );
    $message = adefault( $post_parameters, 'message', '' );
    if( $context == 'js' ) {
      return "$confirm post_action( '$action', '$message' );";
    } else {
      return alink( "javascript:$confirm post_action( '$action', '$message' );", $class, $text, $title, $img );
    }
  }

  $get_parameters['context'] = 'form';
  $action = fc_link( $window, $get_parameters );

  $form_id = new_html_id();

  $form = "<form style='display:inline;' method='post' id='form_$form_id' name='form_$form_id' $action>";
  $form .= "<input type='hidden' name='itan' value='". get_itan() ."'>";
  if( $window == 'self' )
    $post_parameters = array_merge( $self_post_fields, $post_parameters );
  foreach( $post_parameters as $name => $value ) {
    if( $value or ( $value === 0 ) or ( $value === '' ) )
      $form .= "<input type='hidden' name='$name' value='$value'>";
  }
  $form .= "</form>";
  // we may be inside another form, but forms cannot be nested; so we append this form at the end:
  $print_on_exit[] = $form;

  return alink( "javascript:$confirm submit_form( $form_id );", $class, $text, $title, $img );
}

// fc_openwindow(): pop-up $window here and now:
//
function fc_openwindow( $window, $parameters = array(), $options = array() ) {
  if( is_string( $parameters ) )
    $parameters = parameters_explode( $parameters );
  $parameters['context'] = 'js';
  open_javascript( preg_replace( '/&amp;/', '&', fc_link( $window, $parameters, $options ) ) );
}

// reload_immediately(): exit the current script and open $url instead:
//
function reload_immediately( $url ) {
  $url = preg_replace( '/&amp;/', '&', $url );  // doesn't get fed through html engine here
  open_javascript( "self.location.href = '$url';" );
  exit();
}
?>
