<?php // /pi/windows/document_view.php

sql_transaction_boundary('documents');

init_var( 'options', 'global=1,type=u4,sources=http persistent' );

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    open_tag( 'img', array( 'src' => '/pp/fotos/rara.jpg', 'alt' => 'RaRa-Bücher der Bibliothek' ), NULL );
    open_div( 'rights', we('Image:','Bild:') . ' Karla Fritze' );
    echo html_tag( 'h1', '', we('Download-area / Archive: Regulations and Guidelnes','Download-Bereich / Archiv: Ordnungen und Leitfäden' ) );
  close_div();
close_div();

// echo html_tag( 'h1', '', we('Archive: Regulations and Guidelines',"Archiv: Ordnungen und Leitf{$aUML}den") );

echo html_tag( 'h2', '', we('Guidelines', "Leitf{$aUML}den") );
echo alink_document_view( 'type=LF', array( 'format' => 'list', 'orderby' => 'programme_flags, valid_from DESC' ) );

echo html_tag( 'h2', '', we('Course timetables', "Studienverlaufspl{$aUML}ne") );
echo alink_document_view( 'type=SVP', array( 'format' => 'list', 'orderby' => 'programme_flags, valid_from DESC' ) );

echo html_tag( 'h2', '', we('Study and Examination Guidelines',"Studien- und Pr{$uUML}fungsordnungen") );
echo alink_document_view( array( 'type' => array( 'PO', 'SO') ), array( 'format' => 'list', 'orderby' => 'programme_flags, valid_from DESC' ) );

echo html_tag( 'h2', '', we('Module Overviews',"Modul{$uUML}bersichten") );
echo alink_document_view( 'type=MOV', 'format=list' );

echo html_tag( 'h2', '', we('Module Manuals',"Modulhandb{$uUML}cher") );
echo alink_document_view( 'type=MHB', 'format=list' );



?>
