<?php // /pp/windows/lehre/intor.php

sql_transaction_boundary('*');

define( 'OPTION_SHOW_TEASER', 0 );
init_var('options','type=u,global=1,sources=http persistent initval,set_scopes=script,initval='.OPTION_SHOW_TEASER );

if( $cookie_type ) {
  $flag_problems = ( $action === 'save' );
  $fields = init_fields( array(
      'mail' => array( 'size' => 40 )
    , 'gn' => array( 'size' => 40 )
    , 'sn' => array( 'size' => 40 )
    , 'street' => array( 'size' => 40 )
    , 'city' => array( 'size' => 40 )
    , 'country' => array( 'size' => 40 )
    , 'questions' => array( 'cols' => 60, 'lines' => 6 )
    , 'programme' => array()
    )
  , array( 'sources' => 'http' , 'flag_problems' => $flag_problems, 'tables' => 'applicants', 'failsafe' => 0 )
  );

  $app_old = sql_one_applicant( "creator_sessions_id=$login_sessions_id", 0 );
}

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('lehre');
    echo html_tag( 'h1', '', we('Studies / degree programs','Lehre am Institut / Studiengänge') );
  close_div();
close_div();
// echo html_tag( 'h1', '', we('Information for Prospective Physics Students',"Informationen f{$uUML}r Studieninteressierte zu Studieng{$aUML}ngen mit Fach Physik") );

$enroll_link = we('http://www.uni-potsdam.de/en/studium/zugang.html', 'http://www.uni-potsdam.de/studium/zugang.html');

if( $options & OPTION_SHOW_TEASER ) {
  open_fieldset('inline_block medpads qqpads medskips');
    echo inlink( '!', array(
      'class' => 'floatright icon qquadl close'
    , 'options' => ( $options & ~OPTION_SHOW_TEASER )
    , 'title' => we('close teaser','Schliessen' )
    ) );
    
if( 0 ) {
    if( $cookie_type && ! $app_old ) {
      if( ( $action === 'save' ) && ( ! $error_messages ) ) {
        $values = array( 'language' => $language_suffix );
        foreach( $fields as $fieldname => $field ) {
          if( $fieldname[ 0 ] !== '_' ) {
            $values[ $fieldname ] = $field['value'];
          }
        }
    
        $error_messages = sql_save_applicant( 0, $values, 'action=dryrun' );
      }
  
      if( ( $action === 'save' ) && ( ! $error_messages ) ){
        $applicants_id = sql_save_applicant( 0, $values, 'action=hard' );
    
        open_div( 'clear bold bigpads', we('Thank you for you registration! We will contact you by email soon.', "Vielen Dank f{$uUML}r Ihre Registrierung! Wir werden uns bald per email mit Ihnen in Verbindung setzen." ) );

        echo teaser_view( 'studium', 'format=plain' );
  
      } else {
        if( ! $error_messages ) {
          echo teaser_view( 'studium', 'format=plain' );
        }
        
        echo html_tag( 'h2', 'clear bigpadt', we( "Visit us at the Institute!", "Besuchen Sie uns am Institut!" ) );
        open_span( 'block smallpads bold', we(
          "Sie haben Interesse an einem Studium am Institut für Physik und Astronomie der Universität Potsdam?
           Sie wollen unsere Räumlichkeiten kennenlernen und einmal reinschnuppern, was einen im Studium so erwartet?
           Dann kommen Sie auf einen Besuch vorbei!"
        , "Sie haben Interesse an einem Studium am Institut für Physik und Astronomie der Universität Potsdam?
           Sie wollen unsere Räumlichkeiten kennenlernen und einmal reinschnuppern, was einen im Studium so erwartet?
           Dann kommen Sie auf einen Besuch vorbei!"
        ) );
        open_span( 'block smallpads bold', 
          "Hier können Sie sich für einen Besuch am Institut für Physik und Astronomie anmelden.
           Wir zeigen Ihnen den Campus Golm und alle Orte, die für künftige Physikstudierende wichtig werden.
           Die Hörsäle und Seminarräume, sowie die Labore des Grundpraktikums.
           Bei Interesse organisieren wir gerne, dass Sie sich in eine der Physikvorlesungen der ersten Semester setzen können,
           um einen besseren Eindruck vom Studium zu gewinnen!
           Auch haben Sie die Gelegenheit, sich mit Physikstudierenden auszutauschen und aus erster Hand zu erfahren, was das Physikstudium so ausmacht.
        ");
        open_span( 'block smallpadt medpadb bold', 
         "Interesse geweckt? Dann melden Sie sich an, wir melden uns zur Terminvereinbarung bei Ihnen. Wir freuen uns auf Sie!"
        );

        // open_fieldset('line', label_element( $fields['gn'], '', we('first name', 'Vorname') ), string_element( $fields['gn'] ) );
        open_fieldset('line', label_element( $fields['sn'], '', we('name (*)', 'Name (*)') ), string_element( $fields['sn'] ) );
        open_fieldset('line', label_element( $fields['mail'], '', we('email (*)', 'Email (*)') ), string_element( $fields['mail'] ) );
        // open_fieldset('line', label_element( $fields['street'], '', we('street and number','Strasse und Hausnummer') ), string_element( $fields['street'] ) );
        open_fieldset('line', label_element( $fields['city'], '', we('place of residence','Wohnort und ggf. Land') ), string_element( $fields['city'] ) );
        // open_fieldset('line', label_element( $fields['country'], '', we('country','Land') ), string_element( $fields['country'] ) );
        open_fieldset(
          'line smallskips'
        , label_element( $fields['programme'], '', we("I'm interested in the degree programme...(*)", "Ich Interessiere mich f{$uUML}r den Studiengang...(*)"  ) )
        );
          echo radiolist_element( $fields['programme'], array( 'choices' => array(
            PROGRAMME_BSC => $programme_text[ PROGRAMME_BSC ]
          , PROGRAMME_BED => $programme_text[ PROGRAMME_BED ]
          , PROGRAMME_MSC => $programme_text[ PROGRAMME_MSC ]
          , PROGRAMME_MED => $programme_text[ PROGRAMME_MED ]
          , PROGRAMME_M_ASTRO => $programme_text[ PROGRAMME_M_ASTRO ]
          ) ) );
        close_fieldset();
      
        open_fieldset('line', label_element( $fields['questions'], '', we('your comments or questions','Ihre Anmerkungen und Fragen an uns') ), textarea_element( $fields['questions'] ) );
      
        open_span('left qquadl block', save_button_view( array( 'text' => we('Submit','Abschicken'), 'P5_offs' => '0x0' ) ) );
      
        open_span( 'block small medpadt ', we(
          "Your registration here is optional; it will neither substitute, nor is it required for, formal application and/or enrollment on the "
          . html_alink( $enroll_link, "class=href outlink small,text=university web site" ) ."."
        , "Ihre Registrierung hier ist freiwillig; sie ersetzt nicht die formale Bewerbung und/oder Einschreibung {$uUML}ber die "
          . html_alink( $enroll_link, "class=href outlink small,text=Seiten der Universit{$aUML}t" )
          . " und ist daf{$uUML}r auch keine Voraussetzung."
        ) );
        open_span( 'block small smallpads ', we(
          "Your data will be kept confidential and will not be transfered to any person outside of the institute."
        , "Ihre Daten werden vertraulich behandelt und nicht an Stellen au{$SZLIG}erhalb des Instituts weitergegeben."
        ) );
      }
    
    } else { // $app_old

      echo teaser_view( 'studium', 'format=plain' );
  
    }

  open_span( 'block bold bigpadt');
    echo "Ein erstes Kennenlerntreffen für Studieninteressierte am Physikinstitut findet statt am";
    open_div( 'center bold smallpads', "Donnerstag 7.7. um 10:00 Uhr in Haus 28 (Physikinstitut), Campus Golm, Raum 1.033" );
    open_span( 'block smallpads', "
      Sie haben Gelegenheit, mit "
      . alink_person_view( "sn=gühr", 'text=Prof. Gühr,default=Prof. Gühr' )
      ." sowie mit anderen Mitgliedern des Instituts und der Fachschaft zu sprechen;
      anschließend bieten wir eine Haus- und Laborführung an.
    " );
    open_span( 'block smallpads', "An dem Treffen können Sie auch ohne Voranmeldung teilnehmen." );
  close_span();

  } else { // manual switch: 0 or 1

      echo teaser_view( 'studium', 'format=plain' );
  }
  
  close_fieldset();
}



open_ccbox( '', we('Physics | Bachelor / Master','Physik | Ein-Fach-Bachelor / Master') );

  open_span( 'floatright large medpads qqpadl', photo_view( '/pp/fotos/bsc.jpg', 'Karla Fritze', array( 'class' => 'photo', 'format' => 'url' ) ) );
  open_tag( 'p', 'smallskips', "
    Das Physikstudium an der Uni Potsdam besteht aus einem 3-jährigen
    Bachelorstudiengang, der bei Interesse um einen 2-jährigen
    Masterstudiengang erg{$aUML}nzt werden kann. Im Bachelorstudium werden die
    Grundlagen im Fach Physik erworben. Neben dem Grundlagenstudium bietet
    der Bachelor bereits die M{$oUML}glichkeit, sich in ein "
  . inlink( 'forschung', 'class=href alink,text=Fachgebiet' ) .
    " der Physik zu vertiefen und weitere akademische
    Kompetenzen an anderen Fakultäten und Instituten zu sammeln.
    Der Masterstudiengang bietet die M{$oUML}glichkeit, physikalische und andere akademische
    Kompetenzen weiter zu vertiefen, der Schwerpunkt liegt dabei auf der Mitarbeit in "
  . inlink( 'forschung', array( 'text' => "Arbeitsgruppen des Instituts oder kooperierender Forschungseinrichtungen" ) )
  . '.'
  );
  
  open_ul();
  
    open_li();
      echo html_tag( 'h3', 'medskips', inlink( 'bsc', array( 'text' => we('Bachelor program (BSc) in Physics', 'Bachelorstudium - Bachelor of Science (BSc) in Physik') ) ) );
      
      open_ul();
      
        open_li( 'tinyskips', we(
          em('Admission')
          . " to the Bachelor of Science in physics program is " . em('not restricted (no Numerus Clausus)') . "; no application for admission is required."
        ,
          em('Zulassung')
          . ' zum Studiengang Bachelor of Science (BSc) in Physik'
          . " ist " . em("nicht beschr{$aUML}nkt (kein NC)") . "; die Einschreibung erfolgt ohne vorherige Bewerbung."
        ) );
      
      
        open_li( 'tinyskips'
        , tb( html_alink(
              we('http://www.uni-potsdam.de/en/studium/zugang/enrollment-bachelor.html', 'http://www.uni-potsdam.de/studium/zugang/immatrikulation-bachelor.html')
            , 'class=href outlink,text='.we('Enrollment for the Bachelor program at the University of Potsdam', "Einschreibung zum Bachelorstudium an der Universit{$aUML}t Potsdam" )
            ) 
          , we(
             "Enrollment for a Bachelor degree program takes place from August 15 until September 15 for the following Winter term.
              Additionally, enrollment is possible from February 15 until March 15 for the following Summer term, but only for
              higher semesters of study (not for beginners).
              Enrollment is only possible in the specified periods."
            ,
             "Die Einschreibung zum Bachelorstudium in Physik erfolgt jeweils im Zeitraum 15.08. bis 15.09. f{$uUML}r das folgende Wintersemester.
              F{$uUML}r h{$oUML}here Fachsemester (alle au{$SZLIG}er dem ersten Fachsemester) ist die Einschreibung auch vom 15.02. bis 15.03 f{$uUML}r das folgende Sommersemester m{$oUML}glich.
              Die Einschreibung erfolgt nur in den angegebenen Zeitr{$aUML}umen."
            )
          )
        );
      
      
      close_ul();
      
    close_li();
  
  
  
    open_li();
    
      echo html_tag( 'h3', 'medskips', inlink( 'msc', array( 'text' => we('Master program (MSc) in Physics', 'Masterstudium - Master of Science (MSc) in Physik') ) ) );
      
      open_ul();
        open_li( 'tinyskips'
        , tb( html_alink(
                we('http://www.uni-potsdam.de/en/studium/zugang/application-master.html', 'http://www.uni-potsdam.de/studium/zugang/bewerbung-master.html')
              , 'class=href outlink,text='.we('Application for admission to the Master program at the University of Potsdam', "Bewerbung um Zulassung zum Masterstudium an der Universit{$aUML}t Potsdam" )
              ) 
          , array(
              we( em('Admission')
                  . ' to the Master of Science (MSc) in physics program is '
                  .  em('not restricted (no NC); nevertheless, application for admission is required prior to enrollment.')
              ,
                  em('Zulassung')
                  . " zum Studiengang Master of Science (MSc) in Physik ist "
                  . em( "nicht beschr{$aUML}nkt (kein NC); dennoch ist vor der Einschreibung eine Bewerbung um Zulassung erforderlich.")
              )
            , we('deadline for application: March 01 (for summer term 2017)'
                ,"Bewerbungsfrist zum Sommersemester 2017 ist der 01. M{$aUML}rz")
            , 'Wenn sie sich auf ein höheres Fachsemester bewerben wollen, weil sie
               bereits an einer anderen Universität im Masterstudiengang
               immatrikuliert sind oder waren, wenden sie sich bitte unabhängig von diesen
               Fristen an den ' . alink_person_view( 'offices.board=examBoardMono,offices.function=chair', 'text=Prüfungsausschussvorsitzenden' ) .'.'
            )
          )
        );
      
        open_li( 'tinyskips'
        , tb( html_alink(
              we('http://www.uni-potsdam.de/en/studium/zugang/enrollment-master.html', 'http://www.uni-potsdam.de/studium/zugang/immatrikulation-master.html')
            , 'class=href outlink,text='.we('Enrollment for the Master program at the University of Potsdam', "Einschreibung zum Masterstudium an der Universit{$aUML}t Potsdam" )
            ) 
          , we(
              'After admission, you will receive instructions on how to proceed with '
              . html_alink( 'http://www.uni-potsdam.de/studium/zugang/immatrikulation-master.html', 'class=href outlink,text=enrollment' ) .'.'
            ,  'Mit dem Zulassungsbescheid erhalten Sie die Information, wie Sie die '
               . html_alink( 'http://www.uni-potsdam.de/studium/zugang/immatrikulation-master.html', 'class=href outlink,text=Immatrikulation' )
               . " vornehmen k{$oUML}nnen. Die Beantragung erfolgt online vom 15.08. bis 10.11. f{$uUML}r das Wintersemester bzw. vom 15.02. bis 10.05. f{$uUML}r das Sommersemester."
            )
          )
        );
      
      close_ul();
    
    close_li();
  
  close_ul();
  
  open_div('medskips', bold( we( 'Course Guidance: ' , "Studienberatung: " ) ) . alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list,class=bold' ) );
  open_div('clear','');

close_ccbox();


open_ccbox( '', 'Astrophysics | Master (MSc)' );
  open_div( 'illu', image('astrophysics') );
  open_tag( 'p', 'smallskips', we('in English language','in englischer Sprache') );
  open_tag( 'p', 'smallskips', inlink( 'mastro', array( 'text' => we('more information...', 'weitere Informationen zum Studiengang...') ) ) );
  open_div('medskips', bold( we( 'Course Guidance: ' , "Studienberatung: " ) ) . alink_person_view( 'people_id!=0,board=guidance,function=astro', 'office=1,format=list,class=bold' ) );
  open_div('clear','');

close_ccbox();


open_ccbox( '', we('Bachelor of Education (BEd) / Master of Education (MEd) with Physics','Lehramtsbezogener Bachelor (BEd) / Master (MEd) mit Fach Physik') );
  open_span( 'floatright large medpads qqpadl', photo_view( '/pp/fotos/bed.jpg', 'Karla Fritze', array( 'class' => 'photo', 'format' => 'url' ) ) );

open_tag( 'p', 'smallskips', "
  Das Lehramtsstudium der Physik an der Uni Potsdam besteht aus einem 3-j{$aUML}hrigen Bachelor- und
  einem 2-j{$aUML}hrigen Masterstudiengang.
  Im Bachelorstudium werden fachliche Kenntnisse im Fach Physik einschlie{$SZLIG}lich der spezifischen
  Erkenntnis- und Arbeitsmethoden sowie Kompetenzen der Fachdidaktik erworben, die dazu bef{$aUML}higen,
  einen Sch{$uUML}lerorientierten und wissenschaftlich fundierten Physikunterricht zu gestalten.
  Die Ausbildung in experimenteller Physik erfolgt vorwiegend gemeinsam mit Studierenden ohne
  Lehramtsbezug.
" );


open_ul();
  open_li();
    echo html_tag( 'h3', 'medskips', inlink( 'bed', array( 'text' => we('Bachelor program (BEd)','Bachelorstudium Lehramt (BEd) mit Fach Physik') ) ) );
    
    open_ul();
    
      open_li( 'tinyskips', we(
          em("Admission")
          . " to the Bachelor of Education (BEd)"
          . " degree program is " . em( "not restricted (no Numerus Clausus) in physics") . "; depending on the other subject(s), application for admission may or may not be required."
        ,
          em("Zulassung ")
          . " zum Studiengang Bachelor of Education (BEd)"
          . " ist " . em( "im Fach Physik nicht beschr{$aUML}nkt (kein NC)." )
          . " F{$uUML}r andere F{$aUML}cher
              kann eine Zulassungsbeschr{$aUML}nkung bestehen; ob vor der Einschreibung eine
              Bewerbung um Zulassung erforderlich ist, h{$aUML}ngt daher von der F{$aUML}cherkombination ab."
        )
      );
    
      open_li( 'tinyskips'
      , tb( html_alink(
            we('http://www.uni-potsdam.de/en/studium/zugang/enrollment-bachelor.html', 'http://www.uni-potsdam.de/studium/zugang/immatrikulation-bachelor.html')
          , 'class=href outlink,text='.we('Enrollment for the Bachelor program at the University of Potsdam', "Einschreibung zum Bachelorstudium an der Universit{$aUML}t Potsdam" )
          ) 
        , we(
           "Enrollment for a Bachelor degree program takes place from August 15 until September 15 for the following Winter term.
            Additionally, enrollment is possible from February 15 until March 15 for the following Summer term, but only for
            higher semesters of study (not for beginners).
            Enrollment is only possible in the specified periods."
          ,
           "Die Einschreibung zum Bachelorstudium in Physik erfolgt jeweils im Zeitraum 15.08. bis 15.09. f{$uUML}r das folgende Wintersemester.
            F{$uUML}r h{$oUML}here Fachsemester (alle au{$SZLIG}er dem ersten Fachsemester) ist die Einschreibung auch vom 15.02. bis 15.03 f{$uUML}r das folgende Sommersemester m{$oUML}glich.
            Die Einschreibung erfolgt nur in den angegebenen Zeitr{$aUML}umen."
          )
        )
      );
    
    close_ul();

  close_li();

  open_li();

    echo html_tag( 'h3', 'medskips', inlink( 'med', array( 'text' => we('Master program (MEd)','Masterstudium Lehramt (MEd) mit Fach Physik') ) ) );
    
    open_ul();
    
      open_li( 'tinyskips' , we(
        em('Admission')
        . ' to the Master of Education (MEd) '
              . ' degree program is ' . em('not restricted (no NC)') . '; no application for admission is required.'
        , em('Zulassung')
        . " zum Studiengang Master of Education (MEd) "
              . ' ist ' . em( "nicht beschr{$aUML}nkt (kein NC)" ) . "; die Einschreibung erfolgt ohne vorherige Bewerbung."
      ) );
    
      open_li( 'tinyskips'
      , tb( html_alink(
            we('http://www.uni-potsdam.de/en/studium/zugang/enrollment-master.html', 'http://www.uni-potsdam.de/studium/zugang/immatrikulation-master.html')
          , 'class=href outlink,text='.we('Enrollment for the Master program at the University of Potsdam', "Einschreibung zum Masterstudium an der Universit{$aUML}t Potsdam" )
          ) 
        , we( 'deadlines for enrollment: February 15 until May 10 (for summer term) and August 15 until November 10 (for winter term)'
            , "Einschreibezeitraum: 15.02. bis 10.05. (zum Sommersemester) und 15.08. bis 10.11. (zum Wintersemester)" )
        )
      );
    
    close_ul();

  close_li();

close_ul();


  open_div('medskips', bold( we( 'Course Guidance: ' , "Studienberatung: " ) ) . alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list,class=bold' ) );

// echo tb( we(" You can apply for admission (in physics, only required for the Master of Science degree program) and enroll on the web site of the University: "
//            ,"Bewerbung um Zulassung (im Fach Physik nur erforderlich f{$uUML}r den Studiengang mit Abschluss Master of Science (MSc)) und Einschreibung erfolgen {$uUML}ber die Webseite der Universit{$aUML}t:" )
// , array(
//     html_alink( $enroll_link
//     , 'class=href outlink,text='.we('Application and Enrollment at the University of Potsdam','Bewerbung und Einschreibung an der Universität Potsdam')
//     ) 
//   , we(' Please read the program-specific notes above; in particular, enrollment will only be possible in the specified periods!', " Bitte beachten Sie die Hinweise zu den einzelnen Studieng${aUML}ngen oben; insbesondere ist die Einschreibung nur in den unten angegebenen Zeitr{$aUML}umen m{$oUML}glich!" )
//   )
// , 'bigskips'
// );


  open_div('clear','');
close_ccbox();


open_ccbox( '', we('PhD Program at the Insitute of Physics','Promotionsstudium am Institut für Physik') );

  echo tb( inlink( 'phd', array( 'text' => we( 'Information on the PhD program', 'Informationen zum Promotionsstudium' ) ) ) );

close_ccbox();

?>
