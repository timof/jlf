<?php


init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'people_id', 'global,type=u,sources=self http,set_scopes=self' );

need_priv( 'person', $people_id ? 'edit' : 'create', $people_id );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {

  switch( $reinit ) {
    case 'init':
      $sources = 'http self initval';
      break;
    case 'self':
      $sources = 'self initval';  // need 'initval' here for big blobs!
      break;
    case 'reset':
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
  , 'failsafe' => 0   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $people_id ) {
    $person = sql_person( $people_id );
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

  //  debug( $edit_pw, 'edit_pw' );
  //  debug( $edit_account, 'edit_account' );

  $fields = array(
      'title' => 'size=10'
    , 'gn' => 'size=40'
    , 'sn' => 'size=40'
    , 'url' => 'size=60'
    , 'jpegphoto' => 'set_scopes='
    , 'flag_institute' => 'text='.we('list as member of institute','als Institutsmitglied anzeigen')
  );
  if( $edit_account ) {
    $fields['privs'] = '';
    $fields['authentication_method_simple'] = 'type=b';
    $fields['authentication_method_ssl'] = 'type=b';
    $fields['uid'] = 'size=20';
  }
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    $fields['flag_virtual'] = 'text='.we('virtual','virtuell');
    $fields['flag_deleted'] = 'text='.we('marked as deleted','als geloescht markiert');
  }
  $f = init_fields( $fields, $opts );

  $problems = $f['_problems'];
  $changes = $f['_changes'];
  $pw_class = '';

  if( $edit_account ) {
    if( $f['authentication_method_simple']['value'] ) {
      if( ! $person['password_hashfunction'] ) {
        // $problems['passwd'] = $problems['passwd2'] = 'need password';
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

  $opts['tables'] = 'affiliations';
  if( $reinit == 'reset' ) {
    init_var( 'naff', "global,type=U,sources=initval,default=1,set_scopes=self,initval=$naff_old" );
  } else {
    init_var( 'naff', "global,type=U,sources=self initval,default=1,set_scopes=self,initval=$naff_old" );
  }
  $priv_position = have_priv( 'person', 'position' );
  $priv_position_budget = have_priv( 'person', 'positionBudget' );

  for( $j = 0; $j < $naff; $j++ ) {
    $row = adefault( $aff_rows, $j, array() );
    $opts['rows'] = array( 'affiliations' => $row );
    $opts['cgi_prefix'] = "aff{$j}_";
    $fields = array(
      'priority' => "sources=default,default=$j"
    , 'roomnumber' => 'size=40'
    , 'groups_id' => 'type=U'
    , 'street' => 'size=40'
    , 'street2' => 'size=40'
    , 'city' => 'size=40'
    , 'telephonenumber' => 'size=40'
    , 'facsimiletelephonenumber' => 'size=40'
    , 'mail' => 'size=40'
    , 'note' => 'lines=4,cols=60'
    , 'typeofposition' => 'auto=1,default=o'
    , 'teaching_obligation' => 'min=0,max=8,auto=1'
    , 'teaching_reduction' => 'min=0,max=8,auto=1'
    , 'teaching_reduction_reason' => 'size=20'
    );
    if( adefault( $row, 'typeofposition' ) == 'H' ) {
      $is_budget = true;
    }
    if( $priv_position ) {
      $edit_position[ $j ] = true;
      if( adefault( $row, 'typeofposition' ) === 'H' ) {
        if( ! $priv_position_budget ) {
          $fields['typeofposition'] .= ',sources=initval';
          $edit_position[ $j ] = false;
        }
      }
    } else {
      $fields['typeofposition'] .= ',sources=initval';
      $edit_position[ $j ] = false;
    }

    if( ! have_priv( 'person', 'teaching_obligation', $people_id ) ) {
      $fields['teaching_obligation'] .= ',sources=initval';
      $fields['teaching_reduction'] .= ',sources=initval';
      $fields['teaching_reduction_reason'] .= ',sources=initval';
    }
    $faff[ $j ] = init_fields( $fields, $opts );
    $fa = & $faff[ $j ];
    if( $fa['roomnumber']['value'] ) {
      $fa['roomnumber']['value'] =
        preg_replace( '/^(\d[.]\d+)$/', '2.28.$1', $fa['roomnumber']['value'] );
    }
    if( $fa['telephonenumber']['value'] ) {
      $fa['telephonenumber']['value'] =
        preg_replace( '/^(\d{4})$/', '+49 331 977 $1', $fa['telephonenumber']['value'] );
    }
    if( $fa['facsimiletelephonenumber']['value'] ) {
      $fa['facsimiletelephonenumber']['value'] =
        preg_replace( '/^(\d{4})$/', '+49 331 977 $1', $fa['facsimiletelephonenumber']['value'] );
    }
    if( ! $fa['teaching_obligation']['value'] ) {
      $fa['teaching_reduction']['value'] = 0;
      $fa['teaching_reduction_reason']['value'] = '';
    }
    if( $flag_problems ) {
      if( $fa['teaching_reduction']['value'] ) {
        if( ! $fa['teaching_reduction_reason']['value'] ) {
          $fa['teaching_reduction_reason']['class'] = 'problem';
          $fa['teaching_reduction_reason']['problem'] = 'need reason';
          $fa['_problems']['teaching_reduction_reason'] = 'need reason';
        }
      }
    }

    $problems = array_merge( $problems, $fa['_problems'] );
    $changes = array_merge( $changes, $fa['_changes'] );
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
        $info_messages[] = we('entry was saved','Eintrag wurde gespeichert');
        reinit('reset');

      } else {
        $error_messages[] = we('saving failed','Speichern fehlgeschlagen' );
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
      need_priv( 'person', 'edit', $people_id );
      sql_update( 'people', $people_id, array( 'jpegphoto' => '' ) );
      reinit('self');
      break;

    case 'deletePerson':
      // is handled at end of script - we want to display the data for a last time then fade it :-)
  }
}

// debug( $f['jpegphoto']['source'], 'jpegphoto: from source: ' + strlen( $f['jpegphoto']['value'] ) + ' bytes' );

if( $people_id ) {
  open_fieldset( 'old', we('permanent data for person','Stammdaten Person') );
} else {
  open_fieldset( 'new', we('new person','Neue Person') );
}
  flush_all_messages();

  open_fieldset( 'table', 'Person:' );

    open_tr();
      open_td( 'right', label_element( $f['title'], '', we('Title:','Titel:') ) );
      open_td( '', string_element( $f['title'] ) );

    open_tr();
      open_td( 'right', label_element( $f['gn'], '', we('First name(s):','Vorname(n):') ) );
      open_td( '', string_element( $f['gn'] ) );

    open_tr();
      open_td( 'right', label_element( $f['sn'], '', we('Last name:','Nachname:') ) );
      open_td( '', string_element( $f['sn'] ) );

    open_tr();
      open_td( 'right', label_element( $f['url'], '', 'Homepage:' ) );
      open_td( '', string_element( $f['url'] ) );

    open_tr();
      open_td( 'right', 'Flags:' );
      open_td();
        open_span( 'qquad', checkbox_element( $f['flag_institute'] ) );
        if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
          open_span( 'qquad', checkbox_element( $f['flag_virtual'] ) );
          open_span( 'qquad', checkbox_element( $f['flag_deleted'] ) );
        }

if( $people_id ) {
    if( $f['jpegphoto']['value'] ) {
      open_tr();
        open_td( '', we('existing photo:','vorhandenes Foto:' ) );
        open_td( 'oneline',
          html_tag( 'img', array(
              'height' => '100'
            , 'src' => 'data:image/jpeg;base64,' . $f['jpegphoto']['value']
            ), NULL
          )
        . inlink( '', array(
            'action' => 'deletePhoto', 'class' => 'drop'
          , 'title' => we('delete photo','Foto löschen')
          , 'confirm' => we('really delete photo?','Foto wirklich löschen?')
          ) )
        );
    }
    open_tr('td:smallskips');
      open_td( '', label_element( $f['jpegphoto'], '', we('upload photo:','Foto hochladen:') ) );
      open_td( 'oneline', file_element( $f['jpegphoto'] ) . ' (jpeg, max. 200kB)' );

if( $edit_account ) {

    open_tr('solidtop td:bigskipt');
      open_td( 'right', label_element( $f['authentication_method_simple'], '', 'simple auth:' ) );
      open_input( $f['authentication_method_simple'], 'td oneline' );
         echo radiobutton_element( $f['authentication_method_simple'], array( 'value' => 1, 'text' => we('yes','ja') ) );
         qquad();
         echo radiobutton_element( $f['authentication_method_simple'], array( 'value' => 0, 'text' => we('no','nein') ) );

    open_tr();
      open_td( 'right', label_element( $f['authentication_method_ssl'], '', 'ssl auth:' ) );
      open_input( $f['authentication_method_ssl'], 'td' );
        echo radiobutton_element( $f['authentication_method_ssl'], array( 'value' => 1, 'text' => we('yes','ja') ) );
        qquad();
        echo radiobutton_element( $f['authentication_method_ssl'], array( 'value' => 0, 'text' => we('no','nein') ) );

    open_tr();
      open_td( '', label_element( $f['uid'], '', we('user id:','Benutzerkennung:') ) );
      open_td( '', string_element( $f['uid'] ) );

    open_tr();
      open_td( '', we('password:','Password:') );
      if( $person['password_hashfunction'] ) {
        open_td( 'kbd', "{$person['password_hashfunction']}: {$person['password_hashvalue']}" );
      } else {
        open_td( '', we('(no password set)','(kein Passwort gesetzt)') );
      }

    open_tr('td:smallskipb');
      open_td( 'right', label_element( $f['privs']['class'], '', we('privileges:','Rechte:') ) );
      open_td( 'input '.$f['privs']['class'] );
        echo radiobutton_element( $f['privs'], array( 'value' => 0, 'text' => we('none','keine') ) );
        quad();
        echo radiobutton_element( $f['privs'], array( 'value' => PERSON_PRIV_USER, 'text' => we('user','user') ) );
        quad();
        echo radiobutton_element( $f['privs'], array( 'value' => PERSON_PRIV_COORDINATOR, 'text' => we('coordinator','coordinator') ) );
        quad();
        echo radiobutton_element( $f['privs'], array( 'value' => PERSON_PRIV_ADMIN, 'text' => we('admin','admin') ) );

}
if( $edit_pw ) {
    open_tr('td:smallskipb');
      open_td( 'oneline', label_element( 'passwd', "$pw_class", we('new password:','Neues Passwort:') ) );
      open_td( "oneline $pw_class"
      ,  html_tag( 'input', 'type=password,size=8,name=passwd,value=', NULL )
        . hskip('2em')
        . html_tag( 'label', "$pw_class,for=passwd2", we('again: ','nochmal: ') )
        . html_tag( 'input', 'type=password,size=8,name=passwd2,value=', NULL )
      );
}
}

  close_fieldset(); //Person

//
// affiliations:
//

  for( $j = 0; $j < $naff; $j++ ) {

    $fa = & $faff[ $j ];

    $legend = sprintf( we('contact','Kontakt') .' %d:', $j+1 );
    open_fieldset( 'table td:smallskips;quads', $legend );

      if( ( $naff > 1 ) && $edit_affiliations ) {
        open_tr();
            open_td();
            open_td( 'right', inlink( 'self', "class=button drop,action=naffDelete,message=$j,text=".we('delete contact','Kontakt löschen') ) );
      }

      open_tr();
        open_td( '', label_element( $fa['groups_id'], '', we('Group:','Gruppe:') ) );
        open_td( 'oneline' );
          if( $edit_affiliations ) {
            echo selector_groups( $fa['groups_id'] );
          } else if( ( $groups_id = $fa['groups_id']['value'] ) ) {
            echo html_alink_group( $groups_id ); // , 'text='.we('details of group..','Details der Gruppe...') );
          }

      open_tr();
        open_td( '', label_element( $fa['roomnumber'], '', we('Room:','Raum:' ) ) );
        open_td( '', string_element( $fa['roomnumber'] ) );

      open_tr();
        open_td( '', label_element( $fa['street'], '', we('Street:','Strasse:') ) );
        open_td( '', string_element( $fa['street'] ) );

      open_tr();
        open_td( '', label_element( $fa['street2'], '', ' ' ) );
        open_td( '', string_element( $fa['street2'] ) );

      open_tr();
        open_td( '', label_element( $fa['city'], '', we('City:','Stadt:') ) );
        open_td( '', string_element( $fa['city'] ) );

      open_tr();
        open_td( '', label_element( $fa['telephonenumber'], '', we('Phone:','Telefon:') ) );
        open_td( '', string_element( $fa['telephonenumber'] ) );

      open_tr();
        open_td( '', label_element( $fa['facsimiletelephonenumber'], '', 'Fax:' ) );
        open_td( '', string_element( $fa['facsimiletelephonenumber'] ) );

      open_tr();
        open_td( '', label_element( $fa['mail'], '', 'email:' ) );
        open_td( '', string_element( $fa['mail'] ) );

      open_tr();
        open_td( '', label_element( $fa['note'], '', we('Note:','Notiz:') ) );
        open_td( '', textarea_element( $fa['note'] ) );

      open_tr('solidtop td:/smallskips/medskips/');
        open_td( '', label_element( $fa['typeofposition'], '', we('position:','Stelle:') ) );

        if( have_priv( 'person', 'position', $people_id ) ) {
          open_td( '', selector_typeofposition( $fa['typeofposition'], array( 'positionBudget' => have_priv( 'person', 'positionBudget' ) ) ) );
        } else {
          $t = $fa['typeofposition']['value'];
          $tt = adefault( $choices_typeofposition, $t, we('unknown','unbekannt') );
          open_td( '', "$t ($tt)" );
        }

      open_tr();
        open_td( '', label_element( $fa['teaching_obligation'], '', we('teaching oblication: ','Lehrverpflichtung: ') ) );
        open_td();
          if( have_priv( 'person', 'teaching_obligation', $people_id ) ) {
            open_div( 'oneline',
              selector_smallint( $fa['teaching_obligation'] )
              . hskip('2em') . we('reduction: ','Reduktion: ') . selector_smallint( $fa['teaching_reduction'] )
            );
            if( $fa['teaching_reduction']['value'] ) {
              open_div( 'oneline smallskips'
              , label_element( $fa['teaching_reduction_reason'], 'smallskips oneline', we('reason for reduction: ','Reduktionsgrund: ') )
                . string_element( $fa['teaching_reduction_reason'] )
              );
            }
          } else {
            open_div( 'oneline', $fa['teaching_obligation']['value'] . hskip('2em') . we('reduction: ','Reduktion: ') . $fa['teaching_reduction']['value'] );
            if( $fa['teaching_reduction']['value'] ) {
              open_div( 'oneline', we('reason for reduction: ','Reduktionsgrund: ') . $fa['teaching_reduction_reason'] );
            }
          }


    close_fieldset();
  }

  if( $edit_affiliations ) {
    open_div( 'right medskips', inlink( 'self', 'class=button plus,action=naffPlus,text='.we('add contact','Kontakt hinzufügen') ) );
  }
  open_div('right bigskipt');
    if( $people_id ) {
      echo inlink( 'self', array(
        'class' => 'drop button qquadr'
      , 'action' => 'deletePerson'
      , 'text' => we('delete person','Person löschen')
      , 'confirm' => we('really delete person?','Person wirklich löschen?')
      , 'inactive' => sql_delete_people( $people_id, 'check' )
      ) );
      echo inlink( 'person_view', array(
        'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' )
      , 'people_id' => $people_id
      ) );
      echo template_button_view();
    }
    echo reset_button_view();
    echo save_button_view();
  close_div();


close_fieldset();

if( $action === 'deletePerson' ) {
  need( $people_id );
  sql_delete_people( $people_id );
  js_on_exit( "flash_close_message($H_SQ".we('person deleted','Person gelöscht')."$H_SQ );" );
  js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
}

?>
