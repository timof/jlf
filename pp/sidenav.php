<?php // sidenav.php - last modified:  20130429.154026utc  by: root@uranos

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
  $have_current_script = false;
  $i_am_parent = false;

  $s = html_div( "menupane level$level" );
  foreach( $map as $script => $entry ) {
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
      list( $sub, $i_am_parent, $flatsub ) = build_menu_tree( $childs, $parents + array( $level => $script ) );
      $flatmap += $flatsub;
    } else {
      $sub = '';
      $i_am_parent = 0;
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
    $s .= html_div( $class );
    $s .= inlink( $script, array( 'class' => 'href sidenav', 'text' => $defaults['parameters']['text'] ) );
    if( $i_am_script || $i_am_parent ) {
      $s .= $sub;
    }
    $s .= html_div( false );
  }
  $s .= html_div( false );
  return array( $s, $have_current_script || $i_am_parent, $flatmap );
}


list( $menu, $devnull, $sidenav_flatmap ) = build_menu_tree( $sidenav_map );

echo $menu;

?>
