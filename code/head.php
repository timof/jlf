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
  open_div( 'table hfill corporatecolor' . ( $readonly ? ' ro' : '' ) . ',id=theHeader' );
    open_div( 'tr' );

      open_div( 'left quadr td top' );
        open_div();
          if( ( $window !== 'menu' ) || ( "$thread" !== '1' ) ) {  // not main window:
            echo html_tag( 'a', 'class=close quads,title=close,href=javascript:if(opener)opener.focus();window.close();', '' );
          }
          echo html_tag( 'a', 'class=print quads,title=print,href=javascript:window.print();', '' );
          if( $login_sessions_id ) {
            echo inlink( '!submit', 'class=fork quads,title=fork,login=fork' );
          }
          if( $script != 'menu' ) {
            echo inlink( 'menu', 'class=home quads,text=,img=,title=home' );
          }
          echo inlink( '!submit', 'class=reload quads,title=reload' );
        close_div();
        // if( $logged_in ) {
        //   open_div( 'quads smallskips left', inlink( '!submit', 'text=logout...,login=logout' ) );
        // }
      close_div();

      open_div( 'left quads td top' );
        open_div( 'banner1', $bannertext1 );
        if( $bannertext2 ) {
          open_div( 'banner2', $bannertext2 );
        }
      close_div();

      open_div( 'right quad td bottom' );
        open_div( 'right', "$jlf_application_name $jlf_application_instance [$window/$thread]" );
        if( function_exists( 'window_title' ) ) {
          open_div( 'right', window_title() );
        }
        open_div( 'oneline smallskip' );
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
        close_div();
      close_div();

    close_div(); //tr
  close_div(); //table

  // open_div( 'noprint,id=navigation' );
  //   echo "navivation:";
  // close_div();
  
}

if( $global_context >= CONTEXT_IFRAME ) {

  // open_div( 'class=floatingframe,id=popupframe' );
  //   open_div( 'class=floatingpayload popup,id=popuppayload', 'popup payload' );
  //   open_div( 'class=shadow,id=popupshadow', '' );
  // close_div();

  open_div( $readonly ? 'payload,ro' : 'payload' . ',id=thePayload,onclick=window.focus();' );

  // position payload now to avoid flickering:
  open_javascript( "$({$H_SQ}thePayload{$H_SQ}).style.top = $({$H_SQ}theHeader{$H_SQ}).offsetHeight;" );
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
    if( preg_match( '/^(\d+)x(\d+)$/', $offs_field['value'], /* & */ $matches ) ) {
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
