<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

define( 'OPTION_HGB_FORMAT', 1 );
define( 'OPTION_HGB_SHOW_EMPTY', 2 );

init_var( 'options', 'global=1,type=u,sources=http persistent,default=0,set_scopes=self' );
init_var( 'kontenkreis', 'global=1,type=W1,pattern=/^[BE]$/,sources=http persistent,set_scopes=self,default=B' );

$field_geschaeftsjahr = init_var( 'geschaeftsjahr', 'global,type=U,sources=http persistent initval,set_scopes=self,initval='.$geschaeftsjahr_thread );
$field_stichtag_bis = init_var( 'stichtag_bis', 'global,type=u,sources=http persistent,default=1231,min=100,max=1299,set_scopes=self' );
$field_flag_ausgefuehrt = init_var( 'flag_ausgefuehrt', 'global,type=B,sources=http persistent initval,set_scopes=self,auto=1,initval=1' );

$filters = array( 'geschaeftsjahr' => $geschaeftsjahr, 'valuta <=' => $stichtag_bis );
if( $flag_ausgefuehrt != 2 ) {
  $filters['flag_ausgefuehrt'] = $flag_ausgefuehrt;
}
if( $kontenkreis === 'E' ) {
  $field_stichtag_von = init_var( 'stichtag_von', 'global,type=u,sources=http persistent,default=0100,min=100,max=1299,set_scopes=self' );
  $field_geschaeftsbereich = init_var( 'geschaeftsbereich', 'global,type=a64,sources=http persistent,default=,set_scopes=self' );
  if( $geschaeftsbereich ) {
    $filters['geschaeftsbereich'] = $geschaeftsbereich;
  }
  if( $stichtag_von > $stichtag_bis ) {
    if( $field_stichtag_von['source'] == 'http' ) {
      $stichtag_bis = $stichtag_von;
    } else {
      $stichtag_von = $stichtag_bis;
    }
  }
  $filters['valuta >='] = $stichtag_von;
}


$erster_titel = 1;
function show_rubrik( $rubrik ) {
  global $erster_titel;
  open_tr( 'rubrik' );
    open_th( 'colspan=3', html_tag( 'div', '', $rubrik ) );
  $erster_titel = 1;
}

function show_titel( $titel, $subtitel, $seite, $saldo ) {
  global $erster_titel;
  $rounded = sprintf( "%.2lf", $saldo );
  open_tr( $erster_titel ? 'erstertitel' : $titel ? 'titel' : 'subtitel' );
    if( ! $subtitel ) {
      open_td( 'right,colspan=2', $titel );
    } else {
      open_td( 'right', $titel );
      open_td( 'right italic', $subtitel );
    }
    open_td( 'number bottom', saldo_view( $seite, $rounded ) );
  $erster_titel = 0;
}


function show_saldo_E() {
  global $filters, $geschaeftsjahr;
  $saldo = sql_unterkonten_saldo( $filters + array( 'seite' => 'P', 'kontenkreis' => 'E' ) )
         - sql_unterkonten_saldo( $filters + array( 'seite' => 'A', 'kontenkreis' => 'E' ) );
  show_titel( inlink( 'erfolgskonten', array( 'class' => 'href', 'text' => 'Saldo Erfolgskonten', 'kontenkreis' => 'E' ) ) , '' , 'P', $saldo );
  $saldo_E_shown = true;
  return $saldo;
}

function show_seite( $kontenkreis, $seite ) {
  global $filters, $unterstuetzung_geschaeftsbereiche, $geschaeftsjahr;

  $konten = sql_hauptkonten(
    array( 'kontenkreis' => $kontenkreis, 'seite' => $seite )
  , array( 'orderby' => 'rubrik, titel, geschaeftsbereich' )
  );
  $seitensaldo = 0;
  open_table( 'inner hfill smallskipt' );
    $rubrik = '';
    foreach( $konten as $k ) {
      $saldo = sql_unterkonten_saldo( $filters + array( 'hauptkonten_id' => $k['hauptkonten_id'] ) );
      if( ( ! $k['flag_hauptkonto_offen'] ) && ( abs( $saldo ) < 0.005 ) ) {
        continue;
      }
      if( $rubrik != $k['rubrik'] ) {
        $rubrik = $k['rubrik'];
        show_rubrik( $rubrik );
        $titel = '';
      }
      if( ( $kontenkreis == 'E' ) && $unterstuetzung_geschaeftsbereiche && ! adefault( $filters, 'geschaeftsbereich', 0 ) ) {
        $gb = $k['geschaeftsbereich'];
        if( $titel != $k['titel'] ) {
          $titel_link = $titel = $k['titel'];
        } else {
          $titel_link = '';
        }
        $subtitel_link = inlink( '!', array( 'class' => 'href', 'text' => $gb, 'UID_geschaeftsbereich' => value2uid( $gb ) ) );
      } else {
        $titel_link = inlink( 'hauptkonto', array( 'class' => 'href', 'text' => " {$k['titel']} ", 'hauptkonten_id' => $k['hauptkonten_id'] ) );
        $subtitel_link = '';
      }
      show_titel( $titel_link, $subtitel_link, $seite, $saldo );
      $seitensaldo += $saldo;
    }
    if( ( $kontenkreis == 'B' ) && ( $seite == 'P' ) ) {
      show_rubrik( 'Jahresergebnis' );
      $seitensaldo += show_saldo_E();
    }
  close_table();
  return $seitensaldo;
}

function show_hgb_GuV() {
  global $hgb_klassen, $geschaeftsjahr;

  menatwork();
  open_table( 'hfill' );
    open_tr();
      open_th();
      open_th( '', 'Aufwand' );
      open_th( '', 'Ertrag' );

    $saldoP = 0.0;
    $j = '';
    $j_rubrik = '';
    $j_titel = '';
    $j_subtitel = '';
    foreach( $hgb_klassen as $i => $klasse ) {
      $indices = explode( '.', $i );
      $i_kontenkreis = adefault( $indices, 0, '' );
      $i_seite = adefault( $indices, 1, '' );
      $i_rubrik = adefault( $indices, 2, '' );
      $i_titel = adefault( $indices, 3, '' );
      $i_subtitel = adefault( $indices, 4, '' );

      if( $i_kontenkreis !== 'E' )
        continue;

        if( adefault( $klasse, 'zwischensumme', false ) ) {
          

        } else {
          $postensaldo = sql_unterkonten_saldo( $filters + array( 'kontenkreis' => 'E', 'hgb_klasse' => $i ) );
        }
      


    }
}


function show_seite_hgb_bilanz( $seite ) {
  global $hgb_klassen, $filters, $geschaeftsjahr, $options;
  $seitensaldo = 0.0;
  open_table( 'inner hfill' );
    $j = '';
    $j_rubrik = '';
    $j_titel = '';
    $j_subtitel = '';

    foreach( $hgb_klassen as $i => $klasse ) {
      $indices = explode( '.', $i );
      $i_kontenkreis = adefault( $indices, 0, '' );
      $i_seite = adefault( $indices, 1, '' );
      $i_rubrik = adefault( $indices, 2, '' );
      $i_titel = adefault( $indices, 3, '' );
      $i_subtitel = adefault( $indices, 4, '' );

      if( ( $i_kontenkreis !== 'B' ) || ( $i_seite !== $seite ) ) {
        continue;
      }

      $teilbetrag = preg_match( '/[a-c][.]$/', $i );

      if( $i === 'B.P.A.V.' ) {
        // spezialfall: jahresergebnis:
        $saldo = sql_unterkonten_saldo( array( '&&', $filters, 'seite' => 'P', 'kontenkreis' => 'E' ) )
               - sql_unterkonten_saldo( array( '&&', $filters, 'seite' => 'A', 'kontenkreis' => 'E' ) );
      } else {
        // echte bestandskonten:
        if( ! ( $options & OPTION_HGB_SHOW_EMPTY ) ) {
          if( ! sql_unterkonten( array( '&&', 'kontenkreis' => 'B', 'hgb_klasse' => $i ) ) ) {
            continue;
          }
        }
        $u = sql_unterkonten( array( '&&', 'kontenkreis' => 'B', 'hgb_klasse' => $i ) );
        $saldo = sql_unterkonten_saldo( array( '&&', $filters, 'kontenkreis' => 'B', 'hgb_klasse' => $i ) );
        if( $u ) {
          debug( $i, $saldo );
        }
      }
      if( $i_rubrik != $j_rubrik ) {
        open_tr( 'hgb_rubrik' );
          open_th( 'colspan=3', "$i_rubrik. {$klasse['rubrik']}" );
          $j_rubrik = $i_rubrik;
          $j_titel = '';
          if( ! $i_titel ) {
            open_th( 'rubrik number', saldo_view( $seite, $saldo ) );
            $seitensaldo += $saldo;
            continue;
          } else {
            open_th();
          }
      }
      if( $i_titel != $j_titel ) {
        open_tr( 'hgb_titel' );
          open_td( 'qquads', '' );
          open_td( 'colspan=2', "$i_titel. {$klasse['titel']}" );
          $j_titel = $i_titel;
          $j_subtitel = '';
          if( ! $i_subtitel ) {
            open_td( 'number', saldo_view( $seite, $saldo ) );
            $seitensaldo += $saldo;
            continue;
          } else {
            if( $teilbetrag ) {
              $saldo = sql_unterkonten_saldo( $filters + array( 'kontenkreis' => 'B', 'hgb_klasse' => "$i_seite.$i_rubrik.$i_titel." ) );
              open_td( 'number', saldo_view( $seite, $saldo ) );
              $seitensaldo += $saldo;
            } else {
              open_td();
            }
          }
      }
      open_tr( 'hgb_subtitel' );
        open_td( 'qquads', '' );
        open_td( 'qquads', '' );
        if( $teilbetrag ) {
          open_td( 'qquads left', "{$klasse['subtitel']}: ".saldo_view( $seite, $saldo) );
          open_td();
        } else {
          open_td( '', "$i_subtitel. {$klasse['subtitel']}" );
          open_td( 'number',  saldo_view( $seite, $saldo ) );
          $seitensaldo += $saldo;
        }
    }
  close_table();
  return $seitensaldo;
}


if( "$kontenkreis" == 'B' ) {

  open_tag( 'h1', 'oneline' );
    echo "Bestandskonten (Bilanz)";
  // open_span( 'onlyprint' );
    echo " --- Gesch{$aUML}ftsjahr: $geschaeftsjahr";
    switch( $stichtag_bis ) {
      case 100:
        echo " --- Er{$oUML}ffnungsbilanz";
        break;
      case 1231:
        echo " --- Schlussbilanz vor Gewinnverwendung";
        break;
      case 1299:
        echo " --- Schlussbilanz nach Gewinnverwendung";
        break;
      default:
        echo " --- Stichtag: $stichtag_bis";
        break;
    }
  // close_span();
  close_tag( 'h1' );

  open_div( 'center' );
    echo html_tag( 'img', "src=sus/img/dilbert.5652.gif" );
  close_div();

  open_div( 'noprint menubox' );
    open_table( 'css filters' );
      open_caption( '', 'Filter' );
      open_tr();
        open_th( '', "Gesch{$aUML}ftsjahr:" );
        open_td( 'oneline', filter_geschaeftsjahr( $field_geschaeftsjahr ) );
      open_tr();
        open_th( '', 'Stichtag:' );
        open_td( 'oneline', selector_valuta( $field_stichtag_bis ) );
      open_tr();
        open_th( '', "Status:" );
        open_td( '', radiolist_element( $field_flag_ausgefuehrt, "choices=:geplant:ausgef{$uUML}hrt:alle" ) );
    close_table();
    open_table('css actions' );
      open_caption( '', 'Aktionen' );
      open_tr();
        open_td( '', inlink( 'hauptkonto', 'class=big button,text=Neues Bestandskonto,kontenkreis=B' ) );
        open_td( 'oneline' );
          echo checkbox_element( array( 'name' => 'options', 'value' => $options, 'mask' => OPTION_HGB_FORMAT, 'text' => 'striktes HGB Format', 'auto' => '1' ) );
          if( $options & OPTION_HGB_FORMAT ) {
            qquad();
            echo checkbox_element( array( 'name' => 'options', 'value' => $options, 'mask' => OPTION_HGB_SHOW_EMPTY, 'text' => 'Positionen ohne Konten anzeigen', 'auto' => '1' ) );
          }
    close_table();
  close_div();

  open_table( 'layout hfill,colgroup=50% 50%' );
    open_tr();
      open_th( 'left,style=padding:6px;', 'Aktiva' );
      open_th( 'right,style=padding:6px;', 'Passiva' );
    open_tr();

      if( $options & OPTION_HGB_FORMAT ) {
        open_td();
        $aktiva_saldo = show_seite_hgb_bilanz( 'A' );
        open_td();
        $passiva_saldo = show_seite_hgb_bilanz( 'P' );
      } else {
        open_td();
        $aktiva_saldo = show_seite( 'B', 'A' );
        open_td();
        $passiva_saldo = show_seite( 'B', 'P' );
      }

    open_tr( 'summe posten titel' );
      open_th( 'number', saldo_view( 'A', $aktiva_saldo ) );
      open_th( 'number', saldo_view( 'P', $passiva_saldo ) );

    open_tr();
      open_td( 'bigskip', ' ' );
  close_table();

}


if( "$kontenkreis" == 'E' ) {

  open_tag( 'h1', 'oneline' );
    echo "Erfolgskonten (Gewinn- und Verlustrechnung)";
  // open_span( 'onlyprint' );
    switch( $geschaeftsbereich ) {
      case '':
        $g = "alle Gesch{$aUML}ftsbereiche";
        break;
      default:
        $g = "Gesch{$aUML}ftsbereich: $geschaeftsbereich";
        break;
    }
    echo "  --- Gesch{$aUML}ftsjahr: $geschaeftsjahr --- $g";
    if( $stichtag_von <= 100 ) {
      switch( $stichtag_bis ) {
        case 1231:
          echo " --- Jahresabschluss";
          break;
        default:
          echo " --- Stichtag: $stichtag_bis";
          break;
      }
    } else {
      echo " --- Periode: $stichtag_von bis $stichtag_bis";
    }
  // close_span();
  close_tag( 'h1' );

  open_div( 'noprint menubox' );
    open_table('css filters');
        open_caption('', 'Filter' );
      open_tr();
        open_th( '', "Gesch{$aUML}ftsbereich:" );
        open_td( '', filter_geschaeftsbereich( $field_geschaeftsbereich ) );
      open_tr();
        open_th( '', "Gesch{$aUML}ftsjahr" );
        open_td( '', filter_geschaeftsjahr( $field_geschaeftsjahr ) );
      open_tr();
        open_th( '', 'von:' );
        open_td( '', selector_valuta( $field_stichtag_von ) );
      open_tr();
        open_th( '', 'bis:' );
        open_td( '', selector_valuta( $field_stichtag_bis ) );
      open_tr();
        open_th( '', "Status:" );
        open_td( '', radiolist_element( $field_flag_ausgefuehrt, "choices=:geplant:ausgef{$uUML}hrt:alle" ) );
    close_table();
    open_table('css actions' );
      open_caption( '', 'Aktionen' );
      open_tr();
        open_td( '', inlink( 'hauptkonto', 'class=big button,text=Neues Erfolgskonto,kontenkreis=E' ) );
    close_table();
  close_div();


  open_table( 'layout hfill,colgroup=50% 50%' );
    open_tr();
      open_th( 'left,style=padding:6px;', 'Aufwand' );
      open_th( 'right,style=padding:6px;', 'Ertrag' );
    open_tr();

      open_td();
        $aufwand_saldo = show_seite( 'E', 'A' );
      open_td();
        $ertrag_saldo = show_seite( 'E', 'P' );

    open_tr( 'summe posten titel' );
      open_th( 'number', saldo_view( 'A', $aufwand_saldo ) );
      open_th( 'number', saldo_view( 'P', $ertrag_saldo ) );
    open_tr( 'summe posten titel smallskip' );
      open_th( 'left', ( ( $stichtag_von == 100 ) && ( $stichtag_bis == 1231 ) ) ? 'Jahresergebnis:' : 'Ergebnis:' );
      open_th( 'number', saldo_view( 'P', $ertrag_saldo - $aufwand_saldo ) );
  close_table();

}


?>
