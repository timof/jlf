<?

$leitvariable = array(
  'kontenrahmen_version' => array(
    'meaning' => 'Version des Kontenrahmens in der Datenbank'
  , 'default' => '0'
  , 'comment' => 'Bitte den vorgeschlagenen Wert &uuml;bernehmen und nicht manuell &auml;ndern: diese Variable wird bei Aenderung des Kontenrahmens automatisch aktualisiert!'
  , 'local' => false
  , 'runtime_editable' => 0
  , 'readonly' => 0
  , 'cols' => '3'
  )
, 'unterstuetzung_geschaeftsbereiche' => array(
    'meaning' => 'Geschaeftsbereiche unterstuetzen'
  , 'default' => '1'
  , 'comment' => 'Flag: sollen unterschiedliche Geschaeftsbereiche fuer Erfolgskonten unterstuetzt werden?'
  , 'local' => false
  , 'runtime_editable' => 0
  , 'readonly' => 0
  , 'cols' => '1'
  )
, 'valuta_letzte_buchung' => array(
    'meaning' => 'Valuta der letzten Buchung'
  , 'default' => '20110101'
  , 'comment' => 'Default-Datum fuer neue Buchungen'
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'geschaeftsjahr_min' => array(
    'meaning' => 'minimales Geschaeftsjahr'
  , 'default' => '2010'
  , 'comment' => 'Kontenrahmen wird ab diesem Jahr angelegt'
  , 'local' => false
  , 'runtime_editable' => 0
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'geschaeftsjahr_abgeschlossen' => array(
    'meaning' => 'letztes abgeschlossenes Geschaeftsjahr'
  , 'default' => '2009'
  , 'comment' => 'Geschaeftsjahr bis einschliesslich diesem sind abgeschlossen'
  , 'local' => false
  , 'runtime_editable' => 0
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'geschaeftsjahr_current' => array(
    'meaning' => 'aktuelles Geschaeftsjahr'
  , 'default' => '2010'
  , 'comment' => 'default fuer Geschaeftsjahr'
  , 'local' => false
  , 'runtime_editable' => 0
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'geschaeftsjahr_max' => array(
    'meaning' => 'maximales Geschaeftsjahr'
  , 'default' => '2010'
  , 'comment' => 'Kontenrahmen wird bis einschliesslich diesem Jahr angelegt'
  , 'local' => false
  , 'runtime_editable' => 0
  , 'readonly' => 0
  , 'cols' => '8'
  )
, 'attribute_0' => array(
    'meaning' => 'frei definierbares Konto-Attribut 0'
  , 'default' => ''
  , 'comment' => ''
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_1' => array(
    'meaning' => 'frei definierbares Konto-Attribut 1'
  , 'default' => ''
  , 'comment' => ''
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_2' => array(
    'meaning' => 'frei definierbares Konto-Attribut 2'
  , 'default' => ''
  , 'comment' => ''
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_3' => array(
    'meaning' => 'frei definierbares Konto-Attribut 3'
  , 'default' => ''
  , 'comment' => ''
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_4' => array(
    'meaning' => 'frei definierbares Konto-Attribut 4'
  , 'default' => ''
  , 'comment' => ''
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_5' => array(
    'meaning' => 'frei definierbares Konto-Attribut 5'
  , 'default' => ''
  , 'comment' => ''
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_6' => array(
    'meaning' => 'frei definierbares Konto-Attribut 6'
  , 'default' => ''
  , 'comment' => ''
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '40'
  )
, 'attribute_7' => array(
    'meaning' => 'frei definierbares Konto-Attribut 7'
  , 'default' => ''
  , 'comment' => ''
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '40'
  )
);

?>
