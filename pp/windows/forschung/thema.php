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

echo position_view( $position );

?>
