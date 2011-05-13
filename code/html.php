<?php
// html.php: functions to create html code
// naming convention:
// - function html_*: return string with html-code, don't print to stdout
// - function open_*, close_*: print to stdout

global $open_tags             /* keep track of open tags */
     , $print_on_exit_array   /* print this just before </body> */
     , $js_on_exit_array      /* javascript code to insert just before </body> */
     , $html_id               /* draw-a-number-box to generate unique ids */
     , $form_id               /* id of the currently open form (if any) */
     , $input_event_handlers  /* insert into <input> and similar inside a form */
     , $hidden_input          /* hidden input fields, to be inserted just before </form>
     , $html_hints            /* online hints to display for fields */
     , $td_title, $tr_title   /* can be used to set title for next <td> or <tr> */
     , $have_update_form      /* whether we already have a form called 'update_form' */
;
$open_tags = array();
$print_on_exit_array = array();
$js_on_exit_array = array();
$html_id = 0;
$form_id = '';
$input_event_handlers = '';
$hidden_input = array();
$html_hints = array();
$td_title = '';
$tr_title = '';
$have_update_form = false;

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

// open_tag(), close_tag(): open and close html tag. wrong nesting will cause an error
//
function open_tag( $tag, $class = '', $attr = '' ) {
  global $open_tags;
  if( $class )
    $class = "class='$class'";
  echo "<$tag $class $attr>\n";
  $n = count( $open_tags );
  $open_tags[$n+1] = $tag;
}

function close_tag( $tag ) {
  global $open_tags, $hidden_input;
  $n = count( $open_tags );
  switch( $tag ) {
    case 'form':
      foreach( $hidden_input as $name => $val ) {
        echo "<input type='hidden' name='$name' value='$val'>\n";
      }
      $hidden_input = array();
      break;
  }
  if( $open_tags[$n] == $tag ) {
    echo "</$tag>";
    unset( $open_tags[$n--] );
  } else {
    error( "unmatched close_tag(got:$tag / expected:{$open_tags[$n]})" );
  }
}

function open_div( $class = '', $attr = '', $payload = false ) {
  open_tag( 'div', $class, $attr );
  if( $payload !== false ) {
    echo $payload;
    close_div();
  }
}

function close_div() {
  close_tag( 'div' );
}

function open_span( $class = '', $attr = '', $payload = false ) {
  open_tag( 'span', $class, $attr );
  if( $payload !== false ) {
    echo $payload;
    close_span();
  }
}

function close_span() {
  close_tag( 'span' );
}

// open/close_table(), open/close_td/th/tr():
//   these functions will take care of correct nesting, so explicit call of close_td
//   will rarely be needed
//
$table_level = 0;
$table_options_stack = array();
//
function open_table( $class = '', $attr = '', $options = array() ) {
  global $table_level, $table_options_stack;
  // prettydump( $options, 'table: options' );
  open_tag( 'table', $class, $attr );
  if( $options ) {
    open_caption();
      $toggle_prefix = $options['toggle_prefix'];
      $opts = array();
      foreach( $options['cols'] as $tag => $col ) {
        if( adefault( $col, 'toggle', 1 ) == 0 ) {
          $header = adefault( $col, 'header', $tag );
          $opts[ $tag ] = $header;
        }
      }
      if( $opts ) {
        $opts[''] = 'einblenden...';
        open_span( 'floatleft' );
          dropdown_select( $toggle_prefix.'toggle', $opts );
        close_span();
      }
      $limits = adefault( $options, 'limits', false );
      if( adefault( $limits, 'limits', false ) ) {
        form_limits( $limits );
      }
    close_caption();
  }
  $options['row_number'] = 1;
  $table_options_stack[ ++$table_level ] = $options;
}

function close_table() {
  global $table_level, $table_options_stack, $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[$n] ) {
    case 'td':
    case 'th':
      close_tag( $open_tags[$n] );
    case 'tr':
      close_tag( 'tr' );
    case 'table':
      close_tag( 'table' );
      unset( $table_options_stack[ $table_level-- ] );
      break;
    default:
      error( 'unmatched close_table' );
  }
}

$caption_level = 0;  // allow graceful nesting of captions
function open_caption( $class = '', $attr = '', $payload = false ) {
  global $caption_level;
  if( ! $caption_level++ ) {
    open_tag( 'caption', $class, $attr );
  }
  if( $payload !== false ) {
    echo $payload;
    close_caption();
  }
}

function close_caption() {
  global $caption_level;
  if( ! --$caption_level ) {
    close_tag( 'caption' );
  }
}

function open_tr( $class = '', $attr = '' ) {
  global $open_tags, $tr_title, $table_level, $table_options_stack;
  $n = count( $open_tags );
  switch( $open_tags[$n] ) {
    case 'td':
    case 'th':
      close_tag( $open_tags[$n] );
    case 'tr':
      close_tag( 'tr' );
    case 'table':
      $class .= ( ( $table_options_stack[ $table_level ]['row_number']++ % 2 ) ? ' odd' : ' even' );
      $table_options_stack[ $table_level ]['col_number'] = 0;
      open_tag( 'tr', $class, $attr . $tr_title );
      break;
    default:
      error( 'unexpected open_tr' );
  }
  $tr_title = '';
}

function close_tr() {
  global $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[$n] ) {
    case 'td':
    case 'th':
      close_tag( $open_tags[$n] );
    case 'tr':
      close_tag( 'tr' );
      break;
    case 'table':
      break;  // already closed, never mind...
    default:
      error( 'unmatched close_tr' );
  }
}

function open_tdh( $tag, $class= '', $attr = '', $payload = false, $colspan = 1 ) {
  global $open_tags, $td_title, $table_level, $table_options_stack;
  $n = count( $open_tags );
  if( $colspan !== 1 )
    $attr .= " colspan='$colspan'";
  switch( $open_tags[$n] ) {
    case 'td':
    case 'th':
      close_tag( $open_tags[$n] );
    case 'tr':
      open_tag( $tag, $class . $td_title, $attr );
      break;
    case 'table':
      open_tr();
      open_tag( $tag, $class, $attr . $td_title );
      break;
    default:
      error( "unexpected open_td: innermost open tag: {$open_tags[$n]}" );
  }
  $td_title = '';
  if( $payload !== false ) {
    echo $payload;
    close_td();  // will output either </td> or </th>, whichever is needed!
  }
}

function open_td( $class= '', $attr = '', $payload = false, $colspan = 1 ) {
  open_tdh( 'td', $class, $attr, $payload, $colspan );
}
function open_th( $class= '', $attr = '', $payload = false, $colspan = 1 ) {
  open_tdh( 'th', $class, $attr, $payload, $colspan );
}

function open_list_head( $tag = false, $payload = false, $opts = array() ) {
  global $table_options_stack, $table_level;

  $header = $tag;
  $tag = strtolower( $tag );
  $opts = parameters_merge( $table_options_stack[ $table_level ], $opts );
  if( $tag && isset( $opts['cols'][ $tag ] ) ) {
    $col_opts = $opts['cols'][ $tag ];
  } else {
    $col_opts = array();
  }
  // prettydump( $opts, 'opts' );
  // prettydump( $col_opts, 'col_opts' );

  $class = adefault( $col_opts, 'class', adefault( $opts, 'class', '' ) );
  $attr = adefault( $col_opts, 'attr', adefault( $opts, 'attr', '' ) );
  $colspan = adefault( $col_opts, 'colspan', adefault( $opts, 'colspan', 1 ) );
  $toggle_prefix = adefault( $opts, 'toggle_prefix', 'table_' );
  $toggle = 1;
  $close_link = '';
  $header = ( ( $payload !== false ) ? $payload : adefault( $col_opts, 'header', $header ) );

  if( $tag ) {
    $toggle = adefault( $col_opts, 'toggle', 'on' );
    if( $toggle == 1 ) {
      $close_link = "<span style='float:right;'>" . inlink( '', array( 'class' => 'close_small', 'text' => '', $toggle_prefix.'toggle' => $tag ) ) . "</span>";
    }
    if( adefault( $col_opts, 'sort', false ) ) {
      $sort_prefix = adefault( $opts, 'sort_prefix', 'table_' );
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
  switch( $toggle ) {
    case 'off':
    case '0':
      $class .= ' nodisplay';
      $cols = 0;
      break;
    default:
      $cols = $colspan;
  }
  open_th( $class, $attr, $close_link.$header, $colspan );
  $table_options_stack[ $table_level ]['col_number'] += $cols;
}

function open_list_cell( $tag = '', $payload = false, $opts = array() ) {
  global $table_options_stack, $table_level;

  $tag = strtolower( $tag );
  $opts = parameters_merge( $table_options_stack[ $table_level ], $opts );
  if( $tag && isset( $opts['cols'][ $tag ] ) ) {
    $col_opts = $opts['cols'][ $tag ];
  } else {
    $col_opts = array();
  }
  $toggle = 1;
  $class = adefault( $col_opts, 'class', adefault( $opts, 'class', '' ) );
  $attr = adefault( $col_opts, 'attr', adefault( $opts, 'attr', '' ) );
  $colspan = adefault( $col_opts, 'colspan', adefault( $opts, 'colspan', 1 ) );

  if( $tag ) {
    $toggle = adefault( $col_opts, 'toggle', 'on' );
  }
  switch( $toggle ) {
    case 'off':
    case '0':
      $class .= ' nodisplay';
      $cols = 0;
      break;
    default:
      $cols = $colspan;
  }
  open_td( $class, $attr, $payload, $colspan );
  $table_options_stack[ $table_level ]['col_number'] += $cols;
}


function close_td() {
  global $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[$n] ) {
    case 'td':
    case 'th':
      close_tag( $open_tags[$n] );
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
  global $table_level, $table_options_stack;
  return $table_options_stack[ $table_level ]['row_number'];
}
function current_table_col_number() {
  global $table_level, $table_options_stack;
  return $table_options_stack[ $table_level ]['col_number'];
}

function open_ul( $class = '', $attr = '' ) {
  open_tag( 'ul', $class, $attr );
}

function close_ul() {
  global $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[$n] ) {
    case 'li':
      close_tag( 'li' );
    case 'ul':
      close_tag( 'ul' );
      break;
    default:
      error( 'unmatched close_ul' );
  }
}

function open_li( $class = '', $attr = '', $payload = false ) {
  global $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[$n] ) {
    case 'li':
      close_tag( 'li' );
    case 'ul':
      open_tag( 'li', $class, $attr );
      break;
    default:
      error( 'unexpected open_li' );
  }
  if( $payload !== false ) {
    echo $payload;
    close_li();
  }
}

function close_li() {
  global $open_tags;
  $n = count( $open_tags );
  switch( $open_tags[$n] ) {
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
//
function open_form( $get_parameters = array(), $post_parameters = array(), $hidden = false ) {
  global $input_event_handlers, $hidden_input, $have_update_form;

  if( is_string( $get_parameters ) )
    $get_parameters = parameters_explode( $get_parameters );
  if( is_string( $post_parameters ) )
    $post_parameters = parameters_explode( $post_parameters );

  $name = adefault( $get_parameters, 'name', '' );
  unset( $get_parameters['name'] );
  if( $name == 'update_form' ) {
    need( ! $have_update_form, 'can only have one update form per page' );
    $have_update_form = true;
    $form_id = 'update_form';
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
    , 'offs' => '0x0', 'itan' => get_itan( true )
    )
  , $post_parameters
  );

  // set handler to display SUBMIT and RESET buttons after changes:
  // $input_event_handlers = " onchange='on_change($form_id);' ";

  $attr = adefault( $get_parameters, 'attr', '' );

  $target_script = adefault( $get_parameters, 'script', 'self' );
  $get_parameters['context'] = 'form';
  $get_parameters['form_id'] = $form_id;
  $linkfields = inlink( $target_script, $get_parameters );

  $attr .= " action='{$linkfields['action']}' method='post' name='$name' id='$form_id' ";
  $attr .= " onsubmit=\"do_on_submit('$form_id'); {$linkfields['onsubmit']}\"";
  if( ( $enctype = adefault( $get_parameters, 'enctype', '' ) ) )
    $attr .= " enctype='$enctype'";
  if( $linkfields['target'] )
    $attr .= " target='{$linkfields['target']}'";

  if( $hidden ) {
    $form = "\n<span class='nodisplay'><form $attr>";
      foreach( $post_parameters as $key => $val )
        $form .= "<input type='hidden' name='$key' value='$val'>";
    print_on_exit( $form . "</form></span>" );
  } else {
    echo "\n";
    open_tag( 'form', '', "$attr" );
    $GLOBALS['form_id'] = $form_id;
    foreach( $post_parameters as $key => $val )
      hidden_input( $key, $val );
  }
  js_on_exit( "todo_on_submit[ '$form_id' ] = new Array();" );
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
  global $hidden_input;
  if( $val === false ) {
    global $$name;
    $val = $$name;
  }
  if( $val === NULL )
    unset( $hidden_input[ $name ] );
  else
    $hidden_input[ $name ] = $val;
}

function close_form() {
  global $input_event_handlers, $form_id;
  $input_event_handlers = '';
  $form_id = '';
  // insert an invisible submit button: this allows to submit this form by pressing ENTER:
  open_span( 'nodisplay', '', "<input type='submit'>" );
  echo "\n";
  close_tag( 'form' );
  echo "\n";
}

// open_fieldset():
//   $toggle: allow user to display / hide the fieldset; $toggle == 'on' or 'off' determines initial state
//
function open_fieldset( $class = '', $attr = '', $legend = '', $toggle = false ) {
  if( $toggle ) {
    if( $toggle == 'on' ) {
      $buttondisplay = 'none';
      $fieldsetdisplay = 'block';
    } else {
      $buttondisplay = 'inline';
      $fieldsetdisplay = 'none';
    }
    $id = new_html_id();
    open_span( '', "$attr id='button_$id' style='display:$buttondisplay;'" );
      echo "<a class='button' href='javascript:;' onclick=\"document.getElementById('fieldset_$id').style.display='block';
                            document.getElementById('button_$id').style.display='none';\"
            >$legend...</a>";
    close_span();

    open_fieldset( $class, "$attr style='display:$fieldsetdisplay;' id='fieldset_$id'" );
    echo "<legend><img src='img/close_black_trans.gif' alt='Schließen'
            onclick=\"document.getElementById('button_$id').style.display='inline';
                     document.getElementById('fieldset_$id').style.display='none';\">
          $legend</legend>";
  } else {
    open_tag( 'fieldset', $class, $attr );
    if( $legend )
      echo "<legend>$legend</legend>";
  }
}


function close_fieldset() {
  close_tag( 'fieldset' );
}

function open_javascript( $js = '' ) {
  echo "\n";
  open_tag( 'script', '', "type='text/javascript'" );
  echo "\n";
  if( $js ) {
    echo $js ."\n";
    close_javascript();
  }
}

function close_javascript() {
  close_tag('script');
}

function html_submission_button( $action = 'save', $text = 'Speichern', $class = true, $confirm = '' ) {
  global $form_id;
  if( ! is_string( $class ) )
    $class = ( $class ? 'button' : 'button inactive' );
  $field = ( $action ? 'action' : '' );
  return "<span class='quad'>"
         . inlink( '!submit', array( 'class' => $class, 'form_id' => $form_id, 'text' => $text, 'action' => $action, 'confirm' => $confirm ) )
         . "</span>";
}

function submission_button( $action = 'save', $text = 'Speichern', $class = true, $confirm = '' ) {
  echo html_submission_button( $action, $text, $class, $confirm );
}

function floating_submission_button() {
  global $form_id;

  open_span( 'alert floatingbuttons', "id='floating_submit_button_$form_id'" );
    open_table('layout');
      open_td('alert left');
        echo "
          <a class='close' title='Schliessen' href='javascript:true;'
          onclick='document.getElementById(\"floating_submit_button_$form_id\").style.display = \"none\";'>
        ";
      open_td( 'alert center quad', '', "&Auml;nderungen sind noch nicht gespeichert!" );
    open_tr();
      open_td( 'alert center oneline smallskip', "colspan='2'" );
        reset_button();
        submission_button();
    close_table();
  close_tag('span');
}

function html_reset_button( $text = 'reset' ) {
  global $form_id;
  return "
    <span class='qquad'>
      <a class='button inactive' href='javascript:return true;' id='reset_button_$form_id' title='Reset'
                              onClick=\"document.getElementById('$form_id').reset(); on_reset($form_id); \">$text</a>
    </span>
  ";
}
function reset_button( $text = 'reset' ) {
  echo html_reset_button( $text );
}

function check_all_button( $text = 'select all', $title = '' ) {
  global $form_id;
  $title or $title = $text;
  echo "<a class='button' title='$text' onClick='checkAll($form_id);'>$text</a>";
}
function uncheck_all_button( $text = 'unselect all', $title = '' ) {
  global $form_id;
  $title or $title = $text;
  echo "<a class='button' title='$text' onClick='uncheckAll($form_id);'>$text</a>";
}

function html_close_button( $text = 'close' ) {
  return "<a class='button' onclick='if(opener) opener.focus(); closeCurrentWindow();'>$text</a>";
}
function close_button( $text = 'close' ) {
  echo html_close_button( $text );
}

function html_radio_button( $name, $value, $attr = '', $label = true ) {
  $s = "<input type='radio' class='radiooption' $attr name='$name' value='$value'";
  if( $GLOBALS[$name] == $value )
    $s .= " checked";
  $s .= ">";
  if( $label === true )
    $label = $value;
  if( $label )
    $s .= " $label";
  return $s;
}
function radio_button( $name, $value, $attr = '', $label = true ) {
  echo html_radio_button( $name, $value, $attr, $label );
}

// open_select(): create <select> element
// $auto supports some magic values:
//  - 'reload': on change, reload current window with the new value of $fieldname in the URL
//  - 'post': on change, submit the update_form (inserted at end of every page), posting the
//    $fieldname as hidden parameter 'action' and the selected option value as parameters 'message'.
//  - 'submit': on change, submit current form
//
function open_select( $fieldname, $attr = '', $options = '', $auto = false ) {
  global $input_event_handlers, $form_id;
  if( $auto ) {
    $id = new_html_id();
    switch( $auto ) {
      case 'reload':
        $attr .= " id='select_$id' onchange=\"
          i = document.getElementById('select_$id').selectedIndex;
          s = document.getElementById('select_$id').options[i].value;
          submit_form( 'update_form', '$fieldname', s );
        \" ";
        break;
      case 'post':
        $attr .= " id='select_$id' onchange=\"
          i = document.getElementById('select_$id').selectedIndex;
          s = document.getElementById('select_$id').options[i].value;
          submit_form( '$form_id', '$fieldname', s );
        \" ";
        break;
      case 'submit':
        $attr .= " id='select_$id' onchange=\"submit_form( '$form_id' );\" ";
    }
  }
  open_tag( 'select', '', "$attr $input_event_handlers name='$fieldname'" );
  if( $options ) {
    echo $options;
    close_select();
  }
}

function close_select() {
  close_tag( 'select' );
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

// dropdown_select:
// special options:
//  '!display': link text (overrides all other sources)
//  '': link text, if no option is selected
//  '!empty': link text, if no option, except possibly '0', is available
function dropdown_select( $fieldname, $options, $selected = 0, $auto = 'auto' ) {
  global $form_id;

  if( ! $options ) {
    open_span( 'warn', '', '(selection is empty)' );
    return false;
  }
  // prettydump( $options, 'options' );

  if( $auto == 'auto' )
    $auto = ( $form_id ? 'submit' : 'post' );
  open_span( 'dropdown_button' );
    open_table( 'dropdown_menu' );
      if( isset( $options['!extra'] ) ) {
        open_tr();
          open_td( '', "colspan='2'", $options['!extra'] );
        close_tr();
      }
      $count = 0;
      foreach( $options as $id => $opt ) {
        if( $id === '' )
          continue;
        if( substr( $id, 0, 1 ) === '!' )
          continue;
        if( "$id" !== '0' )
          $count++;
        $class = 'href';
        $text = substr( $opt, 0, 40 );
        switch( $auto ) {
          case 'reload':
            $jlink = inlink( '', array( 'context' => 'js', $fieldname => $id ) );
            $alink = inlink( '', array( 'class' => $class, $fieldname => $id , 'title' => $opt, 'text' => $text ) );
            break;
          case 'submit':
            $jlink = "submit_form( '$form_id', '', '', '$fieldname', '$id' ); ";
            $alink = alink( "javascript: $jlink", $class, $text, $opt );
            break;
          case 'post':
            $jlink = "submit_form( 'update_form', '', '', '$fieldname', '$id' ); ";
            $alink = alink( "javascript: $jlink", $class, $text, $opt );
            break;
        }
        if( "$id" === "$selected" ) {
          open_tr( 'selected' );
            open_td( '', "colspan='2'", $text );
          close_tr();
        } else {
          open_tr();
            open_td( '', '', $alink );
            $button_id = new_html_id();
            open_td( 'warp_button warp0', "id = \"$button_id\" onmouseover=\"schedule_warp( '$button_id', '$form_id', '$fieldname', '$id' ); \" onmouseout=\"cancel_warp(); \" ", '' );
          close_tr();
        }
      }
      if( ( ! $count ) && isset( $options['!empty'] ) ) {
        open_tr();
          open_td( '', "colspan='2'", $options['!empty'] );
        close_tr();
      }
    close_table();

    if( isset( $options['!display'] ) ) {
      $display = $options['!display'];
    } else {
      $display = adefault( $options, array( $selected, '' ), '(please select)' );
    }
    open_span( '', '', $display );
  close_span();
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
    $output .= "<option value='$value'";
    if( "$value" == "$selected" ) {
      $output .= " selected";
      $selected = -1;
    }
    if( $title )
      $output .= " title='$title'";
    $output .= ">$text</option>";
  }
  return $output;
}

function html_options_unique( $selected, $table, $column, $option_0 = false ) {
  $values = sql_unique_values( $table, $column );
  if( $option_0 )
    $values[0] = $option_0;
  $output = html_options( & $selected, $values );
  if( $selected != -1 ) {
    $output = "<option value='' selected>(bitte ausw&auml;hlen)</option>" . $output;
    $selected = -1; // passed by reference!
  }
  return $output;
}


// option_checkbox(): create <input type='checkbox'> element
// when clicked, the current window will be reloaded, with $flag toggled in variable $fieldname in the URL
//
function html_option_checkbox( $fieldname, $flag, $text, $title = false ) {
  global $$fieldname;
  $s = '<input type="checkbox" class="checkbox" onclick="'
         . inlink('', array( $fieldname => ( $$fieldname ^ $flag ), 'context' => 'js' ) ) .'" ';
  if( $title )
    $s .= " title='$title' ";
  if( $$fieldname & $flag )
    $s .= " checked ";
  return $s . ">$text";
}
function option_checkbox( $fieldname, $flag, $text, $title = false ) {
  echo html_option_checkbox( $fieldname, $flag, $text, $title );
}


function html_checkboxes_list( $prefix, $options, $selected = array() ) {
  if( is_string( $selected ) ) {
    $selected = ( $current ? explode( ',', $current ) : array() );
  }
  $s = '';
  foreach( $options as $tag => $title ) {
    $s .= "<li><input type='checkbox' class='checkbox' name='$prefix_$tag'";
    if( in_array( $tag, $selected ) )
      $s .= ' selected';
    $s .= "> $title</li>";
  }
  return $s;
}


// option_radio(): similar to option_checkbox, but generate a radio button:
// on click, reload current window with all $flags_on set and all $flags_off unset
// in variable $fieldname in the URL
//
function html_option_radio( $fieldname, $flags_on, $flags_off, $text, $title = false ) {
  global $$fieldname;
  $all_flags = $flags_on | $flags_off;
  $groupname = "{$fieldname}_{$all_flags}";
  $s = "<input type='radio' class='radiooption' name='$groupname' onclick=\""
        . inlink('', array( 'context' => 'js' , $fieldname => ( ( $$fieldname | $flags_on ) & ~ $flags_off ) ) ) .'"';
  if( ( $$fieldname & $all_flags ) == $flags_on )
    $s .= " checked ";
  return $s . ">$text";
}
function option_radio( $fieldname, $flags_on, $flags_off, $text, $title = false ) {
  echo html_option_radio( $fieldname, $flags_on, $flags_off, $text, $title );
}

// alternatives_radio(): create list of radio buttons to toggle on and of html elements
// (typically: fieldsets, each containing a small form)
// $items is an array:
//  - every key is the id of the element to toggle
//  - every value is either a button label, or a pair of label and title for the button
//
function html_alternatives_radio( $items ) {
  $id = new_html_id();
  $s = "<ul class='plain'>";
  $keys = array_keys( $items );
  foreach( $items as $item => $value ) {
    $s .= "<li>";
    $title = '';
    if( is_array( $value ) ) {
      $text = current($value);
      $title = "title='".next($value)."'";
    } else {
      $text = $value;
    }
    $s .= "<input type='radio' class='radiooption' name='radio_$id' $title onclick=\"";
    foreach( $keys as $key )
      $s .= "document.getElementById('$key').style.display='". ( $key == $item ? 'block' : 'none' ) ."'; ";
    $s .= "\">$text</li>";
  }
  return $s . "</lu>";
}
function alternatives_radio( $items ) {
  echo html_alternatives_radio( $items );
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
    if( $open_tags[$n] == 'body' ) {
      print_on_exit_out();
    }
    close_tag( $open_tags[$n] );
  }
  // maybe we had no body (on early errors) - print anyway, it should at least be readable in the source code:
  print_on_exit_out();
}


// close all open html tags even in case of early error exit:
//
register_shutdown_function( 'close_all_tags' );

function html_div_msg( $class, $msg, $backlink = false ) {
  return "<div class='$class'>$msg " . ( $backlink ? inlink( $backlink, 'text=back...' ) : '' ) ."</div>";
}
function div_msg( $class, $msg, $backlink = false ) {
  echo html_div_msg( $class, $msg, $backlink );
}
function menatwork( $msg = 'men at work here - incomplete code ahead' ) {
  div_msg( 'warn', $msg );
}

function open_hints() {
  global $html_hints;
  $n = count( $html_hints );
  $html_hints[++$n] = new_html_id();
}
function close_hints( $class = 'kommentar', $initial = '' ) {
  global  $html_hints;
  $n = count( $html_hints );
  $id = $html_hints[$n];
  open_div( $class, "id='hints_$id'", $initial );
  unset( $html_hints[$n--] );
}

function html_hint( $hint ) {
  global $html_hints;
  $n = count( $html_hints );
  $id = $html_hints[$n];
  return " onmouseover=\" document.getElementById('hints_$id').firstChild.nodeValue = '$hint'; \" "
        . " onmouseout=\" document.getElementById('hints_$id').firstChild.nodeValue = ' '; \" ";
}


// the following are kludges to replace the missing <spacer> (equivalent of \kern) element:
//
function smallskip() {
  open_div('smallskip', '', '' );
}
function medskip() {
  open_div('medskip', '', '' );
}
function bigskip() {
  open_div('bigskip', '', '' );
}
function quad() {
  open_span('quad', '', '' );
}
function qquad() {
  open_span('qquad', '', '' );
}


// option_menu_row():
// create row in a small dummy table;
// at the end of the document, javascript code will be inserted to move this row into
// a table with id='option_menu_table'
// $payload must contain one or more complete columns (ie <td>...</td> elements)
//
function open_option_menu_row( $payload = false ) {
  global $option_menu_counter;
  $option_menu_counter = new_html_id();
  open_table();
  open_tr( '', "id='option_entry_$option_menu_counter'" );
  if( $payload ) {
    echo $payload;
    close_option_menu_row();
  }
}

function close_option_menu_row() {
  global $option_menu_counter;
  close_table();
  js_on_exit( move_html( 'option_entry_' . $option_menu_counter, 'option_menu_table' ) );
}



// insert_html:
// erzeugt javascript-code, der $element als Child vom element $id ins HTML einfuegt.
// $element is entweder ein string (erzeugt textelement), oder ein
// array( tag, attrs, childs ):
//   - tag ist der tag-name (z.b. 'table')
//   - attrs ist false, oder Liste von Paaren ( name, wert) gewuenschter Attribute
//   - childs ist entweder false, ein Textstring, oder ein Array von $element-Objekten
function insert_html( $id, $element ) {
  $output = '
  ';
  if( ! $element )
    return $output;

  if( is_string( $element ) ) {
    $autoid = new_html_id();
    $output = "$output
      var tnode_$autoid;
      tnode_$autoid = document.createTextNode('$element');
      document.getElementById('$id').appendChild(tnode_$autoid);
    ";
  } else {
    assert( is_array( $element ) );
    $tag = $element[0];
    $attrs = $element[1];
    $childs = $element[2];

    // element mit eindeutiger id erzeugen:
    $autoid = new_html_id();
    $newid = "autoid_$autoid";
    $output = "$output
      var enode_$newid;
      var attr_$autoid;
      enode_$newid = document.createElement('$tag');
      attr_$autoid = document.createAttribute('id');
      attr_$autoid.nodeValue = '$newid';
      enode_$newid.setAttributeNode( attr_$autoid );
    ";
    // sonstige gewuenschte attribute erzeugen:
    if( $attrs ) {
      foreach( $attrs as $a ) {
        $autoid = new_html_id();
        $output = "$output
          var attr_$autoid;
          attr_$autoid = document.createAttribute('{$a[0]}');
          attr_$autoid.nodeValue = '{$a[1]}';
          enode_$newid.setAttributeNode( attr_$autoid );
        ";
      }
    }
    // element einhaengen:
    $output = "$output
      document.getElementById( '$id' ).appendChild( enode_$newid );
    ";

    // rekursiv unterelemente erzeugen:
    if( is_array( $childs ) ) {
      foreach( $childs as $c )
        $output = $output . insert_html( $newid, $c );
    } else {
      // abkuerzung fuer reinen textnode:
      $output = $output . insert_html( $newid, $childs );
    }
  }
  return $output;
}

// replace_html: wie insert_html, loescht aber vorher alle Child-Elemente von $id
function replace_html( $id, $element ) {
  $autoid = new_html_id();
  $output = "
    var enode_$autoid;
    var child_$autoid;
    enode_$autoid = document.getElementById('$id');
    while( child_$autoid = enode_$autoid.firstChild )
      enode_$autoid.removeChild(child_$autoid);
  ";
  return $output . insert_html( $id, $element );
}

function move_html( $id, $into_id ) {
  $autoid = new_html_id();
  return "
    var child_$autoid;
    child_$autoid = document.getElementById('$id');
    document.getElementById('$into_id').appendChild(child_$autoid);
  ";
  // appendChild erzeugt _keine_ Kopie!
  // das urspruengliche element verschwindet, also ist das explizite loeschen unnoetig:
  //   document.getElementById('$id').removeChild(child_$autoid);
}



?>
