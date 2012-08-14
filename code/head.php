<?php

$css_font_size = init_var( 'css_font_size', 'type=U2,sources=http persistent,default=10,set_scopes=session window' );
$font_size = $css_font_size['value'];
unset( $css_font_size );

header_view();

if( $global_format !== 'html' ) {
  // no header (yet) for formats other than html:
  return;
}


if( $global_context >= CONTEXT_WINDOW ) {
  open_div( 'corporatecolor large ' . ( $readonly ? ' ro' : '' ) . ',id=theHeader' );
    open_table( 'hfill' );

  if( ( $window === 'menu' ) && ( $thread === 1 ) ) {  // main window:

      open_tr(); // title, logo, ...
        open_td( 'corporatecolor left quads medskips' );
          if( $login_sessions_id ) {
            open_span( 'quads smallskips corporatecolor'
            , inlink( '!submit', 'class=fork,title=fork,login=fork' ) . inlink( '!submit', 'class=reload,title=reload' )
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
           . inlink( '!submit', 'class=fork,title=fork,login=fork' );
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
            if( $font_size > 8 ) {
              $f = $font_size - 1;
              open_span( 'quads', inlink( '!submit', array(
                'class' => 'button', 'text' => html_tag( 'span', 'tiny', 'A-' ), 'css_font_size' => $f
              , 'title' => "decrease font size to {$f}pt"
              ) ) );
              unset( $f );
            }
            if( $font_size < 16 ) {
              $f = $font_size + 1;
              open_span( 'quads', inlink( '!submit', array(
                'class' => 'button', 'text' => html_tag( 'span', 'large', 'A+' ), 'css_font_size'=> $f
              , 'title' => "increase font size to {$f}pt"
              ) ) );
              unset( $f );
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
  
}

if( $global_context >= CONTEXT_IFRAME ) {

  // open_div( 'class=floatingframe,id=popupframe' );
  //   open_div( 'class=floatingpayload popup,id=popuppayload', 'popup payload' );
  //   open_div( 'class=shadow,id=popupshadow', '' );
  // close_div();

  open_div( $readonly ? 'payload,ro' : 'payload' . ',id=thePayload' );

  // open_javascript( "$({$H_SQ}payload{$H_SQ}).style.marginTop = $({$H_SQ}header{$H_SQ}).offsetHeight + {$H_SQ}px{$H_SQ};" );
  // js_on_exit( "window.onresize = {$H_SQ}resizeHandler();{$H_SQ}; resizeHandler();" );

  if( $global_context >= CONTEXT_WINDOW ) {
    js_on_exit( sprintf( "window.name = {$H_SQ}%s{$H_SQ};", js_window_name( $window, $thread ) ) );
  }

  js_on_exit( "js_init();" );

  // all GET requests via load_url() and POST requests via submit_form() will pass current window scroll
  // position in paramater xoffs. restore position for 'self'-requests:
  //
  if( $parent_script === 'self' ) {
    // restore scroll position:
    $offs_field = init_var( 'offs', 'sources=http,default=0x0' );
    if( preg_match( '/^(\d+)x(\d+)$/', $offs_field['value'], & $matches ) ) {
      $xoff = $matches[ 1 ];
      $yoff = $matches[ 2 ];
      // js_on_exit( "thePayload.scrollTo( $xoff, $yoff ); " );
      js_on_exit( "thePayload.scrollTop = $yoff; thePayload.scrollLeft = $xoff; " );
    }
  }

  // js_on_exit( "js_test();" );
}

if( $global_context >= CONTEXT_DIV ) {
  flush_debug_messages();
}

?>
