<?php

function problem_class( $tag ) {
  global $problems;
  if( ! is_array( $problems ) )
    return '';
  return in_array( $tag, $problems ) ? 'problem' : '';
}

////////////////////////////////////////
//
// functions to output pieces of a form:
// form_row_*():
//   - output one <tr> of a form
//   - the line will usually contain two columns: one for the label, one for the input field
//   - the last (second) column will not be closed; so e.g. a submission_button() can be appended
//
////////////////////////////////////////


function form_row_amount( $label = 'amount:' , $fieldname = 'amount', $initial = 0.0 ) {
  open_tr();
    open_td( 'label '.problem_class( $fieldname ), '', $label );
    open_td( 'kbd' ); echo price_view( $initial, $fieldname );
}

function form_field_monthday( $value, $fieldname ) {
  global $current_form;
  $need_form = ( ! $current_form );
  if( $need_form ) {
    open_span();
    open_form();
  }
  echo monthday_view( $value, $fieldname );
  if( $need_form ) {
    close_form();
    close_span();
  }
}

function form_field_int( $value, $fieldname, $size = 4 ) {
  global $current_form;
  $need_form = ( ! $current_form );
  if( $need_form ) {
    open_span();
    open_form();
  }
  echo int_view( $value, $fieldname, $size );
  if( $need_form ) {
    close_form();
    close_span();
  }
}

function selector_int( $value, $fieldname, $min, $max ) {
  $size = max( strlen( "$min" ), strlen( "$max" ) );
  open_span( 'oneline' );
    if( $value > $min ) {
      echo inlink( '!submit', array( 'class' => 'button', 'text' => ' &lt; ', 'extra_field' => $fieldname, 'extra_value' => $value - 1 ) );
    } else {
      echo alink( '#', 'button_inactive', ' &lt; ' );
    }
    form_field_int( $value, $fieldname, $size );
    if( $value < $max ) {
      echo inlink( '!submit', array( 'class' => 'button', 'text' => ' &gt; ', 'extra_field' => $fieldname, 'extra_value' => $value + 1 ) );
    } else {
      echo alink( '#', 'button_inactive', ' &gt; ' );
    }
  close_span();
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

function form_limits( $limits ) {
  open_div( 'center oneline', "style='padding-bottom:1ex;'" );
    echo inlink( 'self', array(
      $limits['prefix'].'limit_from' => 0
    , 'class' => ( ( $limits['limit_from'] > 0 ) ? 'button' : 'button pressed' )
    , 'text' => '[&lt;&lt;'
    ) );
    echo inlink( 'self', array(
      $limits['prefix'].'limit_from' => max( 0, $limits['limit_from'] - $limits['limit_count'] )
    , 'class' => ( ( $limits['limit_from'] > 0 ) ? 'button' : 'button pressed' )
    , 'text' => ' &lt; '
    ) );
    qquad();
    open_span();
      open_form();
        echo "zeige ";
        echo int_view( $limits['limit_count'], $limits['prefix'].'limit_count', 4 );
        echo " von {$limits['count']} Eintraegen ab ";
        echo int_view( $limits['limit_from'], $limits['prefix'].'limit_from', 4 );
      close_form();
    close_span();
    qquad();
    echo inlink( 'self', array(
      $limits['prefix'].'limit_from' => $limits['limit_from'] + $limits['limit_count']
    , 'class' => ( ( $limits['limit_from'] < $limits['count'] - $limits['limit_count'] ) ? 'button' : 'button pressed' )
    , 'text' => ' &gt; '
    ) );
    echo inlink( 'self', array(
      $limits['prefix'].'limit_from' => max( 0, $limits['count'] - $limits['limit_count'] )
    , 'class' => ( ( $limits['limit_from'] < $limits['count'] - $limits['limit_count'] ) ? 'button' : 'button pressed' )
    , 'text' => '&gt;&gt;]'
    ) );
  close_div();
}


//////////////////////////////////////////////////////////////////
//
// functions to output complete forms, usually followed
// by a handler function to deal with the POSTed data
//
//////////////////////////////////////////////////////////////////


if( ! function_exists( 'form_login' ) ) {
  function form_login() {
    global $problems;
    open_form( '', 'login=login' );
      open_fieldset( 'small_form', "style='padding:2em;width:800px;'", 'Login' );
        if( "$problems" )
          echo "$problems";
        open_div( 'newfield', '', "
          <label>user:</label>
          <select size='1' name='login_people_id'>
            ". html_options_people( 0, array( 'where' => " (people.uid != '' ) and ( people.authentication_methods REGEXP '[[:<:]]simple[[:>:]]' ) " ) ) ."
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
