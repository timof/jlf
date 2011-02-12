<?php

echo "<h1>Darlehen</h1>";

init_global_var( 'options', 'u', 'http,persistent', 0, 'window' );

$filters = handle_filters( array( 'people_id' ) );

open_table('menu');
  open_tr();
    open_th( 'center', "colspan='2'", 'Filter' );
  open_tr();
    open_th( '', '', 'Kreditor:' );
    open_td();
      filter_people();
close_table();

medskip();

darlehenlist_view( $filters, '' );

?>
