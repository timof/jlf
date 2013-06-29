<?php


echo html_tag( 'a'
, array(
    'class' => 'inline_block medskips qquads'
  , 'href' => inlink( 'lehre', 'context=url' )
  )
, html_div( 'huge bold smallskips a', we('Studying at the Institute','Studium am Institut') )
  . html_tag( 'img', 'width=300,src=/pp/fotos/in_the_lab.jpg' )
);



echo html_tag( 'a'
, array(
    'class' => 'inline_block medskips'
  , 'href' => inlink( 'forschung', 'context=url' )
  )
, html_div( 'huge bold smallskips', we('Research at the Institute','Forschung am Institut') )
  . html_tag( 'img', 'width=300,src=/pp/fotos/in_the_lab.jpg' )
);


echo html_tag( 'h2','bigskipt', we('News','Aktuelles') );

echo html_div( '', inlink( 'aktuelles', 'text='.we('more news...','weitere Meldungen...') ) );

?>
