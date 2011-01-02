<?php

need_http_var( 'kontoart', '/^[BE]$/', true );
$filters = array();

$erster_titel = 1;
function show_rubrik( $rubrik ) {
  global $erster_titel;
  open_tr( 'rubrik' );
    open_th( '', "colspan='2'", "<div>$rubrik</div>" );
  $erster_titel = 1;
}

function show_titel( $titel, $seite, $saldo ) {
  global $erster_titel;
  $rounded = sprintf( "%.2lf", $saldo );
  open_tr( $erster_titel ? 'erstertitel' : 'titel' );
    open_td( '', '', $titel );
    open_td( 'number', '', saldo_view( $seite, $rounded ) );
  $erster_titel = 0;
}

// $saldo_E_shown = false;
function show_saldo_E() {
//  global $saldo_E_shown;
//  if( $saldo_E_shown )
//    return 0.0;
  $saldo = sql_unterkonten_saldo( "seite=P,kontoart=E" ) - sql_unterkonten_saldo( "seite=A,kontoart=E" );
  show_titel(
    inlink( 'erfolgskonten'
    , array( 'class' => 'href', 'text' => 'Saldo Erfolgskonten' )
    )
  , 'P', $saldo
  );
  $saldo_E_shown = true;
  return $saldo;
}

function show_seite( $kontoart, $seite ) {
  global $filters;
  $konten = sql_hauptkonten( $filters + array( 'kontoart' => $kontoart, 'seite' => $seite ) );
  smallskip();
  $seitensaldo = 0;
  open_table( 'inner hfill' );
    $rubrik = '';
    foreach( $konten as $k ) {
      if( $rubrik != $k['rubrik'] ) {
        // if( ( $kontoart == 'B' ) && ( $seite == 'P' ) && ( $rubrik == 'Eigenkapital' ) ) {
        //   $seitensaldo += show_saldo_E();
        // }
        $rubrik = $k['rubrik'];
        show_rubrik( $rubrik );
      }
      $saldo = sql_unterkonten_saldo( "hauptkonten_id={$k['hauptkonten_id']}" );
      show_titel(
        inlink( 'hauptkonto'
        , array( 'class' => 'href', 'text' => " {$k['titel']} ", 'hauptkonten_id' => $k['hauptkonten_id'] )
        )
      , $seite, $saldo
      );
      $seitensaldo += $saldo;
    }
//     if( ( $kontoart == 'B' ) && ( $seite == 'P' ) && ( $rubrik == 'Eigenkapital' ) ) {
//       if( ! $saldo_E_shown ) {
//         if( $rubrik != 'Eigenkapital' )
//         $seitensaldo += show_saldo_E();
//       }
//     }
    if( ( $kontoart == 'B' ) && ( $seite == 'P' ) ) {
      show_rubrik( 'Jahresergebnis' );
        $seitensaldo += show_saldo_E();
    }
  close_table();
  return $seitensaldo;
}


if( "$kontoart" == 'B' ) {

  echo "<h1>Bestandskonten (Bilanz)</h1>";
  open_table('menu');
    open_tr();
      open_th('center', "colspan='2'", 'Aktionen' );
    open_tr();
      open_td( '', '', inlink( 'hauptkonto', 'class=bigbutton,text=Neues Bestandskonto,kontoart=B' ) );
  close_table();

  open_table( 'layout hfill' );
    echo "<colgroup><col width='50%'><col width='50%'></colgroup>";
    open_tr();
      open_th( 'left', "style='padding:6px;'", 'Aktiva' );
      open_th( 'right', "style='padding:6px;'", 'Passiva' );
    open_tr();

      open_td();
      $aktiva_saldo = show_seite( 'B', 'A' );

      open_td();
      $passiva_saldo = show_seite( 'B', 'P' );

    open_tr( 'summe posten titel' );
      open_th( 'number', '', saldo_view( 'A', $aktiva_saldo ) );
      open_th( 'number', '', saldo_view( 'P', $passiva_saldo ) );

    open_tr();
      open_td( 'bigskip', '', ' ' );
  close_table();

}


if( "$kontoart" == 'E' ) {

  $filters = handle_filters( 'geschaeftsbereiche_id' );

  echo "<h1>Erfolgskonten (Gewinn- und Verlustrechnung)</h1>";

  open_table('menu');
    open_tr();
      open_th('center', "colspan='2'", 'Filter' );
    open_tr();
      open_th('', '', 'Geschaeftsbereich: ' );
      open_td();
        filter_geschaeftsbereich();
    open_tr();
      open_th('center', "colspan='2'", 'Aktionen' );
    open_tr();
      open_td( '', '', inlink( 'hauptkonto', 'class=bigbutton,text=Neues Erfolgskonto,kontoart=E' ) );
  close_table();

  open_table( 'layout hfill' );
    echo "<colgroup><col width='50%'><col width='50%'></colgroup>";
    open_tr();
      open_th( 'left', "style='padding:6px;'", 'Aufwand' );
      open_th( 'right', "style='padding:6px;'", 'Ertrag' );
    open_tr();

      open_td();
        $aufwand_saldo = show_seite( 'E', 'A' );
      open_td();
        $ertrag_saldo = show_seite( 'E', 'P' );

    open_tr( 'summe posten titel' );
      open_th( 'number', '', saldo_view( 'A', $aufwand_saldo ) );
      open_th( 'number', '', saldo_view( 'P', $ertrag_saldo ) );
    open_tr( 'summe posten titel smallskip' );
      open_th( 'left', '', 'Jahresergebnis:' );
      open_th( 'number', '', saldo_view( 'P', $ertrag_saldo - $aufwand_saldo ) );
//         open_th( 'number' );
//       if( $ertrag_saldo >= $aufwand_saldo ) {
//         open_th( '', '', ' ' );
//         open_th( 'number' );
//           open_span( 'quad', "style='float:left;'", 'Jahresergebnis: ' );
//           echo saldo_view( 'P', $ertrag_saldo - $aufwand_saldo );
//       } else {
//         open_th( 'number' );;
//           open_span( 'quad', "style='float:left;'", 'Jahresergebnis: ' );
//           echo saldo_view( 'A', $aufwand_saldo - $ertrag_saldo );
//         open_th( '', '', ' ' );
//       }
  close_table();

}


?>
