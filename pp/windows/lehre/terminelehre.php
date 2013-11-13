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

close_table();

?>
