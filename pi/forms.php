<?php

function form_login() {
  global $error_messages;
  open_fieldset( 'table', we('Login','Anmelden') );
    open_caption();
      flush_all_messages();
    close_caption();
    open_tr('');
      open_td( '', we('user-id: ','Benutzerkennung: ') );
      // cannot use string_element() here: would pass uid as P0_uid!
      open_td( 'kbd', html_tag( 'input', 'class=td,type=text,size=12,name=uid,value=', NULL ) );
    open_tr('medskip');
      open_td( '', we('password: ','Passwort: ') );
      open_td( 'kbd', html_tag( 'input', 'class=td,type=password,size=12,name=password,value=', NULL ) );
    open_tr('medskip');
      open_td();
      open_td('right');
        echo inlink( '', 'login=nop,class=button,text='.we('back', 'zurück') );
        quad();
        echo inlink( '', 'login=login,class=button,text='.we('log in','Anmelden') );
  close_fieldset();
    hidden_input( 'l', 'login' ); // make 'login' the default action (when pressing ENTER in form)
}

?>
