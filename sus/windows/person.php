<?php


if( $parent_script !== 'self' ) {
  $reinit = 'init'; 
} else if( $action === 'reset' ) {
  $reinit = 'reset';
} else {
  $reinit = 'http';
}

do {

  init_var( 'flag_problems', 'global,pattern=b,sources=self,default=1,set_scopes=self' );

  switch( $reinit ) {
    case 'init':
      // generate empty entry plus initialization from http, or init from existing entry:
      $flag_problems = 0;
      init_var( 'people_id', 'global,pattern=u,sources=http,default=0,set_scopes=self' );
      if( ! $people_id ) {
        $sources = 'http default';
        break;
      } else {
        // fall-through...
      }
    case 'reset':
      // re-initialize from db or generate empty entry from defaults:
      init_var( 'people_id', 'global,pattern=u,sources=self,set_scopes=self' );
      $flag_problems = 0;
      $sources = 'keep default';
      break;
    case 'http':
      // init from persistent state, updated from http:
      init_var( 'people_id', 'global,pattern=u,sources=self,set_scopes=self' );
      $sources = 'http self';
      break;
    case 'persistent':
      // reinitialize from persistent state only (useful in reinit-loop):
      init_var( 'people_id', 'global,pattern=u,sources=self,set_scopes=self' );
      $sources = 'self';
      break;
    default:
      error( 'cannot initialize - invalid $reinit' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'people'
  , 'failsafe' => false
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $action === 'reset' ) {
    $opts['reset'] = 1;
    $flag_problems = 0;
  }
  if( $people_id ) {
    $person = sql_person( $people_id );
    $auth_methods_array = explode( ',', $person['authentication_methods'] );
    $person['auth_method_simple'] = ( in_array( 'simple', $auth_methods_array ) ? 1 : 0 );
    $person['auth_method_ssl'] = ( in_array( 'ssl', $auth_methods_array ) ? 1 : 0 );
    $opts['rows'] = array( 'people' => $person );
  }

  $f = init_fields( array(
      'title' => 'h,size=12'
    , 'gn' => 'h,size=24'
    , 'sn' => 'h,size=24'
    , 'cn' => 'H,size=40'
    , 'jperson' => 'b'
    , 'mail' => 'h,size=40'
    , 'street' => 'h,size=40'
    , 'street2' => 'h,size=40'
    , 'city' => 'h,size=40'
    , 'note' => 'h,rows=2,cols=80'
    , 'telephonenumber' => 'h,size=20'
    , 'uid' => 'w,size=12'
    , 'auth_method_simple' => 'b'
    , 'auth_method_ssl' => 'b'
    )
  , $opts
  );

  $auth_methods_array = array();
  if( $f['auth_method_simple']['value'] )
    $auth_methods_array[] = 'simple';
  if( $f['auth_method_ssl']['value'] )
    $auth_methods_array[] = 'ssl';

  if( $flag_problems ) {
    if( $auth_methods_array ) {
      if( ! $f['uid']['value'] ) {
        $f['uld']['class'] = 'problem';
        $f['uld']['problem'] = 'need uid';
        $f['_problems']['uid'] = 'need uid';
      }
    }
  }

  $reinit = false;

  if( $people_id ) {
    $hk_field = init_var( 'hauptkonten_id', 'sources=http default,pattern=u' );
    if( $hk_field['value'] > 0 ) {
      openwindow( 'unterkonto', array( 'hauptkonten_id' => $hk_field['value'], 'people_id' => $people_id ) );
    }
  }

  handle_action( array( 'reset', 'save', 'update', 'init', 'template', 'unterkontoSchliessen', 'deleteUnterkonto' ) ); 
  switch( $action ) {
    case 'template':
      $people_id = 0;
      break;

    case 'init':
      $people_id = 0;
      break;
  
    case 'save':
      if( ! $f['_problems'] ) {
  
        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            $values[ $fieldname ] = $f[ $fieldname ]['value'];
        }
  
        $values['authentication_methods'] = implode( ',', $auth_methods_array );
        unset( $values['auth_method_ssl'] );
        unset( $values['auth_method_simple'] );
  
        if( $people_id ) {
          sql_update( 'people', $people_id, $values );
        } else {
          $people_id = sql_insert( 'people', $values );
        }
        reinit( 'reset' );
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

} while( $reinit );

if( $people_id ) {
  open_fieldset( 'small_form old', "Stammdaten Person [$people_id]" );
} else {
  open_fieldset( 'small_form new', 'neue Person' );
}
  open_table('hfill,colgroup=20% 30% 50%');
    open_tr();
      open_td( array( 'label' => $f['jperson'] ), 'Art:' );
      open_td( 'colspan=2' );
        open_input( $f['jperson'] );
          echo radiobutton_element( $f['jperson'], array( 'value' => '0', 'text' => 'natürlich' ) );
          quad();
          echo radiobutton_element( $f['jperson'], array( 'value' => '1', 'text' => 'juristisch' ) );
        close_input();

    open_tr( 'smallskip' );
      open_td( array( 'label' => $f['cn'] ), 'cn:' );
      open_td( 'colspan=2', string_element( $f['cn'] ) );

    open_tr( 'medskip smallskipb' );
      open_td( 'bold,colspan=3', 'Kontakt:' );

    open_tr();
      open_td( array( 'label' => $f['title'] ), 'Anrede:' );
      open_td( 'colspan=2', string_element( $f['title'] ) );

    open_tr();
      open_td( array( 'label' => $f['gn'] ), 'Vorname:' );
      open_td( 'colspan=2', string_element( $f['gn'] ) );

    open_tr();
      open_td( array( 'label' => $f['sn'] ), 'Nachname:' );
      open_td( 'colspan=2', string_element( $f['sn'] ) );

    open_tr();
      open_td( array( 'label' => $f['mail'] ), 'Email:' );
      open_td( 'colspan=2', string_element( $f['mail'] ) );

    open_tr();
      open_td( array( 'label' => $f['telephonenumber'] ), 'Telefon:' );
      open_td( 'colspan=2', string_element( $f['telephonenumber'] ) );

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
      open_td( 'colspan=3,bold', 'Zugang:' );

    open_tr();
      open_td( array( 'label' => $f['uid'] ), 'User-Id:' );
      open_td( 'colspan=2', string_element( $f['uid'] ) );

    open_tr();
      open_td( array( 'class' => 'right', 'label' => $f['auth_method_simple'] ), 'simple auth:' );
      open_td( 'colspan=2' );
        open_input( $f['auth_method_simple'] );
          echo radiobutton_element( $f['auth_method_simple'], array( 'value' => 1, 'text' => 'ja' ) );
          quad();
          echo radiobutton_element( $f['auth_method_simple'], array( 'value' => 0, 'text' => 'nein' ) );
        close_input();

    open_tr();
      open_td( array( 'class' => 'right', 'label' => $f['auth_method_ssl'] ), 'ssl auth:' );
      open_td( 'colspan=2' );
        open_input( $f['auth_method_ssl'] );
          echo radiobutton_element( $f['auth_method_ssl'], array( 'value' => 1, 'text' => 'ja' ) );
          quad();
          echo radiobutton_element( $f['auth_method_ssl'], array( 'value' => 0, 'text' => 'nein' ) );
        close_input();

    open_tr( 'medskip' );
      open_td( array( 'class' => 'bold top', 'label' => $f['note'] ), 'Kommentar:' );
      open_td( 'colspan=2', textarea_element( $f['note'] ) );

    open_tr();
      open_td( 'right,colspan=3' );
        if( $people_id )
          template_button();
        reset_button( $f['_changes'] ? '' : 'display=none' );
        submission_button( $f['_changes'] ? '' : 'display=none' );
  close_table();

  if( $people_id ) {

    open_fieldset( 'small_form', 'Personenkonten' );

      $uk = sql_unterkonten( array( 'people_id' => $people_id ) );
      if( ! $uk ) {
        open_div( 'center', '(keine Personenkonten vorhanden)' );
        medskip();
      }

      open_div( 'right oneline smallskip' );
        echo 'Neues Personenkonto: ';
        selector_hauptkonto( NULL, array( 'filters' => 'personenkonto=1' ) );
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
