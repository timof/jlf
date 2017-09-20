<?php
// dynamic css settings - to be included in the head section
//
$font_size = adefault( $GLOBALS, 'font_size', 10 );
$css_corporate_color = adefault( $GLOBALS, 'css_corporate_color', 'f02020' );
$css_form_color = adefault( $GLOBALS, 'css_form_color', 'e0e0e0' );

if( ( $thread = adefault( $GLOBALS, 'thread', 1 ) ) > 1 ) {
  $corporatecolor = rgb_color_lighten( $css_corporate_color, ( $thread - 1 ) * 25 );
} else {
  $corporatecolor = $css_corporate_color;
}
$form_color_modified = rgb_color_lighten( $css_form_color, array( 'r' => -10, 'g' => -10, 'b' => 50 ) );
$form_color_shaded = rgb_color_lighten( $css_form_color, -10 );
$form_color_shadedd = rgb_color_lighten( $css_form_color, -20 );
$form_color_lighter = rgb_color_lighten( $css_form_color, 20 );
$form_color_hover = rgb_color_lighten( $css_form_color, 30 );

// echo html_tag( 'meta', array( 'http-equiv' => 'Content-Type', 'content' => 'text/html; charset=utf-8' ), NULL );

open_tag( 'style', 'type=text/css' );
  printf( "
    body, a, button, input, textarea, .defaults, .normalsize, td, th, caption { font-size:%upt; }
    h4, .large { font-size:%upt; }
    h3, .Large { font-size:%upt; }
    h2, .huge { font-size:%upt; }
    h1, .Huge { font-size:%upt; }
    .small { font-size:%upt; }
    .tiny { font-size:%upt; }
  "
  , $font_size, $font_size + 1, $font_size + 2, $font_size + 5, $font_size + 7, $font_size - 1, $font_size - 2
  );
close_tag( 'style' );

?>
