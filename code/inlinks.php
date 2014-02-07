<?php
//
// inlinks.php (Timo Felbinger, 2008 ... 2011)
//
// functions dealing with internal hyperlinks and cgi variable passing


// pseudo-parameters: when generating links and forms with the functions below,
// these parameters will never be transmitted via GET or POST (except for 'script', 'window', 'thread' and 'format', which
// however will be packed into GET parameter 'me'); rather, they determine how the link itself will look and behave:
//
$pseudo_parameters = array(
  'anchor', 'img', 'attr', 'title', 'text', 'class', 'confirm', 'context', 'enctype', 'thread', 'window', 'script', 'inactive', 'form_id', 'id', 'display', 'format'
);

///////////////////////
//
// internal functions (not supposed to be called by consumers):
//

// get_internal_url(): create an internal URL, passing $parameters in the query string
// - parameters with value NULL and pseudo-parameters will be skipped
// - exception: pseudo-parameter 'anchor' will append an #anchor
//
function get_internal_url( $parameters ) {
  global $pseudo_parameters, $debug, $cookie_type, $cookie, $insert_nonce_in_urls;

  $url = 'index.php?';
  if( $insert_nonce_in_urls ) {
    $url .= 'd=' . random_hex_string( 6 );  // set 'dontcache'-nonce to surely prevent caching...
  }
  if( $cookie_type === 'url' ) {
    $url .= '&c=' . $cookie;
  }
  $parameters = parameters_explode( $parameters );
  if( $debug && ! isset( $parameters['debug'] ) ) {
    $parameters['debug'] = $debug;
  }
  foreach( parameters_explode( $parameters ) as $key => $value ) {
    if( $value === NULL )
      continue;
    if( in_array( $key, $pseudo_parameters ) )
      continue;
    // we only allow whitelisted characters in url; thus, the only problematic character in url will be '&';
    // '&' will be escaped as '&amp;' by an apache extfilter; this is good everywhere except when the url is
    // used inside a <script>../</script>, where '&' needs to be converted to H_AMP)
    //
    need( preg_match( '/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key ), 'illegal parameter name in url' );
    need( preg_match( '/^[a-zA-Z0-9_,.-]*$/', $value ), 'illegal parameter value in url' );
 
    $url .= "&$key=$value";
  }
  if( ( $anchor = adefault( $parameters, 'anchor' ) ) ) {
    need( preg_match( '/^[a-zA-Z0-9_]+$/', $anchor ), 'illegal anchor' );
    $url .= "#$anchor";
  }
  return $url;
}


// js_window_name():  return window name which is unique and constant for this thread of this session
//
function js_window_name( $window, $thread = '1' ) {
  global $login_sessions_id, $login_session_cookie, $jlf_application_name, $jlf_application_instance;
  static $cache;

  $index = "$window $thread";
  if( ! isset( $cache[ $index ] ) ) {
    $cache[ $index ] = md5(
      "$index $login_sessions_id $login_session_cookie $jlf_application_name $jlf_application_instance"
    );
  }
  return $cache[ $index ];
}


//////////////////////////////////////////////
//
// consumer-callable functions follow below:
//

// inlink: create internal link:
//   $target:
//     determines script and defaults for target window, parameters and options:
//     - !<form_id> will POST form <form_id>
//     - '!' or '' will POST the update_form
//     - otherwise, $target is a script name as defined in <application>/inlinks.php,
//       either to generate a GET request, or to be used (with context 'form') in the form action
//   $parameters:
//     - either "k1=v1,k2=v2" string, or array of 'name' => 'value' pairs
//     - will be passed as GET parameters or serialized in POST parameter 's' or 'q', whatever is appropriate
//     - 'name' => NULL can be used to explicitely _not_ pass parameter 'name' even if it is in defaults
//   $opts
//     options, currently unused
//
// $parameters may also contain some pseudo-parameters:
//   text, title, class, img: to specify the look of the link; see html_alink() - useful only with context 'a' (see below)
//   thread: id of target thread (will also be passed in the query string)
//   window: base name of browser target window (will also be passed in the query string)
//           (the actual window name will be a hash involving this name and more information; see js_window_name()!)
//   confirm: if set, a javascript confirm() call will pop up with text $confirm when the link is clicked
//   context: where the link is to be used
//    'a' (default):
//       - return a complete link, either <a> (with GET) or <button> (with POST)
//       - the link will contain javascript if needed: eg with 'confirm' or to open a different window
//    'js': 
//       - return plain javascript code that can be used in event handlers like onclick=...
//    'url':
//       - return a plain url. most pseudo-parameters will have no effect; only possible with GET
//    'form':
//       - return array( 'action' => ..., 'onsubmit' => ..., 'target' => ... ) with attributes for <form>
//       - 'action' maps to a plain url, never javascript
//       - most pseudo parameters will have no effect
//       - the parameter 'form_id' must be specified
//       - $target must be a script, not a !<form_id>
//       - 'onsubmit' code will created to open a different target window if that is requested.
//
if( ! function_exists('inlink') ) {
  function inlink( $target = '', $parameters = array(), $opts = array() ) {
    global $script, $window, $thread;
    global $H_SQ, $pseudo_parameters, $global_format, $jlf_persistent_vars;

    $parameters = parameters_explode( $parameters );
    // $opts = parameters_explode( $opts );

    if( $global_format !== 'html' ) {
      // \href makes no sense for (deep) inlinks - and neither should it look like a link if it isn't one:
      return adefault( $parameters, 'text', ' - ' );
    }

    $context = adefault( $parameters, 'context', 'a' );
    $inactive = adefault( $parameters, 'inactive', false );
    $inactive = adefault( $inactive, 'problems', $inactive );
  //   $loiterhelp = '';
  //   if( $problems = adefault( $parameters, 'problems' ) ) {
  //     $inactive = true;
  //     $loiterhelp = html_div( 'class=loiterhelp' ) . html_tag( 'ul' );
  //     foreach( $problems as $p ) {
  //       $loiterhelp .= html_li( '', $p );
  //     }
  //     $loiterhelp .= .html_tag( 'ul', false ) . html_div( false );
  //   }

    $parent_window = $window;
    $parent_thread = $thread;
    $parent_script = $script;

    if( ( ! $target ) || ( $target === 'self' ) ) switch( $context ) {
      case 'a':
      case 'js':
        $target = '!';
        break;
      case 'form':
      case 'url':
        $target = $script;
        $parent_script = 'self';
        break;
    }

    $js = '';
    $url = '';

    if( $target[ 0 ] === '!' ) {
      $post = 1;
      $form_id = substr( $target, 1 );

      $r = array() ;
      foreach( $parameters as $key => $val ) {
        if( in_array( $key, $pseudo_parameters ) ) {
          continue;
        }
        if( $key == 'login' ) {
          $key = 'l';
        }
        $r[ $key ] = bin2hex( $val );
      }
      $s = parameters_implode( $r );

    } else {
      $post = 0;

      $target_thread = adefault( $parameters, 'thread', $thread );
      $target_format = adefault( $parameters, 'f', 'html' );

      $script_defaults = script_defaults( $target, adefault( $parameters, 'window', '' ), $target_thread );
      if( ! $script_defaults ) {
        need( $context === 'a', "broken link in context [$context]" );
        return html_tag( 'img', array( 'class' => 'icon brokenlink', 'src' => 'img/broken.tiny.trans.gif', 'title' => "broken: $target" ), NULL );
      }
  
      // force canonical script name:
      $target_script = $script_defaults['parameters']['script'];
  
      if( $parent_script == 'self' ) {
        $parameters = array_merge( $jlf_persistent_vars['url'], $parameters );
        // don't pass default parameters (text, title, ... make little sense there) for self-links:
        $script_defaults['parameters'] = array();
      }
      $parameters = array_merge( $script_defaults['parameters'], $parameters );
      $target_window = adefault( $parameters, 'window', $window );
  
      if( ( $target_thread != 1 ) || ( $parent_thread != 1 ) ) {
        $me = sprintf( '%s,%s,%s,%s,%s,%s', $target_script, $parent_script, $target_window, $parent_window, $target_thread, $parent_thread );
      } else if( $target_window !== $parent_window ) {
        $me = sprintf( '%s,%s,%s,%s', $target_script, $parent_script, $target_window, $parent_window );
      } else if( $target_window != 'menu' ) {
        $me = sprintf( '%s,%s,%s', $target_script, $parent_script, $target_window );
      } else {
        $me = sprintf( '%s,%s', $target_script , $parent_script );
      }
      $parameters['m'] = $me;
  
      $target_format = adefault( $parameters, 'format', 'html' );
      if( $target_format != 'html' ) {
        $parameters['f'] = $target_format;
      }
  
      $url = get_internal_url( $parameters );
      $js_window_name = js_window_name( $target_window, $target_thread );
      $option_string = parameters_implode( $script_defaults['options'] );
  
      if( ( $target_window != $parent_window ) || ( $target_thread != $parent_thread ) ) {
        $js = "load_url( {$H_SQ}$url{$H_SQ}, {$H_SQ}$js_window_name{$H_SQ}, {$H_SQ}$option_string{$H_SQ} ); submit_form('update_form');";
        if( $context !== 'form' ) {
          $url = '';
        }
        // if( $target_script == $GLOBALS['script'] ) {
        //   $js = "if( warn_if_unsaved_changes() ) load_url( {$H_SQ}$url{$H_SQ} );";
        // }
      } else {
        $js = "load_url( {$H_SQ}$url{$H_SQ} );";
      }
    }
  
    if( ( $confirm = adefault( $parameters, 'confirm', '' ) ) ) {
      if( $post ) {
        $popup_id = confirm_popup( "javascript: submit_form('$form_id','$s');", array( 'text' => $confirm ) );
        $post = 0;
      } else {
        $popup_id = confirm_popup( "javascript: $js", array( 'text' => $confirm ) );
      }
      $url = '';
      $js = "show_popup('$popup_id');";
      // $confirm = "if( confirm( {$H_SQ}$confirm{$H_SQ} ) ) ";  // old-style confirmation popup
      $confirm = '';
    }
  
    switch( $context ) {
      case 'a':
        $attr = array();
        $baseclass = 'a inlink';
        $linkclass = 'href';
        foreach( $parameters as $a => $val ) {
          switch( $a ) {
            case 'title':
            case 'text':
            case 'img':
            case 'id':
              $attr[ $a ] = $val;
              break;
            case 'class':
              $linkclass = $val;
              break;
            case 'display':
              $attr['style'] = "display:$val;";
              break;
          }
        }
        $attr['class'] = merge_classes( $baseclass, $linkclass );
        if( $inactive ) {
          $attr['class'][] = 'inactive';
          if( isarray( $inactive ) ) {
            $inactive = implode( ' / ', $inactive );
          }
          if( isstring( $inactive ) ) {
            $attr['title'] = ( ( strlen( $inactive ) > 80 ) ? substr( $inactive, 0, 72 ) .'...' : $inactive );
          }
          $text = adefault( $attr, 'text', '' );
          unset( $attr['text'] );
          return html_span( $attr, $text );
        } else {
          if( $post ) {
            return html_button( $form_id, $attr, $s );
          } else {
            return html_alink( $url ? $url : "javascript: $js", $attr );
          }
        }
      case 'url':
        need( $url, 'inlink(): no plain url available' );
        return $url;
      case 'js':
        if( $inactive ) {
          return 'true;';
        } else if( $post ) {
          return "submit_form( {$H_SQ}$form_id{$H_SQ}, {$H_SQ}$s{$H_SQ} );";
        } else {
          return $js;
        }
      case 'form':
        $r = array( 'target' => '', 'action' => '#', 'onsubmit' => '', 'onclick' => '' );
        if( $inactive ) {
          return $r;
        }
        need( $url, 'inlink(): need plain url in context form' );
        need( $form_id = adefault( $parameters, 'form_id', false ), 'context form requires parameter form_id' );
        $r['action'] = $url;
        if( ( $target_window != $parent_window ) || ( $target_thread != $parent_thread ) ) {
          if( $target_window !== 'NOWINDOW' ) { // useful for pdf (with separate viewer) or file download
            $r['target'] = $js_window_name;
            $r['onsubmit'] = "openwindow( {$H_SQ}{$H_SQ}, {$H_SQ}$js_window_name{$H_SQ}, {$H_SQ}$option_string{$H_SQ}, true ) ";
          }
        } else {
          if( $form_id !== 'update_form' ) {
            $r['onsubmit'] = " warn_if_unsaved_changes(); ";
          }
        }
        return $r;
      default:
        error( 'undefined context: [$context]', LOG_FLAG_CODE, 'links' );
    }
  }
}



// entry_link(): link to generic viewer for entry $id in $table:
//
function entry_link( $table, $id, $opts = array() ) {
  global $tables;
  $opts = parameters_explode( $opts );
  if( ! $id ) {
    return span_view( 'href', 'NULL' );
  }
  $t = adefault( $opts, 'text', $table.'['.$id.']' );
  if( ( $col = adefault( $opts, 'col' ) ) ) {
    $t .= "/$col";
  }
  if( $v = adefault( $tables[ $table ], 'viewer' ) ) {
    return inlink( $v, array( $table.'_id' => $id, 'text' => $t, 'class' => 'href inlink' ) );
  } else {
    return inlink( 'any_view', array( 'any_id' => $id, 'table' => $table, 'text' => $t, 'class' => 'href inlink' ) );
  }
}

// any_link(): link to any_view.php:
//
function any_link( $table, $id, $opts = array() ) {
  global $tables;
  $opts = parameters_explode( $opts );
  if( ! $id ) {
    return span_view( 'href', 'NULL' );
  }
  $t = adefault( $opts, 'text', $table.' / '.$id );
  if( ( $col = adefault( $opts, 'col' ) ) ) {
    $t .= " / $col";
  }
  if( adefault( $opts, 'validate' ) ) {
    if( ! sql_query( $table, $id, 'single_field=COUNT' ) ) {
      return span_view( 'red bold', $t );
    }
  }
  return inlink( 'any_view', array(
    'any_id' => $id
  , 'table' => $table
  , 'text' => $t
  , 'class' => 'href inlink'
  , 'window' => "{$table}_$id"  // unlike other views: open unlimited windows
  ) );
}

function action_link( $get_parameters = array(), $post_parameters = array() ) {
  global $open_environments;

  $get_parameters = parameters_explode( $get_parameters, 'script' );
  $post_parameters = parameters_explode( $post_parameters, 'action' );
  if( ! isset( $get_parameters['class'] ) ) {
    $get_parameters['class'] = 'button quads';
  }

  $form_id = open_form( $get_parameters, $post_parameters, 'hidden' );
  return inlink( "!$form_id", $get_parameters );
}

// openwindow(): pop up $script (possibly, in new window) here and now:
//
function openwindow( $script, $parameters = array(), $options = array() ) {
  $parameters = parameters_explode( $parameters );
  $parameters['context'] = 'js';
  open_javascript( str_replace( '&', H_AMP, inlink( $script, $parameters, $options ) ) );
}

// load_immediately(): exit the current script and open $url instead:
//
// function load_immediately( $url ) {
//   global $H_SQ;
//   $url = str_replace( '&', H_AMP, $url );  doesn't get fed through html engine here
//   open_javascript( "self.location.href = {$H_SQ}$url{$H_SQ};" );
//   exit(); COMMIT/ROLLBACK?
// }

// function schedule_reload() {
//   global $H_SQ;
//   js_on_exit( "submit_form( {$H_SQ}update_form{$H_SQ} ); " );
// }

function reinit( $reinit = 'self' ) {
  need( isset( $GLOBALS['reinit'] ) );
  // debug( $action, 'reinit' );
  $_GET['action'] = $GLOBALS['action'] = '';
  need( ( $reinit == 'reset' ) || ( $reinit === 'self' ) );
  $GLOBALS['reinit'] = $reinit;
}


/////////////////////////
//
// handlers and helper functions to handle parameters passed for frequently used mechanisms and gadgets:
//


// handle_actions():
// - make sure $action is in list $actions, or equal to 'nop' or ''
// - appends $actions to global list of actions that can be handled
//
function handle_actions( $actions ) {
  global $action, $message, $actions_handled;

  $actions = parameters_explode( $actions, array( 'default_value' => array() ) );
  if( isstring( $actions ) ) {
    $actions = explode( ',', $actions );
  }
  $actions_handled = array_merge( $actions_handled, $actions );
  if( ( $action === '' ) || ( $action === 'nop' ) ) {
    $action = '';
    $message = '0';
    return true;
  }

  $params = adefault( $actions, $action );
  if( $params !== false ) {
    // TODO: evaluate $params ?
    return true;
  } else {
    error( "illegal action submitted: $action" );
  }
}



////////////////////////////////////
//
// cgi-stuff: sanitizing parameters
//
////////////////////////////////////


// cgi variables which can be passed by GET or POST:
// will be merged with subprojects' $cgi_get_vars
//
$jlf_cgi_get_vars = array(
  'debug' => array( 'type' => 'u' )
, 'id' => array( 'type' => 'u' ) // pseudo entry: will be used for all primary keys of the form <table>_id (but is pseudo-parameter and cannot itself be passed by inlink()!)
, 'options' => array( 'type' => 'u' )
, 'referent' => array( 'type' => 'W128' )
, 'fscript' => array( 'type' => 'w64' )
, 'table' => array( 'type' => 'w128' )
, 'application' => array( 'type' => 'W128' )
, 'offs' => array( 'type' => 'l', 'pattern' => '/^\d+x\d+$|^undefinedxundefined$/', 'default' => '0x0' )
);

// cgi variables which may only be POSTed:
// will be merged with subprojects' $cgi_vars
//
$jlf_cgi_vars = array(
  'action' => array( 'type' => 'w', 'default' => 'nop' )
, 'message' => array( 'type' => 'u' )
, 'SEARCH' => array( 'type' => 'h' )
);

// itan handling:
//
$itan = false;

function get_itan( $name = '' ) {
  global $itan, $login_sessions_id;

  need( $login_sessions_id );
  if( ! $itan ) {
    $itan = array();
    foreach( array( 'update', 'other' ) as $key ) {
      $tan = random_hex_string( 5 );
      $id = sql_insert( 'transactions', array(
        'used' => 0
      , 'sessions_id' => $login_sessions_id
      , 'itan' => $tan
      ) );
      $itan[ $key ] = $id.'_'.$tan;
    }
  }
  return ( ( $name == 'update_form' ) ? $itan['update'] : $itan['other'] );
}

function sanitize_http_input() {
  global $cgi_get_vars, $cgi_vars, $login_sessions_id, $info_messages, $H_SQ, $H_DQ, $initialization_steps, $jlf_persistent_vars, $insert_itan_in_forms, $request_method;

  if( adefault( $initialization_steps, 'http_input_sanitized' ) ) {
    return;
  }
  need( ! get_magic_quotes_gpc(), 'whoa! magic quotes is on!' );
  need( $login_sessions_id );
  foreach( $_GET as $key => $val ) {
    if( isnumeric( $val ) )
      $_GET[ $key ] = $val = "$val";
    need( isstring( $val ), 'GET: non-string value detected' );
    need( check_utf8( $key ), 'GET variable name: invalid utf-8' );
    need( preg_match( '/^[a-zA-Z][a-zA-Z0-9_]*$/', $key ), 'GET variable name: not an identifier' );
    need( check_utf8( $val ), 'GET variable value: invalid utf-8' );
    $key = preg_replace( '/_N[a-z]+\d+_/', '_N_', $key );

    if( isset( $cgi_get_vars[ $key ] ) ) {
      $t = $cgi_vars[ $key ]; // need $cgi_var: $cgi_get_vars holds uncomplete type info
    } else if( preg_match( '/^[a-zA-Z0-9]+_id$/', $key ) ) { // allow arbitrary primary keys
      $t = $cgi_vars['id'];
    } else {
      error( "GET: unexpected variable: [$key]" );
    }
    need( checkvalue( $val, $t ) !== NULL , "GET: unexpected value for variable [$key]" );
    if( adefault( $t, 'persistent' ) === 'url' ) {
      $jlf_persistent_vars['url'][ $key ] = $val;
    }
  }
  if( ( $request_method == 'POST' ) && $_POST /* allow to discard $_POST when creating new session, avoiding confusion below */ ) {
    if( $insert_itan_in_forms ) {
      // all forms must post a valid and unused iTAN:
      need( isset( $_POST['itan'] ), 'incorrect form posted(1)' );
      $itan = $_POST['itan'];
      need( preg_match( '/^\d+_[0-9a-f]+$/', $itan ), 'incorrect form posted(2)' );
      sscanf( $itan, "%u_%s", /* & */ $t_id, /* & */ $itan );
      need( $t_id, 'incorrect form posted(3)' );
      $row = sql_query( 'transactions', "$t_id,single_row=1,default=" );
      need( $row, 'incorrect form posted(4)' );
      if( $row['used'] ) {
        // form was submitted more than once: discard all POST-data:
        $_POST = array();
        $info_messages[] = html_tag( 'div ', 'class=warn bigskips', 'warning: form submitted more than once - data will be discarded' );
      } else {
        need( $row['itan'] === $itan, 'invalid iTAN posted' );
        // print_on_exit( H_LT."!-- login_sessions_id: $login_sessions_id, from db: {$row['sessions_id']} --".H_GT );
        if( (int)$row['sessions_id'] !== (int)$login_sessions_id ) {
          // window belongs to different session - probably leftover from a previous login. discard POST, issue warning and update window:
          $_POST = array();
          $info_messages[] = html_tag( 'div', 'class=warn bigskips', 'warning: invalid sessions id - window will be updated' );
          js_on_exit( "setTimeout( {$H_DQ}submit_form( {$H_SQ}update_form{$H_SQ} ){$H_DQ}, 3000 );" );
        }
        // ok, id was unused; flag it as used:
        sql_update( 'transactions', $t_id, array( 'used' => 1 ) );
      }
    }
    foreach( $_POST as $key => $val ) {
      if( isnumeric( $val ) ) {
        $val = "$val";
      }
      need( isstring( $val ), 'POST: non-string value detected' );
      need( check_utf8( $key ), 'POST variable name: invalid utf-8' );
      need( preg_match( '/^[a-zA-Z][a-zA-Z0-9_]*$/', $key ), 'POST variable name: not an identifier' );
      need( check_utf8( $val ), 'POST variable value: invalid utf-8' );
      $_GET[ $key ] = "$val";
    }
  }
  unset( $_POST );

  // post-process cgi parameters:
  // - discard arbitrary "priority"-prefix, to allow several input elements for same parameter, with
  //   higher priority names overriding lower priority ones
  // - combine parameters distinguished only by OR-prefix into one bitfield (only works for lowest priority)
  // - translate UIDs to strings
  foreach( $_GET as $key => $value ) {
    if( $key[ 0 ] !== 'P' ) {
      unset( $_GET[ $key ] );
      $_GET[ 'P0_' . $key ] = $value;
    }
  }
  need( ksort( $_GET, SORT_STRING ) );
  $cooked = array();
  foreach( $_GET as $key => $value ) {
    $key = preg_replace( '/^P[a-zA-Z0-9]*_/', '', $key );
    // debug( $key, "postprocess: $key" );
    if( preg_match( '/^OR[0-9]*_(.*)$/', $key, /* & */ $matches ) ) {
      $value = checkvalue( $value, jlf_complete_type( array( 'type' => 'u' ) ) );
      need( $value !== null, 'malformed bitfield detected' );
      $key = $matches[ 1 ];
      // php bitwise operators do strange things on strings, so we have to be careful:
      $value = (int)$value | (int)(adefault( $cooked, $key, 0 ) );
    } else if( strncmp( $key, 'UID_', 4 ) == 0 ) {
      if( ( $value !== '' ) && ( $value !== '0' ) ) {
        $value = uid2value( $value );
      }
      $key = substr( $key, 4 );
    } else if( strncmp( $key, 'DEREF_', 6 ) == 0 ) {
      need( preg_match( '/^[a-zA-Z0-9_]*$/', $value ), "malformed reference posted" );
      if( ( $value = adefault( $_GET, $value, false ) ) === false ) {
        continue;
      }
      $key = substr( $key, 6 );
    }
    $cooked[ $key ] = $value;
  }

  $_GET = $cooked;
  $GLOBALS['initialization_steps']['http_input_sanitized'] = true;
}


$jlf_persistent_var_scopes = array( 'self', 'view', 'window', 'script', 'thread', 'session', 'permanent', /* 'url ...is a pseudo-scope: not saved in db but passed in url */ );

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
  return NULL;
}

// set_persistent_var:
//   $value === false: make global variable $$name persistent in $scope;
//     the call will store a reference to $$name, so the final value of $name will be stored in the
//     database at the end of the script
//   $value === NULL: remove $$name from table of persistent variables
function set_persistent_var( $name, $scope = 'self', $value = false ) {
  global $jlf_persistent_vars, $jlf_persistent_var_scopes;

  if( $value === false ) {
    if( isset( $GLOBALS[ $name ] ) ) {
      $value = & $GLOBALS[ $name ];
    } else {
      $value = NULL;
    }
  }
  if( $value === NULL ) {
    if( $scope ) {
      unset( $jlf_persistent_vars[ $scope ][ $name ] );
    } else {
      foreach( $jlf_persistent_var_scopes as $scope )
        unset( $jlf_persistent_vars[ $scope ][ $name ] );
      // fixme: remove from database here and now? remember to do so later?
    }
  } else {
    $jlf_persistent_vars[ $scope ][ $name ] = & $value;
  }
}

function mv_persistent_vars( $scope, $pattern, $replace ) {
  global $jlf_persistent_vars;
  foreach( $jlf_persistent_vars[ $scope ] as $name => $val ) {
    if( preg_match( $pattern, $name ) ) {
      $newname = preg_replace( $pattern, $replace, $name );
      if( $newname !== $name ) {
        $jlf_persistent_vars[ $scope ][ $newname ] = & $jlf_persistent_vars[ $scope ][ $name ];
        unset( $jlf_persistent_vars[ $scope ][ $name ] );
      }
    }
  }
}


function handle_time_post( $name, $type, $old ) {
  $got_something = 0;
  if( isset( $_GET[ $name ] ) ) {
    $v = $_GET[ $name ];
    $got_something = 1;
  } else {
    $v = NULL;
  }
  $d = datetime_wizard( $v, $old );
  for( $j = 1; $j < strlen( $type ); $j++ ) {
    $field = $type[ $j ];
    if( $r = adefault( $_GET, "{$name}_T{$field}" ) !== false ) {
      if( ( $r = checkvalue( $r, 'u4' ) ) === NULL ) {
        continue;
      }
      $d = datetime_wizard( $d, NULL, array( $field => $r ) );
      $got_something = 1;
    }
  }

  return $got_something ? adefault( $d, 'utc', '0' ) : NULL;
}


// init_var( $name, $opts ): retrieve value for $name. $opts is associative array; most fields are optional:
//   'sources': array or space-separated list of sources to try in order. possible sources are:
//       initval (deprecated synonym: init): retrieve $opts['initval'] if it exists
//       persistent: try to retrieve persistent var $name
//       <persistent_var_scope> ( e.g. 'view', 'self', ...): like 'persistent' but only try specified scope
//       http: try $_GET[ $name ] ($_POST has been merged into $_GET and overrides $_GET if both are set)
//       default: use default depending on type. this source will always be used as last resort if a default
//                value !== NULL exists, unless option 'nodefault' is specified
//   'type': used to complete type information 'pattern', 'normalize', 'default', see jlf_get_complete_type()
//   'pattern', 'normalize': used to normalize and type-check value via checkvalue()
//   'default': default value; if not NULL, will be used as last source. This value implies 'no filtering'.
//   'nodefault': flag: don't use default value even if we have one
//   'initval': initial value (to retrieve if 'initval' is specified as source, and to check for modification)
//   'failsafe': boolean option:
//      1 (default): if a source yields some value but checkvalue fails, reject and try next source
//        - if init_var() returns with failsafe=1, the variable is guaranteed to have a legal value
//        - use this if you need a legal value, and have a legal last-resort default 
//      0: stop after first source yields something, even in case of type mismach
//          in particular: if there is any default, init_var() will return it as last resort, even if it is not legal
//        - use to process user input which may be flagged as incorrect and returned to user if needed
//        - also useful to initialize data in the first place, even if defaults are not legal values
//        - if no legal value is obtained, init_var() returns with value === NULL and offending value in 'raw' (see below)
//   'global': ref-bind $name to value in global scope; if option maps to an identifier, use this instead of $name
//   'set_scopes': array or space-separated list of persistent variable scopes to store value in
//   'flag_problems', 'flag_modified': boolean flags, defaulting to 0, to toggle setting of css-class in output
//      (output fields 'problem' and 'modified' will always be set)
//   'type': type abbreviation; can be used to set 'pattern', 'default', 'normalize'
//
//  return value: associative array: contains all $opts, plus additionally the following fields:
//    'name': argument $name
//    'raw': raw value: unchecked as received from whatever source (cgi-input is guaranteed to be valid utf-8 though)
//    'value': type-checked and normalized value, or NULL if type mismatch (only possible with failsafe off)
//    'normalized': if value !== NULL, a reference to value; otherwise, offending value, normalized for redisplay
//    'source': keyword of source from which value was retrieved
//    'problem': string: either empty or 'type mismatch'
//    'modified': non-empty iff $opts['initval'] is set and value !== $opts['initval']
//    'class': suggested CSS class: either 'problem', 'modified' or '', depending on the two fields above
//             and on the 'flag_problems', 'flag_modified' options
//
function init_var( $name, $opts = array() ) {
  global $jlf_persistent_vars, $jlf_persistent_var_scopes, $cgi_vars, $initialization_steps;

  $opts = parameters_explode( $opts );

  $type = jlf_get_complete_type( $name, $opts );
  if( adefault( $opts, 'nodefault' ) ) {
    $default = NULL;
  } else {
    $default = $type['default']; // guaranteed to be set, but may also be NULL
  }

  $sources = adefault( $opts, 'sources', 'http persistent initval' );
  if( ! is_array( $sources ) ) {
    $sources = explode( ' ', $sources );
  }

  if( $default !== NULL ) {
    $sources[] = 'default';
  }

  $failsafe = adefault( $opts, 'failsafe', true );
  $flag_problems = adefault( $opts, 'flag_problems', 0 );
  $flag_modified = adefault( $opts, 'flag_modified', 0 );

  $v = NULL;
  foreach( $sources as $source ) {
    $file_size = 0;
    switch( $source ) {
      case '':
        continue 2;
      case 'http':
        need( $initialization_steps['http_input_sanitized'] );
        if( $type['type'][ 0 ] == 'R' ) {
          if( isset( $_FILES[ $name ] ) && $_FILES[ $name ]['tmp_name'] && ( $_FILES[ $name ]['size'] > 0 ) ) {
            $v = base64_encode( file_get_contents( $_FILES[ $name ]['tmp_name'] ) );
            // $mime_type = $_FILES[ $name ]['type']; // this is pretty useless and can't be trusted anyway!
            $file_size = $_FILES[ $name ]['size'];
            break 1;
          } else {
            continue 2;
          }
        } else if( $type['type'][ 0 ] == 't' ) {
          if( ( $v = handle_time_post( $name, substr( $type['type'], 1 ), adefault( $opts, 'initval', $default ) ) ) !== NULL ) {
            break 1;
          }
        } else if( isset( $_GET[ $name ] ) ) {
          $v = $_GET[ $name ];
          break 1;
        } else {
          continue 2;
        }
      case 'persistent':
        if( ( $v = get_persistent_var( $name ) ) !== NULL ) {
          break 1;
        } else {
          continue 2;
        }
      case 'default':
        if( isarray( $default ) && isset( $default[ $name ] ) ) {
          $v = $default[ $name ];
          break 1;
        } else if( $default !== NULL ) {
          $v = $default;
          break 1;
        } else {
          continue 2;
        }
      case 'init':
        $source = 'initval';
      case 'initval':
        if( isset( $opts['initval'] ) ) {
          $v = $opts['initval'];
          break 1;
        } else {
          continue 2;
        }
      default:
        if( in_array( $source, $jlf_persistent_var_scopes ) ) {
          if( ( $v = get_persistent_var( $name, $source ) ) !== NULL ) {
            $source = 'persistent';
            break 1;
          } else {
            continue 2;
          }
        }
        error( "undefined source: [$source]", LOG_FLAG_CODE, 'init' );
    }
    $v = (string) $v;
    $vn = normalize( $v, $type );
    $type['normalize'] = array(); // no need to normalize again

    debug( $v, "init_var [$name]: considering from $source:", 'init_var', $name );
 
    $type_ok = ( ( $vc = checkvalue( $vn, $type ) ) !== NULL );
    if( $file_size > 0 ) {
      if( ! ( $file_size <= $type['maxlen'] ) ) {
        $v = '';
        $vc = NULL;
        $vn = NULL;
        $type_ok = false;
      }
    }
    debug( $vc, "init_var [$name]: from checkvalue:", 'init_var', $name );
    if( $type_ok || ! $failsafe ) {
      break;
    }
    $v = NULL;
  }

  if( $v === NULL ) {
    error( "init_var: failed to initialize: [$name]", LOG_FLAG_CODE, 'init' );
  }

  $r = $opts;
  $r['name'] = $name;
  $r['source'] = $source;
  $r['raw'] = $v;
  if( $vc === NULL ) {
    $r['normalized'] = $vn;
  } else {
    $r['normalized'] = & $vc;
  }
  $r['value'] = & $vc;
  $r['class'] = '';
  $r['modified'] = '';
  if( ( $vc !== NULL ) && isset( $opts['initval'] ) ) {
    if( $opts['initval'] !== $vc ) {
      $r['modified'] = 'modified';
      if( $flag_modified ) {
        $r['class'] = 'modified';
      }
    }
  }
  $r['problem'] = '';
  if( ! $type_ok ) {
    $r['problem'] = 'type mismatch';
    if( $flag_problems ) {
      $r['class'] = 'problem';
    }
  }
  if( ( $problems = adefault( $opts, 'problems' ) ) ) {
    need( 0, 'deprecated?' );
//    if( adefault( $problems, $name, NULL ) === $r['raw'] ) {
//      $r['class'] = 'problem';
//    }
  }

  if( ( $global = adefault( $opts, 'global', false ) ) !== false ) {
    if( $global === true || isnumeric( "$global" ) ) {
      $global = $name;
    }
    $GLOBALS[ $global ] = & $vc;
  }

  if( ( $set_scopes = adefault( $opts, 'set_scopes', false ) ) ) {
    if( isstring( $set_scopes ) ) {
      $set_scopes = explode( ' ', $set_scopes );
    }
    foreach( $set_scopes as $scope ) {
      $jlf_persistent_vars[ $scope ][ $name ] = & $vc;
    }
  }
  debug( $r, "init_var [$name]: return value:", 'init_var', $name );

  return $r;
}



// init_fields():
// initialize variables for form fields and filters from various sources.
// $fields: list of names, or array <name> => <per-field-options> of variables to initialize
//   init_var will be called for every <name> (unless modified by options below)
// $opts:
//  'merge': array of already initialized variables (from previous call typically) to merge into result
//  'global': flag: ref-bind variables in global scope under <global_name> (default: <name>)
//  'failsafe': as in init_var()
//  'sources' as in init_var(); defaults to 'http persistent init default'
//  'reset': flag: default sources are 'init default' (where 'init' usually means: use value from database)
//  'tables', 'rows': to determine type and previous values
//  'sql_prefix': prefix to derive sql_name (see below)
//  'cgi_prefix': prefix to derive cgi_name (see below); default: <sql_prefix>
// per-field options: most of the above and
//  'basename': name to look for global type information; default: <name>
//  'sql_name': name of sql column, for lookup of existing values and for filter expressions; default: <sql_prefix><name>
//  'cgi_name': name for init_var(): used as name of cgi vars and persistent vars. default: <cgi_prefix><name>
//              (useful in particular when arrays of similar fields need disambiguation in order to be posted in the same form)
//  'global': true|<global_name>: global name to bind to; default: <cgi_name>
//  'type', 'pattern', 'default'... as usual
//  'initval': initial value (with source 'init') and to flag modifications
//   - will default to 'default'
//   - can be specified to force initial value different from default
//   - for db entries, 'initval' will usually be derived from 'rows'
//  'relation': use "$sql_name $relation" in '_filters' map (see below)
// 
// rv: array of 'cgi_name' => data mappings, where data is the output of init_var
// rv will have additional fields
//  '_problems': maps fieldnames to offending raw values
//  '_changes': maps fieldnames to new raw(!) values
//  '_filters': maps fieldnames to non-default values (default value means: no filtering)
//
function init_fields( $fields, $opts = array() ) {
  $fields = parameters_explode( $fields, array( 'default_value' => array() ) );
  $opts = parameters_explode( $opts );

  // merge existing fields into $rv, being careful not to break references:
  //
  if( isset( $opts['merge'] ) ) {
    $rv = & $opts['merge'];
  } else {
    $rv = array();
  }
  foreach( array( '_problems', '_changes', '_filters' ) as $n ) {
    if( ! isset( $rv[ $n  ] ) ) {
      $rv[ $n ] = array();
    }
  }

  $rows = adefault( $opts, 'rows', array() );
  // if( ( $rows = adefault( $opts, 'rows', array() ) ) ) {
  //   $rows = parameters_explode( $rows, array( 'default_value' => array() ) );
  // }
  if( ( $tables = adefault( $opts, 'tables', array() ) ) ) {
    $tables = parameters_explode( $tables, array( 'default_value' => 1 ) );
  }
  $global_global = adefault( $opts, 'global', false );
  $failsafe = adefault( $opts, 'failsafe', 1 );
  $reset = adefault( $opts, 'reset', 0 );
  $readonly = adefault( $opts, 'readonly', 0 );
  $flag_problems = adefault( $opts, 'flag_problems', 0 );
  $flag_modified = adefault( $opts, 'flag_modified', 0 );
  $set_scopes = adefault( $opts, 'set_scopes', 'self' );
  $sql_prefix = adefault( $opts, 'sql_prefix', '' );
  $cgi_prefix = adefault( $opts, 'cgi_prefix', $sql_prefix );

  need( ! isset( $opts['prefix'] ), 'option prefix is deprecated' );

  $sources = adefault( $opts, 'sources', 'http persistent initval default' );

  foreach( $fields as $fieldname => $specs ) {

    $specs = parameters_explode( $specs, 'type' );
    $specs['sql_name'] = $sql_name = adefault( $specs, 'sql_name', $sql_prefix . $fieldname );
    $specs['basename'] = $basename = adefault( $specs, 'basename', $fieldname );
    $specs['cgi_name'] = $cgi_name = adefault( $specs, 'cgi_name', $cgi_prefix . $fieldname );

    // determine type info for field:
    //
    $specs['rows'] = $rows;
    if( isset( $specs['table'] ) ) {
      $specs['tables'] = array( $specs['table'] => 1 );
    } else {
      $specs['tables'] = $tables;
    }

    $specs = array_merge( $specs, jlf_get_complete_type( $basename, $specs ) );
    unset( $specs['rows'] );
    unset( $specs['tables'] );

    // determine initial value for field:
    //
    if( ! isset( $specs['initval'] ) ) {
      $t = adefault( $specs, 'table', false );
      foreach( $rows as $table => $row ) {
        if( $t && ( $t !== $table ) )
          continue;
        if( isset( $row[ $sql_name ] ) ) {
          $specs['initval'] = $row[ $sql_name ];
          break;
        }
        $n = strlen( $table );
        if( substr( $sql_name, 0, $n + 1 ) === "{$table}_" ) {
          foreach( $row as $col => $val ) {
            if( substr( $sql_name, $n + 1 ) === $col ) {
              $specs['initval'] = $val;
              break 2;
            }
          }
        }
      }
    }
    if( ! isset( $specs['initval'] ) ) {
      $specs['initval'] = $specs['default'];
    }

    if( ! isset( $specs['sources'] ) ) {
      if( adefault( $specs, 'reset', $reset ) || adefault( $specs, 'readonly', $readonly ) ) {
        $specs['sources'] = 'initval default';
      } else {
        $specs['sources'] = $sources;
      }
    }

    if( ( $global = adefault( $specs, 'global', $global_global ) ) ) {
      $specs['global'] = ( ( isstring( $global ) && ! isnumeric( $global) ) ? $global : $cgi_name );
    }

    $specs['set_scopes'] = adefault( $specs, 'set_scopes', $set_scopes );
    $specs['failsafe'] = adefault( $specs, 'failsafe', $failsafe );
    $specs['flag_problems'] = $flag_problems;
    $specs['flag_modified'] = $flag_modified;

    $var = init_var( $cgi_name, $specs );

    if( adefault( $var, 'problem' ) ) {
      $rv['_problems'][ $fieldname ] = $var['raw'];
    }
    if( adefault( $var, 'modified' ) ) {
      $rv['_changes'][ $fieldname ] = $var['raw'];
    }
    $rv[ $fieldname ] = $var;
    // if we have a valid non-default value, set filter entry:
    // - default value for filters means: "no filtering"
    // - you can use 'initval' to enforce an initial value different from 'default'
    if( ( $var['value'] !== NULL ) && ( (string)$var['value'] !== (string)$var['default'] ) ) {
      if( ( $relation = adefault( $specs, 'relation' ) ) ) {
        $rv['_filters'][ "$sql_name $relation" ] = & $var['value'];
      } else {
        $rv['_filters'][ $sql_name ] = & $var['value'];
      }
    }
    unset( $var );
  }
  // debug( $rv, 'init_fields: out:' );

  return $rv;
}


?>
