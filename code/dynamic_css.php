<?php  // code/dynamic_css.php: dynamic css settings - to be included in the head section
//
$font_size = adefault( $GLOBALS, 'font_size', 11 );
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
    body, input, textarea, h4, .defaults, .normalsize, td, th, caption { font-size:%upt; }
    h3, .large { font-size:%upt; }
    h2, .Large { font-size:%upt; }
    h1, .huge { font-size:%upt; }
    .small { font-size:%upt; }
    .corporatecolor, .table.corporatecolor $H_GT .tbody $H_GT .tr $H_GT .td {
      background-color:#%s !important;
      color:#ffffff;
    }
    .corporateborder {
      border-color:#%s !important;
    }
    .formcolor, fieldset, .mainmenu, .menu .th, .menu .td.th, .menu .table $H_GT .caption, fieldset fieldset $H_GT legend
    , .menubox .th, .menubox .caption
    , fieldset.table $H_GT .tbody $H_GT .tr $H_GT .td
    , fieldset table.list $H_GT tbody $H_GT tr.even $H_GT td
    , td.popup, td.dropdown_menu
    , fieldset caption .button
    {
      background-color:#%s;
    }
    .formcolor.shaded, fieldset table.list $H_GT tbody $H_GT tr.odd $H_GT td, fieldset table.list $H_GT caption
    , fieldset caption .button.inactive, fieldset caption .button.inactive:hover {
      background-color:#%s;
    }
    .formcolor.shadedd, fieldset table.list $H_GT * $H_GT tr $H_GT th {
      background-color:#%s;
    }
    .formcolor.lighter, .menubox .td
    , fieldset caption .button.pressed, fieldset caption .button:hover {
      background-color:#%s;
    }
    fieldset.old .kbd.modified, fieldset.old .kbd.problem.modified {
      outline:4px solid #%s;
    }
    td.dropdown_menu:hover, td.dropdown_menu.selected {
      background-color:#%s;
    }
  "
  , $font_size, $font_size + 1, $font_size + 2, $font_size + 3, $font_size - 1
  , $corporatecolor, $corporatecolor, $css_form_color, $form_color_shaded, $form_color_shadedd, $form_color_lighter, $form_color_modified, $form_color_hover
  );
close_tag( 'style' );

?>
