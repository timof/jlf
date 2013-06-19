<?php

groupslist_view( '', 'allow_download=1' );

echo html_tag( 'h4', 'medskips', we('Recent publications','Aktuelle Publikationen') );

publicationslist_view( '', array( 'list_options' => 'allow_download=1,orderby=year' ) );

echo inlink( 'publicationslist', 'class=href smallskipt inlink,text='.we('more publications','weitere Publikationen') );

?>
