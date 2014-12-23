<?php

$leitvariable = array(
  'kontenrahmen_version' => array(
    'meaning' => 'Version des Kontenrahmens in der Datenbank'
  , 'default' => ''
  , 'comment' => 'Bitte den vorgeschlagenen Wert uebernehmen und nicht manuell aendern: diese Variable wird bei Aenderung des Kontenrahmens automatisch aktualisiert!'
  , 'runtime_editable' => 0
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '32'
  )
, 'unterstuetzung_geschaeftsbereiche' => array(
    'meaning' => 'Geschaeftsbereiche unterstuetzen'
  , 'default' => '1'
  , 'comment' => 'Flag: sollen unterschiedliche Geschaeftsbereiche fuer Erfolgskonten unterstuetzt werden?'
  , 'runtime_editable' => 0
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '1'
  )
, 'valuta_letzte_buchung' => array(
    'meaning' => 'Valuta der letzten Buchung'
  , 'default' => '20141001'
  , 'comment' => 'Default-Datum fuer neue Buchungen'
  , 'runtime_editable' => 1
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'geschaeftsjahr_min' => array(
    'meaning' => 'minimales Geschaeftsjahr'
  , 'default' => '2014'
  , 'comment' => 'Kontenrahmen wird ab diesem Jahr angelegt'
  , 'runtime_editable' => 0
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'geschaeftsjahr_abgeschlossen' => array(
    'meaning' => 'letztes abgeschlossenes Geschaeftsjahr'
  , 'default' => '2013'
  , 'comment' => 'Geschaeftsjahr bis einschliesslich diesem sind abgeschlossen'
  , 'runtime_editable' => 0
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'geschaeftsjahr_current' => array(
    'meaning' => 'aktuelles Geschaeftsjahr'
  , 'default' => '2014'
  , 'comment' => 'default fuer Geschaeftsjahr'
  , 'runtime_editable' => 0
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'geschaeftsjahr_max' => array(
    'meaning' => 'maximales Geschaeftsjahr'
  , 'default' => '2014'
  , 'comment' => 'maximales Jahr fuer ausgefuehrte Buchungen und automatischen Saldovortrag von Bestandskonten'
  , 'runtime_editable' => 0
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'default_girokonto_id' => array(
    'meaning' => 'meist benutztes Girokonto'
  , 'default' => '0'
  , 'comment' => 'Konto bitte nicht hier, sondern im Konfiguration-Skript setzen!'
  , 'runtime_editable' => 0
  , 'per_application' => 1
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'default_erfolgskonto_zinsaufwand_id' => array(
    'meaning' => 'Erfolgskonto fuer Zinsen'
  , 'default' => '0'
  , 'comment' => 'Konto bitte nicht hier, sondern im Konfiguration-Skript setzen!'
  , 'runtime_editable' => 0
  , 'per_application' => 1
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'autovortragskonten' => array(
    'meaning' => 'Konten fuer automatische Vortragsbuchungen'
  , 'default' => '0'
  , 'comment' => 'Konto bitte nicht hier, sondern im Konfiguration-Skript setzen!'
  , 'runtime_editable' => 0
  , 'per_application' => 1
  , 'readonly' => 0
  , 'cols' => '20'
  )
, 'attribute_0' => array(
    'meaning' => 'frei definierbares Konto-Attribut 0'
  , 'default' => ''
  , 'comment' => ''
  , 'runtime_editable' => 1
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_1' => array(
    'meaning' => 'frei definierbares Konto-Attribut 1'
  , 'default' => ''
  , 'comment' => ''
  , 'runtime_editable' => 1
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_2' => array(
    'meaning' => 'frei definierbares Konto-Attribut 2'
  , 'default' => ''
  , 'comment' => ''
  , 'runtime_editable' => 1
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_3' => array(
    'meaning' => 'frei definierbares Konto-Attribut 3'
  , 'default' => ''
  , 'comment' => ''
  , 'runtime_editable' => 1
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_4' => array(
    'meaning' => 'frei definierbares Konto-Attribut 4'
  , 'default' => ''
  , 'comment' => ''
  , 'runtime_editable' => 1
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_5' => array(
    'meaning' => 'frei definierbares Konto-Attribut 5'
  , 'default' => ''
  , 'comment' => ''
  , 'runtime_editable' => 1
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_6' => array(
    'meaning' => 'frei definierbares Konto-Attribut 6'
  , 'default' => ''
  , 'comment' => ''
  , 'runtime_editable' => 1
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_7' => array(
    'meaning' => 'frei definierbares Konto-Attribut 7'
  , 'default' => ''
  , 'comment' => ''
  , 'runtime_editable' => 1
  , 'per_application' => 0
  , 'readonly' => 0
  , 'cols' => '40'
  )
);

?>
