<?php

$field = init_var( 'geschaeftsjahr_thread', array(
  'type' => 'u'
, 'set_scopes' => 'thread'
, 'initval' => $geschaeftsjahr_current
, 'min' => $geschaeftsjahr_min
, 'max' => $geschaeftsjahr_max
) );
open_table( 'layout hfill' );
  open_tr();
    open_td( 'colspan=2' );
      bigskip();
      open_table( 'menu' );
        mainmenu_fullscreen();
        open_tr();
          open_th( '', 'Geschaeftsjahr:' );
          open_td( 'oneline' );
            selector_int( $field );
      close_table();
close_table();

?>
