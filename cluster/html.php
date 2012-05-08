<?php

function html_options_hosts( $selected = 0, $filters = array(), $option_0 = false ) {
  if( $option_0 )
    $options[0] = $option_0;
  foreach( sql_hosts( $filters ) as $host ) {
    $options[ $host['hosts_id'] ] = $host['fqhostname'];
  }
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='0' selected>(select host)</option>" . $output;
  return $output;
}

function filter_host( $prefix = '', $option_0 = '(alle)' ) {
  global $form_id;
  if( $prefix )
    $prefix = $prefix.'_';
  if( ! isset( $GLOBALS[$prefix.'hosts_id'] ) )
    $GLOBALS[$prefix.'hosts_id'] = 0;
  open_select( $prefix.'hosts_id', '', html_options_hosts( $GLOBALS[$prefix.'hosts_id'], $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_locations( $selected = 0, $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options[0] = $option_0;
  $options += sql_unique_values( 'hosts', 'location' );
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='0' selected>(select location)</option>" . $output;
  return $output;
}

function filter_location( $prefix = '', $option_0 = '(alle)' ) {
  global $form_id;
  if( $prefix )
    $prefix = $prefix.'_';
  if( ! isset( $GLOBALS[$prefix.'locations_id'] ) )
    $GLOBALS[$prefix.'locations_id'] = 0;
  open_select( $prefix.'locations_id', '', html_options_locations( $GLOBALS[$prefix.'locations_id'], $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_type_disk( $selected = 0, $option_0 = false ) {
  if( $option_0 )
    $options[0] = $option_0;
  foreach( array( 'P-ATA', 'P-SCSI', 'S-ATA', 'SAS' ) as $t )
    $options[$t] = $t;
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='0' selected>(select type)</option>" . $output;
  return $output;
}

function filter_type_disk( $prefix = '', $option_0 = '(alle)' ) {
  global $form_id;
  if( $prefix )
    $prefix = $prefix.'_';
  if( ! isset( $GLOBALS[$prefix.'type_disk'] ) )
    $GLOBALS[$prefix.'type_disk'] = 0;
  open_select( $prefix.'type_disk', '', html_options_type_disk( $GLOBALS[$prefix.'type_disk'], $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_type_tape( $selected = 0, $option_0 = false ) {
  if( $option_0 )
    $options[0] = $option_0;
  foreach( array( 'P-ATA', 'P-SCSI', 'S-ATA', 'SAS' ) as $t )
    $options[$t] = $t;
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='0' selected>(select type)</option>" . $output;
  return $output;
}

function filter_type_tape( $prefix = '', $option_0 = '(alle)' ) {
  global $form_id;
  if( $prefix )
    $prefix = $prefix.'_';
  if( ! isset( $GLOBALS[$prefix.'type_tape'] ) )
    $GLOBALS[$prefix.'type_tape'] = 0;
  open_select( $prefix.'type_tape', '', html_options_type_tape( $GLOBALS[$prefix.'type_tape'], $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_accountdomains( $selected = 0, $filters = array(), $option_0 = false ) {
  if( $option_0 )
    $options[0] = $option_0;
  foreach( sql_accountdomains( $filters ) as $l )
    $options[ $l['accountdomains_id'] ] = $l['accountdomain'];
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='0' selected>(select accountdomain)</option>" . $output;
  return $output;
}

function filter_accountdomain( $prefix = '', $option_0 = '(alle)' ) {
  global $form_id;
  if( $prefix )
    $prefix = $prefix.'_';
  if( ! isset( $GLOBALS[$prefix.'accountdomain'] ) )
    $GLOBALS[$prefix.'accountdomain'] = 0;
  open_select( $prefix.'accountdomain', '', html_options_accountdomains( $GLOBALS[$prefix.'accountdomain'], $option_0 ), $form_id ? 'submit' : 'reload' );
}

?>
