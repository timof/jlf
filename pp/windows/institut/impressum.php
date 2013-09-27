<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Imprint','Impressum') );

open_div('headline', we('Address:','Adresse:') );
  echo html_tag( 'address', ''
  ,   html_tag( 'p', '', "Universit{$aUML}t Potsdam, Campus Golm")
    . html_tag( 'p', '', "Institut f{$uUML}r Physik und Astronomie (Haus 28)")
    . html_tag( 'p', '', "Karl-Liebknecht-Stra{$SZLIG}e 24/25")
    . html_tag( 'p', '', '14476 Potsdam-Golm')
    . html_tag( 'p', '', 'Germany')
  );

echo tb( we('Head of the Institute:',"Gesch{$aUML}ftsf{$uUML}hrender Leiter:")
       , alink_person_view( 'board=executive,function=chief', 'office' ) );

echo tb( we('Web admin:','Webadministrator:')
       , alink_person_view( 'board=special,function=admin', 'office' ) );

?>
