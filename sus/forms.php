<?php

// function form_ensure_geschaeftsjahr() {
//   global $now;
//   init_var( 'geschaeftsjahr', "global,type=u,sources=html persistent,default=$geschaeftsjahr_thread" );
//   if( ! $GLOBALS['geschaeftsjahr'] ) {
//     open_form( 'name=update_form' );
//       echo "Geschaeftsjahr: ";
//       echo int_view( $now[0], 'geschaeftsjahr', 4 );
//     close_form();
//     exit();
//   }
// }


function form_login() {
  open_fieldset( 'class=small_form,style=padding:2em;', we('Login','Anmelden') );
    flush_problems();
    hidden_input( 'l', 'login' );
    bigskip();
    open_table('small_form');
      open_tr('medskip');
        open_td( 'label quads', we('user-id: ','Benutzerkennung: ') );
        open_td( 'kbd', string_element( 'name=uid,size=12,priority=0' ) );
      open_tr('medskip');
        open_td( 'label quads', we('password: ','Passwort: ') );
        open_td( 'kbd', html_tag( 'input', 'type=password,size=12,name=password,value=', NULL ) );
      open_tr('medskip');
        open_td();
        open_td('right');
          echo submission_button( 'login=nop,text='.we('back', 'zurück') );
          quad();
          echo submission_button( 'action=,text='.we('log in','Anmelden') );
    close_table();
    bigskip();
  close_fieldset();
}

?>
