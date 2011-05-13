<?php

// klassen fuer hauptkonten (legt auch eigenschaften der unterkonten fest)
//

// kontenrahmen:
//
global $kontenrahmen;
$kontenrahmen = array();

$kontenrahmen[2] = array(
  array( 'kontoklassen_id' => '10', 'cn' => 'Bankkonto', 'bankkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
, array( 'kontoklassen_id' => '30', 'cn' => 'Debitor', 'personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
, array( 'kontoklassen_id' => '40', 'cn' => 'Sachkonto', 'sachkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
, array( 'kontoklassen_id' => '50', 'cn' => 'sonstige Aktiva', 'kontenkreis' => 'B', 'seite' => 'A' )

, array( 'kontoklassen_id' => '100', 'cn' => 'Kreditor', 'personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P' )
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
);

$kontenrahmen[3] = array(
  array( 'kontoklassen_id' => '10', 'cn' => 'Bankkonto', 'bankkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
, array( 'kontoklassen_id' => '30', 'cn' => 'Debitor', 'personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
, array( 'kontoklassen_id' => '40', 'cn' => 'Sachkonto', 'sachkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
, array( 'kontoklassen_id' => '50', 'cn' => 'sonstige Aktiva', 'kontenkreis' => 'B', 'seite' => 'A' )

, array( 'kontoklassen_id' => '100', 'cn' => 'Kreditor', 'personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P' )
, array( 'kontoklassen_id' => '110', 'cn' => 'sonstige Passiva', 'kontenkreis' => 'B', 'seite' => 'P' )
, array( 'kontoklassen_id' => '120', 'cn' => 'Vortrag', 'vortragskonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P' )

, array( 'kontoklassen_id' => '200', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'privat', 'kontenkreis' => 'E', 'seite' => 'P' )
, array( 'kontoklassen_id' => '210', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'privat', 'kontenkreis' => 'E', 'seite' => 'A' )

);


?>
