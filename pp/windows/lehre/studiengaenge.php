<?php

sql_transaction_boundary('*');


echo html_tag( 'h1', '', we('Programs offered at the Insitute of Physics','Studiengänge am Institut für Physik') );


echo html_tag( 'h3', '', we('Mono-Bachelor / -Master in physics','Ein-Fach-Bachelor / - Master in Physik') );

  echo tb( inlink( 'bsc', 'class=quadl,text='.we('Bachelor program: degree "Bachelor of Science" (BSc)', 'Bachelorstudiengang: Abschluss "Bachelor of Science" (BSc)' ) ) );
  echo tb( inlink( 'msc', 'class=quadl,text='.we('Master program: degree "Master of Science" (MSc)', 'Masterstudiengang: Abschluss "Master of Science" (MSc)' ) ) );

echo html_tag( 'h3', '', we('Teacher Training (with physics as one subject)','Lehramtsstudium mit Fach Physik') );

  echo tb( inlink( 'bed', 'class=quadl,text='.we('Bachelor program: degree "Bachelor of Education" (BEd)', 'Bachelorstudiengang: Abschluss "Bachelor of Education" (BEd)' ) ) );
  echo tb( inlink( 'med', 'class=quadl,text='.we('Master program: degree "Master of Education" (MEd)', 'Masterstudiengang: Abschluss "Master of Education" (MEd)' ) ) );
  
echo html_tag( 'h3', '', we('Other Degree Programs','Andere Studiengänge') );

  echo tb( inlink( 'phd', 'class=quadl,text='.we('PhD Program','Promotionsstudium') ) );
  echo tb( inlink( 'diplom', 'class=quadl,text='.we('Diploma/Magister Program','Diplom-/Magisterstudium') ) );
  


?>
