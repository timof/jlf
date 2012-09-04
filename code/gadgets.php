<?php

// dropdown_element
// special choices:
//  '!display': link text (overrides all other sources)
//  '': link text, if no choice is selected
//  '!empty': link text, if no choice, except possibly '0', is available
function dropdown_element( $field ) {
  global $H_SQ;

  if( isset( $field['choices'] ) ) {
    $choices = $field['choices'];
    $use_uids = false;
  } else {
    need( isset( $field['uid_choices'] ) );
    $choices = $field['uid_choices'];
    $use_uids = true;
  }

  if( ! $choices ) {
    open_span( 'warn', we('(selection is empty)','(Auswahl ist leer)' ) );
    return false;
  }

  $selected = adefault( $field, 'value', 0 );
  $fieldname = $field['name'];
  if( $use_uids ) {
    $fieldname = "UID_$fieldname";
    $selected = value2uid( $selected );
  }
  $priority = adefault( $field, 'priority', 1 );
  $fieldclass = adefault( $field, 'class', '' );

  if( $GLOBALS['activate_exploder_kludges'] ) {

    $id = 'select'.new_html_id();
    $attr = array(
      'name' => '' // don't submit unless changed ////was:  "P{$priority}_{$fieldname}"
    , 'id' => $id
    , 'class' => $fieldclass
    , 'onchange' => "submit_form( {$H_SQ}update_form{$H_SQ}, {$H_SQ}P{$priority}_{$fieldname}={$H_SQ} + $({$H_SQ}{$id}{$H_SQ}).value );"
    );
    if( $selected === null ) {
      $selected = '';
    }
    if( ! isset( $choices[ $selected ] ) ) {
      $choices[ $selected ] = we('(please select)','(bitte wählen)');
    }

    $hexchoices = array();
    foreach( $choices as $key => $val ) {
      $hexchoices[ bin2hex( $key ) ] = $val;
    }
    // if( isset( $attr['selected'] ) ) {
    //  $attr['selected'] = bin2hex( $attr['selected'] );
    // }
    $selected = bin2hex( $selected );

    return html_tag( 'select', $attr, html_options( $selected, $hexchoices ) );

  } else {

    $id = 'dropdown'.new_html_id();
    $payload = '';
    if( adefault( $field, 'search', ( count( $choices ) >= 20 ) ) ) {
      $payload .= html_tag(
        'div'
      , 'class=dropdownsearch'
      , html_tag( 'input', array(
            'type' => 'text'
          , 'class' => "kbd string"
          , 'size' => '12'
          , 'value' => ''
          , 'id' => "search_$id"
          , 'onkeyup' => "dropdown_search($H_SQ$id$H_SQ);"
          , 'onchange' => "dropdown_search($H_SQ$id$H_SQ);"
          )
        , NULL
        )
      );
    }
    $header = html_tag( 'div', 'class=dropdownheader', $payload );

    $payload = '';
    // if( isset( $choices['!extra'] ) ) {
    //   open_div('dropdownitem', $choices['!extra'] );
    // }
    $count = 0;
    foreach( $choices as $key => $choice ) {
      if( $key === '' )
        continue;
      if( substr( $key, 0, 1 ) === '!' )
        continue;
      if( "$key" !== '0' ) {
        $unique = $key; // unique choice iff $count==1 after loop
        $count++;
      }
      $text = substr( $choice, 0, 40 );
      $jlink = inlink( '', array( 'context' => 'js', "P{$priority}_{$fieldname}" => $key ) );
      $alink = html_alink( "javascript: $jlink", array( 'class' => 'dropdownlink href', 'text' => $text ) );
      $payload .= html_tag( 'div', 'class=dropdownitem' . ( ( "$key" === "$selected" ) ? ' selected' : '' ), $alink );
        //open_div('dropdownitem', $alink );
          // if( 0 /* use_warp_buttons */ ) {
          //   $button_id = new_html_id();
          //   open_td( 'warp_button warp0', "id = \"$button_id\" onmouseover=\"schedule_warp( '$button_id', '$form_id', '$fieldname', '$key' ); \" onmouseout=\"cancel_warp(); \" ", '' );
          // }
    }
    switch( "$count" ) {
      case '0':
        if( isset( $choices['!empty'] ) ) {
          $payload .= html_tag( 'div', 'class=dropdownitem', $choices['!empty'] );
        }
        break;
      case '1':
        if( ( $selected !== $unique ) && ( adefault( $field, 'auto_select_unique' ) ) ) {
          // not a good idea if several of them trigger for one page:
          // js_on_exit( inlink( '', array( 'context' => 'js', $fieldname => $unique ) ) );
        }
        break;
    }
    $list = html_tag( 'div', 'class=dropdownlist', $payload );

    $dropdown = html_tag( 'div'
    , array(
        'class' => 'floatingpayload dropdown'
      , 'onmouseover' => "mouseoverdropdownbox($H_SQ$id$H_SQ);"
      , 'onmouseout' => "mouseoutdropdownbox($H_SQ$id$H_SQ);"
      )
    , $header . $list
    );

    $frame = html_tag( 'div', "class=floatingframe,id=$id", $dropdown . html_tag( 'div', 'class=shadow', '' ) );

    if( isset( $choices['!display'] ) ) {
      $display = $choices['!display'];
    } else {
      $display = adefault( $choices, array( $selected, '' ), we('(please select)','(bitte wählen)') );
    }
    $button = html_tag( 'span', "class=kbd $fieldclass quads oneline,id=input_".$fieldname, $display );

    return html_tag( 'div'
    , array(
        'class' => 'dropdownelement'
      , 'onmouseover' => "mouseoverdropdownlink($H_SQ$id$H_SQ);"
      , 'onmouseout' => "mouseoutdropdownlink($H_SQ$id$H_SQ);"
      )
    , $frame . $button
    );
  }
}


function selector_int( $field ) {
  $value = adefault( $field, array( 'value', 'old', 'default' ), 0 );
  $min = adefault( $field, 'min', 0 );
  $max = adefault( $field, 'max', 0 );
  $value_in_range = ( ( $value >= $min ) && ( $value <= $max ) );
  $size = max( strlen( "$min" ), strlen( "$max" ) );
  $fieldname = $field['name'];
  $priority = 1 + adefault( $field, 'priority', 1 );
  open_span( 'oneline' );
    echo inlink( '', array( 'class' => 'button tight', 'text' => ' < ', "P{$priority}_{$fieldname}" => min( $max, max( $min, $value - 1 ) ) ) );
    echo int_element( $field + array( 'auto' => 1 ) );
    echo inlink( '', array( 'class' => 'button tight', 'text' => ' > ', "P{$priority}_{$fieldname}" => max( $min, min( $max, $value + 1 ) ) ) );
  close_span();
}

function selector_smallint( $field ) {
  $value = adefault( $field, array( 'value', 'old', 'default' ), 0 );
  need( ( $min = adefault( $field, 'min', false ) ) !== false );
  need( ( $max = adefault( $field, 'max', false ) ) !== false );
  $choices = array( '' => '- ? -' );
  for( $i = $min; $i <= $max; $i++ ) {
    $choices[ $i ] = "- $i -";
  }
  $field['choices'] = $choices;
  echo dropdown_element( $field );
}

function form_limits( $limits ) {
  // debug( $limits, 'limits' );
  $pre = $limits['prefix'];
  open_div( 'center oneline td,style=padding-bottom:0.5ex;' );
    open_span( 'quads', inlink( '!submit', array(
      "P2_{$pre}limit_from" => 0
    , 'class' => ( ( $limits['limit_from'] > 0 ) ? 'button' : 'button pressed' )
    , 'text' => '[<<'
    ) ) );
    open_span( 'quads', inlink( '!submit', array(
      "P2_{$pre}limit_from" => max( 0, $limits['limit_from'] - $limits['limit_count'] )
    , 'class' => ( ( $limits['limit_from'] > 0 ) ? 'button' : 'button pressed' )
    , 'text' => ' < '
    ) ) );
    open_span ( 'qquads oneline' );
      $r = array( 'size' => 4, 'raw' => $limits['limit_count'], 'name' => "{$pre}limit_count" );
      // if( $limits['limit_count'] < 1 ) {
      //   $opts['initial_value'] = '(all)';
      //   $opts['value'] = $limits['count'];
      // }
      echo we('show up to ','zeige bis zu ') . int_element( $r );
      $r['raw'] = $limits['limit_from'];
      $r['name'] = "{$pre}limit_from";
      echo we(' of ',' von '). $limits['count'] . we(' entries from ',' Einträge ab ') . int_element( $r );
      if( $limits['limit_count'] < $limits['count'] ) {
        echo action_button_view( array( 'text' => we(' all ',' alle '), "P2_{$pre}limit_count" => 0 ) );
      }
    close_span();
    open_span( 'quads', inlink( '!submit', array(
      "P2_{$pre}limit_from" => $limits['limit_from'] + $limits['limit_count']
    , 'class' => ( ( $limits['limit_from'] < $limits['count'] - $limits['limit_count'] ) ? 'button' : 'button pressed' )
    , 'text' => ' > '
    ) ) );
    open_span( 'quads', inlink( '!submit', array(
      "P2_{$pre}limit_from" => max( 0, $limits['count'] - $limits['limit_count'] )
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

  $field = tree_merge( parameters_explode( $field ), parameters_explode( $opts ) );

  $v = $field['value'] = max( min( (int) $field['value'], 4 ), 0 );
  $priority = 1 + adefault( $field, 'priority', 1 );
  
  $choice_0 = adefault( $opts, 'choice_0', '' );
  if( $v || ! $choice_0 ) {
    $field['min'] = 1;
    $field['max'] = 4;
    selector_int( $field );
    if( $choice_0 ) {
      open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", "P{$priority}_{$field['name']}" => 0 ) ) );
    }
  } else {
    open_span( 'quads', $choice_0 );
    open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'filter...', "P{$priority}_{$field['name']}" => $thread ) ) );
  }
}

function filter_thread( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'choice_0= (alle) ' ) );
  selector_thread( $field, $opts );
}



function selector_datetime( $field, $opts = array() ) {
  $opts = parameters_explode( $opts );
  

  menatawork();

}


?>
