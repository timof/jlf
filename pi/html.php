<?php

function html_alink_person( $people_id, $opts = array() ) {
  $opts = parameters_explode( $opts, 'class' );
  $person = sql_person( $people_id, NULL );
  if( $person ) {
    if( ! ( $text = adefault( $opts, 'text', false ) ) ) {
      $text = $person['cn'];
    }
    return inlink( 'person_view', array(
      'people_id' => $people_id
    , 'class' => adefault( $opts, 'class', 'href' )
    , 'text' => $text
    , 'title' => $text
    ) );
  } else {
    return we('(no person)','(keine Person)');
  }
}

function html_alink_group( $groups_id, $class = 'href' ) {
  $group = sql_one_group( $groups_id, NULL );
  if( $group ) {
    return inlink( 'group_view', array(
      'groups_id' => $groups_id
    , 'class' => $class
    , 'text' => $group['acronym']
    , 'title' => $group['cn_we']
    ) );
  } else {
    return we('(no group)','(keine Gruppe)');
  }
}

?>
