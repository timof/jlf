<?php

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
open_tag( 'html' );
open_tag( 'head' );
  $c = array( 96, 128, 96 );
  $fade = ( $session_branch ? $session_branch * 24 : 0 );
  $corporatecolor = sprintf( "%02x%02x%02x", $c[0] + $fade, $c[1] + $fade, $c[2] + $fade );
  
  echo "
    <title>$application_name $application_instance</title>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' >
    <link rel='stylesheet' type='text/css' href='code/css.css'>
    <script type='text/javascript' src='code/js.js' language='javascript'></script>
    <style type='text/css'>
      .corporatecolor {
        background-color:#$corporatecolor !important;
      }
    </style>
  ";
close_tag( 'head' );
open_tag( 'body' );

// for forking: pick branch with lowest atime:
//


open_div( 'head corporatecolor' . ( $readonly ? ' ro' : '' ) , "id='header'" );
  open_table( 'hfill' );
    if( $window_id == 'main' ) {
      open_td( 'corporatecolor left quads medskips' );
        if( $logged_in ) {
          open_span( 'quads smallskips corporatecolor', '', "
            <a class='fork' title='fork' href=\"javascript:submit_form( 'update_form', 'action', 'fork' );\">
            </a><a class='reload' id='reload_button' title='reload' href='javascript:document.forms.update_form.submit();'>
            </a>
          " );
        }
        open_span( 'corporatecolor qquad', "style='font-size:24pt;color:#ffffff;font-weight:bold;'", "$jlf_application_name $jlf_application_instance" );
      open_td( 'quads smallskips corporatecolor right' );
        if( $logged_in )
          open_span( 'quads smallskips corporatecolor floatright', '', postaction( 'text=logout...', 'login=logout' ) );
//       open_div( 'corporatecolor right' );
//         echo "&nbsp;";
//         if( ( $window_id == 'main' ) && ( $window == 'menu' ) ) {
//           open_span( 'corporatecolor' );
//             open_ul( 'corporatecolor', "id='menu' style='margin-bottom:0.5ex;'" );
//               mainmenu_header();
//             close_ul();
//           close_span();
//         }
//       close_div();
    } else {
      open_td( 'corporatecolor quads medskips left', '', "
        <a class='close' title='close' href='javascript:if(opener)opener.focus();window.close();'>
        </a><a class='print' title='print' href='javascript:window.print();'>
            <a class='fork' title='fork' href=\"javascript:submit_form( 'update_form', 'action', 'fork' );\">
        </a><a class='reload' id='reload_button' title='reload' href='javascript:document.forms.update_form.submit();'>
        </a>
      " );
      open_td( 'corporatecolor quads medskips right', '', "$jlf_application_name $jlf_application_instance: $window_id" );
    }
  close_table();
close_div();

open_div( $readonly ? 'payload,ro' : 'payload' , "id='payload'" );

?>
