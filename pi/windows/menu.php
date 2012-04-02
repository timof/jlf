<?php

if( $thread == 1 ) {
  open_table( 'layout hfill' );
    open_tr();
      open_td();
        bigskip();
}
      open_table( 'menu' );
        mainmenu_fullscreen();
      close_table();
if( $thread == 1 ) {
  open_td('center');
    bigskip();
    open_div( 'left', we('(this space for rent)','(hier könnte Ihre Anzeige stehen)'  ) );
  close_table();
}

?>
