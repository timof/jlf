<?php

// the htmlDefuse filter will gobble everything up to the doctype babble line:
//
if( $global_context >= CONTEXT_DIV ) {
  echo "


  ERROR: if you see this line in browser, you need to configure htmlDefuse as ExtFilter for your apache server!


  ";  // <--important: make sure the  <doctype-babble starts on first column!
}


$window_title = ( function_exists( 'window_title' ) ? window_title() : $window );
$window_subtitle = ( function_exists( 'window_subtitle' ) ? window_title() : '' );

if( $global_context >= CONTEXT_IFRAME ) {
  open_tag( 'html' );
  open_tag( 'head' );
    // seems one cannot have <script> inside <title>, so we nest it the other way round:
    //
    open_javascript( "document.write( ".H_DQ
                     . html_tag( 'title', '', "$jlf_application_name $jlf_application_instance $window_title [{$H_DQ} + window.name + {$H_DQ}]", 'nodebug' )
                     .H_DQ." );" );
 
    if( $thread > 1 ) {
      $corporatecolor = rgb_color_lighten( $css_corporate_color, ( $thread - 1 ) * 25 );
    } else {
      $corporatecolor = $css_corporate_color;
    }
    $form_color_modified = rgb_color_lighten( $css_form_color, array( 'r' => -10, 'g' => -10, 'b' => 50 ) );
    $form_color_shaded = rgb_color_lighten( $css_form_color, -10 );
    $form_color_hover = rgb_color_lighten( $css_form_color, 30 );
 
    init_var( 'css_font_size', 'global,type=U,sources=http persistent,default=11,set_scopes=session window' );
    sscanf( $css_font_size, '%2u', & $fontsize );
 
    echo html_tag( 'meta', array( 'http-equiv' => 'Content-Type', 'content' => 'text/html; charset=utf-8' ), NULL );
    echo html_tag( 'link', 'rel=stylesheet,type=text/css,href=code/css.css', NULL );
    echo html_tag( 'script', 'type=text/javascript,src=alien/prototype.js,language=javascript', '' );
    echo html_tag( 'script', 'type=text/javascript,src=code/js.js,language=javascript', '' );
    open_tag( 'style', 'type=text/css' );
      printf( "
        body, input, textarea, .defaults, table * td, table * th, table caption { font-size:%upt; }
        h3, .large { font-size:%upt; }
        h2, .larger { font-size:%upt; }
        h1, .huge { font-size:%upt; }
        .tiny { font-size:%upt; }
        .corporatecolor {
          background-color:#%s !important;
          color:#ffffff;
        }
        fieldset.small_form, td.small_form, table.oddeven /* <-- exploder needs this */ td.small_form.oddeven.even, td.popup, td.dropdown_menu {
          background-color:#%s;
        }
        table.oddeven td.small_form.oddeven.odd, th.small_form {
          background-color:#%s;
        }
        fieldset.old .kbd.modified, fieldset.old .kbd.problem.modified {
          outline:4px solid #%s;
        }
        td.dropdown_menu:hover, td.dropdown_menu.selected, legend.small_form {
          background-color:#%s;
        }
      "
      , $fontsize, $fontsize + 1, $fontsize + 2, $fontsize + 3, $fontsize - 1
      , $corporatecolor, $css_form_color, $form_color_shaded, $form_color_modified, $form_color_hover
      );
    close_tag( 'style' );
    if( is_readable( "$jlf_application_name/css.css" ) ) {
      echo html_tag( 'link', "rel=stylesheet,type=text/css,href=$jlf_application_name/css.css", NULL );
    }
  close_tag( 'head' );
  open_tag( 'body', 'class=global' );

  // update_form: every page is supposed to have one. all data posted to self will be part of this form:
  //
  open_form( 'name=update_form' );
}

if( $global_context >= CONTEXT_WINDOW ) {
  open_div( 'head corporatecolor large ' . ( $readonly ? ' ro' : '' ) . ',id=header' );
    open_table( 'hfill' );
  
  if( ( $window === 'menu' ) && ( $thread === 1 ) ) {  // main window:
  
      open_tr(); // title, logo, ...
        open_td( 'corporatecolor left quads medskips' );
          if( $login_sessions_id ) {
            open_span( 'quads smallskips corporatecolor'
            , inlink( '!submit', 'class=fork,title=fork,action=fork' ) . inlink( '!submit', 'class=reload,title=reload' )
            );
          }
          open_span( 'corporatecolor qquad,style=font-size:24pt;font-weight:bold;', "$jlf_application_name $jlf_application_instance [$window/$thread]" );
        open_td( 'quads smallskips corporatecolor right' );
          if( $window_subtitle )
            open_span( 'qquad corporatecolor', window_subtitle() );
  
        if( $logged_in ) {
          open_span( 'quads smallskips corporatecolor floatright', inlink( '!submit', 'text=logout...,login=logout' ) );
        }
  
  } else { // subwindow:
  
      open_tr();
        $s = html_tag( 'a', 'class=close,title=close,href=javascript:if(opener)opener.focus();window.close();', '' )
           . html_tag( 'a', 'class=print,title=print,href=javascript:window.print();', '' )
           . inlink( '!submit', 'class=fork,title=fork,action=fork' );
        if( $script != 'menu' )
          $s .= inlink( 'menu', 'class=home,text=,img=,title=home' );
        $s .= inlink( '!submit', 'class=reload,title=reload' );
        open_td( 'corporatecolor quads smallskips left', html_tag( 'span', 'class=noprint', $s ) );
        open_td( 'corporatecolor' );
          open_div( 'banner', $bannertext1 );
          open_div( 'banner', $bannertext2 );
        open_td( 'corporatecolor quads smallskips right' );
          open_div( 'corporatecolor', "$jlf_application_name $jlf_application_instance [$window/$thread]" );
          if( function_exists( 'window_title' ) )
            open_div( 'corporatecolor', window_title() );
  }
  
      open_tr( 'noprint' ); // menu and buttons
        open_td( 'corporatecolor right,colspan=3' );
  
          open_span( 'oneline qquads' );
            if( $fontsize > 8 ) {
              $f = $fontsize - 1;
              open_span( 'quads', inlink( '!submit', array(
                'class' => 'button', 'text' => html_tag( 'span', 'tiny', 'A' ), 'css_font_size' => $f
              , 'title' => "decrease font size to {$f}pt"
              ) ) );
            }
            if( $fontsize < 16 ) {
              $f = $fontsize + 1;
              open_span( 'quads', inlink( '!submit', array(
                'class' => 'button', 'text' => html_tag( 'span', 'large', 'A' ), 'css_font_size'=> $f
              , 'title' => "increase font size to {$f}pt"
              ) ) );
            }
            if( $show_debug_button ) {
              open_span( 'quads', inlink( '!submit', array(
                'class' => 'button', 'text' => 'D', 'debug' => ( $debug ? '0' : '1' )
              , 'title' => 'toggle debugging mode'
              ) ) );
            }
            if( $language == 'D' ) {
              open_span( 'quads', inlink( '!submit', array(
                'class' => 'button quads', 'text' => 'en', 'language' => 'E'
              , 'title' => 'switch to English language'
              ) ) );
            } else {
              open_span( 'quads', inlink( '!submit', array(
                'class' => 'button quads', 'text' => 'de', 'language' => 'D'
              , 'title' => 'auf deutsche Sprache umschalten'
              ) ) );
            }
          close_span();
  
          if( ( $script === 'menu' ) && ( $window === 'menu' ) && ( $thread === 1 ) ) {
            open_span( 'oneline,id=headmenu' );
              open_ul( 'corporatecolor menurow', "id='menu' style='margin-bottom:0.5ex;'" );
                mainmenu_header();
              close_ul();
            close_span();
          }
  
          // open_span( 'oneline,id=headbuttons' );
          //   submission_button( 'style=display:none;' );
          // close_span();
  
    close_table();
  close_div();
  // open_div( 'noprint,id=navigation' );
  //   echo "navivation:";
  // close_div();
  
  open_div( $readonly ? 'payload,ro' : 'payload' . ',id=payload' );
  open_javascript( "$({$H_SQ}payload{$H_SQ}).style.marginTop = $({$H_SQ}header{$H_SQ}).offsetHeight + {$H_SQ}px{$H_SQ};" );
}

if( $global_context >= CONTEXT_DIV )
  flush_debug_messages();

?>
