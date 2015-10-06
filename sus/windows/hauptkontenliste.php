<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

echo html_tag( 'h1', '', 'Hauptkonten' );

$fields = filters_kontodaten_prepare( array(
    'seite' => 'auto=1'
  , 'kontenkreis' => 'auto=1'
  , 'geschaeftsbereich'
  , 'kontoklassen_id'
  )
, 'auto_select_unique=0'
);

$fields = init_fields(
  array(
    'flag_vortragskonto' => 'B,auto=1'
  , 'flag_personenkonto' => 'B,auto=1'
  , 'flag_sachkonto' => 'B,auto=1'
  , 'flag_bankkonto' => 'B,auto=1'
  , 'flag_hauptkonto_offen' => 'B,auto=1'
  )
, array( 'merge' => $fields )
);

$field_geschaeftsjahr = init_var( 'geschaeftsjahr', "global,type=u4,default=$geschaeftsjahr_thread,min=$geschaeftsjahr_min" );
$filters = $fields['_filters'];


open_div('menubox medskipb');
  open_table('css filters');
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
  open_tr();
    open_th( 'right', "Gesch{$aUML}ftsjahr:" );
    open_td( 'oneline', selector_geschaeftsjahr( $field_geschaeftsjahr ) );
  open_tr();
    open_th( 'right', 'Kontenkreis:' );
    open_td( '', radiolist_element( $fields['kontenkreis'], array( 'choices' => array( 'B' => 'Bestand', 'E' => 'Erfolg', '0' => 'beide' ) ) ) );
//  if( $fields['kontenkreis']['value'] === 'E' ) {
    open_tr();
      open_th( 'right', "Gesch{$aUML}ftsbereich:" );
      open_td( '', filter_geschaeftsbereich( $fields['geschaeftsbereich'] ) );
//  } else {
//    unset( $filters['geschaeftsbereich'] );
//  }
  open_tr();
    open_th( 'right', 'Seite:' );
    open_td( '', radiolist_element( $fields['seite'], array( 'choices' => array( 'A' => 'Aktiva', 'P' => 'Passiva', '0' => 'beide' ) ) ) );
  open_tr();
    open_th( 'right', 'Kontoklasse:' );
    open_td( '', filter_kontoklasse( $fields['kontoklassen_id'], array( 'filters' => $filters ) ) );

  open_tr();
    open_th( 'right,rowspan=1', 'Vortragskonten:' );
    open_td( '', radiolist_element( $fields['flag_vortragskonto'], 'choices=:nein:ja:alle' ) );

  open_tr();
    open_th( 'right,rowspan=1', 'Personenkonten:' );
    open_td( '', radiolist_element( $fields['flag_personenkonto'], 'choices=:nein:ja:alle' ) );

  open_tr();
    open_th( 'right,rowspan=1', 'Sachkonten:' );
    open_td( '', radiolist_element( $fields['flag_sachkonto'], 'choices=:nein:ja:alle' ) );

  open_tr();
    open_th( 'right,rowspan=1', 'Bankkonten:' );
    open_td( '', radiolist_element( $fields['flag_bankkonto'], 'choices=:nein:ja:alle' ) );

  open_tr();
    open_th( 'right,rowspan=1', 'offen:' );
    open_td( '', radiolist_element( $fields['flag_hauptkonto_offen'], 'choices=:nein:ja:alle' ) );

//   open_tr();
//     open_th( 'right', 'HGB-Klasse:' );
//     open_td();
//       filter_hgb_klasse();
//   open_tr();
//     open_th( 'colspan=2', 'Optionen / Aktionen' );
//   open_tr();
//     open_td( 'oneline' );
//     open_td( 'oneline' );
//       echo checkbox_element( 'options', array( 'mask' => OPTION_HAUPTKONTENLISTE, 'text' => 'nur Hauptkonten zeigen', 'auto' => 'submit' ) );
  close_table();
close_div();

hauptkontenlist_view( $filters, "geschaeftsjahr=$geschaeftsjahr" );

?>
