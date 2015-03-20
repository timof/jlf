<?php
// person.php: prototypical edit-script

sql_transaction_boundary('*');

define( 'OPTION_SHOW_STAMM', 1 );
define( 'OPTION_SHOW_KONTAKT', 2 );
define( 'OPTION_SHOW_ANSCHRIFT', 4 );
define( 'OPTION_SHOW_BANK', 8 );
define( 'OPTION_SHOW_ACCOUNT', 16 );

need_priv( 'books', ( ( $action === 'nop' ) ? 'read' : 'write' ) );

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'people_id', 'global,type=u,sources=self http,set_scopes=self' );

if( $people_id ) {
  init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window,default='.OPTION_SHOW_KONTEN );
} else {
  init_var( 'options', 'global,type=u,sources=initval,set_scopes=window,initval='. ( OPTION_SHOW_STAMM | OPTION_SHOW_KONTAKT | OPTION_SHOW_ANSCHRIFT | OPTION_SHOW_BANK ) );
}

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

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'people'
  , 'failsafe' => false // allow invalid values (to be flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $people_id ) {
    $person = sql_person( $people_id );
    $opts['rows'] = array( 'people' => $person );

    if( ( $edit_account = have_priv( 'person', 'account', $people_id ) ) ) {
      $edit_pw = 1;
    } else {
      $edit_pw = have_priv( 'person', 'password', $people_id );
    }
  } else {
    $edit_account = $edit_pw = 0;
  }

  $fields = array(
    'title' => 'size=10'
  , 'gn' => 'size=20'
  , 'sn' => 'size=24'
  , 'cn' => 'type=H,size=40'
  , 'jperson' => ''
  , 'status_person' => ''
  , 'genus' => ''
  , 'dusie' => ''
  , 'mail' => 'size=40'
  , 'street' => 'size=40'
  , 'street2' => 'size=40'
  , 'city' => 'size=40'
  , 'note' => 'lines=4,cols=60'
  , 'telephonenumber' => 'size=20'
  , 'facsimiletelephonenumber' => 'size=20'
  , 'uid' => 'size=12'
  , 'bank_cn' => 'size=40'
  , 'bank_blz' => 'size=20'
  , 'bank_kontonr' => 'size=20'
  , 'bank_iban' => 'size=40'
  , 'bank_bic' => 'size=11'
  );
  if( $edit_account ) {
    $fields['privs'] = '';
    $fields['privlist'] = 'size=60';
    $fields['authentication_method_simple'] = 'type=b';
    $fields['authentication_method_ssl'] = 'type=b';
    $fields['uid'] = 'size=20';
  }
  $f = init_fields( $fields, $opts );
  $problems = $f['_problems'];
  $pw_class = '';

  if( $edit_account ) {
    if( $f['authentication_method_simple']['value'] ) {
      if( ! $person['password_hashfunction'] ) {
        $pw_class = 'problem';
      }
    }
    if( $flag_problems ) {
      if( $f['authentication_method_simple']['value'] || $f['authentication_method_ssl']['value'] ) {
        if( ! $f['uid']['value'] ) {
          $f['uid']['class'] = 'problem';
          $f['uid']['problem'] = 'need uid';
          $f['_problems']['uid'] = 'need uid';
        }
      }
    }
  }

  // initialization done (but re-initialization may be triggered by calling reinit() below):
  //
  $reinit = false;

  // handle actions:
  //
  handle_actions( array( 'reset', 'save', 'init', 'template', 'deletePerson' ) ); 
  switch( $action ) {
    case 'template':
      $people_id = 0;
      $edit_pw = $edit_account = 0;
      break;

    case 'save':
      if( ! $problems ) {
        if( $edit_pw ) {
          $pw = init_var( 'passwd', 'type=h32,default=,scopes=http' );
          $pw2 = init_var( 'passwd2', 'type=h32,default=,scopes=http' );
          if( $pw['value'] && strlen( $pw['value'] ) >= 1 ) {
            if( $pw['value'] !== $pw2['value'] ) {
              $pw_class = 'problem';
            } else {
              auth_set_password( $people_id, $pw['value'] );
              $info_messages[] = we('password has been changed','Passwort wurde geändert');
            }
          }
        }

        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' ) {
            if( $r['source'] !== 'initval' ) {
              $values[ $fieldname ] = $r['value'];
            }
          }
        }

        $error_messages = sql_save_person( $people_id, $values, 'action=dryrun' );
        if( ! $error_messages ) {
          $people_id = sql_save_person( $people_id, $values, 'action=hard' );
          js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
          $info_messages[] = 'Eintrag wurde gespeichert';
          reinit('reset');
        }

      } else {
        $error_messages += new_problem('Speichern fehlgeschlagen');
        // debug( $problems, 'problems' );
      }

      break;

//     case 'createUnterkonto':
//       $hk_field = init_var( 'hauptkonten_id', 'sources=http,type=u' );
//       need( $hf_field['value'] && $people_id );
//       openwindow( 'unterkonto', array( 'hauptkonten_id' => $hk_field['value'], 'people_id' => $people_id ) );
//       break;
// 
//     case 'deleteUnterkonto':
//       need( $message > 0, 'kein unterkonto gewaehlt' );
//       sql_delete_unterkonten( $message );
//       break;
// 
//     case 'unterkontoSchliessen':
//       need( $message > 0, 'kein unterkonto gewaehlt' );
//       sql_unterkonto_schliessen( $message );
//       break;

  }
}

if( $people_id ) {
  open_fieldset( 'old', "Person [$people_id]" );
} else {
  open_fieldset( 'new', 'Neue Person' );
}

  if( $options & OPTION_SHOW_STAMM ) {
    if( $people_id ) {
      $t = inlink( '!', array( 'class' => 'icon close quadr', 'text' => '', 'options' => $options & ~OPTION_SHOW_STAMM ) );
    } else {
      $t = '';
    }
    open_fieldset( '', $t.'Stammdaten:' );
      open_fieldset( 'line'
      , label_element( $f['jperson'], '', 'Art:' )
      , radiobutton_element( $f['jperson'], array( 'value' => 'N', 'text' => 'natürlich' ) )
        . radiobutton_element( $f['jperson'], array( 'value' => 'J', 'text' => 'juristisch' ) )
      );
  
      open_fieldset( 'line' , label_element( $f['cn'], '', 'cn:' ) , string_element( $f['cn'] ) );
      open_fieldset( 'line' , label_element( $f['status_person'], '', 'Status:' ) , selector_status_person( $f['status_person'] ) );
      open_fieldset( '', 'Kommentar:', textarea_element( $f['note'] ) );
    close_fieldset();
  } else {
    open_fieldset( 'line', $f['cn']['value'] );
  }

  if( $options & OPTION_SHOW_KONTAKT ) {
    if( $people_id ) {
      $t = inlink( '!', array( 'class' => 'icon close quadr', 'text' => '', 'options' => $options & ~OPTION_SHOW_KONTAKT ) );
    } else {
      $t = '';
    }
    open_fieldset( '', $t.'Kontakt:' );
      open_fieldset( 'line' , label_element( $f['dusie'], '', 'Anrede:' ) , selector_dusie( $f['dusie'] ) );
      open_fieldset( 'line' , label_element( $f['genus'], '', 'Genus:' ) , selector_genus( $f['genus'] ) );
      open_fieldset( 'line' , label_element( $f['title'], '', 'Titel:' ) , string_element( $f['title'] ) );
      open_fieldset( 'line' , label_element( $f['gn'], '', 'Vorname(n):' ) , string_element( $f['gn'] ) );
      open_fieldset( 'line' , label_element( $f['sn'], '', 'Nachname:' ) , string_element( $f['sn'] ) );
  
      open_fieldset( 'line smallskipt' , label_element( $f['mail'], '', 'Email:' ) , string_element( $f['mail'] ) );
      open_fieldset( 'line' , label_element( $f['telephonenumber'], '', 'Telefon:' ) , string_element( $f['telephonenumber'] ) );
      open_fieldset( 'line' , label_element( $f['facsimiletelephonenumber'], '', 'Fax:' ) , string_element( $f['facsimiletelephonenumber'] ) );
  
    close_fieldset();
  } else {
    open_fieldset( 'line', "{$f['title']['value']} {$f['gn']['value']} {$f['sn']['value']}" );
    echo inlink( '!', array( 'class' => 'button edit', 'text' => 'Kontakt', 'options' => $options | OPTION_SHOW_KONTAKT ) );
  }

  if( $options & OPTION_SHOW_ANSCHRIFT ) {
    if( $people_id ) {
      $t = inlink( '!', array( 'class' => 'icon close quadr', 'text' => '', 'options' => $options & ~OPTION_SHOW_ANSCHRIFT ) );
    } else {
      $t = '';
    }
    open_fieldset( '', 'Anschrift:' );
      open_fieldset( 'line' , label_element( $f['street'], '', "Stra{$SZLIG}e:" ) );
        open_div( 'oneline', string_element( $f['street'] ) );
        open_div( 'oneline', string_element( $f['street2'] ) );
      close_fieldset();
      open_fieldset( 'line' , label_element( $f['city'], '', 'Ort:' ) , string_element( $f['city'] ) );
    close_fieldset();
  } else {
    echo inlink( '!', array( 'class' => 'button edit', 'text' => 'Anschrift', 'options' => $options | OPTION_SHOW_ANSCHRIFT ) );
  }

  if( $options & OPTION_SHOW_BANK ) {
    if( $people_id ) {
      $t = inlink( '!', array( 'class' => 'icon close quadr', 'text' => '', 'options' => $options & ~OPTION_SHOW_BANK ) );
    } else {
      $t = '';
    }
    open_fieldset( '', 'Bankverbindung:' );
  
      open_fieldset( 'line' , label_element( $f['bank_cn'], '', 'Bank:' ) , string_element( $f['bank_cn'] ) );
      open_fieldset( 'line' , label_element( $f['bank_blz'], '', 'BLZ:' ) , string_element( $f['bank_blz'] ) );
      open_fieldset( 'line' , label_element( $f['bank_kontonr'], '', 'Konto-Nr:' ) , string_element( $f['bank_kontonr'] ) );
      open_fieldset( 'line' , label_element( $f['bank_iban'], '', 'IBAN:' ) , string_element( $f['bank_iban'] ) );
      open_fieldset( 'line' , label_element( $f['bank_bic'], '', 'BIC:' ) , string_element( $f['bank_bic'] ) );
    close_fieldset();
  } else {
    echo inlink( '!', array( 'class' => 'button edit', 'text' => 'Bank', 'options' => $options | OPTION_SHOW_BANK ) );
  }


if( $people_id && ( $edit_account || $edit_pw ) ) {

  if( $options & OPTION_SHOW_ACCOUNT ) {
    if( $people_id ) {
      $t = inlink( '!', array( 'class' => 'icon close quadr', 'text' => '', 'options' => $options & ~OPTION_SHOW_ACCOUNT ) );
    } else {
      $t = '';
    }

    open_fieldset( 'medskipt', $t . we('account:','Zugang:') );

      if( $edit_account ) {
    
        open_fieldset('line', label_element( $f['authentication_method_simple'], '', 'simple auth:' ) );
          echo radiobutton_element( $f['authentication_method_simple'], array( 'value' => 1, 'text' => we('yes','ja'), 'class' => 'qquadl' ) );
          echo radiobutton_element( $f['authentication_method_simple'], array( 'value' => 0, 'text' => we('no','nein'), 'class' => 'qquadl' ) );
        close_fieldset();
    
        open_fieldset('line', label_element( $f['authentication_method_ssl'], '', 'ssl auth:' ) );
          echo radiobutton_element( $f['authentication_method_ssl'], array( 'value' => 1, 'text' => we('yes','ja'), 'class' => 'qquadl' ) );
          echo radiobutton_element( $f['authentication_method_ssl'], array( 'value' => 0, 'text' => we('no','nein'), 'class' => 'qquadl' ) );
        close_fieldset();
    
        open_fieldset('line'
        , label_element( $f['uid'], '', we('user id:','Benutzerkennung:') )
        , string_element( $f['uid'] )
        );
    
        open_fieldset('line', we('password:','Password:') );
          if( $person['password_hashfunction'] ) {
            open_div( 'kbd', "{$person['password_hashfunction']}: {$person['password_hashvalue']}" );
          } else {
            open_div( '', we('(no password set)','(kein Passwort gesetzt)') );
          }
        close_fieldset();
  
      } else {
        open_div('smallskips', "Benutzerkennung: {$f['uid']}" ); 
      }
  
      if( $edit_pw ) {
        open_fieldset('line smallskipt', label_element( 'passwd', "class=$pw_class,for=passwd", we('new password:','Neues Passwort:') ) );
          open_tag( 'label', "oneline $pw_class,for=passwd", we('password:','Passwort:') . html_tag( 'input', 'class=quadl,type=password,size=8,name=passwd,value=', NULL ) );
          open_tag( 'label', "oneline $pw_class qquadl,for=passwd2", we('again:','nochmal:') . html_tag( 'input', 'class=quadl,type=password,size=8,name=passwd2,value=', NULL ) );
        close_fieldset();
      }
  
      if( $edit_account ) {
        open_fieldset('line', label_element( $f['privs']['class'], '', we('privileges:','Rechte:') ) );
          open_div('oneline');
            echo radiobutton_element( $f['privs'], array( 'value' => 0, 'text' => we('none','keine') ) );
            echo radiobutton_element( $f['privs'], array( 'value' => PERSON_PRIV_READ, 'text' => 'lesen' ) );
            echo radiobutton_element( $f['privs'], array( 'value' => PERSON_PRIV_WRITE, 'text' => 'schreiben' ) );
            echo radiobutton_element( $f['privs'], array( 'value' => PERSON_PRIV_ADMIN, 'text' => we('admin','admin') ) );
          close_div();
          open_fieldset('line'
          , label_element( $f['privlist'], '', we('more privileges:','weitere Rechte:') )
          , string_element( $f['privlist'] )
          );
        close_fieldset();
      }
    close_fieldset();
  } else {
    echo inlink( '!', array( 'class' => 'button edit', 'text' => 'Account', 'options' => $options | OPTION_SHOW_ACCOUNT ) );
  }
}

  open_div('right bigskipt');
    if( $people_id ) {
      echo inlink( '', array(
        'class' => 'drop button qquadr'
      , 'action' => 'deletePerson'
      , 'text' => we('delete person','Person löschen')
      , 'confirm' => 'wirklich löschen?'
      , 'inactive' => sql_delete_people( $people_id, 'action=dryrun' )
      ) );
      if( have_priv( 'people','create' ) ) {
        echo template_button_view();
      }
    }
    echo reset_button_view();
    if( have_priv( 'people','write', $people_id ) ) {
      echo save_button_view();
    }
  close_div();


if( $people_id ) {

  if( $options & OPTION_SHOW_KONTEN ) {
    
    $t = inlink( '!', array( 'class' => 'icon close quadr', 'text' => '', 'options' => $options & ~OPTION_SHOW_KONTEN ) );
    open_fieldset( 'small_form', $t.'Personenkonten' );

      // open_div( 'right oneline smallskip' );
      //   echo 'Neues Personenkonto: ' . selector_hauptkonto( NULL, array( 'filters' => 'flag_personenkonto=1' ) );
      // close_div();

      init_var( 'unterkonten_id', 'global,type=u,sources=http persistent,set_scopes=self' );
      unterkontenlist_view( array( 'people_id' => $people_id ), array( 'select' => 'unterkonten_id', 'geschaeftsjahr' => $geschaeftsjahr_thread ) );
      if( $unterkonten_id ) {
        bigskip();
        postenlist_view( array( 'unterkonten_id' => $unterkonten_id, 'geschaeftsjahr' => $geschaeftsjahr_thread ) );
      }

    close_fieldset();

    //     open_fieldset( 'small_form', 'Darlehen' );
    //       open_div( 'right', inlink( 'darlehen', array( 
    //         'class' => 'button', 'text' => 'Neues Darlehen', 'people_id' => $people_id
    //       ) ) );
    //       smallskip();
    //       darlehenlist_view( array( 'people_id' => $people_id ), '' );
    //     close_fieldset();
  } else {
    echo inlink( '!', array( 'class' => 'button edit', 'text' => 'Personenkonten', 'options' => $options | OPTION_SHOW_KONTEN ) );
  }
}

close_fieldset();

if( $action === 'deletePerson' ) {
  need( $people_id > 0, 'keine person ausgewaehlt' );
  sql_delete_people( $people_id, 'action=hard' );
  js_on_exit( "flash_close_message($H_SQ".we('person deleted','Person gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
