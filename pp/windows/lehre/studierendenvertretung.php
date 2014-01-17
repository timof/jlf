<?php

echo html_tag( 'h1', '', we('Student representation','Studierendenvertretung') );

echo tb( html_alink( 'http://www.fsr.physik.uni-potsdam.de/doku.php', 'a href outlink,text=Fachschaft Mathe/Physik' )
, we('Representation of physics and mathematics students',
     "Studentische Vertretung der Physik- und Mathematikstudierenden")
);

echo tb( html_alink( 'http://www.stupa.uni-potsdam.de', 'a href outlink,text='.we('Student Parliament',"Studierendenparlament der Universit{$aUML}t Potsdam") )
, we('Student representation at university level',"Studierendenvertretung auf Universit{$aUML}tsebene")
);

echo tb( html_alink( 'http://www.asta.uni-potsdam.de', 'a href outlink,text=Allgemeiner Studierendenausschuss (AStA)' )
, we('Executive committee of the Student Parliament','Exekutive des Studierendenparlaments')
);

?>
