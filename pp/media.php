<?php

$media = array(
   'ufakjuralogo' => '/pp/img/fakjura.gif'
 , 'ufakphil1logo' => '/pp/img/fakphil1.gif'
 , 'ufakphil2logo' => '/pp/img/fakphil2.gif'
 , 'ufakwisologo' => '/pp/img/fakwiso.gif'
 , 'ufakmatnatlogo' => '/pp/img/fakmatnat.gif'
 , 'ufaknulllogo' => '/pp/img/faknull.gif'
// 
// , 'ufakjurabanner' => '/pp/img/bannerjura.gif'
// , 'ufakphil1banner' => '/pp/img/bannerphil1.gif'
// , 'ufakphil2banner' => '/pp/img/bannerphil2.gif'
// , 'ufakwisobanner' => '/pp/img/bannerwiso.gif'
// , 'ufakmatnatbanner' => '/pp/img/bannermatnat.gif'
// , 'ufaknullbanner' => '/pp/img/bannernull.gif'
// 
// , 'fakinstmath' => '/pp/img/bmath.gif'
// , 'fakinstinfo' => '/pp/img/binfo.gif'
// , 'fakinstphys' => '/pp/img/bphysastro.gif'
// , 'fakinstchem' => '/pp/img/bchem.gif'
// , 'fakinstbio' => '/pp/img/bbio.gif'
// , 'fakinstfood' => '/pp/img/bfood.gif'
// , 'fakinstggraph' => '/pp/img/bggraph.gif'
// , 'fakinstgoek' => '/pp/img/bgoek.gif'
// , 'fakinstgwiss' => '/pp/img/bgwiss.gif'
// , 'fakinstnull' => '/pp/img/bnull.gif'
// , 'fakinstdots' => '/pp/img/bdots.gif'

// , 'outlinkover' => '/img/outlink.over.gif'
// // , 'outlinkout' => '/img/outlink.gif'
// , 'inlinkover' => '/img/inlink.over.gif'
// , 'inlinkout' => '/img/inlink.gif'
// , 'fileover' => '/img/file.over.gif'
// , 'fileout' => '/img/file.gif'
// , 'letterover' => '/img/letter.over.gif'
// , 'letterout' => '/img/letter.gif'
);

open_div('nodisplay');
  foreach( $media as $tag => $src ) {
    $id = 'i'.new_html_id();
    echo html_tag( 'img', "id=$tag,src=$src" );
  }
close_div();

?>
