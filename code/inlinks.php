<?php

// inlinks.php (Timo Felbinger, 2008 ... 2011)
//
// functions to create internal hyperlinks


// pseudo-parameters: when generating links and forms with the functions below,
// these parameters will never be transmitted via GET or POST; rather, they determine
// how the link itself will look and behave:
//
$pseudo_parameters = array(
  'img', 'attr', 'title', 'text', 'class', 'confirm', 'anchor', 'url', 'context', 'enctype', 'thread', 'window', 'script', 'inactive', 'form_id', 'id'
);

///////////////////////
//
// internal functions (not supposed to be called by consumers):
//

// jlf_url(): create an internal URL, passing $parameters in the query string.
// - parameters with value NULL will be skipped
// - pseudo-parameters (see open) will always be skipped except for two special cases:
//   - anchor: append an #anchor to the url
//   - url: return the value of this parameter immediately (overriding all others)
//
function jlf_url( $parameters ) {
  global $pseudo_parameters, $debug;

  $url = 'index.php?dontcache='.random_hex_string(6);  // the only way to surely prevent caching...
  $anchor = '';
  foreach( parameters_explode( $parameters ) as $key => $value ) {
    // only allow whitelisted characters in url; this makes sure that
    //  - the only problematic character in url will be '&' vs. '&amp;', and
    //  - '&amp;' can be unambiguously replaced by '&', if necessary:
    // (note that we need '&amp;' escaping everywhere excecpt inside <script>../</script>, but
    //  we do not know yet where this url will be used)
    switch( $key ) {
      case 'anchor':
        need( preg_match( '/^[a-zA-Z0-9_,.-]*$/', $value ), 'illegal parameter value in url' );
        $anchor = "#$value";
        continue 2;
      case 'url':
        need( preg_match( '/^[a-zA-Z0-9_,.-]*$/', $value ), 'illegal parameter value in url' );
        return $value;
      default:
        if( in_array( $key, $pseudo_parameters ) )
          continue 2;
    }
    if( $value !== NULL ) {
      need( preg_match( '/^[a-zA-Z0-9_,.-]*$/', $key ), 'illegal parameter name in url' );
      need( preg_match( '/^[a-zA-Z0-9_,.-]*$/', $value ), 'illegal parameter value in url' );
      $url .= "&amp;$key=$value";
    }
  }
  if( $debug ) {
    $url .= '&amp;debug=1';
  }
  $url .= $anchor;
  return $url;
}


// alink: compose from parts and return an <a href=...> hyperlink
// $url may also contain javascript; if so, '-quotes but no "-quotes must be used in the js code
//
function alink( $url, $attr ) {
  global $activate_safari_kludges, $activate_konqueror_kludges;

  $attr = parameters_explode( $attr, 'class' );
  if( isset( $attr['title'] ) && ! isset( $attr['alt'] ) ) {
    $attr['alt'] = $attr['title'];
  }
  $l = '<a';
  $ia = '';
  $img = $text = '';
  foreach( $attr as $a => $val ) {
    switch( $a ) {
      case 'text':
        $text = $val;
        break;
      case 'img':
        $img = $val;
        break;
      case 'title':
      case 'alt':
        $ia .= " $a=\"$val\"";
        break;
      default:
        $l .= " $a=\"$val\"";
        break;
    }
  }
  if( ! $img ) {
    $l .= $ia;
  }
  $l .= " href=\"$url\">";

  if( $img ) {
    $l .= "<img src='$img' class='icon' $ia />";
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
//     - default: 'self'
//     - special value '!submit': will return link to submit form $parameters['form_id'], or the update_form by default.
//       most parameters will have no effect, except: parameters 'action', 'message' and one 'extra_field',
//       with value 'extra_value', may be POSTed.
//   $parameters:
//     GET parameters to be passed in url: either "k1=v1,k2=v2" string, or array of 'name' => 'value' pairs
//     'name' => NULL can be used to explicitely _not_ pass parameter 'name' even if it is in defaults
//   $options:
//     window options to be passed in javascript:window_open() (will override defaults)
//
// $parameters may also contain some pseudo-parameters:
//   text, title, class, img: to specify the look of the link (see alink above)
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
//       - 'onsubmit' will be used open a different target window if that is requested.
//
function inlink( $script = '', $parameters = array(), $options = array() ) {
  $parameters = parameters_explode( $parameters );
  $options = parameters_explode( $options );

  $inactive = adefault( $parameters, 'inactive', 0 );
  $js = '';
  $url = '';

  $parent_window = $GLOBALS['window'];
  $parent_thread = $GLOBALS['thread'];
  if( $script === '!submit' ) {
    $form_id = adefault( $parameters, 'form_id', 'update_form' );
    $action = adefault( $parameters, 'action', 'update' );
    $message = adefault( $parameters, 'message', '0' );
    $extra_field = adefault( $parameters, 'extra_field', '' );
    $extra_value = adefault( $parameters, 'extra_value', '0' );
    $json = adefault( $parameters, 'json', '' );
    $js = $inactive ? 'true;' : "submit_form( '$form_id', '$action', '$message', '$extra_field', '$extra_value', '$json' ); ";
  } else {
    $script or $script = 'self';
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
    , $target_thread, $target_window, $target_script
    , $parent_thread, $parent_window, $parent_script
    );

    $url = jlf_url( $parameters );
    $options = array_merge( $script_defaults['options'], $options );
    $js_window_name = js_window_name( $target_window, $target_thread );
    $option_string = parameters_implode( $options );

    if( ( $target_window != $parent_window ) || ( $target_thread != $parent_thread ) ) {
      $js = "load_url( '$url', '$js_window_name', '$option_string' );";
    } else {
      $js = "if( warn_if_unsaved_changes() ) load_url( '$url' );";
    }
  }

  if( ( $confirm = adefault( $parameters, 'confirm', '' ) ) ) {
    $confirm = "if( confirm( '$confirm' ) ) ";
  }

  switch( ( $context = adefault( $parameters, 'context', 'a' ) ) ) {
    case 'a':
      $attr = array( 'class' => 'href' . ( $inactive ? ' inactive' : '' ) );
      foreach( $parameters as $a => $val ) {
        switch( $a ) {
          case 'title':
          case 'text':
          case 'img':
          case 'class':
          case 'id':
            $attr[ $a ] = $val;
            break;
        }
      }
      return alink( $inactive ? '#' : "javascript: $confirm $js", $attr );
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
        $r['onsubmit'] = "window.open( '', '$js_window_name', '$option_string' ).focus(); document.forms.update_form.submit(); ";
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
  open_javascript( preg_replace( '/&amp;/', '&', inlink( $script, $parameters, $options ) ) );
}

// load_immediately(): exit the current script and open $url instead:
//
function load_immediately( $url ) {
  $url = preg_replace( '/&amp;/', '&', $url );  // doesn't get fed through html engine here
  open_javascript( "self.location.href = '$url';" );
  exit();
}

function schedule_reload() {
  js_on_exit( "submit_form( 'update_form' ); " );
}


/////////////////////////
//
// handlers and helper functions to handle parameters passed for frequently used mechanisms and gadgets:
//


// handle_filters():
//   - initialize global vars for filters
//   - return array( 'KEY REL' => & GLOBAL_VAR, ... ) referencing non-null filter variables
//
function handle_filters( $keys = array() ) {
  $filters = array();
  if( is_string( $keys ) )
    $keys = explode( ',', $keys );
  foreach( $keys as $k => $default ) {
    if( is_numeric( $k ) ) {
      $k = $default;
      $default = 0;
    }
    $r = split_atom( $k, '=' );
    $name = $r[ 1 ];
    init_global_var( $name, '', 'http,persistent,default', $default, 'self' );
    $v = $GLOBALS[ $name ];
    if( $v and ( "$v" != '0' ) ) {  // only use non-null filters
      $filters[ $k ] = & $GLOBALS[ $name ];
      // fixme: what if $$k gets set to 0 later?
    }
  }
  return $filters;
}

// handle_action():
// - init global vars $action and $message from http
// - if $action is of the form 'action_message', message will be extracted
// - $action must be in list $actions, or 'nop' or ''
//
function handle_action( $actions ) {
  global $action, $message;
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
// list handling: must be done in two steps:
//   - handle_list_options(): will (amonng other things) compute and return 'orderby_sql' expression
//   (sql SELECT query)
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
            switch( $val ) {
              case '0':
              case '1':
                $val = init_global_var( $toggle_prefix.'toggle_'.$tag, 'u', 'persistent', $val, 'view' );
                if( get_http_var( $toggle_prefix.'toggle', 'w' ) == $tag )
                  $val ^= 1;
                if( ! $val )
                  $a['columns_toggled_off']++;
                $GLOBALS[ $toggle_prefix.'toggle_'.$tag ] = $val;
                break;
              case 'off':
                $a['columns_toggled_off']++;
                break;
              default:
              case 'on':
                $val = 'on';
                break;
            }
            $a['cols'][ $tag ]['toggle'] = $val;
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
      $orderby = init_global_var( $sort_prefix.'orderby', 'l', 'http,persistent', adefault( $options, 'orderby', '' ), 'view' );
      $ordernew = init_global_var( $sort_prefix.'ordernew', 'l', 'http', '' );

      $order_keys = orderby_join( $orderby, $ordernew );
      $GLOBALS[ $sort_prefix.'orderby' ] = ( $order_keys ? implode( ',', $order_keys ) : '' );
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
    // prettydump( $a, 'handle_list_options: returning: ' );
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
    $limit_from = init_global_var( $opts['limits_prefix'].'limit_from', 'u', 'http,persistent', $limit_from, 'view' );
    $limit_count = init_global_var( $opts['limits_prefix'].'limit_count', 'u', 'http,persistent', $limit_count, 'view' );
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
  return array(
    'limits' => $limits
  , 'limit_from' => $limit_from
  , 'limit_to' => $limit_to
  , 'limit_count' => $limit_count
  , 'prefix' => $opts['limits_prefix']
  , 'count' => $count
  );
}




////////////////////////////////////
//
// cgi-stuff: sanitizing parameters
//
////////////////////////////////////


// variables which can be passed by GET:
// will be merged with subprojects' $url_vars into global $url_vars
//
//
$jlf_url_vars = array(
  'dontcache' => array( 'type' => 'w' )
, 'debug' => array( 'type' => 'u', 'default' => 0 )
, 'me' => array( 'type' => '/^[a-zA-Z0-9_,]*$/' )
, 'options' => array( 'type' => 'u', 'default' => 0 )
, 'logbook_id' => array( 'type' => 'u', 'default' => 0 )
, 'f_thread' => array( 'type' => 'u', 'default' => 0 )
, 'f_window' => array( 'type' => 'w', 'default' => 0 )
, 'f_sessions_id' => array( 'type' => '0', 'default' => 0 )
, 'action' => array( 'type' => 'w', 'default' => 'nop' )
, 'message' => array( 'type' => 'u', 'default' => '0' )
, 'list_N_ordernew' => array( 'type' => 'l', 'default' => '' )
, 'list_N_limit_from' => array( 'type' => 'u', 'default' => 0 )
, 'list_N_limit_count' => array( 'type' => 'u', 'default' => 20 )
, 'list_N_toggle' => array( 'type' => 'w', 'default' => '' )
, 'offs' => array( 'type' => 'l', 'default' => '0,0' )
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
  global $url_vars, $http_input_sanitized, $login_sessions_id, $debug_messages;

  foreach( $_GET as $key => $val ) {
    $key = preg_replace( '/_N[a-z]+\d+_/', '_N_', $key );
    need( isset( $url_vars[ $key ] ), "unexpected variable $key passed in URL" );
    need( checkvalue( $val, $url_vars[ $key ]['type'] ) !== NULL , "unexpected value for variable $key passed in URL" );
  }
  if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    // all forms must post a valid and unused iTAN:
    need( isset( $_POST['itan'] ), 'incorrect form posted(1)' );
    sscanf( $_POST['itan'], "%u_%s", &$t_id, &$itan );
    need( $t_id, 'incorrect form posted(2)' );
    $row = sql_do_single_row( sql_query( 'SELECT', 'transactions', array( 'transactions_id' => $t_id ) ), NULL );
    need( $row, 'incorrect form posted(3)' );
    if( $row['used'] ) {
      // form was submitted more than once: discard all POST-data:
      $_POST = array();
      $debug_messages[] = "<div class='warn'>warning: form submitted more than once - data will be discarded</div>";
    } else {
      need( $row['itan'] == $itan, 'invalid iTAN posted' );
      print_on_exit( "<!-- login_sessions_id: $login_sessions_id, from db: {$row['sessions_id']} <br> -->" );
      need( $row['sessions_id'] == $login_sessions_id, 'invalid sessions_id' );
      // ok, id was unused; flag it as used:
      sql_update( 'transactions', $t_id, array( 'used' => 1 ) );
    }
    $f = adefault( $_POST, 'extra_field', '' );
    if( $f && is_string( $f ) ) {
      $_POST[ $f ] = adefault( $_POST, 'extra_value', '' );
    }
    unset( $_POST['extra_field'] );
    unset( $_POST['extra_value'] );
    // if( $_POST['action'] === 'reset' ) {
    //   $_POST = array( 'action' => 'update' ); // discard all form data and update view
    // }
  } else {
    $_POST = array();
  }
  $http_input_sanitized = true;
}

// checkvalue: type-check and optionally filter data passed via http: $type can be
//   b : boolean: 0 or 1
//   d : integer number
//   u : non-negative integer
//   U : integer greater than 0
//   h : any string, but filter through htmlspecialchars()
//   H : like h, but must contain non-whitespace character
//   R : raw: any input, no filtering
//   f : fixed-point decimal fraction number
//   w : word: alphanumeric and _; empty string allowed
//   W : non-empty word
//   l : list: like w, but may also contain ',', '-' and '='
//   /.../: regex pattern. value will also be trim()-ed
//
function checkvalue( $val, $type ) {
  $pattern = '';
  $format = '';
  switch( substr( $type, 0, 1 ) ) {
    case 'b':
      $val = trim($val);
      $pattern = '/^[01]$/';
      break;
    case 'H':
      $pattern = '/\S/';
    case 'h':
      if( get_magic_quotes_gpc() ) {
        error( 'magic quotes is on!' );
        $val = stripslashes( $val );
      }
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
  global $http_input_sanitized, $url_vars, $problems;

  if( ! $http_input_sanitized )
    sanitize_http_input();

  if( ! $type ) {
    need( isset( $url_vars[ $name ]['type'] ), "no default type for variable $name" );
    $type = $url_vars[ $name ]['type'];
  }

  if( isset( $_POST[ $name ] ) ) {
    $val = $_POST[ $name ];
  } elseif( isset( $_GET[ $name ] ) ) {
    $val = $_GET[ $name ];
  } else {
    return NULL;
  }
  $val = checkvalue( $val, $type );
  if( $val === NULL ) {
    $problems[ $name ] = 'type mismatch';
  }
  return $val;
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
  // print_on_exit( "<!-- get_persistent_var: no match: [$name] from [$scope] -->" );
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


// init_var():
//
function & init_var( $name, $opts = array() ) {
  global $jlf_persistent_vars, $jlf_persistent_var_scopes, $url_vars;

  $opts = parameters_explode( $opts );

  if( ! ( $type = adefault( $opts, 'type', false ) ) ) {
    if( isset( $url_vars[ $name ]['type'] ) )
      $type = $url_vars[ $name ]['type'];
    else
      error( "$name: cannot determine type" );
  }
  $from_sources = adefault( $opts, 'from_sources', 'http,persistent,default' );
  if( ! is_array( $from_sources ) )
    $from_sources = explode( ',', $from_sources );
  $default = adefault( $opts, 'default', NULL );

  $failsafe = adefault( $opts, 'failsafe', true );

  $v = NULL;
  foreach( $from_sources as $source ) {
    switch( $source ) {
      case 'http':
        if( ( $v = get_http_var( $name, 'r' ) ) !== NULL ) {
          break 1;
        } else {
          continue 2;
        }
      case 'persistent':
        // print_on_exit( "<!-- init_global_var: try persistent on $name... -->" );
        if( ( $v = get_persistent_var( $name ) ) !== NULL ) {
          break 1;
        } else {
          continue 2;
        }
      case 'global_default':
      case 'default':
        if( isset( $url_vars[ $name ]['default'] ) ) {
          $v = $url_vars[ $name ]['default'];
          $source = 'global_default';
          break 1;
        } else {
          continue 2;
        }
      case 'keep_global':
      case 'keep':
        if( isset( $GLOBALS[ $name ] ) ) {
          $v = $GLOBALS[ $name ];
          $source = 'keep_global';
          break 1;
        } else {
          continue 2;
        }
      default:
        if( in_array( $source, $jlf_persistent_var_scopes ) ) {
          if( ( $v = get_persistent_var( $name, $source ) ) !== NULL ) {
            break 1;
          } else {
            continue 2;
          }
        }
        error( 'undefined from_source' );
    }
    $vc = checkvalue( $v, $type );
    if( ( ! $failsafe ) || ( $vc !== NULL ) )
      break;
  }
  if( $v === NULL ) {
    if( is_array( $default ) ) {
      if( isset( $default[ $name ] ) )
        $v = $default[ $name ];
    } else {
      $v = $default;
    }
    $vc = checkvalue( $v, $type );
    $source = 'passed_default';
  }

  if( $v === NULL ) {
    error( "init_var: failed to initialize: $name" );
  }

  $r = array(
    'name' => $name
  , 'raw' => $v
  , 'source' => $source
  , 'raw_pr' => htmlspecialchars( $v )
  , 'value' => & $vc
  , 'problem' => ( ( $vc === NULL ) ? 'type mismatch' : '' )
  );

  if( ( $global = adefault( $opts, 'global', false ) ) )
    $GLOBALS[ $global ] = & $vc;

  if( ( $set_scope = adefault( $opts, 'set_scope', false ) ) ) {
    if( isstring( $set_scope ) )
      $set_scope = explode( ',', $set_scope );
    foreach( $set_scope as $scope ) {
      $jlf_persistent_vars[ $scope ][ $name ] = & $vc;
    }
  }
  return $r;
}

//
// for backward compatibility:
//
function & init_global_var(
  $name
, $type = ''
, $from_sources = 'http,persistent,default'
, $default = NULL
, $set_scope = false
) {
  $r =& init_var( $name, array(
    'global' => $name
  , 'type' => $type
  , 'from_sources' => $from_sources
  , 'default' => $default
  , 'set_scope' => $set_scope
  , 'failsafe' => false
  ) );
  $problems['name'] = $r['problem'];
  return $r['value'];
}

?>
