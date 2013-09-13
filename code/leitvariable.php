<?php

$leitvariable = array(
  'readonly' => array(
    'meaning' => 'Datenbank schreibgeschuetzt setzen (einige sehr eingeschränkte Schreibzugriffe sind dennoch moeglich)'
  , 'default' => '0'
  , 'comment' => 'Flag (1 oder 0), um &Auml;nderungen an der Datenbank, etwa w&auml;hrend offline-Betrieb auf
                  einem anderen Rechner, zu verhindern'
  , 'pattern' => '/^[01]$/'
  , 'runtime_editable' => 1
  , 'cols' => '1'
  )
, 'allowed_authentication_methods' => array(
    'meaning' => 'comma-separated list of allowed authentication methods'
  , 'default' => ''
  , 'comment' => 'currently implemented: simple (ordinary password login), ssl (client certificate), public (no authentication)'
  , 'runtime_editable' => 1
  , 'cols' => '30'
  )
, 'allow_url_cookies' => array(
    'meaning' => 'whether to support session cookies in url (0 or 1)'
  , 'default' => '0'
  , 'comment' => 'browser cookies will be used if available; allowing url cookies provides a failsafe fallback, with some caveats'
  , 'runtime_editable' => 1
  , 'cols' => '1'
  )
, 'database_version' => array(
    'meaning' => 'Version der Datenbank (_Struktur_ der Datenbank)'
  , 'default' => '1'
  , 'comment' => 'Bitte den vorgeschlagenen Wert &uuml;bernehmen und nicht manuell &auml;ndern: diese Variable wird bei Upgrades automatisch hochgesetzt!'
  , 'runtime_editable' => 0
  , 'cols' => '3'
  )
, 'show_debug_button' => array(
    'meaning' => 'Show debug button'
  , 'default' => '1'
  , 'comment' => 'whether to display a debug button in page banner (disable for production servers)'
  , 'runtime_editable' => 1
  , 'cols' => '1'
  )
, 'session_lifetime' => array(
    'meaning' => 'Session lifetime in seconds'
  , 'default' => '200000'
  , 'comment' => 'Sessions expire after that many seconds without user interaction'
  , 'runtime_editable' => 1
  , 'cols' => '6'
  )
, 'css_corporate_color' => array(
    'meaning' => 'Basic color for scheme'
  , 'default' => '608060'
  , 'comment' => 'RRGGBB hexadecimal value of basic color scheme'
  , 'runtime_editable' => 1
  , 'cols' => '6'
  )
, 'css_form_color' => array(
    'meaning' => 'Background color for forms'
  , 'default' => 'd0f0d0'
  , 'comment' => 'RRGGBB hexadecimal value of form background color'
  , 'runtime_editable' => 1
  , 'cols' => '6'
  )
, 'css_font_size' => array(
    'meaning' => 'Basic font size'
  , 'default' => '11'
  , 'comment' => 'Basic font size in pt'
  , 'runtime_editable' => 1
  , 'cols' => '2'
  )
, 'bannertext1' => array(
    'meaning' => 'banner text (first line)'
  , 'default' => ''
  , 'comment' => 'displayed in window head'
  , 'runtime_editable' => 1
  , 'cols' => '40'
  )
, 'bannertext2' => array(
    'meaning' => 'banner text (second line)'
  , 'default' => ''
  , 'comment' => 'displayed in window head'
  , 'runtime_editable' => 1
  , 'cols' => '40'
  )
, 'global_lock' => array(
    'meaning' => 'used only internally as a talking stick. the value is irrelevant.'
  , 'default' => ''
  , 'comment' => 'this entry is used for table locking, similar to a talking stick. the value is irrelevant.'
  , 'runtime_editable' => 1
  , 'cols' => '20'
  )
);

?>
