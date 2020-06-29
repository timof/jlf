<?php // /pp/windows/menu/menu.php

sql_transaction_boundary('*');

// open_div('large bold,style=padding:2em;background-color:#ffff88;color:#ff0000;margin-bottom:2em;');
//   open_div('bigpadb');
//     open_tag( 'h1', 'red', we('Dear Students,', 'Liebe Studierende,' ) );
//     
//     open_tag( 'p', '', we('
//       the lecture period of summer term 2020 will start on Monday April 20;
//       however, no face-to-face teaching will be possible for the time being and online formats
//       will be used instead: videos, documents in Moodle,
//       Zoom-sessions, handing in of scanned excercise sheets, etc.
//     ','
//       der Vorlesungszeitraum des Sommersemesters 2020 beginnt am Montag, dem
//       20. April 2020, allerdings ohne Präsenzveranstaltungen.
//       Die Lehre wird bis auf weiteres in verschiedenen Online-Formaten
//       angeboten: Videos, kommentierte pdfs auf Moodle, Zoom-Sitzungen,
//       Rückgabe eingescannter Übungsblätter etc.
//     ') );
//     
//     open_tag( 'p', '', we('
//     ','
//     ') );
//     
//     open_tag( 'p', '', we('
//       Information on individual courses can be found on '
//         .html_alink( 'https://puls.uni-potsdam.de', 'text=PULS,class=href outlink large bold' ).
//         ' and in '
//         .html_alink( 'https://moodle.uni-potsdam.de', 'text=MOODLE,class=href outlink large bold' ).
//         ', as well as on the web pages of the respective '
//           .inlink('professuren', 'class=large bold,text=teachers' ).
//         ' or their '
//           .inlink('forschung', 'class=large bold,text=groups' ).
//         '.
//     ','
//       Wir bitten Sie, sich auf '
//         .html_alink( 'https://puls.uni-potsdam.de', 'text=PULS,class=href outlink large bold' ).
//         ' im Kommentarfeld der jeweiligen Veranstaltung sowie bei '
//         .html_alink( 'https://moodle.uni-potsdam.de', 'text=MOODLE,class=href outlink large bold' ).
//         ' zu informieren. Bitte besuchen Sie auch die Webseiten der jeweiligen '
//           .inlink('professuren', 'class=large bold,text=Dozenten' ).
//         ' oder deren '
//           .inlink('forschung', 'class=large bold,text=Arbeitsgruppen' ).
//         ' für weitere Informationen.
//     ') );
//     
//     open_tag( 'p', '', we('
//       Please register for courses as soon as possible via '
//         .html_alink( 'https://puls.uni-potsdam.de', 'text=PULS,class=href outlink large bold' ).
//      ' so teachers can "see" you and contact you by email (registration starts on April 20).
//     ','
//       Melden Sie sich bitte nach Beginn der Einschreibefrist am 20.04. so bald wie möglich über '
//         .html_alink( 'https://puls.uni-potsdam.de', 'text=PULS,class=href outlink large bold' ).
//       ' an, damit die Veranstalter*innen Sie "sehen" und Ihnen email schicken können.
//     ') );
//   close_div();
// 
//   open_tag( 'h1', 'red', we('Current Information related to Corona Pandemic','Aktuelle Informationen im Zusammenhang mit der Corona-Pandemie') );
//   open_ul();
//     // open_li( '', alink_document_view( 'tag=corona20200316' ) );
//     open_li( ''
//     , we(
//         'Information on the consequences of the corona crisis on studies and teaching: '
//       , 'Informationen zu den Auswirkungen der Corona-Krise auf Studium und Lehre: '
//       ) . html_alink( 'https://www.uni-potsdam.de/studium/corona', 'class=href outlink large,text=https://www.uni-potsdam.de/studium/corona' )
//     );
//     open_li( ''
//     , we(
//         'General Information from the University can be found on the university web page: '
//       , 'Allgemeine Informationen zu Auswirkungen der Corona-Pandemie finden sie auf der Webseite der  Universität: '
//       ) . html_alink( 'https://www.uni-potsdam.de', 'class=href outlink large,text=https://www.uni-potsdam.de' )
//     );
//     open_li( '', alink_document_view( 'tag=coronaRegeln', 'large' ) );
//   close_ul();
// close_div();

open_div('id=teaser');
  open_div( 'class=overlay,id=i1', image( 'lehre', 'alt=' ) );
  open_div( 'class=overlay,id=i2', image( 'forschung', 'alt=' ) );
  open_div( 'class=overlay init,id=i0', image( 'h28' ) );

  open_div('linkbox');
    echo inlink( 'lehre', array(
      'class' => 'link'
    , 'text' => we('Studies','Studium')
    , 'attr' => array( 'onmouseover' => 'start_rl(1);', 'onmouseout' => 'start_rl(0)' )
    ) );
    echo inlink( 'forschung', array(
      'class' => 'link'
    , 'text' => we('Research','Forschung')
    , 'attr' => array( 'onmouseover' => 'start_rl(2);', 'onmouseout' => 'start_rl(0)' )
    ) );
  close_div();
close_div();

open_div( 'schnelleinstieg bigskips' );
  open_div( 'inline_block' );
    echo html_div( 'class=inline_block qqskipr,style=vertical-align:top;', we('for prospective Students:',"für Studieninteressierte:") );
    open_ul();
      open_li( '', inlink( 'studiengaenge', array( 'text' => we('Degree programs',"Studienangebot") ) ) );
      open_li( '', inlink( 'forschung', array( 'text' => we('Research areas',"Forschungsschwerpunkte") ) ) );
    close_ul();
  close_div();

close_div();



// news ticker
//
$items = array();

$items[] = html_span( 'tickerline'
, we( 'Information regarding coronavirus pandemic: ', 'Informationen zur Coronavirus-Pandemie: ' )
  . html_alink( 'https://www.uni-potsdam.de/presse/aktuelles/coronavirus', 'class=href outlink,text=https://www.uni-potsdam.de/presse/aktuelles/coronavirus' )
);


// $items[] = html_span( 'tickerline',
//   html_span( 'bold red', 'abgesagt: ' )
//   . html_alink( '/klimatag', 'class=href inlink,text=Thementag Wissenschaft und Klimawandel am 30.03.' )
// );
//   '11.05.: '
//   . html_alink( 'http://marchforscience.de/auch-in-deiner-stadt/potsdam', array( 'class' => 'href outlink', 'text' => 'March for science' ) )
// $items[] = html_span( 'tickerline',
//   '11.05.: '
//   . html_alink( 'http://marchforscience.de/auch-in-deiner-stadt/potsdam', array( 'class' => 'href outlink', 'text' => 'March for science' ) )
//   . ' zum '
//   . html_alink( 'http://www.potsdamertagderwissenschaften.de', array( 'class' => 'href outlink', 'text' => 'Tag der Wissenschaften' ) )
//   . ' in Potsdam'
//   );

$events = sql_events(
  array( 'flag_ticker', 'flag_publish', array( '||', 'date=0', "date>=$today_canonical" ) )
, 'orderby=date'
);
foreach( $events as $r ) {
  $items[] = event_view( $r, 'format=ticker' );
}
// $items[] = html_span( 'tickerline bold red', inlink( 'intro', 'text=ab 30.09.: Brückenkurs "Auffrischung Mathe fur Studienanfänger*innen"' ) );
// $items[] = html_span( 'tickerline', inlink( 'studiengaenge', array( 'text' => we('Information for prospective students: degree programs at the institute', "Informationen f{$uUML}r Studieninteressierte: Studiengänge am Institut" ) ) ) );
// $items[] = html_span( 'tickerline', alink_document_view( 'type=VVZ,flag_current', 'format=latest' ) );


open_ccbox( '', we('News','Aktuelles') );

  foreach( $items as $r ) {
    echo html_div( 'tickeritem', "+++$NBSP$NBSP$r$NBSP$NBSP+++" );
  }
  echo html_div( 'smallskipt', inlink( 'veranstaltungsarchiv', 'text='.we('more events...','Veranstaltungsarchiv...') ) );

close_ccbox();


// highlights
//
$items = array();
$highlights = sql_highlights(
  array( 'flag_publish' )
, array( 'limit_from' => 1, 'limit_count' => 3, 'orderby' => 'ctime DESC' )
);
foreach( $highlights as $r ) {
  $items[] = highlight_view( $r, 'format=highlight' );
}
if( $items ) {
  open_ccbox( '', we('Highlights','Aus dem Institut') );
    foreach( $items as $r ) {
      echo $r;
    }
  close_ccbox();
}



// publications --- currently unused
//
// $publications = sql_publications(
//   array(
//     'year >= '=> ( $current_year - 1 )
//   , 'groups.flag_publish'
//   )
// , array( 'limit_from' => 1 , 'limit_count' => 20 , 'orderby' => 'year DESC, ctime DESC' )
// );
$publications = array();
if( count( $publications ) >= 3 ) {
  shuffle( $publications );
  open_div( 'highlight nopads smallskipt' );
  echo html_tag( 'h2', 'tinyskipb tinypadb', we('Recent Publications','Aktuelle Veröffentlichungen') );
  $n = 0;
  foreach( $publications as $pub ) {
    if( ++$n > 3 ) {
      break;
    }
    open_div( 'highlight tinypads', publication_reference_view( $pub ) );
//     $s = html_span( 'block' );
//     $s .= html_span( 'block cn tinyskipb noskipt nopadt larger bold', $pub['cn'] );
//     $t = $pub['summary'];
//     if( strlen( $t ) > 200 ) {
//       $t = trim( substr( $t, 0, 195 ) ) . '...';
//     }
//     $s .= html_span( 'summary', $t );
//     $s .= html_span( false );
//     echo inlink( 'publikation', array( 'class' => 'href', 'text' => $s, 'publications_id' => $pub['publications_id'] ) );
//     echo html_div( 'tinyskips', we('Research group: ','Arbeitsgruppe: ') . alink_group_view( $pub['groups_id'], 'fullname=1' ) );
//     close_div();
  }
  // echo publication_block_view( $spub );
  echo html_div( 'smallskipt', inlink( 'publikationen', 'text='.we('more publications...','weitere Veröffentlichungen...') ) );
  close_div();
}



?>
