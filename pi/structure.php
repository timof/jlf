<?php

$choices_credit_factor = array(
  '1.0' => '1.0'
, '0.8' => '0.8'
, '0.75' => '0.75'
, '0.7' => '0.7'
, '0.67' => '0.67'
, '0.625' => '0.625'
, '0.6' => '0.6'
, '0.5' => '0.5'
, '0.4' => '0.4'
, '0.33' => '0.33'
, '0.3' => '0.3'
, '0.25' => '0.25'
, '0.2' => '0.2'
, '0.15' => '0.15'
, '0.1' => '0.1'
);


$choices_course_type = array(
  'VL' => '- VL -'
, 'UE' => '- ÜB -'
, 'SE' => '- SE -'
, 'GP' => '- GP -'
, 'FP' => '- FP -'
);

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
    , 'privs' => array(
        'sql_type' => 'smallint(4)'
      , 'type' => 'u4'
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
    , 'acronym' => array(
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
      )
    , 'time' => array(
        'sql_type' => 'char(4)'
      , 'type' => 'u4'
      )
    , 'location' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'h128'
      )
    , 'submitter_people_id' => array(
        'sql_type' =>  'int(11)'
      , 'type' => 'U'
      )
    , 'type' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'w128'
      )
    , 'url' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      )
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
      )
    , 'course' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'H'
      )
    , 'cn' => array(
        'sql_type' => 'varchar(128)'
      , 'type' => 'H'
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
      )
    , 'url' => array(
        'sql_type' => 'varchar(256)'
      , 'type' => 'a256'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'exams_id' )
    , 'time' => array( 'unique' => 0, 'collist' => 'utc, programme, semester'  )
    , 'audience' => array( 'unique' => 0, 'collist' => 'programme, semester, utc'  )
    )
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
      )
    , 'contact_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'degree' => array(
        'sql_type' => 'int(4)'
      , 'type' => 'u'
      )
    , 'note' => array(
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
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'positions_id' )
    )
  )
, 'publications' => array(
    'cols' => array(
      'publications_id' => array(
        'sql_type' => 'int(11)'
      , 'extra' => 'auto_increment'
      , 'type' => 'u'
      )
    , 'groups_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'submitter_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'title' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      )
    , 'authors' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      )
    , 'journal' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      )
    , 'year' => array(
        'sql_type' => 'smallint(4)'
      , 'type' => 'u'
      )
    , 'note' => array(
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
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'publications_id' )
    )
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
      )
    , 'ctime' => array(
        'sql_type' => 'char(15)'
      , 'sql_default' => '00000000.000000'
      , 'type' => 't'
      , 'default' => $GLOBALS['utc']
      )
    , 'deadline' => array(
        'sql_type' => 'char(15)'
      , 'default' => '00000000.000000'
      , 'type' => 'tYMDh'
      )
    , 'closed' => array(
        'sql_type' => 'tinyint(1)'
      , 'type' => 'b'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
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
      )
    , 'cn' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
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
    , 'submitter_people_id' => array(
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
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'surveysubmissions_id' )
    , 'survey' => array( 'unique' => 1, 'collist' => 'surveys_id, submitter_people_id' )
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
      )
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
    , 'submitter_people_id' => array(
        'sql_type' => 'int(11)'
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
      , 'default' => substr( $utc, 0, 4 )
      )
    , 'term' => array(
        'sql_type' => 'char(1)'
      , 'pattern' => '/^[WS]$/'
      , 'type' => 'W'
      )
    , 'teacher_groups_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'teacher_people_id' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'U'
      )
    , 'typeofposition' => array(
        'sql_type' => 'varchar(32)'
      , 'type' => 'W1'
      , 'pattern'=> '/^[BTO]$/'
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
      )
    , 'course_type' => array(
        'sql_type' => 'varchar(2)'
      , 'type' => 'W2'
      , 'pattern' => array_keys( $choices_course_type )
      )
    , 'course_title' => array(
        'sql_type' => 'text'
      , 'type' => 'H'
      )
    , 'course_number' => array(
        'sql_type' => 'varchar(32)'
      , 'type' => 'a32'
      )
    , 'module_number' => array(
        'sql_type' => 'varchar(32)'
      , 'type' => 'a32'
      )
    , 'hours_per_week' => array(
        'sql_type' => 'decimal(4,1)'
      , 'type' => 'F6'
      )
    , 'credit_factor' => array(
        'sql_type' => 'decimal(6,3)'
      , 'type' => 'F6'
      , 'pattern' => array_keys( $choices_credit_factor )
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
      )
    , 'participants_number' => array(
        'sql_type' => 'int(11)'
      , 'type' => 'u'
      )
    , 'note' => array(
        'sql_type' => 'text'
      , 'type' => 'h'
      )
    )
  , 'indices' => array(
      'PRIMARY' => array( 'unique' => 1, 'collist' => 'teaching_id' )
    , 'submitter' => array( 'unique' => 0, 'collist' => 'submitter_people_id, year, term' )
    , 'term' => array( 'unique' => 0, 'collist' => 'year, term, submitter_people_id' )
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
