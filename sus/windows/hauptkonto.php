<?php


define( 'OPTION_SHOW_UNTERKONTEN', 1 );
define( 'OPTION_SHOW_POSTEN', 2 );
init_var( 'options', 'global,pattern=u,sources=http persistent,set_scopes=window,default='.OPTION_SHOW_UNTERKONTEN );

do {
  $reinit = false;

  init_var( 'geschaeftsjahr', "global,pattern=u,sources=http persistent,default=$geschaeftsjahr_thread,set_scopes=self" );
  init_var( 'hauptkonten_id', 'global,pattern=u,sources=http persistent,set_scopes=self' );
  init_var( 'flag_problems', 'pattern=u,sources=persistent,default=0,global,set_scopes=self' );

  $hauptkonten_fields = l2a( array(
    'seite', 'kontenkreis', 'geschaeftsjahr', 'kontoklassen_id', 'hauptkonten_hgb_klasse', 'titel'
  , 'rubrik', 'kommentar', 'hauptkonto_geschlossen'
  ) );
  if( $hauptkonten_id ) {
    $hk = sql_one_hauptkonto( $hauptkonten_id );
    $flag_modified = 1;
    // cannot be changed for existing accounts:
    $hauptkonten_fields['seite']['readonly'] = 1;
    $hauptkonten_fields['geschaeftsjahr']['readonly'] = 1;
    $hauptkonten_fields['kontenkreis']['readonly'] = 1;
    $hauptkonten_fields['kontoklassen_id']['readonly'] = 1;
  } else {
    $hk = array();
    $flag_modified = 0;
  }

  $opts = array( 'flag_problems' => & $flag_problems , 'flag_modified' => & $flag_modified, 'tables' => array( 'hauptkonten' ) );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $action === 'reset' ) {
    $opts['reset'] = 1;
    $flag_problems = 0;
  }

  $fields = & init_form_fields( $hauptkonten_fields, array( 'hauptkonten' => $hk ), $opts );

  init_global_var( 'rubriken_id', 'x', 'http', '0' );
  if( $rubriken_id ) {
    $rubrik = sql_unique_value( 'hauptkonten', 'rubrik', $rubriken_id );
  } else {
    $rubriken_id = sql_unique_id( 'hauptkonten', 'rubrik', $rubrik );
  }

  init_global_var( 'titel_id', 'w', 'http', '0' );
  if( $titel_id ) {
    $titel = sql_unique_value( 'hauptkonten', 'titel', $titel_id );
  } else {
    $titel_id = sql_unique_id( 'hauptkonten', 'titel', $titel );
  }

  if( $kontoklassen_id ) {
    if( ! ( $klasse = sql_one_kontoklasse( $kontoklassen_id, NULL ) ) ) {
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
  }
  if( $klasse ) {
    $vortragskonto_name = ( $klasse['vortragskonto'] ? 'Vortragskonto '.$klasse['vortragskonto'] : '' );
  } else {
    $vortragskonto_name = '';
  }

  if( $flag_problems ) {
    if( $hk && $hk['hauptkonto_geschlossen'] ) {
      $problems[] = 'Konto geschlossen';
      $fields['_problems']['hauptkonto_geschlossen'] = 1;
    }
    if( ! $kontoklassen_id ) {
      $fields['_problems']['kontoklassen_id'] = 0;
      $fields['kontoklassen_id']['field_class'] = 'problem';
    }
    if(    ( ! $geschaeftsjahr ) 
        || ( $geschaeftsjahr < $geschaeftsjahr_min )
        || ( $geschaeftsjahr > $geschaeftsjahr_max )
        || ( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen ) ) {
      $problems[] = 'geschaeftsjahr';
      $fields['_problems']['geschaeftsjahr'] = $geschaeftsjahr;
    }
    if( ! $fields['_problems'] && ! $hauptkonten_id ) {
      if( sql_hauptkonten( array(
        'titel' => $titel, 'rubrik' => $rubrik, 'geschaeftsjahr' => $geschaeftsjahr, 'geschaeftsbereich' => $klasse['geschaeftsbereich'] )
      ) ) {
        $problems[] = 'Hauptkonto mit diesen Attributen existiert bereits';
        $fields['_problems']['exists'] = true;
      }
    }
  }
  
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


  $actions = array( 'update', 'reset', 'deleteUnterkonto', 'unterkontoSchliessen' );
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

      if( ! $problems ) {
        $values = array(
          'rubrik' => $rubrik
        , 'titel' => $titel
        , 'hauptkonten_hgb_klasse' => $hauptkonten_hgb_klasse
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
      reinit();
      break;
  
    case 'schliessen':
      need( $kann_schliessen, $oeffnen_schliessen_problem );
      sql_hauptkonto_schliessen( $hauptkonten_id );
      reinit();
      return;
  
    case 'oeffnen':
       need( $kann_oeffnen, $oeffnen_schliessen_problem );
      sql_update( 'hauptkonten', $hauptkonten_id, array( 'hauptkonto_geschlossen' => 0 ) );
      for( $id = hauptkonten_id, $j = $geschaeftsjahr; $j < $geschaeftsjahr_max; $j++ ) {
        $id = sql_hauptkonto_folgekonto_anlegen( $id );
      }
      reinit();
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

} while( $reinit );


if( $hauptkonten_id ) {
  open_fieldset( 'small_form old', "Stammdaten Hauptkonto [$hauptkonten_id]" );
} else {
  open_fieldset( 'small_form new', 'neues Hauptkonto' );
}
  open_table( 'small_form,colgroup=15% 85%');
    open_tr( 'smallskip' );
      $c = field_class( 'geschaeftsjahr' );
      open_td( 'label=geschaftsjahr', "Gesch{$aUML}ftsjahr:" );
      open_td();

if( $hauptkonten_id ) {
        $pred = sql_one_hauptkonto( array( 'folge_hauptkonten_id' => $hauptkonten_id ), true );
        $pred_id = adefault( $pred, 'hauptkonten_id', 0 );
        open_span( '', inlink( '', array(
          'class' => 'button', 'text' => ' < ', 'hauptkonten_id' => $pred_id , 'inactive' => ( $pred_id == 0 )
        ) ) );
        open_span( 'quads kbd bold', $geschaeftsjahr );
        $succ_id = $hk['folge_hauptkonten_id'];
        open_span( '', inlink( '', array( 'class' => 'button', 'text' => ' > '
                                                   , 'hauptkonten_id' => $succ_id, 'inactive' => ( $succ_id == 0 )
        ) ) );

    open_tr( 'smallskip' );
      open_td( 'oneline', 'Kreis/Seite:' );
      open_td( 'qquad bold' );
        echo kontenkreis_name( $kontenkreis );
        echo ' '.seite_name( $seite );
        qquad();

    open_tr( 'smallskip' );
      open_td( '', 'Kontoklasse:' );
      open_td();
        open_span( 'bold', $klasse['cn'] );
        if( $klasse['geschaeftsbereich'] ) {
          open_span( 'quad bold', $klasse['geschaeftsbereich'] );
        }
        if( $vortragskonto_name ) {
          open_span( 'bold qquad', $vortragskonto_name );
        }

} else {
        filter_geschaeftsjahr( '', false );
    open_tr( 'smallskip' );
      open_td( 'label=kontenkreis', 'Kontenkreis:' );
      open_td();
        selector_kontenkreis( 'kontenkreis' );
        open_label( 'seite', 'qquad', 'Seite:' );
        selector_seite( 'seite' );

    open_tr( 'smallskip' );
      open_td( 'label=kontoklassen_id', 'Kontoklasse:' );
      open_td( "qquad kbd $c" );
        if( $kontenkreis )
          $filters['kontenkreis'] = $kontenkreis;
        if( $seite )
          $filters['seite'] = $seite;
        selector_kontoklasse( 'kontoklassen_id', $kontoklassen_id, $filters );
        if( $vortragskonto_name )
          echo " $vortragskonto_name";
}

if( $kontoklassen_id ) {

    open_tr( 'smallskip' );
      open_td( 'label=hauptkonten_hgb_klasse', 'HGB-Klasse:' );
      open_td();
        selector_hgb_klasse( 'hauptkonten_hgb_klasse', $hauptkonten_hgb_klasse, $kontenkreis, $seite );

    open_tr( 'smallskip' );
      open_td( 'top,label=rubrik', 'Rubrik:' );
      open_td();
        open_span( 'large', string_element( 'rubrik', 'size=30' ) );
        if( ! $hauptkonten_id )
          selector_rubrik( 'rubriken_id', 0, $filters );
        if( $rubriken_id )
          $filters['rubriken_id'] = $rubriken_id;

    open_tr( 'smallskip' );
      open_td( 'top,label=titel', 'Titel:' );
      open_td();
        open_span( 'large', string_element( 'titel', 'size=30' ) );
        if( ! $hauptkonten_id )
          selector_titel( 'titel_id', 0, $filters );

    open_tr( 'smallskip' );
      open_td( 'label=kommentar', 'Kommentar:' );
      open_td( 'qquad', textarea_element( 'kommentar', 'rows=4,cols=60' ) );

    open_tr( 'smallskip' );
      open_td( 'right,colspan=2' );
        submission_button( ( $fields['_changes'] ? '' : 'display=none,' ) . 'text=Speichern' );
        // if( $hauptkonten_id && ! $fields['_changes'] )
        //   template_button();
        reset_button( $fields['_changes'] ? '' : 'display=none' );

}

  close_table();


  if( $hauptkonten_id ) {
    open_div( 'smallskip' );
      echo 'Status:';
      if( $hauptkonto_geschlossen ) {
        open_span( 'quads', 'Konto ist geschlossen' );
        if( $kann_oeffnen ) {
          open_span( 'quads', inlink( '!submit', "class=button,text=oeffnen,confirm=wieder oeffnen?,action=oeffnen,message=$hauptkonten_id" ) );
        } else {
          open_span( 'quads small', $oeffnen_schliessen_problem );
        }
      } else {
        open_span( 'quads', 'offen' );
        if( $kann_schliessen ) {
          open_span( 'quads', inlink( '!submit', "class=button,text=schliessen,confirm=konto schliessen?,action=schliessen,message=$hauptkonten_id" ) );
        } else {
          open_span( 'quads small', $oeffnen_schliessen_problem );
        }
      }
    close_div();

    init_var( 'unterkonten_id', 'global,pattern=u,sources=http persistent,default=0,set_scopes=self' );
    $uk = sql_unterkonten( array( 'hauptkonten_id' => $hauptkonten_id ) );
    if( $options & OPTION_SHOW_UNTERKONTEN ) {
      open_fieldset( 'small_form'
        , inlink( 'self', array( 'options' => $options & ~OPTION_SHOW_UNTERKONTEN , 'class' => 'close_small' ) )
          . ' Unterkonten: '
      );
        open_div( 'right smallskip', inlink( 'unterkonto', "class=bigbutton,text=Neues Unterkonto,hauptkonten_id=$hauptkonten_id" ) );
        smallskip();
        if( count( $uk ) == 0 ) {
          open_div( 'center', '(keine Unterkonten vorhanden)' );
        } else {
          if( count( $uk ) == 1 ) {
            $unterkonten_id = $uk[0]['unterkonten_id'];
          }
          unterkontenlist_view( array( 'hauptkonten_id' => $hauptkonten_id ), array( 'select' => 'unterkonten_id' ) );
        }
      close_fieldset();
    } else {
      if( $uk ) {
        open_div( 'smallskip', inlink( 'self', array(
          'options' => $options | OPTION_SHOW_UNTERKONTEN, 'class' => 'button', 'text' => 'Unterkonten anzeigen'
        ) ) );
      } else {
        open_div( 'center smallskip', '(keine Unterkonten vorhanden)' );
        open_div( 'right', inlink( 'unterkonto', "class=bigbutton,text=Neues Unterkonto,hauptkonten_id=$hauptkonten_id" ) );
      }
    }

    if( $options & OPTION_SHOW_UNTERKONTEN ) {
      if( $unterkonten_id ) {
        $posten = sql_posten( array( 'unterkonten_id' => $unterkonten_id ) );
        if( $posten ) {
          if( $options & OPTION_SHOW_POSTEN ) {
            medskip();
            open_fieldset( 'small_form'
              , inlink( 'self', array( 'options' => $options & ~OPTION_SHOW_POSTEN , 'class' => 'close_small' ) )
                . ' Posten: '
            );
            postenlist_view( array( 'unterkonten_id' => $unterkonten_id ) );
            close_fieldset();
          } else {
            open_div( 'smallskip', inlink( 'self', array(
              'options' => $options | OPTION_SHOW_POSTEN, 'class' => 'button', 'text' => count( $posten ) . ' Posten - anzeigen'
            ) ) );
          }
        } else {
          open_div( 'center', '(keine Posten vorhanden)' );
        }
      }
    }
  }

close_fieldset();


?>
