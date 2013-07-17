<?php

function cli_labslist_html() {
  $labs = sql_labs( '', array( 'orderby' => 'owning_group_cn, roomnumber' ) );

  $s = '';
  $n = 1;
  foreach( $labs as $r ) {
    $class = ( ( $n % 2 ) ? 'odd' : 'even' );
    $s .= html_tag( 'tr', "class=$class" );
      $rooms_id = $r['rooms_id'];
      $s .= html_tag( 'td', 'class=cn', $r['groups_cn'] );
      $s .= html_tag( 'td', 'class=cn', $r['roomnumber'] );
      $contact_people_id = $r['contact_people_id'];
      $link = html_tag( 'a', "class=inlink,href=/members/persondetails.m4php~p$contact_people_id", $r['contact_cn'] );
      $s .= html_tag( 'td', 'class=cn', $link );
    $s .= html_tag( 'tr', false );
    $s .= "\n";
    $n++;
  }
  return cli_html_defuse( $s );
}


function cli_peoplelist_html() {
  $people = sql_people( 'flag_institute' );

  $s = '';
  $n = 1;
  foreach( $people as $p ) {
    $class = ( ( $n % 2 ) ? 'odd' : 'even' );
    $s .= html_tag( 'tr', "class=$class" );
      $people_id = $p['people_id'];
      $link = html_tag( 'a', "class=inlink,href=/members/persondetails.m4php~p$people_id", $p['gn'].' '.$p['sn'] );
      $s .= html_tag( 'td', 'class=cn', $link );
      $s .= html_tag( 'td', 'class=telephonenumber', $p[ 'primary_telephonenumber'] );
    $s .= html_tag( 'tr', false );
    $s .= "\n";
    $n++;
  }
  return cli_html_defuse( $s );
}

function cli_peoplelist_cvs() {
  $people = sql_people( 'flag_institute' );

  $s = '';
  $n = 1;
  foreach( $people as $p ) {
    $s .= $p['gn'].' '.$p['sn'] . ' ; ';
    $s .= $p['primary_groupname'] . ' ; ';
    $s .= $p['primary_roomnumber'] . ' ; ';
    $s .= $p['primary_telephonenumber'] . ' ; ';
    $s .= "\n";
    $n++;
  }
  return cli_umlauts_defuse( $s );
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
    if( $person['jpegphoto'] ) {
      $s .= html_tag( 'span', 'style=float:right;'
      , html_tag( 'img', array( 'style' => 'max-width:180px;max-height:180px;' , 'src' => ( 'data:image/jpeg;base64,' . $person['jpegphoto'] ) ), NULL )
      );
    }
    $emails = $phones = $faxes = $rooms = array();
    $affiliations = sql_affiliations( "people_id=$people_id,flags&=".GROUPS_FLAG_LIST );
    foreach( $affiliations as $aff ) {
      if( ( $r = $aff['roomnumber' ] ) ) {
        if( ! in_array( $r, $rooms ) ) {
          $rooms[] = $r;
        }
      }
      if( ( $r = $aff['mail' ] ) ) {
        if( ! in_array( $r, $emails ) ) {
          $emails[] = $r;
        }
      }
      if( ( $r = $aff['telephonenumber' ] ) ) {
        if( ! in_array( $r, $phones ) ) {
          $phones[] = $r;
        }
      }
      if( ( $r = $aff['facsimiletelephonenumber' ] ) ) {
        if( ! in_array( $r, $faxes ) ) {
          $faxes[] = $r;
        }
      }
    }
    $s .= html_tag( 'table', 'class=noborder' ) ."\n";
      $s .= html_tag( 'tr' );
        $s .= html_tag( 'td', '', 'Name:' ) . html_tag( 'td', '', $person['gn'].' '.$person['sn'] );
      $s .= html_tag( 'tr', false ) ."\n";
      if( count( $rooms ) === 1 ) {
        $s .= html_tag( 'tr' );
          $s .= html_tag( 'td', '', '_m4_de(Raum)_m4_en(Room):' ) . html_tag( 'td', '', $rooms[ 0 ] );
        $s .= html_tag( 'tr', false ) ."\n";
      }
      if( count( $phones ) === 1 ) {
        $s .= html_tag( 'tr' );
          $s .= html_tag( 'td', '', '_m4_de(Telefon)_m4_en(Phone):' ) . html_tag( 'td', '', $phones[ 0 ] );
        $s .= html_tag( 'tr', false ) ."\n";
      }
      if( count( $faxes ) === 1 ) {
        $s .= html_tag( 'tr' );
          $s .= html_tag( 'td', '', 'Fax:' ) . html_tag( 'td', '', $faxes[ 0 ] );
        $s .= html_tag( 'tr', false ) ."\n";
      }
      if( count( $emails ) === 1 ) {
        $s .= html_tag( 'tr' );
          $s .= html_tag( 'td', '', 'Email:' ) . html_tag( 'td', '', html_obfuscate_email( $emails[ 0 ] ) );
        $s .= html_tag( 'tr', false ) ."\n";
      }
      if( ( $t = $person['url'] ) ) {
        $s .= html_tag('tr');
          $s .= html_tag( 'td', '', '_m4_de(Webseite)_m4_en(Web page):' );
          $s .= html_tag( 'td', '', html_tag( 'a',  array( 'href' => $person['url'] ), $t ) );
        $s .= html_tag( 'tr', false ) ."\n";
      }

      foreach( $affiliations as $aff ) {
        $s .= html_tag( 'tr', 'class=medskipb' );
          $t = $aff['groups_cn'];
          if( $aff['groups_url'] ) {
            $t = html_tag( 'a',  array( 'href' => $aff['groups_url'] ), $t );
          }
          $s .= html_tag( 'td', '', '_m4_de(Bereich)_m4_en(Group):' ) . html_tag( 'td', '', $t );
        $s .= html_tag( 'tr', false ) ."\n";
        if( $aff['roomnumber'] && ( count( $rooms ) > 1 ) ) {
          $s .= html_tag( 'tr' );
            $s .= html_tag( 'td', '', '_m4_de(Raum)_m4_en(Room):' ) . html_tag( 'td', '', $aff['roomnumber'] );
          $s .= html_tag( 'tr', false ) ."\n";
        }
        if( $aff['telephonenumber'] && ( count( $phones ) > 1 ) ) {
          $s .= html_tag( 'tr' );
            $s .= html_tag( 'td', '', '_m4_de(Telefon)_m4_en(Phone):' ) . html_tag( 'td', '', $aff['telephonenumber'] );
          $s .= html_tag( 'tr', false ) ."\n";
        }
        if( $aff['facsimiletelephonenumber'] && ( count( $faxes ) > 1 ) ) {
          $s .= html_tag( 'tr' );
            $s .= html_tag( 'td', '', 'Fax:' ) . html_tag( 'td', '', $aff['facsimiletelephonenumber'] );
          $s .= html_tag( 'tr', false ) ."\n";
        }
        if( $aff['mail'] && ( count( $emails ) > 1 ) ) {
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
