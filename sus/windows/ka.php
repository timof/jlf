<?php

init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window,default=0' );

$geschaeftsjahr_current = 2012;

$darlehen = sql_darlehen( 'geschaeftsjahr_darlehen=2010' );

open_tag( 'pre' );
foreach( $darlehen as $d ) {
  $darlehen_id = $d['darlehen_id'];
  $person = sql_person( $d['people_id'] );

  $posten_zins_vortrag = array();
  $posten_zins_gutschrift = array();
  $posten_zins_auszahlung = array();

  $posten_darlehen_vortrag = array();
  $posten_darlehen_tilgung = array();

  $darlehen_uk_id = $d['darlehen_unterkonten_id'];
  if( $darlehen_uk_id ) {
    $darlehen_uk_id = sql_get_folge_unterkonten_id( $darlehen_uk_id, $geschaeftsjahr_current );
  }
  if( $darlehen_uk_id ) {
    $posten_darlehen_vortrag = sql_posten( "unterkonten_id=$darlehen_uk_id,valuta=100" );
    $posten_darlehen_tilgung = sql_posten( "unterkonten_id=$darlehen_uk_id,art=S,valuta>100" );
  }

  $zins_uk_id = $d['zins_unterkonten_id'];
  if( $zins_uk_id ) {
    $zins_uk_id = sql_get_folge_unterkonten_id( $zins_uk_id, $geschaeftsjahr_current );
  }
  if( $zins_uk_id ) {
    $posten_zins_vortrag = sql_posten( "unterkonten_id=$zins_uk_id,valuta=100" );
    $posten_zins_gutschrift = sql_posten( "unterkonten_id=$zins_uk_id,art=H,valuta>100" );
    $posten_zins_auszahlung = sql_posten( "unterkonten_id=$zins_uk_id,art=S,valuta>100" );
  }

  $vortrag_saldo = 0.0;
  foreach( $posten_darlehen_vortrag as $p ) {
    $vortrag_saldo += $p['betrag'];
  }
  foreach( $posten_zins_vortrag as $p ) {
    $vortrag_saldo += $p['betrag'];
  }
  if( $vortrag_saldo < 0.01 ) {
    continue;
  }

  $du = ( $person['dusie'] == 'D' );
  
  echo "\n=========================================================================================\n";
  echo "kreditor: {$d['darlehen_unterkonten_cn']}\n";

  echo "anrede: ";
  switch( $person['genus'].$person['dusie'] ) {
    case 'MD':
      echo "maskulin / duzen";
      break;
    case 'MS':
      echo "maskulin / siezen";
      break;
    case 'FD':
      echo "feminin / duzen";
      break;
    case 'FS':
      echo "feminin / siezen";
      break;
    default:
      echo "*** SONDERFALL - manuell pruefen ***";
      break;
  }
  echo "\n";

  printf( "darlehen: %0.2lf / Zins: %.2lf / Konditionen: ", $d['betrag_abgerufen'], $d['zins_prozent'] );
  switch( $d['geschaeftsjahr_tilgung_start'] ) {
    case 2011:
      echo "R (Laufzeit 10 Jahre, 10 Annuitaeten)";
      $kondition = 'R';
      break;
    case 2020:
      echo "K (Laufzeit 10 Jahre, Einmalzahlung)";
      $kondition = 'K';
      break;
    case 2021:
      if( $d['geschaeftsjahr_zinsauszahlung_start'] <= $geschaeftsjahr_current ) {
        echo "LA (Laufzeit 20 Jahre, 10 Jahre tilgungsfrei, jaehrliche Zinsausschuettung)";
        $kondition = 'LA';
      } else {
        echo "L (Laufzeit 20 Jahre, 10 Jahre tilgungsfrei, Zinsansammlung bis 2021)";
        $kondition = 'L';
      }
      break;
    default:
      echo "*** SONDERFALL - manuell pruefen ***";
      $kondition = 'X';
      break;
  }
  echo "\n";
  echo "kommentar: {$person['note']}\n";

  $saldo_darlehen = 0;
  $saldo_zins = 0;
  $posten = '';
  foreach( $posten_darlehen_vortrag as $p ) {
    $posten .= sprintf( "  Vortrag Darlehen aus 2011:     %8.2lf\n", $p['betrag'] );
    $saldo_darlehen += $p['betrag'];
  }
  foreach( $posten_zins_vortrag as $p ) {
    $posten .= sprintf( "  Vortrag Zins aus 2011:         %8.2lf\n", $p['betrag'] );
    $saldo_zins += $p['betrag'];
  }
  foreach( $posten_zins_gutschrift as $p ) {
    $posten .= sprintf( "  Zinsgutschrift fuer 2012:      %8.2lf\n", $p['betrag'] );
    $saldo_zins += $p['betrag'];
  }
  foreach( $posten_darlehen_tilgung as $p ) {
    $posten .= sprintf( "  Tilgungszahlung Ende 2012:     %8.2lf\n", -$p['betrag'] );
    $saldo_darlehen -= $p['betrag'];
  }
  foreach( $posten_zins_auszahlung as $p ) {
    $posten .= sprintf( "  Zinsauszahlung Ende 2012:      %8.2lf\n", -$p['betrag'] );
    $saldo_zins -= $p['betrag'];
  }
  if( $saldo_zins + $saldo_darlehen < 0.01 ) {
    echo "*** Darlehen ist getilgt ***\n\n";
    continue;
  }
  echo "------------------------------------------------------------------------------\n";
  echo "From: UniSolar Potsdam e.V. {$H_LT}info@unisolar-potsdam.de{$H_GT}\n";
  echo "To: {$person['cn']} $H_LT{$person['mail']}$H_GT\n";
  echo "Bcc: timo@qipc.org\n";
  echo "Subject: " . ( $du ? 'Dein' : 'Ihr' ) . " Darlehen an UniSolar Potsdam\n";
  echo "\n";
  echo "\n";
  echo "Liebe".( $person['genus'] == 'M' ? 'r' : '' )
       . " {$person['gn']}" . ( $du ? ",\n" : " {$person['sn']},\n" );
  echo "\n";
  echo "bereits seit zwei Jahren ist nun unsere Photovoltaik-Anlage auf Haus 6 am\n";
  echo "Campus Golm der Universitaet Potsdam in Betrieb; mit einem Darlehen in Hoehe\n";
  echo "von {$d['betrag_abgerufen']} Euro " . ( $du ? 'hast Du' : 'haben Sie' ) . " mitgeholfen, diese Anlage zu finanzieren.\n";
  echo "\n";
  echo "Der Ertrag der Anlage liegt auch in diesem Jahr mit bisher mehr als 31.000 kWh\n";
  echo "bereits deutlich ueber der Prognose. Ein Ueberschuss von mehr als 2000 Euro aus\n";
  echo "der Einspeiseverguetung wird in diesem Jahr fuer gemeinnuetzige Arbeit, wie etwa\n";
  echo "den Kongress 'Energiedemokratie' am vorletzten Wochenende, zur Verfuegung stehen.\n";
  echo "\n";
  echo "Der Stand " . ( $du ? 'Deines' : 'Ihres' ) ." Darlehens zum Ende des laufenden Jahres 2012:\n";  
  echo $posten;
  printf( "  ----------------------------------------\n", $saldo_darlehen );
  printf( "  Saldo Darlehen am 31.12.2012:  %8.2lf\n", $saldo_darlehen );
  printf( "  Saldo Zinskonto am 31.12.2012: %8.2lf\n", $saldo_zins );
  printf( "  ----------------------------------------\n", $saldo_darlehen );
  printf( "  Gesamtguthaben am 31.12.2012:  %8.2lf\n", $saldo_darlehen + $saldo_zins );
  if( $posten_darlehen_tilgung || $posten_zins_auszahlung ) {
    echo "\n";
    echo "Die vorgesehene Auszahlung sollte bis Jahresende auf "
         . ( $du ? 'Deinem' : 'Ihrem' ) ." Konto eingehen.\n";
  }
  if( $posten_zins_auszahlung ) {
    echo "Der ausgewiesene Zinsanteil ist dabei einkommensteuerpflichtig.\n";
  }
  echo "\n";
  echo "Als Bankverbindung haben wir notiert:\n";
  echo "  Bank: {$person['bank_cn']}\n";
  echo "  BLZ: {$person['bank_blz']}\n";
  echo "  Konto-Nr: {$person['bank_kontonr']}\n";
  echo "Falls diese Angaben unrichtig oder unvollstaendig sein sollten, " 
       . ( $du ? 'lass uns ' : 'lassen Sie uns ' ) . "das\n";
  echo "bitte umgehend wissen!\n";
  echo "\n";
  switch( $kondition ) {
    case 'L':
      echo "Nach unserer Vereinbarung wird die Tilgung des Darlehens und die Auszahlung\n";
      echo "der Zinsen Ende des Jahres 2021 beginnen.\n";
      echo "\n";
      echo "Aufgrund des guten Ertrages sind wir aber auch in diesem Jahr in der Lage und\n";
      echo "daran interessiert, in begrenztem Umfang Sondertilgungen zu leisten.\n";
      echo "Wenn " . ( $du ? 'Du' : 'Sie' ) . " Interesse an einer vorzeitigen Rueckzahlung "
           . ( $du ? 'hast' : 'haben' ) . ", dann " . ( $du ? 'schreibe' : 'schreiben Sie' ) . " uns bitte!\n";
      break;
    case 'LA':
      echo "Nach unserer Vereinbarung werden Zinsen jaehrlich ausgezahlt; die Tilgung des\n";
      echo "Darlehens beginnt Ende des Jahres 2021.\n";
      echo "\n";
      echo "Aufgrund des guten Ertrages sind wir aber auch in diesem Jahr in der Lage und\n";
      echo "daran interessiert, in begrenztem Umfang Sondertilgungen zu leisten.\n";
      echo "Wenn " . ( $du ? 'Du' : 'Sie' ) . " Interesse an einer vorzeitigen Rueckzahlung "
           . ( $du ? 'hast' : 'haben' ) . ", dann " . ( $du ? 'schreibe' : 'schreiben Sie' ) . " uns bitte!\n";
      break;
    case 'K':
      echo "Nach unserer Vereinbarung wird die Rueckzahlung des Darlehens und die Auszahlung\n";
      echo "der Zinsen Ende des Jahres 2020 erfolgen.\n";
      echo "\n";
      echo "Aufgrund des guten Ertrages sind wir aber auch in diesem Jahr in der Lage und\n";
      echo "daran interessiert, in begrenztem Umfang Sondertilgungen zu leisten.\n";
      echo "Wenn " . ( $du ? 'Du' : 'Sie' ) . " Interesse an einer vorzeitigen Rueckzahlung "
           . ( $du ? 'hast' : 'haben' ) . ", dann " . ( $du ? 'schreibe' : 'schreiben Sie' ) . " uns bitte!\n";
      break;
    case 'R':
      echo "Aufgrund des guten Ertrages sind wir auch in diesem Jahr in der Lage und\n";
      echo "daran interessiert, in begrenztem Umfang Sondertilgungen zu leisten.\n";
      echo "Wenn " . ( $du ? 'Du' : 'Sie' ) . " Interesse an einer rascheren Rueckzahlung "
           . ( $du ? 'hast' : 'haben' ) . ", dann " . ( $du ? 'schreibe' : 'schreiben Sie' ) . " uns bitte!\n";
#       echo "Aufgrund des guten Ertrags der PV-Anlage sind wir in der Lage und interessiert daran,\n";
#       echo "in begrenztem Umfang Sondertilgungen zu leisten.\n";
#       echo "Wenn " . ( $du ? 'Du' : 'Sie' ) . " Interesse an einer vorzeitigen Rueckzahlung "
#            . ( $du ? 'hast' : 'haben' ) . ", dann " . ( $du ? 'schreibe' : 'schreiben Sie' ) . " uns bitte!\n";
      break;
  }

  echo "\n";
  echo "Winterliche Gruesse,\n";
  echo "Timo Felbinger (fuer UniSolar Potsdam e.V.)\n";
  echo "\n";
  echo "PS: Das Energienetz in Berlin soll demokratisiert und oekologischer werden!\n";
  echo "In der frisch gegruendeten Genossenschaft BuergerEnergie Berlin, siehe\n";
  echo "http://www.buerger-energie-berlin.de, " .( $du ? 'kannst auch Du' : 'koennen auch Sie' ). " Teil der Energiewende\n";
  echo "von unten werden und mit Genossenschaftsanteilen oder Spenden dazu beitragen,\n";
  echo "dem Konzern Vattenfall das Berliner Stromnetz abzukaufen.\n";
  echo "\n";

  echo "------------------------------------------------------------------------------\n";


}

close_tag( 'pre' );

      
?>
