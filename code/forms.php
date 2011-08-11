<?php

function field_class( $tag ) {
  global $problems, $changes;
  if( isset( $problems[ $tag ] ) )
    return 'problem';
  if( isset( $changes[ $tag ] ) )
    return 'modified';
  return '';
}

////////////////////////////////////////
//
// functions to output pieces of a form:
// form_row_*():
//   - output one <tr> of a form
//   - the line will usually contain three columns: one for the label, two for the input field
//   - a different colspan for the input field can be specified, to leave space e.g. for a submission button
//
////////////////////////////////////////


function form_row_amount( $label = 'amount:' , $fieldname = 'amount', $initial = 0.0, $icols = 2 ) {
  $class = field_class( $fieldname );
  open_tr();
    open_td();
      open_label( $fieldname, '', $label );
    open_td( '', '', false, $icols );
      open_input( $fieldname, '', price_view( $initial, $fieldname ) );
}

function form_field_monthday( $value, $fieldname ) {
  global $current_form;
  need( $current_form );
  echo monthday_view( $value, $fieldname );
}

function form_field_int( $value, $fieldname, $size = 4 ) {
  global $current_form;
  need( $current_form );
  echo int_view( $value, $fieldname, $size );
}

function selector_int( $value, $fieldname, $min, $max ) {
  $size = max( strlen( "$min" ), strlen( "$max" ) );
  open_span( 'oneline' );
    if( $value > $min ) {
      echo inlink( '!submit', array( 'class' => 'button', 'text' => ' &lt; ', 'extra_field' => $fieldname, 'extra_value' => $value - 1 ) );
    } else {
      echo alink( '#', 'button pressed', ' &lt; ' );
    }
    form_field_int( $value, $fieldname, $size );
    if( $value < $max ) {
      echo inlink( '!submit', array( 'class' => 'button', 'text' => ' &gt; ', 'extra_field' => $fieldname, 'extra_value' => $value + 1 ) );
    } else {
      echo alink( '#', 'button pressed', ' &gt; ' );
    }
  close_span();
}

function form_row_int( $label = 'number:' , $fieldname = 'number', $size = 4, $initial = 0, $icols = 2 ) {
  $class = field_class( $fieldname );
  open_tr();;
    open_td();
      open_label( $fieldname, '', $label );
    open_td( '', '', false, $icols );
      open_input( $fieldname, '', int_view( $initial, $fieldname, $size ) );
}

function form_row_text( $label = 'note:', $fieldname = 'note', $size = 60, $initial = '', $icols = 2 ) {
  $class = field_class( $fieldname );
  open_tr();
    open_td();
      open_label( $fieldname, '', $label );
    open_td( '', '', false, $icols );
      open_input( $fieldname, '', string_view( $initial, $fieldname, $size ) );
}

function form_limits( $limits ) {
  open_div( 'center oneline', "style='padding-bottom:1ex;'" );
    echo inlink( '!submit', array(
      'extra_field' => $limits['prefix'].'limit_from'
    , 'extra_value' => 0
    , 'class' => ( ( $limits['limit_from'] > 0 ) ? 'button' : 'button pressed' )
    , 'text' => '[&lt;&lt;'
    ) );
    echo inlink( '!submit', array(
      'extra_field' => $limits['prefix'].'limit_from'
    , 'extra_value' => max( 0, $limits['limit_from'] - $limits['limit_count'] )
    , 'class' => ( ( $limits['limit_from'] > 0 ) ? 'button' : 'button pressed' )
    , 'text' => ' &lt; '
    ) );
    qquad();
    open_span();
      // open_form();
        echo "show ";
        echo int_view( $limits['limit_count'], $limits['prefix'].'limit_count', 4 );
        echo " of {$limits['count']} entries from ";
        echo int_view( $limits['limit_from'], $limits['prefix'].'limit_from', 4 );
      // close_form();
    close_span();
    qquad();
    echo inlink( '!submit', array(
      'extra_field' => $limits['prefix'].'limit_from'
    , 'extra_value' => $limits['limit_from'] + $limits['limit_count']
    , 'class' => ( ( $limits['limit_from'] < $limits['count'] - $limits['limit_count'] ) ? 'button' : 'button pressed' )
    , 'text' => ' &gt; '
    ) );
    echo inlink( '!submit', array(
      'extra_field' => $limits['prefix'].'limit_from'
    , 'extra_value' => max( 0, $limits['count'] - $limits['limit_count'] )
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
    // open_form( '', 'login=login' );
      hidden_input( 'login', 'login' );
      open_fieldset( 'small_form', "style='padding:2em;width:800px;'", 'Login' );
        if( "$problems" )
          echo "$problems";
        open_div( 'smallskip', '', "
          <label>user:</label>
          <select size='1' name='login_people_id'>
            ". html_options_people( 0, array( 'where' => " (people.uid != '' ) and ( people.authentication_methods REGEXP '[[:<:]]simple[[:>:]]' ) " ) ) ."
          </select>
          <label style='padding-left:4em;'>password:</label>
            <input type='password' size='8' name='password' value=''>
        " );
        open_div( 'smallskip right' );
          submission_button( false, 'OK' );
        close_div();
      close_fieldset();
    // close_form();
  }
}



?>
