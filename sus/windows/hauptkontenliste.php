<?php

sql_transaction_boundary('*');

define( 'OPTION_PERSONENKONTEN', 1 );
define( 'OPTION_SACHKONTEN', 2 );
define( 'OPTION_ZINSKONTEN', 4 );
// define( 'OPTION_VORTRAGSKONTEN', 8 );
define( 'OPTION_BANKKONTEN', 16 );

$options_field = init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );
$options_field['auto'] = 1;

echo html_tag( 'h1', '', 'Hauptkonten' );

$fields = filters_kontodaten_prepare( array(
  'seite', 'kontenkreis', 'geschaeftsbereiche_id', 'kontoklassen_id'
, 'geschaeftsjahr' => "type=u,default=$geschaeftsjahr_thread,min=$geschaeftsjahr_min"
, 'flag_vortragskonto' => 'B,auto=1'
, 'flag_personenkonto' => 'B,auto=1'
, 'flag_sachkonto' => 'B,auto=1'
, 'flag_bankkonto' => 'B,auto=1'
) );

$geschaeftsjahr = $fields['geschaeftsjahr']['value'];
$filters = $fields['_filters'];
unset( $filters['geschaeftsjahr'] );

handle_actions( array( 'deleteHauptkonto', 'hauptkontoSchliessen' ) );
switch( $action ) {
  case 'deleteHauptkonto':
    need( 0, 'deprecated' );
    need( $message, 'kein hauptkonto gewaehlt' );
    sql_delete_hauptkonten( $message );
    break;

  case 'hauptkontoSchliessen':
    need( 0, 'deprecated' );
    need( $message, 'kein hauptkonto gewaehlt' );
    sql_hauptkonto_schliessen( $message );
    break;
}

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
  open_tr();
    open_th( 'right', 'Geschäftsjahr:' );
    open_td( 'oneline', selector_geschaeftsjahr( $fields['geschaeftsjahr'] ) );
  open_tr();
    open_th( 'right', 'Kontenkreis / Seite:' );
    open_td( 'oneline' );
      echo filter_kontenkreis( $fields['kontenkreis'] );
      qquad();
      echo filter_seite( $fields['seite'] );
  if( $fields['kontenkreis']['value'] === 'E' ) {
    open_tr();
      open_th( 'right', 'Geschäftsbereich:' );
      open_td( '', filter_geschaeftsbereich( $fields['geschaeftsbereiche_id'] ) );
  }
  open_tr();
    open_th( 'right', 'Kontoklasse:' );
    open_td( '', filter_kontoklasse( $fields['kontoklassen_id'], array( 'filters' => $filters ) ) );

  open_tr();
    open_th( 'right,rowspan=1', 'Vortragskonten:' );
    open_td( '', radiolist_element( $fields['flag_vortragskonto'], 'choices=:ja:nein:beide' ) );

  open_tr();
    open_th( 'right,rowspan=1', 'Personenkonten:' );
    open_td( '', radiolist_element( $fields['flag_personenkonto'], 'choices=:ja:nein:beide' ) );

  open_tr();
    open_th( 'right,rowspan=1', 'Sachkonten:' );
    open_td( '', radiolist_element( $fields['flag_sachkonto'], 'choices=:ja:nein:beide' ) );

  open_tr();
    open_th( 'right,rowspan=1', 'Bankkonten:' );
    open_td( '', radiolist_element( $fields['flag_bankkonto'], 'choices=:ja:nein:beide' ) );

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

bigskip();

hauptkontenlist_view( $filters );

?>
