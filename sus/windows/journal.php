<?php

echo "<h1>Journal</h1>";

get_http_var( 'options', 'u', 0, true );

$filters = handle_filters( array(
  'seite', 'kontoart', 'geschaeftsbereiche_id', 'kontoklassen_id', 'hauptkonten_id', 'unterkonten_id'
, 'valuta_von', 'valuta_bis', 'buchungsdatum_von', 'buchungsdatum_bis'
) );
filter_geschaeftsbereich_prepare();
filter_kontoklasse_prepare();
filter_hauptkonto_prepare();
filter_unterkonto_prepare();

get_http_var( 'action', 'w', '' );
switch( $action ) {
  case 'delete':
    need_http_var( 'message', 'U' );
    sql_delete_buchung( $message );
    break;
}

open_table( 'menu' );
  open_tr();
    open_th( 'center', "colspan='2'", 'Filter' );
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
if(0) {
  open_tr();
    open_th( 'right', '', 'Valuta von:' );
    open_td();
      date_selector( 'valuta_von_tag', $valuta_von_tag, 'valuta_von_monat', $valuta_von_monat, 'valuta_von_jahr', $valuta_von_jahr );
    open_th( 'right', '', ' bis:' );
    open_td();
      date_selector( 'valuta_bis_tag', $valuta_bis_tag, 'valuta_bis_monat', $valuta_bis_monat, 'valuta_bis_jahr', $valuta_bis_jahr );

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

buchungenlist_view( $filters, '' );

?>
