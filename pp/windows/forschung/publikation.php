<?php

sql_transaction_boundary('*');

init_var( 'publications_id', 'global,type=U6,sources=http self,set_scopes=self url' );

if( ! ( $publication = sql_one_publication( "publications_id=$publications_id", 0 ) ) ) {
  open_div( 'warn', 'query failed - no such publication' );
  return;
}

if( $deliverable ) switch( $deliverable ) {

//   case 'publication':
//     $publication = array(
//       'dn' => "publications_id=$publications_id,ou=publications,ou=physik,o=uni-potsdam,c=de"
//     , 'cn' => $position['cn']
//     , 'programme_cn' => $position['programme_cn']
//     , 'groups_cn' => $position['groups_cn']
//     , 'people_cn' => $position['people_cn']
//     , 'url' => $position['url']
//     , 'note' => $position['note']
//     );
//     switch( $global_format ) {
//       case 'pdf':
//         begin_deliverable( 'position', 'pdf'
//         , tex2pdf( 'position.tex', array( 'loadfile', 'row' => $position ) )
//         );
//         break;
//       case 'ldif':
//         begin_deliverable( 'position', 'ldif'
//         , ldif_encode( $position )
//         );
//         break;
//       default:
//         error( "unsupported format: [$global_format]" );
//     }
//     return;
// 
//   case 'attachment': // for attached file
//     begin_deliverable( 'attachment', 'pdf' , base64_decode( $position['pdf'] ) );
//     return;

  default:
    error("no such deliverable: $deliverable");
}

echo publication_columns_view( $publication );

?>
