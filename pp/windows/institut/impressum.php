<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', 'Impressum' );

open_div('headline', we('Address:','Adresse:') );
  echo html_tag( 'address', ''
  ,   html_tag( 'p', '', 'Universität Potsdam, Campus Golm')
    . html_tag( 'p', '', 'Institut für Physik und Astronomie (Haus 28)')
    . html_tag( 'p', '', 'Karl-Liebknecht-Straße 24/25')
    . html_tag( 'p', '', '14476 Potsdam-Golm')
    . html_tag( 'p', '', 'Germany')
  );

echo tb( we('Head of the Institute:','Geschäftsführender Leiter:')
       , alink_person_view( 'board=executive,function=chief', 'office' ) );

echo tb( we('Web admin:','Webadministrator:')
       , alink_person_view( 'board=special,function=admin', 'office' ) );

?>
