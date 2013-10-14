<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Imprint','Impressum') );

echo html_tag( 'h2', '', 'Herausgeber' );

open_div('headline', we('Street Address:','Adresse:') );

open_span( 'inline_block', address_view( 'header=0,maplink=0' ) );
//   echo html_tag( 'address', ''
//   ,   html_tag( 'p', '', "Universit{$aUML}t Potsdam, Campus Golm")
//     . html_tag( 'p', '', "Institut f{$uUML}r Physik und Astronomie (Haus 28)")
//     . html_tag( 'p', '', "Karl-Liebknecht-Stra{$SZLIG}e 24/25")
//     . html_tag( 'p', '', '14476 Potsdam-Golm')
//     . html_tag( 'p', '', 'Germany')
//   );

echo tb( we('Head of the Institute:',"Gesch{$aUML}ftsf{$uUML}hrender Leiter:")
       , alink_person_view( 'board=executive,function=chief', 'office' ) );

echo tb( we('Web admin:','Webadministrator:')
       , alink_person_view( 'board=special,function=admin', 'office' ) );

echo html_tag( 'h2', '', 'Haftungsausschluss' );

echo html_tag( 'h3', '', "Haftung f{$uUML}r Inhalte" );

echo "
  Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. Für die
  Richtigkeit, Vollständigkeit und Aktualität der Inhalte können wir jedoch keine
  Gewähr
  übernehmen. Als Diensteanbieter sind wir gemäß Paragraph 7 Abs.1 TMG für eigene
  Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach Paragraph
  8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet,
  übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach
  Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.
  Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach
  den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung
  ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung
  möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir
  diese Inhalte umgehend entfernen.
";

echo html_tag( 'h3', '', "Haftung f{$uUML}r Links" );

echo "
  Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte
  wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch
  keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der
  jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten
  Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche Rechtsverstöße
  überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht
  erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist
  jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei
  Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend
  entfernen.
";

echo html_tag( 'h3', '', 'Urheberrecht' );

echo "
  Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten
  unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung,
  Verbreitung und jede Art der Verwertung außerhalb der Grenzen des
  Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw.
  Erstellers. Downloads und Kopien dieser Seite sind nur für den privaten, nicht
  kommerziellen Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht vom
  Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet.
  Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie
  trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um
  einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden
  wir derartige Inhalte umgehend entfernen.
";


echo html_tag( 'h3', '', 'Datenschutz' );

echo "
  Die Nutzung unserer Webseite ist in der Regel ohne Angabe personenbezogener
  Daten möglich.
";


?>
