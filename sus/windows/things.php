<?php

echo "<h1>Gegenst&auml;nde</h1>";

init_global_var( 'options', 'u', 'http,persistent', 0, 'window' );

init_global_var( 'geschaeftsjahr', 'u', 'http,persistent,keep', $geschaeftsjahr_thread, 'self' );
$filters = handle_filters( array( 'anschaffungsjahr', 'geschaeftsjahr' ) );

open_table('menu');
  open_tr();
    open_th( 'center', "colspan='2'", 'Filter' );
  open_tr();
    open_th( '', '', 'Anschaffungsjahr:' );
    open_td();
      filter_anschaffungsjahr();
  open_tr();
    open_th( '', '', 'Anschaffungsjahr:' );
    open_td();
      selector_geschaeftsjahr( 'geschaeftsjahr', $geschaeftsjahr );
close_table();

medskip();

thingslist_view( $filters, '' );

?>
