<?php

function form_login() {
  open_fieldset( 'class=small_form,style=padding:2em;', we('Login','Anmelden') );
    if( $GLOBALS['valid_cookie_received'] ) {
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
    } else {
      open_div( 'warn bigskips qquads' , we(" You seem to have disabled cookies in your browser. Please switch on cookie support for this site!  "
                                         , " Cookie-Unterst&uuml;tzung ihres Browsers scheint ausgeschaltet zu sein. Bitte erlauben Sie Cookies f&uum;r diese Webseite! " ) );

      open_div( 'right' );
        submission_button( 'login=nop,text='.we('back', 'zurück') );
      close_div();
    }
      bigskip();
  close_fieldset();
}

?>
