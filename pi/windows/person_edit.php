<?php

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'people_id', 'global,type=u,sources=self http,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

$is_admin = have_minimum_person_priv( PERSON_PRIV_ADMIN );

while( $reinit ) {

  switch( $reinit ) {
    case 'init':
      $sources = 'http self initval default';
      break;
    case 'self':
      $sources = 'self initval default';  // need 'initval' here for big blobs!
      break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'initval default';
      break;
    default:
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'person,init' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'people'
  , 'failsafe' => 0   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $people_id ) {
    $person = sql_person( $people_id );
    $auth_methods_array = explode( ',', $person['authentication_methods'] );
    $person['auth_method_simple'] = ( in_array( 'simple', $auth_methods_array ) ? '1' : '0' );
    $person['auth_method_ssl'] = ( in_array( 'ssl', $auth_methods_array ) ? '1' : '0' );
    $opts['rows'] = array( 'people' => $person );

    $aff_rows = sql_affiliations( "people_id=$people_id", 'orderby=affiliations.priority' );
    $naff_old = max( count( $aff_rows ), 1 );
    if( ( $edit_account = have_priv( 'person', 'account', $people_id ) ) ) {
      $edit_pw = 1;
    } else {
      $edit_pw = have_priv( 'person', 'password', $people_id );
    }
    $edit_affiliations = have_priv( 'person', 'affiliations', $person );
  } else {
    $aff_rows = array();
    $naff_old = 1;
    $edit_account = $edit_pw = 0;

    $edit_affiliations = true;
  }

  if( 0 * $debug ) {
    debug( $edit_pw, 'edit_pw' );
    debug( $edit_account, 'edit_account' );
  }
  $fields = array(
      'title' => 'size=10'
    , 'gn' => 'size=40'
    , 'sn' => 'size=40'
    , 'jpegphoto' => 'set_scopes='
  );
  if( $edit_account ) {
    $fields['privs'] = '';
    $fields['auth_method_simple'] = 'type=b';
    $fields['auth_method_ssl'] = 'type=b';
    $fields['uid'] = 'size=20';
  }
  $f = init_fields( $fields , $opts );

  $problems = $f['_problems'];
  $changes = $f['_changes'];
  $pw_class = '';

  if( $edit_account ) {
    $auth_methods_array = array();
    if( $f['auth_method_simple']['value'] ) {
      $auth_methods_array[] = 'simple';
      if( ! $person['password_hashfunction'] ) {
        // $problems['passwd'] = $problems['passwd2'] = 'need password';
        $pw_class = 'problem';
      }
    }
    if( $f['auth_method_ssl']['value'] )
      $auth_methods_array[] = 'ssl';
    if( $flag_problems ) {
      if( $auth_methods_array ) {
        if( ! $f['uid']['value'] ) {
          $f['uid']['class'] = 'problem';
          $f['uid']['problem'] = 'need uid';
          $f['_problems']['uid'] = 'need uid';
        }
      }
    }
  }

  $opts['tables'] = 'affiliations';
  if( $reinit == 'reset' ) {
    init_var( 'naff', "global,type=U,sources=initval,default=1,set_scopes=self,initval=$naff_old" );
  } else {
    init_var( 'naff', "global,type=U,sources=self initval,default=1,set_scopes=self,initval=$naff_old" );
  }
  for( $j = 0; $j < $naff; $j++ ) {
    $opts['rows'] = array( 'affiliations' => adefault( $aff_rows, $j, array() ) );
    $opts['cgi_prefix'] = "aff{$j}_";
    $faff[ $j ] = init_fields( array(
        'priority' => "sources=default,default=$j"
      , 'roomnumber' => 'size=40'
      , 'groups_id' => array( 'more_choices' => array( 0 => ' (keine) ' ) )
      , 'street' => 'size=40'
      , 'street2' => 'size=40'
      , 'city' => 'size=40'
      , 'telephonenumber' => 'size=40'
      , 'facsimiletelephonenumber' => 'size=40'
      , 'mail' => 'size=40'
      , 'note' => 'lines=4,cols=60'
      )
    , $opts
    );
    if( $faff[ $j ]['roomnumber']['value'] ) {
      $faff[ $j ]['roomnumber']['value'] =
        preg_replace( '/^(\d[.]\d+)$/', '2.28.$1', $faff[ $j ]['roomnumber']['value'] );
    }
    if( $faff[ $j ]['telephonenumber']['value'] ) {
      $faff[ $j ]['telephonenumber']['value'] =
        preg_replace( '/^(\d{4})$/', '+49 331 977 $1', $faff[ $j ]['telephonenumber']['value'] );
    }
    if( $faff[ $j ]['facsimiletelephonenumber']['value'] ) {
      $faff[ $j ]['facsimiletelephonenumber']['value'] =
        preg_replace( '/^(\d{4})$/', '+49 331 977 $1', $faff[ $j ]['facsimiletelephonenumber']['value'] );
    }
    $problems = array_merge( $problems, $faff[ $j ]['_problems'] );
    $changes = array_merge( $changes, $faff[ $j ]['_changes'] );
  }

  $reinit = false;

  handle_action( array( 'reset', 'save', 'update', 'init', 'template', 'naffPlus', 'naffDelete', 'deletePhoto', 'deletePerson' ) );
  switch( $action ) {
    case 'template':
      $people_id = 0;
      $edit_pw = $edit_account = 0;
      break;

    case 'save':
      // debug( $f, 'f' );
      if( $edit_pw ) {
        $pw = init_var( 'passwd', 'type=h32,default=,scopes=http' );
        $pw2 = init_var( 'passwd2', 'type=h32,default=,scopes=http' );
        if( $pw['value'] && strlen( $pw['value'] ) >= 1 ) {
          // debug( $pw, 'pw' );
          // debug( $pw2, 'pw2' );
          if( $pw['value'] !== $pw2['value'] ) {
            $pw_class = 'problem';
          } else {
            auth_set_password( $people_id, $pw['value'] );
            $info_messages[] = we('password has been changed','Passwort wurde ge&auml;ndert');
          }
        }
      }
      if( ! $problems ) {
        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            $values[ $fieldname ] = $r['value'];
        }
        if( $people_id ) {
          if( $edit_account ) {
            unset( $values['auth_method_simple'] );
            unset( $values['auth_method_ssl'] );
            $values['authentication_methods'] = implode( ',', $auth_methods_array );
          }
        }
        $aff_values = array();
        for( $j = 0; $j < $naff; $j++ ) {
          $v = array();
          foreach( $faff[ $j ] as $fieldname => $r ) {
            if( $fieldname[ 0 ] !== '_' )
              $v[ $fieldname ] = $r['value'];
          }
          $aff_values[] = $v;
        }
        $people_id = sql_save_person( $people_id, $values, $aff_values );
        js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
        $info_messages[] = we('entry was saved','Eingaben wurden gespeichert');
        reinit('reset');

      } else {
        // debug( $problems, 'problems' );
      }

      break;

    case 'naffPlus':
      $naff++;
      reinit('self');
      break;

    case 'naffDelete':
      // debug( $GLOBALS['jlf_persistent_vars']['self'], 'self before' );
      while( $message < $naff - 1 ) {
        mv_persistent_vars( 'self', '/^aff'.($message+1).'_/', "aff{$message}_" );
        $message++;
      }
      // debug( $GLOBALS['jlf_persistent_vars']['self'], 'self after' );
      $naff--;
      reinit('self');
      break;

    case 'deletePhoto':
      need( $people_id );
      sql_update( 'people', $people_id, array( 'jpegphoto' => '' ) );
      reinit('self');
      break;

    case 'deletePerson':
      // is handled at end of script - we want to display the data for a last time then fade it :-)
  }
}

// debug( $_FILES, 'FILES' );

// debug( $f['jpegphoto']['source'], 'jpegphoto: from source: ' + strlen( $f['jpegphoto']['value'] ) + ' bytes' );

if( $people_id ) {
  open_fieldset( 'small_form old', we('permanent data for person','Stammdaten Person') );
} else {
  open_fieldset( 'small_form new', we('new person','neue Person') );
}
  open_table('small_form hfill');
    open_tr();
      open_th( 'colspan=2', 'Person:' );
    open_tr();
      open_td( array( 'label' => $f['title'] ), we('Title:','Titel:') );
      open_td( '', string_element( $f['title'] ) );
    open_tr();
      open_td( array( 'label' => $f['gn'] ), we('First name(s):','Vorname(n):') );
      open_td( '', string_element( $f['gn'] ) );
    open_tr();
      open_td( array( 'label' => $f['sn'] ), we('Last name:','Nachname:') );
      open_td( '', string_element( $f['sn'] ) );
if( $people_id ) {
    if( $f['jpegphoto']['value'] ) {
      open_tr();
        open_td( '', we('existing photo:','vorhandenes Foto:' ) );
        open_td( 'rowspan=2', html_tag( 'img', array(
            'height' => '100'
          , 'src' => 'data:image/jpeg;base64,' . $f['jpegphoto']['value']
          ), NULL
        ) );
      open_tr();
        open_td( 'right', inlink( '', array(
          'action' => 'deletePhoto', 'class' => 'drop'
        , 'title' => we('delete photo','Foto löschen')
        , 'confirm' => we('really delete photo?','Foto wirklich löschen?')
        ) ) );
    }
    open_tr();
      open_td( array( 'label' => $f['jpegphoto'] ), we('upload photo:','Foto hochladen:') );
      open_td( '', file_element( $f['jpegphoto'] ) . ' (jpeg, max. 200kB)' );
if( $edit_account ) {
    open_tr();
      open_td( array( 'label' => $f['uid'] ), we('user id:','Benutzerkennung:') );
      open_td( '', string_element( $f['uid'] ) );
    open_tr();
      open_td( array( 'class' => 'right', 'label' => $f['auth_method_simple'] ), 'simple auth:' );
      open_td( 'colspan=2' );
        open_input( $f['auth_method_simple'] );
          echo radiobutton_element( $f['auth_method_simple'], array( 'value' => 1, 'text' => we('yes','ja') ) );
          quad();
          echo radiobutton_element( $f['auth_method_simple'], array( 'value' => 0, 'text' => we('no','nein') ) );
        close_input();

    open_tr();
      open_td( array( 'class' => 'right', 'label' => $f['auth_method_ssl'] ), 'ssl auth:' );
      open_td( 'colspan=2' );
        open_input( $f['auth_method_ssl'] );
          echo radiobutton_element( $f['auth_method_ssl'], array( 'value' => 1, 'text' => we('yes','ja') ) );
          quad();
          echo radiobutton_element( $f['auth_method_ssl'], array( 'value' => 0, 'text' => we('no','nein') ) );
        close_input();
    open_tr();
      open_td( '', we('existing password:','aktuelles Password:') );
      if( $person['password_hashfunction'] ) {
        open_td( 'kbd', "{$person['password_hashfunction']}: {$person['password_hashvalue']}" );
      } else {
        open_td( '', we('(no password set)','(kein Passwort gesetzt)') );
      }
    open_tr();
      open_td( array( 'label' => $f['privs'] ), we('privileges:','Rechte:') );
      open_td( 'colspan=2' );
        open_input( $f['privs'] );
          echo radiobutton_element( $f['privs'], array( 'value' => 0, 'text' => we('none','keine') ) );
          quad();
          echo radiobutton_element( $f['privs'], array( 'value' => PERSON_PRIV_USER, 'text' => we('user','user') ) );
          quad();
          echo radiobutton_element( $f['privs'], array( 'value' => PERSON_PRIV_COORDINATOR, 'text' => we('coordinator','coordinator') ) );
          quad();
          echo radiobutton_element( $f['privs'], array( 'value' => PERSON_PRIV_ADMIN, 'text' => we('admin','admin') ) );
        close_input();
}
if( $edit_pw ) {
    open_tr();
      open_td( $pw_class, we('new password:','Neues Passwort:') );
      open_td();
        open_span( "oneline $pw_class" );
          echo html_tag( 'input', 'type=password,size=8,name=passwd,value=', NULL );
          qquad();
          echo we('again: ','nochmal: ') . html_tag( 'input', 'type=password,size=8,name=passwd2,value=', NULL );
        close_span();
}
}
    open_tr('medskip');
      open_td( 'colspan=2' );

    for( $j = 0; $j < $naff; $j++ ) {
      open_tr('medskip');
      open_tr();
        open_th( 'colspan=2' );
          if( ( $naff > 1 ) && $edit_affiliations ) {
            echo inlink( 'self', "class=href drop,action=naffDelete,message=$j,title=".we('delete contact','Kontakt löschen') );
          }
          printf( we('contact','Kontakt') .' %d:', $j+1 );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['roomnumber'] ), we('Group:','Gruppe:') );
        open_td();
          if( $edit_affiliations ) {
            echo selector_groups( $faff[ $j ]['groups_id'] );
          } else {
            echo html_alink_group( $faff[ $j ]['groups_id']['value'] );
          }
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['roomnumber'] ), we('Room:','Raum:') );
        open_td( '', string_element( $faff[ $j ]['roomnumber'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['street'] ), we('Street:','Strasse:') );
        open_td( '', string_element( $faff[ $j ]['street'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['street2'] ), ' ' );
        open_td( '', string_element( $faff[ $j ]['street2'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['city'] ), we('City:','Stadt:') );
        open_td( '', string_element( $faff[ $j ]['city'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['telephonenumber'] ), we('Phone:','Telefon:') );
        open_td( '', string_element( $faff[ $j ]['telephonenumber'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['facsimiletelephonenumber'] ), 'Fax:' );
        open_td( '', string_element( $faff[ $j ]['facsimiletelephonenumber'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['mail'] ), 'email:' );
        open_td( '', string_element( $faff[ $j ]['mail'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['note'] ), we('Note:','Notiz:') );
        open_td();
          echo textarea_element( $faff[ $j ]['note'] );
    }
    if( $edit_affiliations ) {
      open_tr( 'medskip' );
        open_td( 'colspan=2', inlink( 'self', 'class=button plus,action=naffPlus,text='.we('add contact','Kontakt hinzufügen') ) );
    }

    open_tr( 'bigskip' );
      open_td( 'left' );
      open_td( 'right,colspan=2' );
        if( $people_id )
          if( ! sql_delete_people( $people_id, 'check' ) ) {
            echo inlink( 'self', array(
              'class' => 'drop button qquads'
            , 'action' => 'deletePerson'
            , 'text' => we('delete person','Person löschen')
            , 'confirm' => we('really delete person?','Person wirklich löschen?')
            ) );
          }
          echo inlink( 'person_view', array(
            'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
          , 'people_id' => $people_id
          ) );
        if( $people_id && ! $changes )
          template_button();
        reset_button( $changes ? '' : 'display=none' );
        submission_button();
  close_table();
close_fieldset();

if( $action === 'deletePerson' ) {
  need( $people_id );
  sql_delete_people( $people_id );
  js_on_exit( "flash_close_message($H_SQ".we('person deleted','Person gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
