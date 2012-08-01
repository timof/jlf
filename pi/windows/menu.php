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

bigskip();
bigskip();
bigskip();
bigskip();



$text = '';
for( $i = 1; $i < 20; $i++ ) {
  $text .= html_tag( 'div', 'class=medskips', 'line: '.$i );
}

// $id = confirm_popup( 'bla', array( 'text' => $text ) );


open_div( array(
  'class' => 'alink'
, 'style' => 'position:relative;border:1px solid blue;display:inline;'
, 'onmouseover' => "mouseoverdropdownlink({$H_SQ}theframe{$H_SQ});"
, 'onmouseout' => "mouseoutdropdownlink({$H_SQ}theframe{$H_SQ});"
) );
  open_div( 'class=ngdropdownframe,id=theframe' );
    open_div( 'class=ngdropdown' );
      // open_div( '', 'search field' );
      // open_div( 'nglist,id=nglist', $text );
      echo $text;
    close_div();
    open_div( 'class=ngshadow', 'the shadow' );
  close_div();
  echo "some link";
close_div();

open_div( 'id=msg', 'init' );
open_div( 'id=msg2', 'init' );

// js_on_exit( '$'."({$H_SQ}nglist{$H_SQ}).scrollTop = 280;" );

// open_div('right', html_alink( 'javascript: showpos();', 'text=show' ) );
// js_on_exit( "show_popup( $H_SQ$id$H_SQ );" );

?>
