<?php // uninav.php - last modified:  20171004.081518utc  by: root@uranos

echo html_map( array(
  'id' => 'unimap'
, array( 'shape' => 'circle', 'coords' => '5,30,14'
  , 'title' => we('Law School','Juristische Fakultät')
  , 'href' => we( URL_FAKJURA_E, URL_FAKJURA_D )
  , 'onmouseover' => 'moverjura();'
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'circle', 'coords' => '3,57,14'
  , 'title' => we('Faculty of Arts','Philosophische Fakultät')
  , 'href' => we( URL_FAKPHIL_E, URL_FAKPHIL_D )
  , 'onmouseover' => 'moverphil();'
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'circle', 'coords' => '9,79,14'
  , 'title' => we('Faculty of Human Sciences','Humanwissenschaftliche Fakultät')
  , 'href' => we( URL_FAKHUM_E, URL_FAKHUM_D )
  , 'onmouseover' => 'moverhum();'
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'circle', 'coords' => '27,97,14'
  , 'title' => we('Faculty of Economics and Social Sciences','Wirtschafts- und Sozialwissenschaftliche Fakultät')
  , 'href' => we( URL_FAKWISO_E, URL_FAKWISO_D )
  , 'onmouseover' => 'moverwiso();'
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'circle', 'coords' => '52,103,14'
  , 'title' => we('Faculty of Mathematics and Sciences','Mathematisch-Naturwissenschaftliche Fakultät')
  , 'href' => we( URL_FAKMATNAT_E, URL_FAKMATNAT_D )
  , 'onmouseover' => 'movermatnat();'
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'circle', 'coords' => '55,46,45'
  , 'title' => we('University of Potsdam','Universität Potsdam')
  , 'href' => 'http://www.uni-potsdam.de'
  , 'onmouseover' => 'moverdefault();'
  , 'onmouseout' => 'mooseout();'
  )
) );

echo html_tag( 'img', 'src=/pp/img/fakmatnat.gif,id=unilogo,usemap=#unimap,alt=Logo der Universität Potsdam' );

?>
