<?php

init_var( 'positions_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $positions_id ) {
  open_div( 'warn', we('no topic selected','keine Thema gewaehlt') );
  return;
}

$position = sql_one_position( $positions_id );
$a = $position['degree'];
$position['degree_cn'] = '';
$comma = '';
foreach( $degree_text as $degree_id => $degree_cn ) {
  if( $a & $degree_id ) {
    $position['degree_cn'] .= "$comma$degree_cn";
    $comma = ', ';
  }
}

handle_action( array( 'download' ) );
switch( $action ) {
  case 'download':
    // $position = array_intersect_key( $position, array_flip( array( 'cn', 'note', 'degree_cn', 'groups_cn', 'people_cn', 'url' ) ) );
    $position = array(
      'dn' => "positions_id=$positions_id,ou=positions,ou=physik,o=uni-potsdam,c=de"
    , 'cn' => $position['cn']
    , 'degree_cn' => $position['degree_cn']
    , 'groups_cn' => $position['groups_cn']
    , 'people_cn' => $position['people_cn']
    , 'url' => $position['url']
    , 'note' => $position['note']
    );
    switch( $global_format ) {
      case 'pdf':
        echo tex2pdf( 'position.tex', array( 'loadfile', 'row' => $position ) );
        break;
      case 'ldif':
        echo ldif_encode( $position );
        break;
      default:
        error( "unsupported format: [$global_format]" );
    }
  return;
}


open_fieldset( 'small_form old' ); // , we( 'Data of topic', 'Daten Thema' ) );
  open_table('small_form hfill');
    open_tr( 'bigskips' );
      open_td( 'colspan=2,center bold larger', $position['cn'] );

    open_tr( 'medskip' );
      open_td( '', we('Degree:','Abschluss:') );
      open_td( 'oneline', $position['degree_cn'] );

    open_tr();
      open_td( 'colspan=2', $position['note'] );

    if( ( $url = $position['url'] ) ) {
      open_tr( 'medskip' );
        open_td( array( 'label' => $position['url'] ), we('Web page:','Webseite:') );
        open_td( '', html_alink( $position['url'], array( 'text' => $position['url'] ) ) );
    }

    if( ( $pdf = $position['pdf'] ) ) {
      open_tr( 'bigskip' );
        open_td( '', we('more information:', 'weitere Informationen:' ) );
        // open_td( 'oneline', download_link( 'positions_pdf', $positions_id, 'class=file,text=download .pdf' ) );
        open_td( 'oneline', inlink( 'download', "item=positions_id,id=$positions_id,class=file,text=download .pdf" ) );
    }

    open_tr( 'medskip' );
      open_td( '', we('Group:','Gruppe:') );
      open_td( '', html_alink_group( $position['groups_id'] ) );

    open_tr( 'medskip' );
      open_td( '', we('Contact:','Ansprechpartner:') );
      open_td( '', html_alink_person( $position['contact_people_id'] ) );

    open_tr();
      open_td( 'right,colspan=2' );
      echo download_button( 'ldif,pdf' );
      echo inlink( 'position_edit', array(
        'class' => 'button edit', 'text' => we('edit...','bearbeiten...' )
      , 'positions_id' => $positions_id
      , 'inactive' => priv_problems( 'positions', 'edit', $positions_id )
      ) );

  close_table();

close_fieldset();

?>
