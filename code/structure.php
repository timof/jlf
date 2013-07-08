<?php
//
// structure.php: minimal common db structure; may (and usually will) be extended by subproject-local structure.php
//

// constants to be used in table logbook:
//
define( 'LOG_LEVEL_DEBUG', 1 );
define( 'LOG_LEVEL_INFO', 2 );
define( 'LOG_LEVEL_NOTICE', 3 );
define( 'LOG_LEVEL_WARNING', 4 );
define( 'LOG_LEVEL_ERROR', 5 );
 
$log_level_text = array( 1 => 'debug', 2 => 'info', 3 => 'notice', 4 => 'warning', 5 => 'error' );
 
// global debug level: minimum level for messages to be shown
// (preliminary value - to be determined from table leitvariable)
//
$debug_level = LOG_LEVEL_WARNING;

//
// flags: can be combined in a bitmask:
//
define( 'LOG_FLAG_AUTH',   0x01 ); // authentication-related event
define( 'LOG_FLAG_ABORT',  0x02 ); // db operation was aborted
define( 'LOG_FLAG_DELETE', 0x04 ); // involved db deletion operation
define( 'LOG_FLAG_UPDATE', 0x08 ); // involved db update operation
define( 'LOG_FLAG_INSERT', 0x10 ); // involved db insert operation
define( 'LOG_FLAG_INPUT',  0x20 ); // issue in user input
define( 'LOG_FLAG_DATA',   0x40 ); // data model violation
define( 'LOG_FLAG_SYSTEM', 0x80 ); // system operation: garbage collection, gb update, ...
define( 'LOG_FLAG_USER',  0x100 ); // special user operation (forking, ...)
define( 'LOG_FLAG_CODE',  0x200 ); // code model violation

$log_flag_text = array(
  0x01 => 'auth'
, 0x02 => 'abort'
, 0x04 => 'delete'
, 0x08 => 'update'
, 0x10 => 'insert'
, 0x20 => 'input'
, 0x40 => 'consistency'
, 0x80 => 'system'
, 0x100 => 'user'
, 0x200 => 'code'
);


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
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'uid' => array(
        'sql_type' =>  "varchar(16)"
      , 'type' => 'w'
      , 'collation' => 'ascii_bin'
      )
    , 'authentication_methods' => array(
        'sql_type' =>  'text'
      , 'type' => 'l'
      , 'pattern' => '/^[a-zA-Z0-9,]*$/'
      , 'collation' => 'ascii_bin'
      )
    , 'password_hashvalue' => array(
        'sql_type' =>  "varchar(256)"
      , 'type' => 'a256'
      , 'collation' => 'ascii_bin'
      )
    , 'password_hashfunction' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'w64'
      , 'collation' => 'ascii_bin'
      )
    , 'password_salt' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'x64'
      , 'collation' => 'ascii_bin'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'people_id' )
    )
  , 'more_selects' => array(
      // more values to be automatically selected in computed SELECTs:
      // use fully qualified row names with table name `%`, which will be replaced by table aliases where needed.
      //
      'authentication_method_simple' => "CONCAT( ',', `%`.authentication_methods, ',' ) LIKE '%,simple,%' "
    , 'authentication_method_ssl' => "CONCAT( ',', `%`.authentication_methods, ',' ) LIKE '%,ssl,%' "
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
      , 'type' => 'u'
      , 'collation' => 'ascii_bin'
      )
    , 'window' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      , 'collation' => 'ascii_bin'
      )
    , 'script' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      , 'collation' => 'ascii_bin'
      )
    , 'parent_thread' => array(
        'sql_type' =>  'char(1)'
      , 'type' => 'u'
      , 'collation' => 'ascii_bin'
      )
    , 'parent_window' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      , 'collation' => 'ascii_bin'
      )
    , 'parent_script' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      , 'collation' => 'ascii_bin'
      )
    , 'tags' => array(
        'sql_type' =>  'text'
      , 'type' => 'l'
      , 'collation' => 'ascii_bin'
      )
    , 'level' => array(
        'sql_type' =>  'smallint(2)'
      , 'type' => 'u2'
      )
    , 'flags' => array(
        'sql_type' =>  'smallint(4)'
      , 'type' => 'u4'
      )
    , 'links' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'ascii_bin'
      )
    , 'utc' => array(
        'sql_type' =>  "char(15)"
      , 'sql_default' => '0'
      , 'type' => 't'
      , 'default' => $GLOBALS['utc']
      , 'collation' => 'ascii_bin'
      )
    , 'note' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'stack' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      , 'collation' => 'ascii_bin'
      )
    , 'application' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'a64'
      , 'collation' => 'ascii_bin'
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
      , 'collation' => 'ascii_bin'
      )
    , 'value' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'comment' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'leitvariable_id' )
    , 'name' => array( 'unique' => 1, 'collist' => 'name' )
    )
  )
, 'sessions' => array(
    'cols' => array(
      'sessions_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'cookie_signature' => array(
        'sql_type' =>  'varchar(12)'
      , 'type' => 'X12'
      , 'collation' => 'ascii_bin'
      )
    , 'login_authentication_method' => array(
        'sql_type' =>  "varchar(16)"
      , 'type' => 'w'
      , 'collation' => 'ascii_bin'
      )
    , 'login_people_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'ctime' => array(
        'sql_type' =>  "char(15)"
      , 'type' => 't'
      , 'pattern' => '^2\d{7}[.]\d{6}$'
      , 'collation' => 'ascii_bin'
      )
    , 'login_remote_ip' => array(
        'sql_type' =>  "char(15)"
      , 'type' => 'a15'
      , 'pattern' => '^\d[0-9.]*\d$'
      , 'collation' => 'ascii_bin'
      )
    , 'login_remote_port' => array(
        'sql_type' =>  "smallint(6)"
      , 'type' => 'u6'
      )
    , 'application' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'a64'
      , 'collation' => 'ascii_bin'
      )
    , 'atime' => array(
        'sql_type' =>  'char(15)'
      , 'type' => 't'
      , 'pattern' => '^2\d{7}[.]\d{6}$'
      , 'collation' => 'ascii_bin'
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
      , 'collation' => 'ascii_bin'
      )
    , 'thread' => array(
        'sql_type' =>  'char(1)'
      , 'type' => 'u'
      , 'collation' => 'ascii_bin'
      )
    , 'script' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w'
      , 'collation' => 'ascii_bin'
      )
    , 'self' => array(
        'sql_type' =>  'tinyint(1)'
      , 'type' => 'b'
      )
    , 'name' => array(
        'sql_type' =>  'varchar(64)'
      , 'collation' => 'ascii_bin'
      , 'type' => 'w64'
      )
    , 'value' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
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
      , 'collation' => 'ascii_bin'
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
, 'changelog' => array(
    'cols' => array(
      'changelog_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'U'
      , 'extra' => 'auto_increment'
      )
    , 'table' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'W'
      , 'collation' => 'ascii_bin'
      )
    , 'key' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'payload' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    , 'prev_changelog_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'changelog_id' )
    , 'lookup' => array( 'unique' => 0, 'collist' => 'table, ctime' )
    )
  )
, 'uids' => array(
    'cols' => array(
      'uids_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'U'
      , 'extra' => 'auto_increment'
      )
    , 'signature' => array(
        'sql_type' => 'char(10)'
      , 'type' => 'X10'
      , 'collation' => 'ascii_bin'
      )
    , 'value' => array(
        'sql_type' =>  'text'
      , 'type' => 'x'
      , 'collation' => 'ascii_bin'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'uids_id' )
    , 'lookup' => array( 'unique' => 0, 'collist' => 'value(64)' )
    )
  )
);

// expand macros in global $tables. This function is also called from setup.rphp!
//
function expand_table_macros() {
  global $tables, $utc;
  foreach( $tables as $name => $table ) {
    if( ! isset( $table['cols'][ $name.'_id' ] ) ) {
      $tables[ $name ]['cols'][ $name.'_id' ] = array(
        'sql_type' =>  'int(11)'
      , 'type' => 'U'
      , 'extra' => 'auto_increment'
      );
    }
    if( ! isset( $table['indices'] ) ) {
      $tables[ $name ]['indices'] = array();
    }
    if( ! isset( $table['indices']['PRIMARY'] ) ) {
      $tables[ $name ]['indices']['PRIMARY'] = array( 'unique' => 1, 'collist' => $name.'_id' );
    }
    foreach( $table['cols'] as $index => $props ) {
      if( is_numeric( $index ) ) {
        $col = $props;
        $props = false;
      } else {
        $col = $index;
      }
      switch( $col ) {
        case 'CREATION':
          unset( $tables[ $name ]['cols'][ $index ] );
          $tables[ $name ]['cols']['creator_sessions_id'] = array(
            'sql_type' =>  'int(11)'
          , 'type' => ( $props ? $props : 'u' )
          );
          // storing creator_people_id here is data-doubling, but we need this to index on it :-/
          $tables[ $name ]['cols']['creator_people_id'] = array(
            'sql_type' =>  'int(11)'
          , 'type' => 'u'
          );
          $tables[ $name ]['cols']['ctime'] = array(
            'sql_type' => 'char(15)'
          , 'sql_default' => '0'
          , 'type' => 't'
          , 'default' => $utc
          , 'collation' => 'ascii_bin'
          );
          break;
        case 'MODIFICATION':
          unset( $tables[ $name ]['cols'][ $index ] );
          $tables[ $name ]['cols']['modifier_sessions_id'] = array(
            'sql_type' =>  'int(11)'
          , 'type' => ( $props ? $props : 'u' )
          );
          $tables[ $name ]['cols']['mtime'] = array(
            'sql_type' => 'char(15)'
          , 'sql_default' => '0'
          , 'type' => 't'
          , 'default' => $utc
          , 'collation' => 'ascii_bin'
          );
          break;
        case 'CHANGELOG':
          unset( $tables[ $name ]['cols'][ $index ] );
          $tables[ $name ]['cols']['changelog_id'] = array(
            'sql_type' =>  'int(11)'
          , 'sql_default' => '0'
          , 'maxlen' => ( $props ? $props : 256 ) // really the length limit for changelogged values
          , 'type' => 'u'
          );
          break;
        default:
          break;
      }
    }
  }
}


?>
