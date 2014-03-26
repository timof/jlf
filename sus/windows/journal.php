<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

echo html_tag( 'h1', '', 'Journal' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = filters_kontodaten_prepare( array(
  'valuta_von' => 'default=100,min=100,max=1231,type=U,sql_name=valuta,relation=>='
, 'valuta_bis' => 'default=1231,min=100,max=1231,type=U,sql_name=valuta,relation=<='
, 'buchungsdatum_von' => "default={$geschaeftsjahr_min}0101,type=U,sql_name=cdate,relation=>="
, 'buchungsdatum_bis' => "default={$geschaeftsjahr_max}1231,type=U,sql_name=cdate,relation=<="
, 'geschaeftsjahr' => 'type=u,default='.$geschaeftsjahr_thread
, 'seite' /* does this make sense here???? */
, 'kontenkreis' => 'auto=1'
, 'geschaeftsbereich', 'kontoklassen_id', 'geschaeftsjahr', 'hauptkonten_id', 'unterkonten_id'
) );

$filters = $fields['_filters'];

handle_actions( array( 'deleteBuchung' ) );
switch( $action ) {
  case 'deleteBuchung':
    init_var( 'buchungen_id', 'global=1,sources=http,type=U' );
    sql_buche( $buchungen_id, array(), 'action=hard' );
    break;
}

open_div('menubox medskipb');
  open_table('css filters');
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
  open_tr();
    open_th( 'right', "Gesch{$aUML}ftsjahr:" );
    open_td( '', filter_geschaeftsjahr( $fields['geschaeftsjahr'] ) );
  open_tr();
    open_th( 'right', 'Kontenkreis:' );
    open_td( '', filter_kontenkreis( $fields['kontenkreis'] ) );
  if( $fields['kontenkreis']['value'] === 'E' ) {
    open_tr();
      open_th( 'right', "Gesch{$aUML}ftsbereich:" );
      open_td( '', filter_geschaeftsbereich( $fields['geschaeftsbereich'] ) );
  } else {
    unset( $filters['geschaeftsbereich'] );
  }
//  open_tr();
//    open_th( 'right', 'Seite:' );
//    open_td( '', filter_seite( $fields['seite'] ) );
  open_tr();
    open_th( 'right', 'Kontoklasse:' );
    open_td( '', filter_kontoklasse( $fields['kontoklassen_id'], array( 'filters' => $filters ) ) );
  open_tr();
    open_th( 'right', 'Hauptkonto:' );
    open_td( '', filter_hauptkonto( $fields['hauptkonten_id'], array( 'filters' => $filters ) ) );
    if( $fields['hauptkonten_id']['value'] ) {
      open_tr();
        open_th( 'right', 'Unterkonto:' );
        open_td( '', filter_unterkonto( $fields['unterkonten_id'], array( 'filters' => $filters ) ) );
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
    open_td( '', date_selector( 'buchungsdatum_von_tag', $buchungsdatum_von_tag, 'buchungsdatum_von_monat', $buchungsdatum_von_monat, 'buchungsdatum_von_jahr', $buchungsdatum_von_jahr ) );
    open_th( 'right', ' bis:' );
    open_td( '', date_selector( 'buchungsdatum_bis_tag', $buchungsdatum_bis_tag, 'buchungsdatum_bis_monat', $buchungsdatum_bis_monat, 'buchungsdatum_bis_jahr', $buchungsdatum_bis_jahr ) );
}

  close_table();

  open_table('css actions' );
    open_caption( '', 'Aktionen' );
    open_tr( '', inlink( 'buchung', 'class=big button,text=Neue Buchung' ) );
  close_table();
close_div();

buchungenlist_view( $filters );

?>
