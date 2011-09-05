<?

$disk_interfaces = array( 'P-ATA', 'P-SCSI', 'S-ATA', 'SAS' );
$disk_types = array( 'rotating magnetic disk', 'solid state' );
$tape_types = array( 'DDS-3', 'DDS-4', 'SDLT-320', 'LTO-3', 'LTO-4' );

$tables = array(
  'hosts' => array(
    'cols' => array(
      'hosts_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'fqhostname' => array(
        'type' =>  "varchar(64)"
      , 'pattern' => '/^[a-zA-Z0-9.]+$/'
      )
    , 'sequential_number' => array( // bookkeeping: if hardware is replaced
        'type' =>  "int(11)"
      , 'default' => '1'
      , 'pattern' => 'u'
      )
    , 'ip4' => array( // primary IP4 adress
        'type' =>  "varchar(16)"
      , 'pattern' => '/^[0-9.]*$/'
      )
    , 'ip6' => array(
        'type' =>  "varchar(64)"
      , 'pattern' => '/^[0-9.:]*$/'
      )
    , 'oid' => array( // host OID: one-to-one with (fqhostname,sequential_number)
        'type' =>  "varchar(240)"
      , 'pattern' => '/^[0-9.]*$/'
      )
    , 'location' => array(
        'type' =>  "varchar(64)"
      , 'pattern' => 'l'
      )
    , 'invlabel' => array( // official bookkeeping: sticks to hardware
        'type' =>  "varchar(8)"
      , 'pattern' => 'w'
      )
    , 'processor' => array(
        'type' =>  "text"
      )
    , 'active' => array(
        'type' =>  "tinyint(1)"
      , 'pattern' => 'b'
      )
    , 'os' => array(
        'type' =>  "text"
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'hosts_id' )
    , 'name' => array( 'unique' => 1, 'collist' => 'fqhostname, sequential_number' )
    )
  )
/* , 'backupprofiles' => array(
 *     'cols' => array(
 *       'backupprofiles_id' => array(
 *         'type' =>  "int(11)"
 *       , 'pattern' => 'u'
 *       , 'extra' => 'auto_increment'
 *       )
 *     , 'cn' => array(
 *         'type' =>  "varchar(64)"
 *       )
 *     )
 *   , 'indices' => array(
 *       'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupprofiles_id' )
 *     )
 *   )
*/
, 'backupjobs' => array(
    'cols' => array(
      'backupjobs_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'utc' => array(
        'type' => 'char(15)'
      , 'pattern' => '/^\d{8}\.+\d{6}$/'
      )
    , 'cn' => array(
        'type' =>  'varchar(64)'
      , 'pattern' => 'L'
      )
    , 'hosts_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'path' => array(
        'type' =>  "varchar(128)"
      , 'pattern' => '/^[a-zA-Z0-9./]*$/'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupjobs_id' )
    , 'profile' => array( 'unique' => 0, 'collist' => 'cn, utc, hosts_id, path' )
    , 'content' => array( 'unique' => 0, 'collist' => 'hosts_id, path, utc' )
    )
  )
/* , 'backupprofiles_backupjobs_relation' => array(
 *     'cols' => array(
 *       'backupprofiles_backupjobs_relation_id' => array(
 *         'type' =>  "int(11)"
 *       , 'pattern' => 'u'
 *       , 'extra' => 'auto_increment'
 *       )
 *     , 'backupprofiles_id' => array(
 *         'type' => "int(11)"
 *       , 'pattern' => 'u'
 *       )
 *     , 'backupjobs_id' => array(
 *         'type' => "int(11)"
 *       , 'pattern' => 'u'
 *       )
 *     )
 *   , 'indices' => array(
 *       'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupprofiles_backupjobs_relation_id' )
 *     )
 *   )
 * , 'backupjobs_paths_relation' => array(
 *     'cols' => array(
 *       'backupjobs_paths_relation_id' => array(
 *         'type' =>  "int(11)"
 *       , 'pattern' => 'u'
 *       , 'extra' => 'auto_increment'
 *       )
 *     , 'paths_id' => array(
 *         'type' => "int(11)"
 *       , 'pattern' => 'u'
 *       )
 *     , 'backupjobs_id' => array(
 *         'type' => "int(11)"
 *       , 'pattern' => 'u'
 *       )
 *     )
 *   , 'indices' => array(
 *       'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupjobs_paths_relation_id' )
 *     )
 *   )
 *
 * , 'paths' => array(
 *     'cols' => array(
 *       'paths_id' => array(
 *         'type' =>  "int(11)"
 *       , 'pattern' => 'u'
 *       , 'extra' => 'auto_increment'
 *       )
 *     , 'cn' => array(
 *         'type' =>  "varchar(256)"
 *       )
 *     )
 *   , 'indices' => array(
 *       'PRIMARY' => array( 'unique' => 1, 'collist' => 'paths_id' )
 *     , 'name' => array( 'unique' => 1, 'collist' => 'cn' )
 *     )
 *   )
 */
, 'accountdomains' => array(
    'cols' => array(
      'accountdomains_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'accountdomain' => array(
        'type' =>  "varchar(64)"
      , 'pattern' => 'W'
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
      )
    , 'comment' => array(
        'type' =>  "text"
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
        'type' =>  "varchar(240)"
      , 'pattern' => '/^[0-9.]+$/'
      )
    , 'sizeGB' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'U'
      )
    , 'hosts_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'cn' => array( // sticks to hardware
        'type' =>  "varchar(16)"
      , 'pattern' => 'W'
      )
    , 'type_disk' => array(
        'type' =>  "varchar(64)"
      , 'pattern' => 'E,'.implode( ',', $disk_types )
      )
    , 'location' => array(
        'type' =>  "text"
      )
    , 'description' => array(
        'type' =>  "text"
      )
    , 'interface_disk' => array(
        'type' => 'varchar(64)'
      , 'pattern' => 'E,'.implode( ',', $disk_interfaces )
      )
    , 'year' => array(
        'type' => 'smallint(4)'
      , 'pattern' => 'u'
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
        'type' =>  "varchar(240)"
      , 'pattern' => '/^[0-9.]*$/'
      )
    , 'type_tape' => array(
        'type' =>  "varchar(64)"
      , 'pattern' => 'E,'.implode( ',', $tape_types )
      )
    , 'tapewritten_first' => array( // first write access to tape
        'type' =>  'char(15)'
      , 'pattern' => '/^\d{8}\.+\d{6}$/'
      )
    , 'tapewritten_last' => array( // last write access to tape
        'type' =>  'char(15)'
      , 'pattern' => '/^\d{8}\.+\d{6}$/'
      )
    , 'tapewritten_count' => array( // number of _backup sessions_ to this tape
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'cn' => array(
        'type' =>  "varchar(64)"
      )
    , 'good' => array(
        'type' =>  "tinyint(1)"
      , 'pattern' => 'b'
      )
    , 'retired' => array(
        'type' =>  "tinyint(1)"
      , 'pattern' => 'b'
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
      )
    , 'description' => array(
        'type' =>  "text"
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'tapes_id' )
    )
  )
, 'backupchunks' => array(
    'cols' => array(
      'backupchunks_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'oid' => array(
        'type' =>  "varchar(240)"
      , 'pattern' => '/^[0-9.]*$/'
      )
    , 'sizeGB' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'clearhashfunction' => array(
        'type' =>  "varchar(32)"
      , 'pattern' => 'w'
      )
    , 'clearhashvalue' => array(
        'type' =>  "varchar(64)"
      , 'pattern' => 'x'
      )
    , 'crypthashfunction' => array(
        'type' =>  "varchar(32)"
      , 'pattern' => 'w'
      )
    , 'crypthashvalue' => array(
        'type' =>  "varchar(64)"
      , 'pattern' => 'x'
      )
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
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'backupchunks_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'tapes_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'blockumber' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'filenumber' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'chunkwritten' => array(
        'type' =>  'char(15)'
      , 'pattern' => '/^\d{8}\.+\d{6}$/'
      )
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
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'type_service' => array(
        'type' =>  "text"
      )
    , 'description' => array(
        'type' =>  "date"
      )
    , 'hosts_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'url' => array(
        'type' =>  "varchar(256)"
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
      , 'pattern' => 'W'
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
, 'systems' => array(
    'cols' => array(
      'systems_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'arch' => array(
        'type' =>  "text"
      )
    , 'type' => array(
        'type' =>  "text"
      )
    , 'date_built' => array(
        'type' =>  "date"
      )
    , 'parent_systems_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'description' => array(
        'type' =>  "text"
      )
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
      logger( 'starting update_database: from version 0' );
      sql_do( "ALTER TABLE Dienste ADD `dienstkontrollblatt_id` INT NULL DEFAULT NULL "
      , "update_database from version 0 to version 1 FAILED"
      );
      sql_update( 'leitvariable', array( 'name' => 'database_version' ), array( 'value' => 1 ) );
      logger( 'update_database: update to version 1 SUCCESSFUL' );
  }
}

?>
