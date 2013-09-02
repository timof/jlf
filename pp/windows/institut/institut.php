<?php

open_div('headline', we('Address:','Adresse:') );
  echo html_tag( 'address', ''
  ,   html_tag( 'p', '', 'Universität Potsdam, Campus Golm')
    . html_tag( 'p', '', 'Institut für Physik und Astronomie (Haus 28)')
    . html_tag( 'p', '', 'Karl-Liebknecht-Straße 24/25')
    . html_tag( 'p', '', '14476 Potsdam-Golm')
    . html_tag( 'p', '', 'Germany')
  );

// _m4_pushdef([[_m4_tdstyle]],_m4_tdstyle colspan="2")
//   _m4_p(class="smallskip" style="margin-left:5mm;")
//   _m4_outlink(http://www.uni-potsdam.de/lageplaene/golmlage.html,_m4_de(Lagepläne)_m4_en(How to find us))
// _m4_medskip
// 
// 
  echo tb( we('Head of the Institute:','Geschäftsführender Leiter:')
         , alink_person_view( 'board=executive,function=chief', 'office' ) );

  echo tb( we('Deputy Head:','Stellvertretender Geschäftsführender Leiter:')
         , alink_person_view( 'board=executive,function=deputy', 'office' ) );

  echo tb( we('Scientific Coordinator:','Wissenschaftlicher Koordinator:')
         , alink_person_view( 'board=special,function=coordinator', 'office' ) );

  echo tb( inlink( 'institutsrat', 'text='.we('Institute board','Institutsrat') ) );

  echo tb( inlink( 'pruefungsausschuss', 'text='.we('Examination board and board of study affairs','Prüfungsausschuss und Studienkommission') ) );

  echo tb( inlink( 'professuren', 'text='.we('Professors','Professuren') ) );

  echo tb( inlink( 'praktika', 'text='.we('Lab courses','Praktika') ) );

// _m4_tr
//   _m4_inlink(/lehre/beratung.m4,_m4_de(Studienberatung/Prüfungsbeauftragte)_m4_en(Course guidance))
// _m4_tr
//   _m4_inlink(/lehre/pruefer.m4,_m4_de(Pr&uuml;ferverzeichnis für Studiengänge mit dem Fach Physik)_m4_en(List of Examiners))
// _m4_tr

  echo tb( html_tag( 'a', 'href=http://www.uni-potsdam.de/mnfakul/promotion.html,class=href outlink', 'Promotionsausschuss der Fakultät' ) );

  echo tb( 'BAFöG'.we(' guidance:','-Beratung:')
         , alink_person_view( 'board=guidance,function=bafoeg', 'office' ) );

  echo tb( 'SOKRATES/ERASMUS'.we(' guidance:','-Beratung:')
         , alink_person_view( 'board=guidance,function=erasmus', 'office' ) );

// _m4_tr
//   _m4_inlink(/lehre/studierendenvertretung.m4,_m4_de(Studierendenvertretung)_m4_en(Student representation))
// _m4_tr

  echo tb( inlink( 'mitarbeiter', 'text='.we('staff','Personalverzeichnis') ) );

  echo tb( inlink( 'gruppen', 'text='.we('groups','Arbeitsgruppen am Institut') ) );

//   _m4_ifelse([[
//     _m4_tr
//       _m4_inlink(/preise/preise.m4,_m4_de(Preise und Auszeichnungen)_m4_en(Awards))
//     _m4_de(
//     _m4_tr
//       _m4_inlink(/institut/presse/presse.m4,Das Institut im Spiegel der Presse)
//     )
//   ]])
// 


?>
