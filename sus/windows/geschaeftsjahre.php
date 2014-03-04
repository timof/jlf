<?php

sql_transaction_boundary('*','*');

echo html_tag( 'h1', '', 'Geschäftsjahre');

init_var( 'jahr_eingabe', 'type=u,global=1,default=0,sources=http' );

$actions = 'gjMinus, gjPlus';
if( have_priv('*','*') ) {
  $actions .= ', gjMinPlus, gjMaxMinus, gjMaxPlus, gjAbschlussMinus, gjAbschlussPlus';
}
handle_actions( $actions );
$problems = array();
switch( $action ) {

  case 'gjMinus':
    if( $geschaeftsjahr_current <= $geschaeftsjahr_min ) {
      $error_messages += new_problem( "Geschaeftsjahr ist bereits mininmal: $geschaeftsjahr_min" );
    }
    if( ! $error_messages ) {
      sql_update( 'leitvariable', 'name=geschaeftsjahr_current', array( 'value' => --$geschaeftsjahr_current ) );
      logger( "done: geschaeftsjahr_current--; now: $geschaeftsjahr_current", LOG_LEVEL_NOTICE, LOG_FLAG_USER | LOG_FLAG_UPDATE, 'abschluss,geschaeftsjahre' );
    }
    break;
  case 'gjPlus':
    if( $geschaeftsjahr_current >= $geschaeftsjahr_max ) {
      $error_messages += new_problem( "Geschaeftsjahr ist bereits maximal: $geschaeftsjahr_max" );
    }
    if( ! $error_messages ) {
      sql_update( 'leitvariable', 'name=geschaeftsjahr_current', array( 'value' => ++$geschaeftsjahr_current ) );
      logger( "done: geschaeftsjahr_current++; now: $geschaeftsjahr_current", LOG_LEVEL_NOTICE, LOG_FLAG_USER | LOG_FLAG_UPDATE, 'abschluss,geschaeftsjahre' );
    }
    break;

  case 'gjMinPlus':
    if( $geschaeftsjahr_min >= $geschaeftsjahr_current ) {
      $error_messages += new_problem( "minimales Geschaeftsjahr ist noch aktuell: $geschaeftsjahr_current" );
    }
    if( $geschaeftsjahr_min >= $geschaeftsjahr_max ) {
      $error_messages += new_problem( "minimales Geschaeftsjahr ist bereits maximal: $geschaeftsjahr_max" );
    }
    if( ! $error_messages ) {
      sql_delete( 'posten', "geschaeftsjahr=$geschaeftsjahr_min", AUTH );
      sql_delete( 'buchungen', "geschaeftsjahr=$geschaeftsjahr_min", AUTH );
      $geschaeftsjahr_min++;
      sql_update( 'leitvariable', 'name=geschaeftsjahr_min', array( 'value' => $geschaeftsjahr_min ) );
    }
    break;

 case 'gjMaxMinus':
    need( $geschaeftsjahr_current < $geschaeftsjahr_max );
    need( $geschaeftsjahr_abgeschlossen < $geschaeftsjahr_max, 'loeschen nicht moeglich: geschaeftsjahr ist abgeschlossen' );
 
    need( ! sql_buchungen( "geschaeftsjahr=$geschaeftsjahr_max,flag_ausgefuehrt" ), 'loeschen nicht moeglich: buchungen vorhanden' );

    logger( "start: geschaeftsjahr_max--; from: $geschaeftsjahr_max", LOG_LEVEL_DEBUG, LOG_FLAG_USER | LOG_FLAG_UPDATE, 'abschluss,geschaeftsjahre' );
    $geschaeftsjahr_max--;
    sql_update( 'leitvariable', 'name=geschaeftsjahr_max', array( 'value' => $geschaeftsjahr_max ) );
    logger( "done: geschaeftsjahr_max--; now: $geschaeftsjahr_max", LOG_LEVEL_NOTICE, LOG_FLAG_USER | LOG_FLAG_UPDATE, 'abschluss,geschaeftsjahre' );
    break;

  case 'gjMaxPlus':
    logger( "start: geschaeftsjahr_max++; now: $geschaeftsjahr_max", LOG_LEVEL_DEBUG, LOG_FLAG_USER | LOG_FLAG_UPDATE, 'votrag,abschluss,geschaeftsjahre' );
    $geschaeftsjahr = $geschaeftsjahr_max++;
    foreach( sql_hauptkonten( "geschaeftsjahr=$geschaeftsjahr" ) as $hk ) {
      if( ! $hk['hauptkonto_geschlossen'] )
        sql_hauptkonto_folgekonto_anlegen( $hk['hauptkonten_id'] );
    }
    logger( 'geschaeftsjahr_max++: folgekonten hauptkonten angelegt', LOG_LEVEL_DEBUG, LOG_FLAG_INSERT|LOG_FLAG_SYSTEM, 'vortrag,abschluss,hauptkonten,geschaeftsjahre' );
    foreach( sql_unterkonten( "geschaeftsjahr=$geschaeftsjahr" ) as $uk ) {
      if( ! $uk['unterkonto_geschlossen'] )
        sql_unterkonto_folgekonto_anlegen( $uk['unterkonten_id'] );
    }
    logger( 'geschaeftsjahr_max++: folgekonten unterkonten angelegt', LOG_LEVEL_DEBUG, LOG_FLAG_INSERT|LOG_FLAG_SYSTEM, 'vortrag,abschluss,hauptkonten,geschaeftsjahre' );
    sql_saldenvortrag_loeschen( $geschaeftsjahr + 1 );  // sichergehen...
    logger( 'geschaeftsjahr_max++: saldenvortraege geloescht', LOG_LEVEL_DEBUG, LOG_FLAG_DELETE|LOG_FLAG_SYSTEM, 'vortrag,abschluss,buchungen,geschaeftsjahre' );
    sql_saldenvortrag_buchen( $geschaeftsjahr );
    logger( 'geschaeftsjahr_max++: saldenvortraege geloescht', LOG_LEVEL_DEBUG, LOG_FLAG_INSERT|LOG_FLAG_SYSTEM, 'vortrag,abschluss,buchungen,geschaeftsjahre' );
    sql_update( 'leitvariable', 'name=geschaeftsjahr_max', array( 'value' => $geschaeftsjahr_max ) );
    logger( "done: geschaeftsjahr_max++ now: $geschaeftsjahr_max", LOG_LEVEL_NOTICE, LOG_FLAG_USER | LOG_FLAG_UPDATE, 'votrag,abschluss,geschaeftsjahre' );
    break;
  case 'gjAbschlussMinus':
    // fixme: nochmal oeffnen sollte nicht so einfach sein...
    need( $geschaeftsjahr_abgeschlossen >= $geschaeftsjahr_min );
    sql_update( 'leitvariable', 'name=geschaeftsjahr_abgeschlossen', array( 'value' => --$geschaeftsjahr_abgeschlossen ) );
    logger( "done: geschaeftsjahr_abgeschlossen-- now: $geschaeftsjahr_abgeschlossen", LOG_LEVEL_NOTICE, LOG_FLAG_USER | LOG_FLAG_UPDATE, 'abschluss,geschaeftsjahre' );
    break;
  case 'gjAbschlussPlus':
    need( $geschaeftsjahr_abgeschlossen < $geschaeftsjahr_max );
    sql_update( 'leitvariable', 'name=geschaeftsjahr_abgeschlossen', array( 'value' => ++$geschaeftsjahr_abgeschlossen ) );
    logger( "done: geschaeftsjahr_abgeschlossen++ now: $geschaeftsjahr_abgeschlossen", LOG_LEVEL_NOTICE, LOG_FLAG_USER | LOG_FLAG_UPDATE, 'abschluss,geschaeftsjahre' );
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
      echo inlink( '!submit', "class=button,text= > ,inactive=$inactive,confirm=Geschaeftsjahr $geschaeftsjahr_min wirklich loeschen?,action=gjMinPlus" );
  open_tr( 'smallskips' );
    open_th( '', 'abgeschlossen bis einschliesslich:' );
    open_td( 'noright' );
      $inactive = ( $geschaeftsjahr_min >= $geschaeftsjahr_abgeschlossen ) ? 1 : 0;
      echo inlink( '!submit', "class=button,text= < ,inactive=$inactive,confirm=Geschaeftsjahr $geschaeftsjahr_abgeschlossen wieder oeffnen?,action=gjAbschlussMinus" );
    open_td( 'qquads noleft noright', "$geschaeftsjahr_abgeschlossen" );
    open_td( 'noleft' );
      $inactive = ( $geschaeftsjahr_max <= $geschaeftsjahr_abgeschlossen ) ? 1 : 0;
      echo inlink( '!submit', "class=button,text= > ,inactive=$inactive,confirm=Geschaeftsjahr abschliessen?,action=gjAbschlussPlus" );
  open_tr( 'smallskips' );
    open_th( '', 'aktuelles Geschaeftsjahr:' );
    open_td( 'noright' );
      $inactive = ( $geschaeftsjahr_current <= $geschaeftsjahr_min ) ? 1 : 0;
      echo inlink( '!submit', "class=button,text= < ,inactive=$inactive,action=gjMinus" );
    open_td( 'qquads noleft noright', "$geschaeftsjahr_current" );
    open_td( 'noleft' );
      $inactive = ( $geschaeftsjahr_current >= $geschaeftsjahr_max ) ? 1 : 0;
      echo inlink( '!submit', "class=button,text= > ,inactive=$inactive,action=gjPlus" );
  open_tr( 'smallskips' );
    open_th( '', 'letztes Geschaeftsjahr:' );
    open_td( 'noright' );
      $inactive = ( $geschaeftsjahr_current >= $geschaeftsjahr_max ) ? 1 : 0;
      echo inlink( '!submit', "class=button,text= < ,inactive=$inactive,confirm=Geschaeftsjahr $geschaeftsjahr_max wirklich loeschen?,action=gjMaxMinus" );
    open_td( 'qquads noleft noright', "$geschaeftsjahr_max" );
    open_td( 'noleft' );
      echo inlink( '!submit', 'class=button,text= > ,confirm=Neues Geschaeftsjahr anlegen?,action=gjMaxPlus' );
close_table();
bigskip();

geschaeftsjahrelist_view();

?>
