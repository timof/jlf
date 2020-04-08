<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('PhD Program','Promotionsstudium' ) );

echo tb(
  html_alink(
    'http://www.uni-potsdam.de/studium/zugang/promotion.html'
  , 'class=href outlink,text='.we('Information on and Enrollment for the PhD Program at the University of Potsdam', "Informationen und Einschreibung zum Promotionsstudium an der Universit{$aUML}t Potsdam")
  )
);

echo tb(
  html_alink(
    'http://www.uni-potsdam.de/mnfakul/forschung/promotion-und-habilitation/promotion.html'
  , "class=href outlink,text=Informationen der Fakult{$aUML}t zur Promotion"
  )
);

echo tb( html_tag( 'a', 'href=http://www.uni-potsdam.de/mnfakul/die-fakultaet/gremien/promotionsausschuss.html,class=href outlink', 'Promotionsausschuss der Fakultät' ) );

echo tb( html_alink( 'http://www.app.physik.uni-potsdam.de/phd.html', array(
           'class' => 'href outlink'
         , 'text' => we('Structured Doctoral Training in Astrophysics', 'Strukturierte Doktorandenausbildung in Astrophysik')
         ) )
       , we( "a common and lasting framwork for supervision and training of doctorate students
              in astronomy and astrophysics at Universit{$aUML}t Potsdam, Leibniz-Institute for
              Astrophysics Potsdam (AIP) and DESY/Zeuthen"
           , "ein gemeinsamer Rahmen zur Betreuung und Ausbildung von Promivierenden in Astronomie
              und Astrophysik an der Universit{$aUML}t Potsdam, am Leibniz-Institut f{$aUML}r Astrophysik Potsdam (AIP)
              und am DESY (Zeuthen)")
     );

echo tb( we('course directories','Vorlesungsverzeichnisse'), array(
//  alink_document_view( array( 'type' => 'VVZ', 'flag_current', 'programme_flags &=' => PROGRAMME_PHD ), 'format=list,default=' )
  we( 'Compilation of course directories as .pdf has been discontinued.
       Current course directories are available online via '
     , 'Das kommentierte Vorlesungsverzeichnis als .pdf wurde letztmalig für das Wintersemester 2019/20 erstellt.
       Aktuelle Vorlesungsverzeichnisse sind nur noch online verfügbar über '
  ) . html_alink( 'http://puls.uni-potsdam.de', array( 'class' => 'href outlink' , 'text' => 'PULS' ) )
, inlink( 'vorlesungsverzeichnisse', array( 'text' => we('Archive: course directories of past years...',"Archiv: Vorlesungsverzeichnisse vergangener Jahre...") ) )
), 'class=smallskipb' );

$list = array();
$list[] = alink_document_view( array( 'type !=' => 'VVZ', 'flag_current', 'programme_flags &=' => PROGRAMME_PHD ), array( 'format' => 'list', 'default' => NULL ) );
$list[] = inlink( 'ordnungen', array( 'text' => we('older versions...',"{$aUML}ltere Fassungen...") ) );
echo tb( we('Current regulations','Aktuelle Ordnungen'), $list, 'class=smallskipb' );

echo tb( inlink( 'themen', array( 'programme_flags' => PROGRAMME_PHD, 'text' => we('Topics for PhD Theses',"Themenvorschl{$aUML}ge f{$uUML}r Doktorarbeiten") ) ) );

?>
