<?php

$disk_interfaces = array( 'p-ata', 'p-scsi', 's-ata', 'sas' );
$disk_types = array( 'rotating magnetic disk', 'solid state' );
$tape_types = array( 'dds-3', 'dds-4', 'sdlt-320', 'lto-3', 'lto-4' );

// $oid_prefixes ... are in common.php: they need the global $oid_prefix!


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
      , 'type' => 'a64'
      , 'pattern' => '/^[-a-zA-Z0-9.]+$/'
      , 'collation' => 'ascii_bin'
      )
    , 'sequential_number' => array( // bookkeeping: if hardware is replaced
        'sql_type' =>  "int(11)"
      , 'default' => '1'
      , 'type' => 'u'
      )
    , 'mac' => array( // primary MAC adress
        'sql_type' =>  "varchar(15)"
      , 'type' => 'a17'
      , 'pattern' => '/^(([0-9a-f]{2}:){5}[0-9a-f]{2}|)$/'
      , 'normalize' => array( 'T17', 's/[.]/:/g', 'k[0-9a-fA-F:]*', 'l' )
      , 'collation' => 'ascii_bin'
      )
    , 'ip4' => array( // primary IP4 adress
        'sql_type' =>  "varchar(15)"
      , 'type' => 'a15'
      , 'pattern' => '/^[0-9.]*$/'
      , 'collation' => 'ascii_bin'
      )
    , 'ip6' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'a64'
      , 'pattern' => '/^[0-9.:]*$/'
      , 'collation' => 'ascii_bin'
      )
    , 'oid' => array( // host OID: one-to-one with (fqhostname,sequential_number)
        'sql_type' =>  "varchar(240)"
      , 'type' => 'a240'
      , 'pattern' => '/^[0-9.]*$/'
      , 'collation' => 'ascii_bin'
      )
    , 'location' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'a64'
      , 'collation' => 'ascii_bin'
      )
    , 'invlabel' => array( // official bookkeeping: sticks to hardware - may occur in several records!
        'sql_type' =>  "varchar(8)"
      , 'type' => 'w8'
      , 'collation' => 'ascii_bin'
      )
    , 'processor' => array(
        'sql_type' =>  "text"
      , 'type' => 'a128'
      , 'collation' => 'ascii_bin'
      )
    , 'year_inservice' => array(
        'sql_type' => "int(4)"
      , 'type' => 'u4'
      )
    , 'year_outservice' => array(
        'sql_type' => "int(4)"
      , 'type' => 'u4'
      )
    , 'year_manufactured' => array(
        'sql_type' => "int(4)"
      , 'type' => 'u4'
      )
    , 'online' => array(
        'sql_type' =>  "tinyint(1)"
      , 'type' => 'b'
      )
    , 'year_decommissioned' => array(
        'sql_type' => "int(4)"
      , 'type' => 'u4'
      )
    , 'description' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'os' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'hosts_id' )
    , 'name' => array( 'unique' => 1, 'collist' => 'fqhostname, sequential_number' )
    , 'oid' => array( 'unique' => 1, 'collist' => 'oid' )
    )
  , 'more_selects' => array(
      'host_current' => ' IF( `%`.sequential_number = ( SELECT MAX( sequential_number ) FROM hosts AS subhosts WHERE subhosts.fqhostname = `%`.fqhostname ), 1, 0 ) '
    , 'the_current' => ' ( SELECT MAX( sequential_number ) FROM hosts AS subhosts WHERE subhosts.fqhostname = `%`.fqhostname ) '
    )
  , 'viewer' => 'host'
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
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'accountdomains_id' )
    , 'name' => array( 'unique' => 1, 'collist' => 'accountdomain' )
    )
  )
, 'rAccountdomainsHosts' => array(
    'cols' => array(
      'rAccountdomainsHosts_id' => array(
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
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'rAccountdomainsHosts_id' )
    )
  )
, 'rAccountdomainsAccounts' => array(
    'cols' => array(
      'rAccountdomainsAccounts_id' => array(
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
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'rAccountdomainsAccounts_id' )
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
      , 'pattern' => '&^https?://[[:alnum:]./]+$&'
      , 'collation' => 'ascii_bin'
      )
    , 'description' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
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
      , 'collation' => 'ascii_bin'
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
      , 'collation' => 'ascii_bin'
      )
    , 'type_disk' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'E,'.implode( ',', $disk_types )
      , 'collation' => 'ascii_bin'
      )
    , 'location' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'a64'
      , 'collation' => 'ascii_bin'
      )
    , 'description' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'interface_disk' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'E,'.implode( ',', $disk_interfaces )
      , 'collation' => 'ascii_bin'
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
  , 'viewer' => 'disk'
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
      , 'collation' => 'ascii_bin'
      )
    , 'type_tape' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'E,'.implode( ',', $tape_types )
      )
    , 'tapewritten_first' => array( // first write access to tape
        'sql_type' =>  'char(15)'
      , 'type' => 't'
      , 'collation' => 'ascii_bin'
      )
    , 'tapewritten_last' => array( // last write access to tape
        'sql_type' =>  'char(15)'
      , 'type' => 't'
      , 'collation' => 'ascii_bin'
      )
    , 'tapewritten_count' => array( // number of _backup sessions_ to this tape
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'tapechecked_last' => array(
        'sql_type' =>  'char(15)'
      , 'type' => 't'
      , 'collation' => 'ascii_bin'
      )
    , 'cn' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'a64'
      , 'collation' => 'ascii_bin'
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
      , 'collation' => 'ascii_bin'
      )
    , 'description' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'tapes_id' )
    , 'name' => array( 'unique' => 1, 'collist' => 'cn' )
    , 'oid' => array( 'unique' => 1, 'collist' => 'oid' )
    )
  , 'viewer' => 'tape'
  )
// , 'backupprofiles' => array(
//     'cols' => array(
//       'backupprofiles_id' => array(
//         'sql_type' =>  "int(11)"
//       , 'pattern' => 'u'
//       , 'extra' => 'auto_increment'
//       )
//     , 'CREATION'
//     , 'CHANGELOG'
//     )
//   , 'indices' => array(
//       'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupprofiles_id' )
//     )
//   )
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
      , 'collation' => 'ascii_bin'
      )
    , 'description' => array(
        'sql_type' =>  "text"
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'hosts_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'url' => array(
        'sql_type' =>  "varchar(256)"
      , 'type' => 'a256'
      , 'collation' => 'ascii_bin'
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
      , 'collation' => 'ascii_bin'
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
      , 'collation' => 'ascii_bin'
      )
    , 'type' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      , 'collation' => 'ascii_bin'
      )
    , 'date_built' => array(
        'sql_type' =>  "char(8)"
      , 'type' => 't'
      // , 'pattern' => '/^\d{8}$/'
      , 'collation' => 'ascii_bin'
      )
    , 'parent_systems_id' => array(
        'sql_type' =>  'int(11)'
      , 'type' => 'u'
      )
    , 'description' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'systems_id' )
    )
  )
// data structure for backups:
//
// backupjobs: one or more per profile (group by profile)
//  :
//  : (copy hosts_id and target on backup)
//  V
// backupchunks 1 --- n tapechunks n --- 1 tapes
//
// target can be
// - /, the beginning of an absolute path to be tar-ed
// MENATWORK: not yet implemented: - |, followed by a command whose stdout will be archived
//
, 'backupjobs' => array(
    'cols' => array(
      'backupjobs_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'hosts_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'U'
      )
    , 'priority' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u4'
      )
    , 'targets' => array(
        'sql_type' =>  "varchar(1024)"
      , 'type' => 'A1024'
      , 'pattern' => '&^[a-zA-Z0-9./ ]*$&'
      , 'collation' => 'ascii_bin'
      )
    , 'keyname' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'a128'
      , 'pattern' => '&^g[0-9]+/[-a-zA-Z0-9._]*$&'
      , 'collation' => 'ascii_bin'
      )
    , 'keyhashfunction' => array(
        'sql_type' =>  "varchar(32)"
      , 'type' => 'w32'
      , 'collation' => 'ascii_bin'
      )
    , 'keyhashvalue' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'x64'
      , 'default' => '' // 0 makes no sense here
      , 'collation' => 'ascii_bin'
      )
    , 'cryptcommand' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'a128'
      , 'collation' => 'ascii_bin'
      )
    , 'active' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      )
    , 'profile' => array(
        'sql_type' =>  "varchar(128)"
      , 'type' => 'W128'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupjobs_id' )
    , 'profile' => array( 'unique' => 0, 'collist' => 'profile, priority, targets(64)' )
    , 'content' => array( 'unique' => 0, 'collist' => 'hosts_id, targets(64), profile' )
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
      , 'collation' => 'ascii_bin'
      )
    , 'hosts_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'U'
      )
    , 'targets' => array(
        'sql_type' =>  "varchar(1024)"
      , 'type' => 'A1024'
      , 'pattern' => '&^[a-zA-Z0-9./ ]*$&'
      , 'collation' => 'ascii_bin'
      )
    , 'chunkarchivedutc' => array(
        'sql_type' =>  "char(15)"
      , 'type' => 't'
      , 'collation' => 'ascii_bin'
      )
    , 'sizeGB' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'backuplabel' => array(
        'sql_type' => 'varchar(240)'
      , 'type' => 'a240'
      , 'collation' => 'ascii_bin'
      )
    , 'clearhashfunction' => array(
        'sql_type' =>  "varchar(32)"
      , 'type' => 'w32'
      , 'collation' => 'ascii_bin'
      )
    , 'clearhashvalue' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'x64'
      , 'collation' => 'ascii_bin'
      )
    , 'crypthashfunction' => array(
        'sql_type' =>  "varchar(32)"
      , 'type' => 'w32'
      , 'collation' => 'ascii_bin'
      )
    , 'crypthashvalue' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'x64'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'backupchunks_id' )
    , 'clearhash' => array( 'unique' => 0, 'collist' => 'clearhashvalue, clearhashfunction' )
    , 'crypthash' => array( 'unique' => 0, 'collist' => 'crypthashvalue, crypthashfunction' )
    , 'oid' => array( 'unique' => 1, 'collist' => 'oid' )
    , 'content' => array( 'unique' => 1, 'collist' => 'hosts_id, targets(64), chunkarchivedutc' )
    , 'age' => array( 'unique' => 1, 'collist' => 'chunkarchivedutc, hosts_id, targets(64)' )
    )
  , 'more_selects' => array(
      'targets' => "CONCAT( ' ', `%`.targets, ' ' )"
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
    , 'blocknumber' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'filenumber' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'chunkwrittenutc' => array(
        'sql_type' =>  'char(15)'
      , 'type' => 't'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'tapechunks_id' )
    , 'content' => array( 'unique' => 1, 'collist' => 'backupchunks_id, tapes_id, blocknumber' )
    , 'tape' => array( 'unique' => 1, 'collist' => 'tapes_id, blocknumber' )
    , 'age' => array( 'unique' => 1, 'collist' => 'chunkwrittenutc, tapes_id, blocknumber' )
    )
  )
);


?>
