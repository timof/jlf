<?php // /pi/windows/document_view.php

sql_transaction_boundary('documents');

init_var( 'options', 'global=1,type=u4,sources=http persistent' );

if( $deliverable ) switch( $deliverable ) {
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

echo html_tag( 'h1', '', we('Download area','Download Bereich') );

echo html_tag( 'h2', '', we('Course Catalogs','Vorlesungsverzeichnisse') );

open_div( 'smallskips', alink_document_view( 'type=vvz', 'format=list,max=2' ) );

echo tb( inlink( 'vorlesungsverzeichnisse', 'text='.we('Archive: Course catalogs of previous years','Archiv: Vorlesungsverzeichnisse vergangener Jahre') ) );

echo tb( html_alink( 'http://www.uni-potsdam.de/studium/konkret/vorlesungsverzeichnisse.html', 'class=href outlink,text='.we('University of Potsdam',"Universt{$aUML}t Potsdam") )
         , we('Course Catalogs of other departments','Vorlesungsverzeichnisse anderer Bereiche' )
);

echo html_tag( 'h2', '', we('Regulations','Ordnungen') );

open_div( 'smallskips', alink_document_view( 'type=MHB,flag_current', 'format=list' ) );
open_div( 'smallskips', alink_document_view( 'type=SO,flag_current', 'format=list' ) );


?>
