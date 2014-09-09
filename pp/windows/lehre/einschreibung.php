<?php // /pp/windows/lehre/intor.php

sql_transaction_boundary('*');

define( 'OPTION_SHOW_TEASER', 1 );
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
    , 'questions' => array( 'cols' => 40, 'lines' => 6 )
    , 'programme' => array()
    )
  , array( 'sources' => 'http' , 'flag_problems' => $flag_problems, 'tables' => 'applicants', 'failsafe' => 0 )
  );

  $app_old = sql_one_applicant( "creator_sessions_id=$login_sessions_id", 0 );
}

echo html_tag( 'h1', '', we('Information for Prospective Physics Students',"Informationen f{$uUML}r Studieninteressierte zu Studieng{$aUML}ngen mit Fach Physik") );



$enroll_link = we('http://www.uni-potsdam.de/en/studium/zugang.html', 'http://www.uni-potsdam.de/studium/zugang.html');

if( $options & OPTION_SHOW_TEASER ) {
  open_fieldset('inline_block medpads qqpads medskips');
    echo inlink( '!', array(
      'class' => 'floatright icon qquadl close'
    , 'options' => ( $options & ~OPTION_SHOW_TEASER )
    , 'title' => we('close teaser','Schliessen' )
    ) );
    
    echo teaser_view( 'studium', 'format=plain' );
  
    if( $cookie_type && ! $app_old ) {
      if( ( $action === 'save' ) && ( ! $error_messages ) ){
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
    
        open_div( 'clear smallpads', we('Thank you for you registration! We will contact you by email soon.', "Vielen Dank f{$uUML}r Ihre Registrierung! Wir werden uns bald per email mit Ihnen in Verbindung setzen." ) );
  
      } else {
        open_span( 'clear block smallpads', we( "You are interested in studying physics in Potsdam?", "Sie interessieren sich f${uUML}r ein Physikstudium (auch im Lehramt) in Potsdam?" ) );
        open_span( 'block smallpadt medpadb', we(
          "Please send us you contact information, we would like to invite you to an informal
           meeting in this summer!
           (currently, the next meetings ist scheduled for September 9, beginning at 2pm. One or two Professors, a postdoc and a physics student
           will take part and talk with you about studying physics in Potsdam.)"
        , "Bitte tragen Sie hier Ihre Kontaktdaten ein, wir m{$oUML}chten Sie
           gerne noch im Sommer vor Semesterbeginn zu einem Kennenlerntreffen einladen!
           (als n{$aUML}chster m{$oUML}glicher Termin f{$uUML}r das Treffen ist bis jetzt der 9. September ab 14 Uhr vorgesehen, weitere Termine k{$oUML}nnen bei Bedarf vereinbart werden.
           Beim Treffen werden ein oder zwei Physikprofessoren, ein Postdoc und ein_e Vertreter_in der Fachschaft Physik anwesend sein,
           um mit Ihnen {$uUML}ber das Physikstudium in Potsdam zu reden.)"
        ) );
      
        open_fieldset('line', label_element( $fields['gn'], '', we('first name', 'Vorname') ), string_element( $fields['gn'] ) );
        open_fieldset('line', label_element( $fields['sn'], '', we('last name', 'Nachname') ), string_element( $fields['sn'] ) );
        open_fieldset('line', label_element( $fields['mail'], '', we('email', 'Email') ), string_element( $fields['mail'] ) );
        open_fieldset('line', label_element( $fields['street'], '', we('street and number','Strasse und Hausnummer') ), string_element( $fields['street'] ) );
        open_fieldset('line', label_element( $fields['city'], '', we('postal code and city','PLZ und Ort') ), string_element( $fields['city'] ) );
        open_fieldset('line', label_element( $fields['country'], '', we('country','Land') ), string_element( $fields['country'] ) );
        open_fieldset(
          'line smallskips'
        , label_element( $fields['programme'], '', we("I'm interested in the degree programme...", "Ich Interessiere mich f{$uUML}r den Studiengang mit Abschluss..."  ) )
        );
          echo radiolist_element( $fields['programme'], array( 'choices' => array(
            PROGRAMME_BSC => $programme_text[ PROGRAMME_BSC ]
          , PROGRAMME_BED => $programme_text[ PROGRAMME_BED ]
          , PROGRAMME_MSC => $programme_text[ PROGRAMME_MSC ]
          , PROGRAMME_MED => $programme_text[ PROGRAMME_MED ]
          ) ) );
        close_fieldset();
      
        open_fieldset('line', label_element( $fields['questions'], '', we('comments or questions','Anmerkungen und Fragen an uns') ), textarea_element( $fields['questions'] ) );
      
        open_span('right block', save_button_view( 'text='.we('submit','Abschicken') ) );
      
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
    
    }
  close_fieldset();
}



echo html_tag( 'h2', 'bigskipt', we('Bachelor/Master of Science in Physics (BSc or MSc)','Ein-Fach-Bachelor/Master in Physik (BSc oder MSc)') );


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
          , we('deadlines for application: March 15 (for summer term) and September 15 (for winter term)'
              ,"Bewerbungsfristen: 15. M{$aUML}rz (zum Sommersemester) und 15. September (zum Wintersemester)")
          )
        )
      );
    
      open_li( 'tinyskips'
      , tb( html_alink(
            we('http://www.uni-potsdam.de/en/studium/zugang/enrollment-master.html', 'http://www.uni-potsdam.de/studium/zugang/immatrikulation-master.html')
          , 'class=href outlink,text='.we('Enrollment for the Master program at the University of Potsdam', "Einschreibung zum Masterstudium an der Universit{$aUML}t Potsdam" )
          ) 
        , we( 'deadlines for enrollment: February 15 until May 10 (for summer term) and August 15 until November 10 (for winter term)'
            , "Einschreibezeitraum: 15.02. bis 10.05. (zum Sommersemester) und 15.08. bis 10.11. (zum Wintersemester)" )
        )
      );
    
//      open_li( 'tinyskips'
//      , we( em('Guidance') . ' on the MSc (not teaching-oriented) degree program in physics:'
//                 , em("Beratung") . " zum MSc-Studiengang (Ein-Fach-Master, nicht lehramtsbezogen) in Physik:" )
//        . alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list' )
//      );
    
    close_ul();
  
  close_li();

close_ul();

open_div('medskips'
, we( em('Guidance') . ' on the BSc/MSc (not teaching-oriented) degree programs in physics:'
    , em("Beratung") . " zum BSc/MSc-Studiengang (Ein-Fach-Bachelor/Master, nicht lehramtsbezogen) in Physik:" )
  . alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list,class=bold' )
);



echo html_tag( 'h2', 'bigskipt', we('Bachelor/Master of Education (BEd or MEd) with Physics','Lehramtsbezogener Bachelor/Master (BEd oder MEd) mit Fach Physik') );

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
    echo html_tag( 'h3', 'medskipt medskipb', inlink( 'bed', array( 'text' => we('Bachelor program (BEd)','Bachelorstudium Lehramt (BEd) mit Fach Physik') ) ) );
    
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
    
//      open_li( 'tinyskips'
//      , we( em('Guidance') . ' on the BEd program with physics:', em("Beratung") . " zum BEd-Studiengang mit Fach Physik:")
//        . alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list' )
//      );
    
    close_ul();

  close_li();

  open_li();

    echo html_tag( 'h3', 'bigskipt medskipb', inlink( 'med', array( 'text' => we('Master program (MEd)','Masterstudium Lehramt (MEd) mit Fach Physik') ) ) );
    
    open_ul();
    
      open_li( 'tinyskips' , we(
        em('Admission')
        . ' to the Master of Education (MEd) '
              . ' degree program is ' . em('not restricted (no NC)') . '; no application for admission is required.'
        , em('Zulassung')
        . " zum Studiengang Master of Education (MED) "
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
    
//      open_li( 'tinyskips'
//      , we( em('Guidance') . ' on the MEd program with physics:', em("Beratung") . " zum MEd-Studiengang mit Fach Physik):" )
//        . alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list' )
//      );
    
    close_ul();

  close_li();

close_ul();


open_div('medskips'
, we( em('Guidance') . ' on the BEd/MEd program with physics:', em("Beratung") . " zum BEd/MEd-Studiengang (Lehramtsstudium) mit Fach Physik):" )
  . alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list,class=bold' )
);

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


?>
