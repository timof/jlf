<?php

echo "<h1>Posten</h1>";

init_global_var( 'options', 'u', 'http,self', 0, 'window' );

init_global_var( 'geschaeftsjahr', 'u', 'http,persistent,keep', $geschaeftsjahr_thread, 'self' );

$filters = handle_filters( array( 'valuta_von' => 100, 'valuta_bis' => 1299 , 'buchungsdatum_von', 'buchungsdatum_bis' ) );

$filters += filters_kontodaten_prepare( '', array( 'seite', 'kontoart', 'geschaeftsjahr', 'geschaeftsbereiche_id', 'kontoklassen_id', 'hauptkonten_id', 'unterkonten_id' ) );

if( $unterkonten_id = adefault( $filters, 'unterkonten_id', 0 ) ) {
  $uk = sql_one_unterkonto( $unterkonten_id );
  $hauptkonten_id = $uk['hauptkonten_id'];
  $filters['hauptkonten_id'] = & $hauptkonten_id;
}

handle_action( array( 'update' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
}

open_table( 'menu' );
  open_tr();
    open_th( 'center', "colspan='2'", 'Filter' );
  open_tr();
    open_th( 'right', '', 'Geschaeftsjahr:' );
    open_td();
      filter_geschaeftsjahr();
  open_tr();
    open_th( 'right', '', 'Kontoart:' );
    open_td();
      filter_kontoart();
    if( "$kontoart" == 'E' ) {
      open_tr();
      open_th( 'right', '', 'Geschaeftsbereich:' );
      open_td();
        filter_geschaeftsbereich();
    }
  open_tr();
    open_th( 'right', '', 'Seite:' );
    open_td();
      filter_seite();
  open_tr();
    open_th( 'right', '', 'Kontoklasse:' );
    open_td();
      filter_kontoklasse();
  open_tr();
    open_th( 'right', '', 'Hauptkonto:' );
    open_td();
      filter_hauptkonto();
    if( $hauptkonten_id ) {
      open_tr();
        open_th( 'right', '', 'Unterkonto:' );
        open_td();
          filter_unterkonto();
    }
  open_tr();
    open_th( 'right', '', 'Valuta von:' );
    open_td( 'oneline' );
      form_field_monthday( $valuta_von, 'valuta_von' );
      open_span( 'quads th', '', 'bis:' );
      form_field_monthday( $valuta_bis, 'valuta_bis' );

if(0) {
  open_tr();
    open_th( 'right', '', 'Buchungsdatum von:' );
    open_td();
      date_selector( 'buchungsdatum_von_tag', $buchungsdatum_von_tag, 'buchungsdatum_von_monat', $buchungsdatum_von_monat, 'buchungsdatum_von_jahr', $buchungsdatum_von_jahr );
    open_th( 'right', '', ' bis:' );
    open_td();
      date_selector( 'buchungsdatum_bis_tag', $buchungsdatum_bis_tag, 'buchungsdatum_bis_monat', $buchungsdatum_bis_monat, 'buchungsdatum_bis_jahr', $buchungsdatum_bis_jahr );
}

  open_tr();
    open_th( '', "colspan='4'", 'Aktionen' );
  open_tr();
    open_th( '', "colspan='4'", inlink( 'buchung', array( 'class' => 'button', 'text' => 'Neue Buchung' ) ) );
close_table();

bigskip();

postenlist_view( $filters );

?>
