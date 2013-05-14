<?php // sidenav.php - last modified:  20130514.154434utc  by: root@uranos

function _1build_menu_tree( $map, $parents = array() ) {
  $level = count( $parents ) + 1;
  $flatmap = array();
  $s = html_div( "menupane level$level" );
  $have_current_script = false;
  $i_am_parent = false;
  foreach( $map as $script => $entry ) {
    $flatmap[ $script ] = $parents;
    if( ! $entry ) {
      continue;
    }
    $class = "menuentry level$level";
    if( isarray( $entry ) ) {
      list( $sub, $i, $flatsub ) = build_menu_tree( $entry, $parents + array( $level => $script ) );
      if( $i ) {
        $i_am_parent = true;
        $class .= ' parent';
      }
      $flatmap += $flatsub;
    } else {
      $sub = '';
    }
    if( $script === $GLOBALS['script'] ) {
      $have_current_script = true;
      $class .= ' script';
    }
    $defaults = script_defaults( $script );
    $s .= (   html_div( $class )
              . inlink( $script, array( 'class' => 'href sidenav', 'text' => $defaults['parameters']['text'] ) )
              . $sub
            . html_div( false ) );
  }
  $s .= html_div( false );
  return array( $s, $have_current_script || $i_am_parent, $flatmap );
}


function build_menu_tree( $map, $parents = array() ) {
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
    $i_am_script = ( $script === $GLOBALS['script'] );
    if( $i_am_script ) {
      $class[] = 'script';
    } else if( $i_am_parent ) {
      $class[] = 'parent';
    } else if( ! $in_menu ) {
      continue;
    }
    $defaults = script_defaults( $script );
    $class[] = ( $sub ? 'sub' : 'nosub' );
    $s .= html_div( array( 'class' => $class ) );
    $s .= inlink( $script, array( 'class' => 'href sidenav', 'text' => $defaults['parameters']['text'] ) );
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

open_div( 'id=languageLinks' );
  if( $language == 'D' ) {
    open_span( 'quads inactive', 'deutsch' );
    open_span( 'quads', inlink( '!submit', array(
      'class' => 'href quads', 'text' => 'english', 'language' => 'E'
    , 'title' => 'switch to English language'
    ) ) );
  } else {
    open_span( 'quads', inlink( '!submit', array(
      'class' => 'href quads', 'text' => 'deutsch', 'language' => 'D'
    , 'title' => 'auf deutsche Sprache umschalten'
    ) ) );
    open_span( 'quads inactive', 'english' );
  }
close_div();

open_tag( 'address' );
  echo html_tag( 'p', 'corporateborder cxorporatecolor', we('Contact','Kontakt') );
  echo html_tag( 'p', '', we('University of Potsdam','Universität Potsdam') );
  echo html_tag( 'p', '', we('Institute of Physics and Astronomy','Institut für Physik und Astronomie') );
  echo html_tag( 'p', '', 'Karl-Liebknecht-Straße 24/25' );
  echo html_tag( 'p', '', '14476 Potsdam-Golm' );
close_tag( 'address' );

?>
