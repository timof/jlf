<?php



function html_map( $map ) {
  $id = $map['id'];
  $name = adefault( $map, 'name', $id );
  $s = html_tag( 'map', "name=$name,id=$id" );
  $n = 0;
  while( isset( $map[ $n ] ) ) {
    $area = $map[ $n ];
    if( isset( $area['title'] ) && ! isset( $area['alt'] ) ) {
      $area['alt'] = $area['title'];
    }
    $s .= html_tag( 'area', $area, NULL );
    $n++;
  }
  return $s . html_tag( 'map', false );
}

function tb( $headline, $ps = array(), $opts = array() ) {
  $s = '';
  if( $headline ) {
    $s .= html_div( 'item headline', $headline );
  }
  if( ! isarray( $ps ) ) {
    $ps = array( $ps );
  }
  foreach( $ps as $p ) {
    if( ! $p ) {
      continue;
    }
    if( isarray( $p ) ) {
      $t = html_div( 'subheader', $p[ 0 ] ) . html_span( '', $p[ 1 ] );
    } else {
      $t = $p;
    }
    $s .= html_div( 'item', $t );
  }
  return html_div( 'p', $s );
}

?>
