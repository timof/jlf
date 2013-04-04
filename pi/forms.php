<?php

function form_login() {
  open_fieldset( '', we('Login','Anmelden') );
    flush_problems();
    bigskip();
    open_table('fieldset,css=1');
      open_tr('');
        open_label( 'uid', 'td', we('user-id: ','Benutzerkennung: ') );
        // cannot use string_element() here: would pass uid as P0_uid!
        open_td( 'kbd', html_tag( 'input', 'class=td,type=text,size=12,name=uid,value=', NULL ) );
      open_tr('medskip');
        open_label( 'password', 'td', we('password: ','Passwort: ') );
        open_td( 'kbd', html_tag( 'input', 'class=td,type=password,size=12,name=password,value=', NULL ) );
      open_tr('medskip');
        open_td();
        open_td('right');
          echo inlink( '', 'login=nop,class=button,text='.we('back', 'zurück') );
          quad();
          echo inlink( '', 'login=login,class=button,text='.we('log in','Anmelden') );
    close_table();
    hidden_input( 'l', 'login' ); // make 'login' the default action (when pressing ENTER in form)
  close_fieldset();
}

?>
