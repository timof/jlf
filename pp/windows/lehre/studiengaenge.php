<?php

sql_transaction_boundary('*');


echo html_tag( 'h1', '', we('Programs offered at the Insitute of Physics','Studiengänge am Institut für Physik') );


echo html_tag( 'h3', '', we('Mono-Bachelor / -Master','Ein-Fach-Bachelor / - Master') );

  echo tb( inlink( 'bsc', 'text='.we('Bachelor program: degree "Bachelor of Science" (BSc)', 'Bachelor Studiengang: Abschluss "Bachelor of Science" (BSc)' ) ) );
  echo tb( inlink( 'msc', 'text='.we('Master program: degree "Master of Science" (MSc)', 'Master Studiengang: Abschluss "Master of Science" (MSc)' ) ) );

echo html_tag( 'h3', '', we('Teacher Training','Lehramtsstudium') );

  echo tb( inlink( 'bed', 'text='.we('Bachelor program: degree "Bachelor of Education" (BEd)', 'Bachelor Studiengang: Abschluss "Bachelor of Education" (BEd)' ) ) );
  echo tb( inlink( 'med', 'text='.we('Master program: degree "Master of Education" (MEd)', 'Master Studiengang: Abschluss "Master of Education" (MEd)' ) ) );
  
echo html_tag( 'h3', '', we('Other Degree Programs','Andere Studiengänge') );

  echo tb( inlink( 'phd', 'text='.we('PhD Program','Promotionsstudium') ) );
  echo tb( inlink( 'diplom', 'text='.we('Diploma/Magister Program','Diplom-/Magisterstudium') ) );
  


?>
