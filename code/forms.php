<?php

function problem_class( $tag ) {
  global $problems;
  if( ! is_array( $problems ) )
    return '';
  return in_array( $tag, $problems ) ? 'problem' : '';
}

////////////////////////////////////////
//
// functions to output one row of a form
//
// - the line will usually contain two columns: one for the label, one for the input field
// - if a $fieldname is alread part of $self_fields (ie, defining part of the current view), the value
//   will just be printed and cannot be modified (only applies to types that can be in $self_fields)
// - the last (second) column will not be closed; so e.g. a submission_button() can be appended
//
////////////////////////////////////////


function form_row_date( $label, $fieldname = 'date', $initial = 0 ) {
  $year = self_field( $fieldname.'_year' );
  $month = self_field( $fieldname.'_month' );
  $day = self_field( $fieldname.'_day' );
  if( ($year !== NULL) and ($day !== NULL) and ($month !== NULL) ) {
    $date = "$year-$month-$day";
    $fieldname = false;
  } else {
    $date = $initial;
  }
  open_tr();
    open_td( 'label', '', $label );
    open_td( 'kbd oneline' ); echo date_view( $date, $fieldname );
}

function form_row_date_time( $label, $fieldname = 'date_time', $initial = 0 ) {
  $year = self_field( $fieldname.'_year' );
  $month = self_field( $fieldname.'_month' );
  $day = self_field( $fieldname.'_day' );
  $hour = self_field( $fieldname.'_hour' );
  $minute = self_field( $fieldname.'_minute' );
  if( ($year !== NULL) and ($day !== NULL) and ($month !== NULL) and ($hour !== NULL) and ($minute !== NULL) ) {
    $datetime = "$year-$month-$day $hour:$minute";
    $fieldname = false;
  } else {
    $datetime = $initial;
  }
  open_tr();
    open_td( 'label', '', $label );
    open_td( 'kbd' ); echo date_time_view( $datetime, $fieldname );
}

function form_row_amount( $label = 'amount:' , $fieldname = 'amount', $initial = 0.0 ) {
  open_tr();
    open_td( 'label '.problem_class( $fieldname ), '', $label );
    open_td( 'kbd' ); echo price_view( $initial, $fieldname );
}

function form_row_int( $label = 'number:' , $fieldname = 'number', $size = 4, $initial = 0 ) {
  open_tr();
    open_td( 'label '.problem_class( $fieldname ), '', $label );
    open_td( 'kbd' ); echo int_view( $initial, $fieldname, $size );
}

function form_row_text( $label = 'note:', $fieldname = 'note', $size = 60, $initial = '' ) {
  open_tr();
    open_td( 'label '.problem_class( $fieldname ), '', $label );
    open_td( 'kbd' ); echo string_view( $initial, $fieldname, $size );
}


//////////////////////////////////////////////////////////////////
//
// functions to output complete forms, usually followed
// by a handler function to deal with the POSTed data
//
//////////////////////////////////////////////////////////////////


if( ! function_exists( 'form_login' ) ) {
  function form_login() {
    open_form( '', 'login=login' );
      open_fieldset( 'small_form', "style='padding:2em;width:800px;'", 'Login' );
        if( "$problems" )
          echo "$problems";
        open_div( 'newfield', '', "
          <label>user:</label>
          <select size='1' name='login_people_id'>
            ". html_options_people( 0, "(people.uid != '' ) and ( people.authentication_methods REGEXP '[[:<:]]simple[[:>:]]')" ) ."
          </select>
          <label style='padding-left:4em;'>password:</label>
            <input type='password' size='8' name='password' value=''>
        " );
        open_div( 'newfield right' );
          submission_button( false, 'OK' );
        close_div();
      close_fieldset();
    close_form();
  }
}



?>
