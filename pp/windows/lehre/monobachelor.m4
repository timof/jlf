<?php


echo html_tag( 'h1', '', we('Bachelor of Physics (BSc) Programme','Bachelorstudiengang (BSc)' ) );

echo html_tag( 'h2', '', we('Studying in Potsdam','Wahl des Studienortes Potsdam') );

echo we('
  In Potsdam wird das Studienfach Physik als 3-j&auml;hriges Bachelorstudium angeboten;
  die Immatrikulation zum 1.&nbsp;Fachsemester ist im Fach Physik nur zum Beginn eines
  Wintersemesters möglich.
  Das Physikstudium zeichnen sehr gute Betreuungsverhältnisse und eine angenehme
  Arbeitsatmosphäre aus.
','
  In Potsdam wird das Studienfach Physik als 3-j&auml;hriges Bachelorstudium angeboten;
  die Immatrikulation zum 1.&nbsp;Fachsemester ist im Fach Physik nur zum Beginn eines
  Wintersemesters möglich.
  Das Physikstudium zeichnen sehr gute Betreuungsverhältnisse und eine angenehme
  Arbeitsatmosphäre aus.
' );

echo tb( outlink( 'http://www.uni-potsdam.de/zugang/index.html'
, we('Immatrikulation for the Phycics BSc/MSc Programme in Potsdam','Einschreibung zum Physikstudium in Potsdam') ) );

_m4_ifelse(1,1,[[
_m4_tr
_m4_td
  _m4_inlink(/lehre/intro.m4,[[
	  _m4_de(Einf&uuml;hrungsveranstaltungen und Vorkurse)_m4_en(Introductory courses)]],
		[[_m4_de(Vorbereitende Veranstaltungen vor Beginn des Vorlesungszeitraumes)]])
]])


echo tb(
  inlink( 'tutorium', we('Tutorium for beginners','Tutorium fuer Studienanfaenger') )
, we('Optional tutorial sessions: help and guidance from students for students'
    ,'Angebot einer freiwilligen Veranstaltung: Hilfe und Beratung von Studierenden für Studierende' )
);

_m4_tr
  _m4_td
  _m4_outlink([[http://www.uni-potsdam.de/mnfakul/studium/offenermint-raum.html]],
	  [[MINT-Raum]],[[Lernen mit Hilfe von Kommilitonen]]
    )

_m4_tr
  _m4_td
  _m4_inlink([[/members/persondetails.m4php~p21]],
	  Studienberatung: Dr. Horst Gebert,
  [[Studienfachberatung Physik f&uuml;r Diplom-, Magister-,  Bachelor-, Masterstudiengang]]
    )

_m4_bigskip
_m4_tr
<th class='smallskip'>Planung des Studiums</th>


_m4_tr
  _m4_td
  _m4_file(bachelor.verlauf.pdf,Studienverlaufsplan)

_m4_tr
  _m4_td
  _m4_file(bachelor.uebersicht.pdf,Veranstaltungsübersicht)

_m4_tr
  _m4_file(
    /studium/Handbuch_Bachelor111006.pdf,
    [[Modulhandbuch Bachelor Physik (Fassung vom 06.10.2011)]]
  )
_m4_tr
  _m4_file(
    http://theosolid.qipc.org/KomVV_WS2012.pdf,
    [[_m4_de([[Kommentiertes Vorlesungsverzeichnis: Physik, Wintersemester 2012/13]])_m4_en([[Course Catalog: Physics, Winter term 2012/13]])]]
  )
_m4_tr
_m4_td
  _m4_inlink(/lehre/belegung.m4,[[Belegen von Lehrveranstaltungen und Anmeldung zu Pr&uuml;fungen]])
_m4_tr
  _m4_td
  _m4_link(/lehre/termine.m4,Wichtige Termine f&uuml;r Studierende am Institut)

_m4_medskip
_m4_tr
  _m4_file(
    /studium/BaMaOrdnung-Physik-20120523-lesefassung.pdf,
    [[Studienordnung Bachelor/Master Physik (Fassung vom 23.05.2012)]]
  )
  _m4_p(style='padding-left:2em;')
  _m4_inlink(/lehre/studienordnungen.m4,[[&auml;ltere Fassungen...]])
  _m4_ap


_m4_medskip
_m4_tr
  _m4_td
  _m4_inlink(/lehre/themen.bachelor.m4,[[Themenvorschl&auml;ge f&uuml;r Bachelorarbeiten]])


_m4_bigskip
_m4_atable

_m4_include(bottom.m4)

