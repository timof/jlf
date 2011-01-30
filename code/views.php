<?php

function number_selector($name, $min, $max, $selected, $format, $to_stdout = true ){
  global $input_event_handlers;
  $s = "<select name='$name' $input_event_handlers>";
  for( $i = $min; $i <= $max; $i++ ) {
    if( $i == $selected )
    $select_str = ( $i == $selected ? 'selected' : '' );
    $s .= "<option value='$i' $select_str>".sprintf($format,$i)."</option>\n";
  }
  $s .= "</select>";
  if( $to_stdout )
    echo $s;
  return $s;
}

function year_selector( $name, $selected, $to_stdout = true ) {
  return number_selector( $name, 2010, 2040, $selected, '%04u', $to_stdout );
}

function month_selector( $name, $selected, $to_stdout = true ) {
  return number_selector( $name, 1, 12, $selected, '%02u', $to_stdout );
}

/**
 * Stellt eine komplette Editiermöglichkeit für
 * Datum und Uhrzeit zur Verfügung.
 * Muss in ein Formluar eingebaut werden
 * Die Elemente des Datums stehen dann zur Verfügung als
 *   <prefix>_minute
 *   <prefix>_stunde
 *   <prefix>_tag
 *   <prefix>_monat
 *   <prefix>_jahr
 */
function date_time_selector( $sql_date, $prefix, $show_time=true, $to_stdout = true ) {
	$datum = date_parse($sql_date);

  $s = "
    <table class='inner'>
                  <tr>
                     <td><label>Datum:</label></td>
                      <td style='white-space:nowrap;'>
    ". date_selector($prefix."_tag", $datum['day'],$prefix."_monat", $datum['month'], $prefix."_jahr", $datum['year'], false) ."
                   </td>
       </tr>
  ";
  if( $show_time ) {
    $s .= "
         <tr>
                   <td><label>Zeit:</label></td>
                           <td style='white-space:nowrap;'>
      ". time_selector($prefix."_stunde", $datum['hour'],$prefix."_minute", $datum['minute'], false ) ."
                           </td>
                       </tr>
    ";
  }
  $s .= "</table>";
  if( $to_stdout )
    echo $s;
  return $s;
}

function date_selector($tag_feld, $tag, $monat_feld, $monat, $jahr_feld, $jahr, $to_stdout = true ){
  $s = number_selector($tag_feld, 1, 31, $tag,"%02d",false);
  $s .= '.';
  $s .= number_selector($monat_feld,1, 12, $monat,"%02d",false);
  $s .= '.';
  $s .=  number_selector($jahr_feld, 2009, 2015, $jahr,"%04d",false);
  if( $to_stdout )
    echo $s;
  return $s;
}
function time_selector($stunde_feld, $stunde, $minute_feld, $minute, $to_stdout = true ){
  $s =  number_selector($stunde_feld, 0, 23, $stunde,"%02d",false);
  $s .= '.';
  $s .= number_selector($minute_feld,0, 59, $minute,"%02d",false);
  if( $to_stdout )
    echo $s;
  return $s;
}

//////////////////
//
// views for "primitive" types:
// they will return a suitable string, not print to stdout directly!
//

function int_view( $num, $fieldname = false, $size = 6 ) {
  global $input_event_handlers;
  $num = sprintf( "%d", $num );
  if( $fieldname ) {
    return "<input type='text' class='int number' size='$size' name='$fieldname' value='$num' $input_event_handlers>";
  } else {
    return "<span class='int number'>$num</span>";
  }
}

function monthday_view( $date, $fieldname = false ) {
  global $input_event_handlers;
  $date = sprintf( "%04u", $date );
  if( $fieldname ) {
    return "<input type='text' class='int number' size='4' name='$fieldname' value='$date' $input_event_handlers>";
  } else {
    return "<span class='int number'>$date</span>";
  }
}

function price_view( $price, $fieldname = false, $size = 8 ) {
  global $input_event_handlers;
  $price = sprintf( "%.2lf", $price );
  if( $fieldname )
    return "<input type='text' class='price number' size='$size' name='$fieldname' value='$price' $input_event_handlers>";
  else
    return "<span class='price number'>$price</span>";
}

function saldo_view( $seite, $saldo ) {
  $red = '';
  if( $saldo < 0 ) {
    $red = 'red';
    $saldo = -$saldo;
  }
  switch( $seite ) {
    case 'A':
      $s = ( $red ? 'H' : 'S' );
      break;
    case 'P':
      $s = ( $red ? 'S' : 'H' );
      break;
  }
  return "<span class='price number $red'>".sprintf( '%.02lf', $saldo )." $s</span>";
}


function string_view( $text, $fieldname = false, $length = 20, $attr = '' ) {
  global $input_event_handlers;
  if( $fieldname )
    return "<input type='text' class='string' size='$length' name='$fieldname' value='$text' $attr $input_event_handlers>";
  else
    return "<span class='string'>$text</span>";
}

function date_time_view( $datetime, $fieldname = '' ) {
  global $mysqljetzt;
  if( ! $datetime )
    $datetime = $mysqljetzt;
  if( $fieldname ) {
    sscanf( $datetime, '%u-%u-%u %u:%u', &$year, &$month, &$day, &$hour, &$minute );
    return date_selector( $fieldname.'_day', $day, $fieldname.'_month', $month, $fieldname.'_year', $year, false )
           .' '. time_selector( $fieldname.'_hour', $hour, $fieldname.'_minute', $minute, false );
  } else {
    return "<span class='datetime'>$datetime</span>";
  }
}

function date_view( $date, $fieldname = '' ) {
  global $mysqlheute;
  if( ! $date )
    $date = $mysqlheute;
  if( preg_match( '/^\d\d\d\d-\d\d-\d\d$/', $date ) ) {
    sscanf( $date, '%u-%u-%u', &$year, &$month, &$day );
  } else if( preg_match( '/^\d\d\d\d\d\d\d\d$/', $date ) ) {
    $year = substr( $date, 0, 4 );
    $month = substr( $date, 4, 2 );
    $day = substr( $date, 6, 2 );
  } else {
    error( "unsupported date format: $date" );
  }
  return int_view( sprintf( '%04u%02u%02u', $year, $month, $day ), $fieldname, 8 );
  // if( $fieldname ) {
  //  sscanf( $date, '%u-%u-%u', &$year, &$month, &$day );
  //  // return date_selector( $fieldname.'_day', $day, $fieldname.'_month', $month, $fieldname.'_year', $year, false );
  // } else {
  //  return "<span class='date'>$year$month&day</span>";
  // }
}


?>
