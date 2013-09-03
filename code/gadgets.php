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


/*
 * dropdowns:
 * <some selection element id=TDID class=dropdownelement>
 *   <div 'class=floatingframe'>  (invisible; needed to position the following...)
 *     <div 'class=floatingpayload dropdown'>
 *       <div 'class=dropdownheader'>  \
 *       <ul 'class=dropdownlist'>     |
 *         <li 'class=dropdownitem'>   |-- typical payload provided by select_element()
 *         <li 'class=dropdownitem'>   |
 *         ...                        /
 *     <div 'class=shadow'>
 *
*/
// dropdown_element():
// $button: content of the element which activates the dropdown
// $payload: payload of the dropdown pane (usually a list of items)
// $opts:
//
function dropdown_element( $button, $payload, $opts = array() ) {
  global $H_SQ;
  $opts = parameters_explode( $opts );

  $id = adefault( $opts, 'id', 'dropdown'.new_html_id() );

  $dropdown = html_tag( 'div'
  , array(
      'class' => 'floatingpayload dropdown'
    , 'onmouseover' => "mouseoverdropdownbox($H_SQ$id$H_SQ);"
    , 'onmouseout' => "mouseoutdropdownbox($H_SQ$id$H_SQ);"
    )
  , $payload
  );

  $frame = html_tag( 'div', "class=floatingframe,id=$id", $dropdown . html_tag( 'div', 'class=shadow', '' ) );

  $buttonclass = merge_classes( 'dropdownelement', adefault( $opts, 'buttonclass', '' ) );
  // $button = html_tag( 'span', array( 'class' => $buttonclass ), $button );

  return html_tag( 'div'
  , array(
      'class' => $buttonclass
    , 'onmouseover' => "mouseoverdropdownlink($H_SQ$id$H_SQ);"
    , 'onmouseout' => "mouseoutdropdownlink($H_SQ$id$H_SQ);"
    )
  , $frame . "$button"
  );
}

// builtin_select_element(): display a selection list by using the browser built-in <select> element
// $field and $more_opts can both be used to pass any option ($more_opts will override $field):
// 'empty_display':   what to display if no choices are available at all
// 'default_display': what to display if choices are available but none of them is currently selected
// 'selected', 'normalized', 'value' (checked in this order): the currently 'selected' option
// 'class': css class for the <select> element
//
function builtin_select_element( $field, $more_opts = array() ) {
  global $H_SQ;

  $more_opts = parameters_explode( $more_opts );
  $field = parameters_merge( $field, $more_opts );

  if( ! ( $items = adefault( $field, 'items' ) ) ) {
    return html_span( '', adefault( $field, 'empty_display', we('(selection is empty)','(Auswahl ist leer)' ) ) );
  }
  $itemformat = adefault( $field, 'itemformat', 'choice' );
  $selected = adefault( $field, array( 'selected', 'normalized', 'value' ), false );
  $default_display = adefault( $field, 'default_display', we('(please select)','(bitte wählen)') );

  $form_id = adefault( $field, 'form_id', 'update_form' );
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
  $fieldclass = adefault( $field, 'class', '' );
  $priority = adefault( $field, 'priority', 1 );

  if( $itemformat === 'uid_choice' ) {
    $tmp = array();
    foreach( $items as $key => $val ) {
      $tmp[ value2uid( $key ) ] = $val;
    }
    $items = $tmp;
    $fieldname = "UID_$fieldname";
    $selected = value2uid( $selected );
  }
  $pfieldname = "P{$priority}_{$fieldname}";

  $tmp = array();
  foreach( $items as $key => $val ) {
    if( "$val" !== '' ) {
      $tmp[ bin2hex( $key ) ] = $val;
    }
  }
  $items = $tmp;

  if( ( $selected !== false ) && ( "$selected" !== '' ) ) {
    $selected = bin2hex( $selected );
  }

  $id = 'select'.new_html_id();
  $attr = array(
    'name' => '' // don't submit unless changed
  , 'id' => $id
  , 'class' => $fieldclass
  );

  switch( $itemformat ) {
    case 'choice':
    case 'uid_choice':
      $attr['onchange'] = "submit_form( {$H_SQ}{$form_id}{$H_SQ}, {$H_SQ}{$pfieldname}={$H_SQ} + $({$H_SQ}{$id}{$H_SQ}).value );";
      break;
    case 'form_id':
      $attr['onchange'] = "submit_form( $({$H_SQ}{$id}{$H_SQ}).value )";
      break;
    case 'line':
      error( "browser_select_element(): item format 'line' not supported" );
  }
  return html_tag( 'select', $attr, html_options( $items, array( 'selected' => $selected, 'default_display' => $default_display ) ) );
}

// select_element():
// itemformat:
//   'choice': <key> => <option> pairs
//   'uid_choice': <uid> => <option> pairs
//   'form_id' <formid> => <option> pairs
//   'line': payload for <li> to be used verbatim
function select_element( $field, $more_opts = array() ) {
  global $H_SQ;

  if( $GLOBALS['activate_exploder_kludges'] ) {
    return builtin_select_element( $field, $more_opts );
  }
  $more_opts = parameters_explode( $more_opts );
  $field = parameters_merge( $field, $more_opts );

  if( ! ( $items = adefault( $field, 'items' ) ) ) {
    return html_span( '', adefault( $field, 'empty_display', we('(selection is empty)','(Auswahl ist leer)' ) ) );
  }
  $itemformat = adefault( $field, 'itemformat', 'choice' );
  $selected = adefault( $field, array( 'selected', 'normalized', 'value' ), false );
  $default_display = adefault( $field, 'default_display', we('(please select)','(bitte wählen)') );
  $selected = "$selected";

  $form_id = adefault( $field, 'form_id', 'update_form' );

  $buttonclass = adefault( $field, 'class', '' );
  $priority = adefault( $field, 'priority', 1 );
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );

  if( $itemformat === 'uid_choice' ) {
    $tmp = array();
    foreach( $items as $key => $val ) {
      $tmp[ value2uid( $key ) ] = $val;
    }
    $items = $tmp;
    if( $fieldname ) {
      $fieldname = "UID_$fieldname";
    }
    $selected = value2uid( $selected );
    $itemformat = 'choice';
  }
  if( $fieldname ) {
    $pfieldname = "P{$priority}_{$fieldname}";
  }

  $id = 'dropdown'.new_html_id();

  // compose dropdownheader, if any:
  //
  $payload = '';
  if( adefault( $field, 'search', ( count( $items ) >= 20 ) ) ) {
    $payload .= html_tag( 'div'
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
  $header = ( $payload ? html_tag( 'div', 'class=dropdownheader', $payload ) : '' );

  // compose dropdownlist:
  //
  $payload = '';
  $count = 0;
  foreach( $items as $key => $choice ) {
    $class = 'dropdownitem';
    switch( $itemformat ) {
      case 'line':
        $payload .= html_tag( 'li', "class=$class", $choice );
        break;

      case 'choice':
        $text = substr( $choice, 0, 40 );
        $jlink = inlink( '!submit', array( 'context' => 'js', $pfieldname => $key, 'form_id' => $form_id ) );
        $alink = html_alink( "javascript: $jlink", array( 'class' => 'dropdownlink href', 'text' => $text ) );
        if( ( $selected !== NULL ) && ( "$key" === "$selected" ) ) {
          $class .= ' selected';
        }
        $payload .= html_tag( 'li', "class=$class", $alink );
        break;

      case 'form_id':
        $text = substr( $choice, 0, 40 );
        $jlink = inlink( '!submit', array( 'context' => 'js', 'form_id' => $key ) );
        $alink = html_alink( "javascript: $jlink", array( 'class' => 'dropdownlink href', 'text' => $text ) );
        $payload .= html_tag( 'li', "class=$class", $alink );
        break;
    }

  }
  $list = html_tag( 'ul', 'class=dropdownlist', $payload );

  if( ! ( $display = adefault( $field, 'display' ) ) ) {
    $display = adefault( $items, $selected, $default_display );
  }

  return dropdown_element( html_span( $buttonclass, $display ), $header . $list, "id=$id" );
}

function filter_reset_button( $filters, $opts = array() ) {
  $opts = parameters_explode( $opts, 'class' );
  $class = merge_classes( 'button reset floatright', adefault( $opts, 'class', '' ) );
  $parameters = array( 'text' => 'C', 'class' => $class, 'inactive' => true, 'title' => we('reset filter','filter zurücksetzen') );
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

function download_button( $item, $formats, $common_parameters = array() /* , $opts = array() */ ) {
  $formats = parameters_explode( $formats );
  $common_parameters = parameters_explode( $common_parameters );
  // $opts = parameters_explode( $opts );
  $choices = array();
  $common_parameters['script'] = adefault( $common_parameters, 'script', $GLOBALS['script'] );
  foreach( $formats as $f => $props ) {
    if( ! $props && ! isarray( $props ) ) {
      continue;
    }
    $parameters = array( 'f' => $f, 'i' => $item );
    switch( $f ) {
      case 'csv':
      case 'jpg':
        $parameters['window'] = 'NOWINDOW';
        break;
      case 'ldif':
      case 'pdf': // force different browser window (for people with embedded viewers!)
      default:
        $parameters['window'] = 'download';
        break;
    }
    $parameters = parameters_merge( $parameters, $common_parameters );
    if( isstring( $props ) && ! isnumber( $props ) ) {
      $props = parameters_explode( $props );
    }
    if( isarray( $props ) ) {
      $parameters = parameters_merge( $parameters, $props );
    }
    $choices[ open_form( $parameters, '', 'hidden' ) ] = $f;
  }
  return select_element( array( 'default_display' => 'download...', 'items' => $choices, 'itemformat' => 'form_id' ) );
}

function selector_int( $field ) {
  $value = adefault( $field, array( 'value', 'initval', 'default' ), 0 );
  $min = adefault( $field, 'min', 0 );
  $max = adefault( $field, 'max', 0 );
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
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
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
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
  $field += array( 'items' => $choices , 'default_display' => '- ? -' );
  return select_element( $field );
}

function form_limits( $limits ) {
  global $H_SQ, $current_table;
  // debug( $limits, 'limits' );
  $pre = $limits['prefix'];
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
      // echo inlink( '', array( 'text' => 'fit', "P2_DEREF_{$pre}limit_count" => "{$pre}limit_count_fit", 'class' => 'button' ) );
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
  hidden_input( "{$pre}limit_count_fit", 'X' );
  // js_on_exit( "table_find_fit( {$H_SQ}{$current_table['id']}{$H_SQ}, {$H_SQ}{$pre}limit_count_fit{$H_SQ} );" );
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
  $tables = adefault( $filters, 'tables', 'logbook persistentvars' );
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
  $field['items'] = choices_scripts( adefault( $opts, 'filters', '' ) ) + adefault( $opts, 'items', array() );
  $field['itemformat'] = 'uid_choice';
  return select_element( $field );
}

function filter_script( $field, $opts = array() ) {
  return selector_script( $field, add_filter_default( $opts ) );
}

function choices_windows( $filters = array() ) {
  $filters = parameters_explode( $filters, 'tables' );
  $tables = adefault( $filters, 'tables', 'logbook persistentvars' );
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
  if( ! $field ) {
    $field = array( 'name' => 'window' );
  }
  $opts = parameters_explode( $opts );
  $field['uid_choices'] = choices_windows( adefault( $opts, 'filters', array() ) ) + adefault( $opts, 'uid_choices', array() );
  return select_element( $field );
}

function filter_window( $field, $opts = array() ) {
  return selector_window( $field, add_filter_default( $opts ) );
}


function choices_tables() {
  global $tables;

  $choices = array();
  foreach( $tables as $tname => $props ) {
    $choices[ $tname ] = $tname;
  }
  return $choices;
}

function selector_table( $field = NULL, $opts = array() ) {
  if( ! $field ) {
    $field = array( 'name' => 'table' );
  }
  $opts = parameters_explode( $opts );
  $field['items'] = choices_tables() + adefault( $opts, 'choices', array() );
  return select_element( $field );
}

function filter_table( $field, $opts = array() ) {
  return selector_table( $field, add_filter_default( $opts ) );
}

function selector_datetime( $field, $opts = array() ) {
  $opts = parameters_explode( $opts );
  

  menatawork();

}


?>
