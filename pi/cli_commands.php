<?php

function cli_peoplelist_html() {
  $people = sql_people( 'flag_institute' );

  $s = '';
  $n = 1;
  foreach( $people as $p ) {
    $class = ( ( $n % 2 ) ? 'odd' : 'even' );
    $s .= html_tag( 'tr', "class=$class" );
      $s .= html_tag( 'td', 'class=cn', $p['gn'].' '.$p['sn'] );
      $s .= html_tag( 'td', 'class=telephonenumber', $p[ 'primary_telephonenumber'] );
    $s .= html_tag( 'tr', false );
    $s .= "\n";
    $n++;
  }
  return cli_html_defuse( $s );
}

function cli_persondetails_html( $people_id ) {
  need( preg_match( '/^\d{1,6}$/', $people_id ) );
  if( ! $person = sql_person( "people_id=$people_id,flag_institute", 0 ) ) {
    $s = html_tag( 'div', 'warn', 'query failed - no such person' );
  } else {
    $s = '';
    $affiliations = sql_affiliations( "people_id=$people_id" );
    if( $person['jpegphoto'] ) {
      $s .= html_tag( 'span', 'style=float:right;'
      , html_tag( 'img', array( 'height' => '100' , 'src' => ( 'data:image/jpeg;base64,' . $person['jpegphoto'] ) ), NULL )
      );
    }
    $s .= html_tag( 'table', 'class=noborder' );
      $s .= html_tag( 'tr' );
        $s .= html_tag( 'td', '', 'Name:' ) . html_tag( 'td', '', $person['gn'].' '.$person['sn'] );
      $s .= html_tag( 'tr', false );
      foreach( $affiliations as $aff ) {
        if( $aff['roomnumber'] ) {
          $s .= html_tag( 'tr' );
            $s .= html_tag( 'td', '', 'Raum:' ) . html_tag( 'td', '', $aff['roomnumber'] );
          $s .= html_tag( 'tr', false );
        }
        if( $aff['telephonenumber'] ) {
          $s .= html_tag( 'tr' );
            $s .= html_tag( 'td', '', 'Telefon:' ) . html_tag( 'td', '', $aff['telephonenumber'] );
          $s .= html_tag( 'tr', false );
        }
        if( $aff['facsimiletelephonenumber'] ) {
          $s .= html_tag( 'tr' );
            $s .= html_tag( 'td', '', 'Fax:' ) . html_tag( 'td', '', $aff['facsimiletelephonenumber'] );
          $s .= html_tag( 'tr', false );
        }
        if( $aff['mail'] ) {
          $s .= html_tag( 'tr' );
            $s .= html_tag( 'td', '', 'Email:' ) . html_tag( 'td', '', $aff['mail'] );
          $s .= html_tag( 'tr', false );
        }
      }
    $s .= html_tag( 'table', false );
  }
  return cli_html_defuse( $s );
}

?>
