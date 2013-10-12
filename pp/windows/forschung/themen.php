<?php

sql_transaction_boundary('*');

echo html_tag('h1', '', we('Suggested Topics for Theses','Themenvorschläge für Abschlussarbeiten' ) );

$f = init_fields( array(
  'groups_id'
, 'programme_id' => 'relation=&='
, 'REGEX' => 'size=40,auto=1'
) , '' );

open_div('menubox');
  open_table('css menubox');
    open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', we('Programme/Degree:','Studiengang/Abschluss:' ) );
      open_td( '', filter_programme( $f['programme_id'] ) );
    open_tr();
      open_th( '', we('Group:','Bereich:') );
      open_td( '', filter_group( $f['groups_id'] ) );
    open_tr();
      open_th( '', we('search:','Suche:') );
      open_td( '', ' / '.string_element( $f['REGEX'] ).' / ' . filter_reset_button( $f['REGEX'], '/floatright//' ) );
  close_table();
close_div();

positionslist_view( $f['_filters'], 'allow_download=1' );

?>
