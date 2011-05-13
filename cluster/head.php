<?php
  global $angemeldet, $readonly, $foodsoftdir;

$headclass='head';
$payloadclass='';
if( $readonly ) {
  $headclass='headro';
  $payloadclass='ro';
}

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
open_tag( 'html' );
open_tag( 'head' );
?>
  <title>cluster</title>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8' >
  <link rel='stylesheet' type='text/css' href='css/css.css'>
  <script type='text/javascript' src='js/js.js' language='javascript'></script>
<?
close_tag( 'head' );
open_tag( 'body' );

open_div( $headclass, "id='header'" );
  open_table( '', "width='100%'" );
  if( $window_id == 'main' ) {
      open_td();
        open_span('logo');
          open_span( 'logoinvers', '', 'cluster' );
          // echo "...";
        close_span();
      open_td( '', "style='padding-top:1em;'" );
      open_td( '', "style='text-align:right;padding-top:1em;'" );
    open_tr();
      open_td( '', "colspan='3' style='text-align:right;'" );
        open_ul( '', "id='menu' style='margin-bottom:0.5ex;'" );
          hauptmenue_kopfleiste();
        close_ul();
  } else {
    open_td( 'smallskip oneline', "style='width:80px;padding:1ex;'"
                 , "<a class='close' title='Schließen' href='javascript:if(opener)opener.focus();window.close();'>
                    </a><a class='print' title='Ausdrucken' href='javascript:window.print();'>
                    </a><a class='reload' id='reload_button' title='Neu Laden' href='javascript:document.forms.update_form.submit();'>
                    </a>" );
    open_td( 'right', "style='padding:1ex;'", "cluster: $window_id" );
  }
  close_table();
close_div();

open_div( $payloadclass, "id='payload'" );

?>
