<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Diploma Programme', 'Diplomstudiengang' ) );


_m4_tr
  _m4_td
  _m4_inlink([[/members/persondetails.m4php~p21]],
	  Studienberatung: Dr. Horst Gebert,
  [[Studienfachberatung Physik f&uuml;r Diplom-, Magister-,  Bachelor-, Masterstudiengang]]
    )

_m4_smallskip
_m4_tr
  _m4_td
  _m4_link(
    /lehre/pruefer.m4,
    Pr&uuml;ferverzeichnis,
    [[Liste der Pr&uuml;fer f&uuml;r Diplom- und Magisterstudiengänge mit dem Fach Physik]]
  )

_m4_smallskip
_m4_tr
  _m4_file(
    http://theosolid.qipc.org/KomVV_WS2013.pdf,
    [[_m4_de([[Kommentiertes Vorlesungsverzeichnis: Physik, Wintersemester 2013/14]])_m4_en([[Course Schedule: Physics, Winter term 2013/14]])]]
  )

_m4_smallskip
_m4_tr
  _m4_file(/lehre/DP-BachelorMaster-VergleichLehrveranstaltungen.pdf,
    [[&Auml;quivalente Veranstaltungen f&uuml;r Studierende im Diplomstudiengang]]
  )

_m4_smallskip
_m4_tr
  _m4_td(style='padding:1em;')
  _m4_link(
    /lehre/studienordnungen.m4,
    [[Archiv: Studien- und Pr&uuml;fungsordnungen]],
    [[aktuelle und alte Fassungen]]
  )



?>
