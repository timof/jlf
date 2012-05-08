<?php

function html_alink_person( $people_id, $opts = array() ) {
  $opts = parameters_explode( $opts, 'class' );
  if( ! ( $text = adefault( $opts, 'text', false ) ) ) {
    $person = sql_person( $people_id );
    $text = $person['cn'];
  }
  return inlink( 'person_view', array(
    'people_id' => $people_id
  , 'class' => adefault( $opts, 'class', 'href' )
  , 'text' => $text
  , 'title' => $text
  ) );
}

function html_alink_group( $groups_id, $class = 'href' ) {
  $group = sql_one_group( $groups_id );
  return inlink( 'group_view', array(
    'groups_id' => $groups_id
  , 'class' => $class
  , 'text' => $group['acronym']
  , 'title' => $group['cn_we']
  ) );
}

?>
