<?php
//
//
// this file is included from veranstaltung.php!
//
//
if( $deliverable ) switch( $deliverable ) {

   case 'attachment': // for attached file
     begin_deliverable( 'attachment', 'pdf', base64_decode( $event['pdf'] ) );
     return;

  default:
    error("no such deliverable: $deliverable");
}


open_ccbox( '', html_span( 'red bold', 'Abgesagt: Klimatag am 30. März 2020' ) );

open_div( 'red bold Huge', 
  'Angesichts der aktuellen Coronavirus-Epidemie
   müssen wir den Klimatag am 30.03.2020 leider ' . html_span( 'underline', 'absagen' ) .'.
   Wir bemühen uns um einen späteren Ersatztermin.
' );

  echo html_tag( 'h2', '', 'Wissenschaft und Klimawandel' );

  open_div( 'qquads' );

    echo '
Das Institut für Physik und Astronomie der Universität Potsdam bietet am Montag
dem 30. März 2020 einen "Klimatag" für Schülerinnen, Schüler und die
interessierte Öffentlichkeit an. Dazu haben wir Wissenschaftler der Universität
Potsdam und Potsdamer außeruniversitärer Forschungseinrichtungen gewonnen, um
über den aktuellen Stand der Forschung zu diesem dringlichen Thema zu
berichten. Es besteht die Möglichkeit, Fragen zu stellen, und im Rahmen einer
abschließenden Diskussion sollen kontroverse Standpunkte beleuchtet werden. Die
Vorträge sind für Schülerinnen und Schüler der Jahrgangsstufen 10 bis 13
gedacht und werden allgemein verständlich sein. 
    ';

//     open_ul( 'qquadl' );
//       open_li( '', '
//         Stefan Rahmstorf (Potsdamer Institut für Klimafolgenforschung) hat mit seiner Forschung zur Umkehr des Golfstroms Aufsehen erregt.
//       ' );
//       open_li( '', '
//         Axel Bronstert (Uni Potsdam, Umweltwissenschaften und Geographie) nimmt mit
//         seinem Vortrag zu Wasserressourcen die aktuelle Sorge auf, der Region
//         Brandenburg stehe 2019 erneut ein trockener Sommer ins Haus. 
//       ' );
//       open_li( '', '
//         Markus Rex (Alfred-Wegener-Institut für Polarforschung) hat bereits Dutzende von
//         Expeditionen ins Nordpolarmeer hinter sich, wo die klimatischen Veränderungen
//         besonders sichtbar sind.
//       ' );
//       open_li( '', '
//         Dieter Neher (Uni Potsdam, Physik und Astronomie) stellt
//         aktuelle Entwicklungen und Herausforderungen in der fossilfreien
//         Energiegewinnung vor.
//       ' );
//       open_li( '', '
//         Johan Rockström (Direktor am PIK) fasst in einem
//         Impulsreferat die Herausforderungen für Wissenschaft und Gesellschaft
//         zusammen und eröffnet die Podiumsdiskussion.
//       ' );
//     close_ul();

//     echo '
//       Es besteht die Gelegenheit, Fragen zu stellen, und im Rahmen einer
//       Podiumsdiskussion sollen kontroverse Standpunkte beleuchtet werden. Die
//       Vorträge
//       wenden sich vorrangig an Schülerinnen
//       und Schüler
//       der Jahrgangsstufen 10 bis 13 und werden allgemein verständlich
//       sein.
//     ';
  close_div();



open_div( 'qquadl bigskipb' );

  echo html_tag( 'h2', '', 'Vorläufiges Programm' );

//  open_div( 'medskips Large', 'Vormittag: Vortragsreihe (Audimax 1.08.1.45)' );

  open_table( 'medskips css td:qquadr' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '09:15 Uhr' );
      open_td( '', 'Einleitung' );
    close_tr();
  close_table();

  open_table( 'medskips css td:qquadr' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '09:30 Uhr' );
      open_td( '', 'Stefan Rahmstorf' );
    close_tr();
    open_tr( 'td:Large;medium' );
      open_td( '', '' );
      open_td( 'italic', 'Was ist los mit unserem Klima? Die wichtigsten Fakten zur globalen Erwärmung' );
    close_tr();
    open_tr( 'td:smallskipt' );
      open_td( '', '' );
      open_td( '', '
        Stefan Rahmstorf (Potsdamer Institut für Klimafolgenforschung) hat mit seiner Forschung zur Abschwächung des Golfstroms Aufsehen erregt.
      ' );
    close_tr();
  close_table();


  open_table( ' medskips css td:qquadr' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '10:15 Uhr' );
      open_td( '', 'Ricarda Winkelmann' );
    close_tr();
    open_tr( 'td:Large;medium' );
      open_td( '', '' );
      open_td( 'italic', 'Polare Eismassen <==> Einfluss auf das Klima' );
    close_tr();
    open_tr( 'td:smallskipt' );
      open_td( '', '' );
      open_td( '', '
  Ricarda Winkelmann (Potsdamer Institut für Klimafolgenforschung) forscht und
  berichtet über die Rolle von Arktis und Antarktis für das Klima.
      ' );
    close_tr();
  close_table();


  open_div( 'medskips Large', '--- Pause ---' );

  open_table( 'medskips css td:qquadr' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '11:15 Uhr' );
      open_td( '', 'Fred Hattermann' );
    close_tr();
    open_tr( 'td:Large;medium' );
      open_td( '', '' );
      open_td( 'italic', 'Regionale Folgen des Klimawandels' );
    close_tr();
    open_tr( 'td:smallskipt' );
      open_td( '', '' );
      open_td( '', '
  Fred Hattermann (Potsdamer Institut für Klimafolgenforschung) untersucht
  Einflüsse des Klimawandels auf unsere Wasserressourcen und auf Klimaextreme.
      ' );
    close_tr();
  close_table();

  
  open_table( 'medskips css td:qquadr' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '12:00 Uhr' );
      open_td( '', 'Dieter Neher' );
    close_tr();
    open_tr( 'td:Large;medium' );
      open_td( '', '' );
      open_td( 'italic', 'Energie ohne Emission: Forschung an Energiematerialien' );
    open_tr( 'td:smallskipt' );
      open_td( '', '' );
      open_td( '', '
        Dieter Neher (Universität Potsdam) stellt
        aktuelle Entwicklungen und Herausforderungen in der fossilfreien
        Energiegewinnung vor.
      ' );
    close_tr();
  close_table();

  open_div( 'medskips Large', '--- 12:45 - 14:00 Uhr Mittagspause (Gelegenheit zum Essen in der Mensa am Neuen Palais) ---' );

  open_table( 'medskips css td:qquadr' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '14:00 Uhr' );
      open_td( '', 'John Schellnhuber (ehemaliger PIK-Direktor)' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '' );
      open_td( '', 'Einführung zur Podiumsdiskussion' );
    open_tr( 'td:Large;medium;oneline;smallskipt' );
      open_td( '', '14:20 Uhr' );
      open_td( '', 'Podiumsdiskussion (60 Minuten)' );
    open_tr( 'td:Large;medium' );
      open_td( '', '' );
      open_td( '', 'Teilnehmende: Schüler*innen, Studierende und Wissenschaftler*innen' );
  close_table();

  

  // open_div( 'bigkips', 'Programm zum Download: ' . html_span( 'qpadl', alink_document_view( 'tag=programmklimatag', 'class=qpadl' ) ) );
  open_div( 'large bigkips', 'der Klimatag auf Twitter: ' . html_alink( 'https://twitter.com/UPKlimatag', 'href outlink,text=https://twitter.com/UPKlimatag' ) );


  echo html_tag( 'h2', '', 'Anfahrt' );

    open_div( 'qquadl medskips' );

      echo 'Die Veranstaltung findet statt am '
        . html_alink( 'https://www.uni-potsdam.de/db/zeik-portal/gm/lageplan-up.php?komplex=1'
          , array(
              'class' => 'href outlink'
            , 'text' => 'Campus I ' . html_span( 'italic', 'Am Neuen Palais' ) . ' der Universität Potsdam'
            )
          );
        echo ' in Haus 8, Raum 1.45 (Auditorium Maximum)';
//      open_ul( 'quadl' );
//        open_li( '', 'vormittags (Vorträge) in Raum 1.45 (Auditorium Maximum) in Haus 8;' );
//        open_li( '', 'nachmittags (Podiumsdiskussion) .' );
//      close_ul();

    close_div();
   
    open_div( 'qquadl medskips' );
      echo '
        Der Campus ist erreichbar per Bus (Buslininen 605, 606 und 695 bis Haltestelle "Am Neuen Palais" oder "Campus Universität / Lindenallee")
        oder Bahn (Bahnhof "Potsdam Park Sanssouci"; dort halten RB 20, RB 21 und einige Züge der Linie RE 1)
      ';
    close_div();


  echo html_tag( 'h2', '', 'Anmeldung' );


    open_div( 'qquadl' );

      open_tag( 'p' );
        echo '
        Die Veranstaltung ist offen und eintrittsfrei für
        alle Interessierten.  Teilnehmende Schulklassen bitten wir um Anmeldung an
          ' . html_span( '', html_obfuscate_email( 'klimatag@physik.uni-potsdam.de' ) ) . '
          unter Angabe von
        ';
        open_ul( 'qquadl medpadb' );
          open_li( '', 'Teilnehmerzahl (circa)' );
//          open_li( '', 'Teilnahme am Nachmittagsprogramm: ja oder nein' );
          open_li( '', 'Mensaessen (für Schülerinnen und Schüler zum Studierendenpreis) gewünscht: ja oder nein' );
          open_li( '', 'Kontakt(email-)adresse' );
        close_ul();
  
        echo '
          Auch für Rückfragen zur Veranstaltung stehen wir gerne unter
          ' . html_span( '', html_obfuscate_email( 'klimatag@physik.uni-potsdam.de' ) ) . '
          zur Verfügung.
        ';
      close_tag( 'p' );

    close_div();
    
close_div();

open_div('bigskips'
, 'Die Veranstaltung wird organisiert von '
  . alink_person_view( 'gn=dieter,sn=neher' )
  . ' und '
  . alink_person_view( 'gn=frank,sn=spahn' )
  . '.'
);

open_div('bigskips', html_alink( '/klimatag2019', 'href inlink,text=Programm des ersten Klimatags am 17.06.2019' ) );

close_ccbox();

?>
