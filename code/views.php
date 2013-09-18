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



// label_element(): create <label> for form field $field:
// - with css class from $field, to indicate errors or modification
// - with suitable id so the css class can be changed from js
//
function label_element( $field, $opts = array(), $payload = false ) {
  $field = parameters_explode( $field, 'cgi_name' );
  $opts = parameters_explode( $opts, 'class' );
  $class = merge_classes( adefault( $field, 'class', '' ), adefault( $opts, 'class', '' ) );
  $attr = array( 'class' => $class );
  if( ( $fieldname = adefault( $field, array( 'cgi_name', 'name' ), '' ) ) ) {
    $attr['for'] = "input_$fieldname";
    $attr['id'] = adefault( $opts, 'id', "label_$fieldname" );
  }
  if( isset( $opts['for'] ) ) {
    $attr['for'] = $opts['for'];
  } else {
    $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
    $attr['for'] = "input_$fieldname";
  }
  if( isset( $opts['id'] ) ) {
    $attr['id'] = $opts['id'];
  }
  return html_tag( 'label', $attr, $payload );
}


function int_view( $num ) {
  return html_tag( 'span', 'class=int number', sprintf( '%d', $num ) );
}

function int_element( $field, $opts = array() ) {
  $field = parameters_explode( $field, 'cgi_name' );
  $opts = parameters_explode( $opts, 'class' );
  $num = adefault( $field, 'normalized', 0 );
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
  $priority = adefault( $field, 'priority', 1 );
  if( $fieldname ) {
    $class = merge_classes( 'input int number', adefault( $field, 'class', array() ) );
    $class = merge_classes( $class, adefault( $opts, 'class', '' ) );
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
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
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

function price_element( $field, $opts = array() ) {
  $field = parameters_explode( $field );
  $opts = parameters_explode( $opts, 'class' );
  $price = sprintf( "%.2lf", adefault( $field, 'normalized', 0.0 ) );
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
  if( $fieldname ) {
    $class = merge_classes( 'input price number', adefault( $field, 'class', array() ) );
    $class = merge_classes( $class, adefault( $opts, 'class', '' ) );
    $size = adefault( $field, 'size', 8 );
    return html_tag( 'input'
    , array(
        'type' => 'text'
      , 'class' => $class
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
  $priority = $field['priority'] = adefault( $field, 'priority', 1 );
  $id = adefault( $opts, 'id', "input_$fieldname" );
  if( $fieldname ) {
    $class = merge_classes( 'input string', adefault( $field, 'class', '' ) );
    $class = merge_classes( $class, adefault( $opts, 'class', '' ) );
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
      $field['keyformat'] = 'uid_choice';
      $dropdown = select_element( $field ); // will get id="input_UID_$fieldname"
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
  $opts = parameters_explode( $opts, 'class' );
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
  if( $fieldname ) {
    $class = merge_classes( 'input file', adefault( $field, 'class', array() ) );
    $class = merge_classes( $class, adefault( $opts, 'class', '' ) );
    return html_tag( 'input'
    , array(
        'type' => 'file'
      , 'class' => $class
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

function textarea_element( $field, $opts = array() ) {
  $field = parameters_explode( $field );
  $opts = parameters_explode( $opts, 'class' );
  $text = adefault( $field, 'normalized', '' );
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
  $lines = adefault( $field, 'lines', 4 );
  $priority = adefault( $field, 'priority', 1 );
  if( $lines[ 0 ] === '+' ) {
    $lines = count( explode( "\r", $text ) ) + substr( $lines, 1 );
  }
  if( $fieldname ) {
    $class = merge_classes( 'input string', adefault( $field, 'class', array() ) );
    $class = merge_classes( $class, adefault( $opts, 'class', '' ) );
    return html_tag( 'textarea'
    , array(
        'type' => 'text'
      , 'class' => $class
      , 'rows' => $lines
      , 'cols' => adefault( $field, 'cols', 40 )
      , 'name' => "P{$priority}_{$fieldname}"
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
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
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
    return html_span( 'checkbox', $nilrep . html_tag( 'input', $opts, false ) . $text );
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
  $fieldname = adefault( $field, array( 'cgi_name', 'name' ) );
  $id = "{$fieldname}_{$value_checked}";
  $opts = array(
    'type' => 'radio'
  , 'class' => 'kbd checkbox' // _don't_ append $field['class'] --- we flag errors for set of buttons as a whole
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
  return html_span( 'checkbox', html_tag( 'input', $opts, NULL ) . $text );
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
  global $global_format;
  $links = json_decode( $l );
  switch( $global_format ) {
    case 'html':
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
    default:
      return implode( ' ', $l );
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


function photo_view( $jpeg_base64, $rights_by, $opts = array() ) {
  $opts = parameters_explode( $opts, 'style' );
  $style = adefault( $opts, 'style', '' );
  $class = adefault( $opts, 'class', 'photo' );
  $caption = adefault( $opts, 'caption', true );
  if( $caption === true ) {
    if( isnumber( $rights_by ) ) {
      $person = sql_person( $rights_by, 0 );
      if( ! $person ) {
        return '';
      }
      $text = $person['cn'];
      $caption = inlink( 'person_view', array(
        'people_id' => $person['people_id']
      , 'class' => 'href inlink'
      , 'text' => $text
      , 'title' => $text
      ) );
    } else {
      $caption = $rights_by;
    }
    $caption = we('photo: ','Bild: ') . $caption;
  }
  return html_div( $class
  , html_tag( 'img', array( 'style' => $style, 'src' => ( 'data:image/jpeg;base64,' . $jpeg_base64 ) ), NULL )
    . html_div( 'photocaption', $caption )
  );
};

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



// persistent_vars:
//
function persistent_vars_view( $filters = array(), $opts = array() ) {
  global $login_people_id;

  $opts = parameters_explode( $opts );

  $list_options = handle_list_options( adefault( $opts, 'list_options', true ), 'persistent_vars', array( 
    'nr' => 's,t'
  , 'id' => 't,s=persistentvars_id DESC'
  , 'script' => 't,s', 'window' => 't,s' , 'thread' => 't,s'
  , 'self' => 't,s', 'uid' => 't,s', 'session' => 't,s=sessions_id'
  , 'name' => 's'
  , 'value' => 't,s'
  , 'actions' => 't'
  ) );

  // if( ! ( $vars = sql_persistent_vars( array( '&&', $filters, "people_id=$login_people_id" ) ) ) ) {
  if( ! ( $vars = sql_persistent_vars( $filters, $list_options['orderby_sql'] ) ) ) {
    open_div( '', 'no matching entries' );
    return;
  }
  $count = count( $vars );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;

  open_list( $list_options );
    open_list_row('header');
      open_list_cell( 'nr' );
      open_list_cell( 'id' );
      open_list_cell( 'session' );
      open_list_cell( 'thread' );
      open_list_cell( 'script' );
      open_list_cell( 'window' );
      open_list_cell( 'self' );
      open_list_cell( 'name' );
      open_list_cell( 'value' );
      open_list_cell( 'actions' );

    foreach( $vars as $v ) {
      if( $v['nr'] < $limits['limit_from'] )
        continue;
      if( $v['nr'] > $limits['limit_to'] )
        break;
      open_list_row();
        $id = $v['persistentvars_id'];
        $sessions_id = $v['sessions_id'];
        open_list_cell( 'nr', $v['nr'], 'class=number' );
        open_list_cell( 'id', any_link( 'persistentvars', $id, "text=$id" ), 'class=number' );
        open_list_cell( 'session', any_link( 'sessions', $sessions_id, "text=$sessions_id" ), 'class=number' );
        open_list_cell( 'thread', $v['thread'], 'class=number' );
        open_list_cell( 'script', $v['script'] );
        open_list_cell( 'window', $v['window'] );
        open_list_cell( 'self', $v['self'] );
        open_list_cell( 'name', $v['name'] );
        open_list_cell( 'value', $v['value'] );
        
        $t = inlink( '!submit', array( 'class' => 'drop', 'action' => 'deletePersistentVar', 'message' => $v['persistentvars_id'] ) );
        open_list_cell( 'actions', $t );
    }
  close_list();
}

function sessions_view( $filters = array(), $opts = array() ) {

  $list_options = handle_list_options( adefault( $opts, 'list_options', true ), 'sessions', array(
    'id' => 's=sessions_id,t'
  , 'nr' => 't'
  , 'ctime' => 's,t'
  , 'people_cn' => 's,t,h=user'
  , 'login_remote_ip' => 's,t,h=IP'
  , 'logentries_count' => 't,h=log entries'
  , 'valid' => 't,s'
  , 'expired' => 't,s'
  ) );
  $sessions = sql_sessions( $filters, array(
    'orderby' => $list_options['orderby_sql']
  , 'more_joins' => adefault( $opts, 'more_joins', array() )
  ) );
  if( ! $sessions ) {
    open_div( '', 'no matching entries' );
    return;
  }
  $count = count( $sessions );
  $limits = handle_list_limits( $list_options, $count );
  $list_options['limits'] = & $limits;
  open_list( $list_options );
    open_list_row('header');
      open_list_cell('nr');
      open_list_cell('id');
      open_list_cell('ctime');
      open_list_cell('people_cn');
      open_list_cell('login_remote_ip');
      open_list_cell('logentries_count');
      open_list_cell('valid');
      open_list_cell('expired');
    foreach( $sessions as $s ) {
      if( $s['nr'] < $limits['limit_from'] )
        continue;
      if( $s['nr'] > $limits['limit_to'] )
        break;
      $id = $s['sessions_id'];
      open_list_row();
        open_list_cell( 'nr', $s['nr'], 'class=number' );
        open_list_cell( 'id', any_link( 'sessions', $id, "text=$id" ), 'class=number' );
        open_list_cell( 'ctime', $s['ctime'] );
        if( ( $p_id = $s['login_people_id'] ) ) {
          $t = any_link( 'people', $s['login_people_id'], array( 'text' => $s['people_cn'] ) );
        } else {
          $t = '(public)';
        }
        open_list_cell( 'people_cn', $t );
        open_list_cell( 'login_remote_ip', $s['login_remote_ip'] );
        open_list_cell( 'logentries_count', inlink('logbook', array( 'sessions_id' => $id, 'text' => $s['logentries_count'] ) ) );
        open_list_cell( 'valid', $s['valid'] );
        open_list_cell( 'expired', $s['expired'] );
      }
  close_list();
}


function references_view( $referent, $referent_id, $opts = array() ) {
// alternative API:     ( $references, $opts )
  global $tables;

  if( isstring( $referent ) ) {
    $opts = parameters_explode( $opts );
    $references = sql_references( $referent, $referent_id, $opts );
  } else {
    $references = $referent;
    $opts = parameters_explode( $referent_id );
  }

  if( ! $references ) {
    open_div( '', 'no references' );
    return;
  }
  open_list();
    open_list_row('header');
      open_list_cell( 'entry', 'referring entry' );
      open_list_cell( 'column', 'referring column' );
    foreach( $references as $table => $cols ) {
      foreach( $cols as $col => $rows ) {
        foreach( $rows as $id ) {
          open_list_row();
            open_list_cell( 'entry', any_link( $table, $id ), 'number' );
            open_list_cell( 'column', $col );
        }
      }
    }
  close_list();
}

function debug_value_view( $value, $comment, $facility, $object = '', $stack = '' ) {
  $s = html_tag( 'pre', 'left warn black nounderline smallskips solidbottom solidtop' );
  if( $object ) {
    $object = "[$object]";
  }
  $s .= "\n$facility $object: $comment\n";
  $s .= jlf_var_export_html( $value, 1 );
  if( $stack ) {
    $s .= "\nstack:\n" . jlf_var_export_html( $stack );
  }
  $s .= html_tag( 'pre', false );
  return $s;
}

function debug_window_view() {
  global $debug, $debug_requests, $sql_delayed_inserts;

  if( $debug & DEBUG_FLAG_JAVASCRIPT ) {
    open_div( 'debugbox,id=jsdebug', '[INIT]' );
  }
  if( $debug & DEBUG_FLAG_INSITU ) {
    if( ( $debug & DEBUG_FLAG_PROFILE ) || $debug_requests['cooked']['variables'] ) {
      open_div( 'debugbox,id=debugwindow' );
        debug( adefault( $debug_requests['raw'], 'value', '' ), "debug requests:" );
        if( $debug & DEBUG_FLAG_PROFILE ) {
          debug( count( $sql_delayed_inserts['profile'] ), 'profile entries so far:' );
        }
        foreach( $debug_requests['cooked']['variables'] as $var => $op ) {
          if( isset( $GLOBALS[ $var ] ) ) {
            echo debug_value_view( $GLOBALS[ $var ], '', 'global variable', $var );
          } else {
            open_div( 'warn', "$var: not set" );
          }
        }
      close_div();
    }
  }
}

function debug_button_view() {
  global $debug, $debug_requests;
  $field = array( 'cgi_name' => 'debug', 'auto' => 1, 'normalized' => $debug );
  if( function_exists('dropdown_element') || ( $debug & DEBUG_FLAG_DEBUGMENU ) ) {
    $items =
        html_tag( 'li', 'dropdownitem', checkbox_element( $field + array( 'mask' => DEBUG_FLAG_LAYOUT, 'text' => 'layout' ) ) )
      . html_tag( 'li', 'dropdownitem', checkbox_element( $field + array( 'mask' => DEBUG_FLAG_HTML, 'text' => 'html' ) ) )
      . html_tag( 'li', 'dropdownitem', checkbox_element( $field + array( 'mask' => DEBUG_FLAG_PROFILE, 'text' => 'profile' ) ) )
      . html_tag( 'li', 'dropdownitem', checkbox_element( $field + array( 'mask' => DEBUG_FLAG_ERRORS, 'text' => 'errors' ) ) )
      . html_tag( 'li', 'dropdownitem', checkbox_element( $field + array( 'mask' => DEBUG_FLAG_INSITU, 'text' => 'in situ' ) ) )
      . html_tag( 'li', 'dropdownitem', checkbox_element( $field + array( 'mask' => DEBUG_FLAG_JAVASCRIPT, 'text' => 'javascript' ) ) )
    ;
    $field = adefault( $debug_requests, 'raw', array( 'cgi_name' => 'debug_requests' ) );
    $field['size'] = 40;
    $items .= html_tag( 'li', 'dropdownitem smallpads', string_element( $field ) );
  
    $field_display = init_var( 'max_debug_messages_display', 'type=u3,global,default=10' );
    $field_dump = init_var( 'max_debug_messages_dump', 'type=u3,global,default=100' );
    $items .= html_tag( 'li'
    , 'dropdownitem smallpads oneline'
    , span_view( 'qquadr', 'max display: ' . int_element( $field_display ) ) . span_view( 'qquadl', 'max dump: ' . int_element( $field_dump ) )
    );

    if( function_exists('dropdown_element' ) ) {
      $p = '';
      $class = 'dropdownlist quadl';
    } else {
      $p = html_tag( 'div', '', inlink( '', 'class=close,text=close,debug=' . ( $debug & ~DEBUG_FLAG_DEBUGMENU ) ) );
      $class = 'plain';
    }
    $p .= html_tag( 'ul', "$class bigpadb", $items );
  } else {
    $p = inlink( '', 'class=button,text=debug...,debug=' . ( $debug | DEBUG_FLAG_DEBUGMENU ) );
  }
  if( function_exists('dropdown_element') ) {
    return dropdown_element( "debug... [$debug]", $p, 'buttonclass=button qquadr' );
  } else {
    return $p;
  }
}

function root_menu_view() {
  global $script;
  $items =
      html_tag( 'li', 'dropdownitem tinypads', inlink( 'anylist', 'text=tables,class=inlink qquads' ) )
    . html_tag( 'li', 'dropdownitem tinypads', inlink( 'debuglist', "text=debugger,fscript=$script,class=inlink qquads" ) )
    . html_tag( 'li', 'dropdownitem tinypads', inlink( 'profile', "text=profiler,fscript=$script,class=inlink qquads" ) )
    . html_tag( 'li', 'dropdownitem tinypads', inlink( 'sessions', 'text=sessions,class=inlink qquads' ) )
    . html_tag( 'li', 'dropdownitem tinypads', inlink( 'logbook', 'text=logbook,class=inlink qquads' ) )
    . html_tag( 'li', 'dropdownitem tinypads', inlink( 'maintenance', 'text=maintenance,class=inlink qquads' ) )
  ;
  $p = html_tag( 'ul', 'dropdownlist', $items );
  if( function_exists('dropdown_element') ) {
    return dropdown_element( 'admin...', $p, 'buttonclass=button qquadr' );
  } else {
    return $p;
  }
}


// url_view: for _external_ urls: they can meaningfully be embedded into pdf
// (while embedding of _internal_ (deep!) links generated by inlink() into a pdf is, in general, not a good idea)
//
function url_view( $url, $opts = array() ) {
  if( ! $url ) {
    return ' - ';
  }
  $opts = parameters_explode( $opts, 'class' ) ;
  $format = adefault( $opts, 'format', $GLOBALS['global_format'] );
  $text = adefault( $opts, 'text', $url );
  switch( $format ) {
    case 'html':
      $class = merge_classes( 'a', adefault( $opts, 'class', 'href outlink' ) );
      $title = adefault( $opts, 'title', we('link to ','Verweis zu '.$url) );
      return html_alink( $url, array( 'class' => $class, 'text' => $text, 'title' => $title ) );
    case 'pdf':
      // $url = tex_encode( $url );
      // $text = tex_encode( $text );
      return TEX_BS.'href'.TEX_LBR.$url.TEX_RBR.TEX_LBR.$text.TEX_RBR;
    default:
      return $text;
  }
}

function any_field_view( $payload, $field = array() ) {
  global $global_format;
  
  $field = parameters_explode( $field, 'name' );
  $fieldname = adefault( $field, 'name' );
  $validate = adefault( $field, 'validate' );
  if( ! check_utf8( $payload ) ) {
    return span_view( 'bold italic', '(binary data)' );
  } else if( preg_match( '/^([a-zA-Z0-9_]*_)?([a-zA-Z0-9]+)_id$/', $fieldname, /* & */ $v ) ) {
    if( $payload ) {
      return any_link( $v[ 2 ], $payload, "validate=$validate" );
    } else {
      return span_view( 'bold italic', 'NULL' );
    }
  } else {
    return substr( $payload, 0, 64 );
  }
}

function span_view( $classes, $payload ) {
  $bs = TEX_BS;
  $lbr = TEX_LBR;
  $rbr = TEX_RBR;
  $lrbr = TEX_LBR.TEX_RBR;
  if( is_string( $classes ) ) {
    $classes = explode( ' ', $classes );
  }
  switch( $GLOBALS['global_format'] ) {
    case 'html':
      return html_span( array( 'class' => $classes ), $payload );
    case 'csv':
      return $payload;
    case 'pdf':
      $s = $lbr;
      $e = $rbr;
      foreach( $classes as $c ) switch( $c ) {
        case 'bold':
          $s .= "{$bs}bfseries$lrbr";
          break;
        case 'italic':
          $s .= "{$bs}itshape$lrbr";
          break;
        case 'smaller':
          $s .= "{$bs}small$lrbr";
          break;
        case 'larger':
          $s .= "{$bs}large$lrbr";
          break;
        case 'red':
        case 'green':
        case 'blue':
        case 'grey':
          $s .= "$bs$c$lrbr";
          break;
        case 'tt':
          $s .= "{$bs}ttfamily$lrbr";
          break;
        case 'rm':
          $s .= "{$bs}rmfamily$lrbr";
          break;
        case 'underline':
          $s .= "{$bs}underline$lbr";
          $e .= $rbr;
          break;
        case 'quadl':
          $s .= "{$bs}quad$lrbr";
          break;
        case 'qquadl':
          $s .= "{$bs}qquad$lrbr";
          break;
        case 'quads':
          $s .= "{$bs}quad$lrbr";
        case 'quadr':
          $e .= "{$bs}quad$lrbr";
          break;
        case 'qquads':
          $s .= "{$bs}qquad$lrbr";
        case 'qquadr':
          $e .= "{$bs}qquad$lrbr";
          break;
        case 'href':
          $s .= "{$bs}blue{$bs}underline$lbr";
          $e .= $rbr;
          break;
        case 'oneline':
          $s .= "{$bs}hbox$lbr";
          $e .= $rbr;
          break;
      }
      return $s . $payload . $e;
  }
}

// header view: function to start output, and to print low-level headers depending on format; for html: everything up to </head>
//
function html_head_view( $err_msg = '' ) {
  global $initialization_steps, $jlf_application_name, $jlf_application_instance, $debug, $H_DQ, $H_LT, $H_GT, $global_format, $global_filter;

  // in case of errors, we may not be sure and just call this function - thus, check:
  if( isset( $initialization_steps['header_printed'] ) ) {
    return;
  }
  $initialization_steps['header_printed'] = true;

  if( $global_format === 'cli' ) {
    return;
  }

  // print hint for output filter - any output up to and including this line will be gobbled:
  //
  echo "\nextfilter: $global_filter\n";

  begin_deliverable( '*', 'html' );

  echo "$H_LT!DOCTYPE HTML PUBLIC $H_DQ-//W3C//DTD HTML 4.01 Transitional//EN$H_DQ$H_GT\n\n";

  if( ! isset( $initialization_steps['session_ready'] ) ) {
    //for early errors, print emergency headers:
    echo "{$H_LT}html{$H_GT}{$H_LT}head{$H_GT}{$H_LT}title{$H_GT}early error: {$err_msg}{$H_LT}/title{$H_GT}{$H_LT}/head{$H_GT}\n";
    echo "{$H_LT}body{$H_GT}";
    return;
  }

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

    echo html_tag( 'title', '', $window_title );

    $static_css = ( is_readable( "$jlf_application_name/css/css.rphp" ) ? "$jlf_application_name/css/css.rphp" : "code/css/css.rphp" );
    echo html_tag( 'link', "rel=stylesheet,type=text/css,href=$static_css", NULL );
    if( is_readable( "$jlf_application_name/dynamic_css.php" ) ) {
      require_once( "$jlf_application_name/dynamic_css.php" );
    }

    echo html_tag( 'script', 'type=text/javascript,src=alien/prototype.js,language=javascript', '' );
    $js = ( is_readable( "$jlf_application_name/js/js.rphp" ) ? "$jlf_application_name/js/js.rphp" : 'code/js/js.rphp' );
    echo html_tag( 'script', "type=text/javascript,src=$js,language=javascript", '' );

  close_tag('head');
}


?>
