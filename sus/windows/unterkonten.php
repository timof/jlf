<?php

init_global_var( 'options', 'u', 'http,persistent', 0, 'window' );
define( 'OPTION_HAUPTKONTENLISTE', 1 );

if( $options & OPTION_HAUPTKONTENLISTE )
  echo "<h1>Hauptkonten:</h1>";
else
  echo "<h1>Unterkonten:</h1>";

init_global_var( 'geschaeftsjahr', 'u', 'http,persistent,keep', $geschaeftsjahr_thread, 'self' );
$filters = filters_kontodaten_prepare( '', array( 'seite', 'kontoart', 'geschaeftsbereiche_id', 'kontoklassen_id', 'hauptkonten_id', 'geschaeftsjahr' ) );

handle_action( array( 'update', 'deleteUnterkonto' ) );
switch( $action ) {
  case 'update':
    //nop
    break;

  case 'deleteUnterkonto':
    need( $message, 'kein unterkonto gewaehlt' );
    sql_delete_unterkonten( $message );
    break;
}

open_table('menu');
  open_tr();
    open_th('', "colspan='2'", 'Filter' );
  open_tr();
    open_th( 'right', '', 'Geschaeftsjahr:' );
    open_td( 'oneline' );
      filter_geschaeftsjahr();
  open_tr();
    open_th( 'right', '', 'Kontoart / Seite:' );
    open_td( 'oneline' );
      filter_kontoart();
      qquad();
      filter_seite();
  if( "$kontoart" == 'E' ) {
    open_tr();
      open_th( 'right', '', 'Geschaeftsbereich:' );
      open_td();
        filter_geschaeftsbereich();
  }
  open_tr();
    open_th( 'right', '', 'Kontoklasse' . ( $options & OPTION_HAUPTKONTENLISTE ) ? ':' : ' / Hauptkonto:' );
    open_td();
      filter_kontoklasse();
      if( ! ( $options & OPTION_HAUPTKONTENLISTE ) ) {
        qquad();
        filter_hauptkonto();
      }
  open_tr();
    open_th('', "colspan='2'", 'Optionen / Aktionen' );
  open_tr();
    open_td( 'oneline' );
    open_td( 'oneline' );
      option_checkbox( 'options', OPTION_HAUPTKONTENLISTE, 'nur Hauptkonten zeigen' );
close_table();

bigskip();

if( $options & OPTION_HAUPTKONTENLISTE ) {
  hauptkontenlist_view( $filters );
} else {
  unterkontenlist_view( $filters );
}

?>
