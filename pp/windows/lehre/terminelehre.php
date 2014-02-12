<?php

echo html_tag('h1', '', we('Important Dates for students',"Wichtige Termine f{$uUML}r Studierende") );

echo html_tag('h2', '', we('Winter Term 2013/14',"Termine im Wintersemester 2013/14") );

open_table('td:smallskipt;smallskipb;quads');
  open_tr();
    open_td('oneline', "01.10. - 12.10." );
    open_td('', "Vorkurse und Einf{$uUML}hrungsveranstaltungen" );

  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "01.10. - 10.10." );
    open_td('', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text=Belegen der Lehrveranstaltungen (Bachelorstudiengang)' ) );
    
  open_tr();
    open_td('oneline', "03.11." );
    open_td('', "Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen (Bachelorstudiengang" );
    
    
  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "01.10. - 20.11." );
    open_td('', html_alink( 'http://puls.uni-potsdam.de', 'class=href outlink,text=Belegen der Lehrveranstaltungen (Masterstudiengang)' ) );
    
  open_tr();
    open_td('oneline', "20.11." );
    open_td('', "Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen (Masterstudiengang)" );
    
    
  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "14.10 - 07.02." );
    open_td('', "Vorlesungszeitraum" );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "15.01 - 15.02." );
    open_td('', "R{$uUML}ckmeldung zum Sommersemester 2013" );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "03.02. - 01.03." );
    open_td('', "Erster Prüfungszeitraum" );
    
  open_tr();
    open_td('oneline', "14.10. - 26.01." );
    open_td('',  "Anmeldezeitraum für Modulprüfungen im ersten Prüfungszeitraum" );
    
  open_tr();
    open_td('oneline', "26.01." );
    open_td('', "Letzter Termin für Rücktritt von Modulprüfungen im ersten Prüfungszeitraum" );
    
  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "17.03. - 05.04." );
    open_td('', "Zweiter Prüfungszeitraum" );

  open_tr();
    open_td('oneline', "14.10. - 09.03." );
    open_td('',  "Anmeldezeitraum für Modulprüfungen im zweiten Prüfungszeitraum" );
    
  open_tr();
    open_td('oneline', "09.03." );
    open_td('', "Letzter Termin für Rücktritt von Modulprüfungen im zweiten Prüfungszeitraum" );

  open_tr('td:/smallskipt/medskipt/');
    open_td('oneline', "15.02. - 15.03." );
    open_td( ''
    ,  inlink( 'einschreibung'
        , array( 'text' => we( 'Enrollment for a Bachelor degree course (BSc or BEd) in summer term', "Einschreibung f{$uUML}r Bachelor-Studieng{$aUML}nge (BSc und BEd) zum Sommersemester" ) )
       )
       . we( ' (higher semesters of study only - no beginners)', " (nur h{$oUML}here Fachsemester - keine Studienanf{$aUML}nger)" )
    );

  open_tr();
    open_td('oneline', "15.03. " );
    open_td(''
    ,  we('Deadline for ', "Frist f{$uUML}r ")
       . inlink( 'einschreibung', array( 'text' => we( 'application for admission to the Master of Education (MSc) degree course in summer term'
                                                      ,"Bewerbung um Zulassung zum Studiengang Master of Science (MSc) im Sommersemester" ) ) )
       . we( ' (not required for the MEd degree course)', " (f{$uUML}r den MEd Studiengang ist keine Bewerbung erforderlich)" )
    ); 
  open_tr();
    open_td('oneline', "15.02. - 10.05." );
    open_td(''
    ,  inlink( 'einschreibung', array( 'text' => we( 'Enrollment for a Master degree course in summer term', "Einschreibung f{$uUML}r Master-Studieng{$aUML}nge zum Sommersemester" ) ) )
      . we( ' (MSc: only after application and admission / MEd: no application required)', " (MSc: nur nach Bewerbung und Zulassung / MEd: keine Bewerbung erforderlich)" )
    );

close_table();



echo html_tag('h2', 'medskipt', we('Exams in Winter Term 2013/14',"Prüfungstermine im Wintersemester 2013/14") );

open_table('td:smallskipt;smallskipb;qquads;solidtop');

  open_tr();
    open_th( '', 'Veranstaltung' );
    open_th( '', '1. Prüfungstermin' );
    open_th( '', '2. Prüfungstermin' );

  open_tr();
    open_td();
      open_span( 'block', 'R. Gerhard' );
      open_span( 'block', 'Experimentalphysik I' );
      open_span( 'block', 'BSc 101, BEd PHYS-101LAS' );
    open_td();
      open_span( 'block', '11.02.2014' );
      open_span( 'block', '10.15 - 12.15 Uhr' );
      open_span( 'block', '2.27.001, 2.27.101' );
    open_td();
      open_span( 'block', '18.03.2014' );
      open_span( 'block', '10.15 - 12.15 Uhr' );
      open_span( 'block', '2.27.001' );

  open_tr();
    open_td();
      open_div('','M. Wilkens' );
      open_div('', 'Math. Methoden (LA)' );
      open_div('', 'BEd A111, PHY-111LAS' );
    open_td();
      open_div('', '18.02.2014' );
      open_div('', '10-12 Uhr' );
      open_div('', '2.28.0.108' );
    open_td();
      open_div('', '01.04.2014' );
      open_div('', '10-12 Uhr' );

  open_tr();
    open_td();
      open_div('', 'M. Bargheer' );
      open_div('', 'Experimentalphysik III' );
      open_div('', 'BSc 301' );
      open_div('', 'BEd A301, 381, PHYS-301LAS' );
    open_td();
      open_div('', '19.02.2014' );
      open_div('', '10 - 12 Uhr' );
    open_td();
      open_div('', '26.03.2014' );
      open_div('', '10 - 12 Uhr' );

  open_tr();
    open_td();
      open_div( '', 'A. Feldmeier' );
      open_div( '', 'Theoretische Physik II' );
      open_div( '', 'Elektrodynamik und Relativität' );
      open_div( 'block', 'BSc 311' );
    open_td();
      open_div('', '12.02.2014' );
      open_div('', '10-12 Uhr' );
      open_div('', '2.28.0.108' );
    open_td();
      open_div('', '02.04.2014' );
      open_div('', '10-12 Uhr' );
      open_div('', '2.28.0.108' );

  open_tr();
    open_td();
      open_div('', 'U. Magdans' );
      open_div('', 'Physikalische Schulexperimente II' );
      open_div('', 'BEd A581' );
      open_div('', 'MEd 194' );
    open_td('colspan=2');
      open_div('', '17.02., 18.02. und 19.02.2014' );
      open_div('', '10-12 und 13-15 Uhr' );
      open_div('', '(Präsentationen, je 20min)' );

  open_tr();
    open_td();
      open_div('','M. Wilkens' );
      open_div('', 'Quantenmechanik II' );
      open_div('', 'MSc 711' );
    open_td();
      open_div('', '19.02.2014' );
      open_div('', '10 - 12 Uhr' );
      open_div('', '2.28.0.108' );
    open_td();
      open_div('', '02.04.2014' );
      open_div('', '10 - 12 Uhr' );
    
close_table();


?>
