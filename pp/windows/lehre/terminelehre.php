<?php

sql_transaction_boundary('*');

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('lehre');
    echo html_tag( 'h1', '', we('Studies / Dates','Lehre am Institut / Termine') );
  close_div();
close_div();
// echo html_tag('h1', '', we('Dates for Physics Students',"Termine f{$uUML}r Physikstudierende") );

open_ccbox( '', we('Dates for prospective students',"Termine f{$uUML}r Studieninteressierte") );

  open_table('td:smallskipt;smallskipb;quads');
  
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline bold', "01.03. " );
      open_td(''
      ,  we('Deadline for ', "Frist f{$uUML}r ")
         . inlink( 'studiengaenge', array( 'text' => we( 'application for admission to the Master of Science (MSc) degree program in summer term'
                                                        ,"Bewerbung um Zulassung zum Studiengang Master of Science (MSc) im Sommersemester (erstes Fachsemester)" ) ) )
         . html_div( 'small'
           , 'Wenn sie sich auf ein höheres Fachsemester bewerben wollen, weil sie
              bereits an einer anderen Universität im Masterstudiengang
              immatrikuliert sind oder waren, wenden sie sich bitte unabhängig von dieser
              Frist an den ' . alink_person_view( 'offices.board=examBoardMono,offices.function=chair', 'text=Prüfungsausschussvorsitzenden' ) .'.'
           )
      ); 
  
    open_tr();
      open_td( 'oneline', "15.02. - 15.03." );
      open_td(''
      ,  inlink( 'studiengaenge', array( 'text' => we(
            'Enrollment for a Bachelor degree program in summer term'
          , "Einschreibung f{$uUML}r Bachelor-Studieng{$aUML}nge zum Sommersemester" )
         ) )
        . html_div( 'small', we(
            '(higher semesters of study only - no enrollment of beginners!)'
          , "(nur f{$uUML}r h{$oUML}here Fachsemester - keine Einschreibung von Studienanf{$aUML}ngern!)"
          ) )
        . html_div( 'small', we(
            'BSc in physics: no application required before enrollment; BEd with physics: application may be required depending on the other subject.'
            , "BSc in Physik: keine vorherige Bewerbung erforderlich; BEd mit Fach Physik: abh{$aUML}ngig vom anderen Fach kann eine Bewerbung erforderlich sein."
        ) )
      );
  
    open_tr();
      open_td( 'oneline', "15.02. - 10.05." );
      open_td(''
      ,  inlink( 'studiengaenge', array( 'text' => we(
            'Enrollment for a Master degree program in summer term'
          , "Einschreibung f{$uUML}r Master-Studieng{$aUML}nge zum Sommersemester" ) ) )
        . html_div( 'small', $programme_text[ PROGRAMME_MSC ] . we( ': application and admission is required before enrollment', ': Einschreibung nur nach vorheriger Bewerbung und Zulassung' ) )
        . html_div( 'small', $programme_text[ PROGRAMME_MED ] . we( ': application is not required before enrollment', ': Einschreibung erfolgt ohne vorherige Bewerbung' ) )
      );
  
  
  
    open_tr();
      open_td('oneline bold', "15.09." );
      open_td(''
      ,  we('Deadline for ', "Frist f{$uUML}r ")
         . inlink( 'studiengaenge', array( 'text' => we( 'application for admission to the Master of Science (MSc) degree program in winter term'
                                                        ,"Bewerbung um Zulassung zum Studiengang Master of Science (MSc) im Wintersemester (erstes Fachsemester)" ) ) )
         . html_div( 'small' 
           , 'Wenn sie sich auf ein höheres Fachsemester bewerben wollen, weil sie
              bereits an einer anderen Universität im Masterstudiengang
              immatrikuliert sind oder waren, wenden sie sich bitte unabhängig von dieser
              Frist an den ' . alink_person_view( 'offices.board=examBoardMono,offices.function=chair', 'text=Prüfungsausschussvorsitzenden' ) .'.'
           )
      ); 
  
    open_tr();
      open_td( 'oneline', "15.08. - 15.09." );
      open_td(''
      ,  inlink( 'studiengaenge', array( 'text' => we( 'Enrollment for a Bachelor degree program in winter term', "Einschreibung f{$uUML}r Bachelor-Studieng{$aUML}nge zum Wintersemester" ) ) )
        . html_div( 'small', we(
            'BSc in physics: no application required before enrollment; BEd with physics: application may be required depending on the other subject.'
            , "BSc in Physik: keine vorherige Bewerbung erforderlich; BEd mit Fach Physik: abh{$aUML}ngig vom anderen Fach kann eine Bewerbung erforderlich sein."
        ) )
      );
  
    open_tr();
      open_td( 'oneline', "15.08. - 10.11." );
      open_td(''
      ,  inlink( 'studiengaenge', array( 'text' => we(
            'Enrollment for a Master degree program in winter term'
          , "Einschreibung f{$uUML}r Master-Studieng{$aUML}nge zum Wintersemester" ) ) )
        . html_div( 'small', we( 'MSc in physics: application and admission is required before enrollment', 'MSc in Physik: Einschreibung nur nach vorheriger Bewerbung und Zulassung' ) )
        . html_div( 'small', we( 'MEd with physics: application is not required before enrollment', 'MEd mit Fach Physik: Einschreibung erfolgt ohne vorherige Bewerbung' ) )
        . html_div( 'small', html_alink( 'http://www.uni-potsdam.de/studium/studienangebot/masterstudium/master-a-z/astrophysics'
                                        , array( 'class' => 'href outlink', 'text' => $programme_text[ PROGRAMME_M_ASTRO ] ) )
                             . we(' (starting in winter term 2016/17 - ', ' (angeboten ab Wintersemester 2016/17 - ')
                             . html_alink( 'http://www.uni-potsdam.de/en/mnfakul/news/pre-registration.html', 'class=href outlink,text=preregistration' )
                             . ')'
          )
      );
  
  
  //   open_tr('td:/smallskipt/medskipt/');
  //     open_td('oneline', "12.06.2015 " );
  //     open_td(''
  //     , we( 'next '. html_alink( 'http://www.uni-potsdam.de/en/studium/data-storage/zielgruppenbereich/studieninteressierte/hochschulinformationstag.html'
  //                              , 'class=href outlink,text=Hochschulinformationstag' )
  //         , 'nächster '. html_alink( 'http://www.uni-potsdam.de/studium/data-storage/zielgruppenbereich/studieninteressierte/hochschulinformationstag.html'
  //                               , 'class=href outlink,text=Hochschulinformationstag' )
  //       )
  //     );
  
  close_table();
close_ccbox();



open_ccbox( '', we('Dates in Summer term 2017',"Termine im Sommersemester 2017") );

  echo html_tag('h3', 'medskipt', we('General dates in Summer Term 2017',"Allgemeine Termine im Sommersemester 2017") );
  
  open_table('td:smallskipt;smallskipb;quads');
  
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "10.04. - 13.04." );
      open_td('', we('Bridge courses and introductory events', "Vorkurse und Einf{$uUML}hrungsveranstaltungen" ));
  
  
    open_tr('td:/smallskipt/medskipt/'); 
      open_td( 'oneline', html_div( '', '03.04. - 10.05.' ) . html_div( 'bold red qpadl smaller', we( 'except 12.04.', "au{$SZLIG}er 12.04." ) ) );
      open_td( '', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Bachelor degree program','Belegen der Lehrveranstaltungen (Bachelorstudiengang)') ) );
      
    open_tr('td:/smallskipt/medskipt/');
      open_td( 'oneline', "12.04." );
      open_td( '', we('Begin of admission (no registration possible on this day)', "Beginn der Zulassung (keine Belegung an diesem Tag m{$oUML}glich)" ) );
  
  //  open_tr();
  //    open_td('oneline', "03.11." );
  //    open_td('', we('Deadline for cancelation of registration for courses in a Bachelor degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Bachelorstudiengang" ));
  
    open_tr('td:/smallskipt/medskipt/');
      open_td( 'oneline', html_div( '', '03.04. - 20.05.' ) . html_div( 'bold red qpadl smaller', we( 'except 12.04.', "au{$SZLIG}er 12.04." ) ) );
      open_td('', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Master degree program','Belegen der Lehrveranstaltungen (Masterstudiengang)') ) );
  
    // open_tr();
      // open_td('oneline', "20.11." );
      // open_td('', we('Deadline for cancelation of registration for courses in a Master degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Masterstudiengang" ));
      
      open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "18.04 - 28.07." );
      open_td('', we('Lecture period', "Vorlesungszeitraum" ) );
      
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "15.06. - 15.07." );
      open_td('', we('Period for re-registration for Winter term 2017/18', "R{$uUML}ckmeldung zum Wintersemester 2017/18" ) );
  	
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "27.07. - 05.08." );
      open_td('', we('Period for exams and Lab courses', "Zeitraum f{$uUML}r Pr{$uUML}fungen und Praktika" ) );
  
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "07.08. - 31.08." );
      open_td('', we('Summer break', "Sommerpause" ) );
  
  	open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "01.09. - 30.09." );
      open_td('', we('Period for exams and Lab courses', "Zeitraum f{$uUML}r Pr{$uUML}fungen und Praktika" ) );
  	
  close_table();
  
  
  
  echo html_tag('h3', 'medskipt', we('Exams in Summer Term 2017',"Pr{$uUML}fungstermine im Sommersemester 2017") );
  
  open_table('td:smallskipt;smallskipb;qquads;solidtop');
  
    open_tr();
      open_th( '', we( 'course', 'Veranstaltung' ) );
      open_th( 'qqpads', we( 'first examination date', "1. Pr{$uUML}fungstermin" ) );
      open_th( 'qqpads', we( 'second examination date', "2. Pr{$uUML}fungstermin" ) );
  
    open_tr();
      open_td();
        open_span( 'block', 'F. Feudel' );
        open_span( 'block', 'Mathematische Grundlagen' );
        open_span( 'block', 'BEd A111, PHY-111LAS' );
      open_td();
        open_span( 'block', '20.07.' );
        open_span( 'block', '14-16 Uhr' );
        open_span( 'block', '' );
      open_td();
        open_span( 'block', '27.09.' );
        open_span( 'block', '9.15-13 Uhr' );
        open_span( 'block', '2.28.2.123' );
  
    open_tr();
      open_td();
        open_span( 'block', 'M. Bargheer' );
        open_span( 'block', 'Experimentalphysik II' );
        open_span( 'block', 'BSc 201, PHY_201, BEd A201, PHY-201LAS' );
      open_td();
        open_span( 'block', '31.07.' );
        open_span( 'block', '10-12 Uhr' );
        open_span( 'block', '' );
      open_td();
        open_span( 'block', '18.09.' );
        open_span( 'block', '10-12 Uhr' );
        open_span( 'block', '' );
  
    open_tr();
      open_td();
        open_span( 'block', 'R. Metzler' );
        open_span( 'block', 'Theoretische Physik I - Mechanik' );
        open_span( 'block', 'BSc 211, PHY211, Nebenfach MAT211, IFGBW22, GEWBW22' );
      open_td();
        open_span( 'block', '28.07.' );
        open_span( 'block', '' );
        open_span( 'block', '' );
      open_td();
        open_span( 'block', '28.09.' );
        open_span( 'block', '' );
        open_span( 'block', '' );
  
    open_tr();
      open_td();
        open_span( 'block', 'M. Wilkens' );
        open_span( 'block', 'Theoretische Physik III - Quantenmechanik I' );
        open_span( 'block', 'BSc 411' );
      open_td();
        open_span( 'block', '03.08.' );
        open_span( 'block', '10.00 Uhr' );
        open_span( 'block', '2.28.0.108' );
      open_td();
        open_span( 'block', '28.09.' );
        open_span( 'block', '10.00 Uhr' );
        open_span( 'block', '2.28.2.080' );
  
    open_tr();
      open_td();
        open_span( 'block', 'M. Wilkens' );
        open_span( 'block', 'Theoretische Physik III Lehramt' );
        open_span( 'block', 'MEd Physik, Modul A711' );
      open_td();
        open_span( 'block', '03.08.' );
        open_span( 'block', '10.00 Uhr' );
        open_span( 'block', '2.28.0.108' );
      open_td();
        open_span( 'block', '' );
        open_span( 'block', '' );
        open_span( 'block', '' );
  
  close_table();
  
//   open_div( 'smallskips'
//   , we(   '(more dates will be published as soon as they are available)'
//         , "(weitere Termine werden hier ver{$oUML}ffentlicht, sobald sie feststehen)" )
//   );

close_ccbox();


open_ccbox( '', we('Dates in Winter Term 2017/18',"Termine im Wintersemester 2017/18") );

  echo html_tag('h3', 'medskipt', we('General dates in Winter Term 2017/18',"Allgemeine Termine im Wintersemester 2017/18") );
  
  open_table('td:smallskipt;smallskipb;quads');
  
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "04.10. - 13.10." );
      open_td('', we('Bridge courses and introductory events', "Vorkurse und Einf{$uUML}hrungsveranstaltungen" ));
  
    open_tr('td:/smallskipt/medskipt/');
      open_td( 'oneline', html_div( '', '04.10. - 10.11.' ) . html_div( 'bold red qpadl smaller', we( 'except 12.10.', "au{$SZLIG}er 12.10." ) ) );
      open_td( '', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Bachelor degree program','Belegen der Lehrveranstaltungen (Bachelorstudiengang)') ) );
      
    open_tr('td:/smallskipt/medskipt/');
      open_td( 'oneline', "12.10." );
      open_td( '', we('Begin of admission (no registration possible on this day)', "Beginn der Zulassung (keine Belegung an diesem Tag m{$oUML}glich)" ) );
  
  //  open_tr();
  //    open_td('oneline', "03.11." );
  //    open_td('', we('Deadline for cancelation of registration for courses in a Bachelor degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Bachelorstudiengang" ));
  
    open_tr('td:/smallskipt/medskipt/');
      open_td( 'oneline', html_div( '', '04.10. - 20.11.' ) . html_div( 'bold red qpadl smaller', we( 'except 12.10.', "au{$SZLIG}er 12.10." ) ) );
      open_td('', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Master degree program','Belegen der Lehrveranstaltungen (Masterstudiengang)') ) );
  
  //  open_tr();
  //    open_td('oneline', "20.11." );
  //    open_td('', we('Deadline for cancelation of registration for courses in a Master degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Masterstudiengang" ));
      
      open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "16.10. - 09.02." );
      open_td('', we('Lecture period', "Vorlesungszeitraum" ) );
      
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "15.01. - 15.02." );
      open_td('', we('Period for re-registration for Summer term 2017', "R{$uUML}ckmeldung zum Sommersemester 2018" ) );
  
  
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "13.02. - 31.03." );
      open_td('', we('Period for exams and Lab courses', "Zeitraum f{$uUML}r Pr{$uUML}fungen und Praktika" ) );
  
  close_table();
  
  
  
  
  
  echo html_tag('h3', 'medskipt', we('Exams in Winter Term 2017/18',"Pr{$uUML}fungstermine im Wintersemester 2017/18") );
  // 
  
  
  // open_div( 'smallskips'
  // , we(   'More dates will be published here as soon as they are available.'
  //       , "Weitere Termine werden hier ver{$oUML}ffentlicht, sobald sie feststehen." )
  // );
  open_div( 'smallskips'
  , we(   '(exam dates will be published here as soon as they are available.)'
        , "(Termine werden hier ver{$oUML}ffentlicht, sobald sie feststehen)" )
  );

close_ccbox();

?>
