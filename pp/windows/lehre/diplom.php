<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Diploma and Magister Programme', 'Diplom- und Magisterstudiengang' ) );

open_span( 'bigskips qquads comment', we(
  "The diploma programme is discontinued - enrollment for the diploma programme is no longer available"
, "Der Diplomstudiengang l{$aUML}uft aus - Einschreibung zum Diplomstudium im Fach Physik ist an der Universist{$aUML}t Potsdam nicht mehr m{$oUML}glich!"
) );

echo html_tag( 'h2', '', we('Information and guidance for diploma students', 'Informationen und Beratung zum Diplomstudiengang' ) );


echo tb( we('Course guidance for students in diploma programme',"Studienfachberatung Physik f{$uUML}r Studierende im Diplomstudiengang")
       , alink_person_view( 'people_id!=0,board=guidance,function=mono', 'office=1,format=list' )
);

echo tb( we('course directories','Vorlesungsverzeichnisse'), array(
  alink_document_view( array( 'type' => 'VVZ', 'flag_current', 'programme_id &=' => PROGRAMME_DIPLOM ), 'format=list,default=' )
, inlink( 'vorlesungsverzeichnisse', array( 'text' => we('Archive: course directories of past years...',"Archiv: Vorlesungsverzeichnisse vergangener Jahre...") ) )
), 'class=smallskipb' );

$list = alink_document_view( array( 'type !=' => 'VVZ', 'flag_current', 'programme_id &=' => PROGRAMME_DIPLOM ), array( 'format' => 'list', 'default' => NULL ) );
$list[] = inlink( 'ordnungen', array( 'text' => we('older versions...',"{$aUML}ltere Fassungen...") ) );
echo tb( we('Current regulations','Aktuelle Ordnungen'), $list, 'class=smallskipb' );
echo tb( we('Course regulations for diploma programme',"Pr{$uUML}fungsordnung Diplomstudiengang")
       , alink_document_view( array( 'type' => 'PO', 'programme_id &=' => PROGRAMME_DIPLOM ), 'format=latest' )
);

// echo tb( we( 'Equivalent courses for students in diploma programme', "{$AUML}quivalente Veranstaltungen f{$uUML}r Studierende im Diplomstudiengang" )
//         , alink_document_view( array( 'type' => 'INFO', 'tag' => 'equiv_dipl', 'programme_id &=' => PROGRAMME_DIPLOM ), 'format=latest' )
// );
// 
// echo tb( we('Course directory',"Vorlesungsverzeichnis")
//        , alink_document_view( array( 'type' => 'VVZ' ), 'format=latest' )
// );

echo tb( inlink( 'themen', array( 'programme_id' => PROGRAMME_DIPLOM, 'text' => we('Topics for Diploma Theses',"Themenvorschl{$aUML}ge f{$uUML}r Diplomarbeiten") ) ) );


echo html_tag( 'h2', '', we('Examiners for diploma programme', "Pr{$uUML}fer f{$uUML}r Diplomstudiengang Physik" ) );

// we hardcode these as they will only be listed for a short period as the diploma programme is running out

echo html_tag( 'h3', '', we('Intermediate examination', "Vorpr{$uUML}fung" ) );

  
echo tb( we( 'Experimental physics', 'Experimentalphysik' ), array(
  alink_person_view( 'cn=matias bargheer' )
, alink_person_view( 'cn=carsten beta' )
, alink_person_view( 'cn=reimund gerhard' )
, alink_person_view( 'cn=ralf menzel' )
, alink_person_view( 'cn=dieter neher' )
, alink_person_view( 'cn=philipp richter' )
, alink_person_view( 'cn=svetlana santer' )
) );

echo tb( we( 'Theoretical physics', 'Theoretische Physik' ), array(
  alink_person_view( 'cn=achim feldmeier' )
, alink_person_view( 'cn=arkadi pikovski' )
, alink_person_view( 'cn=norbert seehafer' )
, alink_person_view( 'cn=frank spahn' )
, alink_person_view( 'cn=martin wilkens,title=prof. dr.' )
) );


echo html_tag( 'h3', '', we('Final examination', "Hauptpr{$uUML}fung" ) );


echo tb( we( 'Experimental physics', 'Experimentalphysik' ), array(
  alink_person_view( 'cn=matias bargheer' )
, alink_person_view( 'cn=carsten beta' )
, alink_person_view( 'cn=reimund gerhard' )
, alink_person_view( 'cn=ralf menzel' )
, alink_person_view( 'cn=dieter neher' )
, alink_person_view( 'cn=dieter neher' )
, alink_person_view( 'cn=philipp richter' )
, alink_person_view( 'cn=svetlana santer' )
) );

echo tb( we( 'Theoretical physics', 'Theoretische Physik' ), array(
  alink_person_view( 'cn=achim feldmeier' )
, alink_person_view( 'cn=carsten henkel' )
, alink_person_view( 'cn=arkadi pikovski' )
, alink_person_view( 'cn=norbert seehafer' )
, alink_person_view( 'cn=frank spahn' )
, alink_person_view( 'cn=martin wilkens,title=prof. dr.' )
) );

echo html_tag( 'h3', '', we('Required option I', "Wahlpflichtfach I" ) );

echo tb( we( 'Astrophysics', 'Astrophysik' ), array(
  alink_person_view( 'cn=achim feldmeier' )
, alink_person_view( 'cn=wolf-rainer hamann' )
, alink_person_view( 'cn=gottfried mann' )
, alink_person_view( 'cn=martin karl wilhelm pohl' )
, alink_person_view( 'cn=philipp richter' )
, alink_person_view( 'cn=günther rüdiger' , 'default=apl. Prof. Dr. Günther Rüdiger' )
, alink_person_view( 'cn=matthias steinmetz', 'default=Prof. Dr. Matthias Steinmetz' )
, alink_person_view( 'cn=klaus strassmeier', 'default=Prof. Dr. Klaus Strassmeier' )
, alink_person_view( 'cn=lutz wisotzki', 'default=Prof. Dr. Lutz Wisotzki' )
) );

echo tb( we( 'Nonlinear dynamics', 'Nichtlineare Dynamik' ), array(
  alink_person_view( 'cn=markus abel' )
, alink_person_view( 'cn=fred feudel' )
, alink_person_view( 'cn=matthias holschneider', 'default=Prof. Dr. Matthies Holschneider' )
, alink_person_view( 'cn=arkadi pikovski' )
, alink_person_view( 'cn=michael rosenblum' )
, alink_person_view( 'cn=norbert seehafer' )
, alink_person_view( 'cn=frank spahn' )
) );

echo tb( we( 'Solid State Physics', "Festk{$oUML}rperphysik" ), array(
  alink_person_view( 'cn=matias bargheer' )
, alink_person_view( 'cn=carsten beta' )
, alink_person_view( 'cn=reimund gerhard' )
, alink_person_view( 'cn=reinhard lipowsky')
, alink_person_view( 'cn=helmuth möhwald' )
, alink_person_view( 'cn=dieter neher' )
, alink_person_view( 'cn=svetlana santer' )
) );

echo tb( we( 'Photonics', "Photonik" ), array(
  alink_person_view( 'cn=ralf menzel' )
, alink_person_view( 'cn=wolfgang regenstein' )
) );

echo tb( we( 'Quantum Theory', "Quantentheorie" ), array(
  alink_person_view( "cn=johannes blümlein", "default=Johannes Bl{$uUML}mlein" )
, alink_person_view( 'cn=carsten henkel' )
, alink_person_view( 'cn=tord riemann', 'default=Dr. Tord Riemann' )
, alink_person_view( 'cn=bernhard frederick schutz', 'default=Prof. Dr. Bernhard Frederick Schutz' )
, alink_person_view( 'cn=martin wilkens,title=prof. dr.' )
) );

echo tb( we( 'Climate Physics', "Klimaphysik" ), array(
  alink_person_view( 'cn=klaus dethloff', 'default=Klaus Dethloff' )
// RIP: , alink_person_view( 'cn=siegfried franck', 'default=Siegfried Franck' )
, alink_person_view( 'cn=anders levermann', 'default=Anders Levermann' )
, alink_person_view( 'cn=stefan rahmstorf', 'default=Stefan Rahmstorf' )
) );


echo html_tag( 'h3', '', we('Required option II', "Wahlpflichtfach II" ) );

echo tb( we( 'Material Science', "Materialwissenschaft" ), array(
  alink_person_view( 'cn=matias bargheer' )
, alink_person_view( 'cn=burkhard schulz', "default=Dr. Burkhard Schulz" )
) );

echo tb( we( 'Environmental Science', "Umweltwissenschaften" ), array(
  alink_person_view( 'cn=hans joachim schellnhuber', 'default=Prof. Dr. Hans Joachim Schellnhuber' )
) );

echo tb( we( 'Electronics', "Elektronik" ), array(
  alink_person_view( 'cn=dieter neher' )
) );

echo html_tag( 'h2', '', we('Examiners for magister programme', "Pr{$uUML}fer f{$uUML}r Magisterstudiengang mit Fach Physik" ) );

echo html_tag( 'h3', '', we('Intermediate examination', "Vorpr{$uUML}fung" ) );

echo tb( we('like intermediate examination in diploma programme; additionally:', "wie Vorpr{$uUML}fung im Diplomstudiengang; zus{$aUML}tzlich:" ), array(
  alink_person_view( 'cn=wolfgang regenstein' )
, alink_person_view( 'cn=michael rosenblum' )
) );

echo html_tag( 'h3', '', we('Final examination', "Hauptpr{$uUML}fung" ) );

echo tb( we('like final examination in diploma programme (see above); additionally:', "wie Hauptpr{$uUML}fung im Diplomstudiengang (siehe oben); zus{$aUML}tzlich:" ), array(
  alink_person_view( 'cn=wolfgang regenstein' )
, alink_person_view( 'cn=fred feudel' )
, alink_person_view( 'cn=michael rosenblum' )
) );

?>
