<?php

sql_transaction_boundary( '*' );
// sql_transaction_boundary( 'rooms,owning_group=groups,contact=people,contact2=people' );

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('h28innenhof');
    echo html_tag( 'h1', '', we('Institute / Workshop','Institut / Werkstatt') );
  close_div();
close_div();

$group = sql_one_group( 'acronym=werkstatt', 0 );
$head_link = '(tba)';
if( $group ) {
  if( ( $h_id = $group['head_people_id'] ) ) {
    $head_link = alink_person_view( $h_id );
  }
}

if( $GLOBALS['language'] == 'D' ) {

  open_ccbox( '', 'Feinmechanische Werkstatt' );

  open_div( 'smallskips', "
    Die feinmechanische Werkstatt der Physik wird vom Institut fuer Physik und
    Astronomie und von den Erd - und Umweltwissenschaften betrieben.
    Alle anderen Institute der Math.-Nat. Fakultät können Aufträge an
    die Werkstatt geben. Hier erklären wir Ihnen kurz die Möglichkeiten der Werkstatt
    und die Wege zur Auftragserteilung.
  " );

    open_tag( 'h3', '', 'Was bieten wir an?' );
    open_div( 'smallskipb', "
      Wir sind Partner für alle feinmechanischen Arbeiten. Unsere maschinelle
      Ausstattung umfasst: Standbohrmaschinen, Kantbank, Tafelblechschere, Bandsäge, Kreissäge,
      Schleifmaschinen, Drehbänke, (CNC)-Fräsmaschinen, Sandstrahlkabine,
      Kunststoff 3D-Drucker, Laserschneider für Kunstoffe
    " );
    
    open_tag( 'h3', '', 'Wie können Sie Ihre Idee umsetzen?' );
    open_div( 'smallskipb' );
      open_div( '', " Fuellen Sie bitte folgendes Formular aus: " . alink_document_view( 'flag_current,flag_publish,tag=de_27b_6' ) . '.' );
      open_div( '', " Das Formular geben Sie abhängig von Ihrem Institut
                      bei folgenden Personen ab und besprechen die Details der Ausführung:" );
      open_ul();
        open_li( '', 'Aus dem Institut für Physik:
                      Formular bei den Ansprechnpartnern der Werkstatt in den Arbeitgruppen abgeben' );
        open_li( '', 'Aus dem Institut für Erd- und Umweltwissenschaften: Formular abgeben bei Herrn Jens Bölke
                      (jens.bölke@uni-potsdam.de), Haus 2 Raum 0.36 Tel.: 5816' );
        open_li( '', 'Aus allen anderen Instituten der Math.-Nat. Fakultät:
                      Kleinere Aufträge bis zwei Stunden: Formular abgeben bei Mitarbeitern der Werkstatt Haus 27 Raum 0.010;
                      Größere Aufträge: Formular abgeben bei ' . $head_link );
      close_ul();
      echo 'Noch Fragen? Kontaktieren Sie bitte ' . $head_link;
    close_div();

  close_ccbox();

} else { /* if( $GLOBALS['language'] == 'E' ) */

  open_ccbox( '', 'Mechanical Workshop' );
  
    open_div( 'smallskipb', "
      The mechanical physics-workshop is operated by the Institutes of Physics
      and Astronomy and earth- and environmental sciences. It is open to all
      other institutes in the Math.-Nat. Faculty. On this page we briefly explain
      its capabilities as well as the path to get machined parts.
    " );
  
    open_tag( 'h3', '', 'What do we offer' );
    open_div( 'smallskipb', "
       We are partners for all mechanical machining. Our machines include:
       Drill press, sheet metal cutter, band saw, buzz saw, grinding machine,
       lathes, (CNC)-mills, sand-blast cabin, plastic 3D printer, laser cutter for plastic.
     " );
  
    open_tag( 'h3', '', 'How can you realize your parts?' );
    open_div( 'smallskipb' );
     open_div( '', "Please compile the following form: " . alink_document_view( 'flag_current,flag_publish,tag=en_27b_6' ) . '.' );
     open_div( '', "  Depending on your institution, we have different contact points for talking
                      about your order and handing in your form:" );
     open_ul();
       open_li( '', 'For the Institute for Physics and Astronomy:
                     Please hand the form to the workshop-contact persons in the groups' );
       open_li( '', 'For the Institute for Earth- and Environmental Sciences:
                     Please hand the form to Jens Bölke (jens.bölke@uni-potsdam.de), Haus 27, Raum 0.36 Tel.: 5816' );
       open_li( '', 'For all other Institutes of the Math.-Nat. Faculty:
                     small parts up to an estimated workload of 2 hours: Please hand the form to any member of the mechanical workshop Haus 27 Raum 0.010;
                     Larger Items: Please hand the form to ' . $head_link );
    close_ul();    
    echo 'Any questions? Please contact ' . $head_link;
  
  close_ccbox();

}


