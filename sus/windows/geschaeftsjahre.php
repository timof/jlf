<?php

echo "<h1>Geschaeftsjahre</h1>";

handle_action( array( 'update', 'gjMinus', 'gjPlus', 'gjMinPlus', 'gjMaxMinus', 'gjMaxPlus', 'gjAbschlussMinus', 'gjAbschlussPlus' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
  case 'gjMinus':
    need( $geschaeftsjahr_current > $geschaeftsjahr_min );
    sql_update( 'leitvariable', 'name=geschaeftsjahr_current', array( 'value' => --$geschaeftsjahr_current ) );
    logger( "done: geschaeftsjahr_current--; now: $geschaeftsjahr_current", 'vortrag' );
    break;
  case 'gjPlus':
    need( $geschaeftsjahr_current < $geschaeftsjahr_max );
    sql_update( 'leitvariable', 'name=geschaeftsjahr_current', array( 'value' => ++$geschaeftsjahr_current ) );
    logger( "done: geschaeftsjahr_current++; now: $geschaeftsjahr_current", 'vortrag' );
    break;
  case 'gjMinPlus':
    menatwork();
    break;
  case 'gjMaxMinus':
    logger( 'start: geschaeftsjahr_max--', 'info' );
    need( $geschaeftsjahr_current < $geschaeftsjahr_max );
    need( $geschaeftsjahr_abgeschlossen < $geschaeftsjahr_max, 'loeschen nicht moeglich: geschaeftsjahr ist abgeschlossen' );
    need( ! sql_buchungen( "geschaeftsjahr=$geschaeftsjahr_max" ), 'loeschen nicht moeglich: buchungen vorhanden' );
    foreach( sql_unterkonten( array( 'geschaeftsjahr' => $geschaeftsjahr_max - 1 ) ) as $uk ) {
      sql_update( 'unterkonten', $uk['unterkonten_id'], array( 'folge_unterkonten_id' => 0 ) );
    }
    foreach( sql_unterkonten( "geschaeftsjahr=$geschaeftsjahr_max" ) as $uk ) {
      sql_delete_unterkonten( $uk['unterkonten_id'] );
    }
    sql_update( 'hauptkonten', array( 'geschaeftsjahr' => $geschaeftsjahr_max - 1 ), array( 'folge_hauptkonten_id' => 0 ) );
    foreach( sql_hauptkonten( "geschaeftsjahr=$geschaeftsjahr_max" ) as $hk ) {
      sql_delete_hauptkonten( $hk['hauptkonten_id'] );
    }
    sql_update( 'leitvariable', 'name=geschaeftsjahr_max', array( 'value' => --$geschaeftsjahr_max ) );
    logger( "done: geschaeftsjahr_max--; now: $geschaeftsjahr_max", 'vortrag' );
    break;
  case 'gjMaxPlus':
    logger( 'start: geschaeftsjahr_max++', 'vortrag' );
    $geschaeftsjahr = $geschaeftsjahr_max++;
    foreach( sql_hauptkonten( "geschaeftsjahr=$geschaeftsjahr" ) as $hk ) {
      if( ! $hk['hauptkonto_geschlossen'] )
        sql_hauptkonto_folgekonto_anlegen( $hk['hauptkonten_id'] );
    }
    logger( 'geschaeftsjahr_max++: folgekonten hauptkonten angelegt', 'vortrag' );
    foreach( sql_unterkonten( "geschaeftsjahr=$geschaeftsjahr" ) as $uk ) {
      if( ! $uk['unterkonto_geschlossen'] )
        sql_unterkonto_folgekonto_anlegen( $uk['unterkonten_id'] );
    }
    logger( 'geschaeftsjahr_max++: folgekonten unterkonten angelegt', 'vortrag' );
    sql_saldenvortrag_loeschen( $geschaeftsjahr + 1 );  // sichergehen...
    logger( 'geschaeftsjahr_max++: saldenvortraege geloescht', 'vortrag' );
    sql_saldenvortrag_buchen( $geschaeftsjahr );
    logger( 'geschaeftsjahr_max++: saldenvortraege gebucht', 'vortrag' );
    sql_update( 'leitvariable', 'name=geschaeftsjahr_max', array( 'value' => $geschaeftsjahr_max ) );
    logger( "done: geschaeftsjahr_max++; now: $geschaeftsjahr_max", 'vortrag' );
    break;
  case 'gjAbschlussMinus':
    // fixme: nochmal oeffnen sollte nicht so einfach sein...
    need( $geschaeftsjahr_abgeschlossen >= $geschaeftsjahr_min );
    sql_update( 'leitvariable', 'name=geschaeftsjahr_abgeschlossen', array( 'value' => --$geschaeftsjahr_abgeschlossen ) );
    logger( "done: geschaeftsjahr_abgeschlossen--; now: $geschaeftsjahr_abgeschlossen", 'abschluss' );
    break;
  case 'gjAbschlussPlus':
    need( $geschaeftsjahr_abgeschlossen < $geschaeftsjahr_max );
    sql_update( 'leitvariable', 'name=geschaeftsjahr_abgeschlossen', array( 'value' => ++$geschaeftsjahr_abgeschlossen ) );
    logger( "done: geschaeftsjahr_abgeschlossen++; now: $geschaeftsjahr_abgeschlossen", 'abschluss' );
    break;
  break;
}

bigskip();

open_table( 'list' );
  open_tr( 'smallskips' );
    open_th( '', 'erstes Geschaeftsjahr:' );
    open_td( 'noright' );
    open_td( 'qquads noleft noright', "$geschaeftsjahr_min" );
    open_td( 'noleft' );
      $inactive = ( $geschaeftsjahr_min >= $geschaeftsjahr_current ) ? 1 : 0;
      echo inlink( '!submit', "class=button,text= &gt; ,inactive=$inactive,confirm=Geschaeftsjahr $geschaeftsjahr_min wirklich loeschen?,action=gjMinPlus" );
  open_tr( 'smallskips' );
    open_th( '', 'abgeschlossen bis einschliesslich:' );
    open_td( 'noright' );
      $inactive = ( $geschaeftsjahr_min >= $geschaeftsjahr_abgeschlossen ) ? 1 : 0;
      echo inlink( '!submit', "class=button,text= &lt; ,inactive=$inactive,confirm=Geschaeftsjahr $geschaeftsjahr_abgeschlossen wieder oeffnen?,action=gjAbschlussMinus" );
    open_td( 'qquads noleft noright', "$geschaeftsjahr_abgeschlossen" );
    open_td( 'noleft' );
      $inactive = ( $geschaeftsjahr_max <= $geschaeftsjahr_abgeschlossen ) ? 1 : 0;
      echo inlink( '!submit', "class=button,text= &gt; ,inactive=$inactive,confirm=Geschaeftsjahr abschliessen?,action=gjAbschlussPlus" );
  open_tr( 'smallskips' );
    open_th( '', 'aktuelles Geschaeftsjahr:' );
    open_td( 'noright' );
      $inactive = ( $geschaeftsjahr_current <= $geschaeftsjahr_min ) ? 1 : 0;
      echo inlink( '!submit', "class=button,text= &lt; ,inactive=$inactive,action=gjMinus" );
    open_td( 'qquads noleft noright', "$geschaeftsjahr_current" );
    open_td( 'noleft' );
      $inactive = ( $geschaeftsjahr_current >= $geschaeftsjahr_max ) ? 1 : 0;
      echo inlink( '!submit', "class=button,text= &gt; ,inactive=$inactive,action=gjPlus" );
  open_tr( 'smallskips' );
    open_th( '', 'letztes Geschaeftsjahr:' );
    open_td( 'noright' );
      $inactive = ( $geschaeftsjahr_current >= $geschaeftsjahr_max ) ? 1 : 0;
      echo inlink( '!submit', "class=button,text= &lt; ,inactive=$inactive,confirm=Geschaeftsjahr $geschaeftsjahr_max wirklich loeschen?,action=gjMaxMinus" );
    open_td( 'qquads noleft noright', "$geschaeftsjahr_max" );
    open_td( 'noleft' );
      echo inlink( '!submit', 'class=button,text= &gt; ,confirm=Neues Geschaeftsjahr anlegen?,action=gjMaxPlus' );
close_table();
bigskip();

geschaeftsjahrelist_view();

?>
