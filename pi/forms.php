<?php

function form_login() {
  open_fieldset( 'class=small_form,style=padding:2em;', we('Login','Anmelden') );
    flush_problems();
    hidden_input( 'l', 'login' );
    bigskip();
    open_table('small_form');
      open_tr('medskip');
        open_td( 'label quads', we('user-id: ','Benutzerkennung: ') );
        open_td( 'kbd', string_element( array( 'name' => 'uid', 'size' => 20 ) ) );
      open_tr('medskip');
        open_td( 'label quads', we('password: ','Passwort: ') );
        open_td( 'kbd', html_tag( 'input', 'type=password,size=8,name=password,value=', NULL ) );
      open_tr('medskip');
        open_td();
        open_td('right');
          submission_button( 'login=nop,text='.we('back', 'zurück') );
          quad();
          submission_button( 'action=,text='.we('log in','Anmelden') );
    close_table();
    bigskip();
  close_fieldset();
}

?>
