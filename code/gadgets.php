<?php

// dropdown_select:
// special choices:
//  '!display': link text (overrides all other sources)
//  '': link text, if no choice is selected
//  '!empty': link text, if no choice, except possibly '0', is available
function dropdown_select( $field, $choices /* , $auto = 'auto' */ ) {

  $field = parameters_explode( $field, 'name' );
  if( ! $choices ) {
    open_span( 'warn', '(selection is empty)' );
    return false;
  }

  $selected = adefault( $field, 'value', 0 );
  $fieldname = $field['name'];

  if( $GLOBALS['activate_exploder_kludges'] ) {

    $attr = array(
      'name' => $fieldname
    , 'id' => "input_$fieldname"
    , 'onchange' => onchange_handler( $fieldname, 1 )
    );
    
    open_tag( 'select', $attr );
     echo html_options( $selected, $choices );
    close_tag( 'select' );

//   switch( $attr ) {
//     case 'autoreload':
//       $id = new_html_id();
//       $url = fc_link( 'self', array( 'XXX' => 'X', 'context' => 'action', $fieldname => NULL ) );
//       $attr = "id='select_$id' onchange=\"
//         i = document.getElementById('select_$id').selectedIndex;
//         s = document.getElementById('select_$id').options[i].value;
//         self.location.href = '$url'.replace( /XXX=X/, '&$fieldname='+s );
//       \" ";
//       break;
//     case 'autopost':
//       $id = new_html_id();
//       $attr = "id='select_$id' onchange=\"
//         i = document.getElementById('select_$id').selectedIndex;
//         s = document.getElementById('select_$id').options[i].value;
//         post_action( '$fieldname', s );
//       \" ";
//       break;
//   }

  } else {

    // prettydump( $choices, 'choices' );
  
  
    open_span( 'dropdown_button' );
      open_div( 'dropdown_menu' );
        // echo "DIV DROPDOWN_MENU";
        open_popup();
          // echo "in POPUP: start";
          open_table( 'dropdown_menu' );
            if( isset( $choices['!extra'] ) ) {
              open_tr();
                open_td( 'dropdown_menu,colspan=2', $choices['!extra'] );
              close_tr();
            }
            $count = 0;
            foreach( $choices as $id => $choice ) {
              if( $id === '' )
                continue;
              if( substr( $id, 0, 1 ) === '!' )
                continue;
              if( "$id" !== '0' )
                $count++;
              $text = substr( $choice, 0, 40 );
              $jlink = inlink( '', array( 'context' => 'js', $fieldname => $id ) );
              $alink = html_alink( "javascript: $jlink", array( 'class' => 'dropdown_menu href', 'text' => $text ) );
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
            if( ( ! $count ) && isset( $choices['!empty'] ) ) {
              open_tr();
                open_td( 'colspan=2', $choices['!empty'] );
              close_tr();
            }
          close_table();
          // echo "in POPUP: end";
        close_popup();
      close_div();
  
      if( isset( $choices['!display'] ) ) {
        $display = $choices['!display'];
      } else {
        $display = adefault( $choices, array( $selected, '' ), we('(please select)','(bitte wählen)') );
      }
      $c = adefault( $field, 'class', '' );
      open_span( "class=kbd $c quads oneline,id=input_".$fieldname, $display );
    close_span();
  }
}


function selector_int( $field ) {
  $value = adefault( $field, array( 'value', 'old', 'default' ), 0 );
  $min = adefault( $field, 'min', 0 );
  $max = adefault( $field, 'max', 0 );
  $value_in_range = ( ( $value >= $min ) && ( $value <= $max ) );
  $size = max( strlen( "$min" ), strlen( "$max" ) );
  $fieldname = $field['name'];
  open_span( 'oneline' );
    echo inlink( '', array( 'class' => 'button tight', 'text' => ' < ', $fieldname => min( $max, max( $min, $value - 1 ) ) ) );
    echo int_element( $field + array( 'auto' => 1 ) );
    echo inlink( '', array( 'class' => 'button tight', 'text' => ' > ', $fieldname => max( $min, min( $max, $value + 1 ) ) ) );
  close_span();
}

function selector_smallint( $field ) {
  $value = adefault( $field, array( 'value', 'old', 'default' ), 0 );
  need( ( $min = adefault( $field, 'min', false ) ) !== false );
  need( ( $max = adefault( $field, 'max', false ) ) !== false );
  $options = array( '' => '- ? -' );
  for( $i = $min; $i <= $max; $i++ ) {
    $options[ $i ] = "- $i -";
  }
  echo dropdown_select( $field, $options );
}

function form_limits( $limits ) {
  // debug( $limits, 'limits' );
  open_div( 'center oneline td,style=padding-bottom:0.5ex;' );
    open_span( 'quads', inlink( '!submit', array(
      $limits['prefix'].'limit_from' => 0
    , 'class' => ( ( $limits['limit_from'] > 0 ) ? 'button' : 'button pressed' )
    , 'text' => '[<<'
    ) ) );
    open_span( 'quads', inlink( '!submit', array(
      $limits['prefix'].'limit_from' => max( 0, $limits['limit_from'] - $limits['limit_count'] )
    , 'class' => ( ( $limits['limit_from'] > 0 ) ? 'button' : 'button pressed' )
    , 'text' => ' < '
    ) ) );
    open_span ( 'qquads oneline' );
      $r = array( 'size' => 4, 'raw' => $limits['limit_count'], 'name' => $limits['prefix'].'limit_count' );
      // if( $limits['limit_count'] < 1 ) {
      //   $opts['initial_value'] = '(all)';
      //   $opts['value'] = $limits['count'];
      // }
      echo we('show up to ','zeige bis zu ') . int_element( $r );
      $r['raw'] = $limits['limit_from'];
      $r['name'] = $limits['prefix'].'limit_from';
      echo we(' of ',' von '). $limits['count'] . we(' entries from ',' Einträge ab ') . int_element( $r );
      if( $limits['limit_count'] < $limits['count'] ) {
        echo action_button_view( array( 'text' => we(' all ',' alle '), $limits['prefix'].'limit_count' => 0 ) );
      }
    close_span();
    open_span( 'quads', inlink( '!submit', array(
      $limits['prefix'].'limit_from' => $limits['limit_from'] + $limits['limit_count']
    , 'class' => ( ( $limits['limit_from'] < $limits['count'] - $limits['limit_count'] ) ? 'button' : 'button pressed' )
    , 'text' => ' > '
    ) ) );
    open_span( 'quads', inlink( '!submit', array(
      $limits['prefix'].'limit_from' => max( 0, $limits['count'] - $limits['limit_count'] )
    , 'class' => ( ( $limits['limit_from'] < $limits['count'] - $limits['limit_count'] ) ? 'button' : 'button pressed' )
    , 'text' => '>>]'
    ) ) );
  close_div();
}



function html_checkboxes_list( $prefix, $options, $selected = array() ) {
  if( is_string( $selected ) ) {
    $selected = ( $selected ? explode( ',', $selected ) : array() );
  }
  $s = '';
  foreach( $options as $tag => $title ) {
    open_tag( 'li' );
      echo html_tag( 'input', "class=checkbox,name={$prefix}_{$tag}" . ( in_array( $tag, $selected ) ? ',selected=selected' : '' ), NULL );
      echo $title;
    close_tag( 'li' );
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
      $output = html_tag( 'option', 'value=0,selected=selected', '(Person w&auml;hlen)' ) . $output;
    return $output;
  }
}

function selector_thread( $field, $opts = array() ) {
  global $thread;

  $f = $field['name'];
  $v = $field['value'] = max( min( (int) $field['value'], 4 ), 0 );

  $choice_0 = adefault( $opts, 'choice_0', '' );
  if( $v || ! $choice_0 ) {
    $field['min'] = 1;
    $field['max'] = 4;
    selector_int( $field );
    if( $choice_0 ) {
      open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", $field['name'] => 0 ) ) );
    }
  } else {
    open_span( 'quads', $choice_0 );
    open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => $thread ) ) );
  }
}

function filter_thread( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'choice_0= (alle) ' ) );
  selector_thread( $field, $opts );
}



function selector_datetime( $field, $opts = array() ) {
  $opts = parameters_explode( $opts );
  


}


?>
