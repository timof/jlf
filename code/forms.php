<?php

//////////////////////////////////////////////////////////////////
//
// functions to output complete forms, maybe followed
// by a handler function to deal with the POSTed data
//
//////////////////////////////////////////////////////////////////

// if( ! function_exists( 'form_login' ) ) {
//   menatwork();
//   thisdoesntwork!!!
//   function form_login() {
//     debug( $GLOBALS['current_form'], 'current form' );
//     hidden_input( 'l', 'login' );
//     hidden_input( 'x', 'blubb' );
//     debug( $GLOBALS['current_form'], 'current form' );
//     open_fieldset( 'class=small_form,style=padding:2em;', we('Login','Anmelden') );
//       flush_problems();
//       open_div( 'smallskip' );
//         open_span( 'label,', 'user:' );
//         open_tag( 'select', 'size=1,name=login_people_id' );
//           echo html_options_people( 0, array( 'people.uid !=' => '', 'people.authentication_methods ~=' => '[[:<:]]simple[[:>:]]' ) );
//         close_tag( 'select' );
//         open_span( 'label,', 'password:' );
//         echo html_tag( 'input', 'type=password,size=8,name=password,value=' );
//       close_div();
//       open_div( 'smallskip right' );
//         submission_button( 'text=login' );
//       close_div();
//     close_fieldset();
//   }
// }
// 
?>
