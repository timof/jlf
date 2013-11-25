<?php

$leitvariable = array(
  'allowed_authentication_methods' => array(
    'meaning' => 'comma-separated list of allowed authentication methods'
  , 'default' => ''
  , 'comment' => 'currently implemented: simple (ordinary password login), ssl (client certificate), public (no authentication)'
  , 'runtime_editable' => 1
  , 'per_application' => 1
  , 'cols' => '30'
  )
, 'allow_url_cookies' => array(
    'meaning' => 'whether to support session cookies in url (0 or 1)'
  , 'default' => '0'
  , 'comment' => 'browser cookies will be used if available; allowing url cookies provides a failsafe fallback, with some caveats'
  , 'runtime_editable' => 1
  , 'per_application' => 1
  , 'cols' => '1'
  )
, 'database_version' => array(
    'meaning' => 'database version (_structure_ of database)'
  , 'default' => '1'
  , 'comment' => 'please accept the suggested value and never change this manually; will automatically be adjusted by upgrades'
  , 'runtime_editable' => 0
  , 'per_application' => 0
  , 'cols' => '3'
  )
, 'show_debug_button' => array(
    'meaning' => 'show debug button'
  , 'default' => '1'
  , 'comment' => 'whether to display a debug button in page banner (disable for production servers)'
  , 'runtime_editable' => 1
  , 'per_application' => 1
  , 'cols' => '1'
  )
, 'session_lifetime' => array(
    'meaning' => 'session lifetime in seconds'
  , 'default' => '200000'
  , 'comment' => 'sessions expire after that many seconds without user interaction'
  , 'runtime_editable' => 1
  , 'per_application' => 1
  , 'cols' => '6'
  )
, 'insert_nonce_in_urls' => array(
    'meaning' => 'insert random nonce in urls'
  , 'default' => '1'
  , 'comment' => 'whether to insert a random nonce to prevent caching into every internal url'
  , 'runtime_editable' => 1
  , 'per_application' => 1
  , 'cols' => '1'
  )
, 'css_corporate_color' => array(
    'meaning' => 'basic color for scheme'
  , 'default' => '608060'
  , 'comment' => 'RRGGBB hexadecimal value of basic color scheme'
  , 'runtime_editable' => 1
  , 'per_application' => 1
  , 'cols' => '6'
  )
, 'css_form_color' => array(
    'meaning' => 'background color for forms'
  , 'default' => 'd0f0d0'
  , 'comment' => 'RRGGBB hexadecimal value of form background color'
  , 'runtime_editable' => 1
  , 'per_application' => 1
  , 'cols' => '6'
  )
, 'css_font_size' => array(
    'meaning' => 'basic font size'
  , 'default' => '10'
  , 'comment' => 'basic font size in pt'
  , 'runtime_editable' => 1
  , 'per_application' => 1
  , 'cols' => '2'
  )
, 'bannertext1' => array(
    'meaning' => 'banner text (first line)'
  , 'default' => ''
  , 'comment' => 'displayed in window head'
  , 'runtime_editable' => 1
  , 'per_application' => 1
  , 'cols' => '40'
  )
, 'bannertext2' => array(
    'meaning' => 'banner text (second line)'
  , 'default' => ''
  , 'comment' => 'displayed in window head'
  , 'runtime_editable' => 1
  , 'per_application' => 1
  , 'cols' => '40'
  )
, 'global_lock' => array(
    'meaning' => 'used only internally as a talking stick. the value is irrelevant.'
  , 'default' => ''
  , 'comment' => 'this entry is used for table locking, similar to a talking stick. the value is irrelevant.'
  , 'runtime_editable' => 1
  , 'per_application' => 0
  , 'cols' => '20'
  )
);

?>
