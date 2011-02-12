<?php

init_global_var( 'options', 'u', 'http,persistent', 0, 'window' );

init_global_var( 'darlehen_id', 'u', 'http,persistent', 0, 'self' );
$darlehen = ( $darlehen_id ? sql_one_darlehenkonto( $darlehen_id ) : false );
row2global( 'darlehen', $darlehen );

$problems = array();

$fields = array(
  'people_id' => 'U'
, 'darlehen_unterkonten__id' => 'u'
, 'zins_unterkonten_id' => 'u'
, 'zinssatz' => 'f'
, 'betrag_zugesagt' => 'f'
, 'tilgungsbeginn_jahr' => 'u'
);
foreach( $fields as $field => $pattern )
  init_global_var( $field, $pattern, 'http,persistent,keep', '', 'self' );

handle_action( array( 'save', 'update', 'init' ) );
switch( $action ) {
  case 'init':
    $darlehen_id = 0;
    $tilgungsbeginn_jahr = $geschaeftsjahr_current + 1;
    break;

  case 'save':
    foreach( $fields as $fieldname => $type ) {
      if( checkvalue( $fieldname, $type ) !== NULL )
        $values[ $fieldname ] = $$fieldname;
      else
        $problems[] = $fieldname;
    }
    if( ! $problems ) {
      if( $darlehen_id ) {
        sql_update( 'darlehen', $darlehen_id, $values );
      } else {
        $darlehen_id = sql_insert( 'darlehen', $values );
      }
    }
    break;
}

open_fieldset( 'small_form', '', ( $darlehen_id ? 'Stammdaten Darlehen' : 'neues Darlehen' ) );
  open_form( 'name=update_form', 'action=save' );
    open_table('small_form hfill');
      open_tr();
        open_td( problem_class('people_id'), '', 'Kreditor:' );
        open_td();
if( ! $people_id ) {
          open_select( 'people_id', '', html_options_people(), 'submit' );
} else {
          echo $people_cn;

      open_tr();
        open_td( problem_class('betrag_zugesagt'), '', 'Betrag zugesagt:' );
        open_td( '', '', price_view( $betrag_zugesagt, 'betrag_zugesagt' ) );

      open_tr();
        open_td( problem_class('zinssatz'), '', 'Zinssatz:' );
        open_td( '', '', price_view( $zinssatz, 'zinssatz' ) );

      open_tr();
        open_td( problem_class('darlehenkonto_id'), '', 'Darlehenkonto:' );
        open_td();
if( $darlehen_unterkonten_id ) {
        echo inlink( 'unterkonto', array(
          'class' => 'href', 'text' => $darlehen['darlehenkonto_cn'], 'unterkonten_id' => $darlehen_unterkonten_id
        ) );
} else {
        echo filter_unterkonto( 'darlehenkonto', array( 'personenkonto' => 1, 'people_id' => $people_id ) );
}

      open_tr();
        open_td( problem_class('zinskonto_id'), '', 'Zinskonto:' );
        open_td();
if( $zins_unterkonten_id ) {
        echo inlink( 'unterkonto', array(
          'class' => 'href', 'text' => $darlehen['zinskonto_cn'], 'unterkonten_id' => $zins_unterkonten_id
        ) );
} else {
        echo filter_unterkonto( 'darlehenkonto', array( 'personenkonto' => 1, 'people_id' => $people_id ) );
}
      open_tr();
        open_td( problem_class('zinssatz'), '', 'Zinssatz:' );
        open_td( '', '', price_view( $zinssatz, 'zinssatz' ) );
      open_tr( 'smallskip' );
        open_td( 'right', "colspan='2'", html_submission_button( 'save', 'Speichern' ) );
}
    close_table();
  close_form();
close_fieldset();

if( $darlehen_id ) {
  if( $zahlungsplan_id ) {
    open_fieldset( 'small_form', '', 'Zahlungsplan:' );
      zahlungsplan_view( $zahlungsplan_id );
    close_fieldset();
  } else {
    open_div( 'center' );
    echo "(kein Zahlungsplan)";
    open_span( 'qquad', "style='float:right;'", inlink( 'zahlungsplan', array(
      'class' => 'button', 'text' => 'Zahlungsplan erstellen', 'action' => 'init'
    ) ) );
  }
}

?>
