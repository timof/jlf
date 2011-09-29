<?php

echo html_tag( 'h1', '', 'Gegenstände' );

init_var( 'options', 'global,pattern=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields( array( 'anschaffungsjahr', 'geschaeftsjahr' ) );
$filters = $fields['_filters'];

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', 'Anschaffungsjahr:' );
    open_td();
      filter_anschaffungsjahr( $fields['anschaffungsjahr'] );
  open_tr();
    open_th( '', 'Gesch'.H_AMP.'auml;ftsjahr:' );
    open_td();
      filter_geschaeftsjahr( $fields['geschaeftsjahr'] );
close_table();

medskip();

thingslist_view( $filters, '' );

?>
