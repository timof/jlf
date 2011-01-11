<?

$tables = array(
  'people' => array(
    'cols' => array(
      'people_id' => array(
        'type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'cn' => array(
        'type' =>  "varchar(128)"
      , 'default' => ''
      )
    , 'uid' => array(
        'type' =>  "varchar(16)"
      , 'default' => ''
      , 'pattern' => 'w'
      )
    , 'authentication_methods' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    , 'password_hashvalue' => array(
        'type' =>  "varchar(256)"
      , 'default' => ''
      )
    , 'password_hashfunction' => array(
        'type' =>  "varchar(256)"
      , 'default' => ''
      )
    , 'password_salt' => array(
        'type' =>  "varchar(256)"
      , 'default' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'people_id' )
    )
  )
, 'hosts' => array(
    'cols' => array(
      'hosts_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'fqhostname' => array(
        'type' =>  "varchar(64)"
      , 'default' => ''
      )
    , 'sequential_number' => array( // bookkeeping: if hardware is replaced
        'type' =>  "int(11)"
      , 'default' => '1'
      , 'pattern' => 'u'
      )
    , 'ip4' => array( // primary IP4 adress
        'type' =>  "varchar(16)"
      , 'default' => ''
      )
    , 'ip6' => array(
        'type' =>  "varchar(64)"
      , 'default' => ''
      )
    , 'oid' => array( // host OID: one-to-one with (fqhostname,sequential_number)
        'type' =>  "varchar(64)"
      , 'default' => ''
      )
    , 'location' => array(
        'type' =>  "varchar(64)"
      , 'default' => ''
      )
    , 'invlabel' => array( // official bookkeeping: sticks to hardware
        'type' =>  "varchar(8)"
      , 'default' => ''
      , 'pattern' => 'w'
      )
    , 'processor' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    , 'os' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'hosts_id' )
      , 'name' => array( 'unique' => 1, 'collist' => 'fqhostname, sequential_number' )
    )
  )
, 'backupprofiles' => array(
    'cols' => array(
      'backupprofiles_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'cn' => array(
        'type' =>  "varchar(64)"
      , 'default' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'accountdomains_id' )
      , 'name' => array( 'unique' => 1, 'collist' => 'accountdomain' )
    )
  )
, 'backupjobs' => array(
    'cols' => array(
      'backupjobs_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'cn' => array(
        'type' =>  'text'
      , 'pattern' => 'h'
      )
    , 'hosts_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'paths_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'accountdomains_id' )
      , 'name' => array( 'unique' => 1, 'collist' => 'accountdomain' )
    )
  )
, 'backupprofiles_backupjobs_relation' => array(
    'cols' => array(
      'backupprofiles_backupjobs_relation_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'backupprofiles_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'backupjobs_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupprofiles_backupjobs_relation_id' )
    )
  )
, 'backupjobs_paths_relation' => array(
    'cols' => array(
      'backupjobs_paths_relation_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'paths_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'backupjobs_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupjobs_paths_relation_id' )
    )
  )
, 'paths' => array(
    'cols' => array(
      'paths_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'cn' => array(
        'type' =>  "varchar(256)"
      , 'default' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'accountdomains_id' )
      , 'name' => array( 'unique' => 1, 'collist' => 'accountdomain' )
    )
  )
, 'accountdomains' => array(
    'cols' => array(
      'accountdomains_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'accountdomain' => array(
        'type' =>  "varchar(64)"
      , 'default' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'accountdomains_id' )
      , 'name' => array( 'unique' => 1, 'collist' => 'accountdomain' )
    )
  )
, 'accountdomains_hosts_relation' => array(
    'cols' => array(
      'accountdomains_hosts_relation_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'accountdomains_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'hosts_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'accountdomains_hosts_relation_id' )
    )
  )
, 'accountdomains_accounts_relation' => array(
    'cols' => array(
      'accountdomains_accounts_relation_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'accountdomains_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'accounts_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'accountdomains_accounts_relation_id' )
    )
  )
, 'websites' => array(
    'cols' => array(
      'websites_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'hosts_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'url' => array ( // primary url to access this site
        'type' => "varchar(256)"
       , 'default' => ''
      )
    , 'comment' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'websites_id' )
    )
  )
, 'disks' => array(
    'cols' => array(
      'disks_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'oid' => array( // sticks to hardware
        'type' =>  "varchar(64)"
      , 'default' => ''
      )
    , 'sizeGB' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'hosts_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'cn' => array( // sticks to hardware
        'type' =>  "varchar(16)"
      , 'default' => ''
      )
    , 'type_disk' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    , 'location' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    , 'description' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    , 'systems_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'disks_id' )
    )
  )
, 'tapes' => array(
    'cols' => array(
      'tapes_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'oid' => array( // sticks to hardware
        'type' =>  "varchar(64)"
      , 'default' => ''
      )
    , 'type_tape' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    , 'tapewritten_first' => array( // first write access to tape
        'type' =>  "date"
      , 'default' => ''
      )
    , 'tapewritten_last' => array( // last write access to tape
        'type' =>  "date"
      , 'default' => ''
      )
    , 'tapewritten_count' => array( // number of _backup sessions_ to this tape
        'type' =>  "date"
      , 'default' => ''
      )
    , 'cn' => array(
        'type' =>  "varchar(64)"
      , 'default' => ''
      )
    , 'good' => array(
        'type' =>  "tinyint(1)"
      , 'pattern' => 'u'
      )
    , 'retired' => array(
        'type' =>  "tinyint(1)"
      , 'pattern' => 'u'
      )
    , 'leot_blocknumber' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'leot_filenumber' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'location' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    , 'description' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'tapes_id' )
    )
  )
, 'tapechunks' => array(
    'cols' => array(
      'tapechunks_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'oid' => array(
        'type' =>  "varchar(64)"
      , 'default' => ''
      )
    , 'tapes_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'hosts_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'backupjobs_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'sizeGB' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'clearhashfunction' => array(
        'type' =>  "varchar(32)"
      , 'pattern' => 'u'
      )
    , 'clearhashvalue' => array(
        'type' =>  "varchar(32)"
      , 'pattern' => 'u'
      )
    , 'crypthashfunction' => array(
        'type' =>  "varchar(32)"
      , 'pattern' => 'u'
      )
    , 'crypthashvalue' => array(
        'type' =>  "varchar(32)"
      , 'pattern' => 'u'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'tapechunks_id' )
      , 'target' => array( 'unique' => 0, 'collist' => 'hosts_id', 'backupjobs_id' )
    )
  )
, 'services' => array(
    'cols' => array(
      'services_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'type_service' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    , 'description' => array(
        'type' =>  "date"
      , 'default' => ''
      )
    , 'hosts_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'url' => array(
        'type' =>  "varchar(256)"
      , 'default' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'services_id' )
    )
  )
, 'accounts' => array(
    'cols' => array(
      'accounts_id' => array(
        'type' =>  'int(11)'
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'hosts_id' => array(
        'type' =>  'int(11)'
      , 'pattern' => 'u'
      )
    , 'uid' => array(
        'type' =>  "varchar(8)"
      , 'default' => ''
      )
    , 'uidnumber' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'people_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'accounts_id' )
    )
  )
, 'leitvariable' => array(
    'cols' => array(
      'name' => array(
        'type' =>  'varchar(30)'
      , 'default' => ''
      )
    , 'value' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    , 'comment' => array(
        'type' =>  "text"
      , 'default' => ''
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
        'type' =>  "varchar(10)"
      , 'pattern' => 'w'
      )
    , 'login_authentication_method' => array(
        'type' =>  "varchar(16)"
      , 'default' => '0'
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
    , 'window_id' => array(
        'type' =>  'varchar(32)'
      , 'pattern' => 'w'
      )
    , 'name' => array(
        'type' =>  'varchar(32)'
      , 'pattern' => 'w'
      )
    , 'value' => array(
        'type' =>  'text'
      , 'pattern' => 'r'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'sessions_id, window, window_id, name' )
    )
  )
, 'systems' => array(
    'cols' => array(
      'systems_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'arch' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    , 'type' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    , 'date_built' => array(
        'type' =>  "date"
      , 'default' => ''
      )
    , 'parent_systems_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'description' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'systems_id' )
    )
  )
, 'logbook' => array(
    'cols' => array(
      'logbook_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'sessions_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'timestamp' => array(
        'type' =>  "timestamp"
      , 'default' => 'CURRENT_TIMESTAMP'
      )
    , 'note' => array(
        'type' =>  "text"
      , 'default' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'logbook_id' )
    )
  )
, 'transactions' => array(
    'cols' => array(
      'transactions_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'used' => array(
        'type' =>  "tinyint(1)"
      , 'pattern' => 'u'
      )
    , 'itan' => array(
        'type' =>  "varchar(10)"
      , 'pattern' => 'l'
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
      logger( 'starting update_database: from version 0' );
      sql_do( "ALTER TABLE Dienste ADD `dienstkontrollblatt_id` INT NULL DEFAULT NULL "
      , "update_database from version 0 to version 1 FAILED"
      );
      sql_update( 'leitvariable', array( 'name' => 'database_version' ), array( 'value' => 1 ) );
      logger( 'update_database: update to version 1 SUCCESSFUL' );
  }
}

?>
