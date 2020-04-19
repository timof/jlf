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
      open_td('oneline', "01.03. " );
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
      open_td('oneline', "15.09." );
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
//                             . we(' (starting in winter term 2016/17 - ', ' (angeboten ab Wintersemester 2016/17 - ')
//                             . html_alink( 'http://www.uni-potsdam.de/en/mnfakul/news/pre-registration.html', 'class=href outlink,text=preregistration' )
//                             . ')'
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





open_ccbox( '', we('Dates in Winter Term 2019/20',"Termine im Wintersemester 2019/20") );

  echo html_tag('h3', 'medskipt', we('Exams in Winter Term 2019/20',"Pr{$uUML}fungstermine im Wintersemester 2019/20") );
  // 
   open_div('large bold,style=padding:2em;background-color:#ffff88;color:#ff0000;margin-bottom:2em;');
     open_div('smallskips', '
       Klausuren dürfen ab 24.03. bis auf weiteres nicht abgehalten werden.
       Für noch ausstehende Prüfungen werden zeitnahe Ersatztermine 
       angeboten, sobald dies wieder möglich ist.
     ');
     open_div('smallskips', '
       Mündliche Prüfungen können nach Absprache im gegenseitigen Einvernehmen
       durchgeführt werden, ggf. unter Einsatz alternativer
       Kommunikationswege.
     ');
     open_div('smallskips', we(
        'Information on the consequences of the corona crisis on studies and teaching: '
      , 'Informationen zu den Auswirkungen der Corona-Krise auf Studium und Lehre: '
      ) . html_alink( 'https://www.uni-potsdam.de/studium/corona', 'class=href outlink large,text=https://www.uni-potsdam.de/studium/corona' )
    );
   close_div();
  
close_ccbox();


open_ccbox( '', we('Dates in Summer term 2020',"Termine im Sommersemester 2020") );

  open_div('large bold,style=padding:2em;background-color:#ffff88;color:#ff0000;margin-bottom:2em;'
  , 'Die aktuelle Corona-Epidemie wird Auswirkungen auf den Lehrbetrieb im Sommersemester 2020
     haben, insbesondere beginnen die Lehrveranstaltungen erst am 20.April.
     Bis auf weiteres sind keine Präsenzveranstaltungen möglich,
     bitte informieren Sie sich auf den Webseiten der jeweiligen Veranstaltung über
     die alternativen Formate!
     '
  );

  echo html_tag('h3', 'medskipt', we('General dates in Summer Term 2020',"Allgemeine Termine im Sommersemester 2020") );
  
  open_table('td:smallskipt;smallskipb;quads');
  
//     open_tr('td:/smallskipt/medskipt/');
//       open_td('oneline', "03.04. - 06.04." );
//       open_td('', we('Bridge courses and introductory events', "Vorkurse und Einf{$uUML}hrungsveranstaltungen" ));
  
  
    open_tr('td:/smallskipt/medskipt/'); 
      open_td( 'oneline', html_div( '', html_span('red bold','20.04.') . ' - 10.05.' ) ); // . html_div( 'bold red qpadl smaller', we( 'except 08.04.', "au{$SZLIG}er 08.04." ) ) );
      open_td( '', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses','Belegen der Lehrveranstaltungen') ) );
      
    open_tr('td:/smallskipt/medskipt/');
      open_td( 'oneline red bold', "24.04., 14 Uhr" );
      open_td( '', we('Start of admission (no registration possible)', "Beginn der Zulassung (keine Belegung m{$oUML}glich)" ) );
  
  //  open_tr();
  //    open_td('oneline', "03.11." );
  //    open_td('', we('Deadline for cancelation of registration for courses in a Bachelor degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Bachelorstudiengang" ));
  
//     open_tr('td:/smallskipt/medskipt/');
//       open_td( 'oneline', html_div( '', '01.04. - 20.05.' ) . html_div( 'bold red qpadl smaller', we( 'except 04.04.', "au{$SZLIG}er 04.04." ) ) );
//       open_td('', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Master degree program','Belegen der Lehrveranstaltungen (Masterstudiengang)') ) );
  
    // open_tr();
      // open_td('oneline', "20.11." );
      // open_td('', we('Deadline for cancelation of registration for courses in a Master degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Masterstudiengang" ));
      
      open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', html_span('red bold', '20.04' ) . ' - 24.07.' );
      open_td('', we('Lecture period', "Vorlesungszeitraum" ) );
      
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "15.06. - 15.07." );
      open_td('', we('Period for re-registration for Winter term 2020/21', "R{$uUML}ckmeldung zum Wintersemester 2020/21" ) );
  	
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "27.07. - 31.07." );
      open_td('', we('Period for exams and Lab courses', "Zeitraum f{$uUML}r Pr{$uUML}fungen und Praktika" ) );
  
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "01.08. - 31.08." );
      open_td('', we('Summer break', "Sommerpause" ) );
  
  	open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "01.09. - 30.09." );
      open_td('', we('Period for exams and Lab courses', "Zeitraum f{$uUML}r Pr{$uUML}fungen und Praktika" ) );
  	
  close_table();
  
  
  
  echo html_tag('h3', 'medskipt', we('Exams in Summer Term 2020',"Pr{$uUML}fungstermine im Sommersemester 2020") );
  
  open_table('td:smallskipt;smallskipb;qquads;solidtop');
  
    open_tr();
      open_th( '', we( 'course', 'Veranstaltung' ) );
      open_th( 'qqpads', we( 'first examination date', "1. Pr{$uUML}fungstermin" ) );
      open_th( 'qqpads', we( 'second examination date', "2. Pr{$uUML}fungstermin" ) );
  
    open_tr();
      open_td();
        open_span( 'block', 'M. Wilkens' );
        open_span( 'block', 'Mathematische Grundlagen (Lehramt)' );
        open_span( 'block', 'BEd A111, PHY-111LAS' );
       open_td();
         open_span( 'block', '30.07.' );
         open_span( 'block', '10.00 Uhr' );
         open_span( 'block', '2.27.0.01' );
       open_td();
         open_span( 'block', 'tba' );
         open_span( 'block', '' );
         open_span( 'block', '' );
//   
//     open_tr();
//       open_td();
//         open_span( 'block', 'M. Bargheer' );
//         open_span( 'block', 'Experimentalphysik II' );
//         open_span( 'block', 'BSc 201, PHY_201, BEd A201, PHY-201LAS' );
//       open_td();
//         open_span( 'block', '30.07.' );
//         open_span( 'block', '10-12 Uhr' );
//         open_span( 'block', '' );
//       open_td();
//         open_span( 'block', '24.09.' );
//         open_span( 'block', '10-12 Uhr' );
//         open_span( 'block', '' );
//   
    open_tr();
      open_td();
        open_span( 'block', 'A. Feldmeier' );
        open_span( 'block', 'Theoretische Physik I - Mechanik' );
        open_span( 'block', 'BSc 211, PHY211, Nebenfach MAT211, IFGBW22, GEWBW22' );
      open_td();
        open_span( 'block', '29.07.' );
        open_span( 'block', '10.00 Uhr' );
        open_span( 'block', '2.25.F0.01' );
      open_td();
        open_span( 'block', '28.10.' );
        open_span( 'block', '' );
        open_span( 'block', '' );
  
     open_tr();
       open_td();
         open_span( 'block', 'M. Wilkens' );
         open_span( 'block', 'Theoretische Physik III - Quantenmechanik I' );
         open_span( 'block', 'BSc 411' );
       open_td();
         open_span( 'block', '30.07.' );
         open_span( 'block', '10.00 Uhr' );
         open_span( 'block', '2.27.0.01' );
       open_td();
         open_span( 'block', 'tba' );
         open_span( 'block', '' );
         open_span( 'block', '' );
// 
//     open_tr();
//       open_td();
//         open_span( 'block', 'M. Wilkens' );
//         open_span( 'block', 'Theoretische Physik III Lehramt' );
//         open_span( 'block', 'MEd Physik, Modul A711' );
//       open_td();
//         open_span( 'block', '26.07.' );
//         open_span( 'block', '10.00 Uhr' );
//         open_span( 'block', '2.27.0.01' );
//       open_td();
//         open_span( 'block', '27.09.' );
//         open_span( 'block', '10.00 Uhr' );
//         open_span( 'block', '2.28.2.080' );
//   
  close_table();
  
  open_div( 'smallskips'
  , we(   '(More dates will be published here as soon as they are available)'
        , "(Weitere Termine werden hier ver{$oUML}ffentlicht, sobald sie feststehen)" )
  );

close_ccbox();



open_ccbox( '', we('Dates in Winter Term 2020/21',"Termine im Wintersemester 2020/21") );

  echo html_tag('h3', 'medskipt', we('General dates in Winter Term 2020/21',"Allgemeine Termine im Wintersemester 2020/21") );
  
  open_table('td:smallskipt;smallskipb;quads');
  
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "01.10. - 16.10." );
      open_td('', we('Bridge courses and introductory events', "Vorkurse und Einf{$uUML}hrungsveranstaltungen" ));
  
    open_tr('td:/smallskipt/medskipt/');
      open_td( 'oneline', html_div( '', '01.10. - 10.11.' ) . html_div( 'bold red qpadl smaller', we( 'except 15.10.', "au{$SZLIG}er 15.10." ) ) );
      open_td( '', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration, enrollment, and withdrawal period for courses','Anmelde-, Belegungs- und Rücktrittsfrist für Lehrveranstaltungen') ) );
      
    open_tr('td:/smallskipt/medskipt/');
      open_td( 'oneline', "15.10." );
      open_td( '', we('Begin of admission (no registration possible on this day)', "Beginn der Zulassung (keine Belegung an diesem Tag m{$oUML}glich)" ) );
  
  //  open_tr();
  //    open_td('oneline', "03.11." );
  //    open_td('', we('Deadline for cancelation of registration for courses in a Bachelor degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Bachelorstudiengang" ));
  
  //  open_tr('td:/smallskipt/medskipt/');
  //    open_td( 'oneline', html_div( '', '01.10. - 20.11.' ) . html_div( 'bold red qpadl smaller', we( 'except 10.10.', "au{$SZLIG}er 10.10." ) ) );
  //    open_td('', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text='.we('Registration period for courses in a Master degree program','Belegen der Lehrveranstaltungen (Masterstudiengang)') ) );
  
  //  open_tr();
  //    open_td('oneline', "20.11." );
  //    open_td('', we('Deadline for cancelation of registration for courses in a Master degree program',"Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen im Masterstudiengang" ));
      
      open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "19.10. - 12.02." );
      open_td('', we('Lecture period', "Vorlesungszeitraum" ) );
      
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "15.01. - 15.02." );
      open_td('', we('Period for re-registration for Summer term 2021', "R{$uUML}ckmeldung zum Sommersemester 2021" ) );
  
  
    open_tr('td:/smallskipt/medskipt/');
      open_td('oneline', "15.02. - 31.03." );
      open_td('', we('Period for exams and Lab courses', "Zeitraum f{$uUML}r Pr{$uUML}fungen und Praktika" ) );
  
  close_table();
  
  
  echo html_tag('h3', 'medskipt', we('Exams in Winter Term 2020/21',"Pr{$uUML}fungstermine im Wintersemester 2020/21") );
  // 
  
//    open_table('td:smallskipt;smallskipb;qquads;solidtop');
//    
//      open_tr();
//        open_th( '', we( 'course', 'Veranstaltung' ) );
//        open_th( 'qqpads', we( 'first examination date', "1. Pr{$uUML}fungstermin" ) );
//        open_th( 'qqpads', we( 'second examination date', "2. Pr{$uUML}fungstermin" ) );
//     
//      open_tr();
//        open_td();
//          open_span( 'block', 'F. Feudel' );
//          open_span( 'block', 'Theoretische Physik I' );
//          open_span( 'block', 'BEd A511, PHY-511LAS, ICSPHY-511LAS' );
//        open_td();
//          open_span( 'block', '03.02.' );
//          open_span( 'block', '12.00 Uhr' );
//          open_span( 'block', '2.28.0.108' );
//        open_td();
//          open_span( 'block', '25.03.' );
//          open_span( 'block', '10.00 Uhr' );
//          open_span( 'block', '2.28.0.102' );
//    
//       open_tr();
//         open_td();
//           open_span( 'block', 'D. Neher' );
//           open_span( 'block', 'Experimentalphysik I' );
//           open_span( 'block', 'BSc 101, PHY_101, BEd A101, 181, PHY-101LAS, Nebenfach MAT101, IFGP1' );
//         open_td();
//           open_span( 'block', '17.02.' );
//           open_span( 'block', '13.15-14.45 Uhr' );
//           open_span( 'block', '2.27.0.01 und 2.27.1.01' );
//         open_td();
//           open_span( 'block', '24.03.' );
//           open_span( 'block', '10.15-11.45 Uhr' );
//           open_span( 'block', '2.27.0.01' );
//  
//       open_tr();
//         open_td();
//           open_span( 'block', 'A. Feldmeier' );
//           open_span( 'block', 'Theoretische Physik II - Elektrodynamik und Relativität' );
//           open_span( 'block', 'BSc 311, PHY_311, Nebenfach MAT311' );
//         open_td();
//           open_span( 'block', '13.02.' );
//           open_span( 'block', '10.00 Uhr' );
//           open_span( 'block', '2.27.0.01' );
//         open_td();
//           open_span( 'block', '07.04.' );
//           open_span( 'block', '10.00 Uhr' );
//           open_span( 'block', '' );
// 
//     open_tr();
//       open_td();
//       open_span( 'block', 'R. Metzler' );
//         open_span( 'block', 'Theoretische Physik IV - Statistische Physik und Thermodynamik' );
//         open_span( 'block', 'BSc 511, PHY_511, Nebenfach MAT511' );
//       open_td();
//         open_span( 'block', '20.01.' );
//         open_span( 'block', '' );
//         open_span( 'block', '' );
//       open_td();
//         open_span( 'block', '30.03.' );
//         open_span( 'block', '' );
//         open_span( 'block', '' );
// 
//      open_tr();
//        open_td();
//          open_span( 'block', 'C. Henkel' );
//         open_span( 'block', 'Höhere Theoretische Physik - Quantenmechanik II' );
//         open_span( 'block', 'MSc 711' );
//         open_td();
//           open_span( 'block', '13.02.' );
//           open_span( 'block', '10.00 Uhr' );
//           open_span( 'block', '2.27.0.01' );
//         open_td();
//           open_span( 'block', '07.04.' );
//           open_span( 'block', '10.00 Uhr' );
//           open_span( 'block', '' );
//  
//       open_tr();
//         open_td();
//           open_span( 'block', 'M. Wilkens' );
//           open_span( 'block', 'Mathematische Methoden Lehramt' );
//           open_span( 'block', 'BEd Physik PHY-111LAS, A111' );
//         open_td();
//           open_span( 'block', '12.03.' );
//           open_span( 'block', '10.00 Uhr' );
//           open_span( 'block', '2.28.0.108' );
//         open_td();
//           open_span( 'block', 'tba' );
//           open_span( 'block', '' );
//           open_span( 'block', '' );
// 
//      open_tr();
//        open_td();
//          open_span( 'block', 'M. Wilkens' );
//          open_span( 'block', 'Theoretische Physik III Lehramt' );
//          open_span( 'block', 'MEd Physik, Modul A711' );
//        open_td();
//          open_span( 'block', '14.02.' );
//          open_span( 'block', '10.00 Uhr' );
//          open_span( 'block', '2.27.0.01' );
//        open_td();
//          open_span( 'block', '04.04.' );
//          open_span( 'block', '10.00 Uhr' );
//          open_span( 'block', '2.28.2.080' );
// 
// // 
// //     open_tr();
// //       open_td();
// //         open_span( 'block', 'O. Rader' );
// //         open_span( 'block', 'Höhere Festkörperphysik' );
// //         open_span( 'block', 'MSc 701, Nebenfach ICSPHYS741LASe' );
// //         open_td();
// //           open_span( 'block', '22.02.' );
// //           open_span( 'block', '10.00 Uhr' );
// //           open_span( 'block', '2.28.0.108' );
// //         open_td();
// //           open_span( 'block', '' );
// //           open_span( 'block', '' );
// //           open_span( 'block', '' );
// // 
// //   
// close_table();
  
//  open_div( 'smallskips'
//  , we(   'More dates will be published here as soon as they are available.'
//        , "Weitere Termine werden hier ver{$oUML}ffentlicht, sobald sie feststehen." )
//  );
  open_div( 'smallskips'
  , we(   '(Dates will be published here as soon as they are available)'
        , "(Termine werden hier ver{$oUML}ffentlicht, sobald sie feststehen)" )
  );

close_ccbox();


?>
