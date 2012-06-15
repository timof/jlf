<?php

// the htmlDefuse filter will gobble everything up to the doctype babble line:
//
if( $global_context >= CONTEXT_DIV ) {
  echo "


  ERROR: if you see this line in browser, you need to configure htmlDefuse as ExtFilter for your apache server!


  ";
}

$css_font_size = init_var( 'css_font_size', 'type=U2,sources=http persistent,default=11,set_scopes=session window' );
$font_size = $css_font_size['value'];
unset( $css_font_size );

if( $global_context >= CONTEXT_IFRAME ) {
  html_header_view();
}

if( $global_context >= CONTEXT_WINDOW ) {
  open_div( 'head corporatecolor large ' . ( $readonly ? ' ro' : '' ) . ',id=header' );
    open_table( 'id=layout' );
  
      open_tr(); // logo and banner
        open_td( 'id=logo' );

        open_td( 'id=banner' );

      open_tr(); // menu and payload
        open_td( 'id=menu' );
        open_td( 'id=outbacks' );
}

if( $debug && ( $global_context >= CONTEXT_DIV ) ) {
  flush_debug_messages();
}

?>
