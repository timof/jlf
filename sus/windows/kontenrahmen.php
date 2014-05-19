<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*','*');

echo html_tag( 'h1', '', 'Kontenrahmen' );

$kontenrahmen = array(

  'verein' => array(
    array( 'kontoklassen_id' => '10', 'cn' => 'Bankkonto', 'flag_bankkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '30', 'cn' => 'Debitor', 'flag_personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '40', 'cn' => 'Sachkonto', 'flag_sachkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '50', 'cn' => 'sonstige Aktiva', 'kontenkreis' => 'B', 'seite' => 'A' )

  , array( 'kontoklassen_id' => '100', 'cn' => 'Kreditor', 'flag_personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '110', 'cn' => 'sonstige Passiva', 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '121', 'cn' => 'Vortrag ideller Bereich', 'vortragskonto' => 'ideeller Bereich', 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '122', 'cn' => 'Vortrag Vermoegensverwaltung', 'vortragskonto' => 'Vermoegensverwaltung', 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '123', 'cn' => 'Vortrag Zweckbetrieb', 'vortragskonto' => 'Zweckbetrieb', 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '124', 'cn' => 'Vortrag wirtschaftlicher Geschaeftsbetrieb', 'vortragskonto' => 'wirtschaftlicher Geschaeftsbetrieb', 'kontenkreis' => 'B', 'seite' => 'P' )

  , array( 'kontoklassen_id' => '200', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'ideeller Bereich', 'kontenkreis' => 'E', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '210', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'ideeller Bereich', 'kontenkreis' => 'E', 'seite' => 'A' )

  , array( 'kontoklassen_id' => '300', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'Vermoegensverwaltung', 'kontenkreis' => 'E', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '310', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'Vermoegensverwaltung', 'kontenkreis' => 'E', 'seite' => 'A' )

  , array( 'kontoklassen_id' => '400', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'Zweckbetrieb', 'kontenkreis' => 'E', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '410', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'Zweckbetrieb', 'kontenkreis' => 'E', 'seite' => 'A' )

  , array( 'kontoklassen_id' => '500', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'wirtschaftlicher Geschaeftsbetrieb', 'kontenkreis' => 'E', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '510', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'wirtschaftlicher Geschaeftsbetrieb', 'kontenkreis' => 'E', 'seite' => 'A' )
  )


, 'gmbh' => array(
    array( 'kontoklassen_id' => '10', 'cn' => 'Bankkonto', 'flag_bankkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '30', 'cn' => 'Debitor', 'flag_personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '40', 'cn' => 'Sachkonto', 'flag_sachkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '50', 'cn' => 'sonstige Aktiva', 'kontenkreis' => 'B', 'seite' => 'A' )

  , array( 'kontoklassen_id' => '100', 'cn' => 'Kreditor', 'flag_personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '110', 'cn' => 'sonstige Passiva', 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '121', 'cn' => 'Vortrag', 'vortragskonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P' )

  , array( 'kontoklassen_id' => '200', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => '', 'kontenkreis' => 'E', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '210', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => '', 'kontenkreis' => 'E', 'seite' => 'A' )
  )


, 'privat' => array(
    array( 'kontoklassen_id' => '10', 'cn' => 'Bankkonto', 'flag_bankkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '30', 'cn' => 'Debitor', 'flag_personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '40', 'cn' => 'Sachkonto', 'flag_sachkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '50', 'cn' => 'sonstige Aktiva', 'kontenkreis' => 'B', 'seite' => 'A' )
  
  , array( 'kontoklassen_id' => '100', 'cn' => 'Kreditor', 'flag_personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '110', 'cn' => 'sonstige Passiva', 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '120', 'cn' => 'Vortrag', 'vortragskonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P' )
  
  , array( 'kontoklassen_id' => '200', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => '', 'kontenkreis' => 'E', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '210', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => '', 'kontenkreis' => 'E', 'seite' => 'A' )
  )

);

$choices_kontenrahmen = array();
foreach( $kontenrahmen as $key => & $rahmen ) {
  foreach( $rahmen as & $k ) {
    $k['geschaeftsbereich'] = adefault( $k, 'geschaeftsbereich', '' );
    $k['flag_bankkonto'] = adefault( $k, 'flag_bankkonto', '0' );
    $k['flag_sachkonto'] = adefault( $k, 'flag_sachkonto', '0' );
    $k['flag_personenkonto'] = adefault( $k, 'flag_personenkonto', '0' );
    $k['vortragskonto'] = adefault( $k, 'vortragskonto', '' );
  }
  unset( $k );
  $choices_kontenrahmen[ $key ] = $key;
}

$f = init_var( 'version_neu', 'global,type=w64,sources=http persistent,set_scopes=self,default=' );

handle_actions( array( 'installKontenrahmen' ) );
switch( $action ) {
  case 'installKontenrahmen':
    need( $version_neu, "kein Kontenrahmen gew{$aUML}hlt" );
    need( ( $rahmen = adefault( $kontenrahmen, $version_neu ) ), "kein g{$uUML}ltiger Kontenrahmen gew{$aUML}hlt" );
    sql_install_kontenrahmen( $version_neu, $rahmen );
    break;
}


echo html_tag( 'h2', '', "Installierter Kontenrahmen: $kontenrahmen_version" );

$rows = sql_kontoklassen( true, 'orderby=kontoklassen_id' );
if( ! $rows ) {
  open_div( '', '(kein Kontenrahmen installiert)' );
} else {

  kontoklassen_view( $rows );
}


echo html_tag( 'h2', 'medskipt smallskipb', "Verf{$uUML}gbare Kontenrahmen" );

$rows = adefault( $kontenrahmen, $version_neu );

open_div('menubox');
  open_table( 'css filters' );
    // open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Optionen' );
    // open_caption( '', 'Optionen' );
    open_tr();
      open_th( 'right', 'Kontenrahmen:' );
      open_td( '', select_element( $f, array( 'choices' => $choices_kontenrahmen ) ) );
  close_table();
  if( $rows ) {
    open_table('css actions' );
      open_caption( '', 'Aktionen' );
      open_tr( '', inlink( '!', 'class=big button,action=installKontenrahmen,text=Kontenrahmen installieren,confirm=wirklich installieren?' ) );
    close_table();
  }
close_div();

if( $rows ) {
  kontoklassen_view( $rows );
}

?>
