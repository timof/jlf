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
    'meaning' => 'Version der Datenbank'
  , 'default' => '1'
  , 'comment' => 'Bitte den vorgeschlagenen Wert &uuml;bernehmen und nicht manuell &auml;ndern: diese Variable wird bei Upgrades automatisch hochgesetzt!'
  , 'local' => false
  , 'runtime_editable' => 0
  , 'readonly' => 1
  , 'cols' => '3'
  )
, 'ip4_prefix' => array(
    'meaning' => 'ip4 network prefix'
  , 'default' => '141.89.116'
  , 'comment' => 'ip4 network prefix'
  , 'local' => false
  , 'runtime_editable' => 1
  , 'cols' => '20'
  )
, 'default_domain' => array(
    'meaning' => 'default domain'
  , 'default' => 'quantum.physik.uni-potsdam.de'
  , 'comment' => 'default domain to append to hostnames'
  , 'local' => false
  , 'runtime_editable' => 1
  , 'cols' => '60'
  )
, 'oid_prefix' => array(
    'meaning' => 'oid prefix AG wilkens'
  , 'default' => '1.3.6.1.4.1.18832.10.4.2'
  , 'comment' => 'oid prefix AG wilkens'
  , 'local' => false
  , 'runtime_editable' => 1
  , 'cols' => '60'
  )
);
?>
