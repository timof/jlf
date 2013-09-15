<?php // /pi/windows/position_view.php

sql_transaction_boundary('*');

init_var( 'positions_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $positions_id ) {
  open_div( 'warn', we('no topic selected','kein Thema gewaehlt') );
  return;
}

$position = sql_one_position( $positions_id );
$a = $position['programme_id'];
$position['programme_cn'] = '';
$comma = '';
foreach( $programme_text as $programme_id => $programme_cn ) {
  if( $a & $programme_id ) {
    $position['programme_cn'] .= "$comma$programme_cn";
    $comma = ', ';
  }
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


$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'positions', $positions_id ) ) : '' );
open_fieldset( 'small_form old' ); // , we( 'topic / postion', 'Thema / Stelle' ) . $v );
  open_table('small_form hfill');
    open_tr( 'bigskips' );
      open_td( 'colspan=2,center bold larger', $position['cn'] );

    open_tr( 'medskip' );
      open_td( '', we('Programme/final Degree:','Studiengang/Abschluss:') );
      open_td( 'oneline', $position['programme_cn'] );

    open_tr();
      open_td( 'colspan=2', $position['note'] );

    if( ( $url = $position['url'] ) ) {
      open_tr( 'medskip' );
        open_td( array( 'label' => $position['url'] ), we('Web page:','Webseite:') );
        open_td( '', html_alink( $position['url'], array( 'text' => $position['url'] ) ) );
    }

    if( $position['pdf'] ) {
      open_tr( 'bigskip' );
        open_td( '', we('more information:', 'weitere Informationen:' ) );
        // open_td( 'oneline', inlink( 'download', "item=positions_id,id=$positions_id,class=file,text=download .pdf" ) );
        // open_td( 'oneline', action_link( 'position_view,text=download .pdf,class=file,f=pdf,window=download', "action=downloadFile,positions_id=$positions_id" ) );
        open_td( 'oneline', inlink( 'position_view', "text=download .pdf,class=file,f=pdf,window=download,i=attachment,positions_id=$positions_id" ) );
    }

    open_tr( 'medskip' );
      open_td( '', we('Group:','Gruppe:') );
      open_td( '', alink_group_view( $position['groups_id'] ) );

    open_tr( 'medskip' );
      open_td( '', we('Contact:','Ansprechpartner:') );
      open_td( '', alink_person_view( $position['contact_people_id'] ) );

    open_tr();
      open_td( 'right,colspan=2' );
      echo download_button( 'position', 'ldif,pdf', "positions_id=$positions_id" );
      if( $logged_in ) {
        echo inlink( 'position_edit', array(
          'class' => 'button edit', 'text' => we('edit...','bearbeiten...' )
        , 'positions_id' => $positions_id
        , 'inactive' => priv_problems( 'positions', 'edit', $positions_id )
        ) );
      }

  close_table();

close_fieldset();

?>
