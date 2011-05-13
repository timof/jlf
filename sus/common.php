<?php

need( $geschaeftsjahr_min > 0, 'leitvariable not set: geschaeftsjahr_min' );
need( $geschaeftsjahr_max >= $geschaeftsjahr_min, 'problem: leitvariable geschaeftsjahr_max' );
need( ( $geschaeftsjahr_current >= $geschaeftsjahr_min ) && ( $geschaeftsjahr_current <= $geschaeftsjahr_max )
      , 'problem: leitvariable geschaeftsjahr_current' );
need( $geschaeftsjahr_abgeschlossen > 0, 'leitvariable not set: geschaeftsjahr_abgeschlossen' );

init_global_var( 'geschaeftsjahr_thread', 'u', 'http,persistent', $geschaeftsjahr_current, 'thread' );

require_once( "sus/hgb_klassen.php" );

?>
