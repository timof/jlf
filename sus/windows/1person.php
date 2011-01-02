<?php

// init form data, when called from a different script:
//
get_http_var( 'people_id', 'u', 0, true );
$person = ( $people_id ? sql_person( $people_id ) : false );
row2global( 'people', $person );
if( $people_id ) {
  $jperson = ( $jperson ? 'J' : 'N' );
} else {
  $jperson = '';
}
$auth_methods_array = explode( ',', $authentication_methods );
$auth_method_simple = ( in_array( 'simple', $auth_methods_array ) ? 1 : 0 );
$auth_method_ssl = ( in_array( 'ssl', $auth_methods_array ) ? 1 : 0 );

$problem_cn = '';
$problem_uid = '';
$problem_jperson = '';
$problems = false;

// update values from submitted form, or preset by caller:
//
get_http_var( 'title', 'h', $title );
get_http_var( 'gn', 'h', $gn );
get_http_var( 'sn', 'h', $sn );
get_http_var( 'cn', 'h', $cn );
get_http_var( 'jperson', '/^[01JN]$/', $jperson );
switch( $jperson ) {
  case '0':
  case 'N':
    $jperson = 'N';
    break;
  case '1':
  case 'J':
    $jperson = 'J';
    break;
  default:
    $jperson = '';
}
get_http_var( 'mail', 'h', $mail );
get_http_var( 'street', 'h', $street );
get_http_var( 'street2', 'h', $street2 );
get_http_var( 'city', 'h', $city );
get_http_var( 'note', 'h', $note );
get_http_var( 'telephonenumber', 'h', $telephonenumber );
get_http_var( 'uid', 'w', $uid );
get_http_var( 'auth_method_simple', 'u', $auth_method_simple );
get_http_var( 'auth_method_ssl', 'u', $auth_method_ssl );
$auth_methods_array = array();
if( $auth_method_simple )
  $auth_methods_array[] = 'simple';
if( $auth_method_ssl )
  $auth_methods_array[] = 'ssl';
$authentication_methods = implode( ',', $auth_methods_array );

// get_http_var( 'hauptkonten_id', 'u', 0 );
// get_http_var( 'unterkonten_cn', 'h', $cn );


// init_and_update();

handle_action( array( 'save', 'update', 'init' ) ); 
switch( $action ) {
  case 'save':
    if( ! $cn ) {
      $problem_cn = 'problem';
      $problems = true;
    }
    switch( $jperson ) {
      case 'J':
      case 'N':
        break;
      default:
        $problem_jperson = 'problem';
        $problems = true;
    }
    if( $auth_method_simple || $auth_method_ssl ) {
      if( ! $uid ) {
        $problem_uid = 'problem';
        $problems = true;
      }
    }
    if( ! $problems ) {
      $values = array(
        'title' => $title
      , 'gn' => $gn
      , 'sn' => $sn
      , 'cn' => $cn
      , 'jperson' => ( $jperson == 'J' ? 1 : 0 )
      , 'mail' => $mail
      , 'street' => $street
      , 'street2' => $street2
      , 'city' => $city
      , 'note' => $note
      , 'telephonenumber' => $telephonenumber
      , 'uid' => $uid
      , 'authentication_methods' => $authentication_methods
      );
      if( $people_id ) {
        sql_update( 'people', $people_id, $values );
      } else {
        $people_id = sql_insert( 'people', $values );
        $self_fields['people_id'] = $people_id;
        if( $hauptkonten_id ) {
          sql_insert( 'unterkonten', array(
            'hauptkonten_id' => $hauptkonten_id
          , 'people_id' => $people_id
          , 'cn' => $unterkonten_cn
          ) );
        }
      }
    }
    break;
}

open_fieldset( 'small_form', '', ( $people_id ? 'Stammdaten Person' : 'neue Person' ) );
  open_form( 'name=update_form', 'action=save' );
    open_table('small_form hfill');
      open_tr();
        open_td( "$problem_jperson", '', 'Art:' );
        open_td();
          radio_button( 'jperson', 'N', '', 'natürlich' );
          quad();
          radio_button( 'jperson', 'J', '', 'juristisch' );
      open_tr( 'medskip', "id='firma'" );
        open_td( "medskip bold bottom $problem_cn", '', 'cn:' );
        open_td( 'bottom', '', string_view( $cn, 'cn', 40 ) );
      open_tr( '', "id='kontakt'" );
        open_td( 'bold medskip', '', 'Kontakt:' );
      form_row_text( 'Anrede:', 'title', 12, $title );
      form_row_text( 'Vorname:', 'gn', 24, $gn );
      form_row_text( 'Nachname:', 'sn', 40, $sn );
      form_row_text( 'Email: ', 'mail', 40, $mail );
      form_row_text( 'Telefon: ', 'telephonenumber', 40, $telephonenumber );
      open_tr( '', "id='adresse'" );
        open_td( 'bold medskip', '', 'Adresse:' );
      form_row_text( 'Strasse: ', 'street', 40, $street );
      form_row_text( '         ', 'street2', 40, $street2 );
      form_row_text( 'Ort:     ', 'city', 40, $city );
//      if( ! $people_id ) {
//        open_tr();
//          open_td( 'right', '', 'Personenkonto anlegen:' );
//          open_td();
//            open_select( 'hauptkonten_id', '', html_options_hauptkonten( 0, array( 'personenkonto' => 1 ), '(kein Konto)' ) );
//          form_row_text( 'Kontobezeichnung:', 'unterkonten_cn', 40, $unterkonten_cn );
//      }
      open_tr();
        open_td( 'bold medskip', '', 'Zugang:' );
      open_tr();
        open_td( "medskip $problem_uid", '', 'User-ID:' );
        open_td( 'bottom', '', string_view( $uid, 'uid', 40 ) );
      open_tr();
        open_td( 'right', '', 'simple auth:' );
        open_td();
          radio_button( 'auth_method_simple', 1, '', 'ja' );
          quad();
          radio_button( 'auth_method_simple', 0, '', 'nein' );
      open_tr();
        open_td( 'right', '', 'ssl auth:' );
        open_td();
          radio_button( 'auth_method_ssl', 1, '', 'ja' );
          quad();
          radio_button( 'auth_method_ssl', 0, '', 'nein' );
      open_tr();
        open_td( 'bold medskip', '', 'Kommentar:' );
      open_tr();
        open_td( '', "colspan='2'" );
        echo "<textarea name='note' rows='4' cols='60'>$note</textarea>";
      open_tr();
        open_td( 'right', "colspan='2'" );
          submission_button( 'save', 'Speichern', 'button' );
    close_table();
  close_form();

  if( $people_id ) {
    medskip();
    $uk = sql_unterkonten( array( 'people_id' => $people_id ) );
    if( ! $uk ) {
      open_div( 'center', '', '(keine Personenkonten vorhanden)' );
      medskip();
    }

    open_form( 'window=unterkonto', array( 'action' => 'init', 'cn' => $cn, 'people_id' => $people_id ) );
      open_div( 'oneline' );
        echo "Neues Personenkonto anlegen:";
        open_select( 'hauptkonten_id', '', html_options_hauptkonten( 0, array( 'personenkonto' => 1 ) ), 'submit' );
      close_div();
    close_form();

    if( $uk ) {
      medskip();
      if( count( $uk ) == 1 ) {
        $unterkonten_id = $uk[0]['unterkonten_id'];
      } else {
        get_http_var( 'unterkonten_id', 'u', 0, true );
      }
      unterkontenlist_view( array( 'people_id' => $people_id ), 'uk', 'unterkonten_id' );
      if( $unterkonten_id ) {
        bigskip();
        postenlist_view( array( 'unterkonten_id' => $unterkonten_id ), 'p' );
      }
    }
  }
close_fieldset();


?>
