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
      echo "L (Laufzeit 20 Jahre, 10 Jahre tilgungsfrei)";
      $kondition = 'L';
      break;
    default:
      echo "*** SONDERFALL - manuell pruefen ***";
      $kondition = 'X';
      break;
  }
  echo "\n";
  echo "kommentar: {$person['note']}\n";

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
  echo "Golm der Universitaet Potsdam in Betrieb; mit einem Darlehen in Hoehe von\n";
  echo "{$d['betrag_abgerufen']} Euro " . ( $du ? 'hast Du' : 'haben Sie' ) . "mitgeholfen,\n";
  echo "diese Anlage zu finanzieren.";
  echo "\n";
  echo "Auch in diesem Jahr liegt der Ertrag der Anlage mit bisher mehr als 31.000 kWh\n";
  echo "bereits deutlich ueber der Prognose. Ein Ueberschuss von mehr als 2000 Euro aus\n";
  echo "dem Ertrag der Anlage wird in diesem Jahr fuer gemeinnuetzige Arbeit, wie etwa\n";
  echo "den Kongress 'Energiedemokratie' am vorletzten Wochenende, zur Verfuegung stehen.\n";
  echo "\n";
  echo "Der Stand " . ( $du ? 'Deines' : 'Ihres' ) ." Darlehens zum Ende des laufenden Jahres 2012:\n";  
  $saldo_darlehen = 0;
  $saldo_zins = 0;
  foreach( $posten_darlehen_vortrag as $p ) {
    printf( "  Vortrag Darlehen aus 2011:     %8.2lf\n", $p['betrag'] );
    $saldo_darlehen += $p['betrag'];
  }
  foreach( $posten_zins_vortrag as $p ) {
    printf( "  Vortrag Zins aus 2011:         %8.2lf\n", $p['betrag'] );
    $saldo_zins += $p['betrag'];
  }
  foreach( $posten_zins_gutschrift as $p ) {
    printf( "  Zinsgutschrift fuer 2012:      %8.2lf\n", $p['betrag'] );
    $saldo_zins += $p['betrag'];
  }
  foreach( $posten_darlehen_tilgung as $p ) {
    printf( "  Tilgungszahlung Ende 2012:     %8.2lf\n", -$p['betrag'] );
    $saldo_darlehen -= $p['betrag'];
  }
  foreach( $posten_zins_auszahlung as $p ) {
    printf( "  Zinsauszahlung Ende 2012:      %8.2lf\n", -$p['betrag'] );
    $saldo_zins -= $p['betrag'];
  }
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
       . ( $du ? 'lass uns ' : 'lassen Sie uns ' ) . "das bitte\n";
  echo "wissen!\n";
  echo "\n";
  echo "Auch in diesem Jahr sind wir in der Lage und daran interessiert, in begrenztem\n";
  echo "Umfang Sondertilgungen zu leisten.\n";
  echo "Wenn " . ( $du ? 'Du' : 'Sie' ) . " Interesse an einer vorzeitigen Rueckzahlung "
           . ( $du ? 'hast' : 'haben' ) . ", dann " . ( $du ? 'schreibe' : 'schreiben Sie' ) . " uns bitte!\n";

//   switch( $kondition ) {
//     case 'L':
//       echo "Nach unserer Vereinbarung wird die Tilgung des Darlehens und die Auszahlung\n";
//       echo "der Zinsen erst in 10 Jahren beginnen. Wir wuerden aber gern einen Teil unserer\n";
//       echo "Schulden schon rascher als urspruenglich geplant abbauen und bieten "
//             . ( $du ? 'Dir' : 'Ihnen' ) . " daher\n";
//       echo "an, den Vertrag auf jaehrliche Zinsausschuettung umzustellen - in den ersten\n";
//       printf( "10 Jahren waeren das %.2lf Euro pro Jahr; da Zinsen einkommensteuerpflichtig\n", $d['betrag_abgerufen'] * $d['zins_prozent'] / 100.0 );
//       echo "sind, kann das unter Umstaenden vorteilhaft fuer " . ( $du ? 'Dich' : 'Sie' ). " sein.\n";
//       echo "Wenn " . ( $du ? 'Du' : 'Sie' ) . " dieses Angebot annehmen "
//            . ( $du ? 'moechtest' : 'moechten' ) . ", dann "
//            . ( $du ? 'teile' : 'teilen Sie' ) . " uns das bitte moeglichst\n";
//       echo "bald mit!\n";
//       break;
//     case 'K':
//       echo "Nach unserer Vereinbarung wird die Rueckzahlung des Darlehens und die Auszahlung\n";
//       echo "der Zinsen in 9 Jahren erfolgen.\n";
//       echo "Aufgrund des guten Ertrags der PV-Anlage sind wir aber in der Lage und\n";
//       echo "interessiert daran, in begrenztem Umfang Sondertilgungen zu leisten.\n";
//       echo "Wenn " . ( $du ? 'Du' : 'Sie' ) . " Interesse an einer vorzeitigen Rueckzahlung "
//            . ( $du ? 'hast' : 'haben' ) . ", dann " . ( $du ? 'schreibe' : 'schreiben Sie' ) . " uns bitte!\n";
//       break;
//     case 'R':
//       echo "Aufgrund des guten Ertrags der PV-Anlage sind wir in der Lage und interessiert daran,\n";
//       echo "in begrenztem Umfang Sondertilgungen zu leisten.\n";
//       echo "Wenn " . ( $du ? 'Du' : 'Sie' ) . " Interesse an einer vorzeitigen Rueckzahlung "
//            . ( $du ? 'hast' : 'haben' ) . ", dann " . ( $du ? 'schreibe' : 'schreiben Sie' ) . " uns bitte!\n";
//       break;
//   }
  echo "\n";
  echo "Sonnige Gruesse,\n";
  echo "Timo Felbinger (fuer UniSolar Potsdam e.V.)\n";
  echo "\n";


  echo "------------------------------------------------------------------------------\n";


}

close_tag( 'pre' );

      
?>
