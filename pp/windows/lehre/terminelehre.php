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
    
/* 

    _m4_tr
    _m4_td
      03.02.&nbsp;-&nbsp;01.03.
    _m4_td
      Erster Prüfungszeitraum
    
    _m4_tr
    _m4_td
      14.10.&nbsp;-&nbsp;26.01.
    _m4_td
      Anmeldezeitraum für Modulprüfungen im ersten Prüfungszeitraum
    
    _m4_tr
    _m4_td
      26.01.
    _m4_td
      Letzter Termin für Rücktritt von Modulprüfungen im ersten Prüfungszeitraum
    
    _m4_smallskip
    
    _m4_tr
    _m4_td
      17.03.&nbsp;-&nbsp;05.04.
    _m4_td
      Zweiter Prüfungszeitraum
    
    _m4_tr
    _m4_td
      15.10.&nbsp;-&nbsp;09.03.
    _m4_td
      Anmeldezeitraum für Modulprüfungen im zweiten Prüfungszeitraum
    
    _m4_tr
    _m4_td
      09.03.
    _m4_td
      Letzter Termin für Rücktritt von Modulprüfungen im zweiten Prüfungszeitraum
    
    
_m4_atable

<h4 style='padding:1em;'>Termine im Wintersemester 2013/14</h4>

_m4_table(style="padding-left:1em;")
    _m4_ifelse(1,0,[[
    _m4_td
      01.10.&nbsp;-&nbsp;12.10.
    _m4_td
    _m4_p
      _m4_link(/lehre/intro.m4,Vorkurse und Einf{$uUML}hrungsveranstaltungen)
    ]])
    


    _m4_tr
    _m4_td
        01.10.&nbsp;-&nbsp;10.10.
    _m4_td
    _m4_p
      _m4_link(http://puls.uni-potsdam.de,[[Belegen der Lehrveranstaltungen (Bachelorstudiengang)]] )
    
    _m4_tr
    _m4_td(class='')
        03.11.
    _m4_td
    _m4_p
      Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen (Bachelorstudiengang)
    
    
    _m4_smallskip
    _m4_tr
    _m4_td()
        01.10.&nbsp;-&nbsp;20.11.
    _m4_td
    _m4_p
      _m4_link(http://puls.uni-potsdam.de,[[Belegen der Lehrveranstaltungen (Masterstudiengang)]] )
    
    _m4_tr
    _m4_td(class='')
        20.11.
    _m4_td
    _m4_p
      Letzter Termin f{$uUML}r R{$uUML}cktritt von Lehrveranstaltungen (Masterstudiengang)
      
    _m4_ifelse(1,0,[[
    _m4_tr
    _m4_td
      31.05
    _m4_td
      Ausschlussfrist für Bewerbung um Studienplatz im ersten Fachsemester - Bachelorstudiengang -
      f{$uUML}r Alt-Abiturienten 
      (Hochschulzugangsberechtigung vor dem 16.01.2008 erworben)
    ]])
    
    _m4_smallskip
    _m4_tr
    _m4_td
      14.10.&nbsp;-&nbsp;07.02.
    _m4_td
      Vorlesungszeitraum
    
    _m4_tr
    _m4_td
      15.01.&nbsp;-&nbsp;15.02.
    _m4_td
      R{$uUML}ckmeldung zum Sommersemester 2013
    
    _m4_smallskip
    
    
    
    _m4_tr
    _m4_td
      03.02.&nbsp;-&nbsp;01.03.
    _m4_td
      Erster Prüfungszeitraum
    
    _m4_tr
    _m4_td
      14.10.&nbsp;-&nbsp;26.01.
    _m4_td
      Anmeldezeitraum für Modulprüfungen im ersten Prüfungszeitraum
    
    _m4_tr
    _m4_td
      26.01.
    _m4_td
      Letzter Termin für Rücktritt von Modulprüfungen im ersten Prüfungszeitraum
    
    _m4_smallskip
    
    _m4_tr
    _m4_td
      17.03.&nbsp;-&nbsp;05.04.
    _m4_td
      Zweiter Prüfungszeitraum
    
    _m4_tr
    _m4_td
      15.10.&nbsp;-&nbsp;09.03.
    _m4_td
      Anmeldezeitraum für Modulprüfungen im zweiten Prüfungszeitraum
    
    _m4_tr
    _m4_td
      09.03.
    _m4_td
      Letzter Termin für Rücktritt von Modulprüfungen im zweiten Prüfungszeitraum
    
    
_m4_atable



*/

close_table();

?>
