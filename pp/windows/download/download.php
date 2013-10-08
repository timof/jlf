<?php // /pi/windows/document_view.php


need( $deliverable, 'no deliverable selected' );

switch( $deliverable ) {
  case 'document':
    need( $global_format == 'pdf' );
    init_var( 'documents_id', 'global,type=U,sources=http' );
    sql_transaction_boundary('documents');
    $document = sql_one_document( $documents_id );
    begin_deliverable( 'document', 'pdf' , base64_decode( $document['pdf'] ) );
    return;
  default:
    error("no such deliverable: $deliverable");
}

?>
