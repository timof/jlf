<?php // /pi/windows/document_view.php

sql_transaction_boundary('documents');

// init_var( 'options', 'global=1,type=u4,sources=http persistent,set_scopes=self' );

if( $deliverable ) switch( $deliverable ) {
  case 'document':
    init_var( 'documents_id', 'global,type=U,sources=http' );
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

echo html_tag( 'h2', '', we('Current Course Catalogs','Aktuelle Vorlesungsverzeichnisse') );

echo tb( we('Courses at the Institute of Physics and Astronomy',"Lehrveranstaltungen am Institut f{$uUML}r Physik und Astronomie"),
  array(
    alink_document_view( 'type=VVZ,flag_current', 'format=list' )
  , inlink( 'vorlesungsverzeichnisse', 'text='.we('Archive: Course catalogs of past years','Archiv: Vorlesungsverzeichnisse vergangener Jahre') )
  )
);

echo tb( we('Courses at other departments',"Lehrveranstaltungen anderer Bereiche")
  , html_alink( 'http://www.uni-potsdam.de/studium/konkret/vorlesungsverzeichnisse.html', 'class=href outlink,text='.we('University of Potsdam: all course catalogs',"Universt{$aUML}t Potsdam: alle Vorlesungsverzeichnisse") )
);

echo html_tag( 'h2', '', we('Current Regulations','Aktuelle Ordnungen') );


echo tb( we('Module Manuals',"Modulhandb{$uUML}cher")
, alink_document_view( 'type=MHB,flag_current', 'format=list' )
);


echo tb( we('Study Guidelines',"Studienordnungen")
, alink_document_view( 'type=SO,flag_current', 'format=list' )
);


?>
