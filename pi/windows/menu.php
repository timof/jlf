<?php // /pi/windows/menu.php

init_var( 'options', 'global=1,type=u,set_scopes=self,sources=http persistent' );

// if( $thread == 1 ) {
//   open_table( 'css layout hfill' );
//     open_tr();
//       open_td();
// }
  open_ul( 'mainmenu fullscreen a;button:button;big' );
    switch( $window ) {
      case 'menu':
        echo mainmenu_view('class=button big');
        break;
      case 'submenu_lehre':
        echo submenu_lehre_view('class=button big');
        break;
      case 'submenu_root':
        echo submenu_root_view('class=button big');
        break;
    }
  close_ul();

// if( $thread == 1 ) {
//  open_td('center');
//    bigskip();
//    open_div( 'inline_block floatright left', we('(this space for rent)','(hier könnte Ihre Anzeige stehen)'  ) );
//  close_table();
// }


?>
