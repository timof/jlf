<?php
//
// structure.php: minimal common db structure; may (and usually will) be extended by subproject-local structure.php
//


// minimum set of tables;
// if a subproject also has a structure.php, the local array will be tree_merge'd with this:
//
$tables = array(
  'people' => array(
    'cols' => array(
      'people_id' => array(
        'sql_type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'cn' => array(
        'sql_type' =>  "varchar(128)"
      , 'type' => 'H128'
      )
    , 'uid' => array(
        'sql_type' =>  "varchar(16)"
      , 'type' => 'w'
      )
    , 'authentication_methods' => array(
        'sql_type' =>  'text'
      , 'type' => 'l'
      , 'pattern' => '/^[a-zA-Z0-9,]*$/'
      )
    , 'password_hashvalue' => array(
        'sql_type' =>  "varchar(256)"
      , 'type' => 'a256'
      )
    , 'password_hashfunction' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'w64'
      )
    , 'password_salt' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'x64'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'people_id' )
    )
  )
, 'logbook' => array(
    'cols' => array(
      'logbook_id' => array(
        'sql_type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'sessions_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'thread' => array(
        'sql_type' =>  'char(1)'
      , 'type' => 'w'
      )
    , 'window' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      )
    , 'script' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      )
    , 'parent_thread' => array(
        'sql_type' =>  'char(1)'
      , 'type' => 'w'
      )
    , 'parent_window' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      )
    , 'parent_script' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      )
    , 'event' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      )
    , 'utc' => array(
        'sql_type' =>  "char(15)"
      , 'sql_default' => '00000000.000000'
      , 'type' => 't'
      , 'default' => $GLOBALS['utc']
      )
    , 'note' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      )
    , 'stack' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'logbook_id' )
    )
  )
, 'leitvariable' => array(
    'cols' => array(
      'name' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'W'
      )
    , 'value' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      )
    , 'comment' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'name' )
    )
  )
, 'sessions' => array(
    'cols' => array(
      'sessions_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'cookie' => array(
        'sql_type' =>  'varchar(12)'
      , 'type' => 'X12'
      )
    , 'login_authentication_method' => array(
        'sql_type' =>  "varchar(16)"
      , 'type' => 'w'
      )
    , 'login_people_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'ctime' => array(
        'sql_type' =>  "char(15)"
      , 'type' => 't'
      , 'pattern' => '^2\d{7}[.]\d{6}$'
      )
    , 'login_remote_ip' => array(
        'sql_type' =>  "char(15)"
      , 'type' => 'a15'
      , 'pattern' => '^\d[0-9.]*\d$'
      )
    , 'login_remote_port' => array(
        'sql_type' =>  "smallint(6)"
      , 'type' => 'u6'
      )
    , 'atime' => array(
        'sql_type' =>  'char(15)'
      , 'type' => 't'
      , 'pattern' => '^2\d{7}[.]\d{6}$'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'sessions_id' )
    )
  )
, 'persistent_vars' => array(
    'cols' => array(
      'persistent_vars_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'sessions_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'people_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'window' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      )
    , 'thread' => array(
        'sql_type' =>  'char(1)'
      , 'type' => 'w'
      )
    , 'script' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      )
    , 'self' => array(
        'sql_type' =>  'tinyint(1)'
      , 'type' => 'b'
      )
    , 'name' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'w64'
      )
    , 'value' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    , 'json' => array(
        'sql_type' => 'tinyint(1)'
      , 'type' => 'b'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'persistent_vars_id' )
    , 'secondary' => array( 'unique' => 1, 'collist' => 'people_id, sessions_id, thread, window, script, name' )
    )
  )
, 'transactions' => array(
    'cols' => array(
      'transactions_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'U'
      , 'extra' => 'auto_increment'
      )
    , 'used' => array(
        'sql_type' =>  "tinyint(1)"
      , 'type' => 'b'
      )
    , 'itan' => array(
        'sql_type' =>  "varchar(10)"
      , 'type' => 'W'
      , 'pattern' => '/^[0-9]+_[0-9a-z]+$/'
      )
    , 'sessions_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'U'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'transactions_id' )
    )
  )
);



?>
