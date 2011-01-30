<?php

echo "<h1>Unterkonten</h1>";

get_http_var( 'options', 'u', 0, true );
get_http_var( 'geschaeftsjahr_thread', 'u', $now[0], 'thread' );
get_http_var( 'geschaeftsjahr', 'u', $geschaeftsjahr_thread, 'self' );

$filters = handle_filters( array(
  'seite', 'kontoart', 'geschaeftsbereiche_id', 'kontoklassen_id', 'hauptkonten_id', 'geschaeftsjahr'
) );
filter_geschaeftsbereich_prepare();
filter_kontoklasse_prepare();
filter_hauptkonto_prepare();

handle_action( array( 'update', 'unterkontoDelete' ) );
switch( $action ) {
  case 'update':
    //nop
    break;

  case 'unterkontoDelete':
    need( $message, 'kein unterkonto gewaehlt' );
    sql_unterkonto_delete( $message );
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
    open_th( 'right', '', 'Kontoart:' );
    open_td();
      filter_kontoart();
  open_tr();
    open_th( 'right', '', 'Seite:' );
    open_td();
      filter_seite();
  if( "$kontoart" == 'E' ) {
    open_tr();
      open_th( 'right', '', 'Geschaeftsbereich:' );
      open_td();
        filter_geschaeftsbereich();
  }
  open_tr();
    open_th( 'right', '', 'Kontoklasse:' );
    open_td();
      filter_kontoklasse();
  open_tr();
    open_th( 'right', '', 'Hauptkonto:' );
    open_td();
      filter_hauptkonto();
close_table();

bigskip();

unterkontenlist_view( $filters );

?>
