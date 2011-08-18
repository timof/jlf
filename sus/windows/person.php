<?php

// init form data, when called from a different script:
//
init_global_var( 'people_id', 'u', 'http,persistent', 0, 'self' );
if( $people_id ) {
  $person = ( $people_id ? sql_person( $people_id ) : false );
  $auth_methods_array = explode( ',', $person['authentication_methods'] );
  $auth_method_simple = $person['auth_method_simple'] = ( in_array( 'simple', $auth_methods_array ) ? 1 : 0 );
  $auth_method_ssl = $person['auth_method_ssl'] = ( in_array( 'ssl', $auth_methods_array ) ? 1 : 0 );
} else {
  $person = false;
  $auth_method_simple = 0;
  $auth_method_ssl = 0;
}
row2global( 'people', $person );


$problems = array();
$changes = array();

// update values from submitted form, or preset by caller:
//
$fields = array(
  'title' => 'h'
, 'gn' => 'h'
, 'sn' => 'h'
, 'cn' => 'h'
, 'jperson' => 'b'
, 'mail' => 'h'
, 'street' => 'h'
, 'street2' => 'h'
, 'city' => 'h'
, 'note' => 'h'
, 'telephonenumber' => 'h'
, 'uid' => 'w'
, 'auth_method_simple' => 'b'
, 'auth_method_ssl' => 'b'
);
foreach( $fields as $fieldname => $type ) {
  init_global_var( $fieldname, $type, 'http,persistent,keep', '', 'self' );
  if( $people_id ) {
    if( $GLOBALS[ $fieldname ] !== $person[ $fieldname ] ) {
      $changes[ $fieldname ] = 'modified';
    }
  }
}


handle_action( array( 'save', 'update', 'init', 'template', 'unterkontoSchliessen', 'deleteUnterkonto' ) ); 
switch( $action ) {
  case 'template':
    $people_id = 0;
    break;

  case 'init':
    $people_id = 0;
    break;

  case 'save':
    $values = arrau();
    foreach( $fields as $fieldname => $type ) {
      if( checkvalue( $fieldname, $type ) !== NULL) {
        $values[ $fieldname ] = $$fieldname;
      } else {
        $problems[ $fieldname ] = 'type mismatch';
      }
    }
    if( $auth_method_simple || $auth_method_ssl ) {
      if( ! $uid )
        $problems[] = 'uid';
    }
    if( ! $problems ) {
      $auth_methods_array = array();
      if( $auth_method_simple )
        $auth_methods_array[] = 'simple';
      if( $auth_method_ssl )
        $auth_methods_array[] = 'ssl';
      $values['authemtication_methods'] = implode( ',', $auth_methods_array );
      unset( $values['auth_method_ssl'] );
      unset( $values['auth_method_simple'] );

      if( $people_id ) {
        sql_update( 'people', $people_id, $values );
      } else {
        $people_id = sql_insert( 'people', $values );
        if( gdefault( 'hauptkonten_id', 0 ) ) {
          sql_insert( 'unterkonten', array(
            'hauptkonten_id' => $hauptkonten_id
          , 'people_id' => $people_id
          , 'cn' => $unterkonten_cn
          ) );
        }
      }
    }
    break;

  case 'deleteUnterkonto':
    need( $message > 0, 'kein unterkonto gewaehlt' );
    sql_delete_unterkonten( $message );
    break;

  case 'unterkontoSchliessen':
    need( $message > 0, 'kein unterkonto gewaehlt' );
    sql_unterkonto_schliessen( $message );
    break;
}

if( $people_id ) {
  open_fieldset( 'small_form edit', 'Stammdaten Person' );
} else {
  open_fieldset( 'small_form new', 'neue Person' );
}
  open_table('hfill,colgroup=20% 30% 50%');
    open_tr();
      open_td();
        open_label( 'jperson', '', 'Art:' );
      open_td( 'colspan=2' );
        open_input( 'jperson' );
          radio_button( 'jperson', '0', '', 'natürlich' );
          quad();
          radio_button( 'jperson', '1', '', 'juristisch' );
        close_input();

    open_tr( 'smallskip' );
      open_td();
        open_label( 'cn', '', 'cn:' );
      open_td( 'colspan=2', string_element( 'cn', 'size=40' ) );

    open_tr( 'medskip smallskipb' );
      open_td( 'bold,colspan=3', 'Kontakt:' );

    open_tr();
      open_td( 'label=title', 'Anrede:' );
      open_td( 'colspan=2', string_element( 'title', 'size=12' ) );

    open_tr();
      open_td( 'label=gn', 'Vorname:' );
      open_td( 'colspan=2', string_element( 'gn', 'size=24' ) );

    open_tr();
      open_td( 'label=sn', 'Nachname:' );
      open_td( 'colspan=2', string_element( 'sn', 'size=40' ) );

    open_tr();
      open_td( 'label=mail', 'Email:' );
      open_td( 'colspan=2', string_element( 'mail', 'size=40' ) );

    open_tr();
      open_td( 'label=telephonenumber', 'Telefon:' );
      open_td( 'colspan=2', string_element( 'telephonenumber', 'size=40' ) );

    open_tr( 'medskip smallskipb' );
      open_td( 'colspan=3,bold', 'Adresse:' );

    open_tr();
      open_td( 'label=street', 'Strasse:' );
      open_td( 'colspan=2', string_element( 'street', 'size=40' ) );

    open_tr();
      open_td( 'label=street2', '' );
      open_td( 'colspan=2', string_element( 'street2', 'size=40' ) );

    open_tr();
      open_td( 'label=city', 'Ort:' );
      open_td( 'colspan=2', string_element( 'city', 'size=40' ) );

    open_tr( 'medskip smallskipb' );
      open_td( 'colspan=3,bold', 'Zugang:' );

    open_tr();
      open_td( 'label=uid', 'User-Id:' );
      open_td( 'colspan=2', string_element( 'uid', 'size=20' ) );

    open_tr();
      open_td( 'right, label=auth_method_simple', 'simple auth:' );
      open_td( 'colspan=2' );
        open_input( 'auth_method_simple' );
        radio_button( 'auth_method_simple', 1, '', 'ja' );
        quad();
        radio_button( 'auth_method_simple', 0, '', 'nein' );
        close_input();

    open_tr();
      open_td( 'right,label=auth_method_ssl', 'ssl auth:' );
      open_td( 'colspan=2' );
        open_input( 'auth_method_ssl' );
        radio_button( 'auth_method_ssl', 1, '', 'ja' );
        quad();
        radio_button( 'auth_method_ssl', 0, '', 'nein' );
        close_input();

    open_tr( 'medskip' );
      open_td( 'bold top,label=note', 'Kommentar:' );
      open_td( 'colspan=2', textarea_element( 'note', 'rows=4,cols=40' ) );

    open_tr();
      open_td( 'right,colspan=3' );
        if( $people_id && ! $changes )
          template_button();
        submission_button();
  close_table();

  if( $people_id ) {

    open_fieldset( 'small_form', 'Personenkonten' );

      $uk = sql_unterkonten( array( 'people_id' => $people_id ) );
      if( ! $uk ) {
        open_div( 'center', '(keine Personenkonten vorhanden)' );
        medskip();
      }

      open_div( 'oneline' );
        echo "Neues Personenkonto anlegen:";
        filter_hauptkonto( '', "kontenkreis=B,personenkonto=1,geschaeftsjahr=$geschaeftsjahr_current", ' (kein Konto) ' );
      close_div();

      if( $uk ) {
        medskip();
        if( count( $uk ) == 1 ) {
          $unterkonten_id = $uk[0]['unterkonten_id'];
        } else {
          init_global_var( 'unterkonten_id', 'u', 'http,persistent', 0, 'self' );
        }
        unterkontenlist_view( array( 'people_id' => $people_id ), array( 'select' => 'unterkonten_id' ) );
        if( $unterkonten_id ) {
          bigskip();
          postenlist_view( array( 'unterkonten_id' => $unterkonten_id ) );
        }
      }

    close_fieldset();

    open_fieldset( 'small_form', 'Darlehen' );
      open_div( 'right', inlink( 'darlehen', array( 
        'class' => 'button', 'text' => 'Neues Darlehen', 'people_id' => $people_id
      ) ) );
      smallskip();
      darlehenlist_view( array( 'people_id' => $people_id ), '' );
    close_fieldset();
  }

close_fieldset();

?>
