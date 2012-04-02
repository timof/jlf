<?php

function html_alink_person( $people_id, $class = 'href' ) {
  $person = sql_person( $people_id );
  return inlink( 'person', array(
    'people_id' => $people_id
  , 'class' => $class
  , 'text' => $person['cn']
  , 'title' => $person['cn']
  ) );
}

function html_alink_gruppe( $groups_id, $class = 'href' ) {
  $gruppe = sql_one_group( $groups_id );
  return inlink( 'gruppe', array(
    'groups_id' => $groups_id
  , 'class' => $class
  , 'text' => $gruppe['kurzname']
  , 'title' => $gruppe['cn']
  ) );
}

?>
