<?php

echo html_tag( 'h2','', we('Studying Physics at the Institute','Physikstudium am Institut') );

echo html_tag( 'h2','bigskipt', we('Research at the Institute','Forschung am Institut') );




echo html_tag( 'h2','bigskipt', we('News','Aktuelles') );

echo html_div( '', inlink( 'news', 'text='.we('more events...','weitere Termine...') ) );

?>
