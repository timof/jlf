<?php
//
// structure.php: minimal common db structure; may (and usually will) be extended by subproject-local structure.php
//


// minimum set of tables;
// if a subproject also has a structure.php, the local array will be tree_merge'd with this:
//
$tables = array(
  //
  // locks: a table only used as a semaphore
  //
  'locks' => array(
    'cols' => array(
      'locks_id' => array(
        'sql_type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'locks_id' )
    )
  )
  //
  // canary: a table used to detect unlocked tables access
  // it will never be written to and a READ lock can always be obtained to this table without danger of deadlock.
  // access to any other table without explicit locking will then cause an error
  // 
, 'canary' => array(
    'cols' => array(
      'canary_id' => array(
        'sql_type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'canary_id' )
    )
  )
, 'people' => array(
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
      , 'type' => 'w16'
      , 'collation' => 'ascii_bin'
      )
    , 'authentication_methods' => array(
        'sql_type' =>  'varchar(256)'
      , 'type' => 'l256'
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
      // use fully qualified row names with table name `%`, which will be replaced by table alias where needed.
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
    , 'application' => array( // application is also in sessions, but we need it here too if sessions_id === 0!
        'sql_type' => 'varchar(64)'
      , 'type' => 'w64'
      , 'collation' => 'ascii_bin'
      )
    , 'thread' => array(
        'sql_type' =>  'char(1)'
      , 'type' => 'u1'
      , 'collation' => 'ascii_bin'
      )
    , 'window' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w32'
      , 'collation' => 'ascii_bin'
      )
    , 'script' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w32'
      , 'collation' => 'ascii_bin'
      )
    , 'parent_thread' => array(
        'sql_type' =>  'char(1)'
      , 'type' => 'u1'
      , 'collation' => 'ascii_bin'
      )
    , 'parent_window' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w32'
      , 'collation' => 'ascii_bin'
      )
    , 'parent_script' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w32'
      , 'collation' => 'ascii_bin'
      )
    , 'tags' => array(
        'sql_type' =>  'varchar(256)'
      , 'type' => 'l256'
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
    , 'remote_addr' => array( // may or may not be identical to login_remote_ip:login_remote_port!
        'sql_type' =>  "char(21)"
      , 'type' => 'a15'
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
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'logbook_id' )
    )
  , 'viewer' => 'logentry'
  )
, 'leitvariable' => array(
    'cols' => array(
      'name' => array(
        'sql_type' =>  'varchar(128)'
      , 'type' => 'a128'
      , 'pattern' => '/^\w+-(\w+|\*)$/'
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
      , 'type' => 'w16'
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
      , 'type' => 'w64'
      , 'collation' => 'ascii_bin'
      )
    , 'atime' => array(
        'sql_type' =>  'char(15)'
      , 'type' => 't'
      , 'pattern' => '^2\d{7}[.]\d{6}$'
      , 'collation' => 'ascii_bin'
      )
    , 'latest_remote_ip' => array(
        'sql_type' =>  "char(15)"
      , 'type' => 'a15'
      , 'pattern' => '^\d[0-9.]*\d$'
      , 'collation' => 'ascii_bin'
      )
    , 'latest_remote_port' => array(
        'sql_type' =>  "smallint(6)"
      , 'type' => 'u6'
      )
    , 'valid' => array(
        'sql_type' =>  'tinyint(1)'
      , 'type' => 'b'
      )
    , 'gc_lastcheck_utc' => array(
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
, 'persistentvars' => array(
    'cols' => array(
      'persistentvars_id' => array(
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
      , 'type' => 'w32'
      , 'collation' => 'ascii_bin'
      )
    , 'thread' => array(
        'sql_type' =>  'char(1)'
      , 'type' => 'u1'
      , 'collation' => 'ascii_bin'
      )
    , 'script' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w32'
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
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'persistentvars_id' )
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
    , 'itan' => array( // really just the TAN, the primary key is the i
        'sql_type' =>  "varchar(10)"
      , 'type' => 'X10'
      , 'pattern' => '/^[0-9a-z]{10}$/'
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
    , 'tname' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'W64'
      , 'collation' => 'ascii_bin'
      )
    , 'tkey' => array( // a primary key, but we deliberately avoid canonical _id naming here!
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'payload' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION' // creator is also the modifier of the actual entry!
    , 'prev_changelog_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'changelog_id' )
    , 'lookup' => array( 'unique' => 0, 'collist' => 'tname, tkey, ctime' )
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
        'sql_type' => 'char(16)'
      , 'type' => 'X16'
      , 'collation' => 'ascii_bin'
      )
    , 'hexvalue' => array(
        'sql_type' =>  'text'
      , 'type' => 'x'
      , 'collation' => 'ascii_bin'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'uids_id' )
    , 'lookup' => array( 'unique' => 0, 'collist' => 'hexvalue(64)' )
      // ^ cannot be unique to allow parallel writes; reverse lookup is by uids_id
    )
  )
, 'profile' => array(
    'cols' => array(
      'profile_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'U'
      , 'extra' => 'auto_increment'
      )
    , 'utc' => array(
        'sql_type' =>  "char(15)"
      , 'sql_default' => '0'
      , 'type' => 't'
      , 'default' => $GLOBALS['utc']
      , 'collation' => 'ascii_bin'
      )
    , 'sql' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'ascii_bin'
      )
    , 'rows_returned' => array(
        'sql_type' =>  'int(11)'
      , 'type' => 'u'
      )
    , 'wallclock_seconds' => array(
        'sql_type' =>  'decimal(9,6)'
      , 'type' => 'F9'
      )
    , 'script' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w32'
      , 'collation' => 'ascii_bin'
      )
    , 'invocation' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'stack' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'profile_id' )
    , 'lookup' => array( 'unique' => 0, 'collist' => 'script, invocation' )
    )
  , 'viewer' => 'profileentry'
  )
, 'debug' => array(
    'cols' => array(
      'debug_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'U'
      , 'extra' => 'auto_increment'
      )
    , 'script' => array(
        'sql_type' =>  'varchar(32)'
      , 'type' => 'w32'
      , 'collation' => 'ascii_bin'
      )
    , 'utc' => array(
        'sql_type' =>  "char(15)"
      , 'sql_default' => '0'
      , 'type' => 't'
      , 'default' => $GLOBALS['utc']
      , 'collation' => 'ascii_bin'
      )
    , 'facility' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      , 'collation' => 'ascii_bin'
      )
    , 'object' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      , 'collation' => 'ascii_bin'
      )
    , 'stack' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      , 'collation' => 'ascii_bin'
      )
    , 'comment' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      , 'collation' => 'ascii_bin'
      )
    , 'value' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'debug_id' )
    , 'lookup' => array( 'unique' => 0, 'collist' => 'script, facility' )
    )
  , 'viewer' => 'debugentry'
  )
, 'robots' => array(
    'cols' => array(
      'robots_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'U'
      , 'extra' => 'auto_increment'
      )
    , 'ip4' => array(
        'sql_type' =>  "varchar(15)"
      , 'type' => 'a15'
      , 'pattern' => '/^[0-9.]*$/'
      , 'collation' => 'ascii_bin'
      )
    , 'cn' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'h'
      , 'collation' => 'ascii_bin'
      )
    , 'freshmeat' => array(
        'sql_type' =>  'tinyint(1)'
      , 'type' => 'b'
      )
    , 'atime' => array(
        'sql_type' => 'char(15)'
      , 'type' => 't'
      , 'default' => $utc
      , 'collation' => 'ascii_bin'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'robots_id' )
    , 'age' => array( 'unique' => 0, 'collist' => 'atime' )
    , 'robot' => array( 'unique' => 1, 'collist' => 'ip4, cn' )
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
          // with changelog, this is redundant: the creator of a changelog entry is the actual modifier!
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
