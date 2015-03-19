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
    , 'privs' => array(
        'sql_type' => 'smallint(4)'
      , 'type' => 'u4'
      )
    , 'privlist' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'l265'
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
      , 'pattern' => '/^$|^[0-9a-zA-Z._-]+@[0-9a-zA-Z._-]+$/'
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
      , 'pattern' => '/^[A-Z0-9 ]*$/'
      , 'collation' => 'ascii_bin'
      )
    , 'status_person' => array(
        'sql_type' => "varchar(64)"
      , 'type' => 'w64'
      , 'collation' => 'ascii_bin'
      )
    , 'bank_bic' => array(
        'sql_type' =>  'varchar(11)'
      , 'type' => 'a11'
      , 'pattern' => '/^$|[A-Z0-9]{11}$/'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'people_id' )
    , 'cn' => array( 'unique' => 1, 'collist' => 'cn' )
    )
  , 'more_selects' => array(
      'REGEX' => "CONCAT( `%`.cn, ';', `%`.gn, ' ', `%`.sn, ';', `%`.uid, ';', `%`.note, ';', `%`.mail, ';', `%`.bank_cn )"
    )
  )
, 'kontoklassen' => array(
    'cols' => array(
      'kontoklassen_id' => array( // this id is unique but _NOT_ autoincremented
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
    , 'flag_bankkonto' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      )
    , 'flag_personenkonto' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      )
    , 'flag_steuerkonto' => array(
        'sql_type' => 'tinyint(1)'
      , 'type' => 'b'
      )
    , 'flag_steuerbilanzrelevant' => array(
        'sql_type' => 'tinyint(1)'
      , 'type' => 'b'
      , 'default' => '1'
      )
    , 'flag_sachkonto' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      )
    , 'vortragskonto' => array( // not a flag, but see in mysql.php!
        'sql_type' => 'varchar(64)'
      , 'type' => 'a64'
      )
    , 'geschaeftsbereich' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'a64'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'kontoklassen_id' )
    , 'kontenrahmen' => array( 'unique' => 1, 'collist' => 'kontenkreis, seite, kontoklassen_id' )
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
    , 'flag_hauptkonto_offen' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      , 'default' => '1'
      )
    , 'tags_hauptkonto' => array(
        'sql_type' => "varchar(128)"
      , 'type' => 'a128'
      , 'collation' => 'ascii_bin'
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
    , 'bilanz' => array( 'unique' => 0, 'collist' => 'rubrik, titel' )
    , 'klasse' => array( 'unique' => 0, 'collist' => 'kontoklassen_id' )
    )
  )
, 'unterkonten' => array(
    'cols' => array(
      'unterkonten_id' => array(
        'sql_type' =>  "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'hauptkonten_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'cn' => array(
        'sql_type' =>  'varchar(256)'
      , 'type' => 'H256'
      )
    , 'skrnummer' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'flag_zinskonto' => array(
        'sql_type' =>  "tinyint(1)"
      , 'type' => 'b'
      )
    , 'flag_unterkonto_offen' => array(
        'sql_type' => "tinyint(1)"
      , 'type' => 'b'
      , 'default' => '1'
      )
    , 'unterkonten_hgb_klasse' => array(
        'sql_type' => "varchar(32)"
      , 'type' => 'a32'
      , 'pattern' => '/^[a-cA-EIVP0-9.]*$/'
      )
    , 'tags_unterkonto' => array(
        'sql_type' => "varchar(128)"
      , 'type' => 'a128'
      , 'collation' => 'ascii_bin'
      )
    , 'url' => array(
        'sql_type' =>  'varchar(256)'
      , 'type' => 'a256'
      )
    , 'kommentar' => array(
        'sql_type' =>  'text'
      , 'type' => 'h'
      )
    // attribute fuer personenkonten:
    , 'people_id' => array( // fuer personenkonten
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    // attribute fuer sachkonten:
    , 'thing_anschaffungsjahr' => array(
        'sql_type' =>  "int(4)"
      , 'type' => 'u4'
      )
    , 'thing_abschreibungsmodus' => array(
        'sql_type' =>  'varchar(128)'
      , 'type' => 'a128'
      )
    // attribute fuer bankkonten:
    , 'bank_cn' => array(
        'sql_type' =>  'varchar(256)'
      , 'type' => 'h265'
      )
    , 'bank_kontonr' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[0-9 ]*$/'
      )
    , 'bank_blz' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[0-9 ]*$/'
      )
    , 'bank_iban' => array(
        'sql_type' =>  'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[A-Z0-9 ]*$/'
      , 'collation' => 'ascii_bin'
      )
    , 'bank_bic' => array(
        'sql_type' =>  'varchar(11)'
      , 'type' => 'a11'
      , 'pattern' => '/^$|[A-Z0-9]{11}$/'
      , 'collation' => 'ascii_bin'
      )
    , 'bank_url' => array(
        'sql_type' =>  'varchar(256)'
      , 'type' => 'a256'
      )
    // fuer USt-Schuld / Vorsteuer-Forderungen:
    , 'ust_satz' => array(
        'sql_type' => "char(1)"
      , 'default' => '0'
      , 'type' => 'W1'
      , 'pattern' => '/^[012]$/'
      )
    // erstattbarer anteil bei vorsteuer
    , 'ust_faktor_prozent' => array(
        'sql_type' => "decimal(6,2)"
      , 'default' => '100.0'
      , 'type' => 'F'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'unterkonten_id' )
    , 'hauptkonten' => array( 'unique' => 1, 'collist' => 'hauptkonten_id, unterkonten_id' )
    , 'tags_unterkonto' => array( 'unique' => 0, 'collist' => 'tags_unterkonto, hauptkonten_id, unterkonten_id' )
    )
  )
, 'buchungen' => array(
    'cols' => array(
      'buchungen_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'geschaeftsjahr' => array(
        'sql_type' => "smallint(4)"
      , 'type' => 'U4'
      )
    , 'valuta' => array(
        'sql_type' =>  "smallint(4)"
      , 'default' => '100'
      , 'format' => '%04u'
      , 'type' => 'U4'
      )
    , 'flag_ausgefuehrt' => array(
        'sql_type' =>  "tinyint(1)"
      , 'type' => 'b'
      )
    , 'pattern_auszug' => array(
        'sql_type' =>  "varchar(64)"
      , 'type' => 'h64'
      )
    , 'tags_buchung' => array(
        'sql_type' =>  "varchar(128)"
      , 'type' => 'a128'
      , 'collation' => 'ascii_bin'
      )
    , 'beleg' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'h'
      )
    , 'vorfall' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'buchungen_id' )
    , 'valuta' => array( 'unique' => 0, 'collist' => 'geschaeftsjahr, valuta, ctime' )
    , 'journal' => array( 'unique' => 0, 'collist' => 'ctime, buchungen_id, valuta' )
    )
  , 'more_selects' => array(
       'count_postenS' => "( SELECT COUNT(*) FROM posten WHERE ( posten.buchungen_id = `%`.buchungen_id ) AND ( posten.art = 'S' ) )"
     , 'count_postenH' => "( SELECT COUNT(*) FROM posten WHERE ( posten.buchungen_id = `%`.buchungen_id ) AND ( posten.art = 'H' ) )"
     , 'flag_vortrag' => ' IF( `%`.valuta <= 100, 1, 0 ) '
     , 'flag_postultimo' => ' IF( `%`.valuta >= 1232, 1, 0 ) '
     , 'buchungsdatum' => ' SUBSTR( `%`.ctime, 1, 8 ) '
    )
  )
, 'posten' => array(
    'cols' => array(
      'posten_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
      , 'extra' => 'auto_increment'
      )
    , 'unterkonten_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'u'
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
    , 'referenz' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    , 'art' => array(
        'sql_type' => 'char(1)'
      , 'type' => 'W1'
      , 'pattern' => '/^[SH]$/'
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
, 'auszuege' => array(
    'cols' => array(
      'auszuege_id' => array(
        'sql_type' => "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'unterkonten_id' => array(
        'sql_type' => "int(11)"
      , 'type' => 'U'
      )
    , 'geschaeftsjahr' => array(
        'sql_type' => "smallint(4)"
      , 'type' => 'u4'
      )
    , 'buchungsdatum' => array(
        'sql_type' => "smallint(4)"
      , 'default' => '101'
      , 'format' => '%04u'
      , 'type' => 'U4'
      )
    , 'valuta' => array(
        'sql_type' => "smallint(4)"
      , 'default' => '101'
      , 'format' => '%04u'
      , 'type' => 'U4'
      )
    , 'partner' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'H128'
      )
    , 'note' => array(
        'sql_type' => 'varchar(1024)'
      , 'type' => 'H1024'
      )
    , 'betrag' => array(
        'sql_type' => "decimal(12,2)"
      , 'default' => '0'
      , 'type' => 'F'
      , 'format' => '%.2F'
      )
    , 'art' => array(
        'sql_type' => 'char(1)'
      , 'type' => 'W1'
      , 'pattern' => '/^[SH]$/'
      )
    , 'waehrung' => array(
        'sql_type' => 'char(3)'
      , 'type' => 'W3'
      , 'default' => 'EUR'
      , 'pattern' => '/^[A-Z]\{3}$/'
      )
    , 'CREATION'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'auszuege_id' )
    , 'konto' => array( 'unique' => 0, 'collist' => 'unterkonten_id, geschaeftsjahr, valuta' )
    , 'partner' => array( 'unique' => 0, 'collist' => 'partner, geschaeftsjahr, valuta' )
    )
  )
, 'darlehen' => array(
    'cols' => array(
      'darlehen_id' => array(
        'sql_type' => "int(11)"
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'people_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'U'
      )
    , 'darlehen_unterkonten_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'zins_unterkonten_id' => array(
        'sql_type' =>  "int(11)"
      , 'type' => 'u'
      )
    , 'zinsaufwand_unterkonten_id' => array(
        'sql_type' =>  "int(11)"
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
    , 'valuta_zinslauf_start' => array(
        'sql_type' => "smallint(4)"
      , 'default' => '101'
      , 'format' => '%04u'
      , 'type' => 'U4'
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
    , 'cn' => array(
        'sql_type' => 'varchar(1024)'
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
  }


}


?>
