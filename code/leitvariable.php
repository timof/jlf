<?

$leitvariable = array(
  'readonly' => array(
    'meaning' => 'Datenbank schreibgeschuetzt setzen (einige sehr eingeschränkte Schreibzugriffe sind dennoch moeglich)'
  , 'default' => '0'
  , 'local' => true
  , 'comment' => 'Flag (1 oder 0), um &Auml;nderungen an der Datenbank, etwa w&auml;hrend offline-Betrieb auf
                  einem anderen Rechner, zu verhindern'
  , 'pattern' => '/^[01]$/'
  , 'runtime_editable' => 1
  , 'cols' => '1'
  )
, 'allowed_authentication_methods' => array(
    'meaning' => 'comma-separated list of allowed authentication methods'
  , 'default' => 'ssl'
  , 'local' => false
  , 'comment' => '(currently implemented: simple (ordinary password login) and ssl (client certificate)'
  , 'runtime_editable' => 1
  , 'cols' => '30'
  )
, 'database_version' => array(
    'meaning' => 'Version der Datenbank (_Struktur_ der Datenbank)'
  , 'default' => '1'
  , 'comment' => 'Bitte den vorgeschlagenen Wert &uuml;bernehmen und nicht manuell &auml;ndern: diese Variable wird bei Upgrades automatisch hochgesetzt!'
  , 'local' => false
  , 'runtime_editable' => 0
  , 'readonly' => 0
  , 'cols' => '3'
  )
, 'debug_level' => array(
    'meaning' => 'Debug level'
  , 'default' => '2'
  , 'comment' => 'print debug messages up to this level (0: none ... 5: all)'
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '1'
  )
);

?>
