<?php

need( $geschaeftsjahr_min > 0, 'leitvariable not set: geschaeftsjahr_min' );
need( $geschaeftsjahr_max >= $geschaeftsjahr_min, 'problem: leitvariable geschaeftsjahr_max' );
need( ( $geschaeftsjahr_current >= $geschaeftsjahr_min ) && ( $geschaeftsjahr_current <= $geschaeftsjahr_max )
      , 'problem: leitvariable geschaeftsjahr_current' );
need( $geschaeftsjahr_abgeschlossen > 0, 'leitvariable not set: geschaeftsjahr_abgeschlossen' );

init_var( 'geschaeftsjahr_thread', array(
  'type' => 'u'
, 'sources' => 'http thread initval'
, 'initval' => $geschaeftsjahr_current
, 'min' => $geschaeftsjahr_min
, 'max' => $geschaeftsjahr_max
, 'set_scopes' => 'thread'
, 'global' => true
) );

require_once( "sus/hgb_klassen.php" );

$autovortragskonten = parameters_explode( $autovortragskonten );

// if( $default_girokonto_id ) {
//   if( ! ( $default_girokonto = sql_one_unterkonto( $default_girokonto_id, 'default=0,authorized=1' ) ) ) {
//     $default_girokonto_id = 0;
//     sql_update( 'leitvariable', 'name=default_girokonto_id', array( 'value' => 0 ) );
//   }
// }
// 
// if( $default_erfolgskonto_zinsaufwand_id ) {
//   if( ! ( $default_erfolgskonto_zinsaufwand = sql_one_unterkonto( $default_erfolgskonto_zinsaufwand_id, 'default=0,authorized=1' ) ) ) {
//     $default_erfolgskonto_zinsaufwand_id = 0;
//     sql_update( 'leitvariable', 'name=default_erfolgskonto_zinsaufwand_id', array( 'value' => 0 ) );
//   }
// }

$choices_status_person = array(
  'om' => 'ordentliches Mitglied'
, 'fm' => 'Foerdermitglied'
, 'nu' => 'Nutzer'
, 'lf' => 'Lieferant'
, 'xx' => 'andere'
);


?>
