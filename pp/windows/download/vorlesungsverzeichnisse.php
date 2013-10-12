<?php // /pi/windows/document_view.php

sql_transaction_boundary('documents');

init_var( 'options', 'global=1,type=u4,sources=http persistent' );


echo html_tag( 'h1', '', we('Archive: Courses Catalogs','Archiv: Vorlesungsverzeichnisse') );

echo alink_document_view( 'type=VVZ', 'format=list' );


?>
