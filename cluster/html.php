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
    return "no such person [$people_id]";
  }
}

function html_alink_host( $hosts_id, $opts = array() ) {
  $opts = parameters_explode( $opts, 'class' );
  $host = ( isarray( $hosts_id ) ? $hosts_id : sql_one_host( $hosts_id, NULL ) );
  if( $host ) {
    if( ! ( $text = adefault( $opts, 'text', false ) ) ) {
      $text = $host['fqhostname'] . ' / ' . html_tag( 'span', 'bold', $host['sequential_number'] );
    }
    return inlink( 'host', array(
      'hosts_id' => $host['hosts_id']
    , 'class' => adefault( $opts, 'class', 'href' )
    , 'text' => $text
    , 'title' => $host['fqhostname'] . ' / ' . $host['sequential_number']
    ) );
  } else {
    return "no such host [$hosts_id]";
  }
}

function html_alink_disk( $disks_id, $opts = array() ) {
  $opts = parameters_explode( $opts, 'class' );
  $disk = sql_one_disk( $disks_id, NULL );
  if( $disk ) {
    if( ! ( $text = adefault( $opts, 'text', false ) ) ) {
      $text = $disk['fqdiskname'];
    }
    return inlink( 'disk', array(
      'disks_id' => $disks_id
    , 'class' => adefault( $opts, 'class', 'href' )
    , 'text' => $text
    , 'title' => $text
    ) );
  } else {
    return "no such disk [$disks_id]";
  }
}

function html_alink_tape( $tapes_id, $opts = array() ) {
  $opts = parameters_explode( $opts, 'class' );
  $tape = sql_one_tape( $tapes_id, NULL );
  if( $tape ) {
    if( ! ( $text = adefault( $opts, 'text', false ) ) ) {
      $text = $tape['fqtapename'];
    }
    return inlink( 'tape', array(
      'tapes_id' => $tapes_id
    , 'class' => adefault( $opts, 'class', 'href' )
    , 'text' => $text
    , 'title' => $text
    ) );
  } else {
    return "no such tape [$tapes_id]";
  }
}

?>
