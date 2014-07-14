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

echo html_tag( 'h1', '', we('Information for Prospective Students',"Informationen f{$uUML}r Studieninteressierte") );

// echo tb( we('Contact and guidance for prospective students:',"Kontakt und Beratung zu allen Fragen zur Einschreibung:")
//        , alink_person_view( array(
//              'people_id !=' => '0'
//            , 'board' => 'guidance', 'function' => array( 'enrollment_mono', 'enrollment_edu' )
//            )
//          , 'office=1,format=list'
//          )
// );



$enroll_link = we('http://www.uni-potsdam.de/en/studium/zugang0.html', 'http://www.uni-potsdam.de/studium/zugang.html');

if( $options & OPTION_SHOW_TEASER ) {
  open_fieldset('inline_block medpads qqpads medskips');
    echo inlink( '!', array(
      'class' => 'floatright icon qquadl close'
    , 'options' => ( $options & ~OPTION_SHOW_TEASER )
    , 'title' => we('close teaser','Schliessen' )
    ) );
    
    open_div('teaser textaroundphoto bigskipb qquads italic large,style=max-width:600px;' );
      echo html_div( 'floatright', photo_view( '/pp/img/teaser.1.jpg', 'Ismael Carrillo', 'format=url' ) );
      echo html_span('large', '"' ) . we("
        People say physics are hard, well they are right.
        But the proper professors and facilities make this a satisfying challenge.
        I as an astrophysicist student couldn't have chosen a better University;
        the support given to the area inside and outside of the institute with other
        important external collaborations gives us wide opportunities into the future.
      "," 
        Physik hat den Ruf, schwierig zu sein, und das nicht zu unrecht.
        Kompetente Lehrkr{$aUML}fte und die entsprechende technische Ausstattung machen das Studium jedoch zu einer
        lohnenden Herausforderung. Als Astrophysikstudent h{$aUML}tte ich keine bessere Universit{$aUML}t w{$aUML}hlen k{$oUML}nnen.
        Die Unterst{$uUML}tzung in diesen Bereich, intern wie auch durch Kooperationen mit externen Instituten, bietet uns vielf{$aUML}ltige
        Chancen f{$uUML}r die Zukunft.
      ") . html_span('large', '"' );
    close_div();
  
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
           meeting in this summer!"
        , "Bitte tragen Sie hier Ihre Kontaktdaten ein, wir m{$oUML}chten Sie
           gerne noch im Sommer vor Semesterbeginn zu einem Kennenlerntreffen einladen!"
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


echo tb( we(" You can apply for admission (in physics, only required for the Master (MSc and MEd) degree programs) and enroll on the web site of the University: "
           ,"Bewerbung um Zulassung (im Fach Physik nur erforderlich f{$uUML}r die Master-Studieng{$aUML}nge (Abschluss MSc und MEd)) und Einschreibung erfolgen {$uUML}ber die Webseite der Universit{$aUML}t:" )
, array(
    html_alink( $enroll_link
    , 'class=href outlink,text='.we('Application and Enrollment at the University of Potsdam','Bewerbung und Einschreibung an der Universität Potsdam')
    ) 
  , we(' Please read the program-specific notes below; in particular, enrollment will only be possible in the specified periods!', " Bitte beachten Sie die Hinweise zu den einzelnen Studieng${aUML}ngen unten; insbesondere ist die Einschreibung nur in den unten angegebenen Zeitr{$aUML}umen m{$oUML}glich!" )
  )
);


echo html_tag( 'h2', 'bigskipt', we('Bachelor degree program','Bachelorstudium (BSc oder BEd)') );

open_tag( 'p', 'smallskips', we("
  Admission to the Bachelor of Science (BSc, not teaching-oriented) degree program in physics is not restricted (no Numerus Clausus); no application is required before enrollment.
", "
  Der Studiengang Bachelor of Science (BSc, Ein-Fach-Bachelor, nicht lehramtsbezogen) in Physik ist nicht zulassungsbeschr{$aUML}nkt (kein NC); die Einschreibung erfolgt ohne vorherige Bewerbung.
") );

open_tag( 'p', 'smallskips', we(
"Admission to the Bachelor of Education (BEd, teaching-oriented) degree program in physics in not restricted (no Numerus Clausus); depending on the other subject(s), application for admission
 may or may not be required.
", "
 Der Studiengang Bachelor of Education (BEd, lehramtsbezogen) ist im Fach Physik ebenfalls nicht zulassungsbeschr{$aUML}nkt (kein NC). F{$uUML}r andere F{$aUML}cher
 kann eine Zulassungsbeschr{$aUML}nkung bestehen; ob vor der Einschreibung eine
 Bewerbung um Zulassung erforderlich ist h{$aUML}ngt daher von der F{$aUML}cherkombination ab.
") );

open_tag( 'p', 'smallskips', we(
"Enrollment for a Bachelor degree program takes place from August 15 until September 15 for the following Winter term.
 Additionally, enrollment is possible from February 15 until March 15 for the following Summer term, but only for
 higher semesters of study (not for beginners).
 Enrollment is only possible in the specified periods.
", "
 Die Einschreibung zum Bachelorstudium in Physik erfolgt jeweils im Zeitraum 15.08. bis 15.09. für das folgende Wintersemester.
 Für höhere Fachsemester (alle außer dem ersten Fachsemester) ist die Einschreibung auch vom 15.02. bis 15.03 für das folgende Sommersemester möglich.
 Die Einschreibung erfolgt nur in den angegebenen Zeitr{$aUML}umen.
") );


echo tb( we('Guidance on the BSc (not teaching-oriented) degree program:',"Beratung zum BSc-Studiengang (Ein-Fach-Bachelor, nicht lehramtsbezogen):")
       , alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list' )
);
echo tb( we('Guidance on the BEd (teaching-oriented) degree program:',"Beratung zum BEd-Studiengang (Lehramt mit Fach Physik):")
       , alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list' )
);



echo html_tag( 'h2', 'bigskipt', we('Master degree program (MSc or MEd)','Masterstudium (MSc oder MEd)') );


echo tb( we("Application for admission", "Bewerbung um Zulassung")
, array(
    we('Admission to the Master of Science (MSc, not teaching-oriented) in physics degree program is not restricted (no NC); nevertheless, application for admission is required before enrollment:'
      ,"Der Studiengang Master of Science (MSc, nicht lehramtsbezogen) in Physik ist nicht zulassungsbeschr{$aUML}nkt (kein NC); dennoch ist vor der Einschreibung eine Bewerbung um Zulassung erforderlich:")
  , html_alink(
      we('http://www.uni-potsdam.de/en/studium/zugang0/application-master.html', 'http://www.uni-potsdam.de/studium/zugang/bewerbung-master.html')
    , 'class=href outlink,text='.we('Application for admission to the Master program at the University of Potsdam', "Bewerbung um Zulassung zum Masterstudium an der Universit{$aUML}t Potsdam" )
    ) 
  , we('deadlines for application: March 15 (for summer term) and September 15 (for winter term)'
      ,"Bewerbungsfristen: 15. M{$aUML}rz (zum Sommersemester) und 15. September (zum Wintersemester)")
  , we('Admission to the Master of Education (MEd, teaching-oriented) degree program is not restricted (no NC) and no application for admission is required.'
      ,"Der Studiengang Master of Education (MED, lehramtsbezogen) ist nicht zulassungsbeschr{$aUML}nkt (kein NC) und eine Bewerbung um Zulassung ist nicht erforderlich." )
  )
);

echo tb( we("Enrollment for a Master degree program"
          , "Einschreibung zum Masterstudium")
, array(
    html_alink(
      we('http://www.uni-potsdam.de/en/studium/zugang0/enrollment-master.html', 'http://www.uni-potsdam.de/studium/zugang/immatrikulation-master.html')
    , 'class=href outlink,text='.we('Enrollment for the Master program at the University of Potsdam', "Einschreibung zum Masterstudium an der Universit{$aUML}t Potsdam" )
    ) 
  , we('For the Master of Science (MSc, not teaching-oriented) in physics degree program, application and admission is required before enrollment.'
      ,"F{$uUML}r den Studiengang Master of Science (MSc, nicht lehramtsbezogen) in Physik ist die Einschreibung erst nach Bewerbung und Zulassung m{$oUML}glich.")
  , we('Enrollment for the Master of Education (MEd, teaching-oriented) degree program is not restricted,  and no application for admission is required.'
      ,"Einschreibung zum Studiengang Master of Education (MEd, lehramtsbezogen) ist ohne vorherige Bewerbung um Zulassung m{$oUML}glich.")
  , we('deadlines for enrollment: February 15 until May 10 (for summer term) and August 15 until November 10 (for winter term)'
      ,"Einschreibezeitraum: 15.02. bis 10.05. (zum Sommersemester) und 15.08. bis 10.11. (zum Wintersemester)")
  )
);

echo tb( we('Guidance on the MSc program:',"Beratung zum MSc-Studiengang (Ein-Fach-Master):")
       , alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list' )
);
echo tb( we('Guidance on the MEd program:',"Beratung zum MEd-Studiengang (Lehramt mit Fach Physik):")
       , alink_person_view( 'people_id!=0,board=guidance,function=edu', 'office=1,format=list' )
);





?>
