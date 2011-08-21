<?php

//////////////////////////////////////////////////////////////////
//
// functions to output complete forms, maybe followed
// by a handler function to deal with the POSTed data
//
//////////////////////////////////////////////////////////////////

if( ! function_exists( 'form_login' ) ) {
  function form_login() {
    global $problems;
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
  }
}

?>
