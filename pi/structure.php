<?php


$tables = array(
  'people' => array(
    'cols' => array(
      'people_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'genus' => array(
        'sql_type' => 'char(1)'
      , 'default' => '0'
      , 'type' => 'W1'
      , 'pattern' => '/^[NMF0]$/'
      )
    , 'sn' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'H128'
      )
    , 'gn' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'h128'
      )
    , 'title' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      )
    , 'jpegphoto' => array(
        'sql_type' => 'mediumtext' // up to 16MB
      , 'type' => 'R' // must be base64-encoded
      , 'pattern' => '&^$|^/9j/4&'  // signature at beginning of base64-encoded jpeg
      , 'maxlen' => 800000
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'people_id' )
    )
  )
, 'affiliations' => array(
    'cols' => array(
      'affiliations_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'priority' => array(
        'sql_type' => 'int(4)'
      , 'type' => 'u'
      )
    , 'people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'groups_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'roomnumber' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      )
    , 'telephonenumber' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[+]?[0-9 ]*$/'
      )
    , 'facsimiletelephonenumber' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[+]?[0-9 ]*$/'
      )
    , 'mail' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^$|^[0-9a-zA-Z._-]+@[0-9a-zA-Z.-]+$/'
      )
    , 'street' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      )
    , 'street2' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      )
    , 'city' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      )
    , 'country' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      )
    , 'status' => array(
        'sql_type' => 'int(2)'
      , 'type' => 'u2'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
     )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'affiliations_id' )
    , 'secondary' => array( 'unique' => 1, 'collist' => 'people_id, priority' )
    )
  )
, 'groups' => array(
    'cols' => array(
      'groups_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'cn' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'H128'
      )
    , 'cn_en' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'h128'
      )
    , 'head_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'secretary_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'url' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      , 'default' => 'http://'
      )
    , 'url_en' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      )
    , 'kurzname' => array(
        'sql_type' => 'varchar(16)'
      , 'type' => 'H16'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    , 'note_en' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'groups_id' )
    )
  )
// , 'people_groups_relation' => array(
//     'cols' => array(
//       'people_groups_relation_id' => array(
//         'sql_type' => 'int(11)'
//       , 'extra' => 'auto_increment'
//       , 'type' => 'u'
//       )
//     , 'groups_id' => array(
//         'sql_type' => 'int(11)'
//       , 'type' => 'u'
//       )
//     , 'people_id' => array(
//         'sql_type' => 'int(11)'
//       , 'type' => 'u'
//       )
//     )
//   , 'indices' => array(
//       'PRIMARY' => array( 'unique' => 1, 'collist' => 'people_groups_relation_id' )
//     , 'groups' => array( 'unique' => 1, 'collist' => 'groups_id, people_id' )
//     , 'people' => array( 'unique' => 1, 'collist' => 'people_id, groups_id' )
//     )
//   )
, 'termine' => array(
    'cols' => array(
      'termine_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'datum' => array(
        'sql_type' => 'char(8)'
      , 'type' => 'u8'
      )
    , 'zeit' => array(
        'sql_type' => 'char(4)'
      , 'type' => 'u4'
      )
    , 'ort' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'h128'
      )
    , 'bearbeiter_people_id' => array(
        'sql_type' =>  'int(11)'
      , 'type' => 'U'
      )
    , 'art' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'w128'
      )
    , 'url' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'termine_id' )
    )
  )
, 'pruefungen' => array(
    'cols' => array(
      'pruefungen_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'cn' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'H'
      )
    , 'semester' => array(
        'sql_type' => 'int(4)'
      , 'type' => 'u'
      )
    , 'studiengang' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    , 'dozent_groups_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'dozent_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'utc' => array(
        'sql_type' =>  "char(15)"
      , 'sql_default' => '00000000.000000'
      , 'type' => 't'
      , 'default' => $GLOBALS['utc']
      )
    , 'url' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'pruefungen_id' )
    , 'zeit' => array( 'unique' => 0, 'collist' => 'utc, studiengang, semester'  )
    , 'zielgruppe' => array( 'unique' => 0, 'collist' => 'studiengang, semester, utc'  )
    )
  )
, 'bamathemen' => array(
    'cols' => array(
      'bamathemen_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'groups_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'cn' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      )
    , 'ansprechpartner_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'abschluss' => array(
        'sql_type' => 'int(4)'
      , 'type' => 'u'
      )
    , 'beschreibung' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    , 'pdf' => array(
        'sql_type' => 'mediumtext'
      , 'type' => 'R'
      , 'maxlen' => '2000000'
      , 'pattern' => '/^$|^JVBERi/'
      )
    , 'url' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'bamathemen_id' )
    )
  )
, 'umfragen' => array(
    'cols' => array(
      'umfragen_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'initiator_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'cn' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      )
    , 'ctime' => array(
        'sql_type' => 'char(15)'
      , 'sql_default' => '00000000.000000'
      , 'type' => 't'
      , 'default' => $GLOBALS['utc']
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'umfragen_id' )
    )
  )
, 'umfragefelder' => array(
    'cols' => array(
      'umfragefelder_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'umfragen_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'type' => array(
        'sql_type' => 'varchar(16)'
      , 'type' => 'W2'
      )
    , 'cn' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'umfragefelder_id' )
    , 'umfrage' => array( 'unique' => 1, 'collist' => 'umfragen_id, umfragefelder_id' )
    )
  )
, 'umfrageteilnehmer' => array(
    'cols' => array(
      'umfrageteilnehmer_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'U'
      )
    , 'umfragen_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    , 'atime' => array(
        'sql_type' => 'char(15)'
      , 'sql_default' => '00000000.000000'
      , 'type' => 't'
      , 'default' => $GLOBALS['utc']
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'umfrageteilnehmer_id' )
    , 'umfrage' => array( 'unique' => 1, 'collist' => 'umfragen_id, people_id' )
    )
  )
, 'umfrageantworten' => array(
    'cols' => array(
      'umfrageantworten_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'U'
      )
    , 'umfrageteilnehmer_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'antwort' => array(
        'sql_type' => 'text'
      , 'type' => h
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'umfrageantworten_id' )
    , 'umfrage' => array( 'unique' => 1, 'collist' => 'umfrageteilnehmer_id' )
    )
  )
);

function update_database() {
  global $database_version; // from leitvariable
  switch( $database_version ) {
    case 0:
      //
  }

}


?>
