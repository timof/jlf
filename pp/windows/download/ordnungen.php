<?php // /pi/windows/document_view.php

sql_transaction_boundary('documents');

init_var( 'options', 'global=1,type=u4,sources=http persistent' );


echo html_tag( 'h1', '', we('Archive: Regulations','Archiv: Ordnungen') );

echo html_tag( 'h2', '', we('Study Guidelines',"Studienordnungen") );

echo alink_document_view( 'type=SO', 'format=list' );

echo html_tag( 'h2', '', we('Module Manuals',"Modulhandb{$uUML}cher") );

echo alink_document_view( 'type=MHB', 'format=list' );


?>
