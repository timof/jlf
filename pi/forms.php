<?php

function form_login() {
  open_fieldset( 'class=small_form,style=padding:2em;', we('Login','Anmelden') );
    flush_problems();
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
          echo inlink( '', 'login=nop,class=button,text='.we('back', 'zurück') );
          quad();
          echo inlink( '', 'login=login,class=button,text='.we('log in','Anmelden') );
    close_table();
    bigskip();
    hidden_input( 'l', 'login' ); // make 'login' the default action (when pressing ENTER in form)
  close_fieldset();
}

?>
