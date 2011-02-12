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
    break;
  case 'gjPlus':
    need( $geschaeftsjahr_current < $geschaeftsjahr_max );
    sql_update( 'leitvariable', 'name=geschaeftsjahr_current', array( 'value' => ++$geschaeftsjahr_current ) );
    break;
  case 'gjMinPlus':
    menatwork();
    break;
  case 'gjMaxMinus':
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
    break;
  case 'gjMaxPlus':
    $geschaeftsjahr = $geschaeftsjahr_max++;
    prettydump( 'folgekonten hauptkonten anlegen:');
    foreach( sql_hauptkonten( "geschaeftsjahr=$geschaeftsjahr" ) as $hk ) {
      if( ! $hk['hauptkonto_geschlossen'] )
        sql_hauptkonto_folgekonto_anlegen( $hk['hauptkonten_id'] );
    }
    prettydump( 'folgekonten unterkonten anlegen:');
    foreach( sql_unterkonten( "geschaeftsjahr=$geschaeftsjahr" ) as $uk ) {
      if( ! $uk['unterkonto_geschlossen'] )
        sql_unterkonto_folgekonto_anlegen( $uk['unterkonten_id'] );
    }
    prettydump( 'saldenvortrag loeschen:');
    sql_saldenvortrag_loeschen( $geschaeftsjahr + 1 );  // sichergehen...
    prettydump( 'saldenvortrag buchen:');
    sql_saldenvortrag_buchen( $geschaeftsjahr );
    prettydump( 'leitvariable aktualisieren:');
    sql_update( 'leitvariable', 'name=geschaeftsjahr_max', array( 'value' => $geschaeftsjahr_max ) );
    break;
  case 'gjAbschlussMinus':
    // fixme: nochmal oeffnen sollte nicht so einfach sein...
    need( $geschaeftsjahr_abgeschlossen >= $geschaeftsjahr_min );
    sql_update( 'leitvariable', 'name=geschaeftsjahr_abgeschlossen', array( 'value' => --$geschaeftsjahr_abgeschlossen ) );
    break;
  case 'gjAbschlussPlus':
    need( $geschaeftsjahr_abgeschlossen < $geschaeftsjahr_max );
    sql_update( 'leitvariable', 'name=geschaeftsjahr_abgeschlossen', array( 'value' => ++$geschaeftsjahr_abgeschlossen ) );
    break;
  break;
}

bigskip();

open_table( 'list' );
  open_tr( 'smallskips' );
    open_th( '', '', 'erstes Geschaeftsjahr:' );
    open_td( 'noright' );
    open_td( 'qquads noleft noright', '', "$geschaeftsjahr_min" );
    open_td( 'noleft' );
        echo postaction( array( 'update' => 1, 'class' => 'button', 'text' => ' &gt; ', 'inactive' => ( $geschaeftsjahr_min >= $geschaeftsjahr_current )
                              , 'confirm' => "Jahr $geschaeftsjahr_min wirklich loeschen?" )
                       , array( 'action' => 'gjMinPlus' )
        );
  open_tr( 'smallskips' );
    open_th( '', '', 'abgeschlossen bis einschliesslich:' );
    open_td( 'noright' );
      echo postaction( array( 'update' => 1, 'class' => 'button', 'text' => ' &lt; ', 'inactive' => ( $geschaeftsjahr_min >= $geschaeftsjahr_abgeschlossen )
                            , 'confirm' => "Geschaeftsjahr $geschaeftsjahr_abgeschlossen wieder oeffnen?" )
                     , array( 'action' => 'gjAbschlussMinus' )
      );
    open_td( 'qquads noleft noright', '', "$geschaeftsjahr_abgeschlossen" );
    open_td( 'noleft' );
      echo postaction( array( 'update' => 1, 'class' => 'button', 'text' => ' &gt; ', 'inactive' => ( $geschaeftsjahr_max <= $geschaeftsjahr_abgeschlossen )
                            , 'confirm' => 'geschaeftsjahr abschliessen?' )
                     , array( 'action' => 'gjAbschlussPlus' )
      );
  open_tr( 'smallskips' );
    open_th( '', '', 'aktuelles Geschaeftsjahr:' );
    open_td( 'noright' );
      echo postaction( array( 'update' => 1, 'text' => ' &lt; ', 'class' => 'button', 'inactive' => ( $geschaeftsjahr_current <= $geschaeftsjahr_min ) )
                     , array( 'action' => 'gjMinus' )
      );
    open_td( 'qquads noleft noright', '', "$geschaeftsjahr_current" );
    open_td( 'noleft' );
      echo postaction( array( 'update' => 1, 'text' => ' &gt; ', 'class' => 'button', 'inactive' => ( $geschaeftsjahr_current >= $geschaeftsjahr_max ) )
                     , array( 'action' => 'gjPlus' )
      );
  open_tr( 'smallskips' );
    open_th( '', '', 'letztes Geschaeftsjahr:' );
    open_td( 'noright' );
      echo postaction( array( 'update' => 1, 'class' => 'button', 'text' => ' &lt; ', 'inactive' => ( $geschaeftsjahr_current >= $geschaeftsjahr_max )
                              , 'confirm' => "Jahr $geschaeftsjahr_max wirklich loeschen?" )
                     , array( 'action' => 'gjMaxMinus' )
      );
    open_td( 'qquads noleft noright', '', "$geschaeftsjahr_max" );
    open_td( 'noleft' );
      echo postaction( "update,class=button,text= &gt; ,confirm=Neues Geschaeftsjahr anlegen?"
                          , 'action=gjMaxPlus' );
close_table();
bigskip();

geschaeftsjahrelist_view();

?>
