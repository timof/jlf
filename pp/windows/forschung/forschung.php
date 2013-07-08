<?php

echo html_tag( 'h4', 'medskips', we('Key areas','Forschungsschwerpunkte') );
require_once('./pp/windows/forschung/schwerpunkte.php' );
open_div( 'medskips', inlink( 'gruppen', 'class=href smallskipt inlink,text='.we('Research Groups...','Arbeitsgruppen...') ) );


echo html_tag( 'h4', 'medskips', we('Recent Research Highlights','Aktuelle Forschungshighlights') );

publicationslist_view( '', array( 'list_options' => 'allow_download=1,orderby=year' ) );

open_div( 'medskips', inlink( 'publicationslist', 'class=href smallskipt inlink,text='.we('more publications...','weitere Veröffentlichungen..') ) );



echo html_tag( 'h4', 'medskips', we('Open positions','Offene Stellen / Themen für Abschlussarbeiten') );

positionslist_view( '', array( 'list_options' => 'allow_download=1' ) );

open_div( 'medskips', inlink( 'positionslist', 'class=href smallskipt inlink,text='.we('more positions...','weitere Stellen/Themen...') ) );


?>
