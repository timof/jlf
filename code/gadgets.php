<?php

// dropdown_select:
// special options:
//  '!display': link text (overrides all other sources)
//  '': link text, if no option is selected
//  '!empty': link text, if no option, except possibly '0', is available
function dropdown_select( $fieldname, $options, $selected = 0 /* , $auto = 'auto' */ ) {
  global $current_form;

  if( ! $options ) {
    open_span( 'warn', '(selection is empty)' );
    return false;
  }
  // prettydump( $options, 'options' );

  if( $selected === NULL )
    $selected = adefault( $GLOBALS, $fieldname, 0 );

  open_span( 'dropdown_button' );
    open_div( 'dropdown_menu' );
      open_popup();
        open_table( 'dropdown_menu' );
          if( isset( $options['!extra'] ) ) {
            open_tr();
              open_td( 'dropdown_menu,colspan=2', $options['!extra'] );
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
            $text = substr( $opt, 0, 40 );
            $jlink = inlink( '!submit', array( 'context' => 'js', 'extra_field' => $fieldname, 'extra_value' => $id ) );
            $alink = alink( "javascript: $jlink", 'dropdown_menu href', $text, $opt );
            if( "$id" === "$selected" ) {
              open_tr( 'selected' );
                open_td( 'dropdown_menu selected,colspan=2', $text );
              close_tr();
            } else {
              open_tr();
                open_td( 'dropdown_menu,colspan=2', $alink );
                // if( 0 /* use_warp_buttons */ ) {
                //   $button_id = new_html_id();
                //   open_td( 'warp_button warp0', "id = \"$button_id\" onmouseover=\"schedule_warp( '$button_id', '$form_id', '$fieldname', '$id' ); \" onmouseout=\"cancel_warp(); \" ", '' );
                // }
              close_tr();
            }
          }
          if( ( ! $count ) && isset( $options['!empty'] ) ) {
            open_tr();
              open_td( 'colspan=2', $options['!empty'] );
            close_tr();
          }
        close_table();
      close_popup();
    close_div();

    if( isset( $options['!display'] ) ) {
      $display = $options['!display'];
    } else {
      $display = adefault( $options, array( $selected, '' ), '(please select)' );
    }
    open_span( 'kbd', $display );
  close_span();
}



function html_option_checkbox( $fieldname, $flag, $text, $title = false ) {
  global $$fieldname;
  $s = '<input type="checkbox" class="checkbox" onclick="'
         . inlink( '!submit', array( 'extra_field' => $fieldname, 'extra_value' => ( $$fieldname ^ $flag ), 'context' => 'js' ) ) .'" ';
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


if( ! function_exists( 'html_options_people' ) ) {
  function html_options_people( $selected = 0, $filters = array(), $option_0 = false ) {
    if( $option_0 )
      $options[0] = $option_0;
    foreach( sql_people( $filters ) as $p ) {
      $id = $p['people_id'];
      $options[$id] = $p['cn'];
    }
    $output = html_options( & $selected, $options );
    if( $selected != -1 )
      $output = "<option value='0' selected>(Person waehlen)</option>" . $output;
    return $output;
  }
}

function filter_thread( $prefix = '', $option_0 = '(alle)' ) {
  global $current_form, $thread;

  $form_id = ( $current_form ? $current_form['id'] : NULL );
  $f = $prefix.'f_thread';
  $g = & $GLOBALS[ $f ];

  $g = max( min( (int) $g, 4 ), 0 );

  if( $g ) {
    selector_int( $g, $f, 0, 4 );
    open_span( 'quads' );
      if( $option_0 )
        echo inlink( '!submit', array( 'class' => 'button', 'text' => 'Filter...', 'extra_field' => $f, 'extra_value' => 0 ) );
    close_span();
  } else {
    open_span( 'quads', ' (alle) ' );
    open_span( 'quads', inlink( '!submit', array( 'class' => 'button', 'text' => 'Filter...', 'extra_field' => $f, 'extra_value' => $thread ) ) );
  }
}

?>
