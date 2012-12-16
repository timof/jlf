<?php

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

echo html_tag( 'h1', '', 'Hauptkonten' );

$fields = filters_kontodaten_prepare( array(
  'seite', 'kontenkreis', 'geschaeftsbereiche_id', 'kontoklassen_id'
, 'geschaeftsjahr' => "type=u,default=$geschaeftsjahr_thread,min=$geschaeftsjahr_min,max=$geschaeftsjahr_max"
, 'vortragskonto' => 'B,auto=1'
, 'personenkonto' => 'B,auto=1'
, 'sachkonto' => 'B,auto=1'
, 'bankkonto' => 'B,auto=1'
) );
$filters = $fields['_filters'];

handle_action( array( 'update', 'deleteHauptkonto', 'hauptkontoSchliessen' ) );
switch( $action ) {
  case 'update':
    //nop
    break;

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

open_table('menu');
  open_tr();
    open_th( 'colspan=2', 'Filter' );
  open_tr();
    open_th( 'right', 'Geschäftsjahr:' );
    open_td( 'oneline', filter_geschaeftsjahr( $fields['geschaeftsjahr'] ) );
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
    open_th( 'right,rowspan=1', 'Attribute:' );
    open_td();
      echo checkbox_element( 'options', array( 'mask' => OPTION_PERSONENKONTEN, 'text' => 'Personenkonten', 'auto' => 'submit' ) );
      qquad();
      echo checkbox_element( 'options', array( 'mask' => OPTION_SACHKONTEN, 'text' => 'Sachkonten', 'auto' => 'submit' ) );
      qquad();
      echo checkbox_element( 'options', array( 'mask' => OPTION_BANKKONTEN, 'text' => 'Bankkonten', 'auto' => 'submit' ) );
  open_tr();
    open_th( 'right', 'Vortragskonten:' );
    open_td( '', radiolist_element( $fields['vortragskonto'], 'choices=:ja:nein:beide' ) );

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

bigskip();

hauptkontenlist_view( $filters );

?>
