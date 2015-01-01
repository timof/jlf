<?php

$css_font_size = init_var( 'css_font_size', 'type=U2,sources=http persistent,default=9,set_scopes=session window' );
$font_size = $css_font_size['value'];
// unset( $css_font_size );

html_head_view();

open_tag( 'body', 'theBody,class='.( $debug & DEBUG_FLAG_LAYOUT ? 'debug' : '' ) );

// update_form: every page is supposed to have one. all data posted to self will be part of this form:
//
open_form( 'name=update_form' );
open_span( 'nodisplay', html_tag( 'input', 'type=submit', NULL ) );

open_div( 'id=flashmessage', ' ' ); // to be filled from js

// open_div( 'floatingframe popup,id=alertpopup' );
//   open_div( 'floatingpayload popup' );
//     open_div( 'center qquads bigskips,id=alertpopuptext', ' ' );
//     open_div( 'center medskipb', html_alink( 'javascript:hide_popup();', 'class=quads button,text=Ok' ) );
//   close_div();
//   open_div( 'shadow', '' );
// close_div();

require_once( 'pp/media.php' );
require_once( 'pp/outlinks.php' );

open_div( 'hfill,id=theHeader' );
  $l = html_tag( 'img', 'src=/pp/img/haus28innen.gif,alt='.we('physics institute - inside view','Physikinstitut - Innenansicht'), NULL );
  $l = inlink( 'menu', array( 'class' => 'image', 'text' => $l ) );
  open_div( 'id=theLeftLogo', $l );
  open_div( 'id=theFaknav' );
    require_once( 'pp/faknav.php' );
  close_div();
  open_div( 'id=theUninav' );
    require_once( 'pp/uninav.php' );
  close_div();
close_div();

open_div( 'id=theOutback' );

  open_div( 'id=theSidenav' );
    require_once( 'pp/sidenav.php' );
  close_div();

  js_on_exit( "js_init();" );

  // all GET requests via load_url() and POST requests via submit_form() will pass current window scroll
  // position in parameter xoffs. restore position for 'self'-requests:
  //
  $offs_field = init_var( 'offs', 'sources=http,default=0x0' );
  if( preg_match( '/^(\d+)x(\d+)$/', $offs_field['value'], /* & */ $matches ) ) {
    $xoff = $matches[ 1 ];
    $yoff = $matches[ 2 ];
    js_on_exit( "theOutback.scrollTop = $yoff; theOutback.scrollLeft = $xoff; " );
  }

  $parents = adefault( $sidenav_flatmap, $script, array() );

  $n = 0;
//   $level = count( $parents );
//   $closelink = inlink( 'menu', 'class=close,text=' );
//   $p = 'menu';
//   foreach( $parents as $p ) {
//     $parent_defaults = script_defaults( $p );
//     $header = $parent_defaults['parameters']['title'];
//     open_div( "class=off$n lower".( $level - $n ) );
//       open_div( 'titlebar', $closelink . inlink( $p, array( 'text' => $header ) ) );
//     close_div();
//     $closelink = inlink( $p, 'class=close,text=' );
//     $n++;
//   }
  open_div( "class=off$n,id=thePayload" );

?>
