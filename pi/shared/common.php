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
, PROGRAMME_SECOND => we('minor subject', 'Nebenfach')
, PROGRAMME_INTERNSHIP => we('research internship','Forschungspraktikum')
, PROGRAMME_ASSISTANT => we('student assistant','HiWi')
);
$programme_text_short = array(
  PROGRAMME_BSC => 'BSc'
, PROGRAMME_BED => 'BEd'
, PROGRAMME_MSC => 'MSc'
, PROGRAMME_MED => 'MEd'
, PROGRAMME_DIPLOM => 'Diplom'
, PROGRAMME_PHD => 'PhD'
, PROGRAMME_SECOND => we('minor subject', 'NF')
, PROGRAMME_INTERNSHIP => we('internship','Praktikum')
, PROGRAMME_ASSISTANT => we('assistant','HiWi')
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
, 'FO' => we('lab course (research)','Forschungspraktikum')
, 'EP'  => we('introductory project','Einführungsprojekt')
, 'N'  =>  we('(none)','(keine Lehre)')
, 'X'  =>  we('(sabbatical)','(Freisemester)')
);

$choices_documenttype = array( // overrides preliminary (non-we()) settings in structure.php
  'VVZ' => we('Course directory','Vorlesungsverzeichnis')
, 'MHB' => we('Module manual','Modulhandbuch')
, 'MOV' => we('Module overview','Modulübersicht')
, 'SVP' => we('Course timetable','Studienverlaufsplan')
, 'SO' => we('Course regulations','Studienordnung')
, 'PO' => we('Examination regulations','Prüfungsordnung')
, 'LF' => we('Guideline','Leitfaden')
, 'INFO' => we('other information','sonstige Information')
);

$choices_group_status = array(
  GROUPS_STATUS_PROFESSOR => we('professor','Professur')
, GROUPS_STATUS_SPECIAL => we('auxiliary professor',"au{$SZLIG}erplanm{$aUML}{$SZLIG}ige Professur")
, GROUPS_STATUS_JOINT => we('jointly appointed professor','gemeinsam berufene Professur')
, GROUPS_STATUS_EXTERNAL => we('external','externe')
, GROUPS_STATUS_LABCOURSE => we('lab course','Praktikum')
, GROUPS_STATUS_OTHER => we('other','sonstige')
);
$choices_person_status = array(
  PEOPLE_STATUS_OTHER => we('other','sonstige')
, PEOPLE_STATUS_PROFESSOR => we('professor','Professur')
, PEOPLE_STATUS_JOINT => we('jointly appointed professor','gemeinsam berufene Professur')
// , PEOPLE_STATUS_OTHERJOINT => we('jointly appointed (other)','gemeinsam berufene (andere)')
, PEOPLE_STATUS_SPECIAL => we('auxiliary professor',"au{$SZLIG}erplanm{$aUML}{$SZLIG}ige Professur")
, PEOPLE_STATUS_HONORARY => we('honorary professor','Honorarprofessur')
, PEOPLE_STATUS_SENIOR => we('senior academic assistant','Privatdozent')
, PEOPLE_STATUS_EXTERNAL => we('external professor','externe Professur')
, PEOPLE_STATUS_STUDENT => we('student','Studierende_r')
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
  , 'professors' => array( 'function' => we('professors','Professor_innen'), 'count' => '*' )
  , 'deputyProfs' => array( 'function' => we('deputy professors','Stellvertretende Professor_innen'), 'count' => '*' )
  , 'academicStaff' => array( 'function' => we('academic staff','Wissenschaftliche Mitarbeiter_innen'), 'count' => '*' )
  , 'deputyAcademicStaff' => array( 'function' => we('deputy academic staff','Stellvertretende Wissenschaftliche Mitarbeiter_innen'), 'count' => '*' )
  , 'students' => array( 'function' => we('student members','studentische Mitglieder'), 'count' => '*' )
  , 'deputyStudents' => array( 'function' => we('deputy student members','Stellvertretende studentische Mitglieder'), 'count' => '*' )
  , 'technicalStaff' => array( 'function' => we('technical staff','Mitarbeiter_innen Technik/Verwaltung'), 'count' => '*' )
  , 'deputyTechnicalStaff' => array( 'function' => we('deputy technical staff','Stellvertretende Mitarbeiter_innen Technik/Verwaltung'), 'count' => '*' )
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
  , 'deputy' => array( 'function' => we('deputy chair','Stellvertretender Vorsitz'), 'count' => '1' )
  , 'professors' => array( 'function' => we('professors','Professor_innen'), 'count' => '*' )
  , 'staff' => array( 'function' => we('staff','Mitarbeiter_innen'), 'count' => '*' )
  , 'students' => array( 'function' => we('student representatives','studentische Vertreter_innen'), 'count' => '*' )
  )
, 'examBoardEdu' => array(
    '_BOARD' => we('examination board BEd/MEd','Prüfungsausschuss BEd/MEd')
  , '_MINPRIV' => PERSON_PRIV_COORDINATOR
  , 'chair' => array( 'function' => we('chair','Vorsitz'), 'count' => '1' )
  , 'deputy' => array( 'function' => we('deputy chair','Stellvertretender Vorsitz'), 'count' => '1' )
  , 'professors' => array( 'function' => we('professors','Professor_innen'), 'count' => '*' )
  , 'staff' => array( 'function' => we('staff','Mitarbeiter_innen'), 'count' => '*' )
  , 'students' => array( 'function' => we('student representatives','studentische Vertreter_innen'), 'count' => '*' )
  )
, 'studiesBoard' => array(
    '_BOARD' => we('board of study affairs','Studienkommission')
  , '_MINPRIV' => PERSON_PRIV_COORDINATOR
  , 'professors' => array( 'function' => we('professors','Professor_innen'), 'count' => '*' )
  , 'deputyProfs' => array( 'function' => we('deputy professors','Stellvertretende Professor_innen'), 'count' => '*' )
  , 'students' => array( 'function' => we('student members','studentische Mitglieder'), 'count' => '*' )
  , 'deputyStudents' => array( 'function' => we('deputy student members','Stellvertretende studentische Mitglieder'), 'count' => '*' )
  )
, 'guidance' => array(
    '_BOARD' => we('guidance','Beratung')
  , '_MINPRIV' => PERSON_PRIV_COORDINATOR
  , 'enrollment_mono' => array( 'function' => we('guidance for prospective students (BSc,Msc)', "Beratung f{$uUML}r Studienbewerber (BSc,MSc)"), 'count' =>'*' )
  , 'enrollment_edu' => array( 'function' => we('guidance for prospective students (BEd,MEd)', "Beratung f{$uUML}r Studienbewerber (BEd,MEd)"), 'count' =>'*' )
  , 'mono' => array( 'function' => we('course guidance BSc/MSc/Diplom','Studienberatung BSc/MSc/Diplom'), 'count' => 1 )
  , 'edu' => array( 'function' => we('course guidance BEd/MEd','Studienberatung BEd/MEd'), 'count' => 1 )
  , 'erasmus' => array( 'function' => we('SOCRATES/ERASMUS Contact','SOCRATES/ERASMUS Beauftragte_r'), 'count' => '*' )
  , 'bafoeg' => array( 'function' => we("BAF{$oUML}G guidance","BAF{$oUML}G Beratung"), 'count' => '*' )
  )
);

?>
