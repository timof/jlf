<?php

echo html_tag( 'h1', '', 'Konfiguration' );

$fields = array(
  'current_year' => array(
    'type' => 'U4'
  , 'sources' => 'http keep'
  , 'old' => $current_year
  , 'global' => 1
  )
, 'current_term' => array(
    'type' => 'W1'
  , 'sources' => 'http keep'
  , 'old' => $current_term
  , 'pattern' => '/^[WS]$/'
  , 'global' => 1
  )
, 'teaching_survey_open' => array(
    'type' => 'u1'
  , 'sources' => 'http keep'
  , 'old' => $teaching_survey_open
  , 'pattern' => '/^[01]$/'
  , 'global' => 1
  )
, 'teaching_survey_year' => array(
    'type' => 'U4'
  , 'sources' => 'http keep'
  , 'old' => $teaching_survey_year
  , 'global' => 1
  )
, 'teaching_survey_term' => array(
    'type' => 'W1'
  , 'sources' => 'http keep'
  , 'old' => $teaching_survey_term
  , 'pattern' => '/^[WS]$/'
  , 'global' => 1
  )
);

$f = init_fields( $fields, 'failsafe=0' );
debug( $f, 'f' );

return 42;

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
