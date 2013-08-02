<?php //  code/head.php

$css_font_size = init_var( 'css_font_size', 'type=U2,sources=http persistent,default=10,set_scopes=session window' );
$font_size = $css_font_size['value'];
unset( $css_font_size );

html_head_view();

open_tag( 'body', 'id=theBody,onclick=window.focus();' );

open_div( 'id=flashmessage', ' ' ); // to be filled from js

open_div( 'id=theHeader,hfill corporatecolor right' . ( $readonly ? ' ro' : '' ) ); // extra container for padding (no padded tables!)
// open_table( 'css=1,hfill' . ( $readonly ? ' ro' : '' ) );
//   open_tr('td:smallskips;quads');

  open_div('smallskips quads left top inline_block floatleft');
//    open_td( 'left top' );
        if( ( $window !== 'menu' ) || ( "$thread" !== '1' ) ) {  // not main window:
          echo html_tag( 'a', 'class=close,title=close,href=javascript:if(opener)opener.focus();window.close();', '' );
        }
        echo html_tag( 'a', 'class=print,title=print,href=javascript:window.print();', '' );
        if( $login_sessions_id ) {
          echo inlink( '!submit', 'class=fork,title=fork,login=fork' );
        }
        if( $script != 'menu' ) {
          echo inlink( 'menu', 'class=home,text=,img=,title=home' );
        }
        echo inlink( '!submit', 'class=reload,title=reload' );
  close_div();

  open_div('smallskips quads left top inline_block floatleft');
//    open_td( 'left top' );
      open_div( 'banner Large', $bannertext1 );
      if( $bannertext2 ) {
        open_div( 'banner large', $bannertext2 );
      }
  close_div();

  open_div('smallskips quads right inline_block');
//    open_td( 'right bottom' );
      open_div( 'right', "$jlf_application_name $jlf_application_instance [$window/$thread]" );
      if( function_exists( 'window_title' ) ) {
        open_div( 'right', window_title() );
      }
      open_div( 'oneline smallskipt' );
        if( $font_size > 8 ) {
          $f = $font_size - 1;
          open_span( 'quadl', inlink( '!submit', array(
            'class' => 'button', 'text' => html_tag( 'span', 'tiny', 'A-' ), 'css_font_size' => $f
          , 'title' => "decrease font size to {$f}pt"
          ) ) );
          unset( $f );
        }
        if( $font_size < 16 ) {
          $f = $font_size + 1;
          open_span( 'quadl', inlink( '!submit', array(
            'class' => 'button', 'text' => html_tag( 'span', 'large', 'A+' ), 'css_font_size'=> $f
          , 'title' => "increase font size to {$f}pt"
          ) ) );
          unset( $f );
        }
        if( $show_debug_button ) {
          open_span( 'quadl', inlink( '!submit', array(
            'class' => 'button', 'text' => 'D', 'debug' => ( $debug ? '0' : '1' )
          , 'title' => 'toggle debugging mode'
          ) ) );
        }
        if( $language == 'D' ) {
          open_span( 'quadl', inlink( '!submit', array(
            'class' => 'button qpadds', 'text' => 'en', 'language' => 'E'
          , 'title' => 'switch to English language'
          ) ) );
        } else {
          open_span( 'quadl', inlink( '!submit', array(
            'class' => 'button qpadds', 'text' => 'de', 'language' => 'D'
          , 'title' => 'auf deutsche Sprache umschalten'
          ) ) );
        }
      close_div();
  close_div();

// close_table();
close_div();

open_div( 'floatingframe popup,id=alertpopup' );
  open_div( 'floatingpayload popup' );
    open_div( 'center qquads bigskips,id=alertpopuptext', ' ' );
    open_div( 'center medskipb', html_alink( 'javascript:hide_popup();', 'class=quads button,text=Ok' ) );
  close_div();
  open_div( 'shadow', '' );
close_div();

// open_div( 'noprint,id=navigation' );
//   echo "navivation:";
// close_div();

js_on_exit( sprintf( "window.name = {$H_SQ}%s{$H_SQ};", js_window_name( $window, $thread ) ) );

open_div( $readonly ? 'ro' : '' . ',id=theOutbacks,onclick=window.focus();' );

// position outbacks now to avoid flickering:
open_javascript( "$({$H_SQ}theOutbacks{$H_SQ}).style.top = $({$H_SQ}theHeader{$H_SQ}).offsetHeight;" );

begin_deliverable( 'htmlPayloadOnly', 'html' );

open_div( 'id=thePayload' );

// update_form: every page is supposed to have one. all data posted to self will be part of this form:
//
open_form( 'name=update_form' );


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
    js_on_exit( "theOutbacks.scrollTop = $yoff; theOutbacks.scrollLeft = $xoff; " );
  }
}

?>
