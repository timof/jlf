<?php

function cli_peoplelist_html() {
  $people = sql_people( 'flag_institute' );

  $s = '';
  $n = 1;
  foreach( $people as $p ) {
    $class = ( ( $n % 2 ) ? 'odd' : 'even' );
    $s .= html_tag( 'tr', "class=$class" );
      $people_id = $p['people_id'];
      $link = html_tag( 'a', "class=inlink,href=/members/persondetails.m4php?p=$people_id", $p['gn'].' '.$p['sn'] );
      $s .= html_tag( 'td', 'class=cn', $link );
      $s .= html_tag( 'td', 'class=telephonenumber', $p[ 'primary_telephonenumber'] );
    $s .= html_tag( 'tr', false );
    $s .= "\n";
    $n++;
  }
  return cli_html_defuse( $s );
}

# cli_persondetails_html()
# output: 1 title line (person's cn), followed by html payload fragment
#
function cli_persondetails_html( $people_id ) {
  need( preg_match( '/^\d{1,6}$/', $people_id ) );
  if( ! $person = sql_person( "people_id=$people_id,flag_institute", 0 ) ) {
    $s = "\n" . html_tag( 'div', 'warn', 'query failed - no such person' );
  } else {
    $s = trim( "{$person['title']} {$person['gn']} {$person['sn']}" ) . "\n";
    $affiliations = sql_affiliations( "people_id=$people_id" );
    if( $person['jpegphoto'] ) {
      $s .= html_tag( 'span', 'style=float:right;'
      , html_tag( 'img', array( 'height' => '100' , 'src' => ( 'data:image/jpeg;base64,' . $person['jpegphoto'] ) ), NULL )
      );
    }
    $s .= html_tag( 'table', 'class=noborder' ) ."\n";
      $s .= html_tag( 'tr' );
        $s .= html_tag( 'td', '', 'Name:' ) . html_tag( 'td', '', $person['gn'].' '.$person['sn'] );
      $s .= html_tag( 'tr', false ) ."\n";
      foreach( $affiliations as $aff ) {
        $s .= html_tag( 'tr', 'class=medskip' );
          $s .= html_tag( 'td', '', '_m4_de(Bereich)_m4_en(Group):' ) . html_tag( 'td', '', $aff['groups_cn'] );
        $s .= html_tag( 'tr', false ) ."\n";
        if( $aff['roomnumber'] ) {
          $s .= html_tag( 'tr' );
            $s .= html_tag( 'td', '', '_m4_de(Raum)_m4_en(Room):' ) . html_tag( 'td', '', $aff['roomnumber'] );
          $s .= html_tag( 'tr', false ) ."\n";
        }
        if( $aff['telephonenumber'] ) {
          $s .= html_tag( 'tr' );
            $s .= html_tag( 'td', '', '_m4_de(Telefon)_m4_en(Phone):' ) . html_tag( 'td', '', $aff['telephonenumber'] );
          $s .= html_tag( 'tr', false ) ."\n";
        }
        if( $aff['facsimiletelephonenumber'] ) {
          $s .= html_tag( 'tr' );
            $s .= html_tag( 'td', '', 'Fax:' ) . html_tag( 'td', '', $aff['facsimiletelephonenumber'] );
          $s .= html_tag( 'tr', false ) ."\n";
        }
        if( $aff['mail'] ) {
          $s .= html_tag( 'tr' );
            $s .= html_tag( 'td', '', 'Email:' ) . html_tag( 'td', '', html_obfuscate_email( $aff['mail'] ) );
          $s .= html_tag( 'tr', false ) ."\n";
        }
      }
    $s .= html_tag( 'table', false ) ."\n";
  }
  return cli_html_defuse( $s );
}

?>
