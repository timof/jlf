<?php

echo html_tag('h1', '', we('Important Dates for Physics Students',"Wichtige Termine f{$uUML}r Physikstudierende") );


echo html_tag('h2', 'medskipt', we('Dates for prospective students',"Termine f{$uUML}r Studieninteressierte") );

open_table('td:smallskipt;smallskipb;quads');

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
    );



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
      . html_div( 'small', we( 'MSc in physics: application and admission is required before enrollment', 'MSc in Physik: Einschreibung nur nach vorheriger Bewerbung und Zulassung' ) )
      . html_div( 'small', we( 'MEd with physics: application is not required before enrollment', 'MEd mit Fach Physik: Einschreibung erfolgt ohne vorherige Bewerbung' ) )
    );


  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "12.06.2015 " );
    open_td(''
    , we( 'next '. html_alink( 'http://www.uni-potsdam.de/en/studium/data-storage/zielgruppenbereich/studieninteressierte/hochschulinformationstag.html'
                             , 'class=href outlink,text=Hochschulinformationstag' )
        , 'nächster '. html_alink( 'http://www.uni-potsdam.de/studium/data-storage/zielgruppenbereich/studieninteressierte/hochschulinformationstag.html'
                              , 'class=href outlink,text=Hochschulinformationstag' )
      )
    );

close_table();

echo html_tag('h2', '', we('Dates in Winter Term 2014/15',"Termine im Wintersemester 2014/15") );

echo html_tag('h3', 'medskipt', we('General dates in Winter term',"Allgemeine Termine im Wintersemester") );

open_table('td:smallskipt;smallskipb;quads');

  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "01.10. - 10.10." );
    open_td('', we('Bridge courses and introductory events', "Vorkurse und Einf{$uUML}hrungsveranstaltungen" ));


  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', html_div( '', '01.10. - 10.11.' ) . html_div( 'bold red qpadl smaller', we( 'except 10.10.', "au{$SZLIG}er 10.10." ) ) );
    open_td( '', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Bachelor degree program','Belegen der Lehrveranstaltungen (Bachelorstudiengang)') ) );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', "10.10." );
    open_td( '', we('Begin of admission (no registration possible on this day)', "Beginn der Zulassung (keine Belegung an diesem Tag m{$oUML}glich)" ) );

//  open_tr();
//    open_td('oneline', "03.11." );
//    open_td('', we('Deadline for cancelation of registration for courses in a Bachelor degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Bachelorstudiengang" ));

  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', html_div( '', '01.10. - 20.11.' ) . html_div( 'bold red qpadl smaller', we( 'except 10.10.', "au{$SZLIG}er 10.10." ) ) );

    open_td('', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Master degree program','Belegen der Lehrveranstaltungen (Masterstudiengang)') ) );

  open_tr();
    open_td('oneline', "20.11." );
    open_td('', we('Deadline for cancelation of registration for courses in a Master degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Masterstudiengang" ));
    
    open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "13.10 - 06.02." );
    open_td('', we('Lecture period', "Vorlesungszeitraum" ) );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "15.01 - 15.02." );
    open_td('', we('Period for re-registration for Summer term 2014/15', "R{$uUML}ckmeldung zum Sommersemester 2015" ) );


  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "09.02. - 31.03." );
    open_td('', we('Period for exams and Lab courses', "Zeitraum f{$uUML}r Pr{$uUML}fungen und Praktika" ) );

close_table();





echo html_tag('h3', 'medskipt', we('Exams in Winter Term 2014/15',"Pr{$uUML}fungstermine im Wintersemester 2014/15") );

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
      open_span( 'block', '12.02.2015' );
      open_span( 'block', '10.15 - 12.15 Uhr' );
      open_span( 'block', '2.27.001, 2.27.101' );
    open_td();
      open_span( 'block', '17.03.2015' );
      open_span( 'block', '10.15 - 12.15 Uhr' );
      open_span( 'block', '2.27.001' );

  open_tr();
    open_td();
      open_div( '', 'J. Metzger' );
      open_div( '', 'Mathematik für Physiker I' );
      open_div( 'block', 'BSc 121' );
    open_td();
      open_div('', '13.02.2015' );
      open_div('', '10.15 - 12.15 Uhr' );
    open_td();
      open_div('', '20.03.2015' );
      open_div('', '10.15 - 12.15 Uhr' );

//   open_tr();
//     open_td();
//       open_div( '', 'F. Feudel' );
//       open_div( '', 'Mathematische Methoden für Physiker' );
//       open_div( 'block', 'BSc 111' );
//     open_td();
//       open_div('', '' );
//       open_div('', '' );
//       open_div('', '' );
//     open_td();
//       open_div('', '03.04.2014' );
//       open_div('', '09.00 Uhr' );
//       open_div('', '2.28.2.123' );
// 
//   open_tr();
//     open_td();
//       open_div('','M. Wilkens' );
//       open_div('', 'Math. Methoden (LA)' );
//       open_div('', 'BEd A111, PHY-111LAS' );
//     open_td();
//       open_div('', '18.02.2014' );
//       open_div('', '10-12 Uhr' );
//       open_div('', '2.28.0.108' );
//     open_td();
//       open_div('', '01.04.2014' );
//       open_div('', '10-12 Uhr' );
// 
  open_tr();
    open_td();
      open_div('', 'M. Bargheer' );
      open_div('', 'Experimentalphysik III' );
      open_div('', 'BSc 301' );
      open_div('', 'BEd A301, 381, PHYS-301LAS' );
    open_td();
      open_div('', '18.02.2015' );
      open_div('', '10.00 Uhr' );
    open_td();
      open_div('', '25.03.2015' );
      open_div('', '10.00 Uhr' );

  open_tr();
    open_td();
      open_div( '', 'A. Feldmeier' );
      open_div( '', 'Theoretische Physik II' );
      open_div( '', 'Elektrodynamik und Relativität' );
      open_div( 'block', 'BSc 311' );
    open_td();
      open_div('', '10.02.2015' );
      open_div('', '10.00 - 12.00 Uhr' );
//      open_div('', '2.28.0.108' );
    open_td();
      open_div('', '08.04.2015' );
      open_div('', '10.00 - 12.00 Uhr' );
//      open_div('', '2.28.0.108' );

  open_tr();
    open_td();
      open_div( '', 'S. Paycha' );
      open_div( '', 'Mathematik für Physiker III' );
      open_div( 'block', 'BSc 321' );
    open_td();
      open_div('', '06.02.2015' );
      open_div('', '9 - 11 Uhr' );
    open_td();
      open_div('', '10.04.2015' );
      open_div('', '9 - 11 Uhr' );

//   open_tr();
//     open_td();
//       open_div( '', 'F. Feudel' );
//       open_div( '', 'Theoretische Physik III (LA und NF)' );
//       open_div( 'block', 'BEd 383' );
//     open_td();
//       open_div('', '' );
//       open_div('', '' );
//       open_div('', '' );
//     open_td();
//       open_div('', '04.04.2014' );
//       open_div('', '14.00 Uhr' );
//       open_div('', '2.28.2.123' );
// 
//   open_tr();
//     open_td();
//       open_div('', 'U. Magdans' );
//       open_div('', 'Physikalische Schulexperimente II' );
//       open_div('', 'BEd A581' );
//       open_div('', 'MEd 194' );
//     open_td('colspan=2');
//       open_div('', '17.02., 18.02. und 19.02.2014' );
//       open_div('', '10-12 und 13-15 Uhr' );
//       open_div('', '(Präsentationen, je 20min)' );
// 
//   open_tr();
//     open_td();
//       open_div('','M. Wilkens' );
//       open_div('', 'Quantenmechanik II' );
//       open_div('', 'MSc 711' );
//     open_td();
//       open_div('', '19.02.2014' );
//       open_div('', '10 - 12 Uhr' );
//       open_div('', '2.28.0.108' );
//     open_td();
//       open_div('', '02.04.2014' );
//       open_div('', '10 - 12 Uhr' );
//     
close_table();

open_div( 'smallskips'
, we( 'More dates will be published on this page when available.', "Weitere Termine werden demn{$aUML}chst auf dieser Seite ver{$oUML}ffentlicht.")
);



echo html_tag('h2', '', we('Dates in Summer Term 2015',"Termine im Sommersemester 2015") );

echo html_tag('h3', 'medskipt', we('General dates in Summer term',"Allgemeine Termine im Sommersemester") );

open_table('td:smallskipt;smallskipb;quads');

  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "06.04. - 10.04." );
    open_td('', we('Bridge courses and introductory events', "Vorkurse und Einf{$uUML}hrungsveranstaltungen" ));


  open_tr('td:/smallskipt/medskipt/'); 
    open_td( 'oneline', html_div( '', '01.04. - 10.05.' ) . html_div( 'bold red qpadl smaller', we( 'except 10.10.', "au{$SZLIG}er 10.04." ) ) );
    open_td( '', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Bachelor degree program','Belegen der Lehrveranstaltungen (Bachelorstudiengang)') ) );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', "10.04." );
    open_td( '', we('Begin of admission (no registration possible on this day)', "Beginn der Zulassung (keine Belegung an diesem Tag m{$oUML}glich)" ) );

//  open_tr();
//    open_td('oneline', "03.11." );
//    open_td('', we('Deadline for cancelation of registration for courses in a Bachelor degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Bachelorstudiengang" ));

  open_tr('td:/smallskipt/medskipt/');
    open_td( 'oneline', html_div( '', '01.04. - 20.05.' ) . html_div( 'bold red qpadl smaller', we( 'except 10.04.', "au{$SZLIG}er 10.04." ) ) );
    open_td('', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Master degree program','Belegen der Lehrveranstaltungen (Masterstudiengang)') ) );

  // open_tr();
    // open_td('oneline', "20.11." );
    // open_td('', we('Deadline for cancelation of registration for courses in a Master degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Masterstudiengang" ));
    
    open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "13.04 - 25.07." );
    open_td('', we('Lecture period', "Vorlesungszeitraum" ) );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "15.06 - 15.07." );
    open_td('', we('Period for re-registration for Winter term 2015/16', "R{$uUML}ckmeldung zum Wintersemester 2015/16" ) );

  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "1.08. - 31.08." );
    open_td('', we('Summer break', "Sommerpause" ) );

	  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "01.09. - 30.09." );
    open_td('', we('Period for exams and Lab courses', "Zeitraum f{$uUML}r Pr{$uUML}fungen und Praktika" ) );
	
close_table();


echo html_tag('h3', 'medskipt', we('Exams in Summer Term 2015',"Pr{$uUML}fungstermine im Sommersemester 2015") );

// open_div( 'smallskips'
// , we(   'Usually, for each module two examination dates will be offered: a first examination date between July 14 and August 8, and a second examination date between August 15 and September 15.'
//       , "Typischerweise wird zu jedem Modul jeweils ein erster Pr{$uUML}fungstermin im Zeitraum 14.07 bis 08.08. und ein zweiter Pr{$uUML}fungstermin im Zeitraum 15.08. bis 15.09. angeboten." )
// );
// open_div( 'smallskips'
// , we(   'Registration for an examination, as well as cancelation of a registration, will be possible up to 8 days before the respective examination date.'
//       , "Anmeldung zu und R{$uUML}cktritt von Pr{$uUML}fungen ist jeweils bis 8 Tage vor dem jeweiligen Pr{$uUML}fungstermin m{$oUML}glich." )
// );

open_div( 'smallskips'
, we( 'Dates will be published on this page when available.', "Pr{$uUML}fungstermine werden auf dieser Seite ver{$oUML}ffentlicht, sobald sie feststehen.")
);
?>
