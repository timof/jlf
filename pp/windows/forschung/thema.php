<?php

sql_transaction_boundary('*');

init_var( 'positions_id', 'global,type=U6,sources=http self,set_scopes=self url' );

if( ! $position = sql_one_position( "positions_id=$positions_id", 0 ) ) {
  open_div( 'warn', 'query failed - no such topic or position' );
  return;
}

if( $deliverable ) switch( $deliverable ) {

  case 'position':
    $position = array(
      'dn' => "positions_id=$positions_id,ou=positions,ou=physik,o=uni-potsdam,c=de"
    , 'cn' => $position['cn']
    , 'programme_cn' => $position['programme_cn']
    , 'groups_cn' => $position['groups_cn']
    , 'people_cn' => $position['people_cn']
    , 'url' => $position['url']
    , 'note' => $position['note']
    );
    switch( $global_format ) {
      case 'pdf':
        begin_deliverable( 'position', 'pdf'
        , tex2pdf( 'position.tex', array( 'loadfile', 'row' => $position ) )
        );
        break;
      case 'ldif':
        begin_deliverable( 'position', 'ldif'
        , ldif_encode( $position )
        );
        break;
      default:
        error( "unsupported format: [$global_format]" );
    }
    return;

  case 'attachment': // for attached file
    begin_deliverable( 'attachement', 'pdf' , base64_decode( $position['pdf'] ) );
    return;

  default:
    error("no such deliverable: $deliverable");
}

open_tag( 'h1', '', we('Suggested Theses topic: ','Themenvorschlag für Abschlussarbeit' ) );

open_tag( 'h2', 'block center', $position['cn'] );

open_div( 'textaroundphoto smallpads qpads' );
  if( $position['jpegphoto'] ) {
    echo html_span( 'floatright', photo_view( $position['jpegphoto'], $position['jpegphotorights_people_id'], 'style=max-width:320px;max-height:240px;' ) );
  }
  echo $position['note'];
close_div();


open_table('td:smallskips;quads');

  open_tr( 'medskip' );
    open_td( '', we('Programme / final Degree:','Studiengang / Abschluss:') );
    open_td();
    foreach( $programme_text as $id => $t ) {
      if( $position['programme_id'] & $id ) {
        open_div( 'oneline', $t );
      }
    }

if( $position['pdf'] || $position['url'] ) {
  open_tr();
    open_td( '', we('more information:', 'weitere Informationen:' ) );
    open_td();
    if( ( $url = $position['url'] ) ) {
      echo html_div( 'oneline smallskipb', html_alink( $position['url'], array( 'text' => $position['url'] ) ) );
    }
    if( $position['pdf'] ) {
      echo html_div( 'oneline', inlink( 'position_view', "text=download .pdf,class=file,f=pdf,window=download,i=attachment,positions_id=$positions_id" ) );
    }
}

  open_tr( 'medskip' );
    open_td( '', we('Group:','Gruppe:') );
    open_td( '', alink_group_view( $position['groups_id'], 'fullname=1' ) );

  open_tr( 'medskip' );
    open_td( '', we('Contact:','Ansprechpartner:') );
    open_td( '', alink_person_view( $position['contact_people_id'] ) );

  open_tr();
    open_td( 'right,colspan=2' );
    echo download_button( 'position', 'ldif,pdf', "positions_id=$positions_id" );

close_table();


?>
