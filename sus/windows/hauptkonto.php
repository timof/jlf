<?php

init_global_var( 'geschaeftsjahr', 'u', 'http,persistent', $geschaeftsjahr_thread, 'self' );

init_global_var( 'hauptkonten_id', 'u', 'http,persistent', 0, 'self' );
$hk = ( $hauptkonten_id ? sql_one_hauptkonto( $hauptkonten_id ) : false );
row2global( 'hauptkonten', $hk );

$fields = array(
   'kontenkreis' => '^[BE0]$/'
,  'seite' => '/^[AP0]$/'
,  'geschaeftsjahr' => 'u'
,  'kontoklassen_id' => 'u'
,  'titel' => 'h'
,  'rubrik' => 'h'
);
$problems = array();
$changes = array();

if( $hk ) {
  $kontenkreis = $hk['kontenkreis'];
  $seite = $hk['seite'];
} else {
  // those attributes cannot be changed for existing accounts:
  init_global_var( 'kontenkreis', '/^[BE0]$/', 'http,persistent,default', 0, 'self' );
  init_global_var( 'seite', '/^[AP0]$/', 'http,persistent,default', 0, 'self' );
  init_global_var( 'geschaeftsjahr', 'u', 'http,persistent', $geschaeftsjahr_thread, 'self' );
}

init_global_var( 'kontoklassen_id', 'u', 'http,persistent,keep', 0, 'self' );
if( $kontoklassen_id ) {
  $klasse = sql_one_kontoklasse( $kontoklassen_id, true );
  if( ! $klasse ) {
    $kontoklassen_id = 0;
  } else {
    if( $kontenkreis ) {
      if( $kontenkreis != $klasse['kontenkreis'] ) {
        $kontoklassen_id = 0;
      }
    } else {
      $kontenkreis = $klasse['kontenkreis'];
    }
    if( $seite ) {
      if( $seite != $klasse['seite'] ) {
        $kontoklassen_id = 0;
      }
    } else {
      $seite = $klasse['seite'];
    }
  }
  $vortragskonto_name = ( $klasse['vortragskonto'] ? 'Vortragskonto '.$klasse['vortragskonto'] : '' );
} else {
  $vortragskonto_name = '';
}

switch( $kontenkreis ) {
  case 'E':
    $kontenkreis_name = 'Erfolgskonto';
    break;
  case 'B':
    $kontenkreis_name = 'Bestandskonto' . ( $vortragskonto_name ? " ($vortragskonto_name)" : '' );
    break;
  default:
    $kontenkreis = '';
    $kontenkreis_name = '';
}


init_global_var( 'rubrik', 'h', 'http,persistent,keep', '', 'self' );
init_global_var( 'rubriken_id', 'w', 'http', '0' );
if( $rubriken_id ) {
  $rubrik = sql_unique_value( 'hauptkonten', 'rubrik', $rubriken_id );
} else {
  $rubriken_id = sql_unique_id( 'hauptkonten', 'rubrik', $rubrik );
}

init_global_var( 'titel', 'h', 'http,persistent,keep', '', 'self' );
init_global_var( 'titel_id', 'w', 'http', '0' );
if( $titel_id ) {
  $titel = sql_unique_value( 'hauptkonten', 'titel', $titel_id );
} else {
  $titel_id = sql_unique_id( 'hauptkonten', 'titel', $titel );
}

$hgb_klasse = $hauptkonten_hgb_klasse;
init_global_var( 'hgb_klasse', '', 'http,persistent,keep', '', 'self' );

init_global_var( 'kommentar', 'h', 'http,persistent,keep', '', 'self' );


define( 'OPTION_SHOW_UNTERKONTEN', 1 );
init_global_var( 'options', 'u', 'http,persistent', OPTION_SHOW_UNTERKONTEN, 'window' );

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
    $oeffnen_schliessen_problem = sql_hauptkonto_schliessen( $hauptkonten_id, 'check' );
    if( ! $oeffnen_schliessen_problem ) {
      $kann_schliessen = true;
    }
  }
}


$actions = array( 'update', 'deleteUnterkonto', 'unterkontoSchliessen' );
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
    if( ! $problems && ! $hauptkonten_id ) {
      if( sql_hauptkonten( array(
        'titel' => $titel, 'rubrik' => $rubrik, 'geschaeftsjahr' => $geschaeftsjahr, 'geschaeftsbereich' => $klasse['geschaeftsbereich'] )
      ) ) {
        $problems['message'] = 'Hauptkonto mit diesen Attributen existiert bereits';
      }
    }

    if( ! $problems ) {
      $values = array(
        'rubrik' => $rubrik
      , 'titel' => $titel
      , 'hauptkonten_hgb_klasse' => $hgb_klasse
      , 'kommentar' => $kommentar
      );
      if( $hauptkonten_id ) {
        sql_update( 'hauptkonten', $hauptkonten_id, $values );
      } else {
        $values['geschaeftsjahr'] = $geschaeftsjahr;
        $values['kontoklassen_id'] = $kontoklassen_id;
        $hauptkonten_id = sql_insert( 'hauptkonten', $values );
      }
      for( $id = $hauptkonten_id, $j = $geschaeftsjahr; $j < $geschaeftsjahr_max; $j++ ) {
        $id = sql_hauptkonto_folgekonto_anlegen( $id );
      }
    }
    break;

  case 'schliessen':
    need( $kann_schliessen, $oeffnen_schliessen_problem );
    sql_hauptkonto_schliessen( $hauptkonten_id );
    schedule_reload();
    return;

  case 'oeffnen':
     need( $kann_oeffnen, $oeffnen_schliessen_problem );
    sql_update( 'hauptkonten', $hauptkonten_id, array( 'hauptkonto_geschlossen' => 0 ) );
    for( $id = hauptkonten_id, $j = $geschaeftsjahr; $j < $geschaeftsjahr_max; $j++ ) {
      $id = sql_hauptkonto_folgekonto_anlegen( $id );
    }
    schedule_reload();
    return;

  case 'deleteUnterkonto':
    need( $message > 0, 'kein unterkonto gewaehlt' );
    sql_delete_unterkonten( $message );
    break;

  case 'unterkontoSchliessen':
    need( $message > 0, 'kein unterkonto gewaehlt' );
    sql_unterkonto_schliessen( $message );
    break;
}


open_fieldset( 'small_form', '', ( $hauptkonten_id ? 'Stammdaten Hauptkonto': 'neues Hauptkonto' ) );
  // open_form( 'name=update_form', "action=update" );
    open_table('small_form');
      open_tr( 'smallskip' );
        $c = field_class( 'geschaeftsjahr' );
        open_td( "label $c", '', 'Geschaeftsjahr:' );
        open_td( "qquad kbd $c" );
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
          filter_geschaeftsjahr( '', false );
        }
      open_tr( 'smallskip' );
if( ! $kontenkreis ) {
        open_td( '', '', 'Kontenkreis:' );
        open_td( 'qquad' );
          selector_kontenkreis( 'kontenkreis' );
} else {
        open_td( 'oneline', '', 'Kreis/Seite:' );
        open_td( 'qquad' );
          echo "$kontenkreis_name";
          $filters['kontenkreis'] = $kontenkreis;
          qquad();
if( ! $seite ) {
          selector_seite( 'seite' );
} else {
          echo $seite;
          $filters['seite'] = $seite;

      open_tr( 'smallskip' );
        $c = field_class( 'kontoklassen_id' );
        open_td( "label $c", '', "Kontoklasse:" );
        open_td( "qquad kbd $c" );
          if( ! $hauptkonten_id ) {
            selector_kontoklasse( 'kontoklassen_id', $kontoklassen_id, $filters );
          } else {
            open_span( 'bold', '', $klasse['cn'] );
            if( $klasse['geschaeftsbereich'] )
              open_span( 'quad bold', '', $klasse['geschaeftsbereich'] );
          }
          if( $vortragskonto_name ) {
            open_span( 'bold qquad', '', $vortragskonto_name );
          }
          if( $kontoklassen_id )
            $filters['kontoklassen_id'] = $kontoklassen_id;

      open_tr( 'smallskip' );
        $c = field_class( 'hgb_klasse' );
        open_td( "label $c", '', 'HGB-Klasse:' );
        open_td( "qquad kbd $c" );
          selector_hgb_klasse( 'hgb_klasse', $hgb_klasse, $kontenkreis, $seite );

      open_tr( 'smallskip' );
        $c = field_class( 'rubrik' );
        open_td( "top label $c", '', 'Rubrik:' );
        open_td( "qquad oneline kbd $c" );
          open_span( 'large', '', string_view( $rubrik, 'rubrik', 30 ) );
          if( ! $hauptkonten_id )
            selector_rubrik( 'rubriken_id', 0, $filters );
          if( $rubriken_id )
            $filters['rubriken_id'] = $rubriken_id;

      open_tr( 'smallskip' );
        $c = field_class( 'titel' );
        open_td( "top label $c", '', 'Titel:' );
        open_td( "qquad oneline kbd $c" );
          open_span( 'large', '', string_view( $titel, 'titel', 30 ) );
          if( ! $hauptkonten_id )
            selector_titel( 'titel_id', 0, $filters );

      open_tr( 'smallskip' );
        open_td( '', '', 'Kommentar:' );
        open_td( 'qquad' );
          echo "<textarea name='kommentar' rows='4' cols='60' id='textarea_kommentar' >$kommentar</textarea>";

      open_tr( 'smallskip' );
        open_td( 'right', "colspan='2'", html_submission_button( 'save', 'Speichern' ) );

}
}
    close_table();
  // close_form();

  if( $hauptkonten_id ) {
    open_div( 'smallskip' );
      echo 'Status:';
      if( $hauptkonto_geschlossen ) {
        open_span( 'quads', '', 'Konto ist geschlossen' );
        if( $kann_oeffnen ) {
          open_span( 'quads', '', inlink( '!submit', "class=button,text=oeffnen,confirm=wieder oeffnen?,action=oeffnen,message=$hauptkonten_id" ) );
        } else {
          open_span( 'quads small', '', $oeffnen_schliessen_problem );
        }
      } else {
        open_span( 'quads', '', 'offen' );
        if( $kann_schliessen ) {
          open_span( 'quads', '', inlink( '!submit', "class=button,text=schliessen,confirm=konto schliessen?,action=schliessen,message=$hauptkonten_id" ) );
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
        open_div( 'right smallskip', '', inlink( 'unterkonto', "class=bigbutton,text=Neues Unterkonto,hauptkonten_id=$hauptkonten_id" ) );
        smallskip();
        if( count( $uk ) == 0 ) {
          open_div( 'center', '', '(keine Unterkonten vorhanden)' );
        } else {
          if( count( $uk ) == 1 ) {
            $unterkonten_id = $uk[0]['unterkonten_id'];
          } else {
            init_global_var( 'unterkonten_id', 'u', 'http,persistent', 0, 'self' );
          }
          if( $parent_thread != $thread ) {
            $unterkonten_id = 0;  // avoid additional posten-popup after fork
          }
          unterkontenlist_view( array( 'hauptkonten_id' => $hauptkonten_id ), array( 'select' => 'unterkonten_id' ) );
          // if( $unterkonten_id ) {
          //   medskip();
          //   postenlist_view( array( 'unterkonten_id' => $unterkonten_id ) );
          // }
        }
      close_fieldset();
    } else {
      if( $uk ) {
        open_div( 'smallskip', '', inlink( 'self', array(
          'options' => $options | OPTION_SHOW_UNTERKONTEN, 'class' => 'button', 'text' => 'Unterkonten anzeigen'
        ) ) );
      } else {
        open_div( 'center smallskip', '', '(keine Unterkonten vorhanden)' );
        open_div( 'right', '', inlink( 'unterkonto', "class=bigbutton,text=Neues Unterkonto,hauptkonten_id=$hauptkonten_id" ) );
      }
    }
  }

close_fieldset();

// js_on_exit( "if( confirm( 'bla' ) ) alert( 'blubb' ); " );

?>
