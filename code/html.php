<?php
// html.php: functions to create html code
// naming convention:
// - function html_*: return string with html-code, don't print to stdout
// - function open_*, close_*: print to stdout
//
// css design is broken in that cascading goes just a bit to far:
// rules like  
//   table.greenborder tr td { border:1ex solid green; }
// have the unexpected and undesired side effect of putting green borders even
// on nested tables.
// thus, we use the following kludge:
// - all class names of <table> are inserted into every <td> and <th> as well;
// - the rule above can than be written as
//     td.greenborder { border:1ex solid green; }
//   and will still take effect after open_table( 'greenborder' );


// global variables:
//
$open_tags = array();            /* associative array to keep track of open tags and their options */
$open_environments = array();    /* nested environments with inheritable classes */
$current_form = NULL;            /* reference to $open_tags member of type <form> (if any, else NULL) */
$current_table = NULL;           /* reference to innermost $open_tags member of type <table> (if any, else NULL) */
$current_tr = NULL;              /* reference to current <tr> */
$current_list = NULL;            /* reference to innermost $open_tags member of type <ul> or <ol> (if any, else NULL) */
$current_fieldset = NULL;        /* reference to innermost <fieldset> */
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
} else if( preg_match ( '/^mozilla/i', $browser ) ) {  // plain mozilla(?)
  $activate_mozilla_kludges = 1;
} else if( preg_match ( '/^msie/i', $browser ) ) {
  $activate_exploder_kludges = 1;
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

function html_tag( $tag, $attr = array(), $payload = NULL, $nodebug = false ) {
  $n = count( $GLOBALS['open_tags'] );
  $s = ( ( ! $nodebug && $GLOBALS['debug'] ) ? "\n".str_repeat( '  ', $n ) : '' );
  if( $attr === false ) { // produce close-tag
    $s .= H_LT.'/'.$tag.H_GT;
  } else {
    $attr = parameters_explode( $attr, 'class' );
    $s .= H_LT . $tag;
    foreach( $attr as $a => $val ) {
      $s .= ' '.$a.'='.H_DQ.$val.H_DQ;
    }
    if( $payload === false )
      // not yet valid in doctype 'transitional'...  $s .= ' /'.H_GT;
      $s .= H_GT;
    else if( $payload !== NULL )
      $s .= H_GT . $payload . html_tag( $tag, false, NULL, $nodebug );
    else
      $s .= H_GT;
  }
  return $s;
}


// open_tag(), close_tag(): open and close html tag. wrong nesting will cause an error
//   $attr: assoc array of attributes to insert into the tag
//   $opts: assoc array of other options to store in stack $open_tags
//
function & open_tag( $tag, $attr = array(), $opts = array() ) {
  global $open_tags, $open_environments, $current_form, $current_table, $debug;
  global $current_list, $current_tr;

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
    case 'form':
      need( ! $current_form, 'must not nest forms' );
      if( ! isset( $open_tags[ $n ]['hidden_input'] ) )
        $open_tags[ $n ]['hidden_input'] = array();
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
    error( "close_tag(): unmatched tag: got:$tag / expected:{$open_tags[ $n ]['tag']}" );
  }
  switch( "$tag" ) {
    case 'form':
      foreach( $open_tags[ $n ]['hidden_input'] as $name => $val ) {
        echo html_tag( 'input', array( 'type' => 'hidden', 'name' => $name, 'value' => $val ) );
      }
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
            $opts[''] = 'einblenden...';
            open_span( 'td left' );
              dropdown_select( $toggle_prefix.'toggle', $opts );
            close_span();
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
      error( 'unmatched close_table' );
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
      error( 'unexpected open_tr()' );
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
      error( 'unmatched close_tr()' );
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
      error( "unexpected open_td(): innermost open tag: {$open_tags[ $n ]['tag']}" );
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
      , inlink( '!submit', array( 'class' => 'close_small', 'text' => '', 'extra_field' => $toggle_prefix.'toggle', 'extra_value' => $tag ) )
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
      $header = inlink( '!submit', array( 'extra_field' => $sort_prefix.'ordernew', 'extra_value' => $tag, 'text' => $header ) );
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
  // $attr = adefault( $col_opts, 'attr', '' );
  $colspan = adefault( $col_opts, 'colspan', 1 );

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
  open_td( array( 'class' => $class /* , 'attr' => $attr */ , 'colspan' => $colspan ), $payload );
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
      error( 'unmatched close_td' );
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
      error( 'unmatched close_ul()' );
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
      error( 'unexpected open_li()' );
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
      error( 'unmatched close_li' );
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

  // every form gets some default parameters (to be updated via javascript):
  //
  $post_parameters = array_merge(
    array(
      'action' => 'nop', 'message' => '0'
    , 'extra_field' => '', 'extra_value' => '0'
    , 'offs' => '0x0'
    , 'itan' => get_itan( true )  /* iTAN: prevent multiple submissions of same form */
    , 'json' => '' /* reserved for future use */
    )
  , $post_parameters
  );

  need( ! isset( $get_parameters['attr'] ), 'obsolete parameter attr detected' );
  // $attr = adefault( $get_parameters, 'attr', '' );

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
  );
  if( ( $enctype = adefault( $get_parameters, 'enctype', '' ) ) )
    $attr['enctype'] = $enctype;
  if( $linkfields['target'] )
    $attr['target'] = $linkfields['target'];

  if( $hidden ) {
    $form = html_tag( 'span', array( 'class' => 'nodisplay' ) ) . html_tag( 'form', $attr );
      foreach( $post_parameters as $key => $val )
        $form .= html_tag( 'input', array( 'type' => 'hidden', 'name' => $key, 'value' => $val ), false );
    $form .= html_tag( 'form', '', false ) . html_tag( 'span', '', false );
    print_on_exit( $form );
  } else {
    // echo "\n";
    $opts = array( 'hidden_input' => $post_parameters );
    open_tag( 'form', $attr, $opts );
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
  // insert an invisible submit button: this allows to submit this form by pressing ENTER:
  open_span( 'nodisplay', html_tag( 'input', 'type=submit', false ) );
  // echo "\n";
  close_tag( 'form' );
  // echo "\n";
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
      , 'onclick' => "$({&H_SQ}fieldset_$id{$H_SQ}).style.display={$H_SQ}block{$H_SQ};$({$H_SQ}button_$id{$H_SQ}).style.display={$H_SQ}none{$H_SQ};"
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
      ) );
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


// function floating_submission_button() {
//   global $current_form;
// 
//   $form_id = $current_form['id'];
//   open_span( "alert floatingbuttons,id=floating_submit_button_$form_id" );
//     open_table('layout');
//       open_td('alert left');
//         echo "
//           <a class='close' title='Schliessen' href='javascript:true;'
//           onclick='document.getElementById(\"floating_submit_button_$form_id\").style.display = \"none\";'>
//         ";
//       open_td( 'alert center quad', "&Auml;nderungen sind noch nicht gespeichert!" );
//     open_tr();
//       open_td( 'alert center oneline smallskip,colspan=2' );
//         reset_button();
//         submission_button();
//     close_table();
//   close_tag('span');
// }


// function check_all_button( $text = 'select all', $title = '' ) {
//   global $form_id;
//   $title or $title = $text;
//   echo "<a class='button' title='$text' onClick='checkAll($form_id);'>$text</a>";
// }
// function uncheck_all_button( $text = 'unselect all', $title = '' ) {
//   global $form_id;
//   $title or $title = $text;
//   echo "<a class='button' title='$text' onClick='uncheckAll($form_id);'>$text</a>";
// }
//
// function html_radio_button( $name, $value, $attr = '', $label = true ) {
//   $s = "<input type='radio' class='radiooption' $attr name='$name' value='$value'";
//   if( $GLOBALS[$name] == $value )
//     $s .= " checked";
//   $s .= ">";
//   if( $label === true )
//     $label = $value;
//   if( $label )
//     $s .= " $label";
//   return $s;
// }
// function radio_button( $name, $value, $attr = '', $label = true ) {
//   echo html_radio_button( $name, $value, $attr, $label );
// }
// 
// open_select(): create <select> element
// $auto supports some magic values:
//  - 'reload': on change, reload current window with the new value of $fieldname in the URL
//  - 'post': on change, submit the update_form (inserted at end of every page), posting the
//    $fieldname as hidden parameter 'action' and the selected option value as parameters 'message'.
//  - 'submit': on change, submit current form
//
// function open_select( $fieldname, $opts = array(), $auto = false ) {
//   global $current_form;
//   $form_id = $current_form['id'];
//   if( $auto ) {
//     $id = new_html_id();
//     switch( $auto ) {
//       case 'reload':
//         $attr .= " id='select_$id' onchange=\"
//           i = document.getElementById('select_$id').selectedIndex;
//           s = document.getElementById('select_$id').options[i].value;
//           submit_form( 'update_form', '$fieldname', s );
//         \" ";
//         break;
//       case 'post':
//         $attr .= " id='select_$id' onchange=\"
//           i = document.getElementById('select_$id').selectedIndex;
//           s = document.getElementById('select_$id').options[i].value;
//           submit_form( '$form_id', '$fieldname', s );
//         \" ";
//         break;
//       case 'submit':
//         $attr .= " id='select_$id' onchange=\"submit_form( '$form_id' );\" ";
//     }
//   }
//   open_tag( 'select', array( 'attr' => "$attr $form_input_event_handlers name='$fieldname'" ) );
//   if( $options ) {
//     echo $options;
//     close_select();
//   }
// }
// 
// function close_select() {
//   close_tag( 'select' );
// }

function open_label( $fieldname, $opts = array(), $payload = false ) {
  $opts = parameters_explode( $opts, 'class' );
  $c = field_class( $fieldname );
  $opts['class'] = adefault( $opts, 'class', '' ) . " label $c";
  $opts['id'] = 'label_'.$fieldname;
  open_span( $opts, $payload );
}
function close_label() {
  close_tag( 'span' );
}

function open_input( $fieldname, $opts = array(), $payload = false ) {
  $opts = parameters_explode( $opts, 'class' );
  $c = field_class( $fieldname );
  $opts['class'] = adefault( $opts, 'class', '' ) . " kbd $c";
  $opts['id'] = 'input_'.$fieldname;
  open_span( $opts, $payload );
}
function close_input() {
  close_tag( 'span' );
}

// function html_input( $text, $fieldname, $parameters = array(), $options = array() ) {
//   if( is_string( $parameters ) )
//     $parameters = parameters_explode( $parameters );
//   $length = adefault( $parameters, 'length', 20 );
//   unset( $parameters['length'] );
//   $form_id = adefault( $parameters, 'form_id', 'update_form' );
//   $parameters['context'] = 'js';
//   return "<input type='text' size='$length' value='$text' name='$fieldname' id='input_$fieldname' "
//          . " onblur=\"alert('bla');\" >";
//   //       . " onchange=\"submit_form( '$form_id', '', '', '$fieldname', document.getElementById('input_$fieldname').value );\"
// }


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


// option_radio(): similar to option_checkbox, but generate a radio button:
// on click, reload current window with all $flags_on set and all $flags_off unset
// in variable $fieldname in the URL
//
// function html_option_radio( $fieldname, $flags_on, $flags_off, $text, $title = false ) {
//   global $$fieldname;
//   $all_flags = $flags_on | $flags_off;
//   $groupname = "{$fieldname}_{$all_flags}";
//   $s = "<input type='radio' class='radiooption' name='$groupname' onclick=\""
//         . inlink('', array( 'context' => 'js' , $fieldname => ( ( $$fieldname | $flags_on ) & ~ $flags_off ) ) ) .'"';
//   if( ( $$fieldname & $all_flags ) == $flags_on )
//     $s .= " checked ";
//   return $s . ">$text";
// }
// function option_radio( $fieldname, $flags_on, $flags_off, $text, $title = false ) {
//   echo html_option_radio( $fieldname, $flags_on, $flags_off, $text, $title );
// }

// alternatives_radio(): create list of radio buttons to toggle on and of html elements
// (typically: fieldsets, each containing a small form)
// $items is an array:
//  - every key is the id of the element to toggle
//  - every value is either a button label, or a pair of label and title for the button
//
// function html_alternatives_radio( $items ) {
//   $id = new_html_id();
//   $s = "<ul class='plain'>";
//   $keys = array_keys( $items );
//   foreach( $items as $item => $value ) {
//     $s .= "<li>";
//     $title = '';
//     if( is_array( $value ) ) {
//       $text = current($value);
//       $title = "title='".next($value)."'";
//     } else {
//       $text = $value;
//     }
//     $s .= "<input type='radio' class='radiooption' name='radio_$id' $title onclick=\"";
//     foreach( $keys as $key )
//       $s .= "document.getElementById('$key').style.display='". ( $key == $item ? 'block' : 'none' ) ."'; ";
//     $s .= "\">$text</li>";
//   }
//   return $s . "</lu>";
// }
// function alternatives_radio( $items ) {
//   echo html_alternatives_radio( $items );
// }

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


// option_menu_row():
// create row in a small dummy table;
// at the end of the document, javascript code will be inserted to move this row into
// a table with id='option_menu_table'
// $payload must contain one or more complete columns (ie <td>...</td> elements)
//
// function open_option_menu_row( $payload = false ) {
//   global $option_menu_counter;
//   $option_menu_counter = new_html_id();
//   open_table();
//   open_tr( "id=option_entry_$option_menu_counter" );
//   if( $payload ) {
//     echo $payload;
//     close_option_menu_row();
//   }
// }
// 
// function close_option_menu_row() {
//   global $option_menu_counter;
//   close_table();
//   js_on_exit( "move_html( 'option_entry_$option_menu_counter', 'option_menu_table' );" );
// }


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

// obsolete code - functionality now implemented in js:
//
// insert_html:
// // erzeugt javascript-code, der $element als Child vom element $id ins HTML einfuegt.
// // $element is entweder ein string (erzeugt textelement), oder ein
// // array( tag, attrs, childs ):
// //   - tag ist der tag-name (z.b. 'table')
// //   - attrs ist false, oder Liste von Paaren ( name, wert) gewuenschter Attribute
// //   - childs ist entweder false, ein Textstring, oder ein Array von $element-Objekten
// function insert_html( $id, $element ) {
//   $output = '
//   ';
//   if( ! $element )
//     return $output;
// 
//   if( is_string( $element ) ) {
//     $autoid = new_html_id();
//     $output = "$output
//       var tnode_$autoid;
//       tnode_$autoid = document.createTextNode('$element');
//       document.getElementById('$id').appendChild(tnode_$autoid);
//     ";
//   } else {
//     assert( is_array( $element ) );
//     $tag = $element[0];
//     $attrs = $element[1];
//     $childs = $element[2];
// 
//     // element mit eindeutiger id erzeugen:
//     $autoid = new_html_id();
//     $newid = "autoid_$autoid";
//     $output = "$output
//       var enode_$newid;
//       var attr_$autoid;
//       enode_$newid = document.createElement('$tag');
//       attr_$autoid = document.createAttribute('id');
//       attr_$autoid.nodeValue = '$newid';
//       enode_$newid.setAttributeNode( attr_$autoid );
//     ";
//     // sonstige gewuenschte attribute erzeugen:
//     if( $attrs ) {
//       foreach( $attrs as $a ) {
//         $autoid = new_html_id();
//         $output = "$output
//           var attr_$autoid;
//           attr_$autoid = document.createAttribute('{$a[0]}');
//           attr_$autoid.nodeValue = '{$a[1]}';
//           enode_$newid.setAttributeNode( attr_$autoid );
//         ";
//       }
//     }
//     // element einhaengen:
//     $output = "$output
//       document.getElementById( '$id' ).appendChild( enode_$newid );
//     ";
// 
//     // rekursiv unterelemente erzeugen:
//     if( is_array( $childs ) ) {
//       foreach( $childs as $c )
//         $output = $output . insert_html( $newid, $c );
//     } else {
//       // abkuerzung fuer reinen textnode:
//       $output = $output . insert_html( $newid, $childs );
//     }
//   }
//   return $output;
// }

?>
