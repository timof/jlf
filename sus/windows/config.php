<?php

echo html_tag( 'h1', '', 'Konfiguration' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields( array( 'F_sessions_id', 'F_thread', 'F_window', 'F_script' ) );

handle_action( array( 'update', 'deletePersistentVar' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
  case 'deletePersistentVar':
    need( $message );
    $var = sql_persistent_vars( $message );
    if( ! $var )
      break;
    need( ( $var['people_id'] == 0 ) || ( $var['people_id'] == $login_people_id ) );
    sql_delete_persistent_vars( $message );
    break;
}

open_table( 'menu' );
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( 'right', 'thread:' );
    open_td();
      filter_thread( $fields['F_thread'] );
close_table();

bigskip();

$filters = $fields['_filters'];



$then = gmdate( 'Ymd.His', time() - 20 );
open_div( '', 'now: '.$utc );
open_div( '', 'then: '.$then );

$fields = array(
  'default_girokonto_id' => array(
    'type' => 'u'
  , 'sources' => 'http keep'
  , 'old' => $default_girokonto_id
  , 'global' => 1
  )
, 'default_erfolgskonto_zinsaufwand_id' => array(
    'type' => 'u'
  , 'sources' => 'http keep'
  , 'old' => $default_erfolgskonto_zinsaufwand_id
  , 'global' => 1
  )
);
$f = init_fields( $fields );
debug( $f, 'f' );
debug( $default_girokonto_id, 'default_girokonto_id' );
if( isset( $f['_changes']['default_girokonto_id'] ) ) {
  sql_update( 'leitvariable', 'name=default_girokonto_id', array( 'value' => $f['default_girokonto_id']['value'] ) );
}
if( isset( $f['_changes']['default_erfolgskonto_zinsaufwand_id'] ) ) {
  sql_update( 'leitvariable', 'name=default_erfolgskonto_zinsaufwand_id', array( 'value' => $f['default_erfolgskonto_zinsaufwand_id']['value'] ) );
}

  
open_table( 'hfill list' );
  
  open_tr( 'medskip' );
    open_th( '', 'default Girokonto:' );
    open_td();
      selector_unterkonto( $f['default_girokonto_id'], array(
        'filters' => "seite=A,kontenkreis=B,bankkonto,geschaeftsjahr=$geschaeftsjahr_thread"
      , 'more_choices' => array( 0 => ' (keins) ' )
      ) );

  open_tr( 'medskip' );
    open_th( '', 'default Erfolgskonto Zinsaufwand:' );
    open_td();
      selector_unterkonto( $f['default_erfolgskonto_zinsaufwand_id'], array(
        'filters' => "seite=A,kontenkreis=E,geschaeftsjahr=$geschaeftsjahr_thread"
      , 'more_choices' => array( 0 => ' (keins) ' )
      ) );

close_table();

$sessions = sql_sessions( "atime<$then" );
foreach( $sessions as $s ) {
  debug( $s, 'session' );
  $id = $s['sessions_id'];
  // sql_delete( 'persistent_vars', 'sessions_id=$id' );
  // sql_delete( 'sessions', $id );
}

smallskip();

open_fieldset( 'small_form', 'maintenance', 'on' );
  persistent_vars_view( "name=thread_atime,value<$then" );
close_fieldset();


open_fieldset( 'small_form', 'persistent variables', 'on' );
  persistent_vars_view( $filters );
close_fieldset();

?>
