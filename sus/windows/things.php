<?php

echo "<h1>Gegenst&auml;nde</h1>";

get_http_var( 'options', 'u', 0, true );

$filters = handle_filters( array( 'anschaffungsjahr' ) );

open_table('menu');
  open_tr();
    open_th( 'center', "colspan='2'", 'Filter' );
  open_tr();
    open_th( '', '', 'Anschaffungsjahr:' );
    open_td();
      filter_anschaffungsjahr();
close_table();

medskip();

thingslist_view( $filters, '' );

?>
