<?php

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
open_tag( 'html' );
open_tag( 'head' );
  $c = array( 96, 128, 96 );
  $fade = ( $thread ? ( $thread - 1 ) * 32 : 0 );
  $corporatecolor = sprintf( "%02x%02x%02x", $c[0] + $fade, $c[1] + $fade, $c[2] + $fade );

  echo "
    <title>$jlf_application_name $jlf_application_instance</title>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' >
    <link rel='stylesheet' type='text/css' href='code/css.css'>
    <script type='text/javascript' src='code/js.js' language='javascript'></script>
    <style type='text/css'>
      .corporatecolor {
        background-color:#$corporatecolor !important;
        color:#ffffff;
      }
    </style>
  ";
close_tag( 'head' );
open_tag( 'body' );

open_div( 'head corporatecolor' . ( $readonly ? ' ro' : '' ) , "id='header'" );
  open_table( 'hfill' );

if( ( $window == 'menu' ) && ( $thread == 1 ) ) {
      open_td( 'corporatecolor left quads medskips' );
        if( $logged_in ) {
          open_span( 'quads smallskips corporatecolor', '', "
            <a class='fork' title='fork' href=\"javascript:submit_form( 'update_form', 'action', 'fork' );\">
            </a><a class='reload' id='reload_button' title='reload' href='javascript:document.forms.update_form.submit();'>
            </a>
          " );
        }
        open_span( 'corporatecolor qquad', "style='font-size:24pt;font-weight:bold;'", "$jlf_application_name $jlf_application_instance [$window/$thread]" );
      open_td( 'quads smallskips corporatecolor right' );
        if( function_exists( 'window_title' ) )
          open_span( 'qquad corporatecolor', '', window_title() );
if( $logged_in ) {
        open_span( 'quads smallskips corporatecolor floatright', '', postaction( 'text=logout...', 'login=logout' ) );
if( $script == 'menu' ) {
    open_tr();
      open_td( 'corporatecolor right', "colspan='2'" );
        open_ul( 'corporatecolor', "id='menu' style='margin-bottom:0.5ex;'" );
          mainmenu_header();
        close_ul();
}
}

} else {
      $s = "
        <a class='close' title='close' href='javascript:if(opener)opener.focus();window.close();'>
        </a><a class='print' title='print' href='javascript:window.print();'>
            <a class='fork' title='fork' href=\"javascript:submit_form( 'update_form', 'action', 'fork' );\">
        </a>";
      if( $script != 'menu' ) {
        $s .= inlink( 'menu', 'class=home,text=,img=,title=home' );
      }
      $s .= "<a class='reload' id='reload_button' title='reload' href='javascript:document.forms.update_form.submit();'>
             </a> ";
      open_td( 'corporatecolor quads smallskips left', '', "<span class='noprint'>$s</span>" );
      open_td( 'corporatecolor quads smallskips right' );
        open_div( 'corporatecolor', '', "$jlf_application_name $jlf_application_instance [$window/$thread]" );
        if( function_exists( 'window_title' ) )
          open_div( 'corporatecolor', '', window_title() );
}
  close_table();
close_div();
$header_printed = true;
flush_debug_messages();

open_div( $readonly ? 'payload,ro' : 'payload' , "id='payload'" );

?>
