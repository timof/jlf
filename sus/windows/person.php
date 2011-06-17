<?php

// init form data, when called from a different script:
//
init_global_var( 'people_id', 'u', 'http,persistent', 0, 'self' );
$person = ( $people_id ? sql_person( $people_id ) : false );
row2global( 'people', $person );

$auth_methods_array = explode( ',', $authentication_methods );
$auth_method_simple = ( in_array( 'simple', $auth_methods_array ) ? 1 : 0 );
$auth_method_ssl = ( in_array( 'ssl', $auth_methods_array ) ? 1 : 0 );

$problems = array();

// update values from submitted form, or preset by caller:
//
init_global_var( 'title', 'h', 'http,keep' );
init_global_var( 'gn', 'h', 'http,keep' );
init_global_var( 'sn', 'h', 'http,keep' );
init_global_var( 'cn', 'h', 'http,keep' );
init_global_var( 'jperson', '/^[01]$/', 'http,keep' );
init_global_var( 'mail', 'h', 'http,keep' );
init_global_var( 'street', 'h', 'http,keep' );
init_global_var( 'street2', 'h', 'http,keep' );
init_global_var( 'city', 'h', 'http,keep' );
init_global_var( 'note', 'h', 'http,keep' );
init_global_var( 'telephonenumber', 'h', 'http,keep' );
init_global_var( 'uid', 'w', 'http,keep' );
init_global_var( 'auth_method_simple', 'u', 'http,keep' );
init_global_var( 'auth_method_ssl', 'u', 'http,keep' );
$auth_methods_array = array();
if( $auth_method_simple )
  $auth_methods_array[] = 'simple';
if( $auth_method_ssl )
  $auth_methods_array[] = 'ssl';
$authentication_methods = implode( ',', $auth_methods_array );


handle_action( array( 'save', 'update', 'init', 'template', 'unterkontoSchliessen' ) ); 
switch( $action ) {
  case 'template':
    $people_id = 0;
    break;

  case 'init':
    $people_id = 0;
    break;

  case 'save':
    if( ! $cn )
      $problems[] = 'cn';
    switch( "$jperson" ) {
      case '0':
      case '1':
        break;
      default:
        $problems[] = 'jperson';
    }
    if( $auth_method_simple || $auth_method_ssl ) {
      if( ! $uid )
        $problems[] = 'uid';
    }
    if( ! $problems ) {
      $values = array(
        'title' => $title
      , 'gn' => $gn
      , 'sn' => $sn
      , 'cn' => $cn
      , 'jperson' => $jperson
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

open_fieldset( 'small_form', '', ( $people_id ? 'Stammdaten Person' : 'neue Person' ) );
  open_form( 'name=update_form', 'action=save' );
    open_table('small_form hfill');
      open_tr();
        open_td( problem_class('jperson'), '', 'Art:' );
        open_td();
          radio_button( 'jperson', '0', '', 'natürlich' );
          quad();
          radio_button( 'jperson', '1', '', 'juristisch' );
      open_tr( 'medskip', "id='firma'" );
        open_td( 'medskip bold bottom '.problem_class('cn'), '', 'cn:' );
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

      open_tr();
        open_td( 'bold medskip', '', 'Zugang:' );
      form_row_text( 'User-Id:', 'uid', 40, $uid );
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

    open_form( 'script=unterkonto', array( 'action' => 'init', 'cn' => $cn, 'people_id' => $people_id ) );
      open_div( 'oneline' );
        echo "Neues Personenkonto anlegen:";
        filter_hauptkonto( '', "kontenkreis=B,personenkonto=1,geschaeftsjahr=$geschaeftsjahr_current", ' (kein Konto) ' );
      close_div();
    close_form();

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
  }
close_fieldset();

if( $people_id ) {
  open_fieldset( 'small_form', '', 'Darlehen' );
    open_div( 'right', '', inlink( 'darlehen', array( 
      'class' => 'button', 'text' => 'Neues Darlehen', 'people_id' => $people_id
    ) ) );
    smallskip();
    darlehenlist_view( array( 'people_id' => $people_id ), '' );
  close_fieldset();
}

?>
