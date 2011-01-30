<?php

if( ! persistent_var( 'geschaeftsjahr' ) )
  persistent_var( 'geschaeftsjahr', 'self', persistent_var( 'geschaeftsjahr_thread' ) );

get_http_var( 'hauptkonten_id', 'u', 0, true );
$hk = ( $hauptkonten_id ? sql_one_hauptkonto( $hauptkonten_id ) : false );
row2global( 'hauptkonten', $hk );

if( $hk ) {
  $kontoart = $hk['kontoart'];
  $seite = $hk['seite'];
} else {
  get_http_var( 'kontoart', '/^[BE0]$/', 0, 'self' );
  get_http_var( 'seite', '/^[AP0]$/', 0, 'self' );
  get_http_var( 'geschaeftsjahr', 'u', $geschaeftsjahr_thread, 'self' );
}

get_http_var( 'kontoklassen_id', 'u', $kontoklassen_id );
if( $kontoklassen_id ) {
  $klasse = sql_one_kontoklasse( $kontoklassen_id, true );
  if( ! $klasse ) {
    $kontoklassen_id = 0;
  } else {
    if( $kontoart ) {
      if( $kontoart != $klasse['kontoart'] ) {
        $kontoklassen_id = 0;
      }
    } else {
      $kontoart = $klasse['kontoart'];
    }
    if( $seite ) {
      if( $seite != $klasse['seite'] ) {
        $kontoklassen_id = 0;
      }
    } else {
      $seite = $klasse['seite'];
    }
  }
  $is_vortragskonto = $klasse['vortragskonto'];
} else {
  $is_vortragskonto = 0;
}

switch( $kontoart ) {
  case 'E':
    $kontoart_name = 'Erfolgskonto';
    break;
  case 'B':
    $kontoart_name = 'Bestandskonto' . ( $is_vortragskonto ? ' (Vortragskonto)' : '' );
    break;
  default:
    $kontoart = '';
    $kontoart_name = '';
}

$problems = array();

get_http_var( 'rubrik', 'h', $rubrik );
if( $rubrik ) {
  $rubrik_id = sql_unique_id( 'hauptkonten', 'rubrik', $rubrik );
} else {
  get_http_var( 'rubrik_id', 'w', '0' );
  $rubrik = sql_unique_value( 'hauptkonten', 'rubrik', $rubrik_id );
}

get_http_var( 'titel', 'h', $titel );
if( $titel ) {
  $titel_id = sql_unique_id( 'hauptkonten', 'titel', $titel );
} else {
  get_http_var( 'titel_id', 'w', '0' );
  $titel = sql_unique_value( 'hauptkonten', 'titel', $titel_id );
}

get_http_var( 'kommentar', 'h', $kommentar );


define( 'OPTION_SHOW_UNTERKONTEN', 1 );
get_http_var( 'options', 'u', 0, true );

$kann_schliessen = false;
$kann_oeffnen = false;
$oeffnen_schliessen_problem = '';
if( $hauptkonten_id ) {
  if( $hauptkonto_geschlossen ) {
    if( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen ) {
      $oeffnen_schliessen_problem = 'oeffnen nicht moeglich: geschaeftsjahr ist abgeschlossen';
    }
    if( ! $oeffnen_schliessen_problem ) {
      $kann_oeffnen = true;
    }
  } else {
    $oeffnen_schliessen_problem = sql_hauptkonto_close( $hauptkonten_id, 'check' );
    if( ! $oeffnen_schliessen_problem ) {
      $kann_schliessen = true;
    }
  }
}


$actions = array( 'update' );
if( ! $hauptkonto_geschlossen )
  $actions[] = 'save';
if( $kann_oeffnen )
  $actions[] = 'oeffnen';
if( $kann_schliessen )
  $actions[] = 'schliessen';
handle_action( $actions );
switch( $action ) {
  case 'update':
  case 'init':
    // nop
    break;

  case 'save':

    need( ! $hauptkonto_geschlossen );
    if(    ( ! $geschaeftsjahr ) 
        || ( $geschaeftsjahr < $geschaeftsjahr_min )
        || ( $geschaeftsjahr > $geschaeftsjahr_max )
        || ( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen ) )
      $problems[] = 'geschaeftsjahr';
    if( ! $rubrik )
      $problems[] = 'rubrik';
    if( ! $titel )
      $problems[] = 'titel';
    if( ! $kontoklassen_id )
      $problems[] = 'kontoklassen_id';
    if( ! $problems ) {
      $values = array(
        'rubrik' => $rubrik
      , 'titel' => $titel
      , 'kontoklassen_id' => $kontoklassen_id
      , 'kommentar' => $kommentar
      , 'geschaeftsjahr' => $geschaeftsjahr
      );
      if( $hauptkonten_id ) {
        sql_update( 'hauptkonten', $hauptkonten_id, $values );
      } else {
        $hauptkonten_id = sql_insert( 'hauptkonten', $values );
        persistent_var( 'hauptkonten_id', 'self' );
      }
      for( $id = $hauptkonten_id, $j = $geschaeftsjahr; $j < $geschaeftsjahr_max; $j++ ) {
        $id = sql_hauptkonto_folgekonto_anlegen( $id );
      }
    }
    break;

  case 'schliessen':
    need( $kann_schliessen, $oeffnen_schliessen_problem );
    sql_hauptkonto_close( $hauptkonten_id );
    break;
  case 'oeffnen':
    need( $kann_oeffnen, $oeffnen_schliessen_problem );
    sql_update( 'hauptkonten', $hauptkonten_id, array( 'hauptkonto_geschlossen' => 0 ) );
    for( $id = hauptkonten_id, $j = $geschaeftsjahr; $j < $geschaeftsjahr_max; $j++ ) {
      $id = sql_hauptkonto_folgekonto_anlegen( $id );
    }
    break;

  case 'unterkontoDelete':
    need( $message, 'kein unterkonto gewaehlt' );
    sql_unterkonto_delete( $message );
    break;
}


open_fieldset( 'small_form', '', ( $hauptkonten_id ? 'Stammdaten Hauptkonto': 'neues Hauptkonto' ) );
  open_form( 'name=update_form', "action=update" );
    open_table('small_form');
      open_tr( 'smallskips' );
        open_td( problem_class( 'geschaeftsjahr' ), '', 'Geschaeftsjahr:' );
        open_td( '' );
        if( $hauptkonten_id && $geschaeftsjahr ) {
          $pred = sql_one_hauptkonto( array( 'folge_hauptkonten_id' => $hauptkonten_id ), true );
          $pred_id = adefault( $pred, 'hauptkonten_id', 0 );
          open_span( '', '', inlink( '', array( 'class' => 'button', 'text' => ' &lt; '
                                                         , 'hauptkonten_id' => $pred_id , 'inactive' => ( $pred_id == 0 )
          ) ) );
          open_span( 'quads bold', '', $geschaeftsjahr );
          $succ_id = $hk['folge_hauptkonten_id'];
          open_span( '', '', inlink( '', array( 'class' => 'button', 'text' => ' &gt; '
                                                         , 'hauptkonten_id' => $succ_id, 'inactive' => ( $succ_id == 0 )
          ) ) );
        } else {
          filter_geschaeftsjahr();
        }
      open_tr( 'smallskips' );
if( ! $kontoart ) {
        open_td( '', '', 'Kontoart:' );
        open_td( '' );
          open_select( 'kontoart', '', html_options_kontoart(), 'submit' );
} else {
        open_td( '', '', 'Kontoart / Seite:' );
        open_td( '' );
          echo "$kontoart_name";
          hidden_input( 'kontoart' );
          qquad();
if( ! $seite ) {
          open_select( 'seite', '', html_options_seite(), 'submit' );
} else {
          echo "$seite";
          hidden_input( 'seite' );
      open_tr( 'smallskips' );
        open_td( problem_class( 'kontoklassen_id' ), '', "Kontoklasse:" );
        open_td();
          open_select( 'kontoklassen_id' );
            echo html_options_kontoklassen( $kontoklassen_id, "kontoart=$kontoart,seite=$seite" );
          close_select();

//       if( $kontoklassen_id && $is_vortragskonto ) {
//         open_tr( 'smallskips' );
//           open_td( '', '', 'Attribute: ' );
//           open_td();
            if( $is_vortragskonto ) {
              open_span( 'bold qquad', '', 'Vortragskonto' );
            }
//       }

      open_tr( 'smallskips' );
        open_td( 'top '.problem_class('rubrik'), '', 'Rubrik:' );
        open_td();
          open_div();
            open_select( 'rubrik_id', '', html_options_unique( $rubrik_id, 'hauptkonten', 'rubrik' ) );
          close_div();
          open_div( '', '', 'neue Rubrik: ' . string_view( $rubrik_id ? '' : $rubrik, 'rubrik', 30 ) );
      open_tr( 'smallskips' );
        open_td( 'top '.problem_class('titel'), '', 'Titel:' );
        open_td();
          open_div();
            open_select( 'titel_id', '', html_options_unique( $titel_id, 'hauptkonten', 'titel' ) );
          close_div();
          open_div( '', '', 'neuer Titel: ' . string_view( $titel_id ? '' : $titel, 'titel', 30 ) );
      open_tr( 'smallskips' );
        open_td( '', '', 'Kommentar:' );
        open_td( '' );
          echo "<textarea name='kommentar' rows='4' cols='60'>$kommentar</textarea>";

      open_tr( 'smallskips' );
        open_td( 'right', "colspan='2'", html_submission_button( 'save', 'Speichern' ) );

}
}
    close_table();
  close_form();

  if( $hauptkonten_id ) {
    open_div( '' );
      echo 'Status:';
      if( $hauptkonto_geschlossen ) {
        open_span( 'quads', '', 'Konto ist geschlossen' );
        if( $kann_oeffnen ) {
          open_span( 'quads', '', html_submission_button( 'oeffnen', 'wieder oeffnen' ) );
        } else {
          open_span( 'quads small', '', $oeffnen_schliessen_problem );
        }
      } else {
        open_span( 'quads', '', 'offen' );
        if( $kann_schliessen ) {
          open_span( 'quads', '', html_submission_button( 'schliessen', 'konto schliessen' ) );
        } else {
          open_span( 'quads small', '', $oeffnen_schliessen_problem );
        }
      }
    close_div();

    $uk = sql_unterkonten( array( 'hauptkonten_id' => $hauptkonten_id ) );
    if( $options & OPTION_SHOW_UNTERKONTEN ) {
      open_fieldset( 'small_form', ''
        , inlink( 'self', array( 'options' => $options & ~OPTION_SHOW_UNTERKONTEN, 'class' => 'close' ) )
          . ' Unterkonten: '
      );
        smallskip();
        open_div( 'right', '', inlink( 'unterkonto', "class=bigbutton,text=Neues Unterkonto,hauptkonten_id=$hauptkonten_id" ) );
        smallskip();
        if( count( $uk ) == 0 ) {
          open_div( 'center', '', '(keine Unterkonten vorhanden)' );
        } else {
          if( count( $uk ) == 1 ) {
            $unterkonten_id = $uk[0]['unterkonten_id'];
          } else {
            get_http_var( 'unterkonten_id', 'u', 0, true );
          }
          unterkontenlist_view( array( 'hauptkonten_id' => $hauptkonten_id ), true, 'unterkonten_id' );
        }
        if( $unterkonten_id ) {
          bigskip();
          postenlist_view( array( 'unterkonten_id' => $unterkonten_id ) );
        }
      close_fieldset();
    } else {
      if( $uk ) {
        open_div( '', '', inlink( 'self', array(
          'options' => $options | OPTION_SHOW_UNTERKONTEN, 'class' => 'button', 'text' => 'Unterkonten anzeigen'
        ) ) );
      } else {
        open_div( 'center', '', '(keine Unterkonten vorhanden)' );
        open_div( 'right', '', inlink( 'unterkonto', "class=bigbutton,text=Neues Unterkonto,hauptkonten_id=$hauptkonten_id" ) );
      }
    }
  }

close_fieldset();

?>
