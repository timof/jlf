<?php // /pi/windows/document_view.php

sql_transaction_boundary('documents');

init_var( 'options', 'global=1,type=u4,sources=http persistent' );


echo html_tag( 'h1', '', we('Archive: Regulations','Archiv: Ordnungen') );

echo html_tag( 'h2', '', we('Course timetables', "Studienverlaufspl{$aUML}ne") );
echo alink_document_view( 'type=SVP', array( 'format' => 'list', 'orderby' => 'programme_flags, valid_from DESC' ) );

echo html_tag( 'h2', '', we('Study and Examination Guidelines',"Studien- und Pr{$uUML}fungsordnungen") );
echo alink_document_view( array( 'type' => array( 'PO', 'SO') ), array( 'format' => 'list', 'orderby' => 'programme_flags, valid_from DESC' ) );

echo html_tag( 'h2', '', we('Module Overviews',"Modul{$uUML}bersichten") );
echo alink_document_view( 'type=MOV', 'format=list' );

echo html_tag( 'h2', '', we('Module Manuals',"Modulhandb{$uUML}cher") );
echo alink_document_view( 'type=MHB', 'format=list' );



?>
