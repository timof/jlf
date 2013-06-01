<?php

function html_alink_person( $filters, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $person = sql_person( $filters, NULL );
  if( $person ) {
    $text = adefault( $opts, 'text', $person['cn'] );
    return inlink( 'person_view', array(
      'people_id' => $person['people_id']
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
    return inlink( 'group_view', array(
      'groups_id' => $group['groups_id']
    , 'class' => $class
    , 'text' => adefault( $opts, 'text', $group['acronym'] )
    , 'title' => $group['cn_we']
    ) );
  } else {
    return we('(no group)','(keine Gruppe)');
  }
}

?>
