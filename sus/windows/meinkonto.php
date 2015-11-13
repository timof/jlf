<?php

sql_transaction_boundary('*');

init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window,default=0' );

$field_geschaeftsjahr = init_var( 'geschaeftsjahr', "global,type=u,sources=http persistent initval,default=$geschaeftsjahr_thread,min=$geschaeftsjahr_min,max=$geschaeftsjahr_max,allow_null=0,set_scopes=self" );
$field_valuta_von = init_var( 'valuta_von', 'global,type=u,sources=http persistent initval,default=100,min=100,max=1299,set_scopes=self' );
$field_valuta_bis = init_var( 'valuta_bis', 'global,type=u,sources=http persistent initval,default=1299,initval=1231,min=100,max=1299,set_scopes=self' );
if( $valuta_von > $valuta_bis ) {
  if( $field_valuta_von['source'] == 'http' ) {
    $valuta_bis = $valuta_von;
  } else {
    $valuta_von = $valuta_bis;
  }
}

init_var( 'unterkonten_id', 'global,type=U,sources=http persistent,default=0,set_scopes=self' );
need_priv( 'unterkonten', 'read', $unterkonten_id );

$uk = sql_one_unterkonto( $unterkonten_id, AUTH );
$hauptkonten_id = $uk['hauptkonten_id'];
$hk = sql_one_hauptkonto( $hauptkonten_id, AUTH );



open_fieldset( 'old', "Konto: {$uk['cn']}" );

  open_fieldset( '', 'Stammdaten' );

    open_table('css td:bottom;quads;tinypads');
  
      open_tr();
        open_td( '', 'Konto:' );
        open_td( 'bold', $uk['cn'] );
  
      open_tr();
        open_td( '', 'Hauptkonto:' );
        open_td( 'bold', "{$hk['kontenkreis']} {$hk['seite']} {$hk['rubrik']} / {$hk['titel']}" );
      open_tr();
        open_td( '', 'Kontoklasse:' );
        open_td( 'bold', "{$hk['kontoklassen_cn']} {$hk['geschaeftsbereich']}" );
      open_tr();
        open_td( '', 'Attribute:' );
        open_td( 'bold', kontoattribute_view( $uk ) );
      open_tr();
        open_td( '', 'Status:' );
        open_td( 'bold', $uk['flag_unterkonto_offen'] ? 'offen' : 'geschlossen' );
  
      if( $hk['flag_bankkonto'] ) {
        open_tr();
          open_td( '', 'Bank:' );
          open_td( 'bold', $uk['bank_cn'] );
        open_tr();
          open_td( '', 'IBAN:' );
          open_td( 'bold', $uk['bank_iban'] );
      }
      if( $hk['flag_personenkonto'] ) {
        open_tr();
          open_td( '', 'Person:' );
          open_td( 'bold', inlink( 'person', array( 'class' => 'href', 'peope_id' => $uk['people_id'], 'text' => $uk['people_cn'] ) ) );
      }
  
    close_table();

  close_fieldset();

  open_fieldset( '', 'Buchungen' );

    open_table('css td:bottom;quads;tinypads');
    
      open_tr('td:smallpads' );
        open_td( '', "Gesch{$aUML}ftsjahr: "  );
        open_td( '', filter_geschaeftsjahr( $field_geschaeftsjahr ) );
        if( $geschaeftsjahr ) {
          open_tr();
            open_th( '', 'von:' );
            open_td( '', selector_valuta( $field_valuta_von ) );
          open_tr();
            open_th( '', 'bis:' );
            open_td( '', selector_valuta( $field_valuta_bis ) );
        }
    close_table();

    if( $geschaeftsjahr ) {
  
      $filters = array(
        'unterkonten_id' => $unterkonten_id
      , 'geschaeftsjahr' => $geschaeftsjahr
      , 'valuta >=' => $valuta_von
      , 'valuta <=' => $valuta_bis
      );

      postenlist_view( $filters, array(
        'geschaeftsjahr_zeigen' => 0
      , 'authorized' => 1
      , 'columns' => array(
          'aktionen' => 't=off'
        , 'vorfall' => 't=1'
        , 'ust_satz' => 't=off'
        , 'ust_betrag' => 't=off'
        , 'vorsteuer_betrag' => 't=off'
        , 'netto' => 't=off'
        , 'saldo_geplant' => 't=off'
        , 'haben_geplant' => 't=off'
        , 'soll_geplant' => 't=off'
        )
      ) );
  
    } else {
  
      saldenlist_view( "unterkonten_id=$unterkonten_id", 'select_jahr=P3_geschaeftsjahr,'.AUTH );
  
    }

  close_fieldset();

close_fieldset();


?>
