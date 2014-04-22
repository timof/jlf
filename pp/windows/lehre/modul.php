<?php

sql_transaction_boundary('*');

init_var('modul','type=a20,pattern=/^[0-9a-zA-Z]+$/,global=1,sources=http persistent,set_scopes=script' );



$text_softmatter = "
Das Angebot an Lehrveranstaltungen in den Modulen 541a und 741a ist eng mit den
Forschungsaktivit{$aUML}ten am Forschungsstandort Golm und am Helmholtz Zentrum
Berlin verkn{$uUML}pft. In den Lehrveranstaltungen geht es meist um Struktur und
Dynamik sogenannter weicher Materie (Soft Matter). Die Bausteine dieser
Materialklasse sind Molek{$uUML}le die - durch schwache Bindungen
(van-der-Waals-Bindungen) stabilisiert - komplexe supramolekulare Strukturen
ausbilden. Die Besonderheit dieser Systeme besteht darin, dass bereits kleine
Kr{$aUML}fte ausreichen, um Strukturen und Eigenschaften zu ver{$aUML}ndern, was die
Grundlage f{$uUML}r die gro{$SZLIG}e Vielfalt von Funktionen in nat{$uUML}rlichen und
synthetischen kondensierten Systemen bildet. Interessanterweise findet sich ein
'weiches' Verhalten bestimmter Eigenschaften auch in kristallinen Festk{$oUML}rpern
und Nanostrukturen und ist Grundlage spektakul{$aUML}rer Ph{$aUML}nomene. Besonders die
elektronischen Eigenschaften weicher Materie werden erst im Vergleich mit
klassischen Festk{$oUML}rpern verst{$aUML}ndlich.  Die Vielzahl der m{$oUML}glichen Strukturen
und die komplexe Responsivit{$aUML}t der kondensierten Materie erfordert ein genaues
Verst{$aUML}ndnis der physikalischen Vorg{$aUML}nge auf allen relevanten L{$aUML}ngen- und
Zeitskalen. Das experimentell orientierte Lehrangebot umfasst daher eine
Einf{$uUML}hrung in die Prinzipien, die Struktur und Dynamik der weichen Materie
bestimmen (Einf{$uUML}hrung in die Physik weicher Materie, Thin Films and
Interfaces), Vorlesungen zu speziellen hochaufl{$oUML}senden Methodiken (z.B.
Advanced Microscopy, R{$oUML}ntgenstrukturanalyse und ultraschnelle Dynamik) sowie
Veranstaltungen zu speziellen Systemen. Dieses Angebot wird erg{$aUML}nzt durch
theoretisch orientierte Vorlesungen, die sich zum einen mit der
Elektronenstruktur von Molek{$uUML}len und einfachen supramolekularen Systemen
(Dichtefunktionaltheorie) und zum anderen mit der theoretischen Beschreibung
der strukturellen und dynamischen Eigenschaften weicher Materie (Introduction
to Theoretical Soft Matter Physics) besch{$aUML}ftigen. Dar{$uUML}ber hinaus geh{$oUML}ren
auch biologische Objekte in den Bereich der weichen kondensierten Materie. Die
Biophysik bildet daher einen weiteren Schwerpunkt des Vorlesungsangebots in
beiden Modulen. Hier erhalten die Studierenden durch ein konsekutives
Vorlesungsangebot im Bachelor und im Master insgesamt eine umfassende
Ausbildung zu grundlegenden Aspekten der zellul{$aUML}ren Biophysik. Dynamische
Prozesse auf der Mikrometer- und Nanometerskala stehen hier im Fokus des
Interesses.  Die hohe Komplexit{$aUML}t der kondensierten Materie erfordert es, dass
die Veranstaltungen von fachlich kompetenten Lehrenden unterschiedlicher
Disziplinen angeboten werden. Die Lehre in den beiden Modulen wird daher von
Wissenschaftler/innen aus verschiedenen universit{$aUML}ren und
au{$SZLIG}eruniversit{$aUML}ren Einrichtungen getragen. Diese Breite erm{$oUML}glicht es den
Studierenden, die Vielfalt der Forschung an den beteiligten Institutionen
kennenzulernen und sich optimal auf ihre Bachelor- oder Master-Arbeit in einem
einschl{$aUML}gigen Gebiet der Physik kondensierter Materie vorzubereiten.  Das
Veranstaltungsangebot richtet sich vornehmlich an Studierende des Bachelor- und
Masterstudiengangs im Fach Physik und im Lehramt Physik, wir m{$oUML}chten aber auch
Studierende anderer Studieng{$aUML}nge zur Teilnahme an den Veranstaltungen
ermuntern - z.B. im Rahmen von Wahl- und Wahlpflichtmodulen. Insbesondere sind
die Kurse f{$uUML}r Studierende
des Master-Programms 'Polymer Science' sowie
Mitglieder der neu gegr{$uUML}ndeten Graduiertenschulen
'International Max Planck Research School (IMPRS) on Multiscale Bio-Systems: From Molecular Recognition
to Mesoscopic Transport' und der Helmholtz-Graduiertenschule f{$uUML}r
Macromolecular Bioscience geeignet.  Modulbeauftragte f{$uUML}r beide Module ist
Frau Professor Svetlana Santer. Sie informiert sie gerne {$uUML}ber die
prinzipiellen Ziele der Ausbildung in den Modulen, aber auch zu
organisatorischen Aspekten, wie z.B. den Voraussetzungen zur Vergabe der
Leistungspunkte. Zu speziellen inhaltlichen Fragen wenden Sie sich bitte direkt
an die Lehrenden.
";





switch( $modul ) {
  case '541a':

    open_tag( 'h1', '', "Modul 541a: Fachspezialisierung: Physik kondensierter Systeme, 8 LP" );

    open_table('medskips quadl qqquadr td;th:smallskips;quads');
      open_tr();
        open_th( 'colspan=3', we('Winter term','Wintersemester' ) );

      open_tr('solidtop');
        open_td( '', "Einf{$uUML}hrung in Physik weicher Materie".html_tag('br', '', NULL ). "/ Introduction to Soft Matter Physics (wahlweise dt. oder engl.)" );
        open_td( '', "2V1{$UUML}, 4 LP" );
        open_td( '', "Svetlana Santer" );

      open_tr('solidtop');
        open_td( '', "Biophysik I" );
        open_td( '', "2V1{$UUML}, 4 LP" );
        open_td( '', "Carsten Beta" );

      open_tr();
        open_th( 'colspan=3,medpadt', we('Summer term','Sommersemester' ) );

      open_tr('solidtop');
        open_td( '', "Advanced Microscopy (engl.)" );
        open_td( '', "2V1{$UUML}, 4 LP" );
        open_td( '', "Svetlana Santer" );

      open_tr('solidtop');
        open_td( '', "Thin Films and Interfaces (engl.)" );
        open_td( '', "2V1{$UUML}, 4 LP" );
        open_td( '', "Hans Riegler, Helmuth M{$oUML}hwald (beide MPI)" );
//    <a href="http://www.mpikg-golm.mpg.de"><abbr title='Max-Planck Institut für Kolloid- und Grenzflächenforschung'>MPI-KG</abbr></a>)

    close_table();

    open_div('medpadt qquadr', $text_softmatter );

    break;

  case '741a':

    open_tag( 'h1', '', "Modul 741a: Vertiefungsgebiet: Physik kondensierter Systeme, 12 LP" );

    open_table('medskips quadl qqquadr td;th:smallskips;quads');

      open_tr();
        open_th( 'colspan=3', we('Winter term','Wintersemester' ) );

      open_tr('solidtop');
        open_td( '', "Synchrotronmethoden und Ultraschnelle Dynamik" );
        open_td( '', "2V1{$UUML}, 4 LP" );
        open_td( '', "Alexander F{$oUML}hlisch (HZB)" );

      open_tr('solidtop');
        open_td( '', "Physik der Solarzellen / Physics of Solar Cells (wahlweise dt. oder Engl.)" );
        open_td( '', "2S1{$UUML}, 4 LP" );
        open_td( '', "Dieter Neher, Riccardo di Pietro" );

      open_tr('solidtop');
        open_td( '', "Introduction to Theoretical Soft Matter Physics (engl.) " );
        open_td( '', "2V, 3 LP" );
        open_td( '', "N.N." );

      open_tr('solidtop');
        open_td( '', "Organic Solar Cells" );
        open_td( '', "2V1{$UUML}, 1 LP" );
        open_td( '', "Thomas Brenner" );

      open_tr();
        open_th( 'colspan=3,medpadt', we('Summer term','Sommersemester' ) );

      open_tr('solidtop');
        open_td( '', "Organische Halbleiter / Organic Semiconductors (dt. oder engl.)" );
        open_td( '', "2V1{$UUML}, 4 LP" );
        open_td( '', "Riccardo di Pietro, Dieter Neher" );

      open_tr('solidtop');
        open_td( '', "Biophysik II" );
        open_td( '', "2V1{$UUML}, 4 LP" );
        open_td( '', "Carsten Beta" );

      open_tr('solidtop');
        open_td( '', "Strukturcharakterisierung von biobasierten Polymerwerkstoffen " );
        open_td( '', "2V+1{$NBSP}Kompaktpraktikum im IAP, 4 LP" );
        open_td( '', "Hans-Peter Fink, Johannes Ganster (beide IAP)" );

      open_tr('solidtop');
        open_td( '', "Neutron Scattering Applications to Hydrogen Storage Materials (engl.)" );
        open_td( '', "2V1P, 4 LP" );
        open_td( '', "Margarita Russina (HZB), Carsten Beta" );

      open_tr('solidtop');
        open_td( '', "Transducer Properties of Functional Soft Matter" .html_tag('br', '', NULL )." / Sensor- und Aktoreigenschaften weicher Materie (engl. oder dt.)" );
        open_td( '', "2V, 3 LP" );
        open_td( '', "Reimund Gerhard und Mitarbeiter" );

    close_table();

    open_div('medpadt qquadr', $text_softmatter );

  break;
  
}

?>
