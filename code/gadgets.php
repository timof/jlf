<?php


// add_filter_default(): add default choice to turn selector into filter;
// the key for the 'no filter' choice will be
//  - '0' if keys are not uids: such keys are typically primary db keys, where 0 is an impossible value
//  - '0-0' if keys are uids: such keys are typically arbitrary strings, and '0-0' is the hard-wired uid for ''
// which should be suitable for most cases
//
function add_filter_default( $opts = array() ) {
  $opts = parameters_explode( $opts );
  // + for arrays: lhs wins in case of index conflict:
  $opts['choices'] = adefault( $opts, 'choices', array() ) + array( 0 => we( ' (all) ', ' (alle) ' ) );
  $opts['uid_choices'] = adefault( $opts, 'uid_choices', array() ) + array( '0-0' => we( ' (all) ', ' (alle) ' ) );
  return $opts;
}

// dropdown_element( $field ): $field may contain
// - 'choices': array of 'key' => 'option' pairs; options will be offered for selection
// - 'cgi_name': if set, selected 'key' will be submitted to self under this name
// - if cgi_name is not given, 'key' will be interpreted as id of form to be submitted
// - 'form_id': if set, submit 'cgi_name' to this form instead of self
function dropdown_element( $field ) {
  global $H_SQ;

  // what to display if no valid choice is currently selected:
  //
  $default_display = adefault( $field, 'default_display', we('(please select)','(bitte wählen)') );

  // what to display if no choices are available at all:
  //
  $empty_display = adefault( $field, 'empty_display', we('(selection is empty)','(Auswahl ist leer)' ) );

  $choices = adefault( $field, 'choices', array() );
  $uid_choices = adefault( $field, 'uid_choices', array() );
  $use_uids = ( adefault( $field, 'use_uids' ) || $uid_choices );
  $use_action_forms = adefault( $field, 'use_action_forms' ); // keys are form-ids
  if( $use_uids ) {
    foreach( $choices as $key => $val ) {
      $uid_choices[ value2uid( $key ) ] = $val;
    }
    $choices = $uid_choices;
  }

  if( ! $choices ) {
    return html_span( '', $empty_display );
  }

  $selected = adefault( $field, 'value', 0 );
  $priority = adefault( $field, 'priority', 1 );
  $fieldclass = adefault( $field, 'class', '' );
  $form_id = adefault( $field, 'form_id', 'update_form' );

  if( ( $fieldname = adefault( $field, array( 'cgi_name', 'name' ) ) ) ) {
    if( $use_uids ) {
      $fieldname = "UID_$fieldname";
      $selected = value2uid( $selected );
    }
    $pfieldname = "P{$priority}_{$fieldname}";
  }

  if( $GLOBALS['activate_exploder_kludges'] ) {

    $id = 'select'.new_html_id();
    $attr = array(
      'name' => '' // don't submit unless changed ////was:  "P{$priority}_{$fieldname}"
    , 'id' => $id
    , 'class' => $fieldclass
    , 'onchange' => ( $fieldname ?
          "submit_form( {$H_SQ}{$form_id}{$H_SQ}, {$H_SQ}{$pfieldname}={$H_SQ} + $({$H_SQ}{$id}{$H_SQ}).value );"
        : "submit_form( $({$H_SQ}{$id}{$H_SQ}).value )"
      )
    );
    if( $selected === null ) {
      $selected = '';
    }
    if( ! isset( $choices[ $selected ] ) ) {
      $choices[ $selected ] = $default_display;
    }

    $hexchoices = array();
    foreach( $choices as $key => $val ) {
      if( ! $val )
        continue;
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
    $count = 0;
    foreach( $choices as $key => $choice ) {
      $text = substr( $choice, 0, 40 );
      if( $fieldname ) {
        $jlink = inlink( '!submit', array( 'context' => 'js', $pfieldname => $key, 'form_id' => $form_id ) );
      } else {
        $jlink = inlink( '!submit', array( 'context' => 'js', 'form_id' => $key ) );
      }
      $alink = html_alink( "javascript: $jlink", array( 'class' => 'dropdownlink href', 'text' => $text ) );
      $payload .= html_tag( 'div', 'class=dropdownitem' . ( ( "$key" === "$selected" ) ? ' selected' : '' ), $alink );
        //open_div('dropdownitem', $alink );
          // if( 0 /* use_warp_buttons */ ) {
          //   $button_id = new_html_id();
          //   open_td( 'warp_button warp0', "id = \"$button_id\" onmouseover=\"schedule_warp( '$button_id', '$form_id', '$fieldname', '$key' ); \" onmouseout=\"cancel_warp(); \" ", '' );
          // }
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

    $display = adefault( $choices, $selected, $default_display );
    // $button = html_tag( 'span', "class=kbd $fieldclass quads oneline,id=input_".$pfieldname, $display );
    $button = html_tag( 'span', "class=kbd $fieldclass quads oneline", $display );

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

function filter_reset_button( $filters ) {
  $parameters = array( 'text' => 'C', 'class' => 'button reset', 'inactive' => true, 'title' => we('reset filter','filter zurücksetzen') );
  if( isset( $filters['cgi_name'] ) && ! isarray( $filters['cgi_name'] ) ) {
    $filters = array( 'f' => $filters );
  }
  foreach( $filters as $key => $f ) {
    if( $key[ 0 ] === '_' )
      continue;;
    if( $f['value'] !== NULL ) {
      if( $f['value'] !== $f['initval'] ) {
        unset( $parameters['inactive'] );
        $parameters[ $f['cgi_name'] ] = $f['initval'];
      }
    }
  }
  return inlink( '', $parameters );
}

function download_button( $formats, $opts = array() ) {
  $formats = parameters_explode( $formats );
  $opts = parameters_explode( $opts, 'action' );
  $action = adefault( $opts, 'action', 'download' );
  $choices = array();
  foreach( $formats as $f => $flag ) {
    if( ! $flag )
      continue;
    switch( $f ) {
      case 'csv':
      case 'jpg':
        $window = 'NOWINDOW';
        break;
      case 'ldif':
      case 'pdf': // force different browser window (for people with embedded viewers!)
      default:
        $window = 'download';
        break;
    }
    $choices[ open_form( "script=self,window=$window,f=$f", "action=$action", 'hidden' ) ] = $f;
  }
  return dropdown_element( array( 'default_display' => 'download...', 'choices' => $choices ) );
}

function selector_int( $field ) {
  $value = adefault( $field, array( 'value', 'initval', 'default' ), 0 );
  $min = adefault( $field, 'min', 0 );
  $max = adefault( $field, 'max', 0 );
  $value_in_range = ( ( $value >= $min ) && ( $value <= $max ) );
  $size = max( strlen( "$min" ), strlen( "$max" ) );
  $fieldname = $field['cgi_name'];
  $priority = 1 + adefault( $field, 'priority', 1 );
  return html_tag( 'span', 'oneline'
  , inlink( '', array( 'class' => 'button tight', 'text' => ' < ', "P{$priority}_{$fieldname}" => min( $max, max( $min, $value - 1 ) ) ) )
    . int_element( $field + array( 'auto' => 1 ) )
    . inlink( '', array( 'class' => 'button tight', 'text' => ' > ', "P{$priority}_{$fieldname}" => max( $min, min( $max, $value + 1 ) ) ) )
  );
}

// filter_int
// - 'default' is the 'no-filter' value
// - 'initval' is initial value when script is called first time
// - 'default_filter' is the value used when switching from 'no-filter' to 'filter'
function filter_int( $field ) {
  $default_filter = adefault( $field, array( 'default_filter', 'min' ), 0 ); // if not "no-filter"
  $value = adefault( $field, array( 'value', 'initval' ), '' );
  $priority = 1 + adefault( $field, 'priority', 1 );
  $fieldname = $field['cgi_name'];
  if( "$value" === '' ) {
    return html_span( 'quads oneline', we('(any)','(alle)')
             . hskip('2ex') . inlink( '', array( 'class' => 'quad button tight', 'text' => 'filter...', "P{$priority}_{$fieldname}" => $default_filter ) )
           );
  } else {
    return html_span( 'quads online' , selector_int( $field )
             . hskip('2ex') . inlink( '', array( 'class' => 'quad button tight', 'text' => we('any','all'), "P{$priority}_{$fieldname}" => '' ) )
           );
  }
}


function selector_smallint( $field ) {
  $value = adefault( $field, array( 'value', 'initval', 'default' ), 0 );
  need( ( $min = adefault( $field, 'min', false ) ) !== false );
  need( ( $max = adefault( $field, 'max', false ) ) !== false );
  $choices = array();
  for( $i = $min; $i <= $max; $i++ ) {
    $choices[ $i ] = "- $i -";
  }
  $field += array( 'choices' => $choices , 'default_display' => '- ? -' );
  return dropdown_element( $field );
}

function form_limits( $limits ) {
  global $H_SQ, $current_table;
  // debug( $limits, 'limits' );
  $pre = $limits['prefix'];
  open_div( 'center oneline td,style=padding-bottom:0.5ex;' );
    open_span( 'quads', inlink( '!submit', array(
      "P2_{$pre}limit_from" => 1
    , 'class' => ( ( $limits['limit_from'] > 1 ) ? 'button' : 'button pressed' )
    , 'text' => '[<<'
    ) ) );
    open_span( 'quads', inlink( '!submit', array(
      "P2_{$pre}limit_from" => max( 1, $limits['limit_from'] - $limits['limit_count'] )
    , 'class' => ( ( $limits['limit_from'] > 1 ) ? 'button' : 'button pressed' )
    , 'text' => ' < '
    ) ) );
    open_span ( 'qquads oneline' );
      $r = array( 'size' => 4, 'normalized' => $limits['limit_count'], 'name' => "{$pre}limit_count" );
      // if( $limits['limit_count'] < 1 ) {
      //   $opts['initial_value'] = '(all)';
      //   $opts['value'] = $limits['count'];
      // }
      echo we('show up to ','zeige bis zu ') . int_element( $r );
      echo inlink( '', array( 'text' => 'fit', "P2_DEREF_{$pre}limit_count" => "{$pre}limit_count_fit", 'class' => 'button' ) );
      $r['normalized'] = $limits['limit_from'];
      $r['name'] = "{$pre}limit_from";
      echo we(' of ',' von '). $limits['count'] . we(' entries from ',' Einträge ab ') . int_element( $r );
      if( $limits['limit_count'] < $limits['count'] ) {
        echo inlink( '', array( 'text' => we(' all ',' alle '), "P2_{$pre}limit_count" => 0, 'class' => 'button' ) );
      }
    close_span();
    open_span( 'quads', inlink( '!submit', array(
      "P2_{$pre}limit_from" => $limits['limit_from'] + $limits['limit_count']
    , 'class' => ( ( $limits['limit_from'] < $limits['count'] - $limits['limit_count'] ) ? 'button' : 'button pressed' )
    , 'text' => ' > '
    ) ) );
    open_span( 'quads', inlink( '!submit', array(
      "P2_{$pre}limit_from" => max( 1, $limits['count'] - $limits['limit_count'] )
    , 'class' => ( ( $limits['limit_from'] < $limits['count'] - $limits['limit_count'] ) ? 'button' : 'button pressed' )
    , 'text' => '>>]'
    ) ) );
  close_div();
  hidden_input( "{$pre}limit_count_fit", 'X' );
  js_on_exit( "table_find_fit( {$H_SQ}{$current_table['id']}{$H_SQ}, {$H_SQ}{$pre}limit_count_fit{$H_SQ} );" );
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
    $output = html_options( /* & */ $selected, $options );
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
  
  $s = '';
  $choice_0 = adefault( $opts, 'choice_0', '' );
  if( $v || ! $choice_0 ) {
    $field['min'] = 1;
    $field['max'] = 4;
    $s = selector_int( $field );
    if( $choice_0 ) {
      $s .= html_tag( 'span', 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", "P{$priority}_{$field['name']}" => 0 ) ) );
    }
  } else {
    $s .= html_tag( 'span', 'quads', $choice_0 );
    $s .= html_tag( 'span', 'quads', inlink( '', array( 'class' => 'button', 'text' => 'filter...', "P{$priority}_{$field['name']}" => $thread ) ) );
  }
  return $s;
}

function filter_thread( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'choice_0= '.we( ' (all) ', ' (alle) ' ) ) );
  return selector_thread( $field, $opts );
}



function choices_scripts( $filters = array() ) {
  $filters = parameters_explode( $filters, 'tables' );
  $tables = adefault( $filters, 'tables', 'logbook persistent_vars' );
  if( isstring( $tables ) ) {
    $tables = explode( ' ', $tables );
  }
  unset( $filters['tables'] );
  $subqueries = array();
  foreach( $tables as $tname ) {
    $subqueries[] = "SELECT script FROM $tname";
  }
  $query = "SELECT DISTINCT script FROM ( ".implode( " UNION ", $subqueries )." ) AS scripts ORDER BY script";
  $result = mysql2array( sql_do( $query ) );
  $choices = array();
  foreach( $result as $row ) {
    $w = $row['script'];
    $choices[ value2uid( $w ) ] = $w;
  }
  return $choices;
}

function selector_script( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'script' );
  $opts = parameters_explode( $opts );
  $field['uid_choices'] = choices_scripts( adefault( $opts, 'filters', '' ) ) + adefault( $opts, 'uid_choices', array() );
  return dropdown_element( $field );
}

function filter_script( $field, $opts = array() ) {
  return selector_script( $field, add_filter_default( $opts ) );
}

function choices_windows( $filters = array() ) {
  $filters = parameters_explode( $filters, 'tables' );
  $tables = adefault( $filters, 'tables', 'logbook persistent_vars' );
  if( isstring( $tables ) ) {
    $tables = explode( ' ', $tables );
  }
  unset( $filters['tables'] );
  $subqueries = array();
  foreach( $tables as $tname ) {
    $subqueries[] = "SELECT window FROM $tname";
  }
  $query = "SELECT DISTINCT window FROM ( ".implode( " UNION ", $subqueries )." ) AS windows ORDER BY window";
  $result = mysql2array( sql_do( $query ) );
  $choices = array();
  foreach( $result as $row ) {
    $w = $row['window'];
    $choices[ value2uid( $w ) ] = $w;
  }
  return $choices;
}

function selector_window( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'window' );
  $opts = parameters_explode( $opts );
  $field['uid_choices'] = choices_windows( adefault( $opts, 'filters', array() ) ) + adefault( $opts, 'uid_choices', array() );
  return dropdown_element( $field );
}

function filter_window( $field, $opts = array() ) {
  return selector_window( $field, add_filter_default( $opts ) );
}


function selector_datetime( $field, $opts = array() ) {
  $opts = parameters_explode( $opts );
  

  menatawork();

}


?>
