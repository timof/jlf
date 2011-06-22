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
, 'people_people_relation' => array(
    'cols' => array(
      'people_people_relation_id' => array(
        'type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'pattern' => 'u'
      )
    , 'needle_people_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'haystack_people_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'people_people_relation_id' )
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
        'type' =>  'text'
      )
    , 'kommentar' => array(
        'type' =>  'text'
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
        'type' =>  'text'
      , 'pattern' => 'H'
      )
    , 'kontenkreis' => array(
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
    , 'vortragskonto' => array(
        'type' => 'varchar(64)'
      , 'pattern' => 'h'
      )
    , 'geschaeftsbereich' => array(
        'type' => 'varchar(64)'
      , 'pattern' => 'h'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'kontoklassen_id' )
    , 'kontenrahmen' => array( 'unique' => 1, 'collist' => 'kontenkreis, seite, kontoklassen_id' )
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
        'type' =>  'text'
      , 'pattern' => 'H'
      )
    , 'kontonr' => array(
        'type' =>  'text'
      , 'pattern' => '/^[0-9 ]$/'
      )
    , 'blz' => array(
        'type' =>  'text'
      , 'pattern' => '/^[0-9 ]$/'
      )
    , 'url' => array(
        'type' =>  'text'
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
    , 'hauptkonten_hgb_klasse' => array(
        'type' => "varchar(32)"
      , 'pattern' => '^/[A-EIV0-9.]*/$'
      )
    , 'geschaeftsjahr' => array(
        'type' => "smallint(4)"
      , 'pattern' => 'u'
      )
    , 'folge_hauptkonten_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'hauptkonto_geschlossen' => array(
        'type' => "tinyint(1)"
      , 'pattern' => 'u'
      )
    , 'kommentar' => array(
        'type' =>  'text'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'hauptkonten_id' )
    , 'bilanz' => array( 'unique' => 0, 'collist' => 'geschaeftsjahr, rubrik, titel' )
    , 'klasse' => array( 'unique' => 0, 'collist' => 'geschaeftsjahr, kontoklassen_id' )
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
        'type' =>  'text'
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
    , 'things_id' => array( // fuer sachwerte
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'bankkonten_id' => array( // fuer bankkonten
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'unterkonten_hgb_klasse' => array(
        'type' => "varchar(32)"
      , 'pattern' => '^/[A-EIV0-9.]*/$'
      )
    , 'vortragsjahr' => array( // fuer vortragskonten
        'type' => "smallint(4)"
      , 'pattern' => 'u'
      )
    , 'folge_unterkonten_id' => array(
        'type' =>  "int(11)"
      , 'pattern' => 'u'
      )
    , 'zinskonto' => array(
        'type' =>  "tinyint(1)"
      , 'pattern' => 'u'
      )
    , 'unterkonto_geschlossen' => array(
        'type' => "tinyint(1)"
      , 'pattern' => 'u'
      )
    , 'kommentar' => array(
        'type' =>  'text'
      )
    , 'attribute' => array(
        'type' =>  'int(11)'
      , 'pattern' => 'u'
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
    , 'vorfall' => array(
        'type' => 'text'
      , 'pattern' => 'H'
      )
    , 'beleg' => array(
        'type' => 'text'
      )
    , 'valuta' => array(
        'type' =>  "smallint(4)"
      , 'default' => '0100'
      , 'pattern' => '/^[0-9][0-9][0-9][0-9]$/'
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
    , 'valuta' => array( 'unique' => 0, 'collist' => 'valuta, buchungsdatum' )
    , 'journal' => array( 'unique' => 0, 'collist' => 'buchungsdatum, valuta' )
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
        'type' => 'text'
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
    , 'people_id' => array(  // kreditor
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'darlehen_unterkonten_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'zins_unterkonten_id' => array(
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
    , 'zins_prozent' => array(
        'type' => "decimal(6,2)"
      , 'default' => '0'
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    , 'geschaeftsjahr_darlehen' => array(
        'type' => "smallint(4)"
      , 'default' => '0'
      , 'pattern' => 'u'
      )
    , 'geschaeftsjahr_zinslauf_start' => array(
        'type' => "smallint(4)"
      , 'default' => '0'
      , 'pattern' => 'u'
      )
    , 'geschaeftsjahr_tilgung_start' => array(
        'type' => "smallint(4)"
      , 'default' => '0'
      , 'pattern' => 'u'
      )
    , 'geschaeftsjahr_tilgung_ende' => array(
        'type' => "smallint(4)"
      , 'default' => '0'
      , 'pattern' => 'u'
      )
    , 'valuta_zinslauf_start' => array(
        'type' => "smallint(4)"
      , 'default' => '0'
      , 'pattern' => 'u'
      )
    , 'kommentar' => array(
        'type' => 'text'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'darlehen_id' )
    , 'person' => array( 'unique' => 0, 'collist' => 'people_id' )
    )
  )
, 'zahlungsplan' => array(
    'cols' => array(
      'zahlungsplan_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'darlehen_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'geschaeftsjahr' => array(
        'type' => "smallint(4)"
      , 'pattern' => 'u'
      )
    , 'valuta' => array(
        'type' =>  "smallint(4)"
      , 'default' => '0100'
      , 'pattern' => '/^[0-9][0-9][0-9][0-9]$/'
      )
    , 'betrag' => array(
        'type' => "decimal(12,2)"
      , 'default' => '0'
      , 'pattern' => '/^[0-9]+[.]?[0-9]?[0-9]?$/'
      )
    , 'unterkonten_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'art' => array(
        'type' => 'char(1)'
      , 'pattern' => '/^[SH]$/'
      )
    , 'zins' => array(
        'type' => "tinyint(1)"
      , 'pattern' => 'u'
      )
    , 'posten_id' => array(
        'type' => "int(11)"
      , 'pattern' => 'u'
      )
    , 'kommentar' => array(
        'type' => 'text'
      , 'pattern' => 'H'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'zahlungsplan_id' )
    , 'zahlungsplan' => array( 'unique' => 0, 'collist' => 'darlehen_id, geschaeftsjahr, valuta' )
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
      logger( 'updating db structure from version 2', 'update_db' );
      sql_do( "
        CREATE TABLE IF NOT EXISTS kontoklassen (
          `kontoklassen_id` smallint(6) NOT NULL
        , `cn` text NOT NULL
        , `kontenkreis` char(1) NOT NULL
        , `geschaeftsbereich` text NOT NULL
        , `seite` char(1) NOT NULL
        , `bankkonto` tinyint(1) NOT NULL default 0
        , `sachkonto` tinyint(1) NOT NULL default 0
        , `personenkonto` tinyint(1) NOT NULL default 0
        , PRIMARY KEY  ( `kontoklassen_id` )
        , UNIQUE KEY `kontenrahmen` ( `kontenkreis`, `seite`, `kontoklassen_id` )
        )
      " );

      $database_version = 1;
      sql_update( 'leitvariable', array( 'name' => 'database_version' ), array( 'value' => $database_version ) );
      logger( "update db structure to version $database_version SUCCESSFUL", 'update_db' );
  }


  // kontenrahmen: so far, only one:
  //
  global $kontenrahmen_version; // from leitvariable
  if( $kontenrahmen_version != 2 ) {
    logger( 'initializing table `kontoklassen`', 'update_db' );
    require_once( "sus/kontenrahmen.php" );
    sql_delete( 'kontoklassen', 'true' );
    foreach( $kontenrahmen[2] as $kontoklasse ) {
      sql_insert( 'kontoklassen', $kontoklasse, true );
    }

    $kontenrahmen_version = 2;
    sql_update( 'leitvariable', array( 'name' => 'kontenrahmen_version' ), array( 'value' => $kontenrahmen_version ) );
    logger( "kontenrahmen $database_version has been written into table `kontoklassen`", 'update_db' );
  }
}


?>
