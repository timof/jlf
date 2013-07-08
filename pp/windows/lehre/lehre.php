<?php


echo html_tag( 'h2', '', we('Programme-specific Information','Studiengangspezifische Informationen') );

echo tb( inlink( 'monobachelor', 'text='.we('Bachelor Programme','Bachelorstudiengang').' (BSc)' ) );

echo tb( inlink( 'master', 'text='.we('Master Programme','Masterstudiengang').' (MSc)' ) );

echo tb( inlink( 'lehramt', 'text='.we('Teacher Programme','Lehramtsstudium').' (BEd/Med)' ) );

echo tb( inlink( 'diplom', 'text='.we('Diploma/Magister Programme','Diplom-/Magisterstudium') ) );

echo tb( inlink( 'phd', 'text='.we('PhD Programme','Promotionsstudium') ) );


echo html_tag( 'h2', 'medskips', we('General Information for students','Allgemeine Informationen fuer Studierende') );

echo tb( inlink( 'termineStudium', 'text='.we('Important dates for students','Wichtige Termine fuer Studierende am Institut') ) );


// 
// _m4_tr
//   _m4_link(
//     /lehre/pruefungsausschuss.m4,
//     [[Pr&uuml;fungsausschuss und Studienkommission Physik]],
//      [[ Der Pr&uuml;fungsausschuss entscheidet u.a. &uuml;ber Fragen,
//      die Belegungsverpflichtungen angehen. ]]
//   )
// 
// _m4_ifelse([[
// _m4_tr
//   _m4_td
//   _m4_inlink(/lehre/externe.m4,[[_m4_de(Lehrveranstaltungen externer Dozenten)_m4_en(Courses by external lecturers)]])
// ]])
// 
// _m4_tr
//   _m4_inlink(/lehre/seminars.m4,[[_m4_de(Seminare und Kolloquia)_m4_en(Seminars and Colloquia)]],
// 	[[Regelmäßige Seminare, aktuelle Gastvorträge, ...]])
// 
// _m4_tr
//   _m4_inlink(/lehre/praktika.m4,
//     _m4_de(Praktika am Institut f&uuml;r Physik)_m4_en(Lab courses at the Institute of Physics)
//   )
// 
// _m4_dnl  _m4_inlink(
// _m4_dnl     [[/members/persondetails.m4php~p182]],
// _m4_
//   _m4_outlink(http://www.exph.physik.uni-potsdam.de/erasmus.html,
//          [[Auslandsstudium mit SOKRATES/ERASMUS]],
//          [[Austauschprogramm der Europäischen Union]])
// 
// _m4_tr
//     _m4_link(
//       /lehre/studierendenvertretung.m4,
//       _m4_de(Studierendenvertretung)_m4_en(Student representation),
//       _m4_de([[Vertretung der Studierenden in den Gremien der Universität]])
//     )
// 
// _m4_bigskip
// _m4_tr
//   _m4_td
//   _m4_inlink(/lehre/dokumente.m4,[[_m4_de(Download-Bereich)_m4_en(Download area)]],
// 	  [[_m4_de([[Dokumente: Vorlesungsverzeichnisse, Prüfungsordnungen, ...]])]])
// 
// _m4_bigskip

?>
