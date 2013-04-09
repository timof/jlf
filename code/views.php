<?php

//////////////////
//
// views for "primitive" types:
// they will return a suitable string, not print to stdout directly!
//

function onchange_handler( $id, $auto, $fieldname = false ) {
  global $current_form;
  global $H_SQ;
  global $open_environments;
  if( ! $fieldname )
    $fieldname = $id;
  if( $auto ) {
    $id = ( $current_form ? $current_form['id'] : 'update_form' );
    // return "submit_form( '$id' );";
    return 'submit_form('.H_SQ.$id.H_SQ.');';
  } else {
    $comma = '';
    $l = '';
//     foreach( $open_environments as $env ) {
//      $l .= "$comma".$env['id'];
//      $comma = ',';
//    }
    return 'on_change('.H_SQ.$id.H_SQ.','.H_SQ.$l.H_SQ.');';
  }
}

// function field_class( $field ) {
//   global $fields;
//   $field = parameters_explode( $field, 'name' );
//   return adefault( $fields, array( array( $fieldname, 'field_class' ) ), '' );
// }
// 

function int_view( $num ) {
  return html_tag( 'span', 'class=int number', sprintf( '%d', $num ) );
}

function int_element( $field, $opts = array() ) {
  $field = parameters_explode( $field, 'cgi_name' );
  $opts = parameters_explode( $opts, 'class' );
  $num = adefault( $field, 'normalized', 0 );
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
  $class = merge_classes( 'input int number '. adefault( $field, 'class', '' ), adefault( $opts, 'class', '' ) );
  $priority = adefault( $field, 'priority', 1 );
  if( $fieldname ) {
    $size = adefault( $field, 'size', 4 );
//    $fh = '';
//    if( ( $iv = adefault( $opts, 'initial_display', false ) ) !== false ) {
//      $fh = "onfocus=\"s=$('input_$fieldname');s.value = '$num';s.onfocus='true;'\"";
//      $num = $iv;
//    }
    return html_tag( 'input'
    , array(
        'type' => 'text'
      , 'class' => $class
      , 'size' => $size
      , 'name' => "P{$priority}_{$fieldname}"
      , 'value' => $num
      , 'id' => adefault( $opts, 'id', "input_$fieldname" )
      , 'onchange' => onchange_handler( $fieldname, adefault( $field, 'auto', 0 ) )
      )
    , NULL
    );
  } else {
    return int_view( $num );
  }
}

function monthday_view( $date ) {
  return html_tag( 'span', 'class=int number', sprintf( '%04u', $date ) );
}

function monthday_element( $field ) {
  $field = parameters_explode( $field );
  $date = sprintf( '%04u', adefault( $field, 'normalized', 0 ) );
  $fieldname = adefault( $field, 'name' );
  if( $fieldname ) {
    $c = adefault( $field, 'class', '' );
    return html_tag( 'input'
    , array(
        'type' => 'text'
      , 'class' => "kbd int number $c"
      , 'size' => 4
      , 'name' => $fieldname
      , 'value' => $date
      , 'id' => "input_$fieldname"
      , 'onchange' => onchange_handler( $fieldname, adefault( $field, 'auto', 0 ) )
      )
    , NULL
    );
  } else {
    return monthday_view( $date );
  }
}

function price_view( $price ) {
  return html_tag( 'span', 'class=price number', sprintf( '%.2lf', $price ) );
}

function price_element( $field ) {
  $field = parameters_explode( $field );
  $price = sprintf( "%.2lf", adefault( $field, 'normalized', 0.0 ) );
  $fieldname = adefault( $field, 'name' );
  if( $fieldname ) {
    $size = adefault( $field, 'size', 8 );
    $c = adefault( $field, 'class', '' );
    return html_tag( 'input'
    , array(
        'type' => 'text'
      , 'class' => "kbd price number $c"
      , 'size' => $size
      , 'name' => $fieldname
      , 'value' => $price
      , 'id' => "input_$fieldname"
      , 'onchange' => onchange_handler( $fieldname, adefault( $field, 'auto', 0 ) )
      )
    , NULL
    );
  } else {
    return price_view( $price );
  }
}

function string_view( $text ) {
  return html_tag( 'span', 'class=string', $text );
}

function string_element( $field, $opts = array() ) {
  $field = parameters_explode( $field, 'cgi_name' );
  $opts = parameters_explode( $opts, 'class' );
  $text = adefault( $field, 'normalized', '' );
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
  $class = merge_classes( 'input string '. adefault( $field, 'class', '' ), adefault( $opts, 'class', '' ) );
  $priority = $field['priority'] = adefault( $field, 'priority', 1 );
  $id = adefault( $opts, 'id', "input_$fieldname" );
  if( $fieldname ) {
    $size = adefault( $field, 'size' );
    $tag = html_tag( 'input'
    , array(
        'type' => 'text'
      , 'class' => $class
      , 'size' => $size
      , 'name' => "P{$priority}_{$fieldname}"
      , 'value' => $text
      , 'id' => ( $id ? $id : NULL ) // dont use empty string
      , 'onchange' => onchange_handler( $fieldname, adefault( $field, 'auto', 0 ) )
      )
    , NULL
    );
    if( adefault( $field, 'uid_choices' ) ) {
      $field['priority']++;
      $dropdown = dropdown_element( $field ); // will get id="input_UID_$fieldname"
      $field['priority']--;
      $tag = html_tag( 'span', 'oneline', $tag . $dropdown );
    }
    return $tag;
  } else {
    return string_view( $text );
  }
}

function file_element( $field, $opts = array() ) {
  $field = parameters_explode( $field );
  $fieldname = adefault( $field, 'name' );
  $c = adefault( $field, 'class', '' );
  if( $fieldname ) {
    return html_tag( 'input'
    , array(
        'type' => 'file'
      , 'class' => "kbd $c"
      , 'name' => $fieldname
      , 'id' => "input_$fieldname"
      , 'onchange' => onchange_handler( $fieldname, adefault( $field, 'auto', 0 ) )
      )
    , NULL
    );
  }
}

function textarea_view( $text ) {
  // make sure there is no whitespace / lf inserted:
  return html_tag( 'span', 'class=string' ) . $text .html_tag( 'span', false, false, 'nodebug' );
}

function textarea_element( $field ) {
  $field = parameters_explode( $field );
  $text = adefault( $field, 'normalized', '' );
  $fieldname = adefault( $field, 'name' );
  $lines = adefault( $field, 'lines', 4 );
  if( $lines[ 0 ] === '+' ) {
    $lines = count( explode( "\r", $text ) ) + substr( $lines, 1 );
  }
  if( $fieldname ) {
    $c = adefault( $field, 'class', '' );
    return html_tag( 'textarea'
    , array(
        'type' => 'text'
      , 'class' => "kbd string $c"
      , 'rows' => $lines
      , 'cols' => adefault( $field, 'cols', 40 )
      , 'name' => $fieldname
      , 'id' => "input_$fieldname"
      , 'onchange' => onchange_handler( $fieldname, adefault( $field, 'auto', 0 ) )
      )
    ) . $text . html_tag( 'textarea', false, false, 'nodebug' );
  } else {
    return textarea_view( $text );
  }
}

function checkbox_view( $checked = 0, $opts = array() ) {
  $text = adefault( $opts, 'text', '' );
  if( ( $title = adefault( $opts, 'title', $text ) ) ) {
    $title = "title='$title'";
  }
  return html_tag( 'input', 'type=checkbox,class=checkbox,readonly=readonly'. ( $checked ? ',checked=checked' : '' ), NULL ) . $text;
}

function checkbox_element( $field ) {
  $field = parameters_explode( $field );
  $value = adefault( $field, 'normalized', 0 );
  $mask = adefault( $field, 'mask', 1 );
  $checked = ( $value & $mask );
  $fieldname = adefault( $field, 'name' );
  $priority = adefault( $field, 'priority', 1 );
  if( $fieldname ) {
    $c = adefault( $field, 'class', '' );
    $id = "P{$priority}_OR{$mask}_{$fieldname}";  // make sure id is unique
    $opts = array(
      'type' => 'checkbox'
    , 'class' => "kbd checkbox $c"
    , 'value' => $mask
    , 'id' => "input_$id"
    );
    if( adefault( $field, 'auto', 0 ) ) {
      $newvalue = ( $checked ? ( (int)$value & ~(int)$mask ) : ( (int)$value | (int)$mask ) );
      $nilrep = '';
      $opts['name'] = 'DEVNULL';
      $opts['onchange'] = inlink( '', array( 'context' => 'js', $fieldname => $newvalue ) );
    } else {
      $newvalue = $mask;
      $opts['name'] = $id;
      $opts['onchange'] = onchange_handler( $id, 0, $fieldname );
      // force a nil report for every checkbox (to kludge around the incredibly stupid choice of the designers(?)
      // of html to encode "negatory" as <no answer at all>):
      $nilrep = html_tag( 'span', 'nodisplay', html_tag( 'input'
        , array(
            'type' => 'hidden'
          , 'name' => "P{$priority}_OR0_$fieldname"
          , 'value' => '0'
        )
        , NULL
      ) );
    }
    $text = adefault( $field, 'text', '' );
    if( ( $title = adefault( $field, 'title', $text ) ) ) {
      $opts['title'] = $title;
    }
    if( $checked ) {
      $opts['checked'] = 'checked';
    }
    return html_tag( 'span', 'checkbox', $nilrep . html_tag( 'input', $opts, false ) . $text );
  } else {
    return checkbox_view( $checked );
  }
}

function radiobutton_element( $field, $opts ) {
  $field = parameters_explode( $field );
  $opts = parameters_explode( $opts );
  // debug( $field, 'field' );
  // debug( $opts, 'opts' );
  $value = adefault( $field, 'normalized', 0 );
  $value_checked = adefault( $opts, 'value', 1 );
//   $s = "<input type='radio' class='radiooption' name='$groupname' onclick=\""
//         . inlink('', array( 'context' => 'js' , $fieldname => ( ( $$fieldname | $flags_on ) & ~ $flags_off ) ) ) .'"';
  $text = adefault( $opts, 'text', $value );
  $auto = adefault( $opts, 'auto', adefault( $field, 'auto', 0 ) );
  // debug( $auto, 'auto' );
  $fieldname = $field['name'];
  $id = "{$fieldname}_{$value_checked}";
  $opts = array(
    'type' => 'radio'
  , 'class' => 'kbd radiooption' // _don't_ append $field['class'] --- we flag errors for set of buttons as a whole
  , 'name' => $fieldname
  , 'value' => $value_checked
  , 'id' => "input_$id"
  , 'onchange' => onchange_handler( $id, $auto, $fieldname )
  , 'title' => adefault( $opts, 'title', $text )
  );
  if( "$value" === "$value_checked" )
    $opts['checked'] = 'checked';
  // debug( $value, 'value' );
  // debug( $value_checked, 'value_checked' );
  // debug( $opts['checked'], 'checked' );
  return html_tag( 'input', $opts, NULL ) . $text;
}

function radiolist_element( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, 'default_key=text' );
  $choices = adefault( $opts, 'choices', ':on:off' );
  if( isstring( $choices ) ) {
    $choices = explode( $choices[ 0 ], substr( $choices, 1 ) );
  }
  $s = '';
  foreach( $choices as $value => $label ) {
    $s .= html_tag( 'span', 'qquad', radiobutton_element( $field, array( 'value' => $value, 'text' => $label ) ) );
  }
  return $s;
}

function inlinks_view( $l, $opts = array() ) {
  $links = json_decode( $l );
  if( $links ) {
    $s = html_tag( 'ul', 'listoflinks' );
    foreach( $links as $script => $parameters ) {
      $parameters = parameters_explode( $parameters );
      if( isnumber( $script ) ) { // allow several links to same script
        $script = $parameters['script'];
        unset( $parameters['script'] );
      }
      $parameters['class'] = 'href';
      $s .= html_tag( 'li', 'link', inlink( $script, $parameters ) );
    }
    $s .= html_tag( 'ul', false );
    return $s;
  } else {
    return ' - ';
  }
}


function save_button_view( $parameters = array() ) {
  $parameters = tree_merge(
    array( 'action' => 'save', 'text' => we('save','speichern'), 'class' => 'button quads' )
  , parameters_explode( $parameters, 'class' )
  );
  return inlink( '', $parameters );
}
function template_button_view( $parameters = array() ) {
  $parameters = tree_merge(
    array( 'action' => 'template', 'text' => we('use as template','als Vorlage benutzten'), 'class' => 'button quads'  )
  , parameters_explode( $parameters, 'class' )
  );
  return inlink( '', $parameters );
}
function reset_button_view( $parameters = array() ) {
  $parameters = tree_merge(
    array( 'action' => 'reset', 'text' => we('reset','zurücksetzen'), 'class' => 'button quads' )
  , parameters_explode( $parameters, 'class' )
  );
  return inlink( '', $parameters );
}



// function date_time_view( $datetime, $fieldname = '' ) {
//   global $now_mysql;
//   if( ! $datetime )
//     $datetime = $now_mysql;
//   if( $fieldname ) {
//     sscanf( $datetime, '%u-%u-%u %u:%u', &$year, &$month, &$day, &$hour, &$minute );
//     return date_selector( $fieldname.'_day', $day, $fieldname.'_month', $month, $fieldname.'_year', $year, false )
//            .' '. time_selector( $fieldname.'_hour', $hour, $fieldname.'_minute', $minute, false );
//   } else {
//     return "<span class='datetime'>$datetime</span>";
//   }
// }
// 
// function date_view( $date = false, $fieldname = false, $auto = false ) {
//   if( ! $date )
//     $date = $GLOBALS['today_mysql'];
//   if( preg_match( '/^\d\d\d\d-\d\d-\d\d$/', $date ) ) {
//     sscanf( $date, '%u-%u-%u', &$year, &$month, &$day );
//   } else if( preg_match( '/^\d\d\d\d\d\d\d\d$/', $date ) ) {
//     $year = substr( $date, 0, 4 );
//     $month = substr( $date, 4, 2 );
//     $day = substr( $date, 6, 2 );
//   } else {
//     error( "unsupported date format: $date" );
//   }
//   if( $fieldname ) {
//     // sscanf( $date, '%u-%u-%u', &$year, &$month, &$day );
//     return date_selector( $fieldname.'_day', $day, $fieldname.'_month', $month, $fieldname.'_year', $year, false );
//   } else {
//     return "<span class='date'>$year-$month-&day</span>";
//   }
// }
// 
// function number_selector( $name, $min, $max, $selected, $format ) {
//   global $input_event_handlers;
//   $s = "<select name='$name' $input_event_handlers>";
//   for( $i = $min; $i <= $max; $i++ ) {
//     if( $i == $selected )
//     $select_str = ( $i == $selected ? 'selected' : '' );
//     $s .= "<option value='$i' $select_str>".sprintf($format,$i)."</option>\n";
//   }
//   $s .= "</select>";
//   return $s;
// }
// 
// function year_selector( $name, $selected, $from = 2010, $to = 2040 ) {
//   return number_selector( $name, $from, $to, $selected, '%04u' );
// }
// 
// function month_selector( $name, $selected ) {
//   return number_selector( $name, 1, 12, $selected, '%02u' );
// }
// 
// /**
//  * Stellt eine komplette Editiermöglichkeit für
//  * Datum und Uhrzeit zur Verfügung.
//  * Muss in ein Formluar eingebaut werden
//  * Die Elemente des Datums stehen dann zur Verfügung als
//  *   <prefix>_minute
//  *   <prefix>_stunde
//  *   <prefix>_tag
//  *   <prefix>_monat
//  *   <prefix>_jahr
//  */
// function date_time_selector( $sql_date, $prefix, $show_time=true ) {
// 	$datum = date_parse($sql_date);
// 
//   $s = "
//     <table class='inner'>
//                   <tr>
//                      <td><label>Datum:</label></td>
//                       <td style='white-space:nowrap;'>
//     ". date_selector($prefix."_tag", $datum['day'],$prefix."_monat", $datum['month'], $prefix."_jahr", $datum['year'], false) ."
//                    </td>
//        </tr>
//   ";
//   if( $show_time ) {
//     $s .= "
//          <tr>
//                    <td><label>Zeit:</label></td>
//                            <td style='white-space:nowrap;'>
//       ". time_selector($prefix."_stunde", $datum['hour'],$prefix."_minute", $datum['minute'], false ) ."
//                            </td>
//                        </tr>
//     ";
//   }
//   $s .= "</table>";
//   return $s;
// }
// 
// function date_selector($tag_feld, $tag, $monat_feld, $monat, $jahr_feld, $jahr ) {
//   $s = number_selector($tag_feld, 1, 31, $tag,"%02d",false);
//   $s .= '.';
//   $s .= number_selector($monat_feld,1, 12, $monat,"%02d",false);
//   $s .= '.';
//   $s .=  number_selector($jahr_feld, 2009, 2015, $jahr,"%04d",false);
//   return $s;
// }
// function time_selector( $stunde_feld, $stunde, $minute_feld, $minute ) {
//   $s =  number_selector($stunde_feld, 0, 23, $stunde,"%02d",false);
//   $s .= '.';
//   $s .= number_selector($minute_feld,0, 59, $minute,"%02d",false);
//   return $s;
// }
// 


// logbook:
//
function logbook_view( $filters = array(), $opts = true ) {
  global $log_level_text, $log_flag_text;

  $filters = restrict_view_filters( $filters, 'logbook' );

  $opts = handle_list_options( $opts, 'log', array( 
    'nr' => 't'
  , 'id' => 't,s=logbook_id DESC'
  , 'session' => 't,s=sessions_id'
  , 'level' => 't,s'
  , 'login_people_id' => 't,s'
  , 'login_remote_addr' => array( 't', 's' => "CONCAT( login_remote_ip, ':', login_remote_port )" )
  , 'utc' => 't,s'
  , 'thread' => 't,s', 'window' => 't,s', 'script' => 't,s'
  , 'flags' => 't'
  , 'tags' => 't,s'
  , 'links' => 't'
  , 'note' => 't,s'
  , 'actions' => 't'
  ) );

  if( ! ( $logbook = sql_logbook( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', 'no matching entries' );
    return;
  }
  $count = count( $logbook );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list hfill oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr( 'listhead' );
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'session' );
      open_list_head( 'level' );
      open_list_head( 'login_people_id' );
      open_list_head( 'login_remote_addr' );
      open_list_head( 'utc' );
      open_list_head( 'thread', html_tag( 'div', '', 'thread' ) . html_tag( 'div', 'small', 'parent' ) );
      open_list_head( 'window', html_tag( 'div', '', 'window' ) . html_tag( 'div', 'small', 'parent' ) );
      open_list_head( 'script', html_tag( 'div', '', 'script' ) . html_tag( 'div', 'small', 'parent' ) );
      open_list_head( 'flags' );
      open_list_head( 'tags' );
      open_list_head( 'links' );
      open_list_head( 'note');
      // open_list_head( 'left',"rowspan='2'", 'details' );
      open_list_head( 'actions' );

    foreach( $logbook as $l ) {
      if( $l['nr'] < $limits['limit_from'] )
        continue;
      if( $l['nr'] > $limits['limit_to'] )
        break;
      open_tr( 'listrow' );
        open_list_cell( 'nr', $l['nr'], 'class=number' );
        open_list_cell( 'id', $l['logbook_id'], 'class=number' );
        open_list_cell( 'session', $l['sessions_id'], 'class=number' );
        open_list_cell( 'level', adefault( $log_level_text, $l['level'], 'unknown' ) );
        open_list_cell( 'login_people_id'
                      , inlink( 'person_view', array( 'class' => 'href', 'text' => $l['login_people_id'], 'people_id' => $l['login_people_id'] ) )
                      , 'class=number'
        );
        open_list_cell( 'login_remote_addr', "{$l['login_remote_ip']}:{$l['login_remote_port']}", 'class=number' );
        open_list_cell( 'utc', $l['utc'], 'class=right' );
        open_list_cell( 'thread', false, 'class=center' );
          open_div( 'center', $l['thread'] );
          open_div( 'center small', $l['parent_thread'] );
        open_list_cell( 'window', false, 'class=center' );
          open_div( 'center', $l['window'] );
          open_div( 'center small', $l['parent_window'] );
        open_list_cell( 'script', false, 'class=center' );
          open_div( 'center', $l['script'] );
          open_div( 'center small', $l['parent_script'] );
        open_list_cell( 'flags' );
          for( $i = 1; isset( $log_flag_text[ $i ] ) ; $i <<= 1 ) {
            if( $l['flags'] & $i )
              open_div( 'center', $log_flag_text[ $i ] );
          }
        open_list_cell( 'tags', $l['tags'] );
        open_list_cell( 'links', inlinks_view( $l['links'] ), 'class=left' );
        open_list_cell( 'note' );
          if( strlen( $l['note'] ) > 100 ) {
            $s = substr( $l['note'], 0, 100 ).'...';
          } else {
            $s = $l['note'];
          }
          if( $l['stack'] ) {
            $s .= html_tag( 'quad span', 'underline bold', '[stack]' );
          }
          echo inlink( 'logentry', array( 'class' => 'card', 'text' => $s, 'logbook_id' => $l['logbook_id'] ) );
        open_list_cell( 'aktionen' );
          echo inlink( '!submit', 'class=drop,text=,action=deleteLogentry,confirm=are you sure?,message='. $l['logbook_id'] );
    }
  close_table();
}

// persistent_vars:
//
function persistent_vars_view( $filters = array(), $opts = array() ) {
  global $login_people_id;

  $opts = handle_list_options( $opts, 'persistent_vars', array( 
    'nr' => 's'
  , 'id' => 't,s=persistent_vars_id DESC'
  , 'script' => 't,s', 'window' => 't,s' , 'thread' => 't,s'
  , 'self' => 't,s', 'uid' => 't,s', 'session' => 't,s=sessions_id'
  , 'name' => 's'
  , 'value' => 't,s'
  , 'actions' => 't'
  ) );

  // if( ! ( $vars = sql_persistent_vars( array( '&&', $filters, "people_id=$login_people_id" ) ) ) ) {
  if( ! ( $vars = sql_persistent_vars( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', 'no matching entries' );
    return;
  }
  $count = count( $vars );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list hfill oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr( 'listhead' );
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'session' );
      open_list_head( 'thread' );
      open_list_head( 'script' );
      open_list_head( 'window' );
      open_list_head( 'self' );
      open_list_head( 'name' );
      open_list_head( 'value' );
      open_list_head( 'actions' );

    foreach( $vars as $v ) {
      if( $v['nr'] < $limits['limit_from'] )
        continue;
      if( $v['nr'] > $limits['limit_to'] )
        break;
      open_tr( 'listrow' );
        open_list_cell( 'nr', $v['nr'], 'class=number' );
        open_list_cell( 'id', $v['persistent_vars_id'], 'class=number' );
        open_list_cell( 'session', $v['sessions_id'], 'class=number' );
        open_list_cell( 'thread', $v['thread'], 'class=number' );
        open_list_cell( 'script', $v['script'] );
        open_list_cell( 'window', $v['window'] );
        open_list_cell( 'self', $v['self'] );
        open_list_cell( 'name', $v['name'] );
        open_list_cell( 'value', $v['value'] );
        open_list_cell( 'actions' );
          echo inlink( '!submit', array( 'class' => 'drop', 'action' => 'deletePersistentVar', 'message' => $v['persistent_vars_id'] ) );
    }
  close_table();
}

// header view: function to start output, and to print low-level headers depending on format
//
function header_view( $format = '', $err_msg = '' ) {
  global $initialization_steps, $jlf_application_name, $jlf_application_instance, $debug, $H_DQ, $H_LT, $H_GT, $global_format, $global_context;

  // in case of errors, we may not be sure and just call this function - thus, check:
  if( isset( $initialization_steps['header_printed'] ) ) {
    return;
  }
  if( ! $format ) {
    $format = ( isset( $global_format ) ? $global_format : 'html' );
  }

  $initialization_steps['header_printed'] = true;
  if( $format === 'cli' ) {
    return;
  }

  // print hint for output filter - any output up to and including this line will be gobbled:
  //
  switch( $global_format ) {
    case 'html':
      echo "\nextfilter: html\n";
      break;
    default:
      // other format: suppress output except selected lines:
      echo "\nextfilter: divert\n";
      break;
  }

  if( ( $format !== 'html' ) || ( $global_context < CONTEXT_IFRAME ) ) {
    return;
  }
  echo "$H_LT!DOCTYPE HTML PUBLIC $H_DQ-//W3C//DTD HTML 4.01 Transitional//EN$H_DQ$H_GT\n\n";

  if( ! isset( $initialization_steps['session_ready'] ) ) {
    //for early errors, print emergency headers:
    echo "{$H_LT}html{$H_GT}{$H_LT}head{$H_GT}{$H_LT}title{$H_GT}early error: {$err_msg}{$H_LT}/title{$H_GT}{$H_LT}/head{$H_GT}\n";
    echo "{$H_LT}body{$H_GT}"; // {$err_msg}{$H_LT}/body{$H_GT}\n";
    return;
  }

  $font_size = adefault( $GLOBALS, 'font_size', 11 );
  $css_corporate_color = adefault( $GLOBALS, 'css_corporate_color', 'f02020' );
  $css_form_color = adefault( $GLOBALS, 'css_form_color', 'e0e0e0' );
  $thread = adefault( $GLOBALS, 'thread', 1 );
  $window = adefault( $GLOBALS, 'window', '(unknown)' );

  if( $err_msg ) {
    $window_title = $err_msg;
  } else {
    $window_title = ( function_exists( 'window_title' ) ? window_title() : $GLOBALS['window'] );
  }
  $window_title = "$jlf_application_name $jlf_application_instance " . $window_title;
  if( $debug ) {
    $window_title .= " [{$H_DQ} + window.name + {$H_DQ}] ";
  }

  $window_subtitle = ( function_exists( 'window_subtitle' ) ? window_title() : '' );

  open_tag('html');
  open_tag('head');

    // seems one cannot have <script> inside <title>, so we nest it the other way round:
    // open_javascript( 'document.write( ' . H_DQ . html_tag( 'title', '', $window_title, 'nodebug' ) . H_DQ . ' );' );
    echo html_tag( 'title', '', $window_title );

    if( $thread > 1 ) {
      $corporatecolor = rgb_color_lighten( $css_corporate_color, ( $thread - 1 ) * 25 );
    } else {
      $corporatecolor = $css_corporate_color;
    }
    $form_color_modified = rgb_color_lighten( $css_form_color, array( 'r' => -10, 'g' => -10, 'b' => 50 ) );
    $form_color_shaded = rgb_color_lighten( $css_form_color, -10 );
    $form_color_shadedd = rgb_color_lighten( $css_form_color, -20 );
    $form_color_lighter = rgb_color_lighten( $css_form_color, 20 );
    $form_color_hover = rgb_color_lighten( $css_form_color, 30 );

    echo html_tag( 'meta', array( 'http-equiv' => 'Content-Type', 'content' => 'text/html; charset=utf-8' ), NULL );
    echo html_tag( 'link', 'rel=stylesheet,type=text/css,href=code/css.css', NULL );
    if( is_readable( "$jlf_application_name/css.css" ) ) {
      echo html_tag( 'link', "rel=stylesheet,type=text/css,href=$jlf_application_name/css.css", NULL );
    }
    echo html_tag( 'script', 'type=text/javascript,src=alien/prototype.js,language=javascript', '' );
    echo html_tag( 'script', 'type=text/javascript,src=code/js.js,language=javascript', '' );
    if( is_readable( "$jlf_application_name/js.js" ) ) {
      echo html_tag( 'script', "type=text/javascript,src=$jlf_application_name/js.js,language=javascript", '' );
    }
    open_tag( 'style', 'type=text/css' );
      printf( "
        body, input, textarea, .defaults, td, th, table $H_GT caption { font-size:%upt; }
        h3, .large { font-size:%upt; }
        h2, .larger { font-size:%upt; }
        h1, .huge { font-size:%upt; }
        .tiny { font-size:%upt; }
        .corporatecolor, .table.corporatecolor $H_GT .tbody $H_GT .tr $H_GT .td {
          background-color:#%s !important;
          color:#ffffff;
        }
        .formcolor, fieldset, .menu .th, .menu .td.th, .menu .table $H_GT .caption, fieldset fieldset $H_GT legend
        , fieldset.table $H_GT .tbody $H_GT .tr $H_GT .td
        , fieldset table.list $H_GT tbody $H_GT tr.even $H_GT td
        , td.popup, td.dropdown_menu
        , fieldset caption .button
        {
          background-color:#%s;
        }
        .formcolor.shaded, fieldset table.list $H_GT tbody $H_GT tr.odd $H_GT td, fieldset table.list $H_GT caption
        , fieldset caption .button.inactive, fieldset caption .button.inactive:hover {
          background-color:#%s;
        }
        .formcolor.shadedd, fieldset table.list $H_GT * $H_GT tr $H_GT th {
          background-color:#%s;
        }
        .formcolor.lighter, .menu, .menu .td
        , fieldset caption .button.pressed, fieldset caption .button:hover {
          background-color:#%s;
        }
        fieldset.old .kbd.modified, fieldset.old .kbd.problem.modified {
          outline:4px solid #%s;
        }
        td.dropdown_menu:hover, td.dropdown_menu.selected {
          background-color:#%s;
        }
      "
      , $font_size, $font_size + 1, $font_size + 2, $font_size + 3, $font_size - 1
      , $corporatecolor, $css_form_color, $form_color_shaded, $form_color_shadedd, $form_color_lighter, $form_color_modified, $form_color_hover
      );
    close_tag( 'style' );
    if( is_readable( "$jlf_application_name/css.css" ) ) {
      echo html_tag( 'link', "rel=stylesheet,type=text/css,href=$jlf_application_name/css.css", NULL );
    }
  close_tag('head');
  open_tag( 'body', 'theBody,onclick=window.focus();' );

  open_div( 'id=flashmessage', ' ' ); // to be filled from js

  open_div( 'floatingframe popup,id=alertpopup' );
    open_div( 'floatingpayload popup' );
      open_div( 'center qquads bigskips,id=alertpopuptext', '(text to go here)' );
      open_div( 'center medskipb', html_alink( 'javascript:hide_popup();', 'class=quads button,text=Ok' ) );
    close_div();
    open_div( 'shadow', '' );
  close_div();

  // update_form: every page is supposed to have one. all data posted to self will be part of this form:
  //
  open_form( 'name=update_form' );
}

?>
