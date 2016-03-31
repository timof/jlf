<?php

echo html_tag('h1', '', we('Important Dates for Physics Students',"Wichtige Termine f{$uUML}r Physikstudierende") );


echo html_tag('h2', 'medskipt', we('Dates for prospective students',"Termine f{$uUML}r Studieninteressierte") );

open_table('td:smallskipt;smallskipb;quads');

  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "15.03. " );
    open_td(''
    ,  we('Deadline for ', "Frist f{$uUML}r ")
       . inlink( 'einschreibung', array( 'text' => we( 'application for admission to the Master of Science (MSc) degree program in summer term'
                                                      ,"Bewerbung um Zulassung zum Studiengang Master of Science (MSc) im Sommersemester" ) ) )
    ); 

  open_tr();
    open_td( 'oneline', "15.02. - 15.03." );
    open_td(''
    ,  inlink( 'einschreibung', array( 'text' => we(
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
    ,  inlink( 'einschreibung', array( 'text' => we(
          'Enrollment for a Master degree program in summer term'
        , "Einschreibung f{$uUML}r Master-Studieng{$aUML}nge zum Sommersemester" ) ) )
      . html_div( 'small', $programme_text[ PROGRAMME_MSC ] . we( ': application and admission is required before enrollment', ': Einschreibung nur nach vorheriger Bewerbung und Zulassung' ) )
      . html_div( 'small', $programme_text[ PROGRAMME_MED ] . we( ': application is not required before enrollment', ': Einschreibung erfolgt ohne vorherige Bewerbung' ) )
    );



  open_tr();
    open_td('oneline', "15.09. " );
    open_td(''
    ,  we('Deadline for ', "Frist f{$uUML}r ")
       . inlink( 'einschreibung', array( 'text' => we( 'application for admission to the Master of Science (MSc) degree program in winter term'
                                                      ,"Bewerbung um Zulassung zum Studiengang Master of Science (MSc) im Wintersemester" ) ) )
    ); 

  open_tr();
    open_td( 'oneline', "15.08. - 15.09." );
    open_td(''
    ,  inlink( 'einschreibung', array( 'text' => we( 'Enrollment for a Bachelor degree program in winter term', "Einschreibung f{$uUML}r Bachelor-Studieng{$aUML}nge zum Wintersemester" ) ) )
      . html_div( 'small', we(
          'BSc in physics: no application required before enrollment; BEd with physics: application may be required depending on the other subject.'
          , "BSc in Physik: keine vorherige Bewerbung erforderlich; BEd mit Fach Physik: abh{$aUML}ngig vom anderen Fach kann eine Bewerbung erforderlich sein."
      ) )
    );

  open_tr();
    open_td( 'oneline', "15.08. - 10.11." );
    open_td(''
    ,  inlink( 'einschreibung', array( 'text' => we(
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





echo html_tag('h2', '', we('Dates in Winter Term 2015/16',"Termine im Wintersemester 2015/16") );

echo html_tag('h3', 'medskipt', we('General dates in Winter Term 2015/16',"Allgemeine Termine im Wintersemester 2015/16") );

open_table('td:smallskipt;smallskipb;quads');

  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "01.10. - 10.10." );
    open_td('', we('Bridge courses and introductory events', "Vorkurse und Einf{$uUML}hrungsveranstaltungen" ));


  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', html_div( '', '01.10. - 10.11.' ) . html_div( 'bold red qpadl smaller', we( 'except 08.10.', "au{$SZLIG}er 08.10." ) ) );
    open_td( '', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Bachelor degree program','Belegen der Lehrveranstaltungen (Bachelorstudiengang)') ) );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', "08.10." );
    open_td( '', we('Begin of admission (no registration possible on this day)', "Beginn der Zulassung (keine Belegung an diesem Tag m{$oUML}glich)" ) );

//  open_tr();
//    open_td('oneline', "03.11." );
//    open_td('', we('Deadline for cancelation of registration for courses in a Bachelor degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Bachelorstudiengang" ));

  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', html_div( '', '01.10. - 20.11.' ) . html_div( 'bold red qpadl smaller', we( 'except 08.10.', "au{$SZLIG}er 08.10." ) ) );
    open_td('', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Master degree program','Belegen der Lehrveranstaltungen (Masterstudiengang)') ) );

//  open_tr();
//    open_td('oneline', "20.11." );
//    open_td('', we('Deadline for cancelation of registration for courses in a Master degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Masterstudiengang" ));
    
    open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "12.10. - 06.02." );
    open_td('', we('Lecture period', "Vorlesungszeitraum" ) );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "15.01. - 15.02." );
    open_td('', we('Period for re-registration for Summer term 2016', "R{$uUML}ckmeldung zum Sommersemester 2016" ) );


  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "08.02. - 31.03." );
    open_td('', we('Period for exams and Lab courses', "Zeitraum f{$uUML}r Pr{$uUML}fungen und Praktika" ) );

close_table();





echo html_tag('h3', 'medskipt', we('Exams in Winter Term 2015/16',"Pr{$uUML}fungstermine im Wintersemester 2015/16") );
// 
open_table('td:smallskipt;smallskipb;qquads;solidtop');

  open_tr();
    open_th( '', we( 'course', 'Veranstaltung' ) );
    open_th( 'qqpads', we( 'first examination date', "1. Pr{$uUML}fungstermin" ) );
    open_th( 'qqpads', we( 'second examination date', "2. Pr{$uUML}fungstermin" ) );

  open_tr();
    open_td();
      open_span( 'block', 'R. Gerhard' );
      open_span( 'block', 'Experimentalphysik I' );
      open_span( 'block', 'BSc 101, BEd PHYS-101LAS' );
    open_td();
      open_span( 'block', '11.02.2016' );
      open_span( 'block', '10.00 Uhr' );
      open_span( 'block', '2.27.001, 2.27.101' );
    open_td();
      open_span( 'block', '15.03.2016' );
      open_span( 'block', '10.00 Uhr' );
      open_span( 'block', '2.27.001' );

  open_tr();
    open_td();
      open_span( 'block', 'A. Feldmeier' );
      open_span( 'block', 'Theoretische Physik II - Elektrodynamik und Relativität' );
      open_span( 'block', 'BSc 311, MAT311' );
    open_td();
      open_span( 'block', '09.02.2016' );
      open_span( 'block', '10 - 12 Uhr' );
      open_span( 'block', '' );
    open_td();
      open_span( 'block', '05.04.2016' );
      open_span( 'block', '10 - 12 Uhr' );
      open_span( 'block', '2.28.0.108' );

  open_tr();
    open_td();
      open_span( 'block', 'M. Bargheer' );
      open_span( 'block', 'Experimentalphysik III' );
      open_span( 'block', 'BSc 301, BEd A301, PHY-301LAS, MAT301, IFGBW02' );
    open_td();
      open_span( 'block', '17.02.2016' );
      open_span( 'block', '10 - 12 Uhr' );
      open_span( 'block', '' );
    open_td();
      open_span( 'block', '22.03.2016' );
      open_span( 'block', '10 - 12 Uhr' );
      open_span( 'block', '' );

  open_tr();
    open_td();
      open_span( 'block', 'J. Metzger' );
      open_span( 'block', 'Mathematik für Physiker III' );
      open_span( 'block', 'BSc 321' );
    open_td();
      open_span( 'block', '23.02.2016' );
      open_span( 'block', '10 - 12 Uhr' );
      open_span( 'block', '' );
    open_td();
      open_span( 'block', '29.03.2016' );
      open_span( 'block', '10 - 12 Uhr' );
      open_span( 'block', '' );

  open_tr();
    open_td();
      open_span( 'block', 'R. Metzler' );
        open_span( 'block', 'Theoretische Physik IV - Statistische Physik und Thermodynamik' );
      open_span( 'block', 'BSc 511, MAT511' );
    open_td();
      open_span( 'block', '16.02.2016' );
      open_span( 'block', '10 - 12 Uhr' );
      open_span( 'block', '2.28.0.104' );
    open_td();
      open_span( 'block bold red', '07.04.2016' );
      open_span( 'block', '10 - 12 Uhr' );
      open_span( 'block', '' );

  open_tr();
    open_td();
      open_span( 'block', 'M. Wilkens' );
        open_span( 'block', 'Höhere Theoretische Physik - Quantenmechanik II' );
      open_span( 'block', 'MSc 711' );
    open_td();
      open_span( 'block', '16.02.2016' );
      open_span( 'block', '10 Uhr' );
      open_span( 'block', '2.28.0.102' );
    open_td();
      open_span( 'block', '05.04.2016' );
      open_span( 'block', '10 Uhr' );
      open_span( 'block bold red', '2.28.0.108' );

close_table();
// open_div( 'smallskips'
// , we(   'More exam dates will be published here as soon as they are available.'
//       , "Weitere Termine werden hier ver{$oUML}ffentlicht, sobald sie feststehen." )
// , we(   'Exam dates will be published here as soon as they are available.'
//      , "Termine werden hier ver{$oUML}ffentlicht, sobald sie feststehen." )
// );






echo html_tag('h2', '', we('Dates in Summer term 2016',"Termine im Sommersemester 2016") );

echo html_tag('h3', 'medskipt', we('General dates in Summer Term 2016',"Allgemeine Termine im Sommersemester 2016") );

open_table('td:smallskipt;smallskipb;quads');

  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "04.04. - 08.04." );
    open_td('', we('Bridge courses and introductory events', "Vorkurse und Einf{$uUML}hrungsveranstaltungen" ));


  open_tr('td:/smallskipt/medskipt/'); 
    open_td( 'oneline', html_div( '', '01.04. - 10.05.' ) . html_div( 'bold red qpadl smaller', we( 'except 07.04.', "au{$SZLIG}er 07.04." ) ) );
    open_td( '', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Bachelor degree program','Belegen der Lehrveranstaltungen (Bachelorstudiengang)') ) );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', "07.04." );
    open_td( '', we('Begin of admission (no registration possible on this day)', "Beginn der Zulassung (keine Belegung an diesem Tag m{$oUML}glich)" ) );

//  open_tr();
//    open_td('oneline', "03.11." );
//    open_td('', we('Deadline for cancelation of registration for courses in a Bachelor degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Bachelorstudiengang" ));

  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', html_div( '', '01.04. - 20.05.' ) . html_div( 'bold red qpadl smaller', we( 'except 07.04.', "au{$SZLIG}er 07.04." ) ) );
    open_td('', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Master degree program','Belegen der Lehrveranstaltungen (Masterstudiengang)') ) );

  // open_tr();
    // open_td('oneline', "20.11." );
    // open_td('', we('Deadline for cancelation of registration for courses in a Master degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Masterstudiengang" ));
    
    open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "11.04 - 23.07." );
    open_td('', we('Lecture period', "Vorlesungszeitraum" ) );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "15.06. - 15.07." );
    open_td('', we('Period for re-registration for Winter term 2015/16', "R{$uUML}ckmeldung zum Wintersemester 2015/16" ) );

  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "01.08. - 31.08." );
    open_td('', we('Summer break', "Sommerpause" ) );

	  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "01.09. - 30.09." );
    open_td('', we('Period for exams and Lab courses', "Zeitraum f{$uUML}r Pr{$uUML}fungen und Praktika" ) );
	
close_table();



echo html_tag('h3', 'medskipt', we('Exams in Summer Term 2016',"Pr{$uUML}fungstermine im Sommersemester 2016") );

open_table('td:smallskipt;smallskipb;qquads;solidtop');

  open_tr();
    open_th( '', we( 'course', 'Veranstaltung' ) );
    open_th( 'qqpads', we( 'first examination date', "1. Pr{$uUML}fungstermin" ) );
    open_th( 'qqpads', we( 'second examination date', "2. Pr{$uUML}fungstermin" ) );


  open_tr();
    open_td();
      open_span( 'block', 'M. Wilkens' );
        open_span( 'block', 'Theoretische Physik III - Quantenmechanik I' );
      open_span( 'block', 'BSc 411' );
    open_td();
      open_span( 'block', '28.07.2016' );
      open_span( 'block', '10 Uhr' );
      open_span( 'block', '2.28.0.108' );
    open_td();
      open_span( 'block', '' );
      open_span( 'block', '' );
      open_span( 'block', '' );

close_table();

open_div( 'smallskips'
, we(   'More exam dates will be published here as soon as they are available.'
      , "Weitere Termine werden hier ver{$oUML}ffentlicht, sobald sie feststehen." )
);



echo html_tag('h2', '', we('Dates in Winter Term 2016/17',"Termine im Wintersemester 2016/17") );

echo html_tag('h3', 'medskipt', we('General dates in Winter Term 2016/17',"Allgemeine Termine im Wintersemester 2016/17") );

open_table('td:smallskipt;smallskipb;quads');

  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "04.10. - 15.10." );
    open_td('', we('Bridge courses and introductory events', "Vorkurse und Einf{$uUML}hrungsveranstaltungen" ));


  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', html_div( '', '04.10. - 10.11.' ) . html_div( 'bold red qpadl smaller', we( 'except 13.10.', "au{$SZLIG}er 13.10." ) ) );
    open_td( '', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Bachelor degree program','Belegen der Lehrveranstaltungen (Bachelorstudiengang)') ) );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', "13.10." );
    open_td( '', we('Begin of admission (no registration possible on this day)', "Beginn der Zulassung (keine Belegung an diesem Tag m{$oUML}glich)" ) );

//  open_tr();
//    open_td('oneline', "03.11." );
//    open_td('', we('Deadline for cancelation of registration for courses in a Bachelor degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Bachelorstudiengang" ));

  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', html_div( '', '04.10. - 20.11.' ) . html_div( 'bold red qpadl smaller', we( 'except 13.10.', "au{$SZLIG}er 13.10." ) ) );
    open_td('', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Master degree program','Belegen der Lehrveranstaltungen (Masterstudiengang)') ) );

//  open_tr();
//    open_td('oneline', "20.11." );
//    open_td('', we('Deadline for cancelation of registration for courses in a Master degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Masterstudiengang" ));
    
    open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "17.10. - 10.02." );
    open_td('', we('Lecture period', "Vorlesungszeitraum" ) );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "15.01. - 15.02." );
    open_td('', we('Period for re-registration for Summer term 2017', "R{$uUML}ckmeldung zum Sommersemester 2017" ) );


  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "13.02. - 31.03." );
    open_td('', we('Period for exams and Lab courses', "Zeitraum f{$uUML}r Pr{$uUML}fungen und Praktika" ) );

close_table();





echo html_tag('h3', 'medskipt', we('Exams in Winter Term 2016/17',"Pr{$uUML}fungstermine im Wintersemester 2016/17") );
// 


open_div( 'smallskips'
, we(   'Exam dates will be published here as soon as they are available.'
      , "Termine werden hier ver{$oUML}ffentlicht, sobald sie feststehen." )
);

?>
