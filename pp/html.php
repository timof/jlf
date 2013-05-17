<?php

function html_alink_person( $filters, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $person = sql_person( $filters, NULL );
  if( $person ) {
    if( ! ( $text = adefault( $opts, 'text', false ) ) ) {
      $text = $person['cn'];
    }
    return inlink( 'visitenkarte', array(
      'p' => $person['people_id']
    , 'class' => adefault( $opts, 'class', 'href inlink' )
    , 'text' => $text
    , 'title' => $text
    ) );
  } else {
    $default = ( adefault( $opts, 'office' ) ? we(' - vacant - ',' - vakant - ') : we('(no person)','(keine Person)') );
    return adefault( $opts, 'default', $default );
  }
}

function html_alink_group( $filters, $opts = array() ) {
  $opts = parameters_explode( $opts, 'default_key=class' );
  $class = adefault( $opts, 'class', 'href inlink' );
  $group = sql_one_group( $filters, NULL );
  if( $group ) {
    return inlink( 'gruppe', array(
      'g' => $group['groups_id']
    , 'class' => adefault( $opts, 'class', 'href inlink' )
    , 'text' => $group['cn_we']
    , 'title' => $group['cn_we']
    ) );
  } else {
    return adefault( $opts, 'default', we('(no group)','(keine Gruppe)') );
  }
}


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
    $s .= html_div( 'item', $p );
  }
  return $s;
}

?>
