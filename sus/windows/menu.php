<?php

init_global_var( 'geschaeftsjahr_thread', 'u', 'http,persistent', $geschaeftsjahr_current, 'thread' );
open_table( 'layout hfill' );
  open_tr();
    open_td( '', "colspan='2'" );
      bigskip();
      open_form();
      open_table( 'menu' );
        mainmenu_fullscreen();
        open_tr();
          open_th( '', '', 'Geschaeftsjahr:' );
          open_td( 'oneline' );
            selector_int( $geschaeftsjahr_thread, 'geschaeftsjahr_thread', $geschaeftsjahr_min, $geschaeftsjahr_max );
      close_table();
      close_form();
close_table();

?>
