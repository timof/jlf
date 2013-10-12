<?php // pi/common.php
//
// common.php: code to be _executed_ before the per-window code, but after defining functions
//
// the following global assignments call function we() and thus must be in common.php, not basic.pip:
//

$programme_text = array(
  PROGRAMME_BSC => 'Bachelor of Science (BSc)'
, PROGRAMME_BED => 'Bachelor of Education (BEd)'
, PROGRAMME_MSC => 'Master of Science (MSc)'
, PROGRAMME_MED => 'Master of Education (MEd)'
, PROGRAMME_DIPLOM => 'Diplom'
, PROGRAMME_PHD => 'PhD'
, PROGRAMME_SECOND => we('second subject', 'Nebenfach')
, PROGRAMME_INTERNSHIP => we('research internship','Forschungspraktikum')
, PROGRAMME_ASSISTANT => we('student assistant','HiWi')
);


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

$choices_lesson_type = array(
  'VL' => we('lecture','Vorlesung')
, 'UE' => we('exercise class','Übung')
, 'SE' => 'Seminar'
, 'GP' => we('lab course (basic)','Grundpraktikum')
, 'FP' => we('lab course (advanced)','Fortgeschrittenenpraktikum')
, 'P'  => we('lab course (other)','Praktikum (sonstige)')
, 'N'  =>  we('(none)','(keine Lehre)')
, 'X'  =>  we('(sabbatical)','(Freisemester)')
);

$choices_documenttype = array(
  'VVZ' => we('Course schedule','Vorlesungsverzeichnis')
, 'MHB' => 'Modulhandbuch'
, 'SVP' => 'Studienverlaufsplan'
, 'SO' => 'Studienordnung'
, 'PO' => 'Prüfungsordnung'
, 'INFO' => 'sonstige Information'
);

$choices_group_status = array(
  GROUPS_STATUS_PROFESSOR => we('Professor','Professur')
, GROUPS_STATUS_SPECIAL => we('associate professor',"au{$AZLIG}erplanm{$aUML}{$AZLIG}ige Professur")
, GROUPS_STATUS_JOINT => we('professor by joint appointment','gemeinsam berufene Professur')
, GROUPS_STATUS_EXTERNAL => we('external','externe')
, GROUPS_STATUS_LABCOURSE => we('lab course','Praktikum')
, GROUPS_STATUS_OTHER => we('other','sonstige')
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
// , 'professors' => array(
//     '_BOARD' => we('professors','Professuren')
//   , '_MINPRIV' => PERSON_PRIV_COORDINATOR
//   , 'full' => array( 'function' => we('Full Professors','Ordentliche Professuren'), 'count' => '*' )
//   , 'special' => array( 'function' => we('Professors by special appointment','Außerplanmäßige Professuren'), 'count' => '*' )
//   , 'joint' => array( 'function' => we('Jointly appointed Professors','gemeinsam berufene Professuren'), 'count' => '*' )
//   )
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
  , 'erasmus' => array( 'function' => we('SOCRATES/ERASMUS Contact','SOCRATES/ERASMUS Beauftragter'), 'count' => '*' )
  , 'bafoeg' => array( 'function' => we("BAF{$oUML}G guidance","BAF{$oUML}G Beratung"), 'count' => '*' )
  )
);

?>
