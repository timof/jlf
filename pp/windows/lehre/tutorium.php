<?php

echo html_tag( 'h1', '', 'Tutorium' );

echo tb( html_alink( 'http://www.physikfachschaft.de', 'a href outlink,text=Fachschaft Mathe/Physik' )
, we('Representation of physics and mathematics students',
     'Studentische Vertretung der Physik- und Mathematikstudenten, Erstsemestlerinfo, Veranstaltungen')
);

echo tb( html_alink( 'http://www.stupa.uni-potsdam.de', 'a href outlink,text='.we('Student Parliament','Studierendenparlament der Universitaet Potsdam') )
, we('Student representation at university level','Studierendenvertretung auf Universitaetsebene')
);

echo tb( html_alink( 'http://www.asta.uni-potsdam.de', 'a href outlink,text=Allgemeiner Studierendenausschuss (AStA)' )
, we('Executive committee of the Student Parliament','Exekutive des Studierendenparlaments')
);

?>
