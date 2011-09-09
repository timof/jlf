<?php

echo html_tag( 'h1', '', 'Posten' );

init_var( 'options', 'global,pattern=u,sources=http persistent,default=0,set_scopes=window' );

$fields = filters_kontodaten_prepare( array(
  'valuta_von' => 'default=100'
, 'valuta_bis' => 'default=1299'
, 'buchungsdatum_von', 'buchungsdatum_bis'
, 'geschaeftsjahr' => 'pattern=u,default='.$geschaeftsjahr_current
, 'seite', 'kontenkreis', 'geschaeftsbereiche_id', 'kontoklassen_id', 'hauptkonten_id', 'unterkonten_id'
) );

$filters = $fields['_filters'];


handle_action( array( 'update' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
}

open_table( 'menu' );
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( 'right', 'Geschaeftsjahr:' );
    open_td();
      filter_geschaeftsjahr( $fields['geschaeftsjahr'] );
  open_tr();
    open_th( 'right', 'Kontenkreis / Seite:' );
    open_td( 'oneline' );
      filter_kontenkreis( $fields['kontenkreis'] );
      filter_seite( $fields['seite'] );
if( $fields['kontenkreis']['value'] === 'E' ) {
  open_tr();
  open_th( 'right', 'Geschaeftsbereich:' );
  open_td();
    filter_geschaeftsbereich( $fields['geschaeftsbereiche_id'] );
}
  open_tr();
    open_th( 'right', 'Kontoklasse:' );
    open_td();
      filter_kontoklasse( $fields['kontoklassen_id'], array( 'filters' => $filters ) );
  open_tr();
    open_th( 'right', 'Hauptkonto:' );
    open_td();
      filter_hauptkonto( $fields['hauptkonten_id'], array( 'filters' => $filters ) );
    if( $fields['hauptkonten_id']['value'] ) {
      open_tr();
        open_th( 'right', 'Unterkonto:' );
        open_td();
          filter_unterkonto( $fields['unterkonten_id'], array( 'filters' => $filters ) );
    }
  open_tr();
    open_th( 'right', 'Valuta von:' );
    open_td( 'oneline' );
      echo monthday_element( $fields['valuta_von'] );
      open_span( 'quads th', 'bis:' );
      echo monthday_element( $fields['valuta_bis'] );

if(0) {
  open_tr();
    open_th( 'right', 'Buchungsdatum von:' );
    open_td();
      date_selector( 'buchungsdatum_von_tag', $buchungsdatum_von_tag, 'buchungsdatum_von_monat', $buchungsdatum_von_monat, 'buchungsdatum_von_jahr', $buchungsdatum_von_jahr );
    open_th( 'right', ' bis:' );
    open_td();
      date_selector( 'buchungsdatum_bis_tag', $buchungsdatum_bis_tag, 'buchungsdatum_bis_monat', $buchungsdatum_bis_monat, 'buchungsdatum_bis_jahr', $buchungsdatum_bis_jahr );
}

  open_tr();
    open_th( 'colspan=4', 'Aktionen' );
  open_tr();
    open_th( 'colspan=4', inlink( 'buchung', array( 'class' => 'button', 'text' => 'Neue Buchung' ) ) );
close_table();

bigskip();

postenlist_view( $filters );

?>
