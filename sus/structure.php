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
        'type' =>  "text"
      )
    , 'street2' => array(
        'type' =>  "text"
      )
    , 'city' => array(
        'type' =>  "text"
      )
    , 'country' => array(
        'type' =>  "text"
      )
    , 'note' => array(
        'type' =>  "text"
      )
    , 'uid' => array(
        'type' =>  "varchar(16)"
      , 'pattern' => 'w'
      )
    , 'authentication_methods' => array(
        'type' =>  "text"
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
, 'things' => array(
    'cols' => array(
      'things_id' => array(
        'type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'cn' => array(
        'type' =>  "varchar(128)"
      , 'pattern' => 'H'
      )
    , 'anschaffungsjahr' => array(
        'type' =>  "int(4)"
      , 'pattern' => 'U'
      )
    , 'abschreibungszeit' => array(
        'type' =>  "text"
      )
    , 'kommentar' => array(
        'type' =>  "text"
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'things_id' )
      , 'cn' => array( 'unique' => 0, 'collist' => 'cn' )
    )
  )
, 'kontoklassen' => array(
    'cols' => array(
      'kontoklassen_id' => array(
        'type' =>  "smallint(6)"
      , 'pattern' => 'u'
      )
    , 'cn' => array(
        'type' =>  "text"
      , 'pattern' => 'H'
      )
    , 'kontoart' => array(
        'type' => "char(1)"
      , 'pattern' => '/^[BE]$/'
      )
    , 'seite' => array(
        'type' => "char(1)"
      , 'pattern' => '/^[AP]$/'
      )
    , 'bankkonto' => array(
        'type' => "tinyint(1)"
      , 'default' => 0
      )
    , 'personenkonto' => array(
        'type' => "tinyint(1)"
      , 'default' => 0
      )
    , 'sachkonto' => array(
        'type' => "tinyint(1)"
      , 'default' => 0
      )
    , 'geschaeftsbereich' => array(
        'type' => "text"
      , 'pattern' => 'h'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'kontoklassen_id' )
      , 'kontenrahmen' => array( 'unique' => 1, 'collist' => 'kontoart, seite, kontoklassen_id' )
    )
  )
, 'bankkonten' => array(
    'cols' => array(
      'bankkonten_id' => array(
        'type' =>  "smallint(6)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'bank' => array(
        'type' =>  "text"
      , 'pattern' => 'H'
      )
    , 'kontonr' => array(
        'type' =>  "text"
      , 'pattern' => '/^[0-9 ]$/'
      )
    , 'blz' => array(
        'type' =>  "text"
      , 'pattern' => '/^[0-9 ]$/'
      )
    , 'url' => array(
        'type' =>  "text"
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'bankkonten_id' )
    )
  )
, 'hauptkonten' => array(
    'cols' => array(
      'hauptkonten_id' => array(
        'type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'rubrik' => array(
        'type' =>  "varchar(120)"
      , 'pattern' => 'H'
      )
    , 'titel' => array(
        'type' =>  "varchar(120)"
      , 'pattern' => 'H'
      )
    , 'kontoklassen_id' => array(
        'type' =>  "smallint(4)"
      , 'pattern' => 'u'
      )
    , 'kommentar' => array(
        'type' =>  "text"
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'hauptkonten_id' )
      , 'bilanz' => array( 'unique' => 1, 'collist' => 'rubrik, titel' )
      , 'klasse' => array( 'unique' => 0, 'collist' => 'kontoklassen_id' )
    )
  )
, 'unterkonten' => array(
    'cols' => array(
      'unterkonten_id' => array(
        'type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'cn' => array(
        'type' =>  "text"
      , 'pattern' => 'H'
      )
    , 'hauptkonten_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'people_id' => array( // fuer personenkonten
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'darlehen_id' => array( // fuer kreditoren
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'things_id' => array( // fuer sachwerte
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'bankkonten_id' => array( // fuer bankkonten
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'kommentar' => array(
        'type' =>  "text"
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'unterkonten_id' )
      , 'secondary' => array( 'unique' => 1, 'collist' => 'hauptkonten_id, unterkonten_id' )
    )
  )
, 'buchungen' => array(
    'cols' => array(
      'buchungen_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'kommentar' => array(
        'type' => "text"
      , 'pattern' => 'H'
      )
    , 'beleg' => array(
        'type' => "text"
      )
    , 'valuta' => array(
        'type' =>  "date"
      , 'default' => '0000-00-00'
      , 'pattern' => '/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/'
      )
    , 'buchungsdatum' => array(
        'type' =>  "date"
      , 'default' => '0000-00-00'
      , 'pattern' => '/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/'
      )
    , 'sessions_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'buchungen_id' )
      , 'valuta' => array( 'unique' => 0, 'collist' => 'valuta' )
      , 'journal' => array( 'unique' => 0, 'collist' => 'buchungsdatum' )
    )
  )
, 'posten' => array(
    'cols' => array(
      'posten_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'beleg' => array(
        'type' => "text"
      )
    , 'buchungen_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'betrag' => array(
        'type' => "decimal(12,2)"
      , 'default' => '0'
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    , 'art' => array(
        'type' => 'char(1)'
      , 'pattern' => '/^[SH]$/'
      )
    , 'zins' => array(
        'type' => 'tinyint(1)'
      , 'default' => '0'
      )
    , 'unterkonten_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'posten_id' )
      , 'posten_buchung' => array( 'unique' => 0, 'collist' => 'buchungen_id' )
      , 'posten_konto' => array( 'unique' => 0, 'collist' => 'unterkonten_id' )
    )
  )
, 'darlehen' => array(
    'cols' => array(
      'darlehen_id' => array(
        'type' => "int(11)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'unterkonten_id' => array(  // kreditorenkonto
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'betrag_zugesagt' => array(
        'type' => "decimal(12,2)"
      , 'default' => '0'
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    , 'betrag_abgerufen' => array(
        'type' => "decimal(12,2)"
      , 'default' => '0'
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    , 'betrag_eingezogen' => array(
        'type' => "decimal(12,2)"
      , 'default' => '0'
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    , 'zins_prozent' => array(
        'type' => "decimal(6,2)"
      , 'default' => '0'
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    , 'tilgungsbeginn_jahr' => array(
        'type' => "smallint(4)"
      , 'default' => '0'
      , 'pattern' => 'u'
      )
    , 'kommentar' => array(
        'type' => "text"
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'darlehen_id' )
    )
  )
, 'zpposten' => array(
    'cols' => array(
      'zpposten_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'zahlungsplan_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'betrag' => array(
        'type' => "decimal(12,2)"
      , 'default' => '0'
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    , 'art' => array(
        'type' => 'char(1)'
      , 'pattern' => '/^[SH]$/'
      )
    , 'zins' => array(
        'type' => 'tinyint(1)'
      , 'default' => '0'
      )
    , 'unterkonten_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'zpposten_id' )
      , 'zpposten_buchung' => array( 'unique' => 0, 'collist' => 'zahlungsplan_id' )
      , 'zpposten_konto' => array( 'unique' => 0, 'collist' => 'unterkonten_id' )
    )
  )
, 'zahlungsplan' => array(
    'cols' => array(
      'zahlungsplan_id' => array(
        'type' => "int(11)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'unterkonten_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'gegenkonto_id' => array(
        'type' =>  "date"
      , 'pattern' => '/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/'
      , 'default' => '0000-00-00'
      )
    , 'gegenkonto_zins_id' => array(
        'type' =>  "decimal(12,2)"
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      , 'default' => '0'
      )
    , 'tilgung_start' => array(
        'type' =>  "decimal(12,2)"
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    , 'tilgung_jahre' => array(
        'type' =>  "decimal(12,2)"
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    , 'annuitaet' => array(
        'type' =>  "decimal(12,2)"
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    , 'zins_prozent' => array(
        'type' =>  "decimal(8,2)"
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'zahlungsplan_id' )
      , 'konto' => array( 'unique' => 0, 'collist' => 'unterkonten_id' )
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
    , 'timestamp' => array(
        'type' =>  "timestamp"
      , 'default' => 'CURRENT_TIMESTAMP'
      )
    , 'note' => array(
        'type' =>  "text"
      )
    )
    , 'indices' => array(
        'PRIMARY' => array( 'unique' => 1, 'collist' => 'logbook_id' )
    )
  )
, 'leitvariable' => array(
    'cols' => array(
      'name' => array(
        'type' =>  'varchar(30)'
      , 'pattern' => 'W'
      )
    , 'value' => array(
        'type' =>  "text"
      )
    , 'comment' => array(
        'type' =>  "text"
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
      , 'extra' => ''
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
      // 0 -> 1: new table `kontoklassen`:
      //
      logger( 'update_database: updating db structure from version 2' );
      sql_do( "
        CREATE TABLE IF NOT EXISTS kontoklassen (
          `kontoklassen_id` smallint(6) NOT NULL
        , `cn` text NOT NULL
        , `kontoart` char(1) NOT NULL
        , `geschaeftsbereich` text NOT NULL
        , `seite` char(1) NOT NULL
        , `bankkonto` tinyint(1) NOT NULL default 0
        , `sachkonto` tinyint(1) NOT NULL default 0
        , `personenkonto` tinyint(1) NOT NULL default 0
        , PRIMARY KEY  ( `kontoklassen_id` )
        , UNIQUE KEY `kontenrahmen` ( `kontoart`, `seite`, `kontoklassen_id` )
        )
      " );

      $database_version = 1;
      sql_update( 'leitvariable', array( 'name' => 'database_version' ), array( 'value' => $database_version ) );
      logger( "update_database: update db structure to version $database_version SUCCESSFUL" );
  }


  // kontenrahmen: so far, only one:
  //
  global $kontenrahmen_version; // from leitvariable
  if( $kontenrahmen_version != 1 ) {
    logger( 'update_database: initializing table `kontoklassen`' );
    require_once( "sus/kontenrahmen.php" );
    sql_delete( 'kontoklassen', 'true' );
    foreach( $kontenrahmen[1] as $kontoklasse ) {
      sql_insert( 'kontoklassen', $kontoklasse, true );
    }

    $kontenrahmen_version = 1;
    sql_update( 'leitvariable', array( 'name' => 'kontenrahmen_version' ), array( 'value' => $kontenrahmen_version ) );
    logger( "update_database: kontenrahmen $database_version has been written into table `kontoklassen`" );
  }
}


?>
