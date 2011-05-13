<?

////////////////////////////////////////
//
// functions to output one row of a form
//
// - the line will usually contain two columns: one for the label, one for the input field
// - if a $fieldname is alread part of $self_fields (ie, defining part of the current view), the value
//   will just be printed and cannot be modified (only applies to types that can be in $self_fields)
// - the last (second) column will not be closed; so e.g. a submission_button() can be appended
//
////////////////////////////////////////



function form_row_date( $label, $fieldname, $initial = 0 ) {
  $year = self_field( $fieldname.'_year' );
  $month = self_field( $fieldname.'_month' );
  $day = self_field( $fieldname.'_day' );
  if( ($year !== NULL) and ($day !== NULL) and ($month !== NULL) ) {
    $date = "$year-$month-$day";
    $fieldname = false;
  } else {
    $date = $initial;
  }
  open_tr();
    open_td( 'label', '', $label );
    open_td( 'kbd oneline' ); echo date_view( $date, $fieldname );
}

function form_row_date_time( $label, $fieldname, $initial = 0 ) {
  $year = self_field( $fieldname.'_year' );
  $month = self_field( $fieldname.'_month' );
  $day = self_field( $fieldname.'_day' );
  $hour = self_field( $fieldname.'_hour' );
  $minute = self_field( $fieldname.'_minute' );
  if( ($year !== NULL) and ($day !== NULL) and ($month !== NULL) and ($hour !== NULL) and ($minute !== NULL) ) {
    $datetime = "$year-$month-$day $hour:$minute";
    $fieldname = false;
  } else {
    $datetime = $initial;
  }
  open_tr();
    open_td( 'label', '', $label );
    open_td( 'kbd' ); echo date_time_view( $datetime, $fieldname );
}

function form_row_betrag( $label = 'Betrag:' , $fieldname = 'betrag', $initial = 0.0 ) {
  open_tr();
    open_td( 'label', '', $label );
    open_td( 'kbd' ); echo price_view( $initial, $fieldname );
}

function form_row_int( $label = 'Zahl:' , $fieldname = 'zahl', $initial = 0 ) {
  open_tr();
    open_td( 'label', '', $label );
    open_td( 'kbd' ); echo int_view( $initial, $fieldname );
}

function form_row_text( $label = 'Notiz:', $fieldname = 'notiz', $size = 60, $initial = '' ) {
  open_tr();
    open_td( 'label', '', $label );
    open_td( 'kbd' ); echo string_view( $initial, $size, $fieldname );
}


//////////////////////////////////////////////////////////////////
//
// functions to output complete forms, usually followed
// by a handler function to deal with the POSTed data
//
//////////////////////////////////////////////////////////////////

function form_finish_transaction( $transaction_id ) {
  global $input_event_handlers;
  open_form( '', "action=finish_transaction,transaction_id=$transaction_id" );
    open_table('layout');
      form_row_konto();
      form_row_kontoauszug();
      form_row_date( 'Valuta:', 'valuta' );
      open_tr();
        open_td( 'right', "colspan='2'" );
        echo "Best&auml;tigen: <input type='checkbox' name='confirm' value='yes' $input_event_handlers>";
        qquad();
        submission_button( 'OK' );
    close_table();
  close_form();
}

function action_finish_transaction() {
  global $transaction_id, $konto_id, $auszug_jahr, $auszug_nr, $valuta_day, $valuta_month, $valuta_year, $confirm;
  global $dienstkontrollblatt_id;
  need_http_var( 'transaction_id', 'U' );
  need_http_var( 'auszug_jahr', 'U' );
  need_http_var( 'auszug_nr', 'U' );
  need_http_var( 'konto_id', 'U' );
  need_http_var( 'valuta_day', 'U' );
  need_http_var( 'valuta_month', 'U' );
  need_http_var( 'valuta_year', 'U' );
  get_http_var( 'confirm', 'w', 'no' );

  if( $confirm != 'yes' )
    return;

  fail_if_readonly();
  nur_fuer_dienst(4);

  $soll_id = -$transaction_id;
  $soll_transaction = sql_get_transaction( $soll_id );

  $haben_id = sql_bank_transaktion(
    $konto_id, $auszug_jahr, $auszug_nr
  , $soll_transaction['soll'], "$valuta_year-$valuta_month-$valuta_day"
  , $dienstkontrollblatt_id, $soll_transaction['kommentar'], 0
  );

  sql_link_transaction( $soll_id, $haben_id );

  return sql_update( 'gruppen_transaktion', $transaction_id, array(
    'dienstkontrollblatt_id' => $dienstkontrollblatt_id
  ) );
}



?>
