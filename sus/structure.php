<?php


$tables = array(
  'people' => array(
    'cols' => array(
      'people_id' => array(
        'sql_type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'jperson' => array(
        'sql_type' =>  "char(1)"
      , 'default' => ''
      , 'type' => 'W1'
      , 'pattern' => '/^[JN]$/'
      )
    , 'dusie' => array(
        'sql_type' =>  "char(1)"
      , 'type' => 'W1'
      , 'default' => 'S'
      , 'pattern' => '/^[DS0]$/'
      )
    , 'genus' => array(
        'sql_type' =>  "char(1)"
      , 'default' => '0'
      , 'type' => 'W1'
      , 'pattern' => '/^[NMF0]$/'
      )
    , 'sn' => array(
        'sql_type' =>  'varchar(128)'
      , 'type' => 'h128'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'gn' => array(
        'sql_type' =>  "varchar(128)"
      , 'type' => 'h128'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'title' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'telephonenumber' => array(
        'sql_type' =>  "varchar(64)"
      , 'pattern' => '/^[0-9 ]*$/'
      , 'type' => 'a64'
      , 'collation' => 'ascii_bin'
      )
    , 'facsimiletelephonenumber' => array(
        'sql_type' =>  "varchar(64)"
      , 'pattern' => '/^[0-9 ]*$/'
      , 'type' => 'a64'
      , 'collation' => 'ascii_bin'
      )
    , 'mail' => array(
        'sql_type' =>  "varchar(64)"
      , 'pattern' => '/^$|^[0-9a-zA-Z._-]@[0-9a-zA-Z.]+$/'
      , 'type' => 'a64'
      , 'collation' => 'ascii_bin'
      )
    , 'street' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'street2' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'city' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'country' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'note' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'bank_cn' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'bank_kontonr' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[0-9 ]*$/'
      , 'collation' => 'ascii_bin'
      )
    , 'bank_blz' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[0-9 ]*$/'
      , 'collation' => 'ascii_bin'
      )
    , 'bank_iban' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[0-9 ]*$/'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'people_id' )
    )
  , 'more_selects' => array(
      'REGEX' => "CONCAT( cn, ';', gn, ' ', sn, ';', uid, ';', note, ';', mail, ';', bank_cn )"
    )
  )
// , 'people_people_relation' => array( // for is-member-of relations
//     'cols' => array(
//       'people_people_relation_id' => array(
//         'sql_type' =>  "int(11)"
//       , 'extra' => 'auto_increment'
//       , 'type' => 'u'
//       )
//     , 'member_people_id' => array(
//         'sql_type' =>  "int(11)"
//       , 'type' => 'u'
//       )
//     , 'group_people_id' => array(
//         'sql_type' =>  "int(11)"
//       , 'type' => 'u'
//       )
//     )
//   , 'indices' => array(
//       'PRIMARY' => array( 'unique' => 1, 'collist' => 'people_people_relation_id' )
//     )
//   )
, 'things' => array(
    'cols' => array(
      'things_id' => array(
        'sql_type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'cn' => array(
        'sql_type' =>  "varchar(128)"
      , 'type' => 'H128'
      )
    , 'anschaffungsjahr' => array(
        'sql_type' =>  "int(4)"
      , 'type' => 'U4'
      )
    , 'abschreibungszeit' => array(
        'sql_type' =>  'varchar(128)' // to be fixed - not yet used
      , 'type' => 'a128'
      )
    , 'kommentar' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'things_id' )
    , 'cn' => array( 'unique' => 0, 'collist' => 'cn' )
    )
  )
, 'kontoklassen' => array(
    'cols' => array(
      'kontoklassen_id' => array(
        'sql_type' =>  "smallint(6)"
      , 'type' => 'u'
      )
    , 'cn' => array(
        'sql_type' =>  'varchar(256)'
      , 'type' => 'H256'
      )
    , 'kontenkreis' => array(
        'sql_type' => "char(1)"
      , 'type' => 'W1'
      , 'pattern' => '/^[BE]$/' // unlike in $cgi_get_vars, we _dont_ allow 0 in the db
      )
    , 'seite' => array(
        'sql_type' => "char(1)"
      , 'type' => 'W1'
      , 'pattern' => '/^[AP]$/'
      )
    , 'bankkonto' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      )
    , 'personenkonto' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      )
    , 'sachkonto' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      )
    , 'vortragskonto' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      )
    , 'geschaeftsbereich' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'kontoklassen_id' )
    , 'kontenrahmen' => array( 'unique' => 1, 'collist' => 'kontenkreis, seite, kontoklassen_id' )
    )
  )
, 'bankkonten' => array(
    'cols' => array(
      'bankkonten_id' => array(
        'sql_type' =>  "smallint(6)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'bank' => array(
        'sql_type' =>  'varchar(256)'
      , 'type' => 'H265'
      )
    , 'kontonr' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[0-9 ]*$/'
      )
    , 'blz' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[0-9 ]*$/'
      )
    , 'iban' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[0-9 ]*$/'
      )
    , 'url' => array(
        'sql_type' =>  'varchar(256)'
      , 'type' => 'a256'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'bankkonten_id' )
    )
  )
, 'hauptkonten' => array(
    'cols' => array(
      'hauptkonten_id' => array(
        'sql_type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'rubrik' => array(
        'sql_type' =>  "varchar(120)"
      , 'type' => 'H120'
      )
    , 'titel' => array(
        'sql_type' =>  "varchar(120)"
      , 'type' => 'H120'
      )
    , 'kontoklassen_id' => array(
        'sql_type' =>  "smallint(4)"
      , 'type' => 'U'
      )
    , 'hauptkonten_hgb_klasse' => array(
        'sql_type' => "varchar(32)"
      , 'type' => 'a32'
      , 'pattern' => '/^[a-cA-EIVP0-9.]*$/'
      )
    , 'geschaeftsjahr' => array(
        'sql_type' => "smallint(4)"
      , 'type' => 'U4'
      )
    , 'folge_hauptkonten_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'hauptkonto_geschlossen' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      )
    , 'kommentar' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      )
    , 'CREATION'
    , 'CHANGELOG'
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
        'sql_type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'cn' => array(
        'sql_type' =>  'varchar(256)'
      , 'type' => 'H256'
      )
    , 'hauptkonten_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'people_id' => array( // fuer personenkonten
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'things_id' => array( // fuer sachwerte
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'bankkonten_id' => array( // fuer bankkonten
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'unterkonten_hgb_klasse' => array(
        'sql_type' => "varchar(32)"
      , 'type' => 'a32'
      , 'pattern' => '/^[a-cA-EIVP0-9.]*$/'
      )
    , 'vortragsjahr' => array( // fuer vortragskonten
        'sql_type' => "smallint(4)"
      , 'type' => 'u'
      )
    , 'folge_unterkonten_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'zinskonto' => array(
        'sql_type' =>  "tinyint(1)"
      , 'type' => 'b'
      )
    , 'unterkonto_geschlossen' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      )
    , 'kommentar' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      )
    , 'attribute' => array(
        'sql_type' =>  'int(11)'
      , 'type' => 'u'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'unterkonten_id' )
    , 'secondary' => array( 'unique' => 1, 'collist' => 'hauptkonten_id, unterkonten_id' )
    )
  )
, 'buchungen' => array(
    'cols' => array(
      'buchungen_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'vorfall' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      )
    , 'valuta' => array(
        'sql_type' =>  "smallint(4)"
      , 'default' => '100'
      , 'format' => '%04u'
      , 'type' => 'U4'
      )
    , 'buchungsdatum' => array(
        'sql_type' =>  "char(8)"
      , 'type' => 'U8'
      , 'pattern' => '/^\d{8}$/'
      )
    , 'sessions_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'CREATION'
    , 'CHANGELOG'
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
        'sql_type' => "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'beleg' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    , 'buchungen_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'betrag' => array(
        'sql_type' => "decimal(12,2)"
      , 'default' => '0.00'
      , 'type' => 'F'
      , 'format' => '%.2F'
      )
    , 'art' => array(
        'sql_type' => 'char(1)'
      , 'type' => 'W1'
      , 'pattern' => '/^[SH]$/'
      )
    , 'unterkonten_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'CREATION'
    , 'CHANGELOG'
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
        'sql_type' => "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'darlehen_unterkonten_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'zins_unterkonten_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'betrag_zugesagt' => array(
        'sql_type' => "decimal(12,2)"
      , 'default' => '0'
      , 'type' => 'F'
      , 'format' => '%.2F'
      )
    , 'betrag_abgerufen' => array(
        'sql_type' => "decimal(12,2)"
      , 'default' => '0'
      , 'type' => 'f'
      , 'format' => '%.2F'
      )
    , 'zins_prozent' => array(
        'sql_type' => "decimal(6,2)"
      , 'default' => '0'
      , 'type' => 'f'
      , 'format' => '%.2F'
      )
    , 'geschaeftsjahr_darlehen' => array(
        'sql_type' => "smallint(4)"
      , 'type' => 'u4'
      )
    , 'geschaeftsjahr_zinslauf_start' => array(
        'sql_type' => "smallint(4)"
      , 'type' => 'u4'
      )
    , 'geschaeftsjahr_zinsauszahlung_start' => array(
        'sql_type' => "smallint(4)"
      , 'type' => 'u4'
      )
    , 'geschaeftsjahr_tilgung_start' => array(
        'sql_type' => "smallint(4)"
      , 'type' => 'u4'
      )
    , 'geschaeftsjahr_tilgung_ende' => array(
        'sql_type' => "smallint(4)"
      , 'type' => 'u4'
      )
    , 'valuta_zinslauf_start' => array(
        'sql_type' => "smallint(4)"
      , 'default' => '101'
      , 'format' => '%04u'
      , 'type' => 'U4'
      )
    , 'valuta_betrag_abgerufen' => array(
        'sql_type' => "smallint(4)"
      , 'default' => '101'
      , 'format' => '%04u'
      , 'type' => 'U4'
      )
    , 'cn' => array(
        'sql_type' => 'text'
      , 'type' => 'H1024'
      )
    , 'kommentar' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'darlehen_id' )
    , 'konto' => array( 'unique' => 0, 'collist' => 'darlehen_unterkonten_id' )
    )
  )
, 'zahlungsplan' => array(
    'cols' => array(
      'zahlungsplan_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'darlehen_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'geschaeftsjahr' => array(
        'sql_type' => "smallint(4)"
      , 'type' => 'U4'
      )
    , 'valuta' => array(
        'sql_type' =>  "smallint(4)"
      , 'default' => '101'
      , 'format' => '%04u'
      , 'type' => 'U4'
      )
    , 'betrag' => array(
        'sql_type' => "decimal(12,2)"
      , 'default' => '0'
      , 'type' => 'F'
      , 'format' => '%.2F'
      )
    , 'unterkonten_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'art' => array(
        'sql_type' => 'char(1)'
      , 'type' => 'W1'
      , 'pattern' => '/^[SH]$/'
      )
    , 'zins' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      )
    , 'posten_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      )
    , 'kommentar' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    , 'CREATION'
    , 'CHANGELOG'
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
      logger( 'starting update_database: from version 0', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'update_database' );
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
      logger( 'update_database: update to version 1 SUCCESSFUL', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'update_database' );
  }


  // kontenrahmen: so far, only one:
  //
  global $kontenrahmen_version; // from leitvariable
  if( $kontenrahmen_version != 2 ) {
    logger( 'initializing table `kontoklassen`', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'update_database' );
    require_once( "sus/kontenrahmen.php" );
    sql_delete( 'kontoklassen', true );
    foreach( $kontenrahmen[2] as $kontoklasse ) {
      sql_insert( 'kontoklassen', $kontoklasse );
    }

    $kontenrahmen_version = 2;
    sql_update( 'leitvariable', array( 'name' => 'kontenrahmen_version' ), array( 'value' => $kontenrahmen_version ) );
    logger( "kontenrahmen $database_version has been written into table `kontoklassen`", LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'update_database' );
  }
}


?>
