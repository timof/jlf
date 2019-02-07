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
    echo html_tag( 'img', "id=$tag,src=$src,alt=" );
  }
close_div();

function image( $tag, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $credits = adefault( $opts, 'credits', true );
  switch( $tag ) {
    case 'black':
      return html_img( '/pp/fotos/teaserbck.gif'
      , ''
      , ''
      , array( 'format' => 'gif' )
      );
    case 'h28':
      return html_img( '/pp/fotos/haus28b.jpg'
      , we('Physics Institute - building 28 on university campus Golm', 'Institutsgebäude - Haus 28 am Campus Golm')
      , ( $credits === true ? credits('Karla Fritze') : $credits )
      , $opts
      );
    case 'h28i':
      return html_img( '/pp/img/h28i7.jpg'
      , '' // we('Physics institute - inside view','Physikinstitut - Innenansicht')
      , ( $credits === true ? credits('Karla Fritze') : $credits )
      , $opts
      );
    case 'forschung':
      return html_img( '/pp/fotos/forschung3a.jpg'
      , adefault( $opts, 'alt', we('Working in lab on optical table','Arbeit im Labor am optischen Tisch' ) )
      , ( $credits === true ? credits('Karla Fritze') : $credits )
      , $opts
      );
    case 'lehre':
      return html_img( '/pp/fotos/lehreh27a.jpg'
      , adefault( $opts, 'alt', we('Physics lecture in large lecture hall', 'Physikvorlesung im großen Hörsaal'))
      , ( $credits === true ? credits('Carsten Beta') : $credits )
      , $opts
      );
    case 'gp':
      return html_img( '/pp/fotos/gp.jpg'
      , we( 'Student performing experiment in Lab course','Student beim Experimentieren im Grundpraktikum')
      , ( $credits === true ? credits('Karla Fritze') : $credits )
      , $opts
      );
    case 'fp':
      return html_img( '/pp/fotos/master.jpg'
      , we( 'Laser experiment in advanced lab course','Laserexperiment im Fortgeschrittenenpraktikum')
      , ( $credits === true ? credits('Karla Fritze') : $credits )
      , $opts
      );
    case 'h28innenhof':
      return html_img( '/pp/fotos/innenhof2.jpg'
      ,  we( 'Inner courtyard of the physics building at night','Innenhof des Physikgebäudes bei Nacht' )
      , ( $credits === true ? credits('David Gruner') : $credits )
      , $opts
      );
    case 'tutorium':
      return html_img( '/pp/fotos/tutorium5.jpg'
      , we( 'Students in tutorial class working on blackboard','Studierende im Tutorium bei der Arbeit an der Tafel')
      , ( $credits === true ? credits('Ines Mayan') : $credits )
      , $opts
      );
    case 'mint':
      return html_img( '/pp/fotos/mint2.jpg'
      , we( 'Group of Students working in MINT session','Gruppe von Studierenden im MINT-Raum')
      , ( $credits === true ? credits('Karla Fritze') : $credits )
      , $opts
      );
    case 'forum':
      return html_img( '/pp/fotos/forum2.jpg'
      , '' // we( 'Student in Forum room','Student im Forum beim Kaffeetrinken')
      , ( $credits === true ? credits('Ines Mayan') : $credits )
      , $opts
      );
    case 'rara':
      return html_img( '/pp/fotos/rara3.jpg'
      , '' // 'Rara der Uni-Bibliothek'
      , ( $credits === true ? credits('Karla Fritze') : $credits )
      , $opts
      );
    case 'labor':
      return html_img( '/pp/fotos/labor4.jpg'
      , we('Handling hazardous material in glovebox','Umgang mit gefährlichem Material im Handschuhkasten')
      , ( $credits === true ? credits('Dieter Neher') : $credits )
      , $opts
      );
    case 'astrophysics':
      return html_img( '/pp/fotos/mastro.jpg'
      , we('Student at telescope','Student am Teleskop')
      , ( $credits === true ? credits('Karla Fritze') : $credits )
      , $opts
      );
  }
}

?>
