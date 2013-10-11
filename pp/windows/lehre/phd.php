<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('PhD Programme','Promotionsstudium' ) );


_m4_tr
  _m4_link(
    /lehre/beratung.m4,
    Studienfachberatung Physik
  )

_m4_tr
  _m4_outlink([[http://www.app.physik.uni-potsdam.de/phd]]_m4_en([[_en]])[[.html]],
   [[_m4_de(Strukturierte Doktorandenausbildung in Astrophysik)
   _m4_en(Structured Doctoral Training in Astrophysics)]],
   [[_m4_en([[a common and lasting framwork for supervision and training of doctorate students
          in astronomy and astrophysics at Universit&auml;t Potsdam, Leibniz-Institute for
          Astrophysics Potsdam (AIP) and DESY/Zeuthen]])
   _m4_de([[ein gemeinsamer Rahmen zur Betreuung und Ausbildung von Promivierenden in Astronomie
           und Astrophysik an der Universit&auml;t Potsdam, am Leibniz-Institut f&uuml;r Astrophysik Potsdam (AIP)
           und am DESY (Zeuthen)]])]]
   )


_m4_bigskip
_m4_atable

_m4_include(bottom.m4)

?>
