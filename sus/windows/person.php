<?php
// person.php: prototypical edit-script

sql_transaction_boundary('*');

init_var( 'people_id', 'global,type=u,sources=self http,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {

  switch( $reinit ) {
    case 'init':
      // init: the default: initialize from all available sources - useful in first iteration:
      //
      $sources = 'http self initval';
      break;
    case 'self':
      // self: ignore http: reinitialize with variables modified in previous iteration - useful when looping:
      //
      $sources = 'self initval';
      break;
    case 'reset':
      // reset: initialize from db only or generate empty entry from defaults - useful
      //  - when looping after a successful save, to display the actual state after the save
      //  - in first iteration to enforce a reset, usually by passing $action = 'reset'
      //
      $flag_problems = 0;
      $sources = 'initval';
      break;
    default:
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'person,init' );
  }
  init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'people'
  , 'failsafe' => false // allow invalid values (to be flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $people_id ) {
    $person = sql_person( $people_id );
    $opts['rows'] = array( 'people' => $person );
  }

  $f = init_fields( array(
      'title' => 'h,size=12'
    , 'gn' => 'h,size=20'
    , 'sn' => 'h,size=24'
    , 'cn' => 'H,size=40'
    , 'jperson' => ''
    , 'genus' => ''
    , 'dusie' => ''
    , 'mail' => 'h,size=40'
    , 'street' => 'h,size=40'
    , 'street2' => 'h,size=40'
    , 'city' => 'h,size=40'
    , 'note' => 'h,lines=4,cols=60'
    , 'telephonenumber' => 'h,size=20'
    , 'facsimiletelephonenumber' => 'h,size=20'
    , 'uid' => 'w,size=12'
    , 'authentication_method_simple' => 'b'
    , 'authentication_method_ssl' => 'b'
    , 'bank_cn' => 'h,size=40'
    , 'bank_blz' => 'h,size=20'
    , 'bank_kontonr' => 'h,size=20'
    , 'bank_iban' => 'h,size=40'
    )
  , $opts
  );

  if( $flag_problems ) {
    // more consistency checks:
    //
    if( $f['authentication_method_simple']['value'] || $f['authentication_method_ssl']['value'] ) {
      if( ! $f['uid']['value'] ) {
        $f['uid']['class'] = 'problem';
        $f['uid']['problem'] = 'need uid';
        $f['_problems']['uid'] = 'need uid';
      }
    }
  }

  // initialization done (but re-initialization may be triggered by calling reinit() below):
  //
  $reinit = false;

  // handle actions:
  //
  handle_actions( array( 'reset', 'save', 'init', 'template', 'unterkontoSchliessen', 'deleteUnterkonto', 'createUnterkonto', 'deletePerson' ) ); 
  switch( $action ) {
    case 'template':
      $people_id = 0;
      reinit('self');
      break;

    case 'save':
      if( ! $f['_problems'] ) {
        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            $values[ $fieldname ] = $f[ $fieldname ]['value'];
        }
        $people_id = sql_save_person( $people_id, $values );
        reinit('reset');
      }
      break;

    case 'createUnterkonto':
      $hk_field = init_var( 'hauptkonten_id', 'sources=http,type=u' );
      need( $hf_field['value'] && $people_id );
      openwindow( 'unterkonto', array( 'hauptkonten_id' => $hk_field['value'], 'people_id' => $people_id ) );
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

}

if( $people_id ) {
  open_fieldset( 'small_form old', "Stammdaten Person [$people_id]" );
} else {
  open_fieldset( 'small_form new', 'neue Person' );
}
  open_table('hfill,colgroup=10% 30% 60%');
    open_tr();
      open_td( array( 'label' => $f['jperson'] ), 'Art:' );
      open_td( 'colspan=2' );
        open_input( $f['jperson'] );
          echo radiobutton_element( $f['jperson'], array( 'value' => 'N', 'text' => 'natürlich' ) );
          quad();
          echo radiobutton_element( $f['jperson'], array( 'value' => 'J', 'text' => 'juristisch' ) );
        close_input();

    open_tr( 'smallskip' );
      open_td( array( 'label' => $f['cn'] ), 'cn:' );
      open_td( 'colspan=2', string_element( $f['cn'] ) );

    open_tr( 'medskip smallskipb' );
      open_td( 'bold,colspan=3', 'Kontakt:' );

    open_tr();
      open_td( array( 'label' => $f['title'] ), 'Anrede:' );
      open_td( 'colspan=1', string_element( $f['title'] ) );
      open_td( 'colspan=1,oneline' );
        open_label( $f['dusie'], '', 'Anredeform:' );
        echo selector_dusie( $f['dusie'] );
        quad();
        open_label( $f['genus'], '', 'Genus:' );
        echo selector_genus( $f['genus'] );

    open_tr();
      open_td( array( 'label' => $f['gn'] ), 'Vorname:' );
      open_td( 'colspan=1', string_element( $f['gn'] ) );
      open_td( 'oneline' );
        open_label( $f['sn'], '', 'Nachname:' );
        echo string_element( $f['sn'] );

    open_tr();
      open_td( array( 'label' => $f['mail'] ), 'Email:' );
      open_td( 'colspan=2', string_element( $f['mail'] ) );

    open_tr();
      open_td( array( 'label' => $f['telephonenumber'] ), 'Telefon:' );
      open_td( 'colspan=1', string_element( $f['telephonenumber'] ) );
      open_td( 'oneline' );
        open_label( $f['facsimiletelephonenumber'], '', 'Fax:' );
        echo string_element( $f['facsimiletelephonenumber'] );


    open_tr( 'medskip smallskipb' );
      open_td( 'colspan=3,bold', 'Adresse:' );

    open_tr();
      open_td( array( 'label' => $f['street'] ), 'Strasse:' );
      open_td( 'colspan=2', string_element( $f['street'] ) );

    open_tr();
      open_td( array( 'label' => $f['street2'] ), '' );
      open_td( 'colspan=2', string_element( $f['street2'] ) );

    open_tr();
      open_td( array( 'label' => $f['city'] ), 'Ort:' );
      open_td( 'colspan=2', string_element( $f['city'] ) );

    open_tr( 'medskip smallskipb' );
      open_td( 'colspan=3,bold', 'Bankverbindung:' );

    open_tr();
      open_td( array( 'label' => $f['bank_cn'] ), 'Bank:' );
      open_td( 'colspan=2', string_element( $f['bank_cn'] ) );

    open_tr();
      open_td( array( 'label' => $f['bank_blz'] ), 'BLZ:' );
      open_td( 'colspan=1', string_element( $f['bank_blz'] ) );
      open_td( 'oneline' );
        open_label( $f['bank_kontonr'], '', 'Konto-Nr:' );
        echo string_element( $f['bank_kontonr'] );

    open_tr();
      open_td( array( 'label' => $f['bank_iban'] ), 'IBAN:' );
      open_td( 'colspan=2', string_element( $f['bank_iban'] ) );


    open_tr( 'medskip' );
      open_td( array( 'class' => 'bold top', 'label' => $f['note'] ), 'Kommentar:' );
      open_td( 'colspan=2', textarea_element( $f['note'] ) );


    open_tr( 'medskip smallskipb' );
      open_td( 'colspan=3,bold', 'Zugang:' );

    open_tr();
      open_td( array( 'label' => $f['uid'] ), 'User-Id:' );
      open_td( 'colspan=2', string_element( $f['uid'] ) );

    open_tr();
      open_td( array( 'class' => 'right oneline', 'label' => $f['authentication_method_simple'] ), 'simple auth:' );
      open_td( 'colspan=2' );
        open_input( $f['authentication_method_simple'] );
          echo radiobutton_element( $f['authentication_method_simple'], array( 'value' => 1, 'text' => 'ja' ) );
          quad();
          echo radiobutton_element( $f['authentication_method_simple'], array( 'value' => 0, 'text' => 'nein' ) );
        close_input();

    open_tr();
      open_td( array( 'class' => 'right', 'label' => $f['authentication_method_ssl'] ), 'ssl auth:' );
      open_td( 'colspan=2' );
        open_input( $f['authentication_method_ssl'] );
          echo radiobutton_element( $f['authentication_method_ssl'], array( 'value' => 1, 'text' => 'ja' ) );
          quad();
          echo radiobutton_element( $f['authentication_method_ssl'], array( 'value' => 0, 'text' => 'nein' ) );
        close_input();

    open_tr();
      open_td( 'right,colspan=3' );
        if( $people_id ) {
          echo template_button_view();
          if( ! sql_delete_people( $people_id, 'check' ) ) {
            echo inlink( '!submit', "class=button drop,confirm=Person loeschen?,action=deletePerson,message=$people_id,text=Loeschen" );
          }
        }
        echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
        echo save_button_view( $f['_changes'] ? '' : 'display=none' );
  close_table();

  if( $people_id ) {

    open_fieldset( 'small_form', 'Personenkonten' );

      $uk = sql_unterkonten( array( 'people_id' => $people_id ) );
      if( ! $uk ) {
        open_div( 'center', '(keine Personenkonten vorhanden)' );
        medskip();
      }

      open_div( 'right oneline smallskip' );
        echo 'Neues Personenkonto: ' . selector_hauptkonto( NULL, array( 'filters' => 'personenkonto=1' ) );
      close_div();

      if( $uk ) {
        medskip();
        if( count( $uk ) == 1 ) {
          $unterkonten_id = $uk[0]['unterkonten_id'];
        } else {
          init_var( 'unterkonten_id', 'global,type=u,sources=http persistent,set_scopes=self' );
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

if( $action === 'deletePerson' ) {
  need( $people_id > 0, 'keine person ausgewaehlt' );
  sql_delete_people( $people_id );
  js_on_exit( "flash_close_message($H_SQ".we('person deleted','Person gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
