<?php

sql_transaction_boundary('*');

init_var( 'people_id', 'global,type=U6,sources=http persistent,set_scopes=self url' );

if( ! $person = sql_person( "people_id=$people_id,flag_publish,flag_deleted=0,flag_virtual=0", 0 ) ) {
  open_div( 'warn', we('query failed - no such person','Abfrage fehlgeschlagen - keine Person gefunden' ) );
  return;
}


$cn = trim( "{$person['title']} {$person['gn']} {$person['sn']}" );

$emails = $phones = $faxes = $rooms = array();
$affiliations = sql_affiliations( "people_id=$people_id,groups.flag_publish" );
$n_aff = count( $affiliations );
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

if( $person['jpegphoto'] ) {
  open_span( 'floatright', photo_view( $person['jpegphoto'], $person['jpegphotorights_people_id'] ) );
}

echo html_tag( 'h1', '', $cn );

open_table('css,td:smallskips;qquads');

if( count( $rooms ) === 1 ) {
  open_tr();
    open_td( '', we('Room:','Raum:') ); open_td( '', $rooms[ 0 ] );

}
if( count( $phones ) === 1 ) {
  open_tr();
    open_td( '', we('Phone:','Telefon:') ); open_td( '', $phones[ 0 ] );

}
if( count( $faxes ) === 1 ) {
  open_tr();
    open_td( '', 'Fax:' ); open_td( '', $faxes[ 0 ] );

}
if( count( $emails ) === 1 ) {
  open_tr();
    open_td( '', 'Email:' ); open_td( '', html_obfuscate_email( $emails[ 0 ] ) );

}

if( $person['url'] ) {
  open_tr();
    open_td( '', 'Web:' ); open_td( '', html_alink( $person['url'], array( 'class' => 'href outlink', 'text' => $person['url'] ) ) );
}

foreach( $affiliations as $aff ) {
  $class = '/.*skips// medskipt smallskipb';
  if( $n_aff > 1 ) {
    $class .= ' solidtop';
  }
  open_tr( $class );
    // $t = $aff['groups_cn'];
    // if( $aff['groups_url'] ) {
    //   $t = html_tag( 'a',  array( 'class' => 'href outlink', 'href' => $aff['groups_url'] ), $t );
    // }
    open_td( '', we('Group:','Bereich:') ); open_td( '', alink_group_view( $aff['groups_id'], 'fullname=1' ) );

  if( $aff['roomnumber'] && ( count( $rooms ) > 1 ) ) {
    open_tr();
      open_td( '', we('Room:','Raum:') ); open_td( '', $aff['roomnumber'] );

  }
  if( $aff['telephonenumber'] && ( count( $phones ) > 1 ) ) {
    open_tr();
      open_td( '', we('Phone:','Telefon:') ); open_td( '', $aff['telephonenumber'] );

  }
  if( $aff['facsimiletelephonenumber'] && ( count( $faxes ) > 1 ) ) {
    open_tr();
      open_td( '', 'Fax:' ); open_td( '', $aff['facsimiletelephonenumber'] );

  }
  if( $aff['mail'] && ( count( $emails ) > 1 ) ) {
    open_tr();
      open_td( '', 'Email:' ); open_td( '', html_obfuscate_email( $aff['mail'] ) );

  }
}

close_table();

?>
