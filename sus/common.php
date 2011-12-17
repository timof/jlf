<?php

need( $geschaeftsjahr_min > 0, 'leitvariable not set: geschaeftsjahr_min' );
need( $geschaeftsjahr_max >= $geschaeftsjahr_min, 'problem: leitvariable geschaeftsjahr_max' );
need( ( $geschaeftsjahr_current >= $geschaeftsjahr_min ) && ( $geschaeftsjahr_current <= $geschaeftsjahr_max )
      , 'problem: leitvariable geschaeftsjahr_current' );
need( $geschaeftsjahr_abgeschlossen > 0, 'leitvariable not set: geschaeftsjahr_abgeschlossen' );

init_var( 'geschaeftsjahr_thread', array(
  'pattern' => 'u'
, 'sources' => 'http persistent'
, 'default' => $geschaeftsjahr_current
, 'set_scopes' => 'thread'
, 'global' => true
) );

require_once( "sus/hgb_klassen.php" );

if( $default_girokonto_id ) {
  if( ! ( $default_girokonto = sql_one_unterkonto( $default_girokonto_id, NULL ) ) ) {
    $default_girokonto_id = 0;
    sql_update( 'leitvariable', 'name=default_girokonto_id', array( 'value' => 0 ) );
  } else {
    if( $default_girokonto['geschaeftsjahr'] != $geschaeftsjahr_current ) {
      if( $n = sql_get_folge_unterkonten_id( $default_girokonto_id, $geschaeftsjahr_current ) ) {
        $default_girokonto_id = $n;
        sql_update( 'leitvariable', 'name=default_girokonto_id', array( 'value' => $n ) );
      }
    }
  }
}

if( $default_erfolgskonto_zinsaufwand_id ) {
  if( ! ( $default_erfolgskonto_zinsaufwand = sql_one_unterkonto( $default_erfolgskonto_zinsaufwand_id, NULL ) ) ) {
    $default_erfolgskonto_zinsaufwand_id = 0;
    sql_update( 'leitvariable', 'name=default_erfolgskonto_zinsaufwand_id', array( 'value' => 0 ) );
  } else {
    if( $default_erfolgskonto_zinsaufwand['geschaeftsjahr'] != $geschaeftsjahr_current ) {
      if( $n = sql_get_folge_unterkonten_id( $default_erfolgskonto_zinsaufwand_id, $geschaeftsjahr_current ) ) {
        $default_erfolgskonto_zinsaufwand_id = $n;
        sql_update( 'leitvariable', 'name=default_erfolgskonto_zinsaufwand_id', array( 'value' => $n ) );
      }
    }
  }
}

?>
