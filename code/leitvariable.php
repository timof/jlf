<?php

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
  , 'default' => '4'
  , 'comment' => 'print debug messages from this level (0: all ... 5: none)'
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '1'
  )
, 'css_corporate_color' => array(
    'meaning' => 'Basic color for scheme'
  , 'default' => '608060'
  , 'comment' => 'RRGGBB hexadecimal value of basic color scheme'
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '6'
  )
, 'css_form_color' => array(
    'meaning' => 'Background color for forms'
  , 'default' => 'd0f0d0'
  , 'comment' => 'RRGGBB hexadecimal value of form background color'
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '6'
  )
, 'css_font_size' => array(
    'meaning' => 'Basic font size'
  , 'default' => '11'
  , 'comment' => 'Basic font size in pt'
  , 'local' => false
  , 'runtime_editable' => 1
  , 'readonly' => 0
  , 'cols' => '2'
  )
);

?>
