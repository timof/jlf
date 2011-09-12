<?php

$field = init_var( 'geschaeftsjahr_thread', array(
  'pattern' => 'u'
, 'set_scopes' => 'thread'
, 'default' => $geschaeftsjahr_current
, 'min' => $geschaeftsjahr_min
, 'max' => $geschaeftsjahr_max
) );
open_table( 'layout hfill' );
  open_tr();
    open_td( 'colspan=2' );
      bigskip();
      // open_form();
      open_table( 'menu' );
        mainmenu_fullscreen();
        open_tr();
          open_th( '', 'Geschaeftsjahr:' );
          open_td( 'oneline' );
            selector_int( $geschaeftsjahr_thread, 'geschaeftsjahr_thread', $geschaeftsjahr_min, $geschaeftsjahr_max );
      close_table();
      // close_form();
close_table();

?>
