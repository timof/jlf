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
    foreach( $open_environments as $env ) {
      $l .= "$comma".$env['id'];
      $comma = ',';
    }
    return 'on_change('.H_SQ.$id.H_SQ.','.H_SQ.$l.H_SQ.');';
  }
}

// function field_class( $field ) {
//   global $fields;
//   $field = parameters_explode( $field, 'name' );
//   return adefault( $fields, array( array( $fieldname, 'field_class' ) ), '' );
// }
// 
// function field_raw( $fieldname, $opts = array() ) {
//   $opts = parameters_explode( $opts );
//   $fields = & $GLOBALS[ adefault( $opts, 'fields', 'fields' ) ];
//   if( isset( $fields[ $fieldname ]['raw'] ) ) {
//     return $fields[ $fieldname ]['raw'];
//   } else {
//     // men at work: remove this if no longer needed!
//     if( isset( $GLOBALS[ $fieldname ] ) )
//       return $GLOBALS[ $fieldname ];
//   }
//   return adefault( $opts, 'default', '' );
// }

function int_view( $num ) {
  return html_tag( 'span', 'class=int number', sprintf( '%d', $num ) );
}

function int_element( $field ) {
  $num = adefault( $field, 'raw', 0 );
  $fieldname = adefault( $field, 'name' );
  if( $fieldname ) {
    $size = adefault( $field, 'size', 4 );
    $c = adefault( $field, 'class', '' );
//    $fh = '';
//    if( ( $iv = adefault( $opts, 'initial_display', false ) ) !== false ) {
//      $fh = "onfocus=\"s=$('input_$fieldname');s.value = '$num';s.onfocus='true;'\"";
//      $num = $iv;
//    }
    return html_tag( 'input'
    , array(
        'type' => 'text'
      , 'class' => "kbd int number $c"
      , 'size' => $size
      , 'name' => $fieldname
      , 'value' => $num
      , 'id' => "input_$fieldname"
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
  $date = sprintf( '%04u', adefault( $field, 'raw', 0 ) );
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
  $price = sprintf( "%.2lf", adefault( $field, 'raw', 0.0 ) );
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

function string_element( $field ) {
  $text = adefault( $field, 'raw', '' );
  $fieldname = adefault( $field, 'name' );
  if( $fieldname ) {
    $size = adefault( $field, 'size' );
    $c = adefault( $field, 'class', '' );
    return html_tag( 'input'
    , array(
        'type' => 'text'
      , 'class' => "kbd string $c"
      , 'size' => $size
      , 'name' => $fieldname
      , 'value' => $text
      , 'id' => "input_$fieldname"
      , 'onchange' => onchange_handler( $fieldname, adefault( $field, 'auto', 0 ) )
      )
    , NULL
    );
  } else {
    return string_view( $text );
  }
}

function file_element( $field ) {
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
  $text = adefault( $field, 'raw', '' );
  $fieldname = adefault( $field, 'name' );
  if( $fieldname ) {
    $c = adefault( $field, 'class', '' );
    return html_tag( 'textarea'
    , array(
        'type' => 'text'
      , 'class' => "kbd string $c"
      , 'rows' => adefault( $field, 'lines', 4 )
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
  if( isset( $field['value'] ) ) {
    $value = $field['value'];
  } else {
    $value = adefault( $field, 'raw', 0 );
  }
  $mask = adefault( $field, 'mask', 1 );
  $checked = ( $value & $mask );
  $fieldname = adefault( $field, 'name' );
  if( $fieldname ) {
    $c = adefault( $field, 'class', '' );
    $auto = adefault( $field, 'auto', 0 );
    if( $auto ) {
      $id = "{$fieldname}_{$mask}";  // make sure id is unique
      $newvalue = ( $checked ? ( $value & ~$mask ) : ( $value | $mask ) );
      $nilrep = '';
      $onchange = inlink( '', array( 'context' => 'js', $fieldname => $newvalue ) );
    } else {
      $id = $fieldname;
      $newvalue = $mask;
      // force a nil report for every checkbox (to kludge around the incredibly stupid choice of the designers(?)
      // of html to encode "negatory" as <no answer at all>):
      $nilrep = html_tag( 'span', 'nodisplay', html_tag( 'input'
        , array(
            'type' => 'checkbox'
          , 'checked' => 'checked'
          , 'name' => 'nilrep[]'
          , 'value' => $fieldname
        )
        , NULL
      ) );
      $onchange = onchange_handler( $id, $auto, $fieldname );
    }
    $text = adefault( $field, 'text', '' );
    $opts = array(
       'type' => 'checkbox'
     , 'class' => "kbd checkbox $c"
     , 'name' => $id
     , 'value' => $newvalue
     , 'id' => "input_$id"
     , 'onchange' => $onchange
     );
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
  $opts = parameters_explode( $opts );
  // debug( $field, 'field' );
  // debug( $opts, 'opts' );
  $value = ( isset( $field['value'] ) ? $field['value'] : adefault( $field, 'raw', 0 ) );
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


function radiolist_view( ) {

}

function radiolist_element( ) {

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
  


// action_button_view(): generate button to submit one form: this function has two main uses:
// - if any $post_parameters are specified, or $form_id === true, or either window, script or thread are
//   specified in $get_parameters, a hidden form will be created and inserted just before </body>.
//   the main use of this is to submit a form to a different script / window, or to post extra parameters
//   * normally, try to use the update_form to submit to self (so unsaved changes will not be lost)
//   * as the hidden form is inserted at end of document, this will work even inside another form
// - otherwise, an already existing form will be submitted: either the form specified by parameter 'form_id',
//   or the $current_form, or as last resort the 'update_form' (which every document should have):
//   * parameters 'action', 'message' and 'json' can be posted via $get_parameters (where 'json' is
//   not yet fully implemented but reserved for future use)
// in either case, 'class', 'title', 'text', 'img' will determine style of the button.
// if no 'id' is specified, an id will be generated.
//
function action_button_view( $get_parameters = array(), $post_parameters = array() ) {
  global $current_form, $open_environments;

  $get_parameters = parameters_explode( $get_parameters, 'action' );
  $post_parameters = parameters_explode( $post_parameters );

  if(   ( adefault( $get_parameters, 'form_id' ) === true )
      || isset( $get_parameters['script'] )
      || isset( $get_parameters['window'] )
      || isset( $get_parameters['thread'] )
  ) {
    $get_parameters['form_id'] = open_form( $get_parameters, $post_parameters, 'hidden' );
  } else {
    $get_parameters = parameters_merge( $get_parameters, $post_parameters );
    if( ! isset( $get_parameters['form_id'] ) ) {
      $get_parameters['form_id'] = ( $current_form ? $current_form['id'] : 'update_form' );
    }
    if( ! isset( $get_parameters['action'] ) ) {
      $get_parameters['action'] = 'update';
    }
  }
  if( ( $action = adefault( $get_parameters, 'action', '' ) ) ) {
    if( ! isset( $get_parameters['id'] ) ) {
      $n = count( $open_environments );
      $env_id = $open_environments[ $n ]['id'];
      $get_parameters['id'] = "action_{$action}_{$env_id}";
    }
  }
  if( ! isset( $get_parameters['class'] ) ) {
    $get_parameters['class'] = 'button quads';
  }

  return inlink( '!submit', $get_parameters );
}

function submission_button( $parameters = array() ) {
  $parameters = tree_merge(
    array( 'action' => 'save', 'text' => we('save','speichern') )
  , parameters_explode( $parameters, 'class' )
  );
  echo action_button_view( $parameters );
}
function template_button( $parameters = array() ) {
  global $script;
  $parameters = tree_merge(
    array( 'action' => 'template', 'text' => we('use as template','als Vorlage benutzten') )
  , parameters_explode( $parameters, 'class' )
  );
  echo action_button_view( $parameters );
}
function reset_button( $parameters = array() ) {
  $parameters = tree_merge(
    array( 'action' => 'reset', 'text' => we('reset','zurücksetzen') )
  , parameters_explode( $parameters, 'class' )
  );
  echo action_button_view( $parameters );
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
    open_tr();
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
      open_tr();
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
          for( $i = 1; $i <<= 1; isset( $log_flag_text[ $i ] ) ) {
            if( $l['flags'] & $i )
              open_div( 'center', $log_flag_text[ $i ] );
          }
        open_list_cell( 'tags', $l['tags'] );
        open_list_cell( 'links', inlinks_view( $l['links'] ), 'class=left' );
        open_list_cell( 'note' );
          if( strlen( $l['note'] ) > 100 )
            $s = substr( $l['note'], 0, 100 ).'...';
          else
            $s = $l['note'];
          if( $l['stack'] )
            $s .= ' [stack]';
          echo inlink( 'logentry', array( 'class' => 'card', 'text' => $s, 'logbook_id' => $l['logbook_id'] ) );
        open_list_cell( 'aktionen' );
          echo inlink( '!submit', 'class=drop,text=,action=prune,message='. $l['logbook_id'] );
    }
  close_table();
}

// persistent_vars:
//
function persistent_vars_view( $filters = array(), $opts = array() ) {
  global $login_people_id;

  $opts = handle_list_options( $opts, 'persistent_vars', array( 
    'nr' => 's'
  , 'script' => 't,s', 'window' => 't,s' , 'thread' => 't,s'
  , 'self' => 't,s', 'uid' => 't,s', 'session' => 't,s'
  , 'name' => 's'
  , 'value' => 't,s'
  , 'actions' => 't'
  ) );

  if( ! ( $vars = sql_persistent_vars( array( '&&', $filters, "people_id=$login_people_id" ) ) ) ) {
    open_div( '', 'no matching entries' );
    return;
  }
  $count = count( $vars );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list hfill oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr();
      open_list_head( 'nr' );
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
      open_tr();
        open_list_cell( 'nr', $v['nr'], 'class=number' );
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

// header view: function to start output, and to print headers depending on format
//
$header_printed = false;

function header_view( $format = '', $err_msg = '' ) {
  global $header_printed, $jlf_application_name, $jlf_application_instance, $debug, $H_DQ;

  // in case of errors, we may not be sure and just call this function - thus, check:
  if( $header_printed ) {
    return;
  }
  if( ! $format ) {
    $format = $GLOBALS['global_format'];
  }

  echo "format: $format\n";  // hint for output filter
  $header_printed = true;

  if( ( $format !== 'html' ) || ( $GLOBALS['global_context'] < CONTEXT_IFRAME ) ) {
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

  open_tag( 'html' );
  open_tag( 'head' );
    // seems one cannot have <script> inside <title>, so we nest it the other way round:
    //
    open_javascript( 'document.write( ' . H_DQ . html_tag( 'title', '', $window_title, 'nodebug' ) . H_DQ . ' );' );
 
    if( $thread > 1 ) {
      $corporatecolor = rgb_color_lighten( $css_corporate_color, ( $thread - 1 ) * 25 );
    } else {
      $corporatecolor = $css_corporate_color;
    }
    $form_color_modified = rgb_color_lighten( $css_form_color, array( 'r' => -10, 'g' => -10, 'b' => 50 ) );
    $form_color_shaded = rgb_color_lighten( $css_form_color, -10 );
    $form_color_hover = rgb_color_lighten( $css_form_color, 30 );
 
    echo html_tag( 'meta', array( 'http-equiv' => 'Content-Type', 'content' => 'text/html; charset=utf-8' ), NULL );
    echo html_tag( 'link', 'rel=stylesheet,type=text/css,href=code/css.css', NULL );
    echo html_tag( 'script', 'type=text/javascript,src=alien/prototype.js,language=javascript', '' );
    echo html_tag( 'script', 'type=text/javascript,src=code/js.js,language=javascript', '' );
    open_tag( 'style', 'type=text/css' );
      printf( "
        body, input, textarea, .defaults, table * td, table * th, table caption { font-size:%upt; }
        h3, .large { font-size:%upt; }
        h2, .larger { font-size:%upt; }
        h1, .huge { font-size:%upt; }
        .tiny { font-size:%upt; }
        .corporatecolor {
          background-color:#%s !important;
          color:#ffffff;
        }
        fieldset.small_form, td.small_form, table.oddeven /* <-- exploder needs this */ td.small_form.oddeven.even, td.popup, td.dropdown_menu {
          background-color:#%s;
        }
        table.oddeven td.small_form.oddeven.odd, th.small_form {
          background-color:#%s;
        }
        fieldset.old .kbd.modified, fieldset.old .kbd.problem.modified {
          outline:4px solid #%s;
        }
        td.dropdown_menu:hover, td.dropdown_menu.selected, legend.small_form {
          background-color:#%s;
        }
      "
      , $font_size, $font_size + 1, $font_size + 2, $font_size + 3, $font_size - 1
      , $corporatecolor, $css_form_color, $form_color_shaded, $form_color_modified, $form_color_hover
      );
    close_tag( 'style' );
    if( is_readable( "$jlf_application_name/css.css" ) ) {
      echo html_tag( 'link', "rel=stylesheet,type=text/css,href=$jlf_application_name/css.css", NULL );
    }
  close_tag( 'head' );
  open_tag( 'body', 'class=global' );

  // update_form: every page is supposed to have one. all data posted to self will be part of this form:
  //
  open_form( 'name=update_form' );
}

?>
