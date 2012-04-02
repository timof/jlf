<?php
//
// inlinks.php (Timo Felbinger, 2008 ... 2011)
//
// functions dealing with internal hyperlinks and cgi variable passing


// pseudo-parameters: when generating links and forms with the functions below,
// these parameters will never be transmitted via GET or POST (except for 'script', 'window' and 'thread', which
// however will be packed into GET parameter 'me'); rather, they determine how the link itself will look and behave:
//
$pseudo_parameters = array(
  'img', 'attr', 'title', 'text', 'class', 'confirm', 'anchor', 'url', 'context', 'enctype', 'thread', 'window', 'script', 'inactive', 'form_id', 'id', 'display'
);

///////////////////////
//
// internal functions (not supposed to be called by consumers):
//

// get_internal_url(): create an internal URL, passing $parameters in the query string.
// - parameters with value NULL will be skipped
// - pseudo-parameters (see open) will always be skipped except for two special cases:
//   - anchor: append an #anchor to the url
//   - url: return the value of this parameter immediately (overriding all others)
//
function get_internal_url( $parameters ) {
  global $pseudo_parameters, $debug;

  $url = 'index.php?';
  if( ! getenv( 'robot' ) ) {
    $url .= 'dontcache=' . random_hex_string( 6 );  // the only way to surely prevent caching...
  }
  $anchor = '';
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
 
    switch( $key ) {
      case 'anchor':
        // need( preg_match( '/^[a-zA-Z0-9_,.-]*$/', $value ), 'illegal anchor value in url' );
        $anchor = "#$value";
        continue 2;
      case 'url':
        // need( preg_match( '/^[a-zA-Z0-9_,.-]*$/', $value ), 'illegal url value in url' );
        return $value;
    }
    $url .= "&$key=$value";
  }
  if( $debug ) {
    $url .= '&debug=1';
  }
  $url .= $anchor;
  return $url;
}


// js_window_name():
//   return window name which is unique and constant for this thread of this session
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
//   $script:
//     determines script and defaults for target window, parameters and options:
//     - default: 'self'; empty string '' also maps to 'self'
//       'self' will switch to '!update' if possible
//     - special value '!submit': will return link to submit form $parameters['form_id'], or the update_form by default.
//     - special value '!update' will return link to submit the update_form.
//   $parameters:
//     - GET parameters to be passed in url: either "k1=v1,k2=v2" string, or array of 'name' => 'value' pairs
//       'name' => NULL can be used to explicitely _not_ pass parameter 'name' even if it is in defaults
//     - in case of '!submit' or '!update', parameters will be serialized and POSTed in the parameter s
//   $options:
//     window options to be passed in javascript:window_open() (will override defaults)
//
// $parameters may also contain some pseudo-parameters:
//   text, title, class, img: to specify the look of the link; see html_alink()
//   thread: id of target thread (will also be passed in the query string)
//   window: base name of browser target window (will also be passed in the query string)
//           (the actual window name will be a hash involving this name and more information; see js_window_name()!)
//   confirm: if set, a javascript confirm() call will pop up with text $confirm when the link is clicked
//   context: where the link is to be used
//    'a' (default):
//       - return a complete <a href=...>...</a> link. 
//       - the link will usually contain javascript, eg to pass the current window scroll position in the url,
//         or if $confirm is specified
//    'js': 
//       - always return javascript code that can be used in event handlers like onclick=...
//    'action' alias 'form':
//       - return array( 'action' => ..., 'onsubmit' => ..., 'target' => ... ) with attributes for <form>
//       - 'action' maps to plain url, never javascript (most pseudo parameters will have no effect),
//         to be used in <form action=...> attribute.
//       - the parameter 'form_id' must be specified.
//       - 'onsubmit' code will created to open a different target window if that is requested.
//
function inlink( $script = '', $parameters = array(), $options = array() ) {
  global $H_SQ, $current_form, $pseudo_parameters;
  $parameters = parameters_explode( $parameters );
  $options = parameters_explode( $options );

  $context = adefault( $parameters, 'context', 'a' );
  $inactive = adefault( $parameters, 'inactive', 0 );
  $js = '';
  $url = '';

  $parent_window = $GLOBALS['window'];
  $parent_thread = $GLOBALS['thread'];
  $script or $script = 'self';
  if( ( $script === 'self' ) && ( adefault( $current_form, 'id' ) === 'update_form' ) && ( $context !== 'form' ) ) {
    $script = '!update';
  }
  if( ( $script === '!submit' ) || ( $script === '!update' ) ) {
    $form_id = ( ( $script === '!update' ) ? 'update_form' : adefault( $parameters, 'form_id', 'update_form' ) );
    $r = array();
    $l = '';
    foreach( $parameters as $key => $val ) {
      if( in_array( $key, $pseudo_parameters ) )
        continue;
      if( $key == 'login' )
        $l = $val;
      else
        $r[ $key ] = bin2hex( $val );
    }
    $s = parameters_implode( $r );
    // debug( $s, 's' );
    $js = $inactive ? 'true;' : "submit_form( {$H_SQ}$form_id{$H_SQ}, {$H_SQ}$s{$H_SQ}, {$H_SQ}$l{$H_SQ} ); ";
  } else {
    if( $script === 'self' ) {
      $parent_script = 'self';
      $target_script = $GLOBALS['script'];
    } else {
      $parent_script = $GLOBALS['script'];
      $target_script = $script;
    }

    $target_thread = adefault( $parameters, 'thread', $GLOBALS['thread'] );
    $enforced_target_window = adefault( $parameters, 'window', '' );

    $script_defaults = script_defaults( $target_script, $enforced_target_window, $target_thread );
    need( $script_defaults, "no defaults for target script $target_script" );

    // force canonical script name:
    $target_script = $script_defaults['parameters']['script'];

    if( $parent_script == 'self' ) {
      // don't pass default parameters (text, title, ... make little sense there) for self-links:
      $script_defaults['parameters'] = array();
    }

    $parameters = array_merge( $script_defaults['parameters'], $parameters );
    $target_window = adefault( $parameters, 'window', $GLOBALS['window'] );

    $parameters['me'] = sprintf( '%s,%s,%s,%s,%s,%s'
    , $target_script , $target_window , $target_thread
    , $parent_script , $parent_window , $parent_thread
    );

    $url = get_internal_url( $parameters );
    $options = array_merge( $script_defaults['options'], $options );
    $js_window_name = js_window_name( $target_window, $target_thread );
    $option_string = parameters_implode( $options );

    if( ( $target_window != $parent_window ) || ( $target_thread != $parent_thread ) ) {
      $js = "load_url( {$H_SQ}$url{$H_SQ}, {$H_SQ}$js_window_name{$H_SQ}, {$H_SQ}$option_string{$H_SQ} );";
    } else {
      $js = "if( warn_if_unsaved_changes() ) load_url( {$H_SQ}$url{$H_SQ} );";
    }
  }

  if( ( $confirm = adefault( $parameters, 'confirm', '' ) ) ) {
    $confirm = "if( confirm( {$H_SQ}$confirm{$H_SQ} ) ) ";
  }

  switch( $context ) {
    case 'a':
      $attr = array();
      foreach( $parameters as $a => $val ) {
        switch( $a ) {
          case 'title':
          case 'text':
          case 'img':
          case 'class':
          case 'id':
            $attr[ $a ] = $val;
            break;
          case 'display':
            $attr['style'] = "display:$val;";
            break;
        }
        $attr['class'] = adefault( $attr, 'class', 'href' ) . ( $inactive ? ' inactive' : '' );
      }
      return html_alink( $inactive ? '#' : "javascript: $confirm $js", $attr );
    case 'js':
      return ( $inactive ? 'true;' : "$confirm $js" );
    case 'form':
    case 'action':
      $r = array( 'target' => '', 'action' => '#', 'onsubmit' => '' );
      if( $inactive )
        return $r;
      need( $url, 'inlink(): need plain url in context form' );
      need( $form_id = adefault( $parameters, 'form_id', false ), 'context form requires parameter form_id' );
      $r['action'] = $url;
      if( ( $target_window != $parent_window ) || ( $target_thread != $parent_thread ) ) {
        $r['target'] = $js_window_name;
        $r['onsubmit'] = "window.open( {$H_SQ}{$H_SQ}, {$H_SQ}$js_window_name{$H_SQ}, {$H_SQ}$option_string{$H_SQ} ).focus(); document.forms.update_form.submit(); ";
      } else {
        if( $form_id !== 'update_form' )
          $r['onsubmit'] = " warn_if_unsaved_changes(); ";
      }
      return $r;
    default:
      error( 'undefined context: [$context]' );
  }
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
function load_immediately( $url ) {
  global $H_SQ;
  $url = str_replace( '&', H_AMP, $url );  // doesn't get fed through html engine here
  open_javascript( "self.location.href = {$H_SQ}$url{$H_SQ};" );
  exit();
}

function schedule_reload() {
  global $H_SQ;
  js_on_exit( "submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

function reinit( $reinit = 'init' ) {
  need( isset( $GLOBALS['reinit'] ) );
  // debug( $action, 'reinit' );
  $_GET['action'] = $GLOBALS['action'] = '';
  $GLOBALS['reinit'] = $reinit;
}

function download_link( $item, $id, $attr ) {
  return html_alink( "/get.rphp?item=$item&id=$id", $attr );
}


/////////////////////////
//
// handlers and helper functions to handle parameters passed for frequently used mechanisms and gadgets:
//



// handle_action():
// - init global vars $action and $message from http
// - if $action is of the form 'action_message', message will be extracted
// - $action must be in list $actions, or 'nop' or ''
//
function handle_action( $actions ) {
  if( isstring( $actions ) )
    $actions = explode( ',', $actions );
  init_var( 'action', 'global,type=w,sources=http,default=nop' );
  global $action;
  if( ! $action )
    return true;
  if( preg_match( '/^(.+)_(\d+)$/', $action, & $matches ) ) {
    $action = $matches[ 1 ];
    $GLOBALS['message'] = $matches[ 2 ];
  } else {
    init_var( 'message', 'global,type=u,sources=http' );
  }
  foreach( $actions as $a ) {
    if( $a === $action )
      return true;
  }
  need( $action === 'nop', "illegal action submitted: $action" );
  $action = '';
  $GLOBALS['message'] = '0';
  return false;
}

// orderby_join():
//  - explode and return array of order keys from $orderby string
//  - if $ordernew is non-empty, it will become primary order key
//  - if $ordernew is already primary key, sort order will be reversed for this key
//  - if $ordernew is anywhere else in $orderby, this occurence will be deleted
//
function orderby_join( $orderby = '', $ordernew = '' ) {
  if( $orderby ) {
    $order_keys = explode( ',', $orderby );
    if( $ordernew ) {
      if( $order_keys[0] === $ordernew ) {
        $order_keys[0] = "$ordernew-R";
      } else if( $order_keys[0] === "$ordernew-R" ) {
        $order_keys[0] = "$ordernew";
      } else {
        $order_keys_new[] = $ordernew;
        foreach( $order_keys as $key ) {
          if( $key === $ordernew || $key === "$ordernew-R" )
            continue;
          $order_keys_new[] = $key;
        }
        $order_keys = $order_keys_new;
      }
    }
    return $order_keys;
  } else {
    return $ordernew ? array( $ordernew ) : array();
  }
}

////////////////////////////////////////////
// list handling: must be done in three steps:
//   - handle_list_options(): will (among other things) compute and return 'orderby_sql' expression
//   - (..perform SELECT query...)
//   - handle_list_limits(): actually set limit fields based on row count of sql result

// handle_list_options():
//   - initialize and normalize options for lists and returns normalized array of options
//   - handles persistent and http variables for toggling and sorting
// $options: array of options (all optional; missing entries will be created):
//   'select': string: variable name to take key of selected (and highlighted) list entry
//   'sortable': boolean: whether the list can be resorted
//   'orderby_sql': string to be appended to sql 'ORDER BY' clause (computed value - input is overwritten)
//   'limits': numeric: 0 display all elements;
//             otherwise: if list has more than this many entries, allow paging
//   'limit_from': start display at this entry
//   'limit_count': display that many entries (0: all)
//     * with 'limits' === false, 'limit_from' and 'limit_count' are set hard
//     * when paging is on, they provide initial defaults for the view
//   'cols': column options: array( 'tag' => array( 'opt' => 'value', ... ), [, ... ] ) where column options can be
//     't' / 'toggle':
//       'on' (default): always on
//       'off': always off
//       '0': off by default, override by persistent
//       '1': on by default, override by persistent
//     's' / 'sort': expression to be used in sql ORDER BY clause to sort by this column (1: use column tag as key)
//
//  special values for $options:
//    $options === true: choose defaults for all options (mostly on)
//    $options === false: switch most options off
//
function handle_list_options( $options, $list_id = '', $columns = array() ) {
  static $unique_ids = array();
  $a = array(
    'select' => ''
  , 'limits' => false
  , 'limit_from' => 0
  , 'limit_count' => 0  // means 'all'
  , 'sort_prefix' => false
  , 'limits_prefix' => false
  , 'orderby_sql' => true  // implies default sorting
  , 'toggle_prefix' => false
  , 'relation_table' => false  // reserved - currently unused
  , 'cols' => array()
  );
  if( $options === false ) {
    return $a;
  } else {
    $toggle_prefix = '';
    $toggle_command = '';
    $sort_prefix = '';
    if( ! isset( $unique_ids[ $list_id ] ) ) {
      $num = $unique_ids[ $list_id ] = 0;
    } else {
      $num = ++$unique_ids[ $list_id ];
    }
    // allowing to select list entries:
    $a['select'] = adefault( $options, 'select', '' );
    //
    // paging: just set defaults here - to be updated by handle_list_limits() once $count of list entries is known:
    //
    $a['limits'] = adefault( $options, 'limits', 10 );
    $a['limit_from'] = adefault( $options, 'limit_from', 0 );
    $a['limit_count'] = adefault( $options, 'limit_count', 20 );
    $a['limits_prefix'] = adefault( $options, 'limits_prefix', 'list_N'.$list_id.$num.'_' );
    //
    // per-column settings:
    //
    $a['columns_toggled_off'] = 0;
    $a['col_default'] = adefault( $options, 'col_default', 'toggle,sort' );
    foreach( $columns as $tag => $col ) {
      if( is_numeric( $tag ) ) {
        $tag = $col;
        $col = $a['col_default'];
      }
      if( is_string( $col ) )
        $col = parameters_explode( $col );
      foreach( $col as $opt => $val ) {
        if( is_numeric( $opt ) ) {
          $opt = $val;
          $val = 1;
        }
        switch( $opt ) {
          case 'toggle':
          case 't':
            if( ! $toggle_prefix )
              $toggle_prefix = $a['toggle_prefix'] = adefault( $options, 'toggle_prefix', 'list_N'.$list_id.$num.'_' );
            if( ! $toggle_command )
              $toggle_command = init_var( $toggle_prefix.'toggle', 'type=w,sources=http,default=' );
            switch( $val ) {
              case '0':
              case '1':
                $r = init_var( $toggle_prefix.'toggle_'.$tag, "global,type=b,sources=persistent,default=$val,set_scopes=view" );
                $val = $r['value'];
                if( $toggle_command['value'] === $tag )
                  $val ^= 1;
                if( ! $val )
                  $a['columns_toggled_off']++;
                // $GLOBALS[ $toggle_prefix.'toggle_'.$tag ] = $val;
                break;
              case 'off':
                $a['columns_toggled_off']++;
                break;
              default:
              case 'on':
                $val = 'on';
                break;
            }
            $r['value'] = $val;
            $a['cols'][ $tag ]['toggle'] = & $r['value'];
            unset( $r );
            break;
          case 'sort':
          case 's':
            if( ! $sort_prefix )
              $sort_prefix = $a['sort_prefix'] = adefault( $options, 'sort_prefix', 'list_N'.$list_id.$num.'_' );
            if( $val == 1 )
              $val = $tag;
            $a['cols'][ $tag ]['sort'] = $val;
            break;
          default:
            error( "undefined column option: $opt" );
        }
      } // loop: column-opts
    } // loop: columns
    //
    // sorting:
    //
    if( $sort_prefix ) {
      $orderby = init_var( $sort_prefix.'orderby', array(
        'type' => 'l'
      , 'sources' => 'persistent'
      , 'default' => adefault( $options, 'orderby', '' )
      , 'set_scopes' => 'view'
      ) );

      $ordernew = init_var( $sort_prefix.'ordernew', 'type=l,sources=http,default=' );
      $order_keys = orderby_join( $orderby['value'], $ordernew['value'] );
      $orderby['value'] = ( $order_keys ? implode( ',', $order_keys ) : '' );

      // construct SQL clause:
      $sql = '';
      $comma = '';
      foreach( $order_keys as $n => $tag ) {
        if( ( $reverse = preg_match( '/-R$/', $tag ) ) )
          $tag = preg_replace( '/-R$/', '', $tag );
        need( isset( $a['cols'][ $tag ]['sort'] ), "unknown order keyword: $tag" );
        $expression = $a['cols'][ $tag ]['sort'];
        $a['cols'][ $tag ]['sort_level'] = ( $reverse ? (-$n-1) : ($n+1) );
        if( $reverse ) {
          if( preg_match( '/ DESC$/', $expression ) )
            $expression = preg_replace( '/ DESC$/', '', $expression );
          else
            $expression = "$expression DESC";
        }
        $sql .= "$comma $expression";
        $comma = ',';
      }
      $a['orderby_sql'] = $sql;
    }
    //
    // relations:
    //
    // $a['relation_table'] = adefault( $options, 'relation_table', false );
    //
    return $a;
  }
}

// handle_list_limits():
// return array, based on $opts and actual list entry $count:
//  'limits': whether paging is on
//  'limit_from', 'limit_count': the actual values to be used
//
function handle_list_limits( $opts, $count ) {
  $limit_from = adefault( $opts, 'limit_from', 0 );
  $limit_count = adefault( $opts, 'limit_count', 0 );
  if( $opts['limits'] === false ) {
    $limits = false;
  } else {
    $r = init_var( $opts['limits_prefix'].'limit_from', "type=u,sources=http persistent,default=$limit_from,set_scopes=view" );
    $limit_from = & $r['value'];
    unset( $r );
    $r = init_var( $opts['limits_prefix'].'limit_count', "type=u,sources=http persistent,default=$limit_count,set_scopes=view" );
    $limit_count = & $r['value'];
    unset( $r );
    $limit_count_tmp = $limit_count;
    if( $opts['limits'] > $count ) {
      $limits = false;
      $limit_from = 0;
      $limit_count_tmp = $count;
    } else {
      $limits = true;
      $limit_count_tmp = ( $limit_count ? min( $count, $limit_count ) : $count );
      if( $count <= $limit_from )
        $limit_from = $count - 1;
    }
  }
  if( ! $limit_count_tmp )
    $limit_count_tmp = $count;
  if( $limit_from + $limit_count_tmp > $count )
    $limit_from = $count - $limit_count_tmp;
  if( $limit_from < 0 )
    $limit_from = 0;
  $limit_to = min( $count, $limit_from + $limit_count_tmp ) - 1;
  $l = array(
    'limits' => $limits
  , 'limit_from' => $limit_from
  , 'limit_to' => $limit_to
  , 'limit_count' => $limit_count
  , 'prefix' => $opts['limits_prefix']
  , 'count' => $count
  );
  // debug( $l, 'l' );
  return $l;
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
  'dontcache' => array( 'type' => 'x' )
, 'debug' => array( 'type' => 'b' )
, 'me' => array( 'type' => 'l', 'pattern' => '/^[a-zA-Z0-9_,]*$/' )
, 'options' => array( 'type' => 'u' )
, 'logbook_id' => array( 'type' => 'u' )
, 'f_thread' => array( 'type' => 'u' )
, 'f_window' => array( 'type' => 'x' )
, 'f_script' => array( 'type' => 'w' )
, 'f_sessions_id' => array( 'type' => 'u' )
, 'list_N_ordernew' => array( 'type' => 'l' )
, 'list_N_limit_from' => array( 'type' => 'u' )
, 'list_N_limit_count' => array( 'type' => 'u', 'default' => 20 )
, 'list_N_toggle' => array( 'type' => 'w' )
, 'offs' => array( 'type' => 'l', 'pattern' => '/^\d+x\d+$/', 'default' => '0x0' )
);

// cgi variables which may only be POSTed:
// will be merged with subprojects' $cgi_vars
//
$jlf_cgi_vars = array(
  'action' => array( 'type' => 'w', 'default' => 'nop' )
, 'message' => array( 'type' => 'u' )
);

// itan handling:
//
global $itan;
$itan = false;

function get_itan( $force_new = false ) {
  global $itan, $login_sessions_id;

  if( $force_new or ! $itan ) {
    $tan = random_hex_string( 5 );
    $id = sql_insert( 'transactions', array(
      'used' => 0
    , 'sessions_id' => $login_sessions_id
    , 'itan' => $tan
    ) );
    $itan = $id.'_'.$tan;
  }
  return $itan;
}

global $http_input_sanitized;
$http_input_sanitized = false;

function sanitize_http_input() {
  global $cgi_get_vars, $cgi_vars, $http_input_sanitized, $login_sessions_id, $debug_messages;

  if( $http_input_sanitized )
    return;
  need( ! get_magic_quotes_gpc(), 'whoa! magic quotes is on!' );
  foreach( $_GET as $key => $val ) {
    if( isnumeric( $val ) )
      $_GET[ $key ] = $val = "$val";
    need( isstring( $val ), 'GET: non-string value detected' );
    need( check_utf8( $key ), 'GET variable name: invalid utf-8' );
    need( preg_match( '/^[a-zA-Z][a-zA-Z0-9_]*$/', 'W' ), 'GET variable name: not an identifier' );
    need( check_utf8( $val ), 'GET variable value: invalid utf-8' );
    $key = preg_replace( '/_N[a-z]+\d+_/', '_N_', $key );
    need( isset( $cgi_get_vars[ $key ] ), "GET: unexpected variable $key" );
    need( checkvalue( $val, $cgi_vars[ $key ] ) !== NULL , "GET: unexpected value for variable $key" );
  }
  if( ( $_SERVER['REQUEST_METHOD'] == 'POST' ) && $_POST /* allow to discard $_POST when creating new session */ ) {
    // all forms must post a valid and unused iTAN:
    need( isset( $_POST['itan'] ), 'incorrect form posted(1)' );
    $itan = $_POST['itan'];
    need( preg_match( '/^\d+_[0-9a-f]+$/', $itan ), "incorrect form posted(2): $itan" );
    sscanf( $itan, "%u_%s", & $t_id, & $itan );
    need( $t_id, 'incorrect form posted(3)' );
    $row = sql_do_single_row( sql_query( 'SELECT', 'transactions', array( 'transactions_id' => $t_id ) ), NULL );
    need( $row, 'incorrect form posted(4)' );
    if( $row['used'] ) {
      // form was submitted more than once: discard all POST-data:
      $_POST = array();
      $debug_messages[] = html_tag( 'div', 'class=warn', 'warning: form submitted more than once - data will be discarded' );
    } else {
      need( $row['itan'] == $itan, 'invalid iTAN posted' );
      print_on_exit( H_LT."!-- login_sessions_id: $login_sessions_id, from db: {$row['sessions_id']} --".H_GT );
      need( $row['sessions_id'] == $login_sessions_id, 'invalid sessions_id' );
      // ok, id was unused; flag it as used:
      sql_update( 'transactions', $t_id, array( 'used' => 1 ) );
    }
    if( ( $s = adefault( $_POST, 's', '' ) ) ) {
      need( preg_match( '/^[a-zA-Z0-9_,=]*$/', $s ), "malformed parameter s posted: [$s]" );
      $s = parameters_explode( $s );
      foreach( $s as $key => $val ) {
        $_POST[ $key ] = hex_decode( $val );
      }
    }
    // create nil reports for unchecked checkboxen:
    if( isarray( $nilrep = adefault( $_POST, 'nilrep', '' ) ) ) {
      foreach( $nilrep as $name ) {
        need( preg_match( '/^[a-zA-Z][a-zA-Z0-9_]*$/', $name ), 'non-identifier in nilrep list' );
        if( ! isset( $_POST[ $name ] ) )
          $_POST[ $name ] = '0';
      }
      unset( $_POST['nilrep'] );
    }
    foreach( $_POST as $key => $val ) {
      if( isnumeric( $val ) )
        $_POST[ $key ] = $val = "$val";
      need( isstring( $val ), 'POST: non-string value detected' );
      need( check_utf8( $key ), 'POST variable name: invalid utf-8' );
      need( preg_match( '/^[a-zA-Z][a-zA-Z0-9_]*$/', $key ), 'POST variable name: not an identifier' );
      need( check_utf8( $val ), 'POST variable value: invalid utf-8' );
    }
    $_GET = tree_merge( $_GET, $_POST );
  } else {
    $_POST = array();
  }
  $http_input_sanitized = true;
}


$jlf_persistent_var_scopes = array( 'self', 'view', 'window', 'script', 'thread', 'session', 'permanent' );

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


// init_var( $name, $opts ): retrieve value for $name. $opts is associative array; most fields are optional:
//   'sources': array or space-separated list of sources to try in order. possible sources are:
//       keep: retrieve $opts['old'] if it exists
//       persistent: try to retrieve persistent var $name
//       <persistent_var_scope> ( e.g. 'view', 'self', ...): like 'persistent' but only try specified scope
//       http: try $_GET[ $name ] ($_POST has been merged into $_GET and overrides $_GET if both are set)
//       default: use default depending on type. this source will always be used as last resort if a default
//       value !== NULL exists, unless option 'nodefault' is specified
//   'type': used to complete type information 'pattern', 'normalize', 'default', see jlf_get_complete_type()
//   'pattern', 'normalize': used to normalize and type-check value via checkvalue()
//   'default': default value; if specified, implies to try 'default' as last source
//   'nodefault': flag: don't use default value even if we have one
//   'old': old value (to retrieve if 'keep' is specified as source, and to check for modification)
//   'failsafe': boolean option:
//      1 (default): if a source yields some value but checkvalue fails, reject and try next source
//        - if init_var() returns with failsafe = 1, the variable is guaranteed to have a legal value
//        - use this if you need a legal value, and have a legal last-resort default 
//      0: stop after first source yields value !== NULL, even in case of type mismach
//          in particular: if there is any default, init_var() will return it as last resort, even if it is not legal
//        - use to process user input which may be flagged as incorrect and returned to user if needed
//        - also useful to initialize data in the first place, even if defaults are not legal values
//   'global': ref-bind $name to value in global scope; if option maps to an identifier, use this instead of $name
//   'set_scopes': array or space-separated list of persistent variable scopes to store value in
//   'flag_problems', 'flag_modified': boolean flags, defaulting to 1, to toggle setting of class
//      (output fields 'problem' and 'modified' will always be set)
//   'type': type abbreviation; can be used to set 'pattern', 'default', 'normalize'
//
//  return value: associative array: contains all $opts, plus additionally the following fields:
//    'name': argument $name
//    'raw': raw value (unchecked - as received e.g. via http, but guaranteed to be valid utf-8)
//    'value': type-checked value, or NULL if type mismatch (only possible with failsafe off)
//    'source': keyword of source from which value was retrieved
//    'problem': non-empty if value does not match type
//    'modified': non-empty iff $opts['old'] is set and value !== $opts['old']
//    'class': suggested CSS class: either 'problem', 'modified' or '', depending on the two fields above
//      and on the 'flag_problems', 'flag_modified' options
//
function init_var( $name, $opts = array() ) {
  global $jlf_persistent_vars, $jlf_persistent_var_scopes, $cgi_vars;

  $opts = parameters_explode( $opts );
  if( ( $debug = adefault( $opts, 'debug', 0 ) ) )
    debug( $opts, 'init_var: '.$name );

  $type = jlf_get_complete_type( $name, $opts );
  if( adefault( $opts, 'nodefault' ) ) {
    $default = NULL;
  } else {
    $default = $type['default']; // guaranteed to be set, but may also be NULL
  }

  if( $debug )
    $type['debug'] = 1;

  $sources = adefault( $opts, 'sources', 'http persistent default' );
  if( ! is_array( $sources ) )
    $sources = explode( ' ', $sources );

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
        sanitize_http_input();
        if( $type['type'][ 0 ] == 'R' ) {
          if( isset( $_FILES[ $name ] ) && $_FILES[ $name ]['tmp_name'] && ( $_FILES[ $name ]['size'] > 0 ) ) {
            $v = base64_encode( file_get_contents( $_FILES[ $name ]['tmp_name'] ) );
            // $mime_type = $_FILES[ $name ]['type']; // this is pretty useless and can't be trusted anuway!
            $file_size = $_FILES[ $name ]['size'];
            break 1;
          } else {
            continue 2;
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
      case 'keep':
        if( isset( $opts['old'] ) ) {
          $v = $opts['old'];
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
        error( 'undefined source' );
    }
    $v = (string) $v;
    // checkvalue: normalize value, then check for legal values:
    $type_ok = ( ( $vc = checkvalue( $v, $type ) ) !== NULL );
    if( $name == 'pdf' ) {
      // debug( $source, 'source accepted' );
      // debug( $type_ok, 'type_ok' );
    }
    if( $file_size > 0 ) {
      if( ! ( $file_size <= $type['maxlen'] ) ) {
        $v = '';
        $vc = NULL;
        $type_ok = false;
      }
    }
    if( $name == 'pdf' ) {
      // debug( $file_size, 'file_size' );
      // debug( $type['maxlen'], 'maxlen' );
      // debug( $type_ok, 'type_ok' );
    }

    if( $debug )
      debug( $v, 'type_ok: '. ( $type_ok ? 'YES' : 'NOPE' ) );
    if( $type_ok || ! $failsafe )
      break;
    $v = NULL;
  }

  if( $v === NULL ) {
    error( "init_var: failed to initialize: $name" );
  }

  $r = $opts;
  $r['name'] = $name;
  if( $vc !== NULL ) {
    $r['raw'] = & $vc;
  } else {
    $r['raw'] = $v;
  }
  $r['source'] = $source;
  $r['value'] = & $vc;
  $r['class'] = '';
  $r['modified'] = '';
  if( ( $vc !== NULL ) && isset( $opts['old'] ) ) {
    if( $opts['old'] !== $vc ) {
      $r['modified'] = 'modified';
      if( $flag_modified ) {
        $r['class'] = 'modified';
        // debug( $v, "init_var: modified: $name" );
        // debug( $opts['old'], "init_var: old: $name" );
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
    if( adefault( $problems, $name, NULL ) === $r['raw'] ) {
      $r['class'] = 'problem';
    }
  }

  if( ( $global = adefault( $opts, 'global', false ) ) !== false ) {
    if( $global === true || isnumeric( "$global" ) )
      $global = $name;
    $GLOBALS[ $global ] = & $vc;
  }

  if( ( $set_scopes = adefault( $opts, 'set_scopes', false ) ) ) {
    if( isstring( $set_scopes ) )
      $set_scopes = explode( ' ', $set_scopes );
    foreach( $set_scopes as $scope ) {
      $jlf_persistent_vars[ $scope ][ $name ] = & $vc;
    }
  }
  return $r;
}



// init_fields():
// initialize variables for form fields and filters from various sources.
// $fields: list of names, or array 'name' => <per-field-options> of variables to initialize
// $opts:
//  'merge': array of alread initialized variables (from previous call typically) to merge into result
//  'global' => true|<prefix>: ref-bind variables in global scope, with optional prefix
//  'failsafe', 'sources': list of sources as in init_var()
//  'sources' as in init_var(); defaults to 'http persistent keep default'
//  'reset': flag: default sources are 'keep default' (where 'keep' usually means: use value from database!)
//  'tables', 'rows': to determine type and previous values
// per-field options: most of the above and
//  'basename': name to look for in db tables, for global pattern and default information
//  'type', 'pattern', 'default'... as usual
//  'old': previous value; will be derived from 'rows' (from db) or 'default'
//  'relation': use "$basename $relation" in '_filters' map (see below)
// 
// rv: array of 'name' => data mappings, where data is the output of init_var
// rv will have additional fields
//  '_problems': maps fieldnames to offending raw values
//  '_changes': maps fieldnames to new raw(!) values
//  '_filters': maps fieldnames to non-0 values
//
function init_fields( $fields, $opts = array() ) {
  $fields = parameters_explode( $fields, array( 'default_value' => array() ) );
  $opts = parameters_explode( $opts );

  $rv = adefault( $opts, 'merge', array() );
  $rv['_problems'] = adefault( $rv, '_problems', array() );
  $rv['_changes'] = adefault( $rv, '_changes', array() );
  $rv['_filters'] = adefault( $rv, '_filters', array() );

  $rows = adefault( $opts, 'rows', array() );
  // if( ( $rows = adefault( $opts, 'rows', array() ) ) ) {
  //   $rows = parameters_explode( $rows, array( 'default_value' => array() ) );
  // }
  if( ( $tables = adefault( $opts, 'tables', array() ) ) ) {
    $tables = parameters_explode( $tables, array( 'default_value' => 1 ) );
  }
  $bind_global = adefault( $opts, 'global', false );
  $failsafe = adefault( $opts, 'failsafe', 1 );
  $reset = adefault( $opts, 'reset', 0 );
  $readonly = adefault( $opts, 'readonly', 0 );
  $flag_problems = adefault( $opts, 'flag_problems', 0 );
  $flag_modified = adefault( $opts, 'flag_modified', 0 );
  $set_scopes = adefault( $opts, 'set_scopes', 'self' );
  $prefix = adefault( $opts, 'prefix', '' );

  if( isset( $opts['sources'] ) ) {
    $sources = $opts['sources'];
  } else {
    if( adefault( $opts, 'reset' ) || adefault( $opts, 'readonly' ) ) {
      $sources = 'keep default';
    } else {
      $sources = 'http persistent keep default';
    }
  }

  foreach( $fields as $fieldname => $specs ) {

    $specs = parameters_explode( $specs, 'type' );
    $basename = adefault( $specs, 'basename', $fieldname );

    // determine type info for field:
    //
    $specs['rows'] = $rows;
    if( isset( $specs['table'] ) )
      $specs['tables'] = array( $specs['table'] => 1 );
    else
      $specs['tables'] = $tables;
    $specs = array_merge( $specs, jlf_get_complete_type( $basename, $specs ) );
    unset( $specs['rows'] );
    unset( $specs['tables'] );

    // determine 'old' value for field:
    //
    if( ! isset( $specs['old'] ) ) {
      $t = adefault( $specs, 'table', false );
      foreach( $rows as $table => $row ) {
        if( $t && ( $t !== $table ) )
          continue;
        if( isset( $row[ $basename ] ) ) {
          $specs['old'] = $row[ $basename ];
          break;
        }
        $n = strlen( $table );
        if( substr( $basename, 0, $n + 1 ) === "{$table}_" ) {
          foreach( $row as $col => $val ) {
            if( substr( $basename, $n + 1 ) === $col ) {
              $specs['old'] = $val;
              break 2;
            }
          }
        }
      }
    }
    if( ! isset( $specs['old'] ) ) {
      $specs['old'] = $specs['default'];
    }

    if( ! isset( $specs['sources'] ) ) {
      if( adefault( $specs, 'reset', $reset ) || adefault( $specs, 'readonly', $readonly ) ) {
        $specs['sources'] = 'keep default';
      } else {
        $specs['sources'] = $sources;
      }
    }

    if( ( $p = adefault( $specs, 'global', $bind_global ) ) ) {
      $global_prefix = ( ( isstring( $p ) && ! isnumeric( $p) ) ? $p : $prefix );
      $specs['global'] = $global_prefix.$fieldname;
    }
    $specs['set_scopes'] = adefault( $specs, 'set_scopes', $set_scopes );
    $specs['failsafe'] = adefault( $specs, 'failsafe', $failsafe );
    $specs['flag_problems'] = $flag_problems;
    $specs['flag_modified'] = $flag_modified;

    $var = init_var( $prefix.$fieldname, $specs );

    if( adefault( $var, 'problem' ) ) {
      $rv['_problems'][ $fieldname ] = $var['raw'];
    }
    if( adefault( $var, 'modified' ) ) {
      $rv['_changes'][ $fieldname ] = $var['raw'];
    }
    $rv[ $fieldname ] = $var;
    if( $var['value'] ) {
      if( ( $relation = adefault( $specs, 'relation' ) ) ) {
        $rv['_filters'][ "$basename $relation" ] = & $var['value'];
      } else {
        $rv['_filters'][ $basename ] = & $var['value'];
      }
    }
    unset( $var );
  }
  // debug( $rv, 'init_fields: out:' );

  return $rv;
}


?>
