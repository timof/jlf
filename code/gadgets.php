<?php

if( ! function_exists( 'html_options_people' ) ) {
  function html_options_people( $selected = 0, $filters = array(), $option_0 = false ) {
    if( $option_0 )
      $options[0] = $option_0;
    foreach( sql_people( $filters ) as $p ) {
      $id = $k['people_id'];
      $options[$id] = $p['cn'];
    }
    $output = html_options( & $selected, $options );
    if( $selected != -1 )
      $output = "<option value='0' selected>(Person waehlen)</option>" . $output;
    return $output;
  }
}

function filter_thread( $prefix = '', $option_0 = '(alle)' ) {
  global $form_id, $thread;

  $f = $prefix.'f_thread';
  $g = & $GLOBALS[ $f ];

  $g = max( min( $g, 4 ), 0 );

  if( $g ) {
    selector_int( $g, $f, 0, 4 );
    open_span( 'quads' );
    if( $option_0 ) {
      if( $form_id ) {
        echo inlink( '', array( 'class' => 'button', 'text' => "$option_0", 'url' => "javascript:submit_form('form_$form_id', '$f', '0' );" ) );
      } else {
        echo inlink( '', array( 'class' => 'button', 'text' => "$option_0", $f => 0 ) );
      }
    }
    close_span();
  } else {
    open_span( 'quads', '', ' (alle) ' );
    open_span( 'quads' );
      if( $form_id ) {
        echo inlink( '', array(
          'class' => 'button', 'text' => 'Filter...'
        , 'url' => "javascript:submit_form('form_$form_id', '$f', '$thread' );" ) );
      } else {
        echo inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $f => $thread ) );
      }
    close_span();
  }
}

?>
