<?php
//
// common.php: code to be _executed_ before the per-window code, but after defining functions
//
// the following global assignments call function we() and thus must be in common.php, not basic.pip:
//

$degree_text = array(
  DEGREE_BACHELOR => 'Bachelor'
, DEGREE_MASTER => 'Master'
, DEGREE_PHD => 'PhD'
, DEGREE_INTERNSHIP => we('research internship','Forschungspraktikum')
, DEGREE_ASSISTANT => we('student assistant','HiWi')
);

$programme_text = array(
  PROGRAMME_BSC => 'BSc'
, PROGRAMME_BED => 'BEd'
, PROGRAMME_MSC => 'MSc'
, PROGRAMME_MED => 'MEd'
, PROGRAMME_SECOND => we('second subject', 'Nebenfach')
, PROGRAMME_OTHER => we('other','sonstige')
);

// $people_flag_text = array(
//   PEOPLE_FLAG_INSTITUTE => 'institute'
// , PEOPLE_FLAG_DELETED => 'deleted'
// , PEOPLE_FLAG_VIRTUAL => 'virtual'
// );

$choices_typeofposition = array(
  'O' => 'ohne Vertrag/Vergütung'
, 'H' => we('budget','Haushalt')
, 'D' => we('third-party','Drittmittel')
, 'W' => 'Werkvertrag'
, 'E' => 'externe Finanzierung'
, 'P' => 'pensioniert'
, 'M' => 'emeritiert'
, 'A' => 'Lehrauftrag unvergütet'
, 'G' => 'Lehrauftrag vergütet'
, 'X' => 'externe Professur'
, 'o' => we('other','sonstige')
);

$choices_course_type = array(
  'VL' => we('lecture','Vorlesung')
, 'UE' => we('exercise class','Übung')
, 'SE' => 'Seminar'
, 'GP' => we('lab course (basic)','Grundpraktikum')
, 'FP' => we('lab course (advanced)','Fortgeschrittenenpraktikum')
, 'P'  => we('lab course','Praktikum (sonstige)')
, 'X'  =>  we('(none/sabbatical)','(keine/Freisemester)')
//   'VL' => '- VL -'
// , 'UE' => '- ÜB -'
// , 'SE' => '- SE -'
// , 'GP' => '- GP -'
// , 'FP' => '- FP -'
// , 'P'  =>  '- P -'
// , 'X'  =>  '- (keine) -'
);

$current_term = ( ( ( $current_month >= 4 ) && ( $current_month <= 9 ) ) ? 'S' : 'W' );

$boards = array(
  'executive' => array(
    '_BOARD' => we('Executive','Geschäftsführende Leitung')
  , '_MINPRIV' => PERSON_PRIV_COORDINATOR
  , 'chief' => array( 'function' => we('Head of the Institute','Geschäftsführender Leiter'), 'count' => 1 )
  , 'deputy' => array( 'function' => we('Deputy Head','Stellvertretender Geschäftsführender Leiter'), 'count' => 1 )
  )
, 'special' => array(
    '_BOARD' => we('special tasks','besondere Aufgaben')
  , '_MINPRIV' => PERSON_PRIV_ADMIN
  , 'coordinator' => array( 'function' => we('Scientific Coordinator','Wissenschaftlicher Koordinator'), 'count' => 1 )
  , 'admin' => array( 'function' => we('Web admin','Webadministrator'), 'count' => 1 )
  , 'scheduling' => array( 'function' => we('Class scheduling','Stundenplanung'), 'count' => 1 )
  )
, 'instituteBoard' => array(
    '_BOARD' => we('Institute Board','Institutsrat')
  , '_MINPRIV' => PERSON_PRIV_COORDINATOR
  , 'professors' => array( 'function' => we('professors','Professoren'), 'count' => '*' )
  , 'deputyProfs' => array( 'function' => we('deputy professors','Stellvertretende Professoren'), 'count' => '*' )
  , 'academicStaff' => array( 'function' => we('academic staff','Wissenschaftliche Mitarbeiter'), 'count' => '*' )
  , 'deputyAcademicStaff' => array( 'function' => we('deputy academic staff','Stellvertretende Wissenschaftliche Mitarbeiter'), 'count' => '*' )
  , 'students' => array( 'function' => we('student members','studentische Mitglieder'), 'count' => '*' )
  , 'deputyStudents' => array( 'function' => we('deputy student members','Stellvertretende studentische Mitglieder'), 'count' => '*' )
  , 'technicalStaff' => array( 'function' => we('technical staff','Mitarbeiter Technik/Verwaltung'), 'count' => '*' )
  , 'deputyTechnicalStaff' => array( 'function' => we('deputy technical staff','Stellvertretende Mitarbeiter Technig/Verwaltung'), 'count' => '*' )
  )
, 'professors' => array(
    '_BOARD' => we('professors','Professuren')
  , '_MINPRIV' => PERSON_PRIV_COORDINATOR
  , 'full' => array( 'function' => we('Full Professors','Ordentliche Professuren'), 'count' => '*' )
  , 'special' => array( 'function' => we('Professors by special appointment','Außerplanmäßige Professuren'), 'count' => '*' )
  , 'joint' => array( 'function' => we('Jointly appointed Professors','gemeinsam berufene Professuren'), 'count' => '*' )
  )
, 'examBoardMono' => array(
    '_BOARD' => we('examination board BSc/MSc/Diplom','Prüfungsausschuss BSc/MSc/Diplom')
  , '_MINPRIV' => PERSON_PRIV_COORDINATOR
  , 'chair' => array( 'function' => we('chair','Vorsitz'), 'count' => '1' )
  , 'deputy' => array( 'function' => we('deputy chair','Stellv. Vorsitz'), 'count' => '1' )
  , 'professors' => array( 'function' => we('professors','Professoren'), 'count' => '*' )
  , 'staff' => array( 'function' => we('staff','Mitarbeiter'), 'count' => '*' )
  , 'students' => array( 'function' => we('student representatives','studentische Vertreter'), 'count' => '*' )
  )
, 'examBoardEdu' => array(
    '_BOARD' => we('examination board BEd/MEd','Prüfungsausschuss BEd/MEd')
  , '_MINPRIV' => PERSON_PRIV_COORDINATOR
  , 'chair' => array( 'function' => we('chair','Vorsitz'), 'count' => '1' )
  , 'deputy' => array( 'function' => we('deputy chair','Stellv. Vorsitz'), 'count' => '1' )
  , 'professors' => array( 'function' => we('professors','Professoren'), 'count' => '*' )
  , 'staff' => array( 'function' => we('staff','Mitarbeiter'), 'count' => '*' )
  , 'students' => array( 'function' => we('student representatives','studentische Vertreter'), 'count' => '*' )
  )
, 'studiesBoard' => array(
    '_BOARD' => we('board of study affairs','Studienkommission')
  , '_MINPRIV' => PERSON_PRIV_COORDINATOR
  , 'professors' => array( 'function' => we('professors','Professoren'), 'count' => '*' )
  , 'deputyProfs' => array( 'function' => we('deputy professors','Stellvertretende Professoren'), 'count' => '*' )
  , 'students' => array( 'function' => we('student members','studentische Mitglieder'), 'count' => '*' )
  , 'deputyStudents' => array( 'function' => we('deputy student members','Stellvertretende studentische Mitglieder'), 'count' => '*' )
  )
, 'guidance' => array(
    '_BOARD' => we('guidance','Beratung')
  , '_MINPRIV' => PERSON_PRIV_COORDINATOR
  , 'mono' => array( 'function' => we('course guidance BSc/MSc/Diplom','Studienberatung BSc/MSc/Diplom'), 'count' => 1 )
  , 'edu' => array( 'function' => we('course guidance BEd/MEd','Studienberatung BEd/MEd'), 'count' => 1 )
  , 'erasmus' => array( 'function' => we('SOCRATES/ERASMUS Contact','SOCRATES/ERASMUS Beauftragter'), 'count' => 1 )
  , 'bafoeg' => array( 'function' => we('BAFöG guidance','BAFöG Beratung'), 'count' => 1 )
  )
);

?>
