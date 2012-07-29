<?php
// html.php: functions to create html code
// naming convention:
// - function html_*: return string with html-code, don't print to stdout
// - function open_*, close_*: print to stdout
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


// global variables:
//
$open_tags = array();            /* associative array to keep track of open tags and their options */
$open_environments = array();    /* nested environments with inheritable classes */
$current_form = NULL;            /* reference to $open_tags member of type <form> (if any, else NULL) */
$current_table = NULL;           /* reference to innermost $open_tags member of type <table> (if any, else NULL) */
$current_tr = NULL;              /* reference to current <tr> */
$current_list = NULL;            /* reference to innermost $open_tags member of type <ul> or <ol> (if any, else NULL) */
$print_on_exit_array = array();  /* print this just before </body> */
$js_on_exit_array = array();     /* javascript code to insert just before </body> */
$html_id = 0;                    /* draw-a-number counter to generate unique ids */
// $html_hints = array();        /* online hints to display for fields */
$td_title = '';                  /* can be used to set title for next <td> ... */
$tr_title = '';                  /* ... and <tr>  */
$have_update_form = false;       /* whether we already have a form called 'update_form' */

// set flags to activate workarounds for known browser bugs:
//
$browser = $_SERVER['HTTP_USER_AGENT'];
global $activate_mozilla_kludges, $activate_safari_kludges, $activate_exploder_kludges, $activate_konqueror_kludges;
$activate_safari_kludges = 0;
$activate_mozilla_kludges = 0;
$activate_exploder_kludges = 0;
$activate_konqueror_kludges = 0;
if( preg_match ( '/safari/i', $browser ) ) {  // safari sends "Mozilla...safari"!
  $activate_safari_kludges = 1;
} else if( preg_match ( '/konqueror/i', $browser ) ) {  // dito: konqueror
  $activate_konqueror_kludges = 1;
} else if( preg_match ( '/msie/i', $browser ) ) {
  $activate_exploder_kludges = 1;
} else if( preg_match ( '/^mozilla/i', $browser ) ) {  // plain mozilla(?)
  $activate_mozilla_kludges = 1;
}

// new_html_id(): increment and return next unique id:
//
function new_html_id() {
  global $html_id;
  return ++$html_id;
}

function open_html_environment( $class = 'plain' ) {
  global $open_environments;
  $n = count( $open_environments ) + 1;
  $open_environments[ $n ] = array( 'class' => $class, 'id' => 'e'.new_html_id() );
}

function close_html_environment() {
  global $open_environments;
  $n = count( $open_environments );
  unset( $open_environments[ $n ] );
}

function html_tag( $tag, $attr = array(), $payload = false, $nodebug = false ) {
  $n = count( $GLOBALS['open_tags'] );
  $s = ( ( ! $nodebug && $GLOBALS['debug'] ) ? H_LT."!--\n".str_repeat( '  ', $n ).'--'.H_GT : '' );
  if( $attr === false ) { // produce close-tag
    $s .= H_LT.'/'.$tag.H_GT;
  } else {
    $attr = parameters_explode( $attr, 'class' );
    $s .= H_LT . $tag;
    foreach( $attr as $a => $val ) {
      if( $val !== NULL )
        $s .= ' '.$a.'='.H_DQ.$val.H_DQ;
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
    $ia['class'] = 'icon';
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

// open_tag(), close_tag(): open and close html tag. wrong nesting will cause an error
//   $attr: assoc array of attributes to insert into the tag
//   $opts: assoc array of other options to store in stack $open_tags
//
function & open_tag( $tag, $attr = array(), $opts = array() ) {
  global $open_tags, $open_environments, $current_form, $current_table, $debug;
  global $current_list, $current_tr, $H_LT, $H_GT, $H_DQ;

  $attr = parameters_explode( $attr, 'class' );
  $opts = parameters_explode( $opts );

  if( ( $n = count( $open_environments ) ) ) {
    $env_class = $open_environments[ $n ]['class'];
  } else {
    $env_class = '';
  }

  $class = adefault( $attr, 'class', '' );
  need( ! isset( $attr['attr'] ), "obsolete attribute attr detected" );
  if( ( $id = adefault( $attr, 'id', '' ) ) === true ) {
    $id = $attr['id'] = new_html_id();
  }

  // allow some general-purpose options to be passed as pseudo-attributes:
  if( isset( $attr['move_to'] ) ) {
    $opts['move_to'] = $attr['move_to'];
    unset( $attr['move_to'] );
  }

  $n = count( $open_tags ) + 1;
  $opts['attr'] = $attr;
  $open_tags[ $n ] = tree_merge( array( 'tag' => $tag, 'class' => $class, 'id' => $id ), $opts );

  switch( "$tag" ) {
    case 'html':
      // print doctype babble first:
      echo "\n$H_LT!DOCTYPE HTML PUBLIC $H_DQ-//W3C//DTD HTML 4.01 Transitional//EN$H_DQ$H_GT\n\n";
      break;
    case 'form':
      need( ! $current_form, 'must not nest forms' );
      if( ! isset( $open_tags[ $n ]['hidden_input'] ) )
        $open_tags[ $n ]['hidden_input'] = array();
      // nil report list: to store all checkbox names, so we can positively identify the _unchecked_ ones:
      $GLOBALS['current_form'] = & $open_tags[ $n ]; // _must_ use GLOBALS here: $current_form is just a local reference!
      break;
    case 'table':
      $GLOBALS['current_table'] = & $open_tags[ $n ];
      // prettydump( $current_table, 'open_tag(): current_table' );
      break;
    case 'tr':
      $GLOBALS['current_tr'] = & $open_tags[ $n ];
      $env_class .= ' ' . adefault( $current_table, 'class', '' );
      break;
    case 'td':
    case 'th':
      $env_class .= ' ' . adefault( $current_table, 'class', '' ) . ' ' . adefault( $current_tr, 'class', '' );
      break;
    case 'ul':
    case 'ol':
      $GLOBALS['current_list'] = & $open_tags[ $n ];
      break;
    case 'li':
      $env_class .= ' ' . adefault( $current_list, 'class', '' );
      break;
    case 'body':
    case 'fieldset':
      $env_class = '';
      open_html_environment( $class );
      break;
  }
  $attr['class'] = "$env_class $class" . ( $debug ? ' debug' : '' );
  echo html_tag( $tag, $attr );
  return $open_tags[ $n ];
}

function close_tag( $tag ) {
  global $open_tags, $current_form, $current_table, $debug, $H_SQ;

  $n = count( $open_tags );
  if( $open_tags[ $n ]['tag'] !== $tag ) {
    error( "close_tag(): unmatched tag: got:$tag / expected:{$open_tags[ $n ]['tag']}", LOG_FLAG_CODE, 'html' );
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
    case 'body':
    case 'fieldset':
      close_html_environment();
      break;
  }
  echo html_tag( $tag, false );
  if( $target_id = adefault( $open_tags[ $n ], 'move_to', false ) ) {
    $id = $open_tags[ $n ]['id'];
    js_on_exit( "move_html( {$H_SQ}$id{$H_SQ}, {$H_SQ}$target_id{$H_SQ} );" );
  }
  unset( $open_tags[ $n-- ] );
}

function html_header_printed() {
  return ( count( $GLOBALS['open_tags'] ) > 0 );
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

// function open_popup( $attr = array(), $payload = false ) {
//   global $H_SQ;
//   $attr = parameters_explode( $attr, 'class' );
//   $attr['class'] = 'popup ' . adefault( $attr, 'class', '' );
//   $id = new_html_id();
//   $attr['id'] = 'popup_'.$id;
//   open_span( 'origin' );
//   echo "ORIGIN";
//   open_div( "shadow,style=display:none;,id=shadow_$id", 'SHADOW' );
//   open_div( $attr );
//   if( $payload !== false ) {
//     echo $payload;
//     close_popup();
//   }
//   js_on_exit( "add_shadow( $H_SQ$id$H_SQ );" );
// }
// 
// function close_popup() {
//   close_div();
//   close_span();
// }


function open_popup( $attr = array(), $payload = false ) {
  $attr = parameters_explode( $attr, 'class' );
  $attr['class'] = 'popup ' . adefault( $attr, 'class', '' );
    open_table( 'shadow' );
      open_tr( 'top' );
        open_td( 'tdshadow top,colspan=3' );
      open_tr( 'shadow' );
        open_td( 'tdshadow left', '' );
        open_td( $attr );
  if( $payload !== false ) {
    echo $payload;
    close_popup();
  }
}

function close_popup() {
        open_td( 'tdshadow right', ' ' );
      open_tr( 'bottom' );
        open_td( 'tdshadow bottom,colspan=3', ' ' );
    close_table();
}


// open/close_table(), open/close_td/th/tr():
//   these functions will take care of correct nesting, so explicit call of close_td will rarely be needed

// open_table():
// $options: assoc array, may contain html attributes and optionally special table options:
//   'limits': array with options to control paging
//   'toggle_prefix': prefix of persistent variables to allow toggling of table columns
//   'colgroup': space-separated list of column widths
//   'cols': array with per-column options
//
function open_table( $options = array() ) {
  global $current_table, $open_tags;
  $options = parameters_explode( $options, 'class' );
  $attr = array();
  $colgroup = false;
  $limits = false;
  $toggle_prefix = '';
  foreach( $options as $key => $val ) {
    switch( $key ) {
      case 'colgroup':
        $colgroup = $val;
        break;
      case 'limits':
        $limits = $val;
        break;
      case 'toggle_prefix':
        $toggle_prefix = $val;
        break;
      case 'cols':
        break;
      default:
        $attr[ $key ] = $val;
        break;
    }
  }
  open_tag( 'table', $attr, $options );
  if( $colgroup ) {
    echo html_tag( 'colgroup' );
    foreach( explode( ' ', $colgroup ) as $w ) {
      echo html_tag( 'col', array( 'width' => $w ) );
    }
    echo html_tag( 'colgroup', false );
  }
  if( $limits || $toggle_prefix ) {
    open_caption( 'hfill' );
      open_div( 'tr' );
        if( $toggle_prefix ) {
          $opts = array();
          foreach( adefault( $options, 'cols', array() ) as $tag => $col ) {
            if( (string)( adefault( $col, 'toggle', 1 ) ) === '0' ) {
              $header = adefault( $col, 'header', $tag );
              $opts[ $tag ] = $header;
            }
          }
          if( $opts ) {
            $opts[''] = we('show column...','einblenden...');
            open_div( 'td left' );
              dropdown_select( $toggle_prefix.'toggle', $opts );
            close_div();
          }
        }
        if( adefault( $limits, 'limits', false ) ) {
          form_limits( $limits );
        }
      close_div();
    close_caption();
  }
  $GLOBALS['current_table']['row_number'] = 1;
}

function close_table() {
  global $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[ $n ]['tag'] ) {
    case 'td':
    case 'th':
      close_tag( $open_tags[ $n ]['tag'] );
    case 'tr':
      close_tag( 'tr' );
    case 'table':
      close_tag( 'table' );
      break;
    default:
      error( 'unmatched close_table()', LOG_FLAG_CODE, 'html' );
  }
}

function open_caption( $attr = array(), $payload = false ) {
  open_tag( 'caption', $attr );
  if( $payload !== false ) {
    echo $payload;
    close_caption();
  }
}

function close_caption() {
  close_tag( 'caption' );
}

function open_tr( $attr = array() ) {
  global $open_tags, $tr_title, $current_table;
  $attr = parameters_explode( $attr, 'class' );
  $n = count( $open_tags );
  switch( $open_tags[ $n ]['tag'] ) {
    case 'td':
    case 'th':
      close_tag( $open_tags[ $n ]['tag'] );
    case 'tr':
      close_tag( 'tr' );
    case 'table':
      $class = adefault( $attr, 'class', '' );
      $attr['class'] = $class . ( ( $current_table['row_number']++ % 2 ) ? ' odd' : ' even' );
      $current_table['col_number'] = 0;
      open_tag( 'tr', $attr );
      break;
    default:
      error( 'unmatched open_tr()', LOG_FLAG_CODE, 'html' );
  }
  $tr_title = '';
}

function close_tr() {
  global $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[ $n ]['tag'] ) {
    case 'td':
    case 'th':
      close_tag( $open_tags[ $n ]['tag'] );
    case 'tr':
      close_tag( 'tr' );
      break;
    case 'table':
      break;  // already closed, never mind...
    default:
      error( 'unmatched close_tr()', LOG_FLAG_CODE, 'html' );
  }
}

// open_tdh(): open td or th element
// opts: attributes, plus special option
//   'label' => 'fieldname': this cell contains label of form field 'fieldname'
function open_tdh( $tag, $opts = array(), $payload = false ) {
  global $open_tags, $td_title;
  $opts = parameters_explode( $opts, 'class' );
  $label = adefault( $opts, 'label', false );
  unset( $opts['label'] );
  $n = count( $open_tags );
  switch( $open_tags[ $n ]['tag'] ) {
    case 'td':
    case 'th':
      close_tag( $open_tags[ $n ]['tag'] );
    case 'tr':
      open_tag( $tag, $opts );
      break;
    case 'table':
      open_tr();
      open_tag( $tag, $opts );
      break;
    default:
      error( "unexpected open_td(): innermost open tag: {$open_tags[ $n ]['tag']}", LOG_FLAG_CODE, 'html' );
  }
  $td_title = '';
  if( $label !== false )
    open_label( $label );
  if( $payload !== false ) {
    echo $payload;
    if( $label !== false )
      close_label();
    close_td();  // will output either </td> or </th>, whatever is needed!
  }
}

function open_td( $opts = '', $payload = false ) {
  open_tdh( 'td', $opts, $payload );
}
function open_th( $opts = '', $payload = false ) {
  open_tdh( 'th', $opts, $payload );
}

function open_list_head( $tag = '', $payload = false, $opts = array() ) {
  global $current_table;

  $header = $tag;
  $tag = strtolower( $tag );
  $table_opts = parameters_merge( $current_table, $opts );
  $col_opts = parameters_merge( adefault( $current_table, array( array( 'cols', $tag ) ), NULL ), $opts );
  // prettydump( $col_opts, 'col_opts' );
  // prettydump( $table_opts, 'table_opts' );

  $class = adefault( $col_opts, 'class', '' );
  // $attr = adefault( $col_opts, 'attr', '' );
  $colspan = adefault( $col_opts, 'colspan', 1 );
  $toggle_prefix = adefault( $table_opts, 'toggle_prefix', 'table_' );
  $close_link = '';
  $header = ( ( $payload !== false ) ? $payload : adefault( $col_opts, 'header', $header ) );

  $toggle = 'on';
  if( $tag ) {
    $toggle = adefault( $col_opts, 'toggle', 'on' );
    if( "$toggle" === '1' ) {
      $close_link = html_tag( 'span'
      , array( 'style' => 'float:right;' )
      , inlink( '', array( 'class' => 'close_small', 'text' => '', $toggle_prefix.'toggle' => $tag ) )
      );
    }
    if( adefault( $col_opts, 'sort', false ) ) {
      $sort_prefix = adefault( $table_opts, 'sort_prefix', 'table_' );
      switch( ( $n = adefault( $col_opts, 'sort_level', 0 ) ) ) {
        case 1:
        case 2:
        case 3:
          $class .= ' sort_down_'.$n;
          break;
        case -1:
        case -2:
        case -3:
          $class .= ' sort_up_'.(-$n);
          break;
      }
      $header = inlink( '', array( $sort_prefix.'ordernew' => $tag, 'text' => $header ) );
    }
  }
  switch( "$toggle" ) {
    case 'off':
    case '0':
      $class .= ' nodisplay';
      $cols = 0;
      break;
    default:
      $cols = $colspan;
  }
  open_th( array( 'class' => $class /* , 'attr' => $attr */ , 'colspan' => $colspan ), $close_link.$header );
  // prettydump( $options, 'options' );
  $current_table['col_number'] += $cols;
}

function open_list_cell( $tag = '', $payload = false, $opts = array() ) {
  global $current_table;

  $tag = strtolower( $tag );
  // $table_opts = parameters_merge( $current_table, $opts );
  $col_opts = parameters_merge( adefault( $current_table, array( array( 'cols', $tag ) ), NULL ), $opts );
  $class = adefault( $col_opts, 'class', '' );
  $colspan = adefault( $col_opts, 'colspan', 1 );
  $rowspan = adefault( $col_opts, 'rowspan', 1 );
  $toggle = ( $tag ? adefault( $col_opts, 'toggle', 'on' ) : 'on' );
  switch( $toggle ) {
    case 'off':
    case '0':
      $class .= ' nodisplay';
      $cols = 0;
      break;
    default:
      $cols = $colspan;
  }
  $td_opts = array( 'class' => $class );
  if( $colspan !== 1 )
    $td_opts['colspan'] = $colspan;
  if( $rowspan !== 1 )
    $td_opts['rowspan'] = $rowspan;
  open_td( $td_opts, $payload );
  $current_table['col_number'] += $cols;
}


function close_td() {
  global $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[ $n ]['tag'] ) {
    case 'td':
    case 'th':
      close_tag( $open_tags[ $n ]['tag'] );
      break;
    case 'tr':
    case 'table':
      break; // already closed, never mind...
    default:
      error( 'unmatched close_td()', LOG_FLAG_CODE, 'html' );
  }
}

function close_th() {
  close_td();
}

function tr_title( $title ) {
  global $tr_title;
  $tr_title = " title='$title' ";
}
function td_title( $title ) {
  global $td_title;
  $td_title = " title='$title' ";
}

function current_table_row_number() {
  global $current_table;
  return $current_table['row_number'];
}
function current_table_col_number() {
  global $current_table;
  return $current_table['col_number'];
}

function open_ul( $opts = array() ) {
  open_tag( 'ul', $opts );
}

function close_ul() {
  global $open_tags;

  switch( $open_tags[ count( $open_tags ) ]['tag'] ) {
    case 'li':
      close_tag( 'li' );
    case 'ul':
      close_tag( 'ul' );
      break;
    default:
      error( 'unmatched close_ul()', LOG_FLAG_CODE, 'html' );
  }
}

function open_li( $opts = array(), $payload = false ) {
  global $open_tags;
  switch( $open_tags[ count( $open_tags ) ]['tag'] ) {
    case 'li':
      close_tag( 'li' );
    case 'ul':
      open_tag( 'li', $opts );
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
  global $open_tags;
  switch( $open_tags[ count( $open_tags ) ]['tag'] ) {
    case 'li':
      close_tag( 'li' );
      break;
    case 'ul':
      break;  // already closed, never mind...
    default:
      error( 'unmatched close_li()', LOG_FLAG_CODE, 'html' );
  }
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
  global $have_update_form, $H_SQ;

  $get_parameters = parameters_explode( $get_parameters );
  $post_parameters = parameters_explode( $post_parameters );

  $name = adefault( $get_parameters, 'name', '' );
  unset( $get_parameters['name'] );
  if( $name === 'update_form' ) {
    need( ! $have_update_form, 'can only have one update form per page' );
    $have_update_form = true;
    $form_id = $name;
  } else {
    $form_id = "form_" . new_html_id();
    $name = $name ? $name : $form_id;
  }

  // some standard parameters to be present in every form:
  //
  $post_parameters = array_merge(
    array(
      'itan' => get_itan( true ) // iTAN: prevent multiple submissions of same form
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

  $attr = array(
    'action' => $linkfields['action']
  , 'method' => 'post'
  , 'name' => $name
  , 'id' => $form_id
  , 'onsubmit' => "do_on_submit({$H_SQ}$form_id{$H_SQ}); {$linkfields['onsubmit']}"
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
    open_tag( 'form', $attr, array( 'hidden_input' => $post_parameters ) );
  }
  js_on_exit( "todo_on_submit[ {$H_SQ}$form_id{$H_SQ} ] = new Array();" );
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
function open_fieldset( $opts = array(), $legend = '', $toggle = false ) {
  global $H_SQ;
  $opts = parameters_explode( $opts, 'class' );
  if( $toggle ) {
    if( $toggle == 'on' ) {
      $buttondisplay = 'none';
      $fieldsetdisplay = 'block';
    } else {
      $buttondisplay = 'inline';
      $fieldsetdisplay = 'none';
    }
    $id = new_html_id();
    $opts['id'] = 'button_'.$id;
    $opts['style'] = "display:$buttondisplay;";
    open_span( $opts, html_tag( 'a'
    , array(
        'class' => 'button', 'href' => '#'
      , 'onclick' => "$({$H_SQ}fieldset_$id{$H_SQ}).style.display={$H_SQ}block{$H_SQ};$({$H_SQ}button_$id{$H_SQ}).style.display={$H_SQ}none{$H_SQ};"
      )
    , "$legend..."
    ) );

    $opts['id'] = 'fieldset_'.$id;
    $opts['style'] = "display:$fieldsetdisplay;";
    open_fieldset( $opts );
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
    open_tag( 'fieldset', $opts );
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

// open_label(): create <span> with label for form field $field:
// - with css class from $field, to indicate errors or modification
// - with suitable id so the css class can be changed from js
//
function open_label( $field, $payload = false ) {
  $field = parameters_explode( $field, 'name' );
  $c = adefault( $field, 'class', '' );
  $fieldname = adefault( $field, 'name', '' );
  open_span( array( 'class' => 'label '.$c, 'id' => 'label_'.$fieldname ), $payload );
}
function close_label() {
  close_tag( 'span' );
}

// open_input(): similar to open_label(), but to create <span> for form field $field itself:
//
function open_input( $field, $payload = false ) {
  $field = parameters_explode( $field, 'name' );
  $c = adefault( $field, 'class' );
  $fieldname = adefault( $field, 'name', '' );
  open_span( array( 'class' => 'kbd '.$c, 'id' => 'input_'.$fieldname ), $payload );
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

function html_options_unique( & $selected, $table, $column, $option_0 = false ) {
  $values = sql_unique_values( $table, $column );
  if( $option_0 )
    $values[0] = $option_0;
  $output = html_options( & $selected, $values );
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
  while( $n = count( $open_tags ) ) {
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

// color handling
//
function rgb_color_explode( $rgb ) {
  sscanf( $rgb, '%2x%2x%2x', & $r, & $g, & $b );
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

function html_obfuscated_email( $m, $t = false ) {
  global $H_SQ;
  $l = strtr( $m, 'yrudseanx-', '-yrudseanx' );
  $l = str_replace( '@', 'bl.', $l );
  return html_tag( 'script', array( 'type' => 'text/javascript' )
  , "we( $H_SQ$l$H_SQ " . ( $t !== false ? ", $H_SQ$t$H_SQ" : '' ) . ' );'
  );
}

function confirm_popup( $link, $opts = array() ) {
  $opts = parameters_explode( $opts, 'text' );
  $text = adefault( $opts, 'text', we('are you sure?','Sind Sie sicher?') );
  $payload_id = new_html_id();
  $b1 = html_tag( 'span', 'class=td center', html_alink( 'javascript:hide_popup();', 'class=button,text='.we('No','Nein') ) );
  $b2 = html_tag( 'span', 'class=td center', html_alink( $link, 'class=button,text='.we('Yes','Ja') ) );
  $payload = html_tag( 'div', 'class=center', $text )
           . html_tag( 'div', 'class=tr', $b1 . $b2 );
  print_on_exit( html_tag( 'div', "class=ngpopup,id=$payload_id", $payload ) );
  return $payload_id;
}
            
?>
