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

function html_alink_gruppe( $groups_id, $class = 'href' ) {
  $gruppe = sql_one_group( $groups_id );
  return inlink( 'gruppe_view', array(
    'groups_id' => $groups_id
  , 'class' => $class
  , 'text' => $gruppe['kurzname']
  , 'title' => $gruppe['cn']
  ) );
}

?>
