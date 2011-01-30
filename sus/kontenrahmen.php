<?php

// klassen fuer hauptkonten (legt auch eigenschaften der unterkonten fest)
//

// kontenrahmen:
//
global $kontenrahmen;
$kontenrahmen = array();

$kontenrahmen[2] = array(
  array( 'kontoklassen_id' => '10', 'cn' => 'Bankkonto', 'bankkonto' => 1, 'kontoart' => 'B', 'seite' => 'A' )
, array( 'kontoklassen_id' => '30', 'cn' => 'Debitor', 'personenkonto' => 1, 'kontoart' => 'B', 'seite' => 'A' )
, array( 'kontoklassen_id' => '40', 'cn' => 'Sachkonto', 'sachkonto' => 1, 'kontoart' => 'B', 'seite' => 'A' )
, array( 'kontoklassen_id' => '50', 'cn' => 'sonstige Aktiva', 'kontoart' => 'B', 'seite' => 'A' )

, array( 'kontoklassen_id' => '100', 'cn' => 'Kreditor', 'personenkonto' => 1, 'kontoart' => 'B', 'seite' => 'P' )
, array( 'kontoklassen_id' => '110', 'cn' => 'sonstige Passiva', 'kontoart' => 'B', 'seite' => 'P' )
, array( 'kontoklassen_id' => '120', 'cn' => 'Vortrag', 'vortragskonto' => 1, 'kontoart' => 'B', 'seite' => 'P' )

, array( 'kontoklassen_id' => '200', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'ideeller Bereich', 'kontoart' => 'E', 'seite' => 'P' )
, array( 'kontoklassen_id' => '210', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'ideeller Bereich', 'kontoart' => 'E', 'seite' => 'A' )

, array( 'kontoklassen_id' => '300', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'Vermoegensverwaltung', 'kontoart' => 'E', 'seite' => 'P' )
, array( 'kontoklassen_id' => '310', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'Vermoegensverwaltung', 'kontoart' => 'E', 'seite' => 'A' )

, array( 'kontoklassen_id' => '400', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'Zweckbetrieb', 'kontoart' => 'E', 'seite' => 'P' )
, array( 'kontoklassen_id' => '410', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'Zweckbetrieb', 'kontoart' => 'E', 'seite' => 'A' )

, array( 'kontoklassen_id' => '500', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'wirtschaftlicher Geschaeftsbetrieb', 'kontoart' => 'E', 'seite' => 'P' )
, array( 'kontoklassen_id' => '510', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'wirtschaftlicher Geschaeftsbetrieb', 'kontoart' => 'E', 'seite' => 'A' )
);

?>
