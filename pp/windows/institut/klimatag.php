<?php

// sql_transaction_boundary('*');
// 
// open_div('id=teaser');
//   open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
//     echo image('h28innenhof');
//     echo html_tag( 'h1', '', 'Institut / Klimatag am 17. Juni 2019' );
//   close_div();
// close_div();


if( $deliverable ) switch( $deliverable ) {

   case 'attachment': // for attached file
     begin_deliverable( 'attachment', 'pdf', base64_decode( $event['pdf'] ) );
     return;

  default:
    error("no such deliverable: $deliverable");
}


open_ccbox( '', 'Klimatag am 17. Juni 2019' );


  echo html_tag( 'h2', '', 'Was sagt die Wissenschaft zum Klimawandel?' );

  open_div( 'qquads' );

    echo '
      Das Institut für Physik und Astronomie der Universität Potsdam bietet am
      17. Juni 2019 einen "Klimatag"
      für Schülerinnen, Schüler und die
      interessierte Öffentlichkeit an.
      Wissenschaftler der Universität und von Potsdamer Forschungseinrichtungen werden
      über
      den Stand der Forschung zu diesem aktuellen Thema berichten.
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

    echo '
      Es besteht die Gelegenheit, Fragen zu stellen, und im Rahmen einer
      Podiumsdiskussion sollen kontroverse Standpunkte beleuchtet werden. Die
      Vortr�
      wenden sich vorrangig an Schülerinnen
      und Schüler
      der Jahrgangsstufen 10 bis 13 und werden allgemein verständlich
      sein.
    ';
  close_div();



open_div( 'qquadl bigskipb' );

  echo html_tag( 'h2', '', 'Programm' );

  open_div( 'medskips Large', 'Vormittag: Vortragsreihe (Audimax 1.08.1.45)' );

  open_table( 'medskips css td:qquadr' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '09:30 Uhr' );
      open_td( '', 'Stefan Rahmstorf (PIK)' );
    close_tr();
    open_tr( 'td:Large;medium' );
      open_td( '', '' );
      open_td( 'italic', 'Was ist los mit unserem Klima? Die wichtigsten Fakten zur globalen Erwärmung' );
    close_tr();
    open_tr( 'td:smallskipt' );
      open_td( '', '' );
      open_td( '', '
        Stefan Rahmstorf (Potsdamer Institut für Klimafolgenforschung) hat mit seiner Forschung zur Umkehr des Golfstroms Aufsehen erregt
      ' );
    close_tr();
  close_table();


  open_table( ' medskips css td:qquadr' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '10:15 Uhr' );
      open_td( '', 'Axel Bronstert (UP)' );
    close_tr();
    open_tr( 'td:Large;medium' );
      open_td( '', '' );
      open_td( 'italic', 'Wasserresourcen und klimatische Veränderungen - auf was müssen wir uns einstellen?' );
    close_tr();
    open_tr( 'td:smallskipt' );
      open_td( '', '' );
      open_td( '', 'Axel Bronstert (Uni Potsdam, Umweltwissenschaften und Geographie) nimmt mit
        seinem Vortrag zu Wasserressourcen die aktuelle Sorge auf, der Region
        Brandenburg stehe 2019 erneut ein trockener Sommer ins Haus
      ' );
    close_tr();
  close_table();


  open_div( 'medskips Large', '--- Pause ---' );

  open_table( 'medskips css td:qquadr' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '11:15 Uhr' );
      open_td( '', 'Markus Rex (AWI)' );
    close_tr();
    open_tr( 'td:Large;medium' );
      open_td( '', '' );
      open_td( 'italic', 'Die dramatische Erwärmung der Arktis - Was geht uns das an und was können wir tun?' );
    close_tr();
    open_tr( 'td:smallskipt' );
      open_td( '', '' );
      open_td( '', '
        Markus Rex (Alfred-Wegener-Institut für Polarforschung) hat bereits Dutzende von
        Expeditionen ins Nordpolarmeer hinter sich, wo die klimatischen Veränderungen
        besonders sichtbar sind.
      ' );
    close_tr();
//     open_tr( 'td:medskipt' );
//       open_td( '', '' );
//       open_td( '', "
//         Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Etiam congue,
//         mauris id ultrices ultrices, odio metus condimentum orci, eu blandit
//         ipsum nisl et nibh. Maecenas velit quam, accumsan ac, venenatis id,
//         pharetra cursus, risus. Vivamus imperdiet. Cras vel lacus. Sed eu sem.
//         Vestibulum ante ipsum primis in faucibus orci luctus et ultrices
//         posuere cubilia Curae; Nam nisl purus, fermentum ac, sagittis in,
//         luctus vitae, lectus.
//       " );
//     close_tr();
  close_table();

  
  open_table( 'medskips css td:qquadr' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '12:00 Uhr' );
      open_td( '', 'Dieter Neher (UP)' );
    close_tr();
    open_tr( 'td:Large;medium' );
      open_td( '', '' );
      open_td( 'italic', 'Energie ohne Emission: Forschung an Energiematerialien' );
    open_tr( 'td:smallskipt' );
      open_td( '', '' );
      open_td( '', '
        Dieter Neher (Uni Potsdam, Physik und Astronomie) stellt
        aktuelle Entwicklungen und Herausforderungen in der fossilfreien
        Energiegewinnung vor
      ' );
    close_tr();
  close_table();


  open_div( 'medskips Large', '--- Mittagspause (Gelegenheit zum Essen in der Mensa am Neuen Palais) ---' );
  
  open_div( 'bigskipt medskipb Large', 'Nachmittag: Podiumsdiskussion (Hörsaal 1.11.0.09)' );

  open_table( 'medskips css td:qquadr' );
    open_tr( 'td:Large;medium;oneline' );
      open_td( '', '13:45 Uhr' );
      open_td( '', 'Johan Rockström (PIK): Einführung zur Podiumsdiskussion' );
    close_tr();
    open_tr( 'td:Large;medium' );
      open_td( '', '' );
      open_td( 'italic', 'Safeguarding our climate --- from Fridays to future' );
    close_tr();
    open_tr( 'td:smallskipt' );
      open_td( '', '' );
      open_td( '', '
        Johan Rockström (Direktor am PIK) fasst in einem
        Impulsreferat die Herausforderungen für Wissenschaft und Gesellschaft
        zusammen und eröffnet die Podiumsdiskussio
      ' );
    close_tr();
    open_tr( 'td:Large;medium;oneline;medskipt' );
      open_td( '', '14:30 Uhr' );
      open_td( '', 'Podiumsdiskussion' );
    close_tr();
    open_tr( '' );
      open_td( '', '' );
      open_td( '', '(Teilnehmer N.N.)' );
    close_tr();
  close_table();

  open_div( 'medskips', 'Programm zum Download: ' . html_span( 'qpadl', alink_document_view( 'tag=programmklimatag', 'class=qpadl' ) ) );

//       open_tag( 'p' );
//         echo 'Unsere Vortragenden:';
//         
//         open_ul( 'qquadl' );
//           open_li( '', '
//             Stefan Rahmstorf (Potsdamer Institut für Klimafolgenforschung) hat mit seiner Forschung zur Umkehr des Golfstroms Aufsehen erregt.
//           ' );
//           open_li( '', '
//             Axel Bronstert (Uni Potsdam, Umweltwissenschaften und Geographie) nimmt mit
//             seinem Vortrag zu Wasserressourcen die aktuelle Sorge auf, der Region
//             Brandenburg stehe 2019 erneut ein trockener Sommer ins Haus. 
//           ' );
//           open_li( '', '
//             Markus Rex (Alfred-Wegener-Institut für Polarforschung) hat bereits Dutzende von
//             Expeditionen ins Nordpolarmeer hinter sich, wo die klimatischen Veränderungen
//             besonders sichtbar sind.
//           ' );
//           open_li( '', '
//             Dieter Neher (Uni Potsdam, Physik und Astronomie) stellt
//             aktuelle Entwicklungen und Herausforderungen in der fossilfreien
//             Energiegewinnung vor.
//           ' );
//           open_li( '', '
//             Johan Rockström (Direktor am PIK) fasst in einem
//             Impulsreferat die aktuellen Herausforderungen für Wissenschaft und Gesellschaft
//             zusammen und eröffnet die Podiumsdiskussion.
//           ' );
//         close_ul();
// 
//       close_tag( 'p' );
// 




  echo html_tag( 'h2', '', 'Anfahrt' );

    open_div( 'qquadl medskips' );

      echo 'Die Veranstaltung findet statt am '
        . html_alink( 'https://www.uni-potsdam.de/db/zeik-portal/gm/lageplan-up.php?komplex=1'
          , array(
              'class' => 'href outlink'
            , 'text' => 'Campus I ' . html_span( 'italic', 'Am Neuen Palais' ) . ' der Universität Potsdam'
            )
          );
      open_ul( 'quadl' );
        open_li( '', 'vormittags (Vorträge) in Raum 1.45 (Auditorium Maximum) in Haus 8;' );
        open_li( '', 'nachmittags (Podiumsdiskussion) in Hörsaal 0.09 in Haus 11.' );
      close_ul();

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
          Teilnehmende Schulklassen bitten wir um Anmeldung bis zum 31.05. an
          ' . html_span( '', html_obfuscate_email( 'klimatag@physik.uni-potsdam.de' ) ) . '
          unter Angabe von
        ';
        open_ul( 'qquadl medpadb' );
          open_li( '', 'Teilnehmerzahl (circa)' );
          open_li( '', 'Teilnahme am Nachmittagsprogramm: ja oder nein' );
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

open_div('bigskps'
, 'Die Veranstaltung wird organisiert von '
  . alink_person_view( 'gn=dieter,sn=neher' )
  . ' und '
  . alink_person_view( 'gn=frank,sn=spahn' )
  . '.'
);


close_ccbox();

?>
