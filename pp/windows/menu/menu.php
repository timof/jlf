<?php


echo html_tag( 'a'
, array(
    'class' => 'inline_block medskips qquads'
  , 'href' => inlink( 'lehre', 'context=url' )
  )
, html_div( 'huge bold smallskips a', we('Studies','Studium') )
  . html_tag( 'img', 'width=300,src=/pp/fotos/in_the_lab.jpg' )
);



echo html_tag( 'a'
, array(
    'class' => 'inline_block medskips'
  , 'href' => inlink( 'forschung', 'context=url' )
  )
, html_div( 'huge bold smallskips', we('Research','Forschung') )
  . html_tag( 'img', 'width=300,src=/pp/fotos/in_the_lab.jpg' )
);


echo html_tag( 'h2','bigskipt', we('News','Aktuelles') );
echo html_div( '', inlink( 'aktuelles', 'text='.we('more news...','weitere Meldungen...') ) );

$publications = sql_publications(
  'year >= '.( $current_year - 1 )
, array( 'limit_from' => 1 , 'limit_to' => 3 , 'orderby' => 'year DESC, ctime DESC' )
);
if( count( $publications ) >= 2 ) {
  echo html_tag( 'h2','bigskipt', we('Current Publications','Aktuelle Veröffentlichungen') );
  echo publication_columns_view( $publications );
  echo html_div( '', inlink( 'publikationen', 'text='.we('more publications...','weitere Veröffentlichungen...') ) );
}


?>
