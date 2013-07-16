<?php // pi/structure.php

$choices_credit_factor = array(
  '1.000' => '1.000'
, '0.800' => '0.800'
, '0.750' => '0.750'
, '0.700' => '0.700'
, '0.667' => '0.667'
, '0.625' => '0.625'
, '0.600' => '0.600'
, '0.500' => '0.500'
, '0.400' => '0.400'
, '0.333' => '0.333'
, '0.300' => '0.300'
, '0.250' => '0.250'
, '0.200' => '0.200'
, '0.175' => '0.175'
, '0.150' => '0.150'
, '0.100' => '0.100'
);
// new policy?
// $choices_credit_factor = array(
//   '1.000' => '1.000'
// , '0.500' => '0.500'
// );

$choices_SWS_FP = array(
  '0.4' => ' - 0.4 - '
, '0.8' => ' - 0.8 - '
, '1.2' => ' - 1.2 - '
, '1.6' => ' - 1.6 - '
, '2.0' => ' - 2.0 - '
, '2.4' => ' - 2.4 - '
, '2.8' => ' - 2.8 - '
, '3.2' => ' - 3.2 - '
, '3.6' => ' - 3.6 - '
, '4.0' => ' - 4.0 - '
, '4.4' => ' - 4.4 - '
, '4.8' => ' - 4.8 - '
, '5.2' => ' - 5.2 - '
, '5.6' => ' - 5.6 - '
, '6.0' => ' - 6.0 - '
);

$choices_SWS_other = array(
  '0.5' => ' - 0.5 - '
, '1.0' => ' - 1.0 - '
, '1.5' => ' - 1.5 - '
, '2.0' => ' - 2.0 - '
, '2.5' => ' - 2.5 - '
, '3.0' => ' - 3.0 - '
, '3.5' => ' - 3.5 - '
, '4.0' => ' - 4.0 - '
, '5.0' => ' - 5.0 - '
, '6.0' => ' - 6.0 - '
, '7.0' => ' - 7.0 - '
, '8.0' => ' - 8.0 - '
);


$choices_course_type = array( // preliminary - will be overridden in common.php!
  'VL' => '- VL -'
, 'UE' => '- ÜB -'
, 'SE' => '- SE -'
, 'GP' => '- GP -'
, 'FP' => '- FP -'
, 'P'  =>  '- P -'
, 'N'  =>  '- (keine) -'
, 'X'  =>  '- (Freisemester) -'
);


// textual representation needs we() and thus goes to common.php:
//
$keys_typeofposition = array( 'H', 'D' , 'W' , 'E' , 'P' , 'M' , 'O' , 'A' , 'G' , 'X', 'o' );

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
      , 'collation' => 'ascii_bin'
      )
    , 'sn' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'H128'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'gn' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'h128'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'privs' => array(
        'sql_type' => 'smallint(4)'
      , 'type' => 'u4'
      )
    , 'flag_deleted' => array(
        'sql_type' => 'tinyint(1)'
      , 'type' => 'b'
      )
    , 'flag_virtual' => array(
        'sql_type' => 'tinyint(1)'
      , 'type' => 'b'
      )
    , 'flag_institute' => array(
        'sql_type' => 'tinyint(1)'
      , 'type' => 'b'
      , 'default' => '1'
      )
    , 'title' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'url' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      , 'default' => ''
      , 'collation' => 'ascii_bin'
      )
    , 'jpegphoto' => array(
        'sql_type' => 'mediumtext' // up to 16MB
      , 'type' => 'R' // must be base64-encoded
      , 'pattern' => '&^$|^/9j/4&'  // signature at beginning of base64-encoded jpeg
      , 'maxlen' => 800000
      , 'collation' => 'ascii_bin'
      )
    , 'jpegphotorights_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u' // cannot use U here: allow 0 if no photo
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'people_id' )
    )
  , 'more_selects' => array(
      'people_cn' => "CONCAT( `%`.title, ' ', `%`.gn, ' ', `%`.sn )"
    )
  , 'viewer' => 'person_view'
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
    , 'typeofposition' => array(
        'sql_type' => 'varchar(32)'
      , 'type' => 'W1'
      , 'pattern'=> $keys_typeofposition
      , 'collation' => 'ascii_bin'
      , 'default' => 'O'
      )
    , 'teaching_obligation' => array(
        'sql_type' => 'smallint(4)'
      , 'type' => 'u2'
      )
    , 'teaching_reduction' => array(
        'sql_type' => 'smallint(4)'
      , 'type' => 'u2'
      )
    , 'teaching_reduction_reason' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'roomnumber' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'telephonenumber' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[+]?[0-9 ]*$/'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'facsimiletelephonenumber' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^[+]?[0-9 ]*$/'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'mail' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'a64'
      , 'pattern' => '/^$|^[0-9a-zA-Z._-]+@[0-9a-zA-Z.-]+$/'
      , 'collation' => 'ascii_bin'
      )
    , 'street' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'street2' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'city' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'country' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h64'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'affiliations_id' )
    , 'secondary' => array( 'unique' => 1, 'collist' => 'people_id, priority' )
    )
  , 'viewer' => 'person_view'
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
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'cn_en' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'h128'
      , 'collation' => 'utf8_unicode_ci'
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
      , 'default' => ''
      , 'collation' => 'ascii_bin'
      )
    , 'url_en' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      , 'collation' => 'ascii_bin'
      )
    , 'acronym' => array(
        'sql_type' => 'varchar(16)'
      , 'type' => 'H16'
      , 'collation' => 'ascii_bin'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'note_en' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'flags' => array(
        'sql_type' => 'int(11)'
      , 'default' => 0  // can't yet use GROUPS_FLAG_* here
      , 'type' => 'u'
      )
    , 'jpegphoto' => array(
        'sql_type' => 'mediumtext' // up to 16MB
      , 'type' => 'R' // must be base64-encoded
      , 'pattern' => '&^$|^/9j/4&'  // signature at beginning of base64-encoded jpeg
      , 'maxlen' => 800000
      , 'collation' => 'ascii_bin'
      )
    , 'jpegphotorights_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'groups_id' )
    )
  , 'viewer' => 'group_view'
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
, 'events' => array(
    'cols' => array(
      'events_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'date' => array(
        'sql_type' => 'char(8)'
      , 'type' => 'u8'
      , 'collation' => 'ascii_bin'
      )
    , 'time' => array(
        'sql_type' => 'char(4)'
      , 'type' => 'u4'
      , 'collation' => 'ascii_bin'
      )
    , 'location' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'h128'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'type' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'w128'
      , 'collation' => 'ascii_bin'
      )
    , 'url' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'events_id' )
    )
  )
, 'exams' => array(
    'cols' => array(
      'exams_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'module' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'H'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'course' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'H'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'cn' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'H'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'semester' => array(
        'sql_type' => 'int(4)'
      , 'type' => 'u'
      )
    , 'programme' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'teacher_groups_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'teacher_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'utc' => array(
        'sql_type' =>  "char(15)"
      , 'sql_default' => '00000000.000000'
      , 'type' => 'tYMDWhm'
      , 'default' => $GLOBALS['utc']
      , 'collation' => 'ascii_bin'
      )
    , 'url' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'exams_id' )
    , 'time' => array( 'unique' => 0, 'collist' => 'utc, programme, semester'  )
    , 'audience' => array( 'unique' => 0, 'collist' => 'programme, semester, utc'  )
    )
  , 'viewer' => 'exam_view'
  )
, 'offices' => array(
    'cols' => array(
      'offices_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'board' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h'
      )
    , 'function' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'h'
      )
    , 'rank' => array(
        'sql_type' => 'smallint(4)'
      , 'type' => 'u'
      )
    , 'people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'offices_id' )
    , 'secondary' => array( 'unique' => 1, 'collist' => 'board, function, rank' )
    )
  , 'viewer' => 'config'
  )
, 'rooms' => array( // mostly: to link responsible persons to labs
    'cols' => array(
      'rooms_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'roomnumber' => array(
        'sql_type' => 'varchar(64)'
      , 'type' => 'H'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'groups_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'flag_lab' => array(
        'sql_type' => 'tinyint(1)'
      , 'type' => 'b'
      )
    , 'contact_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'rooms_id' )
    , 'group' => array( 'unique' => 0, 'collist' => 'groups_id, roomnumber' )
    , 'room' => array( 'unique' => 0, 'collist' => 'roomnumber' )
    , 'person' => array( 'unique' => 0, 'collist' => 'contact_people_id' )
    )
  , 'viewer' => 'rooms_edit'
  )
, 'positions' => array(
    'cols' => array(
      'positions_id' => array(
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
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'contact_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'degree' => array(
        'sql_type' => 'int(4)'
      , 'type' => 'u'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'pdf' => array(
        'sql_type' => 'mediumtext'
      , 'type' => 'R'
      , 'maxlen' => '2000000'
      , 'pattern' => '/^$|^JVBERi/'
      , 'collation' => 'ascii_bin'
      )
    , 'url' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      , 'collation' => 'ascii_bin'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'positions_id' )
    )
  , 'viewer' => 'position_view'
  )
, 'publications' => array(
    'cols' => array(
      'publications_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'U'
      )
    , 'groups_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'title' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'authors' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'abstract' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'year' => array(
        'sql_type' => 'smallint(4)'
      , 'type' => 'U'
      )
    , 'pdf' => array(
        'sql_type' => 'mediumtext'
      , 'type' => 'R'
      , 'maxlen' => '2000000'
      , 'pattern' => '/^$|^JVBERi/'
      , 'collation' => 'ascii_bin'
      )
    , 'url' => array(
        'sql_type' => 'varchar(512)'
      , 'type' => 'a512'
      , 'collation' => 'ascii_bin'
      )
    , 'journal' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'reference' => array(
        'sql_type' => 'text'
      , 'type' => 'H64'
      , 'collation' => 'ascii_bin'
      )
    , 'journal_url' => array(
        'sql_type' => 'varchar(512)'
      , 'type' => 'a512'
      , 'collation' => 'ascii_bin'
      )
    , 'info_url' => array(
        'sql_type' => 'varchar(512)'
      , 'type' => 'a512'
      , 'collation' => 'ascii_bin'
      )
    , 'jpegphoto' => array(
        'sql_type' => 'mediumtext' // up to 16MB
      , 'type' => 'R' // must be base64-encoded
      , 'pattern' => '&^$|^/9j/4&'  // signature at beginning of base64-encoded jpeg
      , 'maxlen' => 800000
      , 'collation' => 'ascii_bin'
      )
    , 'jpegphotorights_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'publications_id' )
    , 'time' => array( 'unique' => 1, 'collist' => 'year, ctime' )
    )
  , 'viewer' => 'publication_view'
  )
, 'surveys' => array(
    'cols' => array(
      'surveys_id' => array(
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
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'ctime' => array(
        'sql_type' => 'char(15)'
      , 'sql_default' => '00000000.000000'
      , 'type' => 't'
      , 'default' => $GLOBALS['utc']
      , 'collation' => 'ascii_bin'
      )
    , 'deadline' => array(
        'sql_type' => 'char(15)'
      , 'default' => '00000000.000000'
      , 'type' => 'tYMDh'
      , 'collation' => 'ascii_bin'
      )
    , 'closed' => array(
        'sql_type' => 'tinyint(1)'
      , 'type' => 'b'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'surveys_id' )
    )
  )
, 'surveyfields' => array(
    'cols' => array(
      'surveyfields_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'surveys_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'type' => array(
        'sql_type' => 'varchar(16)'
      , 'type' => 'W2'
      , 'collation' => 'ascii_bin'
      )
    , 'cn' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'priority' => array(
        'sql_type' => 'int(4)'
      , 'type' => 'u'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'surveyfields_id' )
    , 'survey' => array( 'unique' => 1, 'collist' => 'surveys_id, surveyfields_id' )
    )
  )
, 'surveysubmissions' => array(
    'cols' => array(
      'surveysubmissions_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'U'
      )
    , 'surveys_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'surveysubmissions_id' )
    , 'survey' => array( 'unique' => 1, 'collist' => 'surveys_id, creator_people_id' )
    , 'submitter' => array( 'unique' => 1, 'collist' => 'creator_people_id, surveys_id' )
    )
  )
, 'surveyreplies' => array(
    'cols' => array(
      'surveyreplies_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'U'
      )
    , 'surveysubmissions_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'surveyfields_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'reply' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'surveyreplies_id' )
    , 'survey' => array( 'unique' => 1, 'collist' => 'surveysubmissions_id, surveyfields_id' )
    )
  )
, 'teaching' => array(
    'cols' => array(
      'teaching_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'U'
      )
    , 'signer_groups_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'signer_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'year' => array(
        'sql_type' => 'smallint(4)'
      , 'type' => 'U4'
      , 'sql_default' => '0'
////      , 'default' => substr( $utc, 0, 4 )
      )
    , 'term' => array(
        'sql_type' => 'char(1)'
      , 'pattern' => '/^[WS]$/'
      , 'type' => 'W'
      , 'collation' => 'ascii_bin'
      )
    , 'teacher_groups_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u' // 0 is legal with 'extern'
      )
    , 'teacher_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'extern' => array(
        'sql_type' => 'tinyint(1)'
      , 'type' => 'u'
      )
    , 'extteacher_cn' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'typeofposition' => array(
        'sql_type' => 'varchar(32)'
      , 'type' => 'W1'
      , 'pattern'=> $keys_typeofposition
      , 'collation' => 'ascii_bin'
      )
    , 'teaching_obligation' => array(
        'sql_type' => 'smallint(4)'
      , 'type' => 'u2'
      )
    , 'teaching_reduction' => array(
        'sql_type' => 'smallint(4)'
      , 'type' => 'u2'
      )
    , 'teaching_reduction_reason' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'course_type' => array(
        'sql_type' => 'varchar(2)'
      , 'type' => 'W2'
      , 'pattern' => array_keys( $choices_course_type )
      , 'collation' => 'ascii_bin'
      )
    , 'course_title' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'course_number' => array(
        'sql_type' => 'varchar(32)'
      , 'type' => 'a32'
      , 'collation' => 'ascii_bin'
      )
    , 'module_number' => array(
        'sql_type' => 'varchar(32)'
      , 'type' => 'a32'
      , 'collation' => 'ascii_bin'
      )
    , 'hours_per_week' => array(
        'sql_type' => 'decimal(4,1)'
      , 'type' => 'F6'
      )
    , 'credit_factor' => array(
        'sql_type' => 'decimal(6,3)'
      , 'type' => 'F6'
      , 'pattern' => array_keys( $choices_credit_factor )
      , 'format' => '%.3F'
      )
    , 'teaching_factor' => array(
        'sql_type' => 'decimal(6,2)'
      , 'type' => 'U2'
      )
    , 'teachers_number' => array(
        'sql_type' => 'smallint(2)'
      , 'type' => 'U2'
      )
    , 'co_teacher' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'participants_number' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      , 'collation' => 'utf8_unicode_ci'
      )
    , 'CREATION'
    , 'CHANGELOG'
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'teaching_id' )
    , 'submitter' => array( 'unique' => 0, 'collist' => 'creator_people_id, year, term' )
    , 'term' => array( 'unique' => 0, 'collist' => 'year, term, creator_people_id' )
    )
  , 'viewer' => 'teaching_edit'
  )
);

function update_database() {
  global $database_version; // from leitvariable
  switch( $database_version ) {
    case 1:
      // logger( 'starting update_database: from version 1', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'update_database' );

      //  sql_do( " ALTER TABLE `people` ADD COLUMN `flags` int(11) not null default 0 " );
      // sql_update( 'people', 'people_id', 'flags=1' );

      // $database_version = 2;
      // sql_update( 'leitvariable', array( 'name' => 'database_version' ), array( 'value' => $database_version ) );
      // logger( 'update_database: update to version 2 SUCCESSFUL', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'update_database' );
  }
}


?>
