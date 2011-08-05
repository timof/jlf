<?php

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
$window_title =( function_exists( 'window_title' ) ? window_title() : $window );
$window_subtitle =( function_exists( 'window_subtitle' ) ? window_title() : '' );
open_tag( 'html' );
open_tag( 'head' );
  // seems one cannot have <script> inside <title>, so we nest it the other way round:
  //
  open_javascript( "document.write( '<title> $jlf_application_name $jlf_application_instance $window_title [' + window.name + ']</title>' ); " );

  if( $thread > 1 ) {
    $corporatecolor = rgb_color_lighten( $css_corporate_color, ( $thread - 1 ) * 25 );
  } else {
    $corporatecolor = $css_corporate_color;
  }
  $form_color_modified = rgb_color_lighten( $css_form_color, 30 );
  $form_color_shaded = rgb_color_lighten( $css_form_color, -10 );

  sscanf( $css_font_size, '%2u', & $fontsize );

  printf( "
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' >
    <link rel='stylesheet' type='text/css' href='code/css.css'>
    <script type='text/javascript' src='alien/prototype.js' language='javascript'></script>
    <script type='text/javascript' src='code/js.js' language='javascript'></script>
    <style type='text/css'>
      body, input, textarea, .defaults, table * td, table * th { font-size:%upt; }
      h3, .large { font-size:%upt; }
      h2, .larger { font-size:%upt; }
      h1, .huge { font-size:%upt; }
      .tiny { font-size:%upt; }
      .corporatecolor {
        background-color:#%s !important;
        color:#ffffff;
      }
      .small_form, fieldset.small_form, .small_form table tr td, .small_form table.oddeven tr.even td {
        background-color:#%s;
      }
      .small_form table.oddeven tr.odd td {
        background-color:#%s;
      }
      /* .small_form table tr td.modified, .small_form .modified
      , .small_form table.oddeven tr.even td.modified
      , .small_form table.oddeven tr.odd td.modified
      */
      .kbd.modified { background-color:#%s; }
    </style>
  "
  , $fontsize, $fontsize + 1, $fontsize + 2, $fontsize + 3, $fontsize - 1
  , $corporatecolor, $css_form_color, $form_color_shaded, $form_color_modified
  );
  if( is_readable( "$jlf_application_name/css.css" ) ) {
    echo "<link rel='stylesheet' type='text/css' href='$jlf_application_name/css.css'>";
  }
close_tag( 'head' );
open_tag( 'body', 'class=defaults' );

open_div( 'head corporatecolor large ' . ( $readonly ? ' ro' : '' ) , "id='header'" );
  open_table( 'hfill' );

if( ( $window === 'menu' ) && ( $thread === 1 ) ) {  // main window:

    open_tr(); // title, logo, ...
      open_td( 'corporatecolor left quads medskips' );
        if( $login_sessions_id ) {
          open_span( 'quads smallskips corporatecolor', ''
          , inlink( '!submit', 'class=fork,title=fork,action=fork' ) . inlink( '!submit', 'class=reload,title=reload' )
          );
        }
        open_span( 'corporatecolor qquad', "style='font-size:24pt;font-weight:bold;'", "$jlf_application_name $jlf_application_instance [$window/$thread]" );
      open_td( 'quads smallskips corporatecolor right' );
        if( $window_subtitle )
          open_span( 'qquad corporatecolor', '', window_subtitle() );

      if( $logged_in ) {
        open_span( 'quads smallskips corporatecolor floatright', '', inlink( '!submit', 'text=logout...,extra_field=login,extra_value=logout' ) );
      }

    open_tr(); // menu and buttons
      open_td( 'corporatecolor right', "colspan='2'" );

        if( $script === 'menu' ) {
          open_span( 'oneline', "id='headmenu'" );
            open_ul( 'corporatecolor menurow', "id='menu' style='margin-bottom:0.5ex;'" );
              mainmenu_header();
            close_ul();
          close_span();
        }

} else { // subwindow:

    open_tr();
      $s = "
        <a class='close' title='close' href='javascript:if(opener)opener.focus();window.close();'></a>
        <a class='print' title='print' href='javascript:window.print();'></a>
      " . inlink( '!submit', 'class=fork,title=fork,action=fork' );
      if( $script != 'menu' )
        $s .= inlink( 'menu', 'class=home,text=,img=,title=home' );
      $s .= inlink( '!submit', 'class=reload,title=reload' );
      open_td( 'corporatecolor quads smallskips left', '', "<span class='noprint'>$s</span>" );
      open_td( 'corporatecolor quads smallskips right' );
        open_div( 'corporatecolor', '', "$jlf_application_name $jlf_application_instance [$window/$thread]" );
        if( function_exists( 'window_title' ) )
          open_div( 'corporatecolor', '', window_title() );

    open_tr();
      open_td( 'corporatecolor right', "colspan='2'" );

}
        open_span( 'nodisplay', "id='headbuttons'" );
          open_span( 'qquads small italics', '', 'there are unsaved changes!' );
          qquad();
          submission_button( 'reset', 'reset' );
          qquad();
          submission_button( 'save', 'save' );
        close_span();

  close_table();
close_div();
$header_printed = true;

open_div( $readonly ? 'payload,ro' : 'payload' , "id='payload'" );
flush_debug_messages();
js_on_exit( "$('payload').style.marginTop = $('header').offsetHeight + 'px';" );

?>
