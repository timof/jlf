<?php
// html.php: functions to create html code
// naming convention:
// - function html_*: return string with html-code, don't print to stdout, don't keep track of open tags
// - function open_*, close_*: print to stdout and keep track of open tags
//
// css design is broken in that cascading goes just a bit to far:
// rules like  
//   table.greenborder tr td { border:1ex solid green; }
// have the unexpected and undesired side effect of putting green borders even on nested tables.
// thus, we use the following kludge:
// - all class names of <table> are propagated into every <td> and <th> as well;
// - the rule above can than be written as
//     td.greenborder { border:1ex solid green; }
//   and will still take effect after open_table( 'greenborder' );
// class name propagation works like this:
// - td and tr inherit from "their" table, td also from "its" tr
// - li inherits from its ul and ol
// - body and fieldset create "environments" which propagate their class to all inner tags, but not to sub-environments


$open_tags = array(              // stack to keep track of open tags and some properties
  1 => array( 'tag' => ''        // 1 is "bottom of stack" entry
       , 'id' => ''
       , 'pclasses' => array()
       , 'role' => ''
  )
);
$current_form = NULL;            // reference to $open_tags member of type <form> (if any, else NULL)
$current_table = NULL;           // reference to innermost $open_tags member of type <table> (if any, else NULL - css tables don't count)
$current_tr = NULL;              // reference to current <tr>
$current_list = NULL;            // reference to innermost $open_tags member of type <ul> or <ol> (if any, else NULL)
$print_on_exit_array = array();  // print this just before </body>
$js_on_exit_array = array();     // javascript code to insert just before </body>
$html_id = 0;                    // draw-a-number counter to generate unique ids
// $html_hints = array();        // online hints to display for fields */

// set flags to activate workarounds for known browser bugs:
//
global $activate_mozilla_kludges, $activate_safari_kludges, $activate_exploder_kludges, $activate_konqueror_kludges;
$activate_safari_kludges = 0;
$activate_mozilla_kludges = 0;
$activate_exploder_kludges = 0;
$activate_konqueror_kludges = 0;
if( ( $browser = adefault( $_SERVER, 'HTTP_USER_AGENT' ) ) ) {
  if( preg_match ( '/safari/i', $browser ) ) {  // safari sends "Mozilla...safari"!
    $activate_safari_kludges = 1;
  } else if( preg_match ( '/konqueror/i', $browser ) ) {  // dito: konqueror
    $activate_konqueror_kludges = 1;
  } else if( preg_match ( '/msie/i', $browser ) ) {
    $activate_exploder_kludges = 1;
  } else if( preg_match ( '/^mozilla/i', $browser ) ) {  // plain mozilla(?)
    $activate_mozilla_kludges = 1;
  }
}

// new_html_id(): increment and return next unique id:
//
function new_html_id() {
  global $html_id;
  return ++$html_id;
}


// html_tag: compose and return arbitrary html tag
// normally, string containing opening tag, payload and close tag are returned. except:
// - $attr === false: produce close-tag only
// - $payload === NULL: solitary tag without payload and no close tag
// - $payload === false: product open-tag only
// - $nodebug == true: do not indent the html-code
function html_tag( $tag, $attr = array(), $payload = false, $nodebug = false ) {
  $n = count( $GLOBALS['open_tags'] );
  $s = ( ( ! $nodebug && $GLOBALS['debug'] ) ? H_LT."!--\n".str_repeat( '  ', $n ).'--'.H_GT : '' );
  if( $attr === false ) { // produce close-tag
    $s .= H_LT.'/'.$tag.H_GT;
  } else {
    $attr = parameters_explode( $attr, 'class' );
    $s .= H_LT . $tag;
    foreach( $attr as $a => $val ) {
      if( $val !== NULL ) {
        if( is_array( $val ) ) { // mostly for 'class' handling
          $val = implode( ' ', $val );
        }
        $s .= ' '.$a.'='.H_DQ.$val.H_DQ;
      }
    }
    if( $payload === NULL )
      // not yet valid in doctype 'transitional'...  $s .= ' /'.H_GT;
      $s .= H_GT;
    else if( $payload !== false )
      $s .= H_GT . $payload . html_tag( $tag, false, false, $nodebug );
    else
      $s .= H_GT;
  }
  return $s;
}

function html_span( $attr, $payload = false ) {
  return html_tag( 'span', $attr, $payload );
}
function html_div( $attr, $payload = false ) {
  return html_tag( 'div', $attr, $payload );
}
function html_li( $attr, $payload = false ) {
  return html_tag( 'li', $attr, $payload );
}


// html_alink: compose from parts and return an <a href=...> hyperlink
// $url may also contain javascript; if so, '-quotes but no "-quotes must be used in the js code
//
function html_alink( $url, $attr ) {
  // global $activate_safari_kludges, $activate_konqueror_kludges;
  // global $H_LT, $H_GT, $H_SQ, $H_DQ;

  $attr = parameters_explode( $attr, 'class' );
  if( isset( $attr['title'] ) && ! isset( $attr['alt'] ) ) {
    $attr['alt'] = $attr['title'];
  }

  $payload = ( isset( $attr['text'] ) ? $attr['text'] : '' );
  unset( $attr['text'] );
  if( adefault( $attr, 'img' ) ) {
    $ia = array( 'src' => $attr['img'], 'class' => 'icon' );
    if( isset( $attr['alt'] ) ) {
      $ia['alt'] = $attr['alt'];
      unset( $attr['alt'] );
    } else if( isset( $attr['title'] ) ) {
      $ia['alt'] = $attr['title'];
    }
    if( $payload )
      $payload .= ' ';
    $payload .= html_tag( 'img', $ia, NULL );
  }
  if( ! $payload ) {
    if( $GLOBALS['activate_safari_kludges'] )
      $payload = H_AMP.'#8203;'; // safari can't handle completely empty links...
    if( $GLOBALS['activate_konqueror_kludges'] )
      $payload = H_AMP.'nbsp;'; // ...dito konqueror (and it can't even handle unicode)
    if( $GLOBALS['activate_exploder_kludges'] ) {
      // if( $attr['class'] ) {
      //   $payload = $attr['class'];
      //   $attr['class'] = 'href';
      // } else {
      $payload = '_'; // H_AMP.'nbsp;';
      // }
    }
  }
  $attr['href'] = $url;
  $l = html_tag( 'a', $attr, $payload );
  if( $GLOBALS['activate_exploder_kludges'] && ! $payload ) {
    $l = H_AMP.'nbsp;' . $l . H_AMP.'nbsp;';
  }
  $l = html_tag( 'span', array( 'onclick' => 'nobubble(event);', 'onmousedown' => 'nobubble(event);' ), $l );
  return $l;
}


// merge_classes(): modify and return classes based on specs:
// $classes: array or space-separated string of class names; the return value will always be an n-array
// $specs: array or space-separated string of specifications. supported rules:
//   /pattern/: filter rule: drop all classes not matching pattern
//   /pattern//: drop rule: drop all classes matching pattern
//   /pattern/subst/: regex_replace to be applied to all $classes
//   <word>: class name to append to $classes unless already present
//
function merge_classes( $classes, $specs ) {
  if( is_string( $classes ) ) {
    $classes = explode( ' ', $classes );
  }
  if( is_string( $specs ) ) {
    $specs = explode( ' ', $specs );
  }
  foreach( $specs as $s ) {
    if( ! $s )
      continue;
    if( $s[ 0 ] != '/' ) {
      if( ! in_array( $s, $classes ) ) {
        $classes[] = $s;
      }
    } else {
      $se = explode( '/', $s );
      switch( count( $se ) ) {
        case 3: // keep-rule
          need( ( ! $se[ 0 ] ) && ( ! $se[ 2 ] ) );
          foreach( $classes as $key => $class ) {
            if( ! preg_match( $s, $class ) ) {
              unset( $classes[ $key ] );
            }
          }
          break;
        case 4:
          need( ( ! $se[ 0 ] ) && ( ! $se[ 3 ] ) );
          if( $se[ 2 ] ) {
            // replacement rule
            foreach( $classes as & $class ) {
              $class = preg_replace( '/'.$se[ 1 ].'/', $se[ 2 ], $class );
            }
            unset( $class );
          } else {
            // drop rule
            foreach( $classes as $key => $class ) {
              if( preg_match( '/'.$se[ 1 ] .'/', $class ) ) {
                unset( $classes[ $key ] );
              }
            }
          }
          break;
        default:
          error( 'cannot parse class specification' );
      }
    }
   }
   return $classes;
}

$tag_roles = array( 'table' => 1, 'thead' => 1, 'tbody' => 1, 'tfoot' => 1, 'tr' => 1, 'td' => 1, 'caption' => 1 );

// open_tag(), close_tag(): open and close html tag. wrong nesting will cause an error
//   $attr: assoc array of attributes to insert into the tag
// 
// if a class name $role from $tag_roles is found in attr['class'], it is treated specially:
//  - use parent class ".$role", not "$tag"
//  - $role is stored in stack opentags to remember structural role
//  - apply special logic to allow omission of close tags for .td and .tr
//  typical uses: "open_fieldset('table') to open a css table, open_div('td') to open a cell
//
function & open_tag( $tag, $attr = array() ) {
  global $open_tags, $debug, $tag_roles;

  $n = count( $open_tags );
  $attr = parameters_explode( $attr, 'class' );
  if( is_string( ( $newclasses = adefault( $attr, 'class', array() ) ) ) ) {
    $newclasses = explode( ' ', $newclasses );
  }
  $role = '';
  foreach( $newclasses as $c ) {
    if( isset( $tag_roles[ $c ] ) ) {
      $role = $c;
    }
  }
  if( ( $role === 'td' ) && ( $open_tags[ $n ]['role'] === 'td' ) ) {
    close_tag();
    $n--;
  }

  $pclasses = $open_tags[ $n ]['pclasses'];
  $thispclasses = adefault( $pclasses, $role ? ".$role" : $tag, array() );

  foreach( $newclasses as $c ) {
    if( ! $c )
      continue;
    $ce = explode( ':', $c );
    if( isset( $ce[ 1 ] ) ) {
      $tags = explode( ';', $ce[ 0 ] );
      foreach( $tags as $t ) {
        $pclasses[ $t ] = merge_classes( adefault( $pclasses, $t, array() ), str_replace( ';', ' ', $ce[ 1 ] ) );
      }
    } else {
      $thispclasses = merge_classes( $thispclasses, str_replace( ';', ' ', $ce[ 0 ] ) );
    }
  }

  need( ! isset( $attr['attr'] ), "obsolete attribute attr detected" );
  if( ( $id = adefault( $attr, 'id', '' ) ) === true ) {
    $id = $attr['id'] = 'i'.new_html_id();
  }

  $open_tags[ ++$n ] = array( 'tag' => $tag, 'pclasses' => $pclasses, 'id' => $id, 'role' => $role );

  switch( "$tag" ) {
    case 'form':
      need( ! $GLOBALS['current_form'], 'must not nest forms' );
      $open_tags[ $n ]['hidden_input'] = array();
      $GLOBALS['current_form'] = & $open_tags[ $n ]; // _must_ use $GLOBALS here: $current_form is just a local reference!
      break;
    case 'table':
      $GLOBALS['current_table'] = & $open_tags[ $n ];
      break;
    case 'tr':
      $GLOBALS['current_tr'] = & $open_tags[ $n ];
      break;
    case 'ul':
    case 'ol':
      $GLOBALS['current_list'] = & $open_tags[ $n ];
      break;
  }
  $attr['class'] = implode( ' ', $thispclasses ) . ( $debug ? ' debug' : '' );
  echo html_tag( $tag, $attr );
  return $open_tags[ $n ];
}

function close_tag( $tag_to_close = false ) {
  global $open_tags, $current_form, $current_table, $debug, $H_SQ;

  $n = count( $open_tags );
  $tag = $open_tags[ $n ]['tag'];
  if( $tag_to_close ) {
    while( $tag_to_close !== $tag ) { // maybe we close a fieldset('table') ?
      switch( $open_tags[ $n ]['role'] ) {
        case 'td':
        case 'tr':
        case 'tbody':
        case 'thead':
        case 'tfoot':
          echo html_tag( $tag, false );
          unset( $open_tags[ $n-- ] );
          $tag = $open_tags[ $n ]['tag'];
          break;
        default:
          error( "close_tag(): unmatched tag: got:$tag_to_close / expected:$tag", LOG_FLAG_CODE, 'html' );
      }
    }
  }
  switch( "$tag" ) {
    case 'form':
      foreach( $open_tags[ $n ]['hidden_input'] as $name => $val ) {
        echo html_tag( 'input', array( 'type' => 'hidden', 'name' => $name, 'value' => $val ) );
      }
      // debug( $open_tags[$n]['hidden_input']['itan'], 'itan' );
      unset( $GLOBALS['current_form'] ); // break reference before...
      $GLOBALS['current_form'] = NULL;   // ...assignment!
      break;
    case 'table':
      unset( $GLOBALS['current_table'] );
      $GLOBALS['current_table'] = NULL;
      for( $j = $n - 1; $j > 0; $j -- ) {
        if( $open_tags[ $j ]['tag'] === 'table' ) {
          $GLOBALS['current_table'] = & $open_tags[ $j ];
          break;
        }
      }
      break;
    case 'tr':
      unset( $GLOBALS['current_tr'] );
      $GLOBALS['current_tr'] = NULL;
      for( $j = $n - 1; $j > 0; $j -- ) {
        if( $open_tags[ $j ]['tag'] === 'tr' ) {
          $GLOBALS['current_tr'] = & $open_tags[ $j ];
          break;
        }
      }
      break;
    case 'ul':
    case 'ol':
      unset( $GLOBALS['current_list'] );
      $GLOBALS['current_list'] = NULL;
      for( $j = $n - 1; $j > 0; $j -- ) {
        if( ( $open_tags[ $j ]['tag'] === 'ul' ) || ( $open_tags[ $j ]['tag'] === 'ol' ) ) {
          $GLOBALS['current_list'] = & $open_tags[ $j ];
          break;
        }
      }
      break;
  }
  echo html_tag( $tag, false );
  unset( $open_tags[ $n-- ] );
}


function open_div( $attr = array(), $payload = false ) {
  open_tag( 'div', $attr );
  if( $payload !== false ) {
    echo $payload;
    close_div();
  }
}

function close_div() {
  close_tag( 'div' );
}

function open_span( $attr = array(), $payload = false ) {
  open_tag( 'span', $attr );
  if( $payload !== false ) {
    echo $payload;
    close_span();
  }
}

function close_span() {
  close_tag( 'span' );
}

function open_pre( $attr = array(), $payload = false ) {
  open_tag( 'pre', $attr );
  if( $payload !== false ) {
    echo $payload;
    close_pre();
  }
}

function close_pre() {
  close_tag( 'pre' );
}



// open/close_table(), open/close_td/th/tr():
//   these functions will take care of correct nesting, so explicit call of close_td will rarely be needed
// open_table() will open html ("real") table by default; to open a css table:
// - either call open_table('css=1') 
// - or pass css class 'table' to any suitable element, eg open_fieldset('table')
// open/close_tr(), open/close_td(), close_table() will automatically produce the correct html or fake (css) table tags depending on context
// thead, tfoot, tbody are optional; tbody will be automatically inserted between table and first tr, for both types of tables

// open_table():
// $options: assoc array, may contain html attributes and optionally special table options:
//   'css': produce css ("fake") table; in this case, the other options below have no effect
//   'limits': array with options to control paging
//   'toggle_prefix': prefix of persistent variables to allow toggling of table columns
//   'colgroup': space-separated list of column widths
//   'cols': array with per-column options
//
function open_table( $options = array() ) {
  $options = parameters_explode( $options, 'class' );
  $attr = array( 'id' => 'table_'.new_html_id() ); // may be overridden below
  $colgroup = false;
  $limits = false;
  $toggle_prefix = '';
  $sort_prefix = '';
  $cols = array();
  $css_table = false;
  foreach( $options as $key => $val ) {
    switch( $key ) {
      case 'colgroup':
        $colgroup = $val;
        break;
      case 'limits':
        $limits = $val;
        break;
      case 'sort_prefix':
        $sort_prefix = $val;
        break;
      case 'toggle_prefix':
        $toggle_prefix = $val;
        break;
      case 'cols':
        $cols = $val;
        break;
      case 'css':
        $css_table = $val;
        break;
      default:
        $attr[ $key ] = $val;
        break;
    }
  }

  if( $css_table ) {
    $attr['class'] =  'table '.adefault( $options, 'class', '' );
    return open_div( $attr );
  }

  $current_table =& open_tag( 'table', $attr );

  if( $colgroup ) {
    echo html_tag( 'colgroup' );
    foreach( explode( ' ', $colgroup ) as $w ) {
      echo html_tag( 'col', array( 'width' => $w ) );
    }
    echo html_tag( 'colgroup', false );
  }

  if( $toggle_prefix ) {
    $current_table['toggle_prefix'] = $toggle_prefix;
  }
  if( $sort_prefix ) {
    $current_table['sort_prefix'] = $sort_prefix;
  }
  if( $cols ) {
    $current_table['cols'] = $cols;
  }
  if( $limits || $toggle_prefix ) {
    open_caption();
    open_div('center'); // no other way(?) to center <caption>
      if( $toggle_prefix ) {
        $choices = array();
        foreach( $cols as $tag => $col ) {
          if( (string)( adefault( $col, 'toggle', 1 ) ) === '0' ) {
            $header = adefault( $col, 'header', $tag );
            $choices[ $tag ] = $header;
          }
        }
        if( $choices ) {
          open_div( 'td left' );
            echo dropdown_element( array(
              'name' => $toggle_prefix.'toggle'
            , 'choices' => $choices
            , 'default_display' => we('show column...','einblenden...')
            ) );
          close_div();
        }
      }
      if( adefault( $limits, 'limits', false ) ) {
        form_limits( $limits );
      }
    close_div();
    close_caption();
  }
  $current_table['row_number'] = 1;
}

function close_table() {
  global $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[ $n ]['tag'] ) {
    case 'td':
    case 'th':
      close_tag();
    case 'tr':
      close_tag();
    case 'tbody':
    case 'thead':
    case 'tfoot':
      close_tag();
    case 'table':
      close_tag();
      return;
    default: // try fake table
      break;
  }
  while( true ) {
    switch( $open_tags[ $n ]['role'] ) {
      case 'td':
      case 'tr':
      case 'tbody':
      case 'thead':
      case 'tfoot':
        close_tag();
        $n--;
        break;
      case 'table':
        close_tag();
        return;
      default:
        error( 'unmatched close_table()', LOG_FLAG_CODE, 'html' );
    }
  }
}

function open_caption( $attr = array(), $payload = false ) {
  global $open_tags;
  $attr = parameters_explode( $attr, 'class' );
  $n = count( $open_tags );
  if( $open_tags[ $n ]['tag'] === 'table' ) {
    open_tag( 'caption', $attr );
  } else if( $open_tags[ $n ]['role'] === 'table' ) {
    $attr['class'] = adefault( $attr, 'class', '' ) . ' caption';
    open_div( $attr );
  }
  if( $payload !== false ) {
    echo $payload;
    close_caption();
  }
}

function close_caption() {
  global $open_tags;
  $n = count( $open_tags );
  if( $open_tags[ $n ]['tag'] === 'caption' ) {
    close_tag('caption');
  } else if( $open_tags[ $n ]['role'] === 'caption' ) {
    close_tag('div');
  } else {
    error( 'unmatched close_caption()', LOG_FLAG_CODE, 'html' );
  }
}

function open_tr( $attr = array() ) {
  global $open_tags, $current_table;
  $attr = parameters_explode( $attr, 'class' );
  $n = count( $open_tags );
  switch( $open_tags[ $n ]['tag'] ) {
    case 'table':
      open_tag('tbody');
      $n++;
    case 'thead':
    case 'tfoot':
    case 'tbody':
      $html_table = true;
      break;
    case 'td':
    case 'th':
      close_tag();
      $n--;
    case 'tr':
      close_tag();
      $n--;
      $html_table = true;
      break;
    default: // try fake table
      $html_table = false;
      break;
  }
  if( $html_table ) {
    $class = adefault( $attr, 'class', '' );
    if( $open_tags[ $n ]['tag'] === 'tbody' ) {
      $attr['class'] = merge_classes( ( $current_table['row_number']++ % 2 ) ? 'odd' : 'even', $class );
    }
    $current_table['col_number'] = 0;
    open_tag( 'tr', $attr );
  } else { // fake table
    switch( $open_tags[ $n ]['role'] ) {
      case 'td':
        close_tag();
        $n--;
      case 'tr':
        close_tag();
        $n--;
        break;
      case 'table':
        open_div('tbody');
        $n++;
      case 'thead':
      case 'tfoot':
      case 'tbody':
        break;
      default:
        error( 'misplaced open_tr()', LOG_FLAG_CODE, 'html' );
    }
    $attr['class'] = adefault( $attr, 'class', '' ) . ' tr';
    open_div( $attr );
  }
}

function close_tr() {
  global $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[ $n ]['tag'] ) {
    case 'td':
    case 'th':
      close_tag( $open_tags[ $n ]['tag'] );
    case 'tr':
      close_tag('tr');
      return;
    default:
      break; // try fake table
  }
  switch( $open_tags[ $n ]['role'] ) {
    case 'td':
      close_tag();
    case 'tr':
      close_tag();
      return;
    default:
      error( 'unmatched close_tr()', LOG_FLAG_CODE, 'html' );
  }
}

// open_tdh(): open td or th element
function open_tdh( $tag, $attr = array(), $payload = false ) {
  global $open_tags;
  $attr = parameters_explode( $attr, 'class' );
  $n = count( $open_tags );
  switch( $open_tags[ $n ]['tag'] ) {
    case 'td':
    case 'th':
      close_tag();
    case 'tr':
      $html_table = true;
      break;
    case 'table':
      open_tag('tbody');
    case 'tbody':
    case 'thead':
    case 'tfoot':
      open_tr();
      $html_table = true;
      break;
    default:
      $html_table = false;
      break;
  }
  if( $html_table ) {
    open_tag( $tag, $attr );
  } else {
    switch( $open_tags[ $n ]['role'] ) {
      case 'table':
        open_div('tbody');
        $n++;
      case 'thead':
      case 'tfoot':
      case 'tbody':
        open_div('tr');
        $n++;
        break;
      case 'td':
        close_tag();
      case 'tr':
        break;
      default:
        error( "unexpected open_td(): innermost open tag: {$open_tags[ $n ]['tag']}", LOG_FLAG_CODE, 'html' );
    }
    $attr['class'] = adefault( $attr, 'class', '' ) . ( ( $tag == 'td' ) ? ' td' : ' td th' );
    open_div( $attr );
  }
  if( $payload !== false ) {
    echo $payload;
    close_tag();
  }
}

function open_td( $attr = '', $payload = false ) {
  open_tdh( 'td', $attr, $payload );
}
function open_th( $attr = '', $payload = false ) {
  open_tdh( 'th', $attr, $payload );
}

function close_td() {
  global $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[ $n ]['tag'] ) {
    case 'td':
    case 'th':
      close_tag();
      return;
  }
  switch( $open_tags[ $n ]['role'] ) {
    case 'td':
      close_tag();
      return;
    default:
      error( 'unmatched close_td()', LOG_FLAG_CODE, 'html' );
  }
}

function close_th() {
  close_td();
}

function current_table_row_number() {
  global $current_table;
  return $current_table['row_number'];
}
function current_table_col_number() {
  global $current_table;
  return $current_table['col_number'];
}

function open_ul( $attr = array() ) {
  open_tag( 'ul', $attr );
}

function close_ul() {
  global $open_tags;

  switch( $open_tags[ count( $open_tags ) ]['tag'] ) {
    case 'li':
      close_tag();
    case 'ul':
      break;
    default:
      error( 'unmatched close_ul()', LOG_FLAG_CODE, 'html' );
  }
  close_tag('ul');
}

function open_li( $attr = array(), $payload = false ) {
  global $open_tags;
  switch( $open_tags[ count( $open_tags ) ]['tag'] ) {
    case 'li':
      close_tag();
    case 'ul':
      open_tag( 'li', $attr );
      break;
    default:
      error( 'unexpected open_li()', LOG_FLAG_CODE, 'html' );
  }
  if( $payload !== false ) {
    echo $payload;
    close_li();
  }
}

function close_li() {
  close_tag( 'li' );
}

// open_form(): open a <form method='post'>
//   $get_parameters: determine the form action: target script and query string
//   (target script is window=$window; default is 'self')
//   $post_parameters: will be posted via <input type='hidden'>
// - hidden input fields will be collected and printed just before </form>
//   (so function hidden_input() (see below) can be called at any point)
// - $get/post_parameters can be arrays or strings (see parameters_explode() in inlinks.php!)
// - if flag $hidden is set, the form will only contain hidden fields and will be inserted
//   just before end of document (to be used to create links which POST data).
//
function open_form( $get_parameters = array(), $post_parameters = array(), $hidden = false ) {
  global $H_SQ;
// global $have_update_form;

  $get_parameters = parameters_explode( $get_parameters );
  $post_parameters = parameters_explode( $post_parameters );

  $name = adefault( $get_parameters, 'name', '' );
  unset( $get_parameters['name'] );
  if( $name === 'update_form' ) {
//    need( ! $have_update_form, 'can only have one update form per page' );
//    $have_update_form = true;
    $form_id = $name;
  } else {
    $form_id = "form_" . new_html_id();
    $name = $name ? $name : $form_id;
  }

  // some standard parameters to be present in every form:
  //
  $post_parameters = array_merge(
    array(
      'itan' => get_itan( $name ) // iTAN: prevent multiple submissions of same form
    , 'offs' => '0x0'  // window scroll position to restore after 'self' call (inserted by js just before submission)
    , 's' => ''        // to pass arbitrary hex-encoded and serialized data (inserted by js just before submission)
    , 'l' => ''        // to pass limited data to be available very early and stored as global $login
    )
  , $post_parameters
  );

  need( ! isset( $get_parameters['attr'] ), 'obsolete parameter attr detected' );

  $target_script = adefault( $get_parameters, 'script', 'self' );
  $get_parameters['context'] = 'form';
  $get_parameters['form_id'] = $form_id;
  $linkfields = inlink( $target_script, $get_parameters );
  $onsubmit = "return do_on_submit({$H_SQ}$form_id{$H_SQ})";
  if( $linkfields['onsubmit'] ) {
    $onsubmit .= " && " . $linkfields['onsubmit'];
  }
  $onsubmit .= ';';

  $attr = array(
    'action' => $linkfields['action']
  , 'method' => 'post'
  , 'name' => $name
  , 'id' => $form_id
  , 'onsubmit' => $onsubmit
  , 'enctype' => 'multipart/form-data' // the magic spell for file upload
  );
  if( ( $enctype = adefault( $get_parameters, 'enctype', '' ) ) )
    $attr['enctype'] = $enctype;
  if( $linkfields['target'] )
    $attr['target'] = $linkfields['target'];

  if( $hidden ) {
    $form = html_tag( 'span', array( 'class' => 'nodisplay' ) ) . html_tag( 'form', $attr );
      foreach( $post_parameters as $key => $val )
        $form .= html_tag( 'input', array( 'type' => 'hidden', 'name' => $key, 'value' => $val ), NULL );
    $form .= html_tag( 'form', false ) . html_tag( 'span', false );
    print_on_exit( $form );
  } else {
    $t =& open_tag( 'form', $attr );
    $t['hidden_input'] = $post_parameters;
  }
  // js_on_exit( "todo_on_submit[ {$H_SQ}$form_id{$H_SQ} ] = new Array();" );
  // js_on_exit( "register_on_submit( '$form_id', \"alert( 'on_submit: $form_id' );\" ) ");
  return $form_id;
}

// hidden_input(): 
// - register parameter $name, value $val to be inserted as a hidden input field
//   just before </form> 
// - thus, this function can be called anywhere in the html structure, not just
//   where <input> is allowed
//
function hidden_input( $name, $val = false ) {
  $hidden_input = & $GLOBALS['current_form']['hidden_input'];
  if( $val === false ) {
    $val = $GLOBALS[ $name ];
  }
  if( $val === NULL )
    unset( $hidden_input[ $name ] );
  else
    $hidden_input[ $name ] = $val;
}

function close_form() {
  close_tag( 'form' );
}

// open_fieldset():
//   $toggle: allow user to display / hide the fieldset; $toggle == 'on' or 'off' determines initial state
//
function open_fieldset( $attr = array(), $legend = '', $toggle = false ) {
  global $H_SQ;
  $attr = parameters_explode( $attr, 'class' );
  if( $toggle ) {
    if( $toggle == 'on' ) {
      $buttondisplay = 'none';
      $fieldsetdisplay = 'block';
    } else {
      $buttondisplay = 'inline';
      $fieldsetdisplay = 'none';
    }
    $id = new_html_id();
    $attr['id'] = 'button_'.$id;
    $attr['style'] = "display:$buttondisplay;";
    open_span( $attr, html_tag( 'a'
    , array(
        'class' => 'button', 'href' => '#'
      , 'onclick' => "$({$H_SQ}fieldset_$id{$H_SQ}).style.display={$H_SQ}block{$H_SQ};$({$H_SQ}button_$id{$H_SQ}).style.display={$H_SQ}none{$H_SQ};"
      )
    , "$legend..."
    ) );

    $attr['id'] = 'fieldset_'.$id;
    $attr['style'] = "display:$fieldsetdisplay;";
    open_fieldset( $attr );
    open_tag( 'legend' );
      echo html_tag( 'img', array(
          'src' => 'img/close.small.blue.trans.gif'
        , 'alt' => 'close'
        , 'onclick' => "$({$H_SQ}button_$id{$H_SQ}).style.display={$H_SQ}inline{$H_SQ};$({$H_SQ}fieldset_$id{$H_SQ}).style.display={$H_SQ}none{$H_SQ};"
        , 'style' => 'padding-right:1ex;'
        )
      , NULL
      );
      echo $legend;
    close_tag( 'legend' );
  } else {
    open_tag( 'fieldset', $attr );
    if( $legend )
      echo html_tag( 'legend', '', $legend );
  }
}

function close_fieldset() {
  close_tag( 'fieldset' );
}

function open_javascript( $js = false ) {
  echo "\n";
  open_tag( 'script', array( 'type' => "text/javascript" ) );
  echo "\n";
  if( $js !== false ) {
    echo $js ."\n";
    close_javascript();
  }
}

function close_javascript() {
  close_tag( 'script' );
}

function open_html_comment( $payload = false ) {
  echo "\n".H_LT.'!-- ';
  if( $payload !== false ) {
    echo $payload;
    close_html_comment();
  }
}

function close_html_comment() {
  echo ' --'.H_GT."\n";
}

// open_label(): create <label> for form field $field:
// - with css class from $field, to indicate errors or modification
// - with suitable id so the css class can be changed from js
//
function open_label( $field, $opts = array(), $payload = false ) {
  $field = parameters_explode( $field, 'cgi_name' );
  $opts = parameters_explode( $opts, 'class' );
  $c = trim( adefault( $opts, 'class', '' ) .' '. adefault( $field, 'class', '' ) );
  $attr = array( 'class' => $c );
  if( ( $fieldname = adefault( $field, array( 'cgi_name', 'name' ), '' ) ) ) {
    $attr['for'] = "input_$fieldname";
    $attr['id'] = "label_$fieldname";
  }
  if( isset( $opts['for'] ) ) {
    $attr['for'] = $opts['for'];
  }
  open_tag( 'label', $attr, $payload );
  if( $payload !== false ) {
    echo $payload;
    close_label();
  }
}
  
function close_label() {
  close_tag( 'label' );
}

// open_input(): similar to open_label(), but to create <span> for form field $field itself:
//
function open_input( $field, $opts = array(), $payload = false ) {
  $field = parameters_explode( $field, 'cgi_name' );
  $opts = parameters_explode( $opts, 'class' );
  $c = trim( 'input '. adefault( $opts, 'class', '' ) .' '. adefault( $field, 'class', '' ) );
  $attr = array( 'class' => $c );
  if( ( $fieldname = adefault( $field, array( 'cgi_name', 'name' ), '' ) ) ) {
    $attr['id'] = "input_$fieldname";
  }
  open_span( $attr, $payload );
}
function close_input() {
  close_tag( 'span' );
}


function html_options( & $selected, $values ) {
  $output = '';
  foreach( $values as $value => $t ) {
    if( is_array( $t ) ) {
      $text = $t[0];
      $title = $t[1];
    } else {
      $text = $t;
      $title = $t;
    }
    $attr = array( 'value' => $value );
    if( $title ) {
      $attr['title'] = $title;
    }
    if( "$value" == "$selected" ) {
      $attr['selected'] = 'selected';
      $selected = -1;
    }
    $output .= html_tag( 'option', $attr, $text );
  }
  return $output;
}

function html_options_distinct( & $selected, $table, $column, $option_0 = false ) {
  $values = sql_query( $table, "distinct=$column" );
  if( $option_0 )
    $values[0] = $option_0;
  $output = html_options( /* & */ $selected, $values );
  if( $selected != -1 ) {
    $output = html_tag( 'option', 'value=,selected=selected', '(please select)' ) . $output;
    $selected = -1; // passed by reference!
  }
  return $output;
}


function print_on_exit( $text ) {
  global $print_on_exit_array;
  $print_on_exit_array[] = $text;
}
function js_on_exit( $text ) {
  global $js_on_exit_array;
  $js_on_exit_array[] = $text;
}

function move_html( $id, $target_id ) {
  js_on_exit( "move_html( {$H_SQ}$id{$H_SQ}, {$H_SQ}$target_id{$H_SQ} );" );
}

function print_on_exit_out() {
  global $print_on_exit_array, $js_on_exit_array;
  // print all html before js, so we can use html objects from js:
  foreach( $print_on_exit_array as $p )
    echo "\n" . $p;
  if( $js_on_exit_array ) {
    open_javascript();
      foreach( $js_on_exit_array as $js )
        echo "\n" . $js;
      echo "\n";
    close_javascript();
  }
  $print_on_exit_array = array();
  $js_on_exit_array = array();
}

function close_all_tags() {
  global $open_tags;
  for( $n = count( $open_tags ); $n > 1; $n-- ) {
    if( $open_tags[ $n ]['tag'] == 'body' ) {
      print_on_exit_out();
    }
    close_tag( $open_tags[ $n ]['tag'] );
  }
  // maybe we had no body (on early errors) - print anyway, it should at least be readable in the source code:
  print_on_exit_out();
}


// close all open html tags even in case of early error exit:
//
register_shutdown_function( 'close_all_tags' );

function menatwork( $msg = 'men at work here - incomplete code ahead' ) {
  open_div( 'warn', $msg );
}

// function open_hints() {
//   global $html_hints;
//   $n = count( $html_hints );
//   $html_hints[++$n] = new_html_id();
// }
// function close_hints( $class = 'kommentar', $initial = '' ) {
//   global  $html_hints;
//   $n = count( $html_hints );
//   $id = $html_hints[$n];
//   open_div( "$class,id=hints_$id", $initial );
//   unset( $html_hints[$n--] );
// }
// 
// function html_hint( $hint ) {
//   global $html_hints;
//   $n = count( $html_hints );
//   $id = $html_hints[$n];
//   return " onmouseover=\" document.getElementById('hints_$id').firstChild.nodeValue = '$hint'; \" "
//         . " onmouseout=\" document.getElementById('hints_$id').firstChild.nodeValue = ' '; \" ";
// }


// the following are kludges to replace the missing <spacer> (equivalent of \kern) element:
//
function smallskip() {
  open_div( 'smallskip', '' );
}
function medskip() {
  open_div( 'medskip', '' );
}
function bigskip() {
  open_div( 'bigskip', '' );
}
function quad() {
  open_span( 'quad', '' );
}
function qquad() {
  open_span( 'qquad', '' );
}

function hskip( $skip = '1ex' ) {
  // return html_span( "style=padding-left:$skip !important;", H_AMP.'nbsp;' /* browsers suffer from 'horror vacui'; we kludge around it... */ );
  return html_span( "style=padding-left:$skip !important;", '' );
}
function vskip( $skip = '1ex' ) {
  return html_div( "style=padding-top:$skip !important;", H_AMP.'nbsp;' );
}

// color handling
//
function rgb_color_explode( $rgb ) {
  sscanf( $rgb, '%2x%2x%2x', /* & */ $r, /* & */ $g, /* & */ $b );
  return array( $r, $g, $b );
}
function rgb_color_implode( $r, $g, $b) {
  return sprintf( "%02x%02x%02x", $r, $g, $b);
}
function rgb_color_lighten( $rgb, $percent ) {
  list( $r, $g, $b ) = rgb_color_explode( $rgb );
  if( ! isarray( $percent ) ) {
    $percent = array( 'r' => $percent, 'g' => $percent, 'b' => $percent );
  }
  if( isset( $percent['r'] ) ) {
    if( ( $p = $percent['r'] ) > 0 ) {
      $r += ( ( 255 - $r ) * $p ) / 100;
    } else {
      $r = ( $r * ( 100 + $p ) / 100 );
    }
  }
  if( isset( $percent['g'] ) ) {
    if( ( $p = $percent['g'] ) > 0 ) {
      $g += ( ( 255 - $g ) * $p ) / 100;
    } else {
      $g = ( $g * ( 100 + $p ) / 100 );
    }
  }
  if( isset( $percent['b'] ) ) {
    if( ( $p = $percent['b'] ) > 0 ) {
      $b += ( ( 255 - $b ) * $p ) / 100;
    } else {
      $b = ( $b * ( 100 + $p ) / 100 );
    }
  }
  return rgb_color_implode( $r, $g, $b );
}


function html_obfuscate_email( $m, $t = false ) {
  global $H_SQ;
  $l = strtr( $m, 'yrudseanx-', '-yrudseanx' );
  $l = str_replace( '@', '@bl.', $l );
  return html_tag( 'script', array( 'type' => 'text/javascript' )
  , "we( $H_SQ$l$H_SQ " . ( $t !== false ? ", $H_SQ$t$H_SQ" : '' ) . ' );'
  );
}

function confirm_popup( $link, $opts = array() ) {
  $opts = parameters_explode( $opts, 'text' );
  $text = adefault( $opts, 'text', we('are you sure?','Sind Sie sicher?') );
  $payload_id = 'popup'.new_html_id();
  $b1 = html_tag( 'div', 'class=td medskipb left', html_alink( 'javascript:hide_popup();', 'class=quads button,text='.we('No','Nein') ) );
  $b2 = html_tag( 'div', 'class=td medskipb right', html_alink( $link, 'class=quads button,text='.we('Yes','Ja') ) );
  $payload = html_tag( 'div', 'class=center qquads bigskips bold,style=color:black;', $text )
           . html_tag( 'div', 'class=table buttons', html_tag( 'div', 'class=tr', $b1 . $b2 ) );
  $payloadbox = html_tag( 'div', "class=floatingpayload popup", $payload );
  $shadow = html_tag( 'div', 'shadow', '' );
  print_on_exit( html_tag( 'div', "class=floatingframe popup,id=$payload_id", $payloadbox . $shadow ) );
  return $payload_id;
}

?>
