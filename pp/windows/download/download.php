<?php // /pi/windows/document_view.php

sql_transaction_boundary('documents');

// init_var( 'options', 'global=1,type=u4,sources=http persistent,set_scopes=self' );

if( $deliverable ) switch( $deliverable ) {
  case 'document':
    init_var( 'documents_id', 'global,type=U,sources=http' );
    $document = sql_one_document( "documents_id=$documents_id,flag_publish" );
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

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('rara');
    echo html_tag( 'h1', '', 'Download' );
  close_div();
close_div();


open_ccbox( '', we('Course Directories','Vorlesungsverzeichnisse') );

  echo tb( we('Courses at the Institute of Physics and Astronomy',"Lehrveranstaltungen am Institut f{$uUML}r Physik und Astronomie"),
    array(
      alink_document_view( 'type=VVZ,flag_current,flag_publish', 'format=list' )
    , inlink( 'vorlesungsverzeichnisse', 'class=href block medpads,text='.we('Archive: Course directories of past years','Archiv: Vorlesungsverzeichnisse vergangener Jahre') )
    )
  );
  
  echo tb( we('Courses at other departments',"Lehrveranstaltungen anderer Bereiche")
    , html_alink( 'http://www.uni-potsdam.de/studium/konkret/vorlesungsverzeichnisse.html', 'class=href outlink,text='.we('University of Potsdam: all course directories',"Universit{$aUML}t Potsdam: alle Vorlesungsverzeichnisse") )
  );
close_ccbox();

open_ccbox( '', we('Regulations and Guidelines',"Ordnungen und Leitf{$aUML}den") );

echo tb( we('Guidelines',"Leitf{$aUML}den")
, alink_document_view( 'type=LF,flag_current,flag_publish', 'format=list' )
);

echo tb( we('Module Manuals',"Modulhandb{$uUML}cher")
, alink_document_view( 'type=MHB,flag_current,flag_publish', 'format=list' )
);

echo tb( we('Study Guidelines',"Studienordnungen")
, array(
    alink_document_view( 'type=SO,flag_current,flag_publish', 'format=list' )
  , inlink( 'ordnungen', 'class=href block medpads,text='.we('Archive: Older versions','Archiv: ältere Fassungen') )
  )
);
close_ccbox();


?>
