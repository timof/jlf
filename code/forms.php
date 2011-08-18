<?php

////////////////////////////////////////
//
// functions to output pieces of a form:
// form_row_*():
//   - output one <tr> of a form
//   - the line will usually contain three columns: one for the label, two for the input field
//   - a different colspan for the input field can be specified, to leave space e.g. for a submission button
//
////////////////////////////////////////


// $form_defaults = array(
//   '' => array( 'cols' => 3 , 'class' => '' )
// );
// 
// function set_form_defaults( $fields ) {
//   global $form_defaults;
//   $fields = parameters_explode( $fields, 'tables' );
//   foreach( $fields as $fieldname => $opts ) {
//     switch( $key ) {
//       case 'tables':
//         $form_defaults['tables'] = $parameters_explode( $val );
//         break;
//       case 'fields':
//         // $form_defaults['fields'] = 
//   
//     }
//   }
//   if( isset( $form_defaults[''] ) ) {
//     foreach( $form_defaults as $fieldname => $opts )
//       if( $fieldname !== '' )
//         $form_defaults[ $fieldname ] = tree_merge( $form_defaults[''], $opts );
//   }
// }
// 
// function form_label( $fieldname, $opts = array() ) {
//   $opts = tree_merge( adefault( $GLOBALS['form_defaults'], $fieldname, array() ), $opts );
//   if( $opts['cols'] > 0 ) {
//     open_td();
//   }
//   $class = 'label';
//   if( adefault( $opts, 'problem', false ) )
//     $class .= ' problem';
//   if( adefault( $opts, 'modified', false ) )
//     $class .= ' modified';
//   if( adefault( $opts, 'new', false ) )
//     $class .= ' new';
//   $label = adefault( $opts, 'label', "$fieldname: " );
//   open_span( array( 'class' => $class, 'id' => 'label_'.$fieldname ) , $label );
// }
// 
// function form_input( $fieldname, $opts = array() ) {
//   $opts = tree_merge( adefault( $GLOBALS['form_defaults'], $fieldname, array() ), $opts );
//   if( $opts['cols'] > 1 ) {
//     open_td( array( 'colspan' => $opts['cols'] ) );
//   }
//   if( isset( $opts['gadget'] ) ) {
//     $opts['gadget'] ( $fieldname );
//   } else {
//     $type = adefault( $opts, 'type', 'h' );
//     if( $type[ 0 ] === '%' ) {
//       // sscanf( $type, '%%%u.%u' );
//     
//     }
//   }
// }
// 
// function form_element( $fieldname, $opts = array() ) {
//   form_label( $fieldname, $opts );
//   form_input( $fieldname, $opts );
// }
//
// function form_row_int( $label = 'number:' , $fieldname = 'number', $size = 4, $initial = 0, $icols = 2 ) {
//   $class = field_class( $fieldname );
//   open_tr();;
//     open_td();
//       open_label( $fieldname, '', $label );
//     open_td( array( 'colspan' => $icols ) );
//       open_input( $fieldname, '', int_view( $initial, $fieldname, $size ) );
// }
// 
// function form_row_text( $label = 'note:', $fieldname = 'note', $size = 60, $initial = '', $icols = 2 ) {
//   $class = field_class( $fieldname );
//   open_tr();
//     open_td();
//       open_label( $fieldname, '', $label );
//     open_td( array( 'colspan' => $icols ) );
//       open_input( $fieldname, '', string_view( $initial, $fieldname, $size ) );
// }


function selector_int( $value, $fieldname, $min, $max ) {
  $size = max( strlen( "$min" ), strlen( "$max" ) );
  open_span( 'oneline' );
    if( $value > $min ) {
      echo inlink( '!submit', array( 'class' => 'button', 'text' => ' &lt; ', 'extra_field' => $fieldname, 'extra_value' => $value - 1 ) );
    } else {
      echo alink( '#', 'class=button pressed,text= &lt; ' );
    }
    int_element( $fieldname, array( 'size' => $size, 'value' => $value ) );
    if( $value < $max ) {
      echo inlink( '!submit', array( 'class' => 'button', 'text' => ' &gt; ', 'extra_field' => $fieldname, 'extra_value' => $value + 1 ) );
    } else {
      echo alink( '#', 'class=button pressed,text= &gt; ' );
    }
  close_span();
}

function form_limits( $limits ) {
  open_div( 'center oneline,style=padding-bottom:1ex;' );
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
        echo "show ";
        echo int_element( $limits['prefix'].'limit_count', array( 'size' => 4, 'value' => $limits['limit_count'] ) );
        echo " of {$limits['count']} entries from ";
        echo int_element( $limits['prefix'].'limit_from', array( 'size' => 4, 'value' => $limits['limit_from'] ) );
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
// functions to output complete forms, maybe followed
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
        open_div( 'smallskip', "
          <label>user:</label>
          <select size='1' name='login_people_id'>
            ". html_options_people( 0, array( 'where' => " (people.uid != '' ) and ( people.authentication_methods REGEXP '[[:<:]]simple[[:>:]]' ) " ) ) ."
          </select>
          <label style='padding-left:4em;'>password:</label>
            <input type='password' size='8' name='password' value=''>
        " );
        open_div( 'smallskip right' );
          submission_button( 'action=login,text=login' );
        close_div();
      close_fieldset();
    // close_form();
  }
}



?>
