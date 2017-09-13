<?php // /pi/windows/document_view.php

sql_transaction_boundary('documents');

init_var( 'options', 'global=1,type=u4,sources=http persistent' );

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('rara');
    echo html_tag( 'h1', '', we('Download / Archive: directories','Download / Archiv: Vorlesungsverzeichnsse' ) );
  close_div();
close_div();

// echo html_tag( 'h1', '', we('Archive: Course Directories','Archiv: Vorlesungsverzeichnisse') );

open_ccbox( '', we('Archive: Course Directories',"Archiv: Vorlesungsverzeichnisse") );

echo alink_document_view( 'type=VVZ', 'format=list,orderby=valid_from DESC' );

close_ccbox();

?>
