<?php

function cli_peoplelist_html() {
  $people = sql_people( 'flag_institute' );

  $s = '';
  $n = 1;
  foreach( $people as $p ) {
    $class = ( ( $n % 2 ) ? 'odd' : 'even' );
    $s .= html_tag( 'tr', "class=$class" );
      $s .= html_tag( 'td', 'class=cn', $p['gn'].' '.$p['sn'] );
      $s .= html_tag( 'td', 'class=roomnumber', trim( $p[ 'gn'].' '.$p['sn'] ) );
      $s .= html_tag( 'td', 'class=telephonenumber', $p[ 'telephonenumber'] );
    $s .= html_tag( 'tr', false );
    $n++;
  }
  return $s;
}

function cli_persondetails_html( $people_id ) {
  need( preg_match( '/^\d{1,6}$/', $people_id ) );
  $person = sql_person( $people_id );
  $s = '';

  return $s;
}


?>
