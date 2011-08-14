
<?php

// prettydump( $_POST, '$_POST' );

init_global_var( 'options', 'u', 'http,persistent', 0, 'window' );

init_global_var( 'darlehen_id', 'u', 'http,persistent', 0, 'self' );
$darlehen = ( $darlehen_id ? sql_one_darlehen( $darlehen_id ) : false );
row2global( 'darlehen', $darlehen );
if( ! $darlehen_id ) {
  init_global_var( 'people_id', 'u', 'http,persistent', 0, 'self' );
  $geschaeftsjahr_darlehen = $geschaeftsjahr_thread;
  $geschaeftsjahr_tilgung_start = $geschaeftsjahr_darlehen;
  $geschaeftsjahr_zinslauf_start = $geschaeftsjahr_darlehen;
  $geschaeftsjahr_tilgung_ende = $geschaeftsjahr_darlehen;
}

$problems = array();
$changes = array();

$fields = array(
  'darlehen_unterkonten_id' => 'u'
, 'zins_unterkonten_id' => 'u'
, 'zins_prozent' => 'f'
, 'betrag_zugesagt' => 'f'
, 'geschaeftsjahr_darlehen' => 'u'
, 'geschaeftsjahr_tilgung_start' => 'u'
, 'geschaeftsjahr_zinslauf_start' => 'u'
, 'geschaeftsjahr_tilgung_ende' => 'u'
);
foreach( $fields as $field => $pattern )
  init_global_var( $field, $pattern, 'http,persistent,keep,default', '', 'self' );

$filters_hk = array( 'personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P', 'geschaeftsjahr' => $geschaeftsjahr_darlehen );
$filters_uk = $filters_hk;
if( $people_id )
  $filters_uk['people_id'] = $people_id;

if( $darlehen_unterkonten_id ) {
  $darlehen_uk = sql_one_unterkonto( $filters_uk + array( 'unterkonten_id' => $darlehen_unterkonten_id, 'zinskonto' => 0 ), 'allownull' );
  if( ! $darlehen_uk ) {
    $darlehen_unterkonten_id = 0;
  } else {
    $people_id = $darlehen_uk['people_id'];
    $filters_uk['people_id'] = $people_id;
  }
}

if( $zins_unterkonten_id ) {
  $zins_uk = sql_one_unterkonto( $filters_uk + array( 'unterkonten_id' => $zins_unterkonten_id, 'zinskonto' => 1 ), 'allownull' );
  if( ! $zins_uk ) {
    $zins_unterkonten_id = 0;
  } else {
    $people_id = $zins_uk['people_id'];
    $filters_uk['people_id'] = $people_id;
  }
}

if( $people_id ) {
  $person = sql_person( $people_id );
}

$geschaeftsjahr_tilgung_start = max( $geschaeftsjahr_darlehen, $geschaeftsjahr_tilgung_start );
$geschaeftsjahr_tilgung_ende = max( $geschaeftsjahr_tilgung_start, $geschaeftsjahr_tilgung_ende );
$geschaeftsjahr_zinslauf_start = max( $geschaeftsjahr_darlehen, $geschaeftsjahr_zinslauf_start );

handle_action( array( 'save', 'update', 'init', 'darlehenkontoAnlegen', 'zinskontoAnlegen', 'zahlungsplanBerechnen' ) );

if( ( $darlehen_hauptkonten_id = get_http_var( 'darlehen_hauptkonten_id', 'u' ) ) )
  $action = 'darlehenkontoAnlegen';
if( ( $zins_hauptkonten_id = get_http_var( 'zins_hauptkonten_id', 'u' ) ) )
  $action = 'zinskontoAnlegen';

switch( $action ) {
  case 'init':
    $darlehen_id = 0;
    break;

  case 'save':
    foreach( $fields as $fieldname => $type ) {
      if( checkvalue( $$fieldname, $type ) !== NULL )
        $values[ $fieldname ] = $$fieldname;
      else
        $problems[] = $fieldname;
    }
    if( $people_id )
      $values['people_id'] = $people_id;
    else
      $problems[] = 'people_id';
    if( ! $problems ) {
      if( $darlehen_id ) {
        sql_update( 'darlehen', $darlehen_id, $values );
      } else {
        $darlehen_id = sql_insert( 'darlehen', $values );
      }
      schedule_reload();
      return;
    }
    break;

  case 'darlehenkontoAnlegen':
    need( $people_id, 'keine Person gewaehlt' );
    $hk = sql_one_hauptkonto( $hk_filters + array( 'hauptkonten_id' => $darlehen_hauptkonten_id ), 'allow_null' );
    need( $hk, 'ungeeignetes Hauptkonto' );
    $darlehen_unterkonten_id = sql_insert( 'unterkonten', array(
      'hauptkonten_id' => $darlehen_hauptkonten_id
    , 'people_id' => $people_id
    , 'zinskonto' => 0
    , 'cn' => 'Darlehenkonto ' . $person['cn']
    ) );
    for( $id = $darlehen_unterkonten_id, $j = $geschaeftsjahr_darlehen; $j < $geschaeftsjahr_max; $j++ ) {
      $id = sql_unterkonto_folgekonto_anlegen( $id );
    }
    openwindow( 'unterkonto', "unterkonten_id=$darlehen_unterkonten_id" );
    break;

  case 'zinskontoAnlegen':
    need( $people_id, 'keine Person gewaehlt' );
    $hk = sql_one_hauptkonto( $hk_filters + array( 'hauptkonten_id' => $darlehen_hauptkonten_id ), 'allow_null' );
    need( $hk, 'ungeeignetes Hauptkonto' );
    $zins_unterkonten_id = sql_insert( 'unterkonten', array(
      'hauptkonten_id' => $darlehen_hauptkonten_id
    , 'people_id' => $people_id
    , 'zinskonto' => 1
    , 'cn' => 'Zinskonto ' . $person['cn']
    ) );
    for( $id = $zins_unterkonten_id, $j = $geschaeftsjahr_darlehen; $j < $geschaeftsjahr_max; $j++ ) {
      $id = sql_unterkonto_folgekonto_anlegen( $id );
    }
    openwindow( 'unterkonto', "unterkonten_id=$zins_unterkonten_id" );
    break;

  default:
  case '':
  case 'nop':
    break;
}

open_fieldset( 'small_form', ( $darlehen_id ? 'Stammdaten Darlehen' : 'neues Darlehen' ) );
  // open_form( 'name=update_form', 'action=save' );
    open_table( 'small_form hfill' );
      open_tr();
        $c = field_class( 'people_id' );
        open_td( "label $c", 'Kreditor:' );
        open_td( "kbd $c" );
if( ! $people_id ) {
          selector_people( 'people_id', 0 );
} else {
          $person = sql_person( $people_id );
          echo inlink( 'person', array( 'class' => 'href', 'people_id' => $people_id, 'text' => $person['cn'] ) );

      open_tr();
        open_td( 'oneline', 'Geschaeftsjahr Darlehen:' );
        open_td( 'oneline' );
          selector_int( $geschaeftsjahr_darlehen, 'geschaeftsjahr_darlehen', $geschaeftsjahr_min, $geschaeftsjahr_max + 42 );

      open_tr();
        $c = field_class('betrag_zugesagt');
        open_td( "oneline label $c", 'Betrag zugesagt:' );
        open_td( "oneline kbd $c", price_view( $betrag_zugesagt, 'betrag_zugesagt' ) );

      open_tr();
        $c = field_class('zins_prozent');
        open_td( "oneline label $c", 'Zinssatz:' );
        open_td( "oneline kbd $c", price_view( $zins_prozent, 'zins_prozent' ) );

      open_tr();
        $c = field_class('darlehen_unterkonten_id');
        open_td( "oneline label $c", 'Darlehenkonto:' );
        open_td( "top kbd $c" );
          open_div();
            selector_unterkonto( 'darlehen_unterkonten_id', $darlehen_unterkonten_id, $filters_uk + array( 'zinskonto' => 0 ), array(
              0 => ' - kein Darlehenkonto - ', '!empty' => '(keine geeigneten Unterkonten angelegt!)'
            ) );
          close_div();
          if( $darlehen_unterkonten_id ) {
            open_div( '', inlink( 'unterkonto', "text=zum Unterkonto...,class=href,unterkonten_id=$darlehen_unterkonten_id" ) );
          }
        open_td( 'qquad top' );
          selector_hauptkonto( 'darlehen_hauptkonten_id', 0, $filters_hk, array(
            '' => 'Neues Darlehenkonto anlegen', '!empty' => 'keine geeigneten Hauptkonten angelegt!'
          ) );

      open_tr();
        $c = field_class('zins_unterkonten_id');
        open_td( "label $c", 'Zinskonto:' );
        open_td( "top kbd $c" );
          open_div();
            selector_unterkonto( 'zins_unterkonten_id', $zins_unterkonten_id, $filters_uk + array( 'zinskonto' => 1 ), array(
              0 => ' - kein Zinskonto - ', '!empty' => '(keine geeigneten Unterkonten angelegt)'
            ) );
          close_div();
          if( $zins_unterkonten_id ) {
            open_div( '', inlink( 'unterkonto', "text=zum Unterkonto...,class=href,unterkonten_id=$zins_unterkonten_id" ) );
          }
        open_td( 'qquad top' );
            selector_hauptkonto( 'zins_hauptkonten_id', 0 , $filters_hk , array(
              '' => 'Neues Darlehenkonto anlegen', '!empty' => '(keine geeigneten Hauptkonten angelegt)'
            ) );

      open_tr();
        $c = field_class('geschaftsjahr_zinslauf_start');
        open_td( "oneline label $c", 'Zinslauf ab Anfang Jahr:' );
        open_td( "oneline kbd $c" );
          selector_int( $geschaeftsjahr_zinslauf_start, 'geschaeftsjahr_zinslauf_start', $geschaeftsjahr_darlehen, $geschaeftsjahr_darlehen + 99 );

      open_tr();
        $c = field_class('geschaftsjahr_tilgung_start');
        open_td( "oneline label $c", 'Tilgung ersmals Anfang Jahr:' );
        open_td( "oneline kbd $c" );
          selector_int( $geschaeftsjahr_tilgung_start, 'geschaeftsjahr_tilgung_start', $geschaeftsjahr_darlehen, $geschaeftsjahr_darlehen + 99 );

      open_tr();
        $c = field_class('geschaftsjahr_tilgung_ende');
        open_td( "oneline label $c", 'Tilgung letztmalig Anfang Jahr:' );
        open_td( "oneline kbd $c" );
          selector_int( $geschaeftsjahr_tilgung_ende, 'geschaeftsjahr_tilgung_ende', $geschaeftsjahr_tilgung_start, $geschaeftsjahr_darlehen + 99 );

      open_tr( 'smallskip' );
        open_td( 'right,colspan=2', html_submission_button( 'save', 'Speichern' ) );
}
    close_table();
  // close_form();
close_fieldset();

if( $darlehen_id ) {
  $zahlungsplan = sql_zahlungsplan( "darlehen_id=$darlehen_id" );
  if( ! $zahlungsplan ) {
    open_fieldset( 'small_form', 'Zahlungsplan:' );
      zahlungsplan_view( $darlehen_id );
    close_fieldset();
  } else {
    open_div( 'center' );
      echo "(kein Zahlungsplan)";
      qquad();
      echo inlink( '!submit', 'class=bigbutton,text=Zahlungsplan erstellen,action=zahlungsplanBerechnen' );
    close_div();
  }
}

?>
