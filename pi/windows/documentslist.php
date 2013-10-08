<?php // /pi/windows/documentslist.php

sql_transaction_boundary('*');

echo html_tag( 'h1', ''
  , we('Documents','Dateien' ) . html_span( 'comment small qpadl', we(
    ' to be offered for download on the public web site of the institute'
  , " die auf den {$oUML}ffentlichen Webseiten zum Download angeboten werden sollen"
  ) )
);


init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
  'programme_id' => array( 'relation' => '&=' )
, 'REGEX' => 'size=40,auto=1'
, 'type' => 'allow_null='
) , 'tables=documents' );

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( 'oneline', we('Programme / Degree:','Studiengang / Abschluss:' ) );
      open_td( '', filter_programme( $f['programme_id'] ) );
    open_tr();
      open_th( '', we('type:','Typ:') );
      open_td( '', filter_documenttype( $f['type'] ) );
    open_tr();
      open_th( '', we('search:','Suche:') );
      open_td( '', ' / '.string_element( $f['REGEX'] ).' / ' . filter_reset_button( $f['REGEX'], '/floatright//' ) );
  close_table();
  if( have_priv( 'documents', 'create' ) ) {
    open_table('css actions' );
      open_caption( '', we('Actions','Aktionen') );
      open_tr( '', inlink( 'document_edit', 'class=bigbutton,text='.we('New Document / Topic','Neue Datei' ) ) );
    close_table();
  }
close_div();

documentslist_view( $f['_filters'], '' );
