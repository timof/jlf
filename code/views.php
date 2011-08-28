<?php

//////////////////
//
// views for "primitive" types:
// they will return a suitable string, not print to stdout directly!
//

function onchange_handler( $id, $auto, $fieldname = false ) {
  global $open_environments;
  if( ! $fieldname )
    $fieldname = $id;
  if( $auto ) {
    return 'submit_input('.H_SQ.$id.H_SQ.','.H_SQ.$fieldname.H_SQ.');';
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

function field_class( $fieldname ) {
  global $fields;
  return $fields[ $fieldname ]['field_class'];
}


function int_view( $num ) {
  return html_tag( 'span', 'class=int number', sprintf( '%d', $num ) );
}

function int_element( $fieldname, $opts = array() ) {
  global $fields;
  $opts = parameters_explode( $opts );
  $num = sprintf( '%d', adefault( $opts, 'value', gdefault( $fieldname, 0 ) ) );
  if( $fieldname ) {
    $size = adefault( $opts, 'size' );
    $c = field_class( $fieldname );
    $fh = '';
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
      , 'onchange' => onchange_handler( $fieldname, adefault( $opts, 'auto', 0 ) )
      )
    , false
    );
  } else {
    return int_view( $num );
  }
}

function monthday_view( $date ) {
  return html_tag( 'span', 'class=int number', sprintf( '%04u', $date ) );
}

function monthday_element( $fieldname, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $date = sprintf( '%04u', adefault( $opts, 'value', gdefault( $fieldname, 0 ) ) );
  if( $fieldname ) {
    $c = field_class( $fieldname );
    return html_tag( 'input'
    , array(
        'type' => 'text'
      , 'class' => "kbd int number $c"
      , 'size' => 4
      , 'name' => $fieldname
      , 'value' => $date
      , 'id' => "input_$fieldname"
      , 'onchange' => onchange_handler( $fieldname, adefault( $opts, 'auto', 0 ) )
      )
    , false
    );
  } else {
    return monthday_view( $date );
  }
}

function price_view( $price ) {
  return html_tag( 'span', 'class=price number', sprintf( '%.2lf', $price ) );
}

function price_element( $fieldname, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $price = sprintf( "%.2lf", adefault( $opts, 'value', gdefault( $fieldname, 0 ) ) );
  if( $fieldname ) {
    $size = adefault( $opts, 'size' );
    $c = field_class( $fieldname );
    return html_tag( 'input'
    , array(
        'type' => 'text'
      , 'class' => "kbd price number $c"
      , 'size' => $size
      , 'name' => $fieldname
      , 'value' => $price
      , 'id' => "input_$fieldname"
      , 'onchange' => onchange_handler( $fieldname, adefault( $opts, 'auto', 0 ) )
      )
    , false
    );
  } else {
    return price_view( $price );
  }
}

function string_view( $text ) {
  return html_tag( 'span', 'class=string', $text );
}



function string_element( $fieldname, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $text = adefault( $opts, 'value', gdefault( $fieldname, '' ) );
  if( $fieldname ) {
    $size = adefault( $opts, 'size' );
    $c = field_class( $fieldname );
    return html_tag( 'input'
    , array(
       'type' => 'text'
      , 'class' => "kbd string $c"
      , 'size' => $size
      , 'name' => $fieldname
      , 'value' => $text
      , 'id' => "input_$fieldname"
      , 'onchange' => onchange_handler( $fieldname, adefault( $opts, 'auto', 0 ) )
      )
    , false
    );
  } else {
    return string_view( $text );
  }
}

function textarea_view( $text, $opts = array() ) {
  $opts = parameters_explode( $opts );
  return html_tag( 'span', 'class=string', $text );
}

function textarea_element( $fieldname, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $text = adefault( $opts, 'value', gdefault( $fieldname, '' ) );
  if( $fieldname ) {
    $c = field_class( $fieldname );
    return html_tag( 'textarea'
    , array(
       'type' => 'text'
      , 'class' => "kbd string $c"
      , 'rows' => adefault( $opts, 'rows', 4 )
      , 'cols' => adefault( $opts, 'cols', 40 )
      , 'name' => $fieldname
      , 'id' => "input_$fieldname"
      , 'onchange' => onchange_handler( $fieldname, adefault( $opts, 'auto', 0 ) )
      )
    , $text
    );
  } else {
    return textarea_view( $text );
  }
}

function checkbox_view( $checked = 0, $opts = array() ) {
  $checked = ( $checked ? 'checked' : '' );
  $text = adefault( $opts, 'text', '' );
  if( ( $title = adefault( $opts, 'title', $text ) ) ) {
    $title = "title='$title'";
  }
  return html_tag( 'input', 'type=checkbox,class=checkbox,readonly=readonly'. ( $checked ? ',checked=checked' : '' ), false ) . $text;
}

function checkbox_element( $fieldname, $opts = false ) {
  $opts = parameters_explode( $opts );
  $value = adefault( $opts, 'value', gdefault( $fieldname, '' ) );
  $mask = adefault( $opts, 'mask', 1 );
  $checked = ( ( $value & $mask ) ?  'checked' : '' );
  if( $fieldname ) {
    $c = field_class( $fieldname );
    $auto = adefault( $opts, 'auto', 0 );
    if( $auto ) {
      $id = "{$fieldname}_{$mask}";  // make sure id is unique
    } else {
      $id = $fieldname;
    }
    $value = ( $value ^ $mask );
    $text = adefault( $opts, 'text', '' );
    $opts = array(
      'type' => 'checkbox'
     , 'class' => "kbd checkbox $c"
     , 'name' => $id
     , 'value' => $value
     , 'id' => "input_$id"
     , 'onchange' => onchange_handler( $id, $auto, $fieldname )
     );
    if( ( $title = adefault( $opts, 'title', $text ) ) ) {
      $opts['title'] = $title;
    }
    if( $checked ) {
      $opts['checked'] = 'checked';
    }
    return html_tag( 'input', $opts, false ) . $text;
  } else {
    return checkbox_view( $checked, $opts );
  }
}

function radiobuttons_view( $buttons ) {
  menatwork();
}

function radiobuttons_element( $fieldname, $buttons, $opts = array() ) {
  if( $fieldname ) {
    $h = onchange_handler( $fieldname, $auto );
    foreach( $buttons as $tag => $val ) {
      menatwork();
    }
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
//   * parameters 'action', 'message', extra_field', 'extra_value, 'json' can be posted via
//     $get_parameters (where 'json' is not yet fully implemented but reserved for future use)
// in either case, 'class', 'title', 'text', 'img' will determine style of the button.
// if no 'id' is specified, an id will be generated.
//
function action_button_view( $get_parameters = array(), $post_parameters = array() ) {
  global $current_form, $open_environments;

  $get_parameters = parameters_explode( $get_parameters, 'action' );
  $post_parameters = parameters_explode( $post_parameters );

  // some parameters can be posted from $get_parameters (see js function submit_form()), and as $get_parameters _only_:
  //
  foreach( array( 'action', 'message', 'extra_field', 'extra_value', 'json' ) as $name ) {
    if( isset( $post_parameters[ $name ] ) ) {
      $get_parameters[ $name ] = $post_parameters[ $name ];
      unset( $post_parameters[ $name ] );
    }
  }

  if(    $post_parameters
      || ( adefault( $get_parameters, 'form_id' ) === true )
      || isset( $get_parameters['script'] )
      || isset( $get_parameters['window'] )
      || isset( $get_parameters['thread'] )
  ) {
    $get_parameters['form_id'] = open_form( $get_parameters, $post_parameters, 'hidden' );
  } else {
    if( ! isset( $get_parameters['form_id'] ) ) {
      $get_parameters['form_id'] = ( $current_form ? $current_form['id'] : 'update_form' );
    }
  }
  if( ! isset( $get_parameters['action'] ) ) {
    $get_parameters['action'] = 'update';
  }
  if( ! isset( $get_parameters['id'] ) ) {
    $n = count( $open_environments );
    $env_id = $open_environments[ $n ]['id'];
    $get_parameters['id'] = "action_{$get_parameters['action']}_{$env_id}";
  }
  if( ! isset( $get_parameters['class'] ) ) {
    $get_parameters['class'] = 'button quads';
  }

  return inlink( '!submit', $get_parameters );
}

function submission_button( $parameters = array() ) {
  $parameters = tree_merge(
    array( 'action' => 'save', 'text' => 'save' )
  , parameters_explode( $parameters, 'class' )
  );
  echo action_button_view( $parameters );
}
function template_button( $parameters = array() ) {
  global $script;
  $parameters = tree_merge(
    array( 'action' => 'template', 'text' => 'use as template' )
  , parameters_explode( $parameters, 'class' )
  );
  echo action_button_view( $parameters );
}
function reset_button( $parameters = array() ) {
  $parameters = tree_merge(
    array( 'action' => 'reset', 'text' => 'reset' )
  , parameters_explode( $parameters, 'class' )
  );
  echo action_button_view( $parameters );
}



// function date_time_view( $datetime, $fieldname = '' ) {
//   global $mysql_now;
//   if( ! $datetime )
//     $datetime = $mysql_now;
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
//     $date = $GLOBALS['mysql_today'];
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

?>
