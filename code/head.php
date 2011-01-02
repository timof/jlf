<?php

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
open_tag( 'html' );
open_tag( 'head' );
  echo "
    <title>$application_name $application_instance</title>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' >
    <link rel='stylesheet' type='text/css' href='code/css.css'>
    <script type='text/javascript' src='code/js.js' language='javascript'></script>
  ";
close_tag( 'head' );
open_tag( 'body' );

open_div( $readonly ? 'head,ro' : 'head' , "id='header'" );
  open_table( '', "width='100%'" );
  if( $window_id == 'main' ) {
      open_td( 'left', "colspan='2'" );
        open_span('logo');
          open_span( 'logoinvers', '', "$jlf_application_name $jlf_application_instance" );
          // echo "...";
        close_span();
      open_td( '', "style='padding-top:1em;'" );
      open_td( 'right', "style='padding-top:1em;'", $logged_in ? postaction( 'text=logout...', 'login=logout' ) : '' );
    open_tr();
      open_td( 'left' );
      if( $logged_in )
        echo "<a class='reload' id='reload_button' title='reload' href='javascript:document.forms.update_form.submit();'> </a>&nbsp;";
      open_td( ' right', "colspan='3'" );
        open_ul( '', "id='menu' style='margin-bottom:0.5ex;'" );
          mainmenu_header();
        close_ul();
  } else {
    open_td( 'smallskip oneline', "style='width:80px;padding:1ex;'"
                 , "<a class='close' title='Schließen' href='javascript:if(opener)opener.focus();window.close();'>
                    </a><a class='print' title='Ausdrucken' href='javascript:window.print();'>
                    </a><a class='reload' id='reload_button' title='Neu Laden' href='javascript:document.forms.update_form.submit();'>
                    </a>" );
    open_td( 'right', "style='padding:1ex;'", "$jlf_application_name $jlf_application_instance: $window_id" );
  }
  close_table();
close_div();

open_div( $readonly ? 'payload,ro' : 'payload' , "id='payload'" );

?>
