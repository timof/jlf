<?php


$tables = array(
  'people' => array(
    'cols' => array(
      'people_id' => array(
        'type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'jperson' => array(
        'type' =>  "tinyint(1)"
      , 'default' => '0'
      , 'pattern' => '/^[01]$/'
      )
    , 'cn' => array(
        'type' =>  "varchar(128)"
      , 'pattern' => 'H'
      )
    , 'sn' => array(
        'type' =>  "varchar(128)"
      )
    , 'gn' => array(
        'type' =>  "varchar(128)"
      )
    , 'title' => array(
        'type' =>  "varchar(64)"
      )
    , 'telephonenumber' => array(
        'type' =>  "varchar(64)"
      , 'pattern' => '/^[0-9 ]+$/'
      )
    , 'mail' => array(
        'type' =>  "varchar(64)"
      , 'pattern' => '/^$|^[0-9a-zA-Z._-]@[0-9a-zA-Z.]+$/'
      )
    , 'street' => array(
        'type' =>  'text'
      )
    , 'street2' => array(
        'type' =>  'text'
      )
    , 'city' => array(
        'type' =>  'text'
      )
    , 'country' => array(
        'type' =>  'text'
      )
    , 'note' => array(
        'type' =>  'text'
      )
    , 'memberof_people_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'uid' => array(
        'type' =>  "varchar(16)"
      , 'pattern' => 'w'
      )
    , 'authentication_methods' => array(
        'type' =>  'text'
      , 'pattern' => '/^[a-zA-Z0-9,]*$/'
      )
    , 'password_hashvalue' => array(
        'type' =>  "varchar(256)"
      , 'pattern' => 'r'
      )
    , 'password_hashfunction' => array(
        'type' =>  "varchar(256)"
      , 'pattern' => 'w'
      )
    , 'password_salt' => array(
        'type' =>  "varchar(256)"
      , 'pattern' => '/^[0-9a-f]*$/'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'people_id' )
    )
  )
, 'pruefungen' => array(
    'cols' => array(
      'pruefungen_id' => array(
        'type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'cn' => array(
        'type' =>  "varchar(128)"
      , 'pattern' => 'H'
      )
    , 'semester' => array(
        'type' =>  "int(4)"
      , 'pattern' => 'u'
      )
    , 'abschluss' => array(
        'type' =>  "varchar(32)"
      , 'pattern' => 'h'
      )
    , 'kommentar' => array(
        'type' =>  "text"
      )
    , 'dozent' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'datum' => array(
        'type' =>  "date"
      , 'default' => '0000-00-00'
      , 'pattern' => '/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/'
      )
    , 'zeit' => array(
        'type' => "time"
      , 'default' => '00:00:00'
      , 'pattern' => '/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'pruefungen_id' )
    , 'zeit' => array( 'unique' => 0, 'collist' => 'datum, zeit'  )
    , 'zielgruppe' => array( 'unique' => 0, 'collist' => 'semester, abschluss, datum'  )
    )
  )
, 'themen' => array(
    'cols' => array(
      'themen_id' => array(
        'type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'cn' => array(
        'type' =>  "text"
      , 'pattern' => 'H'
      )
    , 'dozent' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'abschluss' => array(
        'type' =>  "varchar(32)"
      , 'pattern' => 'h'
      )
    , 'beschreibung' => array(
        'type' => "text"
      , 'pattern' => 'h'
      )
    , 'url' => array(
        'type' => "text"
      , 'pattern' => 'h'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'themen_id' )
    )
  )
, 'logbook' => array(
    'cols' => array(
      'logbook_id' => array(
        'type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'sessions_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'thread' => array(
        'type' =>  'char(1)'
      , 'pattern' => 'w'
      )
    , 'window' => array(
        'type' =>  'varchar(32)'
      , 'pattern' => 'w'
      )
    , 'script' => array(
        'type' =>  'varchar(32)'
      , 'pattern' => 'w'
      )
    , 'parent_thread' => array(
        'type' =>  'char(1)'
      , 'pattern' => 'w'
      )
    , 'parent_window' => array(
        'type' =>  'varchar(32)'
      , 'pattern' => 'w'
      )
    , 'parent_script' => array(
        'type' =>  'varchar(32)'
      , 'pattern' => 'w'
      )
    , 'event' => array(
        'type' =>  'varchar(32)'
      , 'pattern' => 'w'
      )
    , 'parent_script' => array(
        'type' =>  'varchar(32)'
      , 'pattern' => 'w'
      )
    , 'timestamp' => array(
        'type' =>  "timestamp"
      , 'default' => 'CURRENT_TIMESTAMP'
      )
    , 'note' => array(
        'type' =>  'text'
      )
    , 'stack' => array(
        'type' =>  'text'
      , 'pattern' => 'h'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'logbook_id' )
    )
  )
, 'leitvariable' => array(
    'cols' => array(
      'name' => array(
        'type' =>  'varchar(64)'
      , 'pattern' => 'W'
      )
    , 'value' => array(
        'type' =>  'text'
      )
    , 'comment' => array(
        'type' =>  'text'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'name' )
    )
  )
, 'sessions' => array(
    'cols' => array(
      'sessions_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'cookie' => array(
        'type' =>  'varchar(12)'
      , 'pattern' => '/^[0-9a-f]+$/'
      )
    , 'login_authentication_method' => array(
        'type' =>  "varchar(16)"
      , 'pattern' => 'w'
      )
    , 'login_people_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'sessions_id' )
    )
  )
, 'sessionvars' => array(
    'cols' => array(
      'sessions_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'window' => array(
        'type' =>  'varchar(32)'
      , 'pattern' => 'w'
      )
    , 'thread' => array(
        'type' =>  'char(1)'
      , 'pattern' => 'w'
      )
    , 'script' => array(
        'type' =>  'varchar(32)'
      , 'pattern' => 'w'
      )
    , 'self' => array(
        'type' =>  'tinyint(1)'
      , 'pattern' => 'u'
      )
    , 'name' => array(
        'type' =>  'varchar(64)'
      , 'pattern' => 'w'
      )
    , 'value' => array(
        'type' =>  'text'
      , 'pattern' => 'r'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'sessions_id, thread, window, script, name' )
    )
  )
, 'transactions' => array(
    'cols' => array(
      'transactions_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'U'
      , 'extra' => 'auto_increment'
      )
    , 'used' => array(
        'type' =>  "tinyint(1)"
      , 'default' => '0'
      , 'pattern' => '/^[01]$/'
      )
    , 'itan' => array(
        'type' =>  "varchar(10)"
      , 'pattern' => '/^[0-9]+_[0-9a-z]+$/'
      )
    , 'sessions_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'transactions_id' )
    )
  )
);

function update_database() {
  global $database_version; // from leitvariable
  switch( $database_version ) {
    case 0:
      //
  }

}


?>
