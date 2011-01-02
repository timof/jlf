<?

$tables = array(
  'people' => array(
    'cols' => array(
      'people_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'cn' => array(
        'type' =>  "varchar(128)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'uid' => array(
        'type' =>  "varchar(16)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'authentication_methods' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'password_hashvalue' => array(
        'type' =>  "varchar(256)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'password_hashfunction' => array(
        'type' =>  "varchar(256)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'password_salt' => array(
        'type' =>  "varchar(256)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'fqhostname' => array(
        'type' =>  "varchar(64)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'sequential_number' => array( // bookkeeping: if hardware is replaced
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '1'
      , 'extra' => ''
      )
    , 'ip4' => array( // primary IP4 adress
        'type' =>  "varchar(16)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'ip6' => array(
        'type' =>  "varchar(64)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'oid' => array( // host OID: one-to-one with (fqhostname,sequential_number)
        'type' =>  "varchar(64)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'location' => array(
        'type' =>  "varchar(64)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'invlabel' => array( // official bookkeeping: sticks to hardware
        'type' =>  "varchar(8)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'processor' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'os' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'hosts_id' )
      , 'name' => array( 'unique' => 1, 'collist' => 'fqhostname, sequential_number' )
    )
  )
, 'accountdomains' => array(
    'cols' => array(
      'accountdomains_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'accountdomain' => array(
        'type' =>  "varchar(64)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'accountdomains_id' => array(
        'type' => "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'hosts_id' => array(
        'type' => "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'accountdomains_id' => array(
        'type' => "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'accounts_id' => array(
        'type' => "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'hosts_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'url' => array ( // primary url to access this site
        'type' => "varchar(256)"
       , 'null' => 'NO'
       , 'default' => ''
       , 'extra' => ''
      )
    , 'comment' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'oid' => array( // sticks to hardware
        'type' =>  "varchar(64)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'sizeGB' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'hosts_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'cn' => array( // sticks to hardware
        'type' =>  "varchar(16)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'type_disk' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'location' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'description' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'systems_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'oid' => array( // sticks to hardware
        'type' =>  "varchar(64)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'type_tape' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'tapewritten_first' => array( // first write access to tape
        'type' =>  "date"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'tapewritten_last' => array( // last write access to tape
        'type' =>  "date"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'tapewritten_count' => array( // number of _backup sessions_ to this tape
        'type' =>  "date"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'cn' => array(
        'type' =>  "varchar(64)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'good' => array(
        'type' =>  "tinyint(1)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'retired' => array(
        'type' =>  "tinyint(1)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'leot_blocknumber' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'leot_filenumber' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'location' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'description' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'oid' => array(
        'type' =>  "varchar(64)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'tapes_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'tapechunks_id' )
    )
  )
, 'services' => array(
    'cols' => array(
      'services_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'type_service' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'description' => array(
        'type' =>  "date"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'hosts_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'url' => array(
        'type' =>  "varchar(256)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'hosts_id' => array(
        'type' =>  'int(11)'
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'uid' => array(
        'type' =>  "varchar(8)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'uidnumber' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'people_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'value' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'comment' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'cookie' => array(
        'type' =>  "varchar(10)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'login_authentication_method' => array(
        'type' =>  "varchar(16)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'login_people_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'sessions_id' )
    )
  )
, 'systems' => array(
    'cols' => array(
      'systems_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'arch' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'type' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'date_built' => array(
        'type' =>  "date"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'parent_systems_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'description' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'sessions_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'timestamp' => array(
        'type' =>  "timestamp"
      , 'null' => 'NO'
      , 'default' => 'CURRENT_TIMESTAMP'
      , 'extra' => ''
      )
    , 'note' => array(
        'type' =>  "text"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
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
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => 'auto_increment'
      )
    , 'used' => array(
        'type' =>  "tinyint(1)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    , 'itan' => array(
        'type' =>  "varchar(10)"
      , 'null' => 'NO'
      , 'default' => ''
      , 'extra' => ''
      )
    , 'sessions_id' => array(
        'type' =>  "int(11)"
      , 'null' => 'NO'
      , 'default' => '0'
      , 'extra' => ''
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'transactions_id' )
    )
  )
);

?>
