<?php

init_global_var( 'options', 'u', 'http,persistent', 0, 'self' );

define( 'OPTION_PERSONENKONTEN', 1 );
define( 'OPTION_SACHKONTEN', 2 );
define( 'OPTION_ZINSKONTEN', 4 );
define( 'OPTION_VORTRAGSKONTEN', 8 );
define( 'OPTION_BANKKONTEN', 16 );

echo "<h1>Unterkonten:</h1>";

init_global_var( 'geschaeftsjahr', 'u', 'http,persistent,keep', $geschaeftsjahr_thread, 'self' );
$filters = filters_kontodaten_prepare( '', array( 'seite', 'kontenkreis', 'geschaeftsbereiche_id', 'kontoklassen_id', 'hauptkonten_id', 'geschaeftsjahr' ) );
// $filters += handle_filters( 'hgb_klasse' );


if( get_http_var( 'people_id' ) ) {
  $options |= OPTION_PERSONENKONTEN;
}
$personenkonten = ( $options & OPTION_PERSONENKONTEN );
if( $personenkonten ) {
  $filters['personenkonto'] = 1;
  $filters += handle_filters( 'people_id' );
}

if( get_http_var( 'things_id' ) ) {
  $options |= OPTION_SACHKONTEN;
}
$sachkonten = ( $options & OPTION_SACHKONTEN );
if( $sachkonten ) {
  $filters['sachkonto'] = 1;
  $filters += handle_filters( 'things_id' );
}

$zinskonten = ( $options & OPTION_ZINSKONTEN );
if( $zinskonten ) {
  $filters['zinskonto'] = 1;
}

$vortragskonten = ( $options & OPTION_VORTRAGSKONTEN );
if( $vortragskonten ) {
  $filters['is_vortragskonto'] = 1;
}

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
    open_td( 'oneline' );
      filter_geschaeftsjahr();
  open_tr();
    open_th( 'right', 'Kontenkreis / Seite:' );
    open_td( 'oneline' );
      filter_kontenkreis();
      qquad();
      filter_seite();
  if( "$kontenkreis" == 'E' ) {
    open_tr();
      open_th( 'right', 'Geschaeftsbereich:' );
      open_td();
        filter_geschaeftsbereich();
  }
  open_tr();
    open_th( 'right', 'Kontoklasse / Hauptkonto:' );
    open_td();
      filter_kontoklasse();
      qquad();
      filter_hauptkonto();
  open_tr();
    open_th( 'right,rowspan=2', 'Attribute:' );
    open_td();
      option_checkbox( 'options', OPTION_PERSONENKONTEN, 'Personenkonten' );
      qquad();
      option_checkbox( 'options', OPTION_SACHKONTEN, 'Sachkonten' );
      qquad();
      option_checkbox( 'options', OPTION_BANKKONTEN, 'Bankkonten' );
  open_tr();
    // open_th();
    open_td();
      option_checkbox( 'options', OPTION_ZINSKONTEN, 'Zinskonten' );
      qquad();
      option_checkbox( 'options', OPTION_VORTRAGSKONTEN, 'Vortragskonten' );
      
  if( $personenkonten ) {
    open_tr();
      open_th( 'right', 'Person:' );
      open_td();
        filter_person();
  }
  if( $sachkonten ) {
    open_tr();
      open_th( 'right', 'Gegenstand:' );
      open_td();
        filter_thing();
  }

//   open_tr();
//     open_th( 'right', '', 'HGB-Klasse:' );
//     open_td();
//       filter_hgb_klasse();
//   open_tr();
//     open_th('', "colspan='2'", 'Optionen / Aktionen' );
//   open_tr();
//     open_td( 'oneline' );
//     open_td( 'oneline' );
//       option_checkbox( 'options', OPTION_HAUPTKONTENLISTE, 'nur Hauptkonten zeigen' );
close_table();

bigskip();

unterkontenlist_view( $filters );

?>
