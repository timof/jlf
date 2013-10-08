<?php // /pi/windows/document_view.php


need( $deliverable, 'no deliverable selected' );

switch( $deliverable ) {
  case 'document':
    init_var( 'documents_id', 'global,type=U,sources=http' );
    sql_transaction_boundary('documents');
    $document = sql_one_document( $documents_id );
    if( $document['pdf'] ) {
      need( $global_format == 'pdf' );
      begin_deliverable( 'document', 'pdf', base64_decode( $document['pdf'] ) );
    } else {
      need( $global_format == 'html' );
      begin_deliverable( 'document', 'html' );
        open_javascript( "self.location.href = {$H_SQ}{$document['url']}{$H_SQ};" );
      end_deliverable( 'document' );
    }
    return;
  default:
    error("no such deliverable: $deliverable");
}

?>
