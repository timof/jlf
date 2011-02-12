<?php

// inlinks.php (Timo Felbinger, 2008, 2009)
//
// functions and definitions for internal hyperlinks, in particular: window properties


// pseudo-parameters: when generating links and forms with the functions below,
// these parameters will never be transmitted via GET or POST; rather, they determine
// how the link itself will look and behave:
//
$pseudo_parameters = array(
  'img', 'attr', 'title', 'text', 'class', 'confirm', 'anchor', 'url', 'context', 'enctype', 'thread', 'window', 'script', 'inactive'
);

//
// internal functions (not supposed to be called by consumers):
//



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

function parameters_implode( $a ) {
  $s = '';
  $comma = '';
  foreach( $a as $k => $v ) {
    $s .= "$comma$k=$v";
    $comma = ',';
  }
  return $s;
}

// jlf_url(): create an internal URL, passing $parameters in the query string.
// - parameters with value NULL will be skipped
// - pseudo-parameters (see open) will always be skipped except for two special cases:
//   - anchor: append an #anchor to the url
//   - url: return the value of this parameter immediately (overriding all others)
//
function jlf_url( $parameters ) {
  global $pseudo_parameters, $form_id;

  $url = 'index.php?dontcache='.random_hex_string(6);  // the only way to surely prevent caching...
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

// js_window_name():
//   return window name which is unique and constant for this thread of this session
//
function js_window_name( $window, $thread = '1' ) {
  global $login_session_id, $login_session_cookie, $jlf_application_name, $jlf_application_instance;
  static $cache;

  if( isset( $cache[$window][$thread] ) ) {
    return $cache[$window][$thread];
  } else {
    return $cache[$window][$thread] = md5(
      "$window $thread $login_session_id $login_session_cookie $jlf_application_name $jlf_application_instance"
    );
  }
}



//////////////////////////////////////////////
//
// consumer-callable functions follow below:
//

// inlink: create internal link:
//   $script: determines script, defaults for target window, parameters and options. default: 'self'
//   $parameters: GET parameters to be passed in url: either "k1=v1&k2=v2" string, or array of 'name' => 'value' pairs
//                this will override any defaults and persistent variables
//                use 'name' => NULL to explicitely _not_ pass $name even if it is in defaults or persistent
//   $options:    window options to be passed in javascript:window_open() (will override defaults)
// $parameters may also contain some pseudo-parameters:
//   text, title, class, img: to specify the look of the link (see alink above)
//   thread: id of target thread
//   window: base name of browser target window (will also be passed in the query string)
//           (the actual window name will be composed of base name `thread'-parameter)
//   confirm: if set, a javascript confirm() call will pop up with text $confirm when the link is clicked
//   context: where the link is to be used
//    'a' (default): return a complete <a href=...>...</a> link. the link will contain javascript if the target window
//                   is differerent from the current window or if $confirm is specified.
//    'js': always return javascript code that can be used in event handlers like onclick=...
//    'action': always return the plain url, never javascript (most pseudo parameters will have no effect)
//    'form': return string of attributes suitable to insert into a <form>-tag. the result always contains action='...'
//            and may also contain target='...' and onsubmit='...' attributes if needed.
// as a special case, $window === NULL can be used to just open a browser window with no document
// (this can be used in <form onsubmit='...', in combination with target=..., to submit a form into a new window)
//
function inlink( $script = '', $parameters = array(), $options = array() ) {
  // allow string or array form:
  if( is_string( $parameters ) )
    $parameters = parameters_explode( $parameters );
  if( is_string( $options ) )
    $options = parameters_explode( $options );

  $inactive = adefault( $parameters, 'inactive', 0 );

  $parent_window = $GLOBALS['window'];
  $parent_thread = $GLOBALS['thread'];
  if( $script === NULL ) {
    $url = '';
    $context = 'js';  // window.open() _needs_ js (and opening empty windows is only useful in onsubmit() anyway)
    $target_window = adefault( $parameters, 'window', '_new' );
    $target_thread = adefault( $parameters, 'thread', $GLOBALS['thread'] );
  } else {
    $script or $script = 'self';
    if( $script == 'self' ) {
      $parent_script = 'self';
      $target_script = $GLOBALS['script'];
    } else {
      $parent_script = $GLOBALS['script'];
      $target_script = $script;
    }

    $target_thread = adefault( $parameters, 'thread', $GLOBALS['thread'] );
    $enforced_target_window = adefault( $parameters, 'window', '' );

    $script_defaults = script_defaults( $target_script, $enforced_target_window, $target_thread );
    // prettydump( $script_defaults );

    if( ! $script_defaults )  // probably: no access to this item; don't generate a link, just return plain text, if any:
      return adefault( $parameters, 'text', '' );

    // force canonical script name:
    $target_script = $script_defaults['parameters']['script'];

    if( $parent_script == 'self' ) {
      // don't pass default parameters (text, title, ... make little sense there) for self-links:
      $script_defaults['parameters'] = array();
    }

    $parameters = array_merge( $script_defaults['parameters'], $parameters );
    $target_window = adefault( $parameters, 'window', $GLOBALS['window'] );

    $me = sprintf( '%s,%s,%s,%s,%s,%s'
    , $target_thread, $target_window, $target_script
    , $parent_thread, $parent_window, $parent_script
    );
    $parameters['me'] = $me;
    print_on_exit( "<!-- inlink: script: $script / me: [$me] -->" );

    $url = $inactive ? '#' : jlf_url( $parameters );
    $context = adefault( $parameters, 'context', 'a' );
    $options = array_merge( $script_defaults['options'], $options );
  }

  $option_string = parameters_implode( $options );

  $confirm = '';
  if( isset( $parameters['confirm'] ) )
    $confirm = "if( confirm( '{$parameters['confirm']}' ) ) ";

  $js_window_name = js_window_name( $target_window, $target_thread );

  switch( $context ) {
    case 'a':
      if( ( $target_window != $parent_window ) || ( $target_thread != $parent_thread ) ) {
        $url = "javascript: $confirm window.open( '$url', '$js_window_name', '$option_string' ).focus();";
      } else if( $confirm ) {
        $url = "javascript: $confirm self.location.href='$url';";
      }
      $title = adefault( $parameters, 'title', '' );
      $text = adefault( $parameters, 'text', '' );
      $img = adefault( $parameters, 'img', '' );
      $class = adefault( $parameters, 'class', 'href' ) . ( $inactive ? ' inactive' : '' );
      return alink( $url, $class, $text, $title, $img );
    case 'action':
      return $url;
    case 'js':
      if( $inactive ) {
        return 'true;';
      } else if( ( $target_window != $parent_window ) || ( $target_thread != $parent_thread ) ) {
        return "$confirm window.open( '$url', '$js_window_name', '$option_string' ).focus();";
      } else {
        return "$confirm self.location.href='$url';";
      }
    case 'form':
      if( $inactive ) {
        return "action='javascript:true;'";
      }
      $enctype = adefault( $parameters, 'enctype', '' );
      if( $enctype )
        $enctype = "enctype='$enctype'";
      if( ( $target_window != $parent_window ) || ( $target_thread != $parent_thread ) ) {
        $target = "target='$js_window_name'";
        // $onsubmit: 
        //  - make sure the target window exists (open empty window unless already open), then
        //  - force reload of document in current window (to issue fresh iTANs for all forms):
        $parameters['window'] = $target_window;
        $parameters['thread'] = $target_thread;
        $onsubmit = 'onsubmit="'. inlink( NULL, $parameters, $options ) . ' document.forms.update_form.submit(); "';
      } else {
        $target = '';
        $onsubmit = '';
      }
      return "action='$url' $target $onsubmit $enctype";
    default:
      error( 'undefined $context' );
  }
}

// postaction(): generates simple form and one submit button
// $get_parameters: determine the url as in inlink. In particular, 'window' allows to submit this form to
//                  an arbitrary script in a different window (default: submit to same script), and the
//                  style of the <a> can be specified.
// $post_parameter: additional parameters to be POSTed in hidden input fields.
// forms can't be nested; thus, to allow postaction() to be called inside other forms, we
//   - use an <a>-element for the submit button and
//   - insert the actual form at the end of the document
//
// if 'update' is one of the $get_parameters, the update_form (inserted at bottom of every page) will
// be used; from $get_parameters, only pseudo-parameters will take effect, and the only $post_parameters
// which can be passed are 'action' and 'message'.
//
function postaction( $get_parameters = array(), $post_parameters = array(), $options = array() ) {
  if( is_string( $get_parameters ) )
    $get_parameters = parameters_explode( $get_parameters );
  if( is_string( $post_parameters ) )
    $post_parameters = parameters_explode( $post_parameters );

  $inactive = adefault( $get_parameters, 'inactive', 0 );

  $target_script = adefault( $get_parameters, 'script', 'self' );
  unset( $get_parameters['script'] );
  if( $target_script != 'self' ) {
    $enforced_target_window = adefault( $get_parameters, 'window', '' );
    $target_thread = adefault( $get_parameters, 'thread', $GLOBALS['thread'] );
    $script_defaults = script_defaults( $target_script, $enforced_target_window, $target_thread );
    $get_parameters = array_merge( $script_defaults['parameters'], $get_parameters );
  }

  $title = adefault( $get_parameters, 'title', '' );
  $text = adefault( $get_parameters, 'text', '' );
  $class = adefault( $get_parameters, 'class', 'button' ) . ( $inactive ? ' inactive' : '' );
  $img = adefault( $get_parameters, 'img', '' );
  $context = adefault( $get_parameters, 'context', 'a' );

  if( $confirm = adefault( $get_parameters, 'confirm', '' ) )
    $confirm = " if( confirm( '$confirm' ) ) ";

  if( isset( $get_parameters['update'] ) ) {
    $action = adefault( $post_parameters, 'action', '' );
    $message = adefault( $post_parameters, 'message', '' );
    if( $context == 'js' ) {
      return $inactive ? 'true;' : "$confirm post_action( '$action', '$message' );";
    } else {
      if( $inactive ) {
        return alink( '#', $class, $text, $title, $img );
      } else {
        return alink( "javascript:$confirm post_action( '$action', '$message' );", $class, $text, $title, $img );
      }
    }
  }

  $get_parameters['context'] = 'form';
  $action = inlink( $target_script, $get_parameters, $options );

  $form_id = new_html_id();

  $form = "<form style='display:inline;' method='post' id='form_$form_id' name='form_$form_id' $action>";
  $form .= "<input type='hidden' name='itan' value='". get_itan( true ) ."'>";
  foreach( $post_parameters as $name => $value ) {
    if( $value or ( $value === 0 ) or ( $value === '' ) )
      $form .= "<input type='hidden' name='$name' value='$value'>";
  }
  $form .= "</form>";
  // we may be inside another form, but forms cannot be nested; so we append this form at the end:
  print_on_exit( $form );

  return alink( "javascript:$confirm submit_form( 'form_$form_id', false, false );", $class, $text, $title, $img );
}

//
// handlers for some special and frequently used variables:
//

// handle_orderby(): for ordering tables:
//
// get and evaluate <prefix>orderby and <prefix>ordernew
// - change $orderby according to $ordernew
// - return argument string for sql ORDER keyword
// - $defaults: array of <tag> -> <sql-key> pairs
//
function handle_orderby( $defaults, $prefix = '' ) {
  global ${$prefix.'orderby'}, ${$prefix.'ordernew'}, $jlf_window_fields;
  init_global_var( $prefix.'orderby', 'l', 'http,persistent', '', 'window' );
  init_global_var( $prefix.'ordernew', 'l', 'http', '' );
  // prettydump( ${$prefix.'orderby'} );
  // prettydump( ${$prefix.'ordernew'} );
  if( ${$prefix.'ordernew'} )
    ${$prefix.'orderby'} = orderby_join( ${$prefix.'orderby'}, ${$prefix.'ordernew'} );
  $jlf_window_fields[ $prefix.'orderby' ] = & ${$prefix.'orderby'};
  return orderby_string2sql( $defaults, ${$prefix.'orderby'} );
}

function handle_filters( $keys = array() ) {
  $filters = array();
  if( is_string( $keys ) )
    $keys = explode( ',', $keys );
  foreach( $keys as $k => $default ) {
    if( is_numeric( $k ) ) {
      $k = $default;
      $default = 0;
    }
    init_global_var( $k, '', 'http,persistent,default', $default, 'self' );
    $v = $GLOBALS[ $k ];
    echo "\n<!-- handle_filters: $k => $v -->";
    if( $v and ( "$v" != '0' ) ) {  // only use non-null filters
      $filters[ $k ] = & $GLOBALS[ $k ];
      // fixme: what if $$k gets set to 0 later?
    }
  }
  return $filters;
}

function handle_action( $actions ) {
  global $action, $message, $mysqljetzt;
  $message = 0;
  init_global_var( 'action', 'w', 'http', 'nop' );
  if( $action ) {
    $n = strpos( $action, '_' );
    if( $n ) {
      sscanf( '0'.substr( $action, $n+1 ), '%u', & $message );
      $action = substr( $action, 0, $n );
    } else {
      init_global_var( 'message', 'w', 'http', 0 );
    }
    foreach( $actions as $a ) {
      if( $a === $action )
        return true;
    }
    need( $action === 'nop', "illegal action submitted: $action" );
  }
  $action = '';
  $message = '0';
  return false;
}


// itan handling:
//
global $itan;
$itan = false;

function set_itan() {
  global $itan, $login_sessions_id;
  $tan = random_hex_string(5);
  $id = sql_insert( 'transactions' , array(
    'used' => 0
  , 'sessions_id' => $login_sessions_id
  , 'itan' => $tan
  ) );
  $itan = $id.'_'.$tan;
}

function get_itan( $force_new = false ) {
  global $itan;
  if( $force_new or ! $itan )
    set_itan();
  return $itan;
}

// openwindow(): pop up $script (possibly, in new window) here and now:
//
function openwindow( $script, $parameters = array(), $options = array() ) {
  if( is_string( $parameters ) )
    $parameters = parameters_explode( $parameters );
  $parameters['context'] = 'js';
  open_javascript( preg_replace( '/&amp;/', '&', inlink( $script, $parameters, $options ) ) );
}

// reload_immediately(): exit the current script and open $url instead:
//
function reload_immediately( $url ) {
  $url = preg_replace( '/&amp;/', '&', $url );  // doesn't get fed through html engine here
  open_javascript( "self.location.href = '$url';" );
  exit();
}



////////////////////////////////////
//
// cgi-stuff: sanitizing parameters
//
////////////////////////////////////



// variables which can be passed by GET (subprojects may append to this array):
//
global $jlf_url_vars;
$jlf_url_vars = array(
  'dontcache' => array( 'type' => 'w' )
, 'me' => array( 'type' => '/^[a-zA-Z0-9_,]*$/' )
, 'options' => array( 'type' => 'u', 'default' => 0 )
, 'logbook_id' => array( 'type' => 'u', 'default' => 0 )
, 'f_thread' => array( 'type' => 'u', 'default' => 0 )
, 'f_window' => array( 'type' => 'w', 'default' => 0 )
, 'f_sessions_id' => array( 'type' => '0', 'default' => 0 )
, 'action' => array( 'type' => 'w', 'default' => 'nop' )
);


global $http_input_sanitized;
$http_input_sanitized = false;

function sanitize_http_input() {
  global $jlf_url_vars, $http_input_sanitized, $login_sessions_id;

  foreach( $_GET as $key => $val ) {
    $key = preg_replace( '/_N\d+_/', '_N_', $key );
    need( isset( $jlf_url_vars[ $key ] ), "unexpected variable $key passed in URL" );
    need( checkvalue( $val, $jlf_url_vars[ $key ]['type'] ) !== false , "unexpected value for variable $key passed in URL" );
  }
  if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    // all forms must post a valid and unused iTAN:
    need( isset( $_POST['itan'] ), 'incorrect form posted(1)' );
    sscanf( $_POST['itan'], "%u_%s", &$t_id, &$itan );
    need( $t_id, 'incorrect form posted(2)' );
    $row = sql_do_single_row( sql_query( 'SELECT', 'transactions', array( 'transactions_id' => $t_id ) ), true );
    need( $row, 'incorrect form posted(3)' );
    if( $row['used'] ) {
      // formular wurde mehr als einmal abgeschickt: POST-daten verwerfen:
      $_POST = array();
      echo "<div class='warn'>warning: form submitted more than once - data will be discarded</div>";
    } else {
      need( $row['itan'] == $itan, 'invalid iTAN posted' );
      print_on_exit( "<!-- login_sessions_id: $login_sessions_id, from db: {$row['sessions_id']} <br> -->" );
      need( $row['sessions_id'] == $login_sessions_id, 'invalid sessions_id' );
      // id ist noch unverbraucht: jetzt entwerten:
      sql_update( 'transactions', $t_id, array( 'used' => 1 ) );
    }
  } else {
    $_POST = array();
  }
  $http_input_sanitized = true;
}

// - $type: 
//   d : ganze Zahl
//   u : nicht-negative ganze Zahl
//   U : positive ganze Zahl (echt groesser als 0)
//   h : wendet htmlspecialchars an (erlaubt sichere und korrekte ausgabe in HTML)
//   H : wie h, aber mindestens ein non-whitespace zeichen
//   R : raw: keine Einschraenkung, keine Umwandlung
//   f : Festkommazahl
//   w : bezeichner: alphanumerisch und _; leerstring zugelassen
//   W : bezeichner: alphanumerisch und _, mindestens ein zeichen
//   l : wie w, aber zusaetzlich sind ',', '-' und '=' erlaubt
//   /.../: regex pattern. Wert wird ausserdem ge-trim()-t
function checkvalue( $val, $type ) {
  $pattern = '';
  $format = '';
  switch( substr( $type, 0, 1 ) ) {
    case 'H':
      $pattern = '/\S/';
    case 'h':
      if( get_magic_quotes_gpc() )
        $val = stripslashes( $val );
      $val = htmlspecialchars( $val );
      break;
    case 'R':
    case 'r':
      break;
    case 'U':
      $val = trim($val);
      $pattern = '/^\d*[1-9]\d*$/';
      break;
    case 'u':
      $val = trim($val);
      // eventuellen nachkommateil (und sonstigen Muell) abschneiden:
      $val = preg_replace( '/[^\d].*$/', '', $val );
      $pattern = '/^\d+$/';
      break;
    case 'd':
      $val = trim($val);
      // eventuellen nachkommateil abschneiden:
      $val = preg_replace( '/[.].*$/', '', $val );
      $pattern = '/^-{0,1}\d+$/';
      break;
    case 'f':
      $val = str_replace( ',', '.' , trim($val) );
      $format = '%f';
      $pattern = '/^[-\d.]+$/';
      break;
    case 'l':
      $val = trim($val);
      $pattern = '/^[a-zA-Z0-9_,=-]*$/';
      break;
    case 'w':
      $val = trim($val);
      $pattern = '/^[a-zA-Z0-9_]*$/';
      break;
    case 'W':
      $val = trim($val);
      $pattern = '/^[a-zA-Z0-9_]+$/';
      break;
    case '/':
      $val = trim($val);
      $pattern = $type;
      break;
    default:
      return NULL;
  }
  if( $pattern ) {
    if( ! preg_match( $pattern, $val ) ) {
      return NULL;
    }
  }
  if( $format ) {
    sscanf( $val, $format, & $val );
  }
  return $val;
}

function get_http_var( $name, $type = '' ) {
  global $http_input_sanitized, $jlf_url_vars;

  if( ! $http_input_sanitized )
    sanitize_http_input();

  if( ! $type ) {
    need( isset( $jlf_url_vars[ $name ]['type'] ), "no default type for variable $name" );
    $type = $jlf_url_vars[ $name ]['type'];
  }

  if( isset( $_POST[ $name ] ) ) {
    $val = $_POST[ $name ];
  } elseif( isset( $_GET[ $name ] ) ) {
    $val = $_GET[ $name ];
  } else {
    return NULL;
  }
  $val = checkvalue( $val, $type );
  return $val;
}

// function get_http_form_var( $name, $type = false ) {
//   if( ! get_http_var( $name, $type ) ) {
//     $GLOBALS["problem_$name"] = 'problem';
//     $GLOBALS["problems"] = true;
//   }
// }

$jlf_persistent_var_scopes = array( 'self', 'window', 'script', 'thread', 'session', 'permanent' );

function get_persistent_var( $name, $scope = false ) {
  global $jlf_persistent_vars, $jlf_persistent_var_scopes;

  if( $scope ) {
    if( isset( $jlf_persistent_vars[ $scope ][ $name ] ) )
      return $jlf_persistent_vars[ $scope ][ $name ];
  } else {
    foreach( $jlf_persistent_var_scopes as $scope )
      if( isset( $jlf_persistent_vars[ $scope ][ $name ] ) )
        return $jlf_persistent_vars[ $scope ][ $name ];
  }
  print_on_exit( "<!-- get_persistent_var: no match: [$name] from [$scope] -->" );
  return NULL;
}

// set_persistent_var:
//   $value !== NULL: make global variable $$name persistent in $scope;
//     the call will store a reference to $$name, so the final value of $name will be stored in the
//     database at the end of the script
//   $value === NULL: remove $$name from table of persistent variables
function set_persistent_var( $name, $scope = 'self', $value = false ) {
  global $jlf_persistent_vars, $jlf_persistent_var_scopes;

  if( $value === NULL ) {
    if( $scope ) {
      unset( $jlf_persistent_vars[ $scope ][ $name ] );
    } else {
      foreach( $jlf_persisten_var_scopes as $scope )
        unset( $jlf_persistent_vars[ $scope ][ $name ] );
      // fixme: remove from database here and now? remember to do so later?
    }
  } else {
    if( $value !== false ) {
      $GLOBALS[ $name ] = $value;
    }
    $jlf_persistent_vars[ $scope ][ $name ] = & $GLOBALS[ $name ];
  }
}


// init_global_var():
// - $type: nur relevant fuer from_scope 'http'
// - $default: last resort default; wenn array, so wird $default[$name] versucht
//
function init_global_var(
  $name
, $type = ''
, $from_scopes = 'http,persistent,default'
, $default = NULL
, $set_scope = false
) {
  global $jlf_persistent_vars, $jlf_persistent_var_scopes;

  if( ! is_array( $from_scopes ) )
    $from_scopes = explode( ',', $from_scopes );

  $v = NULL;
  foreach( $from_scopes as $scope ) {
    switch( $scope ) {
      case 'http':
        if( ( $v = get_http_var( $name, $type ) ) !== NULL ) {
          $source = 'http';
          break 2;
        } else {
          continue;
        }
      case 'persistent':
        print_on_exit( "<!-- init_global_var: try persistent on $name... -->" );
        if( ( $v = get_persistent_var( $name ) ) !== NULL ) {
          $source = 'persistent';
          break 2;
        } else {
          continue;
        }
      case 'default':
        if( isset( $jlf_url_vars[ $name ]['default'] ) ) {
          $v = $jlf_url_vars[ $name ]['default'];
          $source = 'global_default';
          break 2;
        } else {
          continue;
        }
      case 'keep':
        if( isset( $GLOBALS[ $name ] ) ) {
          $v = $GLOBALS[ $name ];
          $source = 'keep';
          break 2;
        } else {
          continue;
        }
      default:
        if( in_array( $scope, $jlf_persistent_var_scopes ) ) {
          if( ( $v = get_persistent_var( $name, $scope ) ) !== NULL ) {
            $source = $scope;
            break 2;
          } else {
            continue;
          }
        }
        error( 'undefined from_scope' );
    }
  }
  if( $v === NULL ) {
    if( is_array( $default ) ) {
      if( isset( $default[ $name ] ) )
        $v = $default[ $name ];
    } else {
      $v = $default;
    }
    $source = 'passed_default';
  }

  if( $v === NULL ) {
    prettydump( $jlf_persistent_vars, 'persistent vars' );
    error( "init_global_var: failed to initialize: $name" );
  }

  $vh = htmlspecialchars( $v );
  print_on_exit( "<!-- init_global_var: $name: from $source: [$vh] -->" );

  $GLOBALS[ $name ] = $v;
  if( $set_scope )
    $jlf_persistent_vars[ $set_scope ][ $name ] = & $GLOBALS[ $name];
  return $v;
}


?>
