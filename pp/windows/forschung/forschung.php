<?php

echo tb( inlink( 'gruppen', 'text='.we('Research groups at the institute','Gruppen am Institut') ) );

$publications = sql_publications( 'orderby=year,ctime' );
echo tb( inlink( 'publikationen', 'text='.we('...more publications','...weitere Publikationen') ) );

?>
