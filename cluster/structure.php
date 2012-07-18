<?

$disk_interfaces = array( 'P-ATA', 'P-SCSI', 'S-ATA', 'SAS' );
$disk_types = array( 'rotating magnetic disk', 'solid state' );
$tape_types = array( 'DDS-3', 'DDS-4', 'SDLT-320', 'LTO-3', 'LTO-4' );

$tables = array(
  'hosts' => array(
    'cols' => array(
      'hosts_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'fqhostname' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'h64'
      , 'pattern' => '/^[a-zA-Z0-9.]+$/'
      )
    , 'sequential_number' => array( // bookkeeping: if hardware is replaced
        'sql_type' =>  "int(11)"
      , 'default' => '1'
      , 'type' => 'u'
      )
    , 'ip4' => array( // primary IP4 adress
        'sql_type' =>  "varchar(15)"
      , 'type' => 'a15'
      , 'pattern' => '/^[0-9.]*$/'
      )
    , 'ip6' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'a64'
      , 'pattern' => '/^[0-9.:]*$/'
      )
    , 'oid' => array( // host OID: one-to-one with (fqhostname,sequential_number)
        'sql_type' =>  "varchar(240)"
      , 'type' => 'a240'
      , 'pattern' => '/^[0-9.]*$/'
      )
    , 'location' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'a64'
      )
    , 'invlabel' => array( // official bookkeeping: sticks to hardware
        'sql_type' =>  "varchar(8)"
      , 'type' => 'w8'
      )
    , 'processor' => array(
        'sql_type' =>  "text"
      , 'type' => 'a128'
      )
    , 'year_manufactured' => array(
        'sql_type' => "int(4)"
      , 'type' => 'u4'
      )
    , 'year_decommissioned' => array(
        'sql_type' => "int(4)"
      , 'type' => 'u4'
      )
    , 'description' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      )
    , 'os' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'hosts_id' )
    , 'name' => array( 'unique' => 1, 'collist' => 'fqhostname, sequential_number' )
    )
  )
, 'backupprofiles' => array(
    'cols' => array(
      'backupprofiles_id' => array(
        'sql_type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'cn' => array(
        'sql_type' =>  "varchar(64)"
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupprofiles_id' )
    )
  )
, 'backupjobs' => array(
    'cols' => array(
      'backupjobs_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'cn' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      )
    , 'hosts_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'path' => array(
        'sql_type' =>  "varchar(128)"
      , 'type' => 'a128'
      , 'pattern' => '/^[a-zA-Z0-9./]*$/'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupjobs_id' )
    , 'profile' => array( 'unique' => 0, 'collist' => 'cn, hosts_id, path' )
    , 'content' => array( 'unique' => 0, 'collist' => 'hosts_id, path' )
    )
  )
, 'backupprofiles_backupjobs_relation' => array(
    'cols' => array(
      'backupprofiles_backupjobs_relation_id' => array(
        'sql_type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'backupprofiles_id' => array(
        'sql_type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'backupjobs_id' => array(
        'sql_type' => "int(11)"
      , 'pattern' => 'u'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupprofiles_backupjobs_relation_id' )
    , 'profiles' => array( 'unique' => 1, 'collist' => 'backupprofiles_id, backupjobs_id' )
    , 'jobs' => array( 'unique' => 1, 'collist' => 'backupjobs_id, backupprofiles_id' )
    )
  )
, 'accountdomains' => array(
    'cols' => array(
      'accountdomains_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'accountdomain' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'W64'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'accountdomains_id' )
    , 'name' => array( 'unique' => 1, 'collist' => 'accountdomain' )
    )
  )
, 'accountdomains_hosts_relation' => array(
    'cols' => array(
      'accountdomains_hosts_relation_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'accountdomains_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'hosts_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'accountdomains_hosts_relation_id' )
    )
  )
, 'accountdomains_accounts_relation' => array(
    'cols' => array(
      'accountdomains_accounts_relation_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'accountdomains_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'accounts_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'accountdomains_accounts_relation_id' )
    )
  )
, 'websites' => array(
    'cols' => array(
      'websites_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'hosts_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'url' => array ( // primary url to access this site
        'sql_type' => "varchar(256)"
      , 'type' => 'a256'
      , 'pattern' => '^https?://[[:alnum:]./]+$/'
      )
    , 'description' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'websites_id' )
    )
  )
, 'disks' => array(
    'cols' => array(
      'disks_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'oid' => array( // sticks to hardware
        'sql_type' =>  "varchar(240)"
      , 'type' => 'a240'
      , 'pattern' => '/^[0-9.]*$/'
      )
    , 'sizeGB' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'U'
      )
    , 'hosts_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'cn' => array( // sticks to hardware
        'sql_type' =>  "varchar(16)"
      , 'type' => 'W'
      )
    , 'type_disk' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'E,'.implode( ',', $disk_types )
      )
    , 'location' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'a64'
      )
    , 'description' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      )
    , 'interface_disk' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'E,'.implode( ',', $disk_interfaces )
      )
    , 'year_manufactured' => array(
        'sql_type' => "int(4)"
      , 'type' => 'u'
      )
    , 'year_decommissioned' => array(
        'sql_type' => "int(4)"
      , 'type' => 'u'
      )
    , 'systems_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'disks_id' )
    )
  )
, 'tapes' => array(
    'cols' => array(
      'tapes_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'oid' => array( // sticks to hardware
        'sql_type' =>  "varchar(240)"
      , 'type' => 'a240'
      , 'pattern' => '/^[0-9.]*$/'
      )
    , 'type_tape' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'E,'.implode( ',', $tape_types )
      )
    , 'tapewritten_first' => array( // first write access to tape
        'sql_type' =>  'char(15)'
      , 'type' => 't'
      )
    , 'tapewritten_last' => array( // last write access to tape
        'sql_type' =>  'char(15)'
      , 'type' => 't'
      )
    , 'tapewritten_count' => array( // number of _backup sessions_ to this tape
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'cn' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'a64'
      )
    , 'good' => array(
        'sql_type' =>  "tinyint(1)"
      , 'type' => 'b'
      )
    , 'retired' => array(
        'sql_type' =>  "tinyint(1)"
      , 'type' => 'b'
      )
    , 'leot_blocknumber' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'leot_filenumber' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'location' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'a64'
      )
    , 'description' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'tapes_id' )
    )
  )
, 'backupchunks' => array(
    'cols' => array(
      'backupchunks_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'oid' => array(
        'sql_type' =>  "varchar(240)"
      , 'type' => 'a240'
      , 'pattern' => '/^[0-9.]*$/'
      )
    , 'sizeGB' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'clearhashfunction' => array(
        'sql_type' =>  "varchar(32)"
      , 'type' => 'w32'
      )
    , 'clearhashvalue' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'x64'
      )
    , 'crypthashfunction' => array(
        'sql_type' =>  "varchar(32)"
      , 'type' => 'w32'
      )
    , 'crypthashvalue' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'x64'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupchunks_id' )
    , 'clearhash' => array( 'unique' => 0, 'collist' => 'clearhashvalue, clearhashfunction' )
    , 'crypthash' => array( 'unique' => 0, 'collist' => 'crypthashvalue, crypthashfunction' )
    , 'oid' => array( 'unique' => 0, 'collist' => 'oid' )
    )
  )
, 'tapechunks' => array(
    'cols' => array(
      'tapechunks_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'backupchunks_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'tapes_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'blockumber' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'filenumber' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'chunkwritten' => array(
        'sql_type' =>  'char(15)'
      , 'type' => 't'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'tapechunks_id' )
    , 'content' => array( 'unique' => 0, 'collist' => 'backupchunks_id' )
    , 'tape' => array( 'unique' => 0, 'collist' => 'tapes_id', 'blocknumber' )
    , 'age' => array( 'unique' => 0, 'collist' => 'chunkwritten', 'tapes_id', 'blocknumber' )
    )
  )
, 'services' => array(
    'cols' => array(
      'services_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'type_service' => array(
        'sql_type' =>  "text"
      , 'type' => 'a'
      )
    , 'description' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      )
    , 'hosts_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'url' => array(
        'sql_type' =>  "varchar(256)"
      , 'type' => 'a256'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'services_id' )
    )
  )
, 'accounts' => array(
    'cols' => array(
      'accounts_id' => array(
        'sql_type' =>  'int(11)'
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'hosts_id' => array(
        'sql_type' =>  'int(11)'
      , 'type' => 'u'
      )
    , 'uid' => array(
        'sql_type' =>  "varchar(8)"
      , 'type' => 'W8'
      )
    , 'uidnumber' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'people_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'accounts_id' )
    )
  )
, 'systems' => array(
    'cols' => array(
      'systems_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'arch' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'a64'
      )
    , 'type' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      )
    , 'date_built' => array(
        'sql_type' =>  "char(8)"
      , 'type' => 't'
      , 'pattern' => '/^\d{8}$/'
      )
    , 'parent_systems_id' => array(
        'sql_type' =>  'int(11)'
      , 'type' => 'u'
      )
    , 'description' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'systems_id' )
    )
  )
);

function update_database() {
  global $database_version; // from leitvariable

  switch( $database_version ) {
    case 0:
      logger( 'starting update_database: from version 0', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'update_database' );
      sql_do( "ALTER TABLE Dienste ADD `dienstkontrollblatt_id` INT NULL DEFAULT NULL "
      , "update_database from version 0 to version 1 FAILED"
      );
      sql_update( 'leitvariable', array( 'name' => 'database_version' ), array( 'value' => 1 ) );
      logger( 'update_database: update to version 1 SUCCESSFUL', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'update_database' );
  }
}

?>
