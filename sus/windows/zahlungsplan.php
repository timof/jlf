<?php

init_var( 'options', 'global,pattern=u,sources=http persistent,set_scopes=window,default=0' );

if( $parent_script !== 'self' ) {
  $reinit = 'init';  // generate empty entry, plus initialization from http
} else if( $action === 'reset' ) {
  $reinit = 'reset'; // re-initialize from db, or generate empty entry
} else {
  $reinit = 'http';
}

init_var( 'zahlungsplan_id', 'global,pattern=u,sources=http persistent,default=0,set_scopes=self' );
if( $zahlungsplan_id ) {
  $zahlungsplan = sql_one_zahlungsplan( $zahlungsplan_id );
  $darlehen_id = $zahlungsplan['darlehen_id'];
} else {
  $zahlungsplan = array();
  init_var( 'darlehen_id', 'global,pattern=U,sources=http persistent,set_scopes=self' );
}
$darlehen = sql_one_darlehen( $darlehen_id );


do {

  switch( $reinit ) {
    case 'init':
      init_var( 'darlehen_id', 'global,pattern=u,sources=http,default=0,set_scopes=self' );




function init() {
  global $zahlungsplan_id, $zpposten, $unterkonten_id, $uk;
  global $problems;

  init_global_var( 'zahlungsplan_id', 'u', 'http,persistent', 0, 'self' );
  $zpposten = array();
  if( $zahlungsplan_id ) {
    $zahlungsplan = sql_one_zahlungsplan( $zahlungsplan_id );
    $unterkonten_id = $zahlungsplan['unterkonten_id'];
    $zpposten = sql_zpposten( array( 'zahlungsplan_id' => $zahlungsplan_id ) );
  } else {
    init_global_var( 'unterkonten_id', 'U', 'http,persistent', 'self' );
  }
  $uk = sql_one_unterkonto( $unterkonten_id );
}


init();
handle_action( 'init', 'update', 'save', 'compute' );

switch( $action ) {
  case '':
  case 'nop':
    break;
  case 'drop_zpposten':
    need( $zahlungsplan_id );
    sql_delete( 'zpposten', array( 'zahlungsplan_id' => $zahlungsplan_id ) );
    $zpposten = array();
    break;
  case 'drop_zp':
    need( $zahlungsplan_id );
    sql_delete( 'zpposten', array( 'zahlungsplan_id' => $zahlungsplan_id ) );
    sql_delete( 'zahlungsplan', $zahlungsplan_id );
    $zpposten = array();
    $zahlungsplan_id = 0;
    break;
  case 'compute':
    need( $zahlungsplan_id );
    need( ! $zpposten );
    compute_zahlungsplan();
    break;
  case 'save':
    case 'update':
    need( ! $zpposten );
    
  
}






echo html_tag( 'h1', '', 'Unterkonto '.inlink( 'unterkonto', array( 'unterkonten_id' => $unterkonten_id, 'text' => $uk['cn'] ) ). ' --- Zahlungsplan' );


if( ! $zpposten ) {

    open_div( 'smallskip' );
      if( $zhlungsplan ) {
        open_span( 'qquad', inlink( '', array(
            'class' => 'button', 'text' => 'Zahlungsplan berechnen'
          , 'action' => 'compute', 'update' => 1
        ) ) );
      }
      open_span( 'qquad', action_button_view( 'action=compute,Zahlungsplan berechnen' ) );
    close_div();
} else {


      
  // open_fieldset( 'small_form', 'Rahmendaten Zahlungsplan' );



  bigskip();
  open_table( 'list' );
    open_tr();
      open_th( '', 'Valuta', 'valuta', $p_ );
      open_th( '', 'Soll', 'soll', $p_ );
      open_th( '', 'Haben', 'haben', $p_ );
      open_th( '', 'Gegenkonto', 'unterkonto', $p_ );
      open_th( '', 'Aktionen' );

    foreach( $zpposten as $p ) {
      $gegenkonto_id = $p['unterkonten_id'];
      $gegenkonto = sql_one_unterkonto( $gegenkonto_id );
      open_tr();
        open_td( 'right', date_weird2canonical( $p['valuta'] ) );
        switch( $p['art'] ) {
          case 'S':
            open_td( 'number', price_view( $p['betrag'] ) );
            open_td( '', ' ' );
            break;
          case 'H':
            open_td( '', ' ' );
            open_td( 'number', price_view( $p['betrag'] ) );
            break;
        }
        open_td( 'left', inlink( 'unterkonto', array( 'unterkonten_id' => $gegenkonto_id, 'text' => $gegenkonto['cn'] ) ) );
        open_td();
          echo inlink( '!submit', 'class=drop,action=delete,message='.$p['zpposten_id'] );
          $valuta = date_weird2canonical( $p['valuta'] );
          echo inlink( 'buchung', array(
            'text' => 'Buchung ausf'.H_AMP.'uuml;hren', 'action' => 'update'
          , 'nS' => 1, 'nH' => 1,  'valuta' => $valuta
          , 'pS1_betrag' => $p['betrag']
          , 'pS1_unterkonten_id' => ( $p['art'] == 'S' ? $unterkonten_id : $gegenkonten_id )
          , 'pH1_betrag' => $p['betrag']
          , 'pH1_unterkonten_id' => ( $p['art'] == 'H' ? $unterkonten_id : $gegenkonten_id )
          ) );
    }

  close_table();



}
    




