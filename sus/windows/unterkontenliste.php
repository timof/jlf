<?php

$of = init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );

define( 'OPTION_PERSONENKONTEN', 1 );
define( 'OPTION_SACHKONTEN', 2 );
define( 'OPTION_ZINSKONTEN', 4 );
// define( 'OPTION_VORTRAGSKONTEN', 8 );
define( 'OPTION_BANKKONTEN', 16 );

echo html_tag( 'h1', '', 'Unterkonten' );

// debug( $options, 'options' );

$fields = filters_kontodaten_prepare( array(
  'seite', 'kontenkreis', 'geschaeftsbereiche_id', 'kontoklassen_id', 'hauptkonten_id'
, 'geschaeftsjahr' => "default=$geschaeftsjahr_thread"
, 'people_id', 'things_id'
, 'vortragskonto' => 'B,default=2'
) );
$filters = $fields['_filters'];


$personenkonten = ( $options & OPTION_PERSONENKONTEN );
if( $personenkonten ) {
  $filters['personenkonto'] = 1;
}

$sachkonten = ( $options & OPTION_SACHKONTEN );
if( $sachkonten ) {
  $filters['sachkonto'] = 1;
} 

$zinskonten = ( $options & OPTION_ZINSKONTEN );
if( $zinskonten ) {
  $filters['zinskonto'] = 1;
}

// $vortragskonten = ( $options & OPTION_VORTRAGSKONTEN );
// if( $vortragskonten ) {
//   $filters['is_vortragskonto'] = 1;
// }

$bankkonten = ( $options & OPTION_BANKKONTEN );
if( $bankkonten ) {
  $filters['bankkonto'] = 1;
}

handle_action( array( 'update', 'deleteUnterkonto', 'unterkontoSchliessen' ) );
switch( $action ) {
  case 'update':
    //nop
    break;

  case 'deleteUnterkonto':
    need( $message, 'kein unterkonto gewaehlt' );
    sql_delete_unterkonten( $message );
    break;

  case 'unterkontoSchliessen':
    need( $message, 'kein unterkonto gewaehlt' );
    sql_unterkonto_schliessen( $message );
    break;

}

open_table('menu');
  open_tr();
    open_th( 'colspan=2', 'Filter' );
  open_tr();
    open_th( 'right', 'Geschaeftsjahr:' );
    open_td( 'oneline', filter_geschaeftsjahr( $fields['geschaeftsjahr'] ) );
  open_tr();
    open_th( 'right', 'Kontenkreis / Seite:' );
    open_td( 'oneline' );
      echo filter_kontenkreis( $fields['kontenkreis'] );
      qquad();
      echo filter_seite( $fields['seite'] );
  if( $fields['kontenkreis']['value'] === 'E' ) {
    open_tr();
      open_th( 'right', 'Geschaeftsbereich:' );
      open_td( '', filter_geschaeftsbereich( $fields['geschaeftsbereiche_id'] ) );
  }
  open_tr();
    open_th( 'right', 'Kontoklasse / Hauptkonto:' );
    open_td();
      echo filter_kontoklasse( $fields['kontoklassen_id'], array( 'filters' => $filters ) );
      qquad();
      echo filter_hauptkonto( $fields['hauptkonten_id'], array( 'filters' => $filters ) );
  open_tr();
    open_th( 'right,rowspan=3', 'Attribute:' );
    open_td();
      echo checkbox_element( array( 'name' => 'options', 'raw' => $options, 'mask' => OPTION_PERSONENKONTEN, 'text' => 'Personenkonten', 'auto' => 'submit' ) );
      if( $fields['people_id']['value'] || $personenkonten ) {
        open_span( 'qquad oneline', 'Person: ' . filter_person( $fields['people_id'] ) );
      }
  open_tr();
    // open_th();
    open_td();
      echo checkbox_element( array( 'name' => 'options', 'raw' => $options, 'mask' => OPTION_SACHKONTEN, 'text' => 'Sachkonten', 'auto' => 'submit' ) );
      if( $fields['things_id']['value'] || $sachkonten ) {
        open_span( 'qquad oneline', 'Gegenstand: ' . filter_thing( $fields['things_id'] ) );
      }
  open_tr();
    // open_th();
    open_td();
      echo checkbox_element(  array( 'name' => 'options', 'raw' => $options, 'mask' => OPTION_ZINSKONTEN, 'text' => 'Zinskonten', 'auto' => 'submit' ) );
      qquad();
      echo checkbox_element(  array( 'name' => 'options', 'raw' => $options, 'mask' => OPTION_BANKKONTEN, 'text' => 'Bankkonten', 'auto' => 'submit' ) );

  open_tr();
    open_th( 'right', 'Vortragskonten:' );
    open_td( '', radiolist_element( $fields['vortragskonto_tri'], 'choices=:ja:nein:beide' ) );


//   open_tr();
//     open_th( 'right', '', 'HGB-Klasse:' );
//     open_td();
//       filter_hgb_klasse();
//   open_tr();
//     open_th('', "colspan='2'", 'Optionen / Aktionen' );
//   open_tr();
//     open_td( 'oneline' );
//     open_td( 'oneline', checkbox_element( 'options', array( 'mask' => OPTION_HAUPTKONTENLISTE, 'text' => 'nur Hauptkonten zeigen', 'auto' => 'submit' ) ) );
close_table();

bigskip();

unterkontenlist_view( $filters );

?>
