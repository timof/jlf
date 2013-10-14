<?php // sidenav.php - last modified:  20131014.143358utc  by: root@uranos


function build_menu_tree( $map, $parents = array() ) {
  $global_script = $GLOBALS['script_defaults']['parameters']['script'];
  $level = count( $parents ) + 1;
  $flatmap = array();
  $s = html_div( "menupane level$level" );
  $you_are_parent = false;

  $s = '';
  foreach( $map as $script => $entry ) {
    $i_am_parent = 0;
    $flatmap[ $script ] = $parents;
    if( ! isarray( $entry ) ) {
      $in_menu = $entry;
      $childs = array();
    } else {
      $in_menu = $entry['menu'];
      $childs = $entry['childs'];
    }
    $class = array( 'menuentry', "level$level" );
    if( $childs ) {
      list( $sub, $i, $flatsub ) = build_menu_tree( $childs, $parents + array( $level => $script ) );
      $flatmap += $flatsub;
      if( $i ) {
        $i_am_parent = 1;
      }
    } else {
      $sub = '';
    }
    $i_am_script = ( $script === $global_script );
    if( $i_am_script ) {
      $class[] = 'script';
    } else if( $i_am_parent ) {
      $class[] = 'parent';
    } else if( ! $in_menu ) {
      continue;
    }
    $defaults = script_defaults( $script );
    $class[] = ( $sub ? 'sub' : 'nosub' );
    $linkclass = 'href sidenav';
    $s .= html_div( array( 'class' => $class ) );
    $s .= inlink( $script, array( 'class' => $linkclass, 'text' => $defaults['parameters']['text'], 'inactive' => ! $in_menu ) );
    $s .= html_div( false );
    if( $i_am_script || $i_am_parent ) {
      $s .= $sub;
      $you_are_parent = true;
    }
  }
  if( $s ) {
    $s = html_div( "menupane level$level", $s );
  }
  return array( $s, $you_are_parent, $flatmap );
}


list( $menu, $devnull, $sidenav_flatmap ) = build_menu_tree( $sidenav_map );

echo $menu;

open_div( 'links oneline,id=languageLinks' );
  if( $language == 'D' ) {
    // open_span( 'quads inactive', 'deutsch' );
    echo inlink( '!', array(
      'class' => 'href', 'text' => 'language: switch to English', 'language' => 'E'
    , 'title' => 'switch to English language / Sprache: auf Englisch umschalten'
    ) );
  } else {
    echo inlink( '!', array(
      'class' => 'href', 'text' => 'Sprache: auf Deutsch umschalten', 'language' => 'D'
    , 'title' => 'Sprache: auf Deutsch umschalten / switch to German language'
    ) );
    // open_span( 'quads inactive', 'english' );
  }
close_div();

open_div( 'links oneline,id=selectFontSize' );
  echo we('font size: ','Schriftgröße:');
  if( $font_size > 8 ) {
    $f = $font_size - 1;
    open_span( 'qquadl', inlink( '!', array(
      'class' => 'href inlink', 'text' => html_tag( 'span', 'tiny', 'A-' ), 'css_font_size' => $f
    , 'title' => we('decrease font size to ','Schriftgröße herabsetzen auf ')."{$f}pt"
    ) ) );
    unset( $f );
  }
  if( $font_size < 16 ) {
    $f = $font_size + 1;
    open_span( 'qquadl', inlink( '!', array(
      'class' => 'href inlink', 'text' => html_tag( 'span', 'large', 'A+' ), 'css_font_size'=> $f
    , 'title' => we('increase font size to ','Schriftgröße erhöhen auf ')."{$f}pt"
    ) ) );
    unset( $f );
  }
close_div();

address_view( 'format=narrow' );

if( $debug || $show_debug_button ) {
  open_div( 'links,id=debugButton', debug_button_view() );
}

?>
