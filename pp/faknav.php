<?php // faknav.php - last modified:  20130519.185409utc  by: timof@


$instbanner = "$('instbanner')";
echo html_map( array(
  'id' => 'instmap'
, array( 'shape' => 'rect', 'coords' => '20,30,71,50'
  , 'title' => we('Insitute of Mathematics','Insitut für Mathematik')
  , 'href' => we( URL_INSTMATH_E, URL_INSTMATH_D )
  , 'onmouseover' => "fadeover( 'instbanner', \$('fakinstmath').src );"
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'rect', 'coords' => '72,30,122,50'
  , 'title' => we('Insitute of Information Technology','Insitut für Informatik')
  , 'href' => we( URL_INSTINFO_E, URL_INSTINFO_D )
  , 'onmouseover' => "fadeover( 'instbanner', \$('fakinstinfo').src );"
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'rect', 'coords' => '123,30,173,50'
  , 'title' => we('Insitute of Physics','Insitut für Physik')
  , 'href' => we( URL_INSTPHYS_E, URL_INSTPHYS_D )
////  , 'onmouseover' => "fadeover( 'instbanner', \$('fakinstphys').src );"
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'rect', 'coords' => '174,30,224,50'
  , 'title' => we('Insitute of Chemistry','Insitut für Chemie')
  , 'href' => we( URL_INSTCHEM_E, URL_INSTCHEM_D )
  , 'onmouseover' => "fadeover( 'instbanner', \$('fakinstchem').src );"
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'rect', 'coords' => '225,30,275,50'
  , 'title' => we('Insitute of Biology','Insitut für Biologie')
  , 'href' => we( URL_INSTBIO_E, URL_INSTBIO_D )
  , 'onmouseover' => "fadeover( 'instbanner', \$('fakinstbio').src );"
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'rect', 'coords' => '276,30,326,50'
  , 'title' => we('Insitute of Nutritional Science','Insitut für Ernährungswissenschaft')
  , 'href' => we( URL_INSTFOOD_E, URL_INSTFOOD_D )
  , 'onmouseover' => "fadeover( 'instbanner', \$('fakinstfood').src );"
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'rect', 'coords' => '327,30,377,50'
  , 'title' => we('Insitute of Geography','Insitut für Geographie')
  , 'href' => we( URL_INSTGGRAPH_E, URL_INSTGGRAPH_D )
  , 'onmouseover' => "fadeover( 'instbanner', \$('fakinstggraph').src );"
  , 'onmouseout' => 'mooseout();'
  )
, array( 'shape' => 'rect', 'coords' => '378,30,428,50'
  , 'title' => we('Insitute of Earth and Environmental Sciences','Insitut für Erd- und Umweltwissenschaften')
  , 'href' => we( URL_INSTGOEK_E, URL_INSTGOEK_D )
  , 'onmouseover' => "fadeover( 'instbanner', \$('fakinstgoek').src );"
  , 'onmouseout' => 'mooseout();'
  )
) );

open_div( 'oneline qquads right,id=unileiste' );
//   if( $show_debug_button ) {
//     open_span( 'quads', inlink( '!submit', array(
//       'class' => 'href', 'text' => '[D]', 'debug' => ( $debug ? '0' : '1' )
//     , 'title' => 'toggle debugging mode'
//     ) ) );
//   }
//   if( $language == 'D' ) {
//     // $url = 'http://www.physics.uni-potsdam.de/'.inlink( '', array( 'context' => 'url' ) );
//     // open_span( 'quads', html_tag( 'a', "href=$url,class=button quads,title=switch to English language", 'EN' ) );
//           open_span( 'quads', inlink( '!submit', array(
//             'class' => 'button quads', 'text' => 'en', 'language' => 'E'
//           , 'title' => 'switch to English language'
//           ) ) );
//   } else {
//     // $url = 'http://www.physik.uni-potsdam.de/'.inlink( '', array( 'context' => 'url' ) );
//     // open_span( 'quads', html_tag( 'a', "href=$url,class=button quads,title=auf deutsche Sprache umschalten", 'DE' ) );
//           open_span( 'quads', inlink( '!submit', array(
//             'class' => 'button quads', 'text' => 'de', 'language' => 'D'
//           , 'title' => 'auf deutsche Sprache umschalten'
//           ) ) );
//   }
//
//   if( $font_size > 8 ) {
//     $f = $font_size - 1;
//     open_span( 'quads', inlink( '!submit', array(
//       'class' => 'button', 'text' => html_tag( 'span', 'tiny', 'A-' ), 'css_font_size' => $f
//     , 'title' => "decrease font size to {$f}pt"
//     ) ) );
//     unset( $f );
//   }
//   if( $font_size < 16 ) {
//     $f = $font_size + 1;
//     open_span( 'quads', inlink( '!submit', array(
//       'class' => 'button', 'text' => html_tag( 'span', 'large', 'A+' ), 'css_font_size'=> $f
//     , 'title' => "increase font size to {$f}pt"
//     ) ) );
//     unset( $f );
//   }
  echo html_alink( 'http://www.intern.uni-potsdam.de', 'class=href outlink qquadr,text=Uni Potsdam Intranet' );
  echo html_alink( 'http://webmail.uni-potsdam.de', 'class=href outlink qquadr,text=Uni Potsdam Webmail' );
close_div();

open_div( 'corporatecolor huge bold,id=bannerFakultaet' );
  echo html_alink( we( URL_FAKMATNAT_E, URL_FAKMATNAT_D ), 'class=href corporatecolor,text=Mathematisch-Naturwissenschaftliche Fakultät' );
//   , array(
//       'href' => we( URL_FAKMATNAT_E, URL_FAKMATNAT_D )
//     , 'onmouseover' => "fadeover( 'instbanner', \$('fakinstdots').src );"
//     , 'onmouseout' => 'mooseout();'
//     )
//   , html_tag( 'img', 'id=fakbanner,src=/pp/img/bannermatnat.gif,style=opacity:1.0;' )
//  );
close_div();
open_div( 'corporatecolor huge bold,id=bannerInstitut' );
  // open_div( '', html_tag( 'img', 'id=instbanner,src=/pp/img/bphysastro.gif,style=opacity:1.0;,usemap=#instmap' ) );
  echo 'Institut für Physik und Astronomie';
close_div();

?>
