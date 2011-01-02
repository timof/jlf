<?php

get_http_var( 'hauptkonten_id', 'u', 0, true );
$hk = ( $hauptkonten_id ? sql_one_hauptkonto( $hauptkonten_id ) : false );
row2global( 'hauptkonten', $hk );

if( ! $hk ) {
  get_http_var( 'kontoart', '/^[BE]$/', 0 );
  get_http_var( 'seite', '/^[AP]$/', 0 );
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
}

switch( $kontoart ) {
  case 'E':
    $kontoart_name = 'Erfolgskonto';
    break;
  case 'B':
    $kontoart_name = 'Bestandskonto';
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
  get_http_var( 'rubrik_id', 'w' );
  $rubrik = sql_unique_value( 'hauptkonten', 'rubrik', $rubrik_id );
}

get_http_var( 'titel', 'h', $titel );
if( $titel ) {
  $titel_id = sql_unique_id( 'hauptkonten', 'titel', $titel );
} else {
  get_http_var( 'titel_id', 'w' );
  $titel = sql_unique_value( 'hauptkonten', 'titel', $titel_id );
}

get_http_var( 'kommentar', 'h', $kommentar );


define( 'OPTION_SHOW_UNTERKONTEN', 1 );
get_http_var( 'options', 'u', 0, true );


handle_action( array( 'save', 'update', 'init' ) ); 
switch( $action ) {
  case 'save':

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
      );
      if( $hauptkonten_id ) {
        sql_update( 'hauptkonten', $hauptkonten_id, $values );
      } else {
        $hauptkonten_id = sql_insert( 'hauptkonten', $values );
        $self_fields['hauptkonten_id'] = $hauptkonten_id;
      }
    }
    break;
}


open_fieldset( 'small_form', '', ( $hauptkonten_id ? 'Stammdaten Hauptkonto': 'neues Hauptkonto' ) );
  open_form( 'name=update_form', "action=init,kontoart=$kontoart" );
    open_table('small_form hfill');
      open_tr();
        open_td( "smallskip", '', "Kontoart:" );
        open_td();
if( ! $kontoart ) {
          open_select( 'kontoart', '', html_options_kontoart(), 'submit' );
} else {
          echo "$kontoart_name";
          hidden_input( 'kontoart' );
      open_tr();
        open_td( "smallskip", '', "Seite:" );
        open_td();
if( ! $seite ) {
          open_select( 'seite', '', html_options_seite(), 'submit' );
} else {
          echo "$seite";
          hidden_input( 'seite' );
      open_tr();
        open_td( 'smallskip '.problem_class( 'kontoklassen_id' ), '', "Kontoklasse:" );
        open_td( 'smallskip' );
          open_select( 'kontoklassen_id' );
            echo html_options_kontoklassen( $kontoklassen_id, "kontoart=$kontoart,seite=$seite" );
          close_select();
      open_tr();
        open_td( 'smallskip top '.problem_class('rubrik'), '', 'Rubrik:' );
        open_td( 'smallskip' );
          open_div();
            open_select( 'rubrik_id', '', html_options_unique( $rubrik_id, 'hauptkonten', 'rubrik' ) );
          close_div();
          open_div( '', '', 'neue Rubrik: ' . string_view( $rubrik_id ? '' : $rubrik, 'rubrik', 30 ) );
      open_tr();
        open_td( 'smallskip top '.problem_class('titel'), '', 'Titel:' );
        open_td( 'smallskip' );
          open_div();
            open_select( 'titel_id', '', html_options_unique( $titel_id, 'hauptkonten', 'titel' ) );
          close_div();
          open_div( '', '', 'neuer Titel: ' . string_view( $titel_id ? '' : $titel, 'titel', 30 ) );
      open_tr();
        open_td( 'smallskip', '', 'Kommentar:' );
        open_td( 'smallskip' );
          echo "<textarea name='kommentar' rows='4' cols='40'>$kommentar</textarea>";
      open_tr();
        open_td( 'right', "colspan='2'", html_submission_button( 'save', 'Speichern' ) );
}
}
    close_table();
  close_form();

  if( $hauptkonten_id ) {
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
          unterkontenlist_view( array( 'hauptkonten_id' => $hauptkonten_id ), 'uk', 'unterkonten_id' );
        }
        if( $unterkonten_id ) {
          bigskip();
          postenlist_view( array( 'unterkonten_id' => $unterkonten_id ), 'p' );
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
